<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\Stock;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    private function checkStaffAccess(Request $request)
    {
        if (!$request->user()->is_staff) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
    }

    public function index(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $borrowings = Borrowing::with(['user', 'book', 'stock'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('staff.borrowings.index', compact('borrowings'));
    }

    public function create(Request $request)
    {
        $this->checkStaffAccess($request);
        return view('staff.borrowings.create');
    }

    public function store(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'notes' => 'nullable|string',
            'auto_approve' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Kullanıcının mevcut ödünç aldığı kitap sayısını kontrol et
            $activeBookCount = Borrowing::where('user_id', $validatedData['user_id'])
                ->whereNull('returned_at')
                ->count();

            if ($activeBookCount >= 4) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu kullanıcı maksimum kitap limitine (4) ulaşmış durumda.'
                    ], 422);
                }
                return back()->with('error', 'Bu kullanıcı maksimum kitap limitine (4) ulaşmış durumda.');
            }

            // Get the book
            $book = Book::findOrFail($validatedData['book_id']);
            
            // Check if book has available stock
            $availableStock = $book->stocks()
                ->where('status', 'available')
                ->where('is_available', true)
                ->whereDoesntHave('borrowings', function($q) {
                    $q->whereNull('returned_at');
                })
                ->first();

            if (!$availableStock) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu kitabın uygun kopyası bulunmamaktadır.'
                    ], 422);
                }
                return back()->with('error', 'Bu kitabın uygun kopyası bulunmamaktadır.');
            }

            // Create borrowing record
            $borrowing = new Borrowing();
            $borrowing->user_id = $validatedData['user_id'];
            $borrowing->book_id = $validatedData['book_id'];
            $borrowing->stock_id = $availableStock->id;
            $borrowing->borrow_date = $validatedData['borrow_date'];
            $borrowing->due_date = $validatedData['due_date'];
            $borrowing->notes = $validatedData['notes'];
            $borrowing->status = $request->input('auto_approve', true) ? 'approved' : 'pending';
            $borrowing->fine_amount = 0.00;
            $borrowing->save();

            // Update stock status
            $availableStock->update([
                'status' => 'borrowed',
                'is_available' => false
            ]);

            // Update book status if all copies are borrowed
            $remainingCopies = $book->stocks()
                ->where('status', 'available')
                ->where('is_available', true)
                ->count();

            if ($remainingCopies === 0) {
                $book->update(['status' => 'borrowed']);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ödünç verme işlemi başarıyla kaydedildi.',
                    'redirect' => route('staff.borrowings.index')
                ]);
            }

            return redirect()->route('staff.borrowings.index')
                ->with('success', 'Ödünç verme işlemi başarıyla kaydedildi.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Borrowing creation error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ödünç verme işlemi sırasında bir hata oluştu: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Ödünç verme işlemi sırasında bir hata oluştu.');
        }
    }

    public function show(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        $borrowing->load(['user', 'book', 'stock']);
        return view('staff.borrowings.show', compact('borrowing'));
    }

    public function edit(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        return view('staff.borrowings.edit', compact('borrowing'));
    }

    public function update(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        
        $validated = $request->validate([
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'condition' => 'required|in:good,damaged,lost',
            'damage_description' => 'required_if:condition,damaged'
        ]);

        try {
            DB::beginTransaction();

            // Update borrowing with validated data
            $borrowing->update([
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'],
                'condition' => $validated['condition'],
                'returned_at' => now(), // Kitap iade edildiğinde tarihi kaydet
                'status' => 'returned', // Durumu iade edildi olarak güncelle
                'returned_to' => auth()->id() // İade alan personeli kaydet
            ]);

            // Handle different conditions
            if ($validated['condition'] === 'damaged') {
                // Create damage fine (50% of book value)
                $damageAmount = $borrowing->book->price * 0.5;
                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'user_id' => $borrowing->user_id,
                    'book_id' => $borrowing->book_id,
                    'amount' => $damageAmount,
                    'type' => Fine::TYPE_DAMAGE,
                    'description' => $validated['damage_description'] ?? 'Kitap hasarlı olarak işaretlendi',
                    'status' => Fine::STATUS_PENDING,
                    'payment_method' => Fine::PAYMENT_METHOD_CASH,
                    'collected_by' => auth()->id()
                ]);

                // Update stock condition
                $borrowing->stock->update([
                    'condition' => 'poor',
                    'notes' => $validated['damage_description']
                ]);
            } elseif ($validated['condition'] === 'lost') {
                // Create lost book fine (2x book value)
                $lostAmount = $borrowing->book->price * 2;
                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'user_id' => $borrowing->user_id,
                    'book_id' => $borrowing->book_id,
                    'amount' => $lostAmount,
                    'type' => Fine::TYPE_LOST,
                    'description' => 'Kitap kayıp olarak bildirildi',
                    'status' => Fine::STATUS_PENDING,
                    'payment_method' => Fine::PAYMENT_METHOD_CASH,
                    'collected_by' => auth()->id()
                ]);

                // Update stock status
                $borrowing->stock->update([
                    'status' => 'lost',
                    'is_available' => false
                ]);
            } else {
                // İyi durumda iade edilen kitabı stoğa geri al
                $borrowing->stock->update([
                    'status' => 'available',
                    'is_available' => true
                ]);
            }

            // Check for late return
            if (now()->gt($borrowing->due_date)) {
                $daysLate = now()->diffInDays($borrowing->due_date);
                $lateAmount = $daysLate * config('library.late_fee_per_day', 1); // Günlük 1 TL gecikme bedeli

                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'user_id' => $borrowing->user_id,
                    'book_id' => $borrowing->book_id,
                    'amount' => $lateAmount,
                    'type' => Fine::TYPE_LATE,
                    'description' => "Kitap {$daysLate} gün geç iade edildi",
                    'status' => Fine::STATUS_PENDING,
                    'payment_method' => Fine::PAYMENT_METHOD_CASH,
                    'collected_by' => auth()->id()
                ]);
            }

            DB::commit();

            $message = 'Ödünç kaydı başarıyla güncellendi.';
            if ($validated['condition'] !== 'good') {
                $message .= ' Ceza işlemi oluşturuldu.';
            }

            return redirect()
                ->route('staff.borrowings.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Borrowing update error: ' . $e->getMessage());
            return back()->with('error', 'Güncelleme işlemi sırasında bir hata oluştu.');
        }
    }

    public function destroy(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        
        // Check if the borrowing has been returned or is cancelled
        if ($borrowing->status !== 'returned' && $borrowing->status !== 'cancelled') {
            // If book is borrowed and not returned, release the stock first
            if ($borrowing->stock) {
                $borrowing->stock->is_available = true;
                $borrowing->stock->save();
            }
        }
        
        // Delete the borrowing record
        $borrowing->delete();
        
        return redirect()->route('staff.borrowings.index')
            ->with('success', 'Ödünç kaydı başarıyla silindi.');
    }

    /**
     * Search users for borrowing
     */
    public function searchUser(Request $request)
    {
        try {
            $search = $request->get('search');
            
            if (empty($search) || strlen($search) < 2) {
                return response()->json([]);
            }
            
            // Users tablosundan arama yap
            $users = User::where('is_staff', false)
                ->where('is_admin', false)
                ->where(function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('surname', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                })
                ->with(['borrowings' => function($query) {
                    $query->whereNull('returned_at')
                        ->with(['book', 'stock']);
                }])
                ->select('id', 'name', 'surname', 'email', 'phone', 'created_at')
                ->limit(10)
                ->get();

            $formattedUsers = $users->map(function($user) {
                // Aktif ödünç sayısını hesapla
                $activeBookCount = $user->borrowings->count();
                
                // Gecikmiş kitapları kontrol et
                $overdueBooks = $user->borrowings->filter(function($borrowing) {
                    return $borrowing->due_date && now()->gt($borrowing->due_date);
                })->count();
                
                // Kullanıcının ödünç alabileceği maksimum kitap sayısı
                $maxBooks = 4;
                
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' ' . $user->surname . ' (' . $user->email . ')',
                    'name' => $user->name . ' ' . $user->surname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'member_since' => $user->created_at->format('d.m.Y'),
                    'active_books' => $activeBookCount,
                    'overdue_books' => $overdueBooks,
                    'can_borrow' => $activeBookCount < $maxBooks && $overdueBooks === 0,
                    'current_books' => $user->borrowings->map(function($borrowing) {
                        return [
                            'title' => $borrowing->book->title,
                            'due_date' => $borrowing->due_date ? $borrowing->due_date->format('d.m.Y') : '-',
                            'is_overdue' => $borrowing->due_date && now()->gt($borrowing->due_date)
                        ];
                    })
                ];
            });
            
            return response()->json($formattedUsers);
            
        } catch (\Exception $e) {
            \Log::error('User search error: ' . $e->getMessage());
            return response()->json(['error' => 'Arama sırasında bir hata oluştu'], 500);
        }
    }

    public function approve(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        
        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Bu ödünç kaydı zaten onaylanmış veya reddedilmiş.');
        }

        try {
            DB::beginTransaction();

            $borrowing->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $request->user()->id
            ]);

            // Update stock status
            $borrowing->stock->update([
                'status' => 'borrowed',
                'is_available' => false
            ]);

            DB::commit();

            return back()->with('success', 'Ödünç verme işlemi onaylandı.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Borrowing approval error: ' . $e->getMessage());
            return back()->with('error', 'Onaylama işlemi sırasında bir hata oluştu.');
        }
    }

    public function reject(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        
        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Bu ödünç kaydı zaten onaylanmış veya reddedilmiş.');
        }

        try {
            DB::beginTransaction();

            $borrowing->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $request->user()->id
            ]);

            // Make stock available again
            $borrowing->stock->update([
                'status' => 'available',
                'is_available' => true
            ]);

            DB::commit();

            return back()->with('success', 'Ödünç verme talebi reddedildi.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Borrowing rejection error: ' . $e->getMessage());
            return back()->with('error', 'Reddetme işlemi sırasında bir hata oluştu.');
        }
    }

    public function return(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
        
        if ($borrowing->returned_at) {
            return back()->with('error', 'Bu kitap zaten iade edilmiş.');
        }

        // Validate request
        $validated = $request->validate([
            'condition' => 'required|in:good,damaged,lost',
            'damage_description' => 'required_if:condition,damaged',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get related records
            $book = $borrowing->book;
            $stock = $borrowing->stock;

            // Update borrowing record
            $borrowing->update([
                'returned_at' => now(),
                'returned_to' => $request->user()->id,
                'condition' => $validated['condition'],
                'notes' => $validated['notes']
            ]);

            // Handle different return conditions
            switch ($validated['condition']) {
                case 'damaged':
                    // Hasarlı kitap için ceza oluştur (kitap değerinin %50'si)
                    $damageAmount = $book->price * 0.5;
                    Fine::create([
                        'borrowing_id' => $borrowing->id,
                        'user_id' => $borrowing->user_id,
                        'book_id' => $book->id,
                        'amount' => $damageAmount,
                        'type' => Fine::TYPE_DAMAGE,
                        'description' => $validated['damage_description'] ?? 'Kitap hasarlı olarak iade edildi',
                        'status' => Fine::STATUS_PENDING,
                        'payment_method' => Fine::PAYMENT_METHOD_CASH,
                        'collected_by' => auth()->id()
                    ]);
                    
                    // Stok durumunu güncelle
                    $stock->update([
                        'status' => 'damaged',
                        'condition' => 'poor',
                        'notes' => $validated['damage_description'] ?? 'Kitap hasarlı olarak iade edildi'
                    ]);
                    break;

                case 'lost':
                    // Kayıp kitap için ceza oluştur (kitap değerinin 2 katı)
                    $lostAmount = $book->price * 2;
                    Fine::create([
                        'borrowing_id' => $borrowing->id,
                        'user_id' => $borrowing->user_id,
                        'book_id' => $book->id,
                        'amount' => $lostAmount,
                        'type' => Fine::TYPE_LOST,
                        'description' => 'Kitap kayıp olarak bildirildi',
                        'status' => Fine::STATUS_PENDING,
                        'payment_method' => Fine::PAYMENT_METHOD_CASH,
                        'collected_by' => auth()->id()
                    ]);
                    
                    // Stok durumunu güncelle
                    $stock->update([
                        'status' => 'lost',
                        'is_available' => false
                    ]);
                    break;

                default:
                    // İyi durumda iade edilen kitabı stoğa geri al
                    $stock->update([
                        'status' => 'available',
                        'is_available' => true
                    ]);
                    break;
            }

            // Check for late return
            if (now()->gt($borrowing->due_date)) {
                $daysLate = now()->diffInDays($borrowing->due_date);
                $lateAmount = $daysLate * config('library.late_fee_per_day', 1); // Günlük 1 TL varsayılan gecikme bedeli

                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'user_id' => $borrowing->user_id,
                    'book_id' => $book->id,
                    'amount' => $lateAmount,
                    'type' => Fine::TYPE_LATE,
                    'description' => "Kitap {$daysLate} gün geç iade edildi",
                    'status' => Fine::STATUS_PENDING,
                    'payment_method' => Fine::PAYMENT_METHOD_CASH,
                    'collected_by' => auth()->id(),
                    'days_late' => $daysLate
                ]);
            }

            // Update book status based on available copies
            $availableCopies = $book->stocks()
                ->where('status', 'available')
                ->where('is_available', true)
                ->count();

            $book->update([
                'status' => $availableCopies > 0 ? 'available' : 'unavailable'
            ]);

            DB::commit();

            $message = 'Kitap başarıyla iade alındı.';
            if (Fine::where('borrowing_id', $borrowing->id)->exists()) {
                $message .= ' Ceza işlemi oluşturuldu.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Book return error: ' . $e->getMessage());
            return back()->with('error', 'İade işlemi sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function searchBook(Request $request)
    {
        try {
            $search = $request->get('search');
            
            if (empty($search) || strlen($search) < 2) {
                return response()->json([]);
            }

            $books = Book::with(['authors', 'publisher', 'stocks' => function($query) {
                    $query->where('is_available', true)
                        ->where('status', 'available');
                }])
                ->where(function($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('isbn', 'LIKE', "%{$search}%");
                })
                ->whereHas('stocks', function($query) {
                    $query->where('is_available', true)
                        ->where('status', 'available');
                })
                ->get()
                ->map(function($book) {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'isbn' => $book->isbn,
                        'author' => $book->authors->pluck('full_name')->join(', '),
                        'publisher' => $book->publisher ? $book->publisher->name : '-',
                        'available_count' => $book->stocks->count(),
                        'total_count' => $book->stocks->count()
                    ];
                })
                ->filter(function($book) {
                    return $book['available_count'] > 0;
                })
                ->values();
            
            return response()->json($books);
            
        } catch (\Exception $e) {
            \Log::error('Book search error: ' . $e->getMessage());
            return response()->json(['error' => 'Arama sırasında bir hata oluştu'], 500);
        }
    }
} 
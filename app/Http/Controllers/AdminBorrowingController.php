<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Stock;
use App\Models\Borrowing;
use App\Models\Fine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminBorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $borrowings = Borrowing::with(['user', 'book'])->orderBy('created_at', 'desc')->get();
        
        // Sadece normal kullanıcıları al (admin ve personel olmayan)
        $users = User::select('id', 'name', 'email')
                     ->where('is_admin', 0)
                     ->where('is_staff', 0)
                     ->orderBy('name')
                     ->get();
        
        // Stok durumu müsait olan kitapları getir
        $books = Book::whereHas('stocks', function($query) {
            $query->where('is_available', true)
                  ->where('status', 'available');
        })->get();
        
        return view('admin.borrowings', compact('borrowings', 'users', 'books'));
    }

    /**
     * Search books by ISBN or title
     */
    public function searchBooks(Request $request)
    {
        $search = $request->get('search');
        
        $books = Book::with(['authors', 'publisher', 'stocks' => function($query) {
                $query->where('is_available', true)
                      ->where('status', 'available');
            }])
            ->where(function($query) use ($search) {
                $query->where('isbn', 'LIKE', "%{$search}%")
                      ->orWhere('title', 'LIKE', "%{$search}%");
            })
            ->get()
            ->map(function($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                    'author' => $book->authors->pluck('full_name')->join(', '),
                    'publisher' => $book->publisher ? $book->publisher->name : '-',
                    'publication_year' => $book->publication_year,
                    'available_count' => $book->availableStocks->count(),
                    'total_count' => $book->stocks->count()
                ];
            })
            ->filter(function($book) {
                return $book['available_count'] > 0;
            })
            ->values();
            
        return response()->json($books);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Sadece normal kullanıcıları al (admin ve personel olmayan)
        $users = User::where('is_admin', 0)
                     ->where('is_staff', 0)
                     ->get();
                     
        $books = Book::whereHas('stocks', function($query) {
            $query->where('quantity', '>', 0);
        })->get();
        
        return view('admin.borrowings', compact('users', 'books'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verileri doğrula
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Kullanıcı kontrolü
            $user = User::find($validated['user_id']);
            if (!$user) {
                return redirect()->route('admin.borrowings.index')
                                ->with('error', 'Seçilen kullanıcı bulunamadı.');
            }
            
            // Personel ve admin kullanıcıları ödünç alamaz
            if ($user->is_admin || $user->is_staff) {
                return redirect()->route('admin.borrowings.index')
                                ->with('error', 'Personel ve yöneticiler kitap ödünç alamaz.');
            }

            // Kitap kontrolü
            $book = Book::find($validated['book_id']);
            if (!$book) {
                return redirect()->route('admin.borrowings.index')
                                ->with('error', 'Seçilen kitap bulunamadı.');
            }

            // Kullanılabilir stok kontrolü
            $availableStock = $book->stocks()
                ->where('is_available', true)
                ->where('status', 'available')
                ->first();

            if (!$availableStock) {
                return redirect()->route('admin.borrowings.index')
                                ->with('error', 'Bu kitap için uygun stok bulunmamaktadır.');
            }

            // Aktif ödünç sayısı kontrolü (maksimum 4)
            $activeBorrowings = Borrowing::where('user_id', $user->id)
                ->whereNull('returned_at')
                ->count();

            if ($activeBorrowings >= 4) {
                return redirect()->route('admin.borrowings.index')
                                ->with('error', 'Kullanıcı maksimum ödünç alma limitine ulaşmış (4).');
            }

            // Ödünç verme kaydı oluştur
            $borrowing = new Borrowing();
            $borrowing->user_id = $validated['user_id'];
            $borrowing->book_id = $validated['book_id'];
            $borrowing->stock_id = $availableStock->id;
            $borrowing->borrow_date = $validated['borrow_date'];
            $borrowing->due_date = $validated['due_date'];
            $borrowing->notes = $validated['notes'];
            $borrowing->status = 'active';
            $borrowing->save();

            // Stok durumunu güncelle
            $availableStock->status = 'borrowed';
            $availableStock->is_available = false;
            $availableStock->save();

            DB::commit();

            return redirect()->route('admin.borrowings.index')
                            ->with('success', 'Ödünç verme işlemi başarıyla kaydedildi.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Ödünç verme hatası: ' . $e->getMessage());
            return redirect()->route('admin.borrowings.index')
                            ->with('error', 'Ödünç verme işlemi sırasında bir hata oluştu.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['user', 'book']);
        
        // For AJAX requests, return HTML partial
        if (request()->ajax()) {
            $html = view('admin.borrowings.partials.borrowing_details', compact('borrowing'))->render();
            return response()->json(['html' => $html]);
        }
        
        // For normal requests, return full view
        return view('admin.borrowings.show', compact('borrowing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Borrowing $borrowing)
    {
        // Sadece normal kullanıcıları al (admin ve personel olmayan)
        $users = User::where('is_admin', 0)
                     ->where('is_staff', 0)
                     ->get();
                     
        $books = Book::all();
        $borrowing->load(['user', 'book']);
        
        return view('admin.borrowings.edit', compact('borrowing', 'users', 'books'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Borrowing $borrowing)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $borrowing->return_date = $validated['return_date'];
        $borrowing->notes = $validated['notes'] ?? null;
        $borrowing->save();

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Ödünç bilgileri başarıyla güncellendi.');
    }

    /**
     * Process return of a book.
     */
    public function returnBook(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->returned_at) {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Bu kitap zaten iade edilmiş.');
        }

        $validated = $request->validate([
            'returned_at' => 'required|date',
            'condition' => 'required|string|in:good,damaged,lost',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $borrowing->returned_at = $validated['returned_at'];
            $borrowing->condition = $validated['condition'];
            $borrowing->notes = $validated['notes'];
            $borrowing->status = 'returned';
            $borrowing->save();

            // Stok durumunu güncelle
            if ($borrowing->stock) {
                $borrowing->stock->status = 'available';
                $borrowing->stock->is_available = true;
                
                // Eğer kitap hasar görmüş veya kayıpsa durumu güncelle
                if ($validated['condition'] === 'damaged') {
                    $borrowing->stock->status = 'damaged';
                    $borrowing->stock->is_available = false;
                } elseif ($validated['condition'] === 'lost') {
                    $borrowing->stock->status = 'lost';
                    $borrowing->stock->is_available = false;
                }
                
                $borrowing->stock->save();
            }

            // Gecikme cezası kontrolü
            if ($borrowing->isOverdue()) {
                $overdueDays = $borrowing->getOverdueDays();
                $fineAmount = $overdueDays * (session('overdue_fine_per_day') ?? 1.0);

                $fine = new Fine([
                    'user_id' => $borrowing->user_id,
                    'book_id' => $borrowing->book_id,
                    'borrowing_id' => $borrowing->id,
                    'amount' => $fineAmount,
                    'reason' => 'Gecikme cezası - ' . $overdueDays . ' gün',
                    'due_date' => now()->addDays(30),
                    'status' => 'pending'
                ]);
                $fine->save();
            }

            DB::commit();

            return redirect()->route('admin.borrowings.index')
                             ->with('success', 'Kitap başarıyla iade edildi.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('İade işlemi hatası: ' . $e->getMessage());
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'İade işlemi sırasında bir hata oluştu.');
        }
    }

    /**
     * Update the status of a borrowing (approve/reject).
     */
    public function updateStatus(Request $request, Borrowing $borrowing)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_reason' => 'nullable|required_if:status,rejected|string|max:255',
        ]);

        if ($validated['status'] === 'approved') {
            return $this->approve($request, $borrowing);
        } else {
            return $this->reject($request, $borrowing);
        }
    }

    /**
     * Approve a borrowing request.
     */
    private function approve(Request $request, Borrowing $borrowing)
    {
        // Zaten onaylanmış veya reddedilmişse hata döndür
        if ($borrowing->status !== 'pending') {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Bu ödünç verme isteği zaten işlenmiş.');
        }

        // Kitabın stok durumunu kontrol et
        $book = $borrowing->book;
        if ($book->quantity <= 0 || $book->available_quantity <= 0) {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Bu kitap şu anda stokta bulunmuyor.');
        }

        // Ödünç verme durumunu güncelle
        $borrowing->status = 'approved';
        $borrowing->save();

        // Kitap stokunu güncelle
        $book->decrement('quantity');
        $book->decrement('available_quantity');
        
        // Eğer kitabın stok durumu 0 olduysa, durumunu 'borrowed' olarak güncelle
        if ($book->available_quantity <= 0) {
            $book->status = 'borrowed';
            $book->save();
        }

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Ödünç verme isteği başarıyla onaylandı.');
    }

    /**
     * Reject a pending borrowing request.
     */
    private function reject(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'pending') {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Bu ödünç talebi beklemede değil.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $borrowing->status = 'rejected';
        $borrowing->reject_reason = $validated['reason'];
        if ($request->filled('notes')) {
            $borrowing->notes = $validated['notes'];
        }
        $borrowing->save();

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Ödünç talebi başarıyla reddedildi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Borrowing $borrowing)
    {
        // Eğer onaylanmış ve iade edilmemiş bir ödünç ise kitap stokunu artır
        if ($borrowing->status === 'approved' && !$borrowing->returned_at) {
            $borrowing->book->increment('quantity');
        }

        $borrowing->delete();

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Ödünç kaydı başarıyla silindi.');
    }

    /**
     * API: Get borrowings for a specific user
     */
    public function getUserBorrowings(User $user)
    {
        $borrowings = Borrowing::with(['book'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($borrowing) {
                return [
                    'id' => $borrowing->id,
                    'book_id' => $borrowing->book_id,
                    'book_title' => $borrowing->book->title,
                    'borrow_date' => $borrowing->borrow_date ? $borrowing->borrow_date->format('d.m.Y') : '-',
                    'due_date' => $borrowing->due_date ? $borrowing->due_date->format('d.m.Y') : '-',
                    'is_overdue' => $borrowing->isOverdue(),
                    'overdue_days' => $borrowing->getOverdueDays(),
                    'status' => $borrowing->status
                ];
            });
            
        return response()->json($borrowings);
    }
    
    /**
     * API: Get details for a specific borrowing
     */
    public function getBorrowingDetails(Borrowing $borrowing)
    {
        $borrowingData = [
            'id' => $borrowing->id,
            'user_id' => $borrowing->user_id,
            'book_id' => $borrowing->book_id,
            'borrow_date' => $borrowing->borrow_date ? $borrowing->borrow_date->format('d.m.Y') : '-',
            'due_date' => $borrowing->due_date ? $borrowing->due_date->format('d.m.Y') : '-',
            'returned_at' => $borrowing->returned_at ? $borrowing->returned_at->format('d.m.Y') : null,
            'status' => $borrowing->status,
            'is_overdue' => $borrowing->isOverdue(),
            'overdue_days' => $borrowing->getOverdueDays(),
            'potential_fine' => $borrowing->getOverdueDays() * (session('overdue_fine_per_day') ?? 1.0)
        ];
        
        return response()->json($borrowingData);
    }

    /**
     * Search users by name or email
     */
    public function searchUsers(Request $request)
    {
        $search = $request->get('search');
        
        if (empty($search) || strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::where('is_admin', 0)
            ->where('is_staff', 0)
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('surname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->select('id', 'name', 'surname', 'email')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' ' . $user->surname . ' (' . $user->email . ')',
                    'name' => $user->name . ' ' . $user->surname,
                    'email' => $user->email
                ];
            });
            
        return response()->json($users);
    }
} 
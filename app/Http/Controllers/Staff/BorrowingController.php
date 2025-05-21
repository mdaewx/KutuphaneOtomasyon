<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Fine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
        
        $query = Borrowing::with(['user', 'book', 'book.authors']);
        
        // Kitap adı araması
        if ($request->filled('book')) {
            $bookSearch = $request->input('book');
            $query->whereHas('book', function($q) use ($bookSearch) {
                $q->where('title', 'like', "%{$bookSearch}%");
            });
        }
        
        // Kullanıcı adı araması
        if ($request->filled('user')) {
            $userSearch = $request->input('user');
            $query->whereHas('user', function($q) use ($userSearch) {
                $q->where('name', 'like', "%{$userSearch}%");
            });
        }
        
        // Durum filtresi
        if ($request->filled('status')) {
            $status = $request->input('status');
            
            if ($status === 'overdue') {
                $query->where('due_date', '<', now())
                      ->whereNull('returned_at')
                      ->where('status', 'approved');
            } else {
                $query->where('status', $status);
            }
        }
        
        // Tarih aralığı filtresi
        if ($request->filled('date_range')) {
            $dateRange = $request->input('date_range');
            $dates = explode(' - ', $dateRange);
            
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay();
                
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('borrow_date', [$startDate, $endDate])
                      ->orWhereBetween('due_date', [$startDate, $endDate])
                      ->orWhereBetween('returned_at', [$startDate, $endDate]);
                });
            }
        }
        
        // Sıralama
        $borrowings = $query->latest()->paginate(10);
            
        return view('staff.borrowings.index', compact('borrowings'));
    }

    public function create(Request $request)
    {
        $this->checkStaffAccess($request);
        
        try {
            // Tüm kullanıcıları al, hata ayıklama için
            $all_users = \App\Models\User::all();
            
            // Sadece normal kullanıcıları al (admin ve personel olmayan)
            $users = \App\Models\User::where('is_admin', 0)
                                    ->where('is_staff', 0)
                                    ->orderBy('name')
                                    ->get();
            
            // Kullanıcılar boşsa veya yoksa hata mesajı yazdır
            if ($users->isEmpty()) {
                \Log::warning('Kullanıcı kaydı bulunamadı. Toplam kullanıcı sayısı: ' . $all_users->count());
                $users = \App\Models\User::all(); // Alternatif olarak tüm kullanıcıları al
            }
            
            // Stokta en az bir kopya olan tüm kitapları getir
            $books = \App\Models\Book::with(['stocks'])
                ->whereHas('stocks', function($query) {
                    $query->where(function($q) {
                        // Kitap ödünç verilebilir durumda olanları göster (is_available veya status kontrolü)
                        $q->where('is_available', true)
                          ->orWhere('status', 'available');
                    });
                })
                ->orderBy('title')
                ->get();
                
            // Her kitap için kullanılabilir stok sayısını hesapla ve ekstra bir özellik olarak ekle
            foreach ($books as $book) {
                $book->available_copies = $book->stocks()
                    ->where(function($q) {
                        $q->where('is_available', true)
                          ->orWhere('status', 'available');
                    })
                    ->whereDoesntHave('borrowings', function($query) {
                        $query->whereNull('returned_at');
                    })
                    ->count();
            }
            
            return view('staff.borrowings.create', compact('users', 'books', 'all_users'));
        } catch (\Exception $e) {
            \Log::error('Kullanıcı bilgileri alınırken hata: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ödünç verme formu hazırlanırken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        }
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
            'auto_approve' => 'nullable|boolean'
        ]);

        // Kitabın uygun kopyası var mı kontrol et
        $book = Book::findOrFail($validatedData['book_id']);
        $availableStock = $book->stocks()
            ->where('status', 'available')
            ->whereDoesntHave('borrowings', function($query) {
                $query->whereNull('returned_at');
            })
            ->first();

        if (!$availableStock) {
            return back()->withErrors(['book_id' => 'Bu kitabın uygun kopyası kalmamıştır.']);
        }

        // Ödünç verme kaydı oluştur
        $borrowing = new Borrowing();
        $borrowing->user_id = $validatedData['user_id'];
        $borrowing->book_id = $validatedData['book_id'];
        $borrowing->stock_id = $availableStock->id;
        $borrowing->borrow_date = $validatedData['borrow_date'];
        $borrowing->due_date = $validatedData['due_date'];
        $borrowing->notes = $validatedData['notes'];
        $borrowing->status = $request->input('auto_approve', true) ? 'approved' : 'pending';
        $borrowing->save();

        // Stok durumunu güncelle
        $availableStock->status = 'borrowed';
        $availableStock->save();

        return redirect()->route('staff.borrowings.index')
            ->with('success', 'Ödünç verme işlemi başarıyla kaydedildi.');
    }

    public function show(Request $request, Borrowing $borrowing)
    {
        $this->checkStaffAccess($request);
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
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'returned_at' => 'nullable|date',
            'status' => 'required|in:pending,approved,returned,cancelled',
            'notes' => 'nullable|string',
            'payment_method' => 'required_if:status,returned',
            'payment_reference' => 'required_if:status,returned',
            'damage_level' => 'nullable|in:minor,moderate,severe',
            'damage_description' => 'required_if:damage_level,minor,moderate,severe',
            'damage_photos.*' => 'nullable|image|max:2048' // Her fotoğraf max 2MB
        ]);

        // Eğer kitap iade ediliyorsa
        if ($validated['status'] === 'returned' && $validated['returned_at']) {
            $returnedAt = Carbon::parse($validated['returned_at']);
            $dueDate = Carbon::parse($validated['due_date']);
            $fines = [];

            // 1. Gecikme Cezası Kontrolü
            if ($returnedAt->gt($dueDate)) {
                $lateDays = $returnedAt->diffInDays($dueDate);
                $fineAmount = $lateDays * 1; // Günlük 1 TL ceza

                // Gecikme cezası oluştur
                $fines[] = Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'amount' => $fineAmount,
                    'payment_status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'is_damage_fine' => false
                ]);
            }

            // 2. Hasar Cezası Kontrolü
            if ($request->filled('damage_level')) {
                // Hasar seviyesine göre ceza oranı
                $damageRates = [
                    'minor' => 0.25, // %25
                    'moderate' => 0.50, // %50
                    'severe' => 1.00 // %100
                ];

                // Kitabın değeri (örnek olarak 50 TL)
                $bookValue = $borrowing->book->price ?? 50;
                
                // Hasar cezası hesapla
                $damageAmount = $bookValue * $damageRates[$request->damage_level];

                // Hasar fotoğraflarını kaydet
                $photosPaths = [];
                if ($request->hasFile('damage_photos')) {
                    foreach ($request->file('damage_photos') as $photo) {
                        $path = $photo->store('damage-photos', 'public');
                        $photosPaths[] = $path;
                    }
                }

                // Hasar cezası oluştur
                $fines[] = Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'amount' => $damageAmount,
                    'payment_status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'is_damage_fine' => true,
                    'damage_level' => $request->damage_level,
                    'damage_description' => $request->damage_description,
                    'damage_photos' => $photosPaths
                ]);
            }

            // Ceza ödemeleri yapıldıysa
            if ($request->payment_method && $request->payment_reference) {
                foreach ($fines as $fine) {
                    $fine->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'approved_at' => now(),
                        'approved_by' => auth()->id()
                    ]);
                }
            }

            // Kitabı stoğa geri al
            $borrowing->stock->update(['status' => 'available']);
        }

        $borrowing->update($validated);

        return redirect()
            ->route('staff.borrowings.index')
            ->with('success', 'Ödünç kaydı başarıyla güncellendi.');
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

    public function searchUser(Request $request)
    {
        $this->checkStaffAccess($request);
        $term = $request->input('term');
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }
        
        $users = User::where(function($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%");
        })
        ->where('is_admin', 0)
        ->where('is_staff', 0)
        ->select('id', 'name', 'email')
        ->limit(10)
        ->get();
        
        return response()->json($users);
    }
} 
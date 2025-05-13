<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        
        $query = Borrowing::with(['user', 'book', 'stock']);
        
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
        
        // Validasyon
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'notes' => 'nullable|string',
            'auto_approve' => 'nullable|boolean'
        ]);
        
        // Kitap için mevcut bir stok var mı kontrol et
        $book = \App\Models\Book::find($validated['book_id']);
        if (!$book->isAvailable()) {
            return redirect()->back()->with('error', 'Bu kitap şu anda stokta mevcut değil.')->withInput();
        }
        
        // Kitabın tüm kullanılabilir stoklarını al
        $availableStocks = $book->stocks()
            ->where(function($q) {
                $q->where('is_available', true)
                  ->orWhere('status', 'available');
            })
            ->whereDoesntHave('borrowings', function($query) {
                $query->whereNull('returned_at');
            })
            ->get();
        
        if ($availableStocks->isEmpty()) {
            return redirect()->back()->with('error', 'Bu kitap için uygun stok bulunamadı.')->withInput();
        }
        
        // Kitabın ilk uygun stok kaydını seç (burada çoklu stok seçimi için form geliştirilebilir)
        $stock = $availableStocks->first();
        
        // Ödünç kaydı oluştur
        $borrowing = new \App\Models\Borrowing();
        $borrowing->user_id = $validated['user_id'];
        $borrowing->book_id = $validated['book_id'];
        $borrowing->stock_id = $stock->id;
        $borrowing->borrow_date = $validated['borrow_date'];
        $borrowing->due_date = $validated['due_date'];
        
        // Otomatik onay kontrolü
        $borrowing->status = $request->has('auto_approve') ? 'approved' : 'pending';
        
        $borrowing->notes = $validated['notes'] ?? null;
        $borrowing->save();
        
        // Kitap durumunu güncelle
        if ($borrowing->status === 'approved') {
            $stock->is_available = false;
            $stock->save();
        }
        
        return redirect()->route('staff.borrowings.index')->with('success', 'Ödünç kaydı başarıyla oluşturuldu.');
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
            'status' => 'required|in:pending,approved,returned,cancelled',
            'returned_at' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'fine_amount' => 'nullable|numeric|min:0'
        ]);
        
        // Handle returned status
        if ($validated['status'] === 'returned' && !$borrowing->returned_at) {
            $validated['returned_at'] = $validated['returned_at'] ?? now();
            
            // Update the stock availability
            if ($borrowing->stock) {
                $borrowing->stock->is_available = true;
                $borrowing->stock->save();
            }
            
            // Calculate fine if book is returned late
            if ($validated['returned_at'] > $borrowing->due_date) {
                $dueDate = \Carbon\Carbon::parse($borrowing->due_date);
                $returnedDate = \Carbon\Carbon::parse($validated['returned_at']);
                $daysLate = $dueDate->diffInDays($returnedDate);
                
                // Calculate fine (adjust the daily fine amount as needed)
                $dailyFine = 1.00; // 1 TL per day
                $fine = $daysLate * $dailyFine;
                
                // Update fine amount if not explicitly set
                if (!isset($validated['fine_amount'])) {
                    $validated['fine_amount'] = $fine;
                }
            }
        }
        
        // Update borrowing
        $borrowing->update($validated);
        
        return redirect()->route('staff.borrowings.index')
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
} 
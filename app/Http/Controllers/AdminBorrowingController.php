<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminBorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $borrowings = Borrowing::with(['user', 'book'])->latest()->get();
        
        // Sadece normal kullanıcıları al (admin ve personel olmayan)
        $users = User::select('id', 'name', 'email')
                     ->where('is_admin', 0)
                     ->where('is_staff', 0)
                     ->orderBy('name')
                     ->get();
        
        // Stok durumu 0'dan büyük olan kitapları getir
        $books = Book::where('quantity', '>', 0)->get();
        
        return view('admin.borrowings', compact('borrowings', 'users', 'books'));
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
                     
        $books = Book::where('quantity', '>', 0)->get();
        
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

        $book = Book::findOrFail($validated['book_id']);
        
        if ($book->quantity <= 0 || $book->available_quantity <= 0) {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Bu kitap şu anda stokta bulunmuyor.');
        }

        $borrowing = new Borrowing();
        $borrowing->user_id = $validated['user_id'];
        $borrowing->book_id = $validated['book_id'];
        $borrowing->borrow_date = $validated['borrow_date'];
        $borrowing->due_date = $validated['due_date'];
        $borrowing->notes = $validated['notes'] ?? null;
        $borrowing->status = $request->has('auto_approve') ? 'approved' : 'pending';
        $borrowing->save();

        // Eğer otomatik onaylandıysa kitap stokunu azalt
        if ($borrowing->status === 'approved') {
            $book->decrement('quantity');
            $book->decrement('available_quantity');
            
            // Eğer kitabın stok durumu 0 olduysa, durumunu 'borrowed' olarak güncelle
            if ($book->available_quantity <= 0) {
                $book->status = 'borrowed';
                $book->save();
            }
        }

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Ödünç verme işlemi başarıyla oluşturuldu.');
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

        $borrowing->returned_at = $validated['returned_at'];
        $borrowing->condition = $validated['condition'];
        $borrowing->notes = $validated['notes'] ?? $borrowing->notes;
        $borrowing->status = 'returned';
        $borrowing->save();

        // Kitabın durumunu ve stok bilgilerini güncelle
        $book = $borrowing->book;
        
        // Kitap durumu iyi veya hasarlı ise stok artır, kayıpsa stok artırma
        if ($validated['condition'] !== 'lost') {
            $book->increment('quantity');
            $book->increment('available_quantity');
            
            // Kitabın durumunu güncelle
            if ($book->status === 'borrowed' && $book->available_quantity > 0) {
                $book->status = 'available';
                $book->save();
            }
        }

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Kitap başarıyla iade alındı.');
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
} 
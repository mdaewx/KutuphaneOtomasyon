<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FineController extends Controller
{
    /**
     * Tüm cezaları görüntüle
     */
    public function index()
    {
        // Tüm cezaları ve gecikmiş ödünçleri al
        $fines = Fine::with(['user', 'book'])->orderBy('created_at', 'asc')->get();
        
        // Gecikmiş ama henüz iade edilmemiş kitaplar
        $overdueBorrowings = Borrowing::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->get();
            
        // Her gecikmiş ödünç için potansiyel ceza hesapla
        foreach ($overdueBorrowings as $borrowing) {
            $borrowing->potential_fine = $borrowing->calculateFine($this->getDailyFineRate());
            $borrowing->overdue_days = $borrowing->getOverdueDays();
            // Ensure borrow_date property exists
            $borrowing->borrowed_at = $borrowing->borrow_date ?? $borrowing->created_at;
        }
        
        // Kullanıcı bazında toplam ceza tutarları
        $userFines = DB::table('fines')
            ->select('user_id', DB::raw('SUM(amount) as total_fine'))
            ->groupBy('user_id')
            ->get();
        
        // Günlük ceza tutarı
        $dailyFineRate = $this->getDailyFineRate();
        
        // Ceza eklemek için kullanıcı listesi
        $users = User::where('is_admin', 0)
               ->where('is_staff', 0)
               ->orderBy('name')
               ->get();
        
        // Ceza eklemek için kitap listesi
        $books = Book::orderBy('title')->get();
        
        return view('admin.fines.index', compact(
            'fines', 
            'overdueBorrowings', 
            'userFines', 
            'dailyFineRate',
            'users',
            'books'
        ));
    }
    
    /**
     * Ödenmiş olarak işaretle
     */
    public function markAsPaid(Request $request, $id)
    {
        $fine = Fine::findOrFail($id);
        
        // Ödeme bilgilerini güncelle
        $fine->paid = true;
        $fine->paid_at = $request->has('payment_date') ? 
            Carbon::createFromFormat('Y-m-d', $request->payment_date) : now();
            
        // Ek ödeme bilgilerini kaydet
        $fine->payment_method = $request->payment_method ?? 'cash';
        $fine->payment_notes = $request->payment_notes;
        $fine->collected_by = auth()->user()->id; // Tahsil eden personel
        
        $fine->save();
        
        return redirect()->route('admin.fines.index')
            ->with('success', 'Ceza ödenmiş olarak işaretlendi.');
    }
    
    /**
     * Ceza oranını güncelle
     */
    public function updateFineRate(Request $request)
    {
        $request->validate([
            'daily_fine_rate' => 'required|numeric|min:0|max:100'
        ]);
        
        // Ayarlar tablosunu kullanarak güncelleme yap
        // Setting::updateOrCreate(['key' => 'overdue_fine_per_day'], ['value' => $request->daily_fine_rate]);
        
        // Alternatif olarak, geçici bir çözüm olarak session'da saklayabiliriz
        session(['overdue_fine_per_day' => $request->daily_fine_rate]);
        
        return redirect()->route('admin.fines.index')
            ->with('success', 'Günlük ceza tutarı güncellendi: ' . $request->daily_fine_rate . ' TL');
    }
    
    /**
     * Gecikmiş bir kitabı iade et ve ceza oluştur
     */
    public function returnOverdueBook(Request $request, $borrowingId)
    {
        $borrowing = Borrowing::with(['user', 'book'])->findOrFail($borrowingId);
        
        if (!$borrowing->isOverdue()) {
            return redirect()->route('admin.fines.index')
                ->with('error', 'Bu kitap gecikmiş değil.');
        }
        
        // Kitabı iade et ve ceza tutarını hesapla
        $borrowing->returned_at = now();
        $borrowing->status = 'returned';
        $borrowing->condition = $request->input('condition', 'good');
        
        $overdueDays = $borrowing->getOverdueDays();
        $fineAmount = $overdueDays * $this->getDailyFineRate();
        $borrowing->amount = $fineAmount;
        $borrowing->save();
        
        // Fine tablosuna kayıt ekle
        Fine::create([
            'user_id' => $borrowing->user_id,
            'book_id' => $borrowing->book_id,
            'borrowing_id' => $borrowing->id,
            'days_late' => $overdueDays,
            'amount' => $fineAmount,
            'paid' => false
        ]);
        
        return redirect()->route('admin.fines.index')
            ->with('success', 'Kitap iade edildi ve ' . $fineAmount . ' TL ceza uygulandı.');
    }
    
    /**
     * Ceza detayını görüntüle
     */
    public function show($id)
    {
        $fine = Fine::with(['user', 'book'])->findOrFail($id);
        return view('admin.fines.show', compact('fine'));
    }
    
    /**
     * Cezayı sil
     */
    public function destroy($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->delete();
        
        return redirect()->route('admin.fines.index')
            ->with('success', 'Ceza silindi.');
    }
    
    /**
     * Cezayı affet (tutarı sıfırla)
     */
    public function forgive($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->amount = 0;
        $fine->paid = true;
        $fine->paid_at = now();
        $fine->save();
        
        return redirect()->route('admin.fines.index')
            ->with('success', 'Ceza affedildi.');
    }
    
    /**
     * Günlük ceza tutarını al
     */
    private function getDailyFineRate()
    {
        // Ayarlar tablosundan al
        // $setting = Setting::where('key', 'overdue_fine_per_day')->first();
        // return $setting ? (float) $setting->value : 1.0;
        
        // Alternatif olarak, geçici bir çözüm olarak session'dan al
        return session('overdue_fine_per_day', 1.0);
    }
    
    /**
     * Yeni ceza oluştur
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        
        $fine = new Fine();
        $fine->user_id = $request->user_id;
        $fine->book_id = $request->book_id;
        $fine->days_late = 0; // Manuel girilen cezalar için varsayılan 0
        $fine->amount = $request->amount;
        $fine->paid = $request->has('paid');
        
        if ($request->has('paid')) {
            $fine->paid_at = now();
            $fine->payment_method = 'cash'; // Varsayılan ödeme yöntemi
            $fine->collected_by = auth()->id();
        }
        
        // Notları ekleme
        if ($request->filled('notes')) {
            $fine->payment_notes = $request->notes;
        }
        
        $fine->save();
        
        return redirect()->route('admin.fines.index')
            ->with('success', 'Ceza başarıyla eklendi.');
    }
    
    /**
     * Seçilen üyenin ödünç aldığı kitapları getir (JSON)
     */
    public function getUserBooks($userId)
    {
        $books = Borrowing::with('book')
            ->where('user_id', $userId)
            ->whereNull('returned_at')
            ->get()
            ->map(function($borrowing) {
                return [
                    'id' => $borrowing->book->id,
                    'title' => $borrowing->book->title
                ];
            });
        return response()->json($books);
    }
} 
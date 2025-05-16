<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    /**
     * Tüm cezaları görüntüle
     */
    public function index()
    {
        // Tüm cezaları ve gecikmiş ödünçleri al
        $fines = Fine::with(['user', 'book'])->orderBy('created_at', 'desc')->get();
        
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
            ->select('user_id', DB::raw('SUM(fine_amount) as total_fine'))
            ->groupBy('user_id')
            ->get();
        
        // Günlük ceza tutarı
        $dailyFineRate = $this->getDailyFineRate();
        
        return view('admin.fines.index', compact('fines', 'overdueBorrowings', 'userFines', 'dailyFineRate'));
    }
    
    /**
     * Ödenmiş olarak işaretle
     */
    public function markAsPaid($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->paid = true;
        $fine->paid_at = now();
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
        $borrowing->fine_amount = $fineAmount;
        $borrowing->save();
        
        // Fine tablosuna kayıt ekle
        Fine::create([
            'user_id' => $borrowing->user_id,
            'book_id' => $borrowing->book_id,
            'borrowing_id' => $borrowing->id,
            'days_late' => $overdueDays,
            'fine_amount' => $fineAmount,
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
        $fine->fine_amount = 0;
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
} 
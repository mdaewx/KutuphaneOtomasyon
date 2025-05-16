<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    /**
     * Display a listing of fines.
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
        }
        
        // Günlük ceza tutarı
        $dailyFineRate = $this->getDailyFineRate();
        
        return view('staff.fines.index', compact('fines', 'overdueBorrowings', 'dailyFineRate'));
    }

    /**
     * Display detailed information about a fine.
     */
    public function show($id)
    {
        $fine = Fine::with(['user', 'book'])->findOrFail($id);
        return view('staff.fines.show', compact('fine'));
    }

    /**
     * Process an overdue book return and create a fine.
     */
    public function returnOverdueBook(Request $request, $borrowingId)
    {
        $borrowing = Borrowing::with(['user', 'book'])->findOrFail($borrowingId);
        
        if (!$borrowing->isOverdue()) {
            return redirect()->route('staff.fines.index')
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
        
        return redirect()->route('staff.fines.index')
            ->with('success', 'Kitap iade edildi ve ' . $fineAmount . ' TL ceza uygulandı.');
    }

    /**
     * Mark a fine as paid.
     */
    public function markAsPaid($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->paid = true;
        $fine->paid_at = now();
        $fine->save();
        
        return redirect()->route('staff.fines.index')
            ->with('success', 'Ceza ödenmiş olarak işaretlendi.');
    }

    /**
     * Forgive a fine.
     */
    public function forgive($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->fine_amount = 0;
        $fine->paid = true;
        $fine->paid_at = now();
        $fine->save();
        
        return redirect()->route('staff.fines.index')
            ->with('success', 'Ceza affedildi.');
    }

    /**
     * Get the daily fine rate.
     */
    private function getDailyFineRate()
    {
        // Get from session for now (eventually this should be in settings table)
        return session('overdue_fine_per_day', 1.0);
    }
}

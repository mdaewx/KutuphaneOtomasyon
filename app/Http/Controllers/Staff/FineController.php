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
        $fines = Fine::with(['user', 'book'])
            ->latest()
            ->get();

        return view('staff.fines.index', compact('fines'));
    }

    /**
     * Display detailed information about a fine.
     */
    public function show(Fine $fine)
    {
        $fine->load(['user', 'book', 'book.authors']);
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
        
        return redirect()->route('staff.fines.index')
            ->with('success', 'Kitap iade edildi ve ' . $fineAmount . ' TL ceza uygulandı.');
    }

    /**
     * Mark a fine as paid.
     */
    public function markAsPaid(Fine $fine)
    {
        if ($fine->status !== 'pending') {
            return back()->with('error', 'Bu ceza zaten ödenmiş veya iptal edilmiş.');
        }

        $fine->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return back()->with('success', 'Ceza ödenmiş olarak işaretlendi.');
    }

    /**
     * Forgive a fine.
     */
    public function forgive(Fine $fine)
    {
        if ($fine->status !== 'pending') {
            return back()->with('error', 'Bu ceza zaten ödenmiş veya iptal edilmiş.');
        }

        $fine->update([
            'status' => 'cancelled',
            'amount' => 0,
            'paid_at' => now()
        ]);

        return back()->with('success', 'Ceza affedildi.');
    }

    /**
     * Get the daily fine rate.
     */
    private function getDailyFineRate()
    {
        // Get from session for now (eventually this should be in settings table)
        return session('overdue_fine_per_day', 1.0);
    }

    public function approve(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer',
            'payment_reference' => 'required|string',
            'admin_notes' => 'nullable|string'
        ]);

        $fine->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'],
            'admin_notes' => $validated['admin_notes'],
            'paid_at' => now(),
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        return redirect()
            ->route('staff.fines.index')
            ->with('success', 'Ceza ödemesi başarıyla onaylandı.');
    }

    public function cancel(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string'
        ]);

        $fine->update([
            'payment_status' => 'cancelled',
            'admin_notes' => $validated['admin_notes'],
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        return redirect()
            ->route('staff.fines.index')
            ->with('success', 'Ceza kaydı iptal edildi.');
    }
}

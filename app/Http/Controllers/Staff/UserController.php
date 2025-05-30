<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Borrowing;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private function checkStaffAccess(Request $request)
    {
        if (!$request->user()->is_staff) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $users = User::where('is_admin', 0)
            ->where('is_staff', 0)
            ->withCount(['borrowings', 'activeBorrowings'])
            ->orderBy('name')
            ->paginate(15);
            
        return view('staff.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user)
    {
        $this->checkStaffAccess($request);
        
        $user->load([
            'borrowings' => function($query) {
                $query->latest();
            },
            'borrowings.book',
            'borrowings.fines'
        ]);
        
        // Aktif ödünç alınan kitaplar
        $activeBorrowings = $user->borrowings()
            ->whereNull('returned_at')
            ->with(['book'])
            ->get();
            
        // Geçmiş ödünç kayıtları
        $borrowingHistory = $user->borrowings()
            ->whereNotNull('returned_at')
            ->with(['book'])
            ->latest()
            ->get();
            
        // Aktif cezalar
        $pendingFines = Fine::where('user_id', $user->id)
            ->whereNull('paid_at')
            ->with(['book'])
            ->get();
            
        // Toplam istatistikler
        $stats = [
            'total_borrowings' => $user->borrowings()->count(),
            'active_borrowings' => $activeBorrowings->count(),
            'total_fines' => $user->fines()->sum('amount'),
            'pending_fines' => $pendingFines->sum('amount')
        ];
        
        return view('staff.users.show', compact(
            'user',
            'activeBorrowings',
            'borrowingHistory',
            'pendingFines',
            'stats'
        ));
    }
} 
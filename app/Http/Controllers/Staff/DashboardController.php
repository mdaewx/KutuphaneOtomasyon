<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private function checkStaffAccess(Request $request)
    {
        if (!$request->user()->isStaff()) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
    }

    public function index(Request $request)
    {
        $this->checkStaffAccess($request);

        // İstatistikler
        $totalBooks = Book::count();
        $totalBorrowings = Borrowing::count();
        $activeBorrowings = Borrowing::whereNull('returned_at')->count();
        $totalUsers = User::count();

        // Son ödünç alınan kitaplar
        $recentBorrowings = Borrowing::with(['user', 'book'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Gecikmiş kitaplar
        $overdueBorrowings = Borrowing::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', now())
            ->get();

        return view('staff.dashboard', compact(
            'totalBooks',
            'totalBorrowings',
            'activeBorrowings',
            'totalUsers',
            'recentBorrowings',
            'overdueBorrowings'
        ));
    }
} 
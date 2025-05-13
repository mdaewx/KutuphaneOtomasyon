<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Borrowing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    /**
     * Raporlar ana sayfası
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * En popüler kitaplar raporu
     */
    public function popularBooks()
    {
        $books = Book::withCount(['borrowings'])
            ->having('borrowings_count', '>', 0)
            ->orderBy('borrowings_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.popular-books', compact('books'));
    }

    /**
     * En aktif kullanıcılar raporu
     */
    public function activeUsers()
    {
        $users = User::where('is_admin', 0)
            ->withCount(['borrowings' => function ($query) {
                $query->whereYear('created_at', now()->year);
            }])
            ->having('borrowings_count', '>', 0)
            ->orderBy('borrowings_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.active-users', compact('users'));
    }

    /**
     * Gecikmiş teslimler raporu
     */
    public function overdue()
    {
        $overdueBooks = Borrowing::with(['user', 'book'])
            ->where('is_returned', 0)
            ->where('return_date', '<', Carbon::now())
            ->orderBy('return_date', 'asc')
            ->get();

        return view('admin.reports.overdue', compact('overdueBooks'));
    }
    
    /**
     * Aylık kitap ödünç işlemleri raporu
     */
    public function monthlyStats(Request $request)
    {
        $year = $request->year ?? now()->year;
        $months = [];
        $borrowings = [];
        $returns = [];
        
        // Her ay için veri
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::createFromDate($year, $i, 1)->formatLocalized('%B');
            
            $monthlyBorrowings = Borrowing::whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->count();
                
            $monthlyReturns = Borrowing::whereYear('returned_at', $year)
                ->whereMonth('returned_at', $i)
                ->where('is_returned', 1)
                ->count();
                
            $borrowings[] = $monthlyBorrowings;
            $returns[] = $monthlyReturns;
        }
        
        $years = range(now()->year - 5, now()->year);
        
        return view('admin.reports.monthly-stats', compact('months', 'borrowings', 'returns', 'year', 'years'));
    }
    
    /**
     * Kategori istatistikleri raporu
     */
    public function categories()
    {
        $categories = \App\Models\Category::withCount('books')
            ->orderBy('books_count', 'desc')
            ->get();
            
        return view('admin.reports.categories', compact('categories'));
    }
} 
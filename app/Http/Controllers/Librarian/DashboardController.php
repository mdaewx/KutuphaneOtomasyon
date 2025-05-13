<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\User;
use App\Models\Borrowing;
use App\Models\Shelf;
use App\Models\Stock;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Bugün yapılan ödünç işlemleri
        $todayBorrowings = Borrowing::whereDate('created_at', Carbon::today())->count();
        
        // İade bekleyen ödünçler
        $pendingReturns = Borrowing::whereNull('returned_at')->count();
        
        // Gecikmiş kitaplar
        $overdueBooks = Borrowing::whereNull('returned_at')
            ->where('due_date', '<', Carbon::today())
            ->count();
            
        // Müsait raf sayısı
        $shelves = Shelf::all();
        $availableShelves = 0;
        
        foreach ($shelves as $shelf) {
            if (!$shelf->isFull()) {
                $availableShelves++;
            }
        }
        
        // Bugünün etkinlikleri
        $todayActivities = Borrowing::with(['user', 'book'])
            ->whereDate('created_at', Carbon::today())
            ->orWhereDate('returned_at', Carbon::today())
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($borrowing) {
                return (object) [
                    'type' => $borrowing->returned_at && Carbon::parse($borrowing->returned_at)->isToday() 
                        ? 'return' 
                        : 'borrow',
                    'book' => $borrowing->book,
                    'user' => $borrowing->user,
                    'created_at' => $borrowing->returned_at && Carbon::parse($borrowing->returned_at)->isToday()
                        ? Carbon::parse($borrowing->returned_at)
                        : $borrowing->created_at
                ];
            });
            
        // Gecikmiş ödünçler listesi
        $overdueBorrowings = Borrowing::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', Carbon::today())
            ->orderBy('due_date')
            ->take(10)
            ->get();
        
        return view('librarian.dashboard', compact(
            'todayBorrowings', 
            'pendingReturns', 
            'overdueBooks', 
            'availableShelves',
            'todayActivities',
            'overdueBorrowings'
        ));
    }
} 
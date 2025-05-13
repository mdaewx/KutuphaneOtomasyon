<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index()
    {
        // Ana sayfada sadece veritabanına eklenmiş kitapları göster
        $all_books = \App\Models\Book::where('id', '>', 0)->get();
        
        // Veritabanında kitap varsa onları göster, yoksa boş koleksiyon döndür
        if ($all_books->count() > 0) {
            $featured_books = $all_books->random(min(4, $all_books->count()));
            
            $latest_books = \App\Models\Book::latest()->take(min(8, $all_books->count()))->get();
            
            // Popüler kitapları, ödünç alınma sayısına göre sırala
            $popular_books = \App\Models\Book::withCount(['borrowings' => function($query) {
                $query->whereNotNull('returned_at');
            }])
            ->orderByDesc('borrowings_count')
            ->take(min(8, $all_books->count()))
            ->get();
        } else {
            $featured_books = collect([]);
            $latest_books = collect([]);
            $popular_books = collect([]);
        }

        // Kategorileri ana sayfada göstermek için
        $categories = \App\Models\Category::withCount('books')->orderBy('name')->get();

        return view('home', compact('featured_books', 'latest_books', 'popular_books', 'categories'));
    }
}

<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    private function checkStaffAccess(Request $request)
    {
        if (!$request->user()->is_staff) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
    }

    public function index(Request $request)
    {
        $this->checkStaffAccess($request);

        $books = Book::with(['authors', 'stocks', 'publisher', 'category'])
            ->withCount('stocks')
            ->paginate(10);

        // İstatistikler
        $totalBooks = Book::count();
        $availableBooks = Book::whereHas('stocks', function($query) {
            $query->where('is_available', true);
        })->count();
        $borrowedBooks = Borrowing::whereNull('returned_at')->count();
        $overdueBooks = Borrowing::whereNull('returned_at')
            ->where('due_date', '<', now())
            ->count();

        return view('staff.books.index', compact(
            'books',
            'totalBooks',
            'availableBooks',
            'borrowedBooks',
            'overdueBooks'
        ));
    }

    public function create(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $authors = \App\Models\Author::orderBy('name')->get();
        $publishers = \App\Models\Publisher::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('staff.books.create', compact('authors', 'publishers', 'categories'));
    }

    public function store(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'publisher_id' => 'required|exists:publishers,id',
            'category_id' => 'required|exists:categories,id',
            'publication_year' => 'required|integer|min:1000|max:' . (date('Y') + 1),
            'language' => 'required|string|max:50',
            'page_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'author_ids' => 'required|array',
            'author_ids.*' => 'exists:authors,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Debug için yayınevi bilgisini logla
            \Log::info('Kitap oluşturma - Yayınevi bilgisi:', [
                'publisher_id' => $validatedData['publisher_id'],
                'publisher_exists' => \App\Models\Publisher::find($validatedData['publisher_id']) ? 'Evet' : 'Hayır'
            ]);

            $book = Book::create($validatedData);
            
            // Yayınevi ilişkisini kontrol et ve logla
            \Log::info('Kitap oluşturuldu - Yayınevi kontrolü:', [
                'book_id' => $book->id,
                'publisher_id_in_book' => $book->publisher_id,
                'publisher_relation_works' => $book->publisher ? 'Evet' : 'Hayır'
            ]);

            // Yazarları ekle
            $book->authors()->attach($validatedData['author_ids']);

            // Stok kayıtları oluştur
            for ($i = 0; $i < $validatedData['quantity']; $i++) {
                $book->stocks()->create([
                    'status' => 'available',
                    'condition' => 'new',
                    'is_available' => true
                ]);
            }

            DB::commit();

            return redirect()->route('staff.books.index')
                ->with('success', 'Kitap başarıyla eklendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Kitap ekleme hatası:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Kitap eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Book $book)
    {
        $this->checkStaffAccess($request);
        $book->load(['authors', 'category', 'publisher', 'activeBorrowings.user', 'stocks']);
        return view('staff.books.show', compact('book'));
    }

    public function edit(Request $request, Book $book)
    {
        $this->checkStaffAccess($request);
        
        $authors = \App\Models\Author::orderBy('name')->get();
        $publishers = \App\Models\Publisher::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('staff.books.edit', compact('book', 'authors', 'publishers', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $this->checkStaffAccess($request);
        
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,id',
            'isbn' => 'required|string|max:13|unique:books,isbn,' . $book->id,
            'page_count' => 'required|integer|min:1',
            'language' => 'required|string|max:50',
            'publication_year' => 'required|integer|min:1800|max:' . date('Y'),
            'description' => 'nullable|string',
            'author_id' => 'required|exists:authors,id',
        ]);
        
        // Update book
        $book->update([
            'title' => $validatedData['title'],
            'category_id' => $validatedData['category_id'],
            'publisher_id' => $validatedData['publisher_id'],
            'isbn' => $validatedData['isbn'],
            'page_count' => $validatedData['page_count'],
            'language' => $validatedData['language'],
            'publication_year' => $validatedData['publication_year'],
            'description' => $validatedData['description'],
        ]);
        
        // Sync author (removes old associations and adds new one)
        $book->authors()->sync([$validatedData['author_id']]);
        
        return redirect()->route('staff.books.index')
            ->with('success', 'Kitap başarıyla güncellendi.');
    }

    public function destroy(Request $request, Book $book)
    {
        $this->checkStaffAccess($request);
        // Implementation
    }
    
    public function search(Request $request, $isbn)
    {
        $this->checkStaffAccess($request);
        
        try {
            // Debug log ekleyelim
            \Log::info('Staff book search started', ['isbn' => $isbn]);
            
            $book = Book::with(['authors', 'publisher', 'category', 'stocks'])
                ->where('isbn', $isbn)
                ->first();
            
            if (!$book) {
                \Log::warning('Book not found', ['isbn' => $isbn]);
                return response()->json([
                    'error' => 'Kitap bulunamadı.',
                    'isbn' => $isbn
                ], 404);
            }

            // Tüm stok kayıtlarını kontrol et
            $stockStatus = $book->stocks()
                ->select('id', 'status', 'is_available')
                ->get();
                
            \Log::info('Stock status', ['book_id' => $book->id, 'stocks' => $stockStatus]);

            // Kullanılabilir stok sayısını kontrol et
            $availableStockCount = $book->stocks()
                ->where('status', 'available')
                ->where('is_available', true)
                ->count();

            \Log::info('Available stock count', [
                'book_id' => $book->id, 
                'count' => $availableStockCount
            ]);

            if ($availableStockCount === 0) {
                return response()->json([
                    'error' => 'Bu kitabın tüm kopyaları ödünç verilmiş durumda.',
                    'book' => $book,
                    'isbn' => $isbn
                ], 400);
            }

            // Yazar isimlerini birleştir
            $authorNames = $book->authors->pluck('name')->join(', ');

            return response()->json([
                'book' => $book,
                'authors' => $authorNames,
                'available_copies' => $availableStockCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Book search error', [
                'error' => $e->getMessage(),
                'isbn' => $isbn,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Arama sırasında bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Non-AJAX fallback search that returns HTML response
     */
    public function searchFallback(Request $request)
    {
        $this->checkStaffAccess($request);
        $isbn = $request->get('isbn');
        
        \Log::info('Staff book search fallback requested', ['isbn' => $isbn, 'ip' => $request->ip()]);
        
        try {
            $book = Book::with(['authors', 'publisher', 'category'])
                ->where('isbn', $isbn)
                ->first();
            
            if (!$book) {
                return response()->view('staff.books.search-fallback', [
                    'error' => 'Kitap bulunamadı. (ISBN: ' . $isbn . ')',
                    'isbn' => $isbn
                ]);
            }
            
            // Prepare author names
            $authorNames = $book->authors->pluck('name')->join(', ');
            
            return response()->view('staff.books.search-fallback', [
                'book' => $book,
                'authorNames' => $authorNames,
                'publisherName' => $book->publisher ? $book->publisher->name : 'Belirtilmemiş',
                'categoryName' => $book->category ? $book->category->name : 'Belirtilmemiş',
                'isbn' => $isbn,
                'success' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Book search fallback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'isbn' => $isbn
            ]);
            
            return response()->view('staff.books.search-fallback', [
                'error' => 'Arama sırasında bir hata oluştu: ' . $e->getMessage(),
                'isbn' => $isbn
            ]);
        }
    }
} 
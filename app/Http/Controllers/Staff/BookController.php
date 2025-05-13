<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;

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
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,id',
            'isbn' => 'required|string|max:13|unique:books',
            'page_count' => 'required|integer|min:1',
            'language' => 'required|string|max:50',
            'publication_year' => 'required|integer|min:1800|max:' . date('Y'),
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'author_id' => 'required|exists:authors,id',
            'stock_quantity' => 'nullable|integer|min:1',
        ]);

        // Create book using fillable fields
        $book = Book::create([
            'title' => $validatedData['title'],
            'category_id' => $validatedData['category_id'],
            'publisher_id' => $validatedData['publisher_id'],
            'isbn' => $validatedData['isbn'],
            'page_count' => $validatedData['page_count'],
            'language' => $validatedData['language'],
            'publication_year' => $validatedData['publication_year'],
            'description' => $validatedData['description'],
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/covers', $filename);
            $book->cover_image = $filename;
            $book->save();
        }

        // Attach author
        $book->authors()->attach($validatedData['author_id']);
        
        // Create stock entries
        $stockQuantity = $validatedData['stock_quantity'] ?? 1; // Default to 1 if not provided
        for ($i = 0; $i < $stockQuantity; $i++) {
            $book->stocks()->create([
                'is_available' => true,
                'condition' => 'Yeni',
                'barcode' => 'BK' . $book->id . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            ]);
        }

        return redirect()->route('staff.books.index')
            ->with('success', 'Kitap başarıyla eklendi.');
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
        
        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($book->cover_image) {
                \Storage::delete('public/covers/' . $book->cover_image);
            }
            
            $image = $request->file('cover_image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/covers', $filename);
            $book->cover_image = $filename;
            $book->save();
        }
        
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
    
    public function search(Request $request)
    {
        $this->checkStaffAccess($request);
        $isbn = $request->get('isbn');
        
        // Log search information for debugging
        \Log::info('Staff book search requested', ['isbn' => $isbn, 'ip' => $request->ip()]);
        
        // Use try-catch for better error handling
        try {
            // Log to browser console
            $response = [
                'debug' => true,
                'message' => 'Searching for ISBN: ' . $isbn
            ];
            
            $book = Book::with(['authors', 'publisher', 'category'])
                ->where('isbn', $isbn)
                ->first();
            
            if (!$book) {
                \Log::warning('Book not found', ['isbn' => $isbn]);
                return response()->json([
                    'error' => 'Kitap bulunamadı.',
                    'debug' => true,
                    'isbn' => $isbn
                ], 404);
            }

            \Log::info('Book found', ['id' => $book->id, 'title' => $book->title]);

            // Format cover image URL if exists
            $coverImageUrl = null;
            if ($book->cover_image) {
                $coverImageUrl = asset('storage/covers/' . $book->cover_image);
                $book->cover_image = $coverImageUrl;
            }
            
            // Return comprehensive book data
            return response()->json([
                'book' => $book,
                'authors' => $book->authors->pluck('name')->join(', '),
                'publisher' => $book->publisher ? $book->publisher->name : 'Belirtilmemiş',
                'category' => $book->category ? $book->category->name : 'Belirtilmemiş',
                'success' => true,
                'debug' => true,
                'timestamp' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Book search error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'isbn' => $isbn
            ]);
            return response()->json([
                'error' => 'Arama sırasında bir hata oluştu: ' . $e->getMessage(),
                'success' => false,
                'debug' => true,
                'errorTrace' => $e->getTraceAsString()
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
            
            // Format cover image URL if exists
            if ($book->cover_image) {
                $book->cover_image_url = asset('storage/covers/' . $book->cover_image);
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
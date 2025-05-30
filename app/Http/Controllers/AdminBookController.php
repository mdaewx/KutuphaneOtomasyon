<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Stock;
use App\Models\Shelf;
use App\Models\AcquisitionSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with(['category', 'publisher', 'authors', 'stocks'])
            ->orderBy('id', 'asc')
            ->get();
        
        // Her kitap için mevcut durumda olduğunu belirt
        // Not: Kullanıcı henüz hiçbir kitap ödünç vermediğini belirttiği için
        // tüm kitapları "mevcut" olarak işaretliyoruz
        $books->map(function($book) {
            $book->is_available = true;
            
            // Aktif ödünç kaydı olan kitapları kontrol et
            $activeBorrowings = $book->borrowings()->whereNull('returned_at')->count();
            if ($activeBorrowings > 0) {
                // Gerçekten ödünç verildiyse, o zaman durum değişsin
                $book->is_available = false;
            }
            
            return $book;
        });
        
        $publishers = Publisher::orderBy('id', 'asc')->get();
        return view('admin.books.index', compact('books', 'publishers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $publishers = Publisher::orderBy('id', 'asc')->get();
        $authors = Author::all();
        return view('admin.books.create', compact('categories', 'publishers', 'authors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,id',
            'isbn' => 'required|string|max:13|unique:books',
            'page_count' => 'required|integer|min:1',
            'language' => 'required|string|max:50',
            'publication_year' => 'required|integer|min:1800|max:' . date('Y'),
            'description' => 'nullable|string',
            'authors' => 'required|array|min:1',
            'authors.*' => 'exists:authors,id'
        ]);

        try {
            DB::beginTransaction();

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
                'available_quantity' => 1
            ]);

            // Attach authors
            $book->authors()->attach($validatedData['authors']);

            // Create initial stock record
            $book->stocks()->create([
                'status' => 'available',
                'condition' => 'new',
                'is_available' => true
            ]);

            DB::commit();

            return redirect()->route('admin.books.index')
                ->with('success', 'Kitap başarıyla eklendi.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Kitap ekleme hatası:', [
                'error' => $e->getMessage(),
                'data' => $validatedData
            ]);
            return back()
                ->withInput()
                ->with('error', 'Kitap eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['category', 'publisher', 'authors', 'stocks', 'activeBorrowings.user']);
        
        // Stok durumu hesaplama
        $book->available_quantity = $book->getAvailableQuantityAttribute();
        $book->quantity = $book->getTotalQuantityAttribute();
        
        return view('admin.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        $publishers = Publisher::all();
        $authors = Author::all();
        return view('admin.books.edit', compact('book', 'categories', 'publishers', 'authors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,id',
            'isbn' => 'required|string|max:13|unique:books,isbn,' . $book->id,
            'page_count' => 'required|integer|min:1',
            'language' => 'required|string|max:50',
            'publication_year' => 'required|integer|min:1800|max:' . date('Y'),
            'description' => 'nullable|string',
            'authors' => 'required|array|min:1',
            'authors.*' => 'exists:authors,id'
        ]);

        try {
            DB::beginTransaction();

            // Update book using fillable fields
            $book->update([
                'title' => $validatedData['title'],
                'category_id' => $validatedData['category_id'],
                'publisher_id' => $validatedData['publisher_id'],
                'isbn' => $validatedData['isbn'],
                'page_count' => $validatedData['page_count'],
                'language' => $validatedData['language'],
                'publication_year' => $validatedData['publication_year'],
                'description' => $validatedData['description']
            ]);

            // Sync authors
            $book->authors()->sync($validatedData['authors']);

            // Update available_quantity based on stocks
            $availableStocks = $book->stocks()->where('is_available', true)->count();
            $book->update(['available_quantity' => $availableStocks]);

            DB::commit();

            return redirect()->route('admin.books.index')
                ->with('success', 'Kitap başarıyla güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Kitap güncelleme hatası:', [
                'error' => $e->getMessage(),
                'data' => $validatedData
            ]);
            return back()
                ->withInput()
                ->with('error', 'Kitap güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if ($book->activeBorrowings()->count() > 0) {
            return redirect()->route('admin.books.index')
                ->with('error', 'Bu kitap ödünç verilmiş durumda olduğu için silinemez.');
        }

        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Kitap başarıyla silindi.');
    }

    /**
     * Check ISBN number
     */
    public function checkIsbn($isbn)
    {
        // Önce kitaplar tablosunda kontrol et
        $existingBook = Book::with(['publisher', 'category', 'authors'])->where('isbn', $isbn)->first();
        if ($existingBook) {
            return response()->json([
                'exists' => true,
                'book' => [
                    'title' => $existingBook->title,
                    'author' => $existingBook->authors->pluck('name')->implode(', '),
                    'isbn' => $existingBook->isbn,
                    'publisher' => $existingBook->publisher ? $existingBook->publisher->name : '-',
                    'category' => $existingBook->category,
                    'language' => $existingBook->language,
                    'page_count' => $existingBook->page_count,
                    'available_quantity' => $existingBook->available_quantity,
                    'total_quantity' => $existingBook->quantity
                ]
            ]);
        }

        // Stok tablosunda kontrol et
        $stock = Stock::where('isbn', $isbn)->first();
        if ($stock) {
            return response()->json([
                'exists' => false,
                'stock' => [
                    'book_title' => $stock->book_title,
                    'author_ids' => $stock->author_ids ? explode(',', $stock->author_ids) : [],
                    'publisher_id' => $stock->publisher_id,
                    'category_id' => $stock->category_id,
                    'language' => $stock->language,
                    'publication_year' => $stock->publication_year,
                    'description' => $stock->description
                ]
            ]);
        }

        return response()->json([
            'exists' => false,
            'stock' => null
        ]);
    }

    /**
     * Show the form for adding a new acquisition source.
     */
    public function addAcquisitionSource(Book $book)
    {
        $sourceTypes = AcquisitionSource::SOURCE_TYPES;
        return view('admin.books.add-acquisition', compact('book', 'sourceTypes'));
    }

    /**
     * Store a new acquisition source.
     */
    public function storeAcquisitionSource(Request $request, Book $book)
    {
        $validatedData = $request->validate([
            'source_type' => 'required|in:' . implode(',', array_keys(AcquisitionSource::SOURCE_TYPES)),
            'source_name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string|max:255',
            'acquisition_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'contact_info' => 'nullable|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Handle document file upload
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/documents', $filename);
            $validatedData['document_file'] = $filename;
        }

        $book->acquisitionSources()->create($validatedData);

        return redirect()->route('admin.books.show', $book)
            ->with('success', 'Edinme kaynağı başarıyla eklendi.');
    }

    /**
     * Repair all publisher relationships for books
     */
    public function repairPublishers()
    {
        // Yayınevlerini al
        $publishers = \App\Models\Publisher::all();
        if ($publishers->isEmpty()) {
            // Eğer yayınevi yoksa, PublisherSeeder'ı çalıştır
            \Artisan::call('db:seed', ['--class' => 'PublisherSeeder']);
            $publishers = \App\Models\Publisher::all();
        }

        // Yayınevi olmayan kitapları bul
        $booksWithoutPublisher = \App\Models\Book::whereNull('publisher_id')->get();
        
        foreach ($booksWithoutPublisher as $book) {
            // Rastgele bir yayınevi seç
            $randomPublisher = $publishers->random();
            
            // Kitabın yayınevini güncelle
            $book->publisher_id = $randomPublisher->id;
            $book->save();
        }

        return redirect()->route('admin.books.index')
            ->with('success', 'Kitapların yayınevi bilgileri güncellendi.');
    }

    /**
     * Search for a book by ISBN
     */
    public function searchByIsbn($isbn)
    {
        // Log the search attempt
        \Log::info('Admin book search by ISBN', ['isbn' => $isbn]);
        
        $book = Book::with(['authors', 'publisher', 'category', 'stocks'])
            ->where('isbn', $isbn)
            ->first();
        
        if (!$book) {
            return response()->json([
                'message' => 'Kitap bulunamadı',
                'success' => false
            ], 404);
        }
        
        // Debug publisher information
        \Log::info('Book found with publisher info', [
            'book_id' => $book->id,
            'book_title' => $book->title,
            'publisher_id' => $book->publisher_id,
            'publisher' => $book->publisher ? $book->publisher->name : 'NULL',
            'has_publisher_relation' => $book->publisher ? 'YES' : 'NO'
        ]);
        
        // Kapak resmi URL'sini ayarla
        if ($book->cover_image) {
            $coverImageUrl = asset('storage/covers/' . $book->cover_image);
            $book->cover_image = $coverImageUrl;
        }
        
        // Kitap detaylarını hazırla
        $book->details = [
            'authors' => $book->authors->map(function($author) {
                return $author->name . ' ' . $author->surname;
            })->join(', '),
            'publisher' => $book->publisher ? $book->publisher->name : 'Belirtilmemiş',
            'category' => $book->category ? $book->category->name : 'Belirtilmemiş',
            'isbn' => $book->isbn
        ];
        
        // Stok durumu bilgisini ekle
        $book->available_quantity = $book->stocks()->where('is_available', true)->count();
        $book->total_quantity = $book->stocks()->count();
        
        // Include publisher directly in the response too
        return response()->json([
            'book' => $book,
            'publisher' => $book->publisher ? $book->publisher->name : 'Belirtilmemiş',
            'success' => true
        ]);
    }

    /**
     * Search for books by title, author, or ISBN
     */
    public function search(Request $request)
    {
        $term = $request->input('term');
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([
                'books' => [],
                'total_count' => 0,
                'success' => true
            ]);
        }
        
        // Log the search attempt
        \Log::info('Admin book search', ['term' => $term]);
        
        // Search in books by title or ISBN
        $books = Book::with(['authors', 'publisher', 'category'])
            ->where(function($query) use ($term) {
                $query->where('title', 'LIKE', "%{$term}%")
                    ->orWhere('isbn', 'LIKE', "%{$term}%");
            });
            
        // Handle author search by joining with the authors table
        $booksWithAuthorMatch = Book::with(['authors', 'publisher', 'category'])
            ->join('author_book', 'books.id', '=', 'author_book.book_id')
            ->join('authors', 'author_book.author_id', '=', 'authors.id')
            ->where(function($query) use ($term) {
                $query->where('authors.name', 'LIKE', "%{$term}%")
                    ->orWhere('authors.surname', 'LIKE', "%{$term}%");
            })
            ->select('books.*')
            ->distinct();
            
        // Combine the queries
        $books = $books->union($booksWithAuthorMatch)
            ->get();
        
        // Prepare the response
        $formattedBooks = $books->map(function($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'author_names' => $book->authors->map(function($author) {
                    return $author->name . ' ' . $author->surname;
                })->join(', '),
                'publisher' => $book->publisher ? $book->publisher->name : 'Belirtilmemiş',
                'category' => $book->category ? $book->category->name : 'Belirtilmemiş',
                'cover_image' => $book->cover_image ? asset('storage/covers/' . $book->cover_image) : null,
                'available_quantity' => $book->getAvailableQuantityAttribute(),
                'total_quantity' => $book->getTotalQuantityAttribute()
            ];
        });
        
        return response()->json([
            'books' => $formattedBooks,
            'total_count' => $formattedBooks->count(),
            'success' => true
        ]);
    }
} 
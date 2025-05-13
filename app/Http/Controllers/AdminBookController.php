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

class AdminBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with(['category', 'publisher', 'authors', 'stocks'])
            ->orderBy('id', 'desc')
            ->get();
        
        // Her kitap için isAvailable değerini ekle
        $books->map(function($book) {
            $book->is_available = $book->isAvailable();
            return $book;
        });
        
        $publishers = Publisher::orderBy('name')->get();
        return view('admin.books.index', compact('books', 'publishers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $publishers = Publisher::all();
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'authors' => 'required|array|min:1',
            'authors.*' => 'exists:authors,id',
        ]);

        // Debug: Log publisher_id to see what's coming from the form
        \Log::info('Publisher ID from form', [
            'publisher_id' => $request->publisher_id,
            'all_data' => $request->all()
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
            try {
                // Ensure directory exists
                if (!Storage::exists('public/covers')) {
                    Storage::makeDirectory('public/covers');
                }
                
                $image = $request->file('cover_image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/covers', $filename);
                
                // Debug log
                \Log::info('New book cover uploaded', [
                    'book_id' => $book->id,
                    'filename' => $filename,
                    'path' => $path,
                    'exists' => Storage::exists($path)
                ]);
                
                $book->cover_image = $filename;
                $book->save();
            } catch (\Exception $e) {
                \Log::error('Cover upload failed during book creation', [
                    'book_id' => $book->id,
                    'error' => $e->getMessage()
                ]);
                // Continue despite image error - book is already created
            }
        }

        // Attach authors
        $book->authors()->attach($validatedData['authors']);

        return redirect()->route('admin.books.index')
            ->with('success', 'Kitap başarıyla eklendi.');
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'authors' => 'required|array|min:1',
            'authors.*' => 'exists:authors,id',
        ]);

        // Debug: Log publisher_id to see what's coming from the form
        \Log::info('Publisher ID from update form', [
            'publisher_id' => $request->publisher_id,
            'book_id' => $book->id,
            'current_publisher_id' => $book->publisher_id,
            'all_data' => $request->all()
        ]);

        // Update book using fillable fields
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
            try {
                // Delete old cover image if exists
                if ($book->cover_image) {
                    Storage::delete('public/covers/' . $book->cover_image);
                }
                
                // Ensure directory exists
                if (!Storage::exists('public/covers')) {
                    Storage::makeDirectory('public/covers');
                }
                
                $image = $request->file('cover_image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/covers', $filename);
                
                // Debug log
                \Log::info('Updated book cover', [
                    'book_id' => $book->id,
                    'filename' => $filename,
                    'path' => $path,
                    'exists' => Storage::exists($path)
                ]);
                
                $book->cover_image = $filename;
                $book->save();
            } catch (\Exception $e) {
                \Log::error('Cover upload failed during book update', [
                    'book_id' => $book->id,
                    'error' => $e->getMessage()
                ]);
                // Continue despite image error
            }
        }

        // Sync authors
        $book->authors()->sync($validatedData['authors']);

        return redirect()->route('admin.books.index')
            ->with('success', 'Kitap başarıyla güncellendi.');
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

        if ($book->cover_image) {
            Storage::delete('public/covers/' . $book->cover_image);
        }

        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Kitap başarıyla silindi.');
    }

    /**
     * Upload a new cover image for the book.
     */
    public function uploadCover(Request $request, Book $book)
    {
        $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Delete old cover image if exists
            if ($book->cover_image) {
                Storage::delete('public/covers/' . $book->cover_image);
            }

            $image = $request->file('cover_image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Ensure directory exists
            if (!Storage::exists('public/covers')) {
                Storage::makeDirectory('public/covers');
            }
            
            // Store the image
            $path = $image->storeAs('public/covers', $filename);
            
            // Debug log
            \Log::info('Cover image uploaded', [
                'book_id' => $book->id,
                'book_title' => $book->title,
                'filename' => $filename,
                'path' => $path,
                'exists' => Storage::exists($path)
            ]);
            
            // Update book
            $book->cover_image = $filename;
            $book->save();
            
            return redirect()->back()->with('success', 'Kapak resmi başarıyla güncellendi.');
        } catch (\Exception $e) {
            \Log::error('Cover upload failed', [
                'book_id' => $book->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Kapak resmi yüklenirken bir hata oluştu: ' . $e->getMessage());
        }
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
                    'total_quantity' => $existingBook->quantity,
                    'cover_image' => $existingBook->cover_image
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
        // Get default publisher or create one
        $defaultPublisher = Publisher::first();
        if (!$defaultPublisher) {
            $defaultPublisher = Publisher::create([
                'name' => 'Varsayılan Yayınevi',
                'address' => 'İstanbul',
                'phone' => '-'
            ]);
        }
        
        // Fix books with NULL publisher_id
        $nullUpdated = Book::whereNull('publisher_id')->update([
            'publisher_id' => $defaultPublisher->id
        ]);
        
        // Fix books with invalid publisher_id
        $validPublisherIds = Publisher::pluck('id')->toArray();
        $orphanedUpdated = Book::whereNotIn('publisher_id', $validPublisherIds)
            ->whereNotNull('publisher_id')
            ->update([
                'publisher_id' => $defaultPublisher->id
            ]);
        
        // Clear model and relation caches
        Book::flushCache();
        Publisher::flushCache();
        
        // Clear Laravel caches
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        
        return redirect()->route('admin.books.index')
            ->with('success', "Yayınevi ilişkileri düzeltildi. Güncellenen kitap sayısı: Boş olanlar: {$nullUpdated}, Geçersiz olanlar: {$orphanedUpdated}");
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
} 
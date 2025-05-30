<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['publisher', 'authors', 'category', 'stocks.shelf']);
        
        // Kategori filtreleme
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Arama
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('authors', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%");
                  })
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }
        
        // Durum filtreleme
        if ($request->has('status')) {
            if ($request->status === 'available') {
                $query->whereHas('stocks', function($q) {
                    $q->where('is_available', true);
                });
            } elseif ($request->status === 'borrowed') {
                $query->whereDoesntHave('stocks', function($q) {
                    $q->where('is_available', true);
                });
            }
        }
        
        $books = $query->orderBy('id', 'asc')->paginate(12);
        $categories = \App\Models\Category::orderBy('name', 'asc')->get();
        
        return view('books.index', compact('books', 'categories'));
    }

    public function show(Book $book)
    {
        $book->load(['category', 'publisher']);
        return view('books.show', compact('book'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $books = Book::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->orWhere('isbn', 'like', "%{$query}%")
            ->with('category')
            ->get();
            
        // Her kitap için mevcut olup olmadığını kontrol edip, JSON'a ekle
        $booksWithAvailability = $books->map(function($book) {
            // Kitabın JSON temsilini al
            $bookArray = $book->toArray();
            
            // isAvailable metodu değerini ekle
            $bookArray['is_available'] = $book->isAvailable();
            
            return $bookArray;
        });
        
        return response()->json($booksWithAvailability);
    }

    public function searchByIsbn(Request $request)
    {
        // CORS başlıkları ekleyelim - geliştirme sırasında yardımcı olacaktır
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
        ];
        
        $isbn = $request->input('isbn');
        \Log::info('ISBN arama isteği başladı', ['isbn' => $isbn, 'ip' => $request->ip()]);
        
        if (empty($isbn)) {
            \Log::warning('ISBN parametre değeri boş', ['request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'ISBN numarası gereklidir.'
            ], 400, $headers);
        }
        
        try {
            // Kitabı ISBN'e göre arayalım ve ilişkili verileri de yükleyelim
            $book = Book::with(['authors', 'publisher'])->where('isbn', $isbn)->first();
            
            if ($book) {
                \Log::info('Kitap ISBN ile bulundu', [
                    'id' => $book->id, 
                    'title' => $book->title,
                    'has_cover' => $book->cover_image ? 'yes' : 'no'
                ]);
                
                $coverImageUrl = null;
                if ($book->cover_image) {
                    $coverImageUrl = asset('storage/covers/' . $book->cover_image);
                    
                    // Kapak resmi dosya varlığı kontrolü
                    if (!file_exists(public_path('storage/covers/' . $book->cover_image))) {
                        \Log::warning('Kapak resmi dosyası bulunamadı', [
                            'path' => 'storage/covers/' . $book->cover_image
                        ]);
                        
                        // Alternatif yol kontrolü
                        if (file_exists(storage_path('app/public/covers/' . $book->cover_image))) {
                            $coverImageUrl = asset('storage/app/public/covers/' . $book->cover_image);
                            \Log::info('Alternatif kapak resmi yolu kullanıldı', ['url' => $coverImageUrl]);
                        }
                    }
                }
                
                $responseData = [
                'success' => true,
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                    'authors' => $book->authors->map(function($author) {
                        return $author->name . ' ' . $author->surname;
                    }),
                    'publisher' => $book->publisher ? $book->publisher->name : null,
                    'publication_year' => $book->publication_year,
                    'page_count' => $book->page_count,
                    'language' => $book->language,
                    'description' => $book->description,
                        'cover_image' => $coverImageUrl
                ]
                ];
                
                \Log::info('Kitap verisi başarıyla döndürüldü', ['book_id' => $book->id]);
                return response()->json($responseData, 200, $headers);
        }
        
            \Log::warning('Kitap ISBN ile bulunamadı', ['isbn' => $isbn]);
        return response()->json([
            'success' => false,
            'message' => 'Kitap bulunamadı.'
            ], 404, $headers);
        } catch (\Exception $e) {
            \Log::error('ISBN araması sırasında hata oluştu', [
                'error' => $e->getMessage(),
                'isbn' => $isbn,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Arama işlemi sırasında bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500, $headers);
        }
    }
    
    /**
     * Ödünç alma işlemini gerçekleştir
     */
    public function borrow(Request $request)
    {
        try {
            \Log::info('Ödünç alma isteği başladı', ['book_id' => $request->book_id, 'user_id' => auth()->id()]);
            
            $request->validate([
                'book_id' => 'required|exists:books,id',
            ]);

            $user = auth()->user();
            if (!$user) {
                \Log::error('Kullanıcı bulunamadı veya oturum açmamış');
                return redirect()->route('login')->with('error', 'Kitap ödünç almak için lütfen giriş yapın.');
            }
            
            $book = Book::findOrFail($request->book_id);
            
            \Log::info('Kitap bulundu', ['book' => $book->title, 'available' => $book->available_quantity]);
            
            // Kitap mevcut mu kontrol et
            if (!$book->isAvailable()) {
                \Log::warning('Kitap mevcut değil', [
                    'book_id' => $book->id, 
                    'available' => $book->available_quantity, 
                    'status' => $book->status,
                    'active_loans' => $book->activeBorrowings()->count()
                ]);
                return redirect()->back()->with('error', 'Bu kitap şu anda mevcut değil.');
            }
            
            // Zaten ödünç alınmış mı kontrol et
            $existingBorrowing = \App\Models\Borrowing::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->whereNull('returned_at')
                ->exists();
                
            if ($existingBorrowing) {
                \Log::warning('Kitap zaten ödünç alınmış', ['user_id' => $user->id, 'book_id' => $book->id]);
                return redirect()->back()->with('error', 'Bu kitabı zaten ödünç almışsınız.');
            }
            
            // Yeni ödünç kaydı oluştur
            $borrowing = new \App\Models\Borrowing();
            $borrowing->user_id = $user->id;
            $borrowing->book_id = $book->id;
            $borrowing->borrow_date = now();
            $borrowing->due_date = now()->addDays(14); // 2 hafta süre
            $borrowing->status = 'approved'; // Statüyü doğrudan approved yaparak "kitap ödünç alındı" olarak işaretleyelim
            $borrowing->amount = null; // Başlangıçta ceza tutarı yok
            
            \Log::info('Yeni ödünç kaydı oluşturuldu, kaydediliyor');
            
            $saved = $borrowing->save();
            if (!$saved) {
                \Log::error('Ödünç kaydı kaydedilemedi');
                return redirect()->back()->with('error', 'Ödünç alma kaydı oluşturulamadı. Lütfen tekrar deneyin.');
            }
            
            \Log::info('Ödünç kaydı başarıyla kaydedildi', ['id' => $borrowing->id]);
            
            // Kitabın available_quantity'sini azalt
            $book->decrement('available_quantity');
            
            // Eğer available_quantity 0 olursa, kitabı status'unu "borrowed" olarak ayarla
            if ($book->available_quantity <= 0) {
                $book->status = 'borrowed';
                $book->save();
                \Log::info('Kitap ödünç durumuna güncellendi', ['status' => $book->status]);
            }
            
            \Log::info('Ödünç alma işlemi başarıyla tamamlandı');
            
            return redirect()->route('profile')->with('success', 'Kitap başarıyla ödünç alındı.');
        } catch (\Exception $e) {
            \Log::error('Ödünç alma sırasında hata oluştu', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->back()->with('error', 'Ödünç alma işlemi sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }
}

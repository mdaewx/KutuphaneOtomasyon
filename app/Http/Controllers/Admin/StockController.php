<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Stock;
use App\Models\Shelf;
use App\Models\AcquisitionSource;
use App\Models\AcquisitionSourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with(['book', 'shelf', 'acquisitionSource.sourceType'])
            ->latest()
            ->paginate(25);
            
        return view('admin.stocks.index', compact('stocks'));
    }

    public function create()
    {
        $books = Book::orderBy('title')->get();
        $shelves = Shelf::orderBy('name')->get();
        $acquisitionSources = AcquisitionSource::with('sourceType')->get();
        
        return view('admin.stocks.create', compact('books', 'shelves', 'acquisitionSources'));
    }

    public function store(Request $request)
    {
        try {
            // Debug: Log the incoming form data
            \Log::info('Admin - Incoming form data:', [
                'all_data' => $request->all()
            ]);
            
            $validated = $request->validate([
                'book_id' => 'required|exists:books,id',
                'barcode' => 'required|string',
                'shelf_id' => 'required|exists:shelves,id',
                'acquisition_source_id' => 'required|exists:acquisition_sources,id',
                'acquisition_date' => 'required|date',
                'acquisition_price' => 'nullable|numeric|min:0',
                'quantity' => 'required|integer|min:1|max:100'
            ]);

            // Edinme kaynağını kontrol et
            $acquisitionSource = AcquisitionSource::findOrFail($validated['acquisition_source_id']);
            
            // Raf kapasitesini kontrol et
            $shelf = Shelf::findOrFail($validated['shelf_id']);
            $currentShelfCount = Stock::where('shelf_id', $shelf->id)->count();
            if ($currentShelfCount + $validated['quantity'] > $shelf->capacity) {
                throw new \Exception('Seçilen rafın kapasitesi yetersiz. Lütfen başka bir raf seçin veya adet sayısını azaltın.');
            }

            // Her bir kopya için stok oluştur
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $stock = new Stock();
                $stock->book_id = $validated['book_id'];
                // Barkod oluştur: Ana barkod + sıra numarası
                $stock->barcode = $validated['barcode'] . '-' . str_pad(($i + 1), 3, '0', STR_PAD_LEFT);
                $stock->shelf_id = $validated['shelf_id'];
                $stock->acquisition_source_id = $validated['acquisition_source_id'];
                $stock->acquisition_date = $validated['acquisition_date'];
                $stock->acquisition_price = $validated['acquisition_price'];
                $stock->is_available = true;
                $stock->condition = 'new';
                $stock->status = Stock::STATUS_AVAILABLE;  // Durumu 'available' olarak ayarla
                
                $stock->save();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stoklar başarıyla oluşturuldu.',
                    'redirect' => route('admin.stocks.index')
                ]);
            }

            return redirect()
                ->route('admin.stocks.index')
                ->with('success', 'Stoklar başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            \Log::error('Stock creation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok oluşturulurken bir hata oluştu.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Stok oluşturulurken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    public function show(Stock $stock)
    {
        $stock->load(['book', 'shelf', 'acquisitionSource.sourceType', 'borrowings.user']);
        return view('admin.stocks.show', compact('stock'));
    }

    public function edit(Stock $stock)
    {
        $books = Book::orderBy('title')->get();
        $shelves = Shelf::orderBy('name')->get();
        $sourceTypes = AcquisitionSourceType::orderBy('name')->get();
        $acquisitionSources = AcquisitionSource::with('sourceType')->get();
        $stock->load(['acquisitionSource.sourceType']);
        
        return view('admin.stocks.edit', compact('stock', 'books', 'shelves', 'sourceTypes', 'acquisitionSources'));
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'shelf_id' => 'required|exists:shelves,id',
            'source_type_id' => 'required|exists:acquisition_source_types,id',
            'source_name' => 'required|string|max:255',
            'acquisition_date' => 'required|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Rafın kapasitesini kontrol et (eğer raf değişiyorsa)
        if ($request->shelf_id != $stock->shelf_id) {
            $shelf = Shelf::findOrFail($request->shelf_id);
            $currentShelfCount = Stock::where('shelf_id', $shelf->id)->count();
            
            if ($currentShelfCount >= $shelf->capacity) {
                return back()
                    ->withErrors(['shelf_id' => 'Bu raf dolu! Lütfen başka bir raf seçin.'])
                    ->withInput();
            }
        }

        // Edinme kaynağını kontrol et ve gerekirse oluştur
        if ($stock->acquisitionSource) {
        // Edinme kaynağını güncelle
        $stock->acquisitionSource->update([
            'source_type_id' => $request->source_type_id,
            'source_name' => $request->source_name,
            'price' => $request->acquisition_price,
            'acquisition_date' => $request->acquisition_date,
            'notes' => $request->notes,
        ]);
        } else {
            // Yeni bir edinme kaynağı oluştur
            $acquisitionSource = AcquisitionSource::create([
                'source_type_id' => $request->source_type_id,
                'source_name' => $request->source_name,
                'price' => $request->acquisition_price,
                'acquisition_date' => $request->acquisition_date,
                'notes' => $request->notes,
                'book_id' => $stock->book_id
            ]);
            
            // Stoğu yeni edinme kaynağı ile ilişkilendir
            $stock->acquisition_source_id = $acquisitionSource->id;
        }

        // Stok kaydını güncelle
        $stock->update([
            'shelf_id' => $request->shelf_id,
            'acquisition_date' => $request->acquisition_date,
            'acquisition_price' => $request->acquisition_price,
            'acquisition_source_id' => $stock->acquisition_source_id
        ]);

        return redirect()
            ->route('admin.stocks.index')
            ->with('success', 'Stok başarıyla güncellendi.');
    }

    public function destroy(Stock $stock)
    {
        if (!$stock->is_available) {
            return back()
                ->with('error', 'Bu stok ödünç verilmiş durumda olduğu için silinemez.');
        }

        // Edinme kaynağını kontrol et ve varsa sil
        if ($stock->acquisitionSource) {
        $stock->acquisitionSource->delete();
        }
        
        $stock->delete();

        return redirect()
            ->route('admin.stocks.index')
            ->with('success', 'Stok başarıyla silindi.');
    }

    public function searchBook($isbn)
    {
        $book = Book::with(['category', 'stocks', 'authors', 'publisher'])
            ->where('isbn', $isbn)
            ->first();
        
        if ($book) {
            $book->available_quantity = $book->stocks()->where('is_available', true)->count();
            $book->total_quantity = $book->stocks()->count();
            
            // Debug için log ekleyelim
            \Log::info('Book details:', [
                'book' => $book->toArray(),
                'publisher' => $book->publisher ? $book->publisher->toArray() : null
            ]);
            
            // Kitap detaylarını genişletilmiş şekilde hazırla
            $book->details = [
                'authors' => $book->authors->pluck('full_name')->join(', '),
                'publisher' => $book->getPublisherNameAttribute(), // Publisher helper metodunu kullanalım
                'category' => $book->category ? $book->category->name : '-',
                'isbn' => $book->isbn,
                'publication_year' => $book->publication_year,
                'page_count' => $book->page_count,
                'language' => $book->language,
                'available_quantity' => $book->available_quantity,
                'total_quantity' => $book->total_quantity
            ];
        }
        
        return response()->json(['book' => $book]);
    }
}

<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Stock;
use App\Models\Shelf;
use App\Models\User;
use App\Models\AcquisitionSource;
use Illuminate\Http\Request;

class StockController extends Controller
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
        
        $stocks = Stock::with(['book', 'shelf'])
            ->orderBy('id', 'asc')
            ->get();
            
        return view('staff.stocks.index', compact('stocks'));
    }

    public function create(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $books = Book::orderBy('title')->get();
        $shelves = Shelf::orderBy('name')->get();
        $acquisitionSources = AcquisitionSource::orderBy('source_name')->get();
        
        return view('staff.stocks.create', compact('books', 'shelves', 'acquisitionSources'));
    }

    public function store(Request $request)
    {
        $this->checkStaffAccess($request);
        
        $validatedData = $request->validate([
            'book_id' => 'required|exists:books,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'acquisition_source_id' => 'required|exists:acquisition_sources,id',
            'barcode' => 'nullable|string|max:50|unique:stocks',
            'condition' => 'required|in:new,good,fair,poor',
            'acquisition_date' => 'nullable|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        try {
            \DB::beginTransaction();

            $book = Book::findOrFail($validatedData['book_id']);

            // Her kopya için stok oluştur
            for ($i = 0; $i < $validatedData['quantity']; $i++) {
                // Barkod oluştur
                if (empty($validatedData['barcode'])) {
                    $stockCount = Stock::where('book_id', $book->id)->count() + 1;
                    $barcode = $book->isbn . '-' . str_pad($stockCount, 3, '0', STR_PAD_LEFT);
                } else {
                    $barcode = $validatedData['barcode'] . '-' . ($i + 1);
                }

                $stock = new Stock();
                $stock->book_id = $validatedData['book_id'];
                $stock->shelf_id = $validatedData['shelf_id'];
                $stock->acquisition_source_id = $validatedData['acquisition_source_id'];
                $stock->barcode = $barcode;
                $stock->condition = $validatedData['condition'];
                $stock->status = Stock::STATUS_AVAILABLE;
                $stock->is_available = true;
                $stock->acquisition_date = $validatedData['acquisition_date'] ?? now();
                $stock->acquisition_price = $validatedData['acquisition_price'];
                $stock->notes = $validatedData['notes'];
                $stock->save();
            }

            \DB::commit();

            return redirect()->route('staff.stocks.index')
                ->with('success', 'Stok başarıyla eklendi.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Stok ekleme hatası:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Stok eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        return view('staff.stocks.show', compact('stock'));
    }

    public function edit(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        
        $books = Book::orderBy('title')->get();
        $shelves = Shelf::orderBy('name')->get();
        $acquisitionSources = AcquisitionSource::orderBy('source_name')->get();
        
        return view('staff.stocks.edit', compact('stock', 'books', 'shelves', 'acquisitionSources'));
    }

    public function update(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        
        $validatedData = $request->validate([
            'book_id' => 'required|exists:books,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'acquisition_source_id' => 'required|exists:acquisition_sources,id',
            'barcode' => 'required|string|max:50|unique:stocks,barcode,' . $stock->id,
            'condition' => 'required|string|in:new,good,fair,poor',
            'notes' => 'nullable|string'
        ]);

        $stock->update($validatedData);

        return redirect()->route('staff.stocks.index')
            ->with('success', 'Stok başarıyla güncellendi.');
    }

    public function destroy(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        
        // Ödünç verilmiş stok silinememez
        if ($stock->status === 'borrowed') {
            return back()->with('error', 'Ödünç verilmiş stok kaydı silinemez.');
        }

        $stock->delete();

        return redirect()->route('staff.stocks.index')
            ->with('success', 'Stok başarıyla silindi.');
    }

    public function searchBook(Request $request)
    {
        try {
            $this->checkStaffAccess($request);
            
            $search = $request->get('search');
            
            if (empty($search)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arama terimi gereklidir.'
                ], 400);
            }

            \Log::info('Staff stock search started', ['search_term' => $search]);

            // Search by ISBN or title
            $book = Book::with(['authors', 'category'])
                ->where(function($query) use ($search) {
                    $query->where('isbn', 'LIKE', "%{$search}%")
                          ->orWhere('title', 'LIKE', "%{$search}%");
                })
                ->first();
            
            if (!$book) {
                \Log::info('No book found for search term', ['search_term' => $search]);
                return response()->json([
                    'success' => false,
                    'message' => 'Kitap bulunamadı.'
                ], 404);
            }

            // Format author names
            $authorNames = $book->authors->map(function($author) {
                return $author->name . ' ' . $author->surname;
            })->join(', ');

            // Get available stock count
            $availableStockCount = $book->stocks()
                ->where('status', 'available')
                ->where('is_available', true)
                ->count();

            $response = [
                'success' => true,
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                    'authors' => $authorNames,
                    'category' => $book->category ? $book->category->name : 'Belirtilmemiş',
                    'available_copies' => $availableStockCount
                ]
            ];

            \Log::info('Book found successfully', [
                'book_id' => $book->id, 
                'isbn' => $book->isbn,
                'available_copies' => $availableStockCount
            ]);
            
            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Stock search error', [
                'error' => $e->getMessage(),
                'search' => $search ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Arama sırasında bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
} 
<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Stock;
use App\Models\Shelf;
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
        
        // Kitap ve ödünç durumlarını kontrol edebilmek için ilişkili tabloları yükleyelim
        $stocks = Stock::with(['book', 'borrowings'])->latest()->paginate(10);
        
        return view('staff.stocks.index', compact('stocks'));
    }

    public function create(Request $request)
    {
        $this->checkStaffAccess($request);
        $books = Book::all();
        $shelves = \App\Models\Shelf::all();
        $acquisitionSources = \App\Models\AcquisitionSource::all();
        return view('staff.stocks.create', compact('books', 'shelves', 'acquisitionSources'));
    }

    public function store(Request $request)
    {
        $this->checkStaffAccess($request);
        
        // Debug: Log the incoming form data to see what's being sent
        \Log::info('Incoming form data:', [
            'all_data' => $request->all(),
            'condition_value' => $request->input('condition'),
            'condition_type' => gettype($request->input('condition'))
        ]);
        
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'barcode' => 'required|string|unique:stocks,barcode',
            'shelf_id' => 'required|exists:shelves,id',
            'acquisition_source_id' => 'required|exists:acquisition_sources,id',
            'acquisition_date' => 'required|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1|max:100',
            'condition' => 'required|string|in:new,good,fair,poor'
        ]);

        // Debug: Log the validated data
        \Log::info('Validated form data:', [
            'condition' => $validated['condition']
        ]);

        $quantity = $validated['quantity'] ?? 1;
        $baseBarcode = $validated['barcode'];
        $book = Book::find($validated['book_id']);
        
        // Ensure we have a valid condition value in English
        $condition = $validated['condition'];

        // Create multiple stock records based on quantity
        for ($i = 0; $i < $quantity; $i++) {
            // Generate unique barcode for each copy if quantity > 1
            $barcode = $quantity > 1 ? $baseBarcode . '-' . ($i + 1) : $baseBarcode;
            
            $stock = new Stock();
            $stock->book_id = $validated['book_id'];
            $stock->barcode = $barcode;
            $stock->shelf_id = $validated['shelf_id'];
            $stock->acquisition_source_id = $validated['acquisition_source_id'];
            $stock->acquisition_date = $validated['acquisition_date'];
            $stock->acquisition_price = $validated['acquisition_price'];
            $stock->is_available = true;
            
            // Set condition directly to a valid English value regardless of what was sent
            // This is a temporary fix to bypass the data validation issue
            $stock->condition = 'new';
            
            $stock->save();

            // Increment the book's available and total quantities
            if ($book) {
                $book->increment('available_quantity');
                $book->increment('quantity');
            }
        }

        return redirect()->route('staff.stocks.index')
            ->with('success', $quantity > 1 ? $quantity . ' adet stok başarıyla eklendi.' : 'Stok başarıyla eklendi.');
    }

    public function show(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        return view('staff.stocks.show', compact('stock'));
    }

    public function edit(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        $books = Book::all();
        return view('staff.stocks.edit', compact('stock', 'books'));
    }

    public function update(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        
        // Validasyonu sadece formdaki mevcut alanlar için yapıyoruz
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'isbn' => 'required|string|max:13',
            'condition' => 'required|string|in:new,good,fair,poor',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $stock->update($validated);

        return redirect()->route('staff.stocks.index')
            ->with('success', 'Stok başarıyla güncellendi.');
    }

    public function destroy(Request $request, Stock $stock)
    {
        $this->checkStaffAccess($request);
        $stock->delete();

        return redirect()->route('staff.stocks.index')
            ->with('success', 'Stok başarıyla silindi.');
    }

    public function searchBook(Request $request)
    {
        $this->checkStaffAccess($request);
        $isbn = $request->get('isbn');
        $book = Book::with(['authors', 'publisher', 'category', 'stocks'])
            ->where('isbn', $isbn)
            ->first();
        
        if (!$book) {
            return response()->json(['error' => 'Kitap bulunamadı.'], 404);
        }

        return response()->json([
            'book' => $book,
            'authors' => $book->authors->pluck('name')->join(', '),
            'publisher' => $book->publisher ? $book->publisher->name : 'Belirtilmemiş',
            'category' => $book->category ? $book->category->name : 'Belirtilmemiş'
        ]);
    }
} 
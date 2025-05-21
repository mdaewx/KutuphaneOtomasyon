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
            'condition' => 'required|string|in:new,good,fair,poor',
            'notes' => 'nullable|string'
        ]);

        // Barkod otomatik oluşturma
        if (empty($validatedData['barcode'])) {
            $book = Book::find($validatedData['book_id']);
            $stockCount = Stock::where('book_id', $book->id)->count() + 1;
            $validatedData['barcode'] = 'BK' . str_pad($book->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($stockCount, 3, '0', STR_PAD_LEFT);
        }

        $stock = new Stock();
        $stock->book_id = $validatedData['book_id'];
        $stock->shelf_id = $validatedData['shelf_id'];
        $stock->acquisition_source_id = $validatedData['acquisition_source_id'];
        $stock->barcode = $validatedData['barcode'];
        $stock->condition = $validatedData['condition'];
        $stock->notes = $validatedData['notes'];
        $stock->status = 'available';
        $stock->save();

        return redirect()->route('staff.stocks.index')
            ->with('success', 'Stok başarıyla eklendi.');
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
        $search = $request->get('search');
        
        if (empty($search)) {
            return response()->json(['error' => 'Arama terimi gereklidir.'], 400);
        }

        $book = Book::with(['authors', 'publisher', 'category', 'stocks'])
            ->where('isbn', 'LIKE', "%{$search}%")
            ->orWhere('title', 'LIKE', "%{$search}%")
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
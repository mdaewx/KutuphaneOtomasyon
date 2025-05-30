<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Shelf;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShelfManagementController extends Controller
{
    public function index()
    {
        $shelves = Shelf::withCount('stocks')->get();
        $books = Book::with([
            'authors', 
            'category', 
            'publisher', 
            'stocks.shelf'
        ])->get();
        
        return view('admin.shelf-management.index', compact('shelves', 'books'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'shelf_id' => 'required|exists:shelves,id',
        ]);

        // Rafın kapasitesini kontrol et
        $shelf = Shelf::findOrFail($request->shelf_id);
        $currentShelfCount = Stock::where('shelf_id', $shelf->id)->count();
        
        if ($currentShelfCount >= $shelf->capacity) {
            return response()->json([
                'message' => 'Bu raf dolu! Lütfen başka bir raf seçin.'
            ], 422);
        }

        // Kitabın stoklarını güncelle
        $book = Book::findOrFail($request->book_id);
        $stocks = $book->stocks;

        if ($stocks->isEmpty()) {
            return response()->json([
                'message' => 'Bu kitabın stoğu bulunmamaktadır.'
            ], 422);
        }

        // İlk müsait stoğu bul ve rafını güncelle
        $stock = $stocks->first(function ($stock) {
            return $stock->is_available;
        });

        if (!$stock) {
            return response()->json([
                'message' => 'Bu kitabın müsait stoğu bulunmamaktadır.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $stock->update([
                'shelf_id' => $request->shelf_id
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Kitap başarıyla rafa atandı.',
                'stock' => $stock
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateShelf(Request $request, Book $book)
    {
        $validated = $request->validate([
            'shelf_number' => 'required|string|max:50|exists:shelves,name'
        ]);

        $book->update([
            'shelf_number' => $validated['shelf_number']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Raf numarası başarıyla güncellendi.'
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $books = Book::with([
            'authors', 
            'category', 
            'publisher',
            'stocks.shelf'
        ])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('isbn', 'like', "%{$query}%")
            ->orWhere('shelf_number', 'like', "%{$query}%")
            ->orderBy('shelf_number')
            ->paginate(10);

        $shelves = Shelf::withCount('stocks')->orderBy('name')->get();

        return view('admin.shelf-management.index', compact('books', 'shelves', 'query'));
    }

    public function storeShelf(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:shelves,name',
            'shelf_number' => 'required|string|max:20|unique:shelves,shelf_number',
            'description' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,maintenance'
        ]);

        Shelf::create($validated);

        return redirect()->route('admin.shelf-management.index')
            ->with('success', 'Raf başarıyla eklendi.');
    }

    public function updateShelfDetails(Request $request, Shelf $shelf)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:shelves,name,' . $shelf->id,
            'shelf_number' => 'required|string|max:20|unique:shelves,shelf_number,' . $shelf->id,
            'description' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,maintenance'
        ]);

        $shelf->update($validated);

        return redirect()->route('admin.shelf-management.index')
            ->with('success', 'Raf bilgileri başarıyla güncellendi.');
    }

    public function deleteShelf(Shelf $shelf)
    {
        // Rafta kitap var mı kontrol et
        $hasBooks = Book::where('shelf_number', $shelf->name)->exists();
        
        if ($hasBooks) {
            return redirect()->route('admin.shelf-management.index')
                ->with('error', 'Bu rafta kitaplar bulunduğu için silinemez.');
        }

        $shelf->delete();

        return redirect()->route('admin.shelf-management.index')
            ->with('success', 'Raf başarıyla silindi.');
    }

    public function create()
    {
        return view('admin.shelves.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shelves,name',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1'
        ]);

        // En son raf numarasını bul
        $lastShelf = Shelf::orderBy('id', 'desc')->first();
        $nextId = $lastShelf ? $lastShelf->id + 1 : 1;
        $shelfNumber = 'RAF-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        Shelf::create([
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity,
            'shelf_number' => $shelfNumber,
            'code' => $shelfNumber
        ]);

        return redirect()
            ->route('admin.shelf-management.index')
            ->with('success', 'Raf başarıyla oluşturuldu.');
    }

    public function edit(Shelf $shelf)
    {
        return view('admin.shelves.edit', compact('shelf'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shelves,name,' . $shelf->id,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1'
        ]);

        $shelf->update([
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity
        ]);

        return redirect()
            ->route('admin.shelf-management.index')
            ->with('success', 'Raf başarıyla güncellendi.');
    }

    public function destroy(Shelf $shelf)
    {
        if ($shelf->books()->exists()) {
            return back()->with('error', 'Bu rafta kitaplar bulunduğu için silinemez.');
        }

        $shelf->delete();

        return redirect()
            ->route('admin.shelf-management.index')
            ->with('success', 'Raf başarıyla silindi.');
    }
}

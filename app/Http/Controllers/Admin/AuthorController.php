<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::orderBy('id', 'asc')
            ->paginate(10);

        $startingNumber = ($authors->currentPage() - 1) * $authors->perPage() + 1;
        
        $authors->getCollection()->transform(function ($author) use (&$startingNumber) {
            $author->row_number = $startingNumber++;
            return $author;
        });
        
        return view('admin.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('admin.authors.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
            ]);

            $author = Author::create($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Yazar başarıyla eklendi.',
                    'redirect' => route('admin.authors.index')
                ]);
            }

            return redirect()->route('admin.authors.index')
                ->with('success', 'Yazar başarıyla eklendi.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yazar eklenirken bir hata oluştu.',
                    'errors' => $e->getMessage()
                ], 422);
            }

            return back()->withInput()->with('error', 'Yazar eklenirken bir hata oluştu.');
        }
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
        ]);

        $author->update($validated);

        return redirect()->route('admin.authors.index')
            ->with('success', 'Yazar başarıyla güncellendi.');
    }

    public function destroy(Author $author)
    {
        $author->delete();
        return redirect()->route('admin.authors.index')
            ->with('success', 'Yazar başarıyla silindi.');
    }
} 
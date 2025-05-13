<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Publisher::orderBy('name')->paginate(10);
        return view('admin.publishers.index', compact('publishers'));
    }

    public function create()
    {
        return view('admin.publishers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        Publisher::create($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Yayınevi başarıyla eklendi.');
    }

    public function edit(Publisher $publisher)
    {
        return view('admin.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $publisher->update($validated);

        return redirect()->route('admin.publishers.index')
            ->with('success', 'Yayınevi başarıyla güncellendi.');
    }

    public function destroy(Publisher $publisher)
    {
        // Ona bağlı kitapların publisher_id'sini null yap
        $publisher->books()->update(['publisher_id' => null]);
        $publisher->delete();
        return redirect()->route('admin.publishers.index')
            ->with('success', 'Yayınevi başarıyla silindi.');
    }
} 
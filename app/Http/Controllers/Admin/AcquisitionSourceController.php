<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcquisitionSource;
use App\Models\AcquisitionSourceType;
use Illuminate\Http\Request;

class AcquisitionSourceController extends Controller
{
    public function index()
    {
        $sources = AcquisitionSource::with(['sourceType', 'book'])
            ->orderBy('created_at', 'asc')
            ->paginate(25);
        $types = AcquisitionSourceType::orderBy('name')->get();

        return view('admin.acquisitions.index', compact('sources', 'types'));
    }

    public function create()
    {
        $types = AcquisitionSourceType::orderBy('name')->get();
        $authors = \App\Models\Author::orderBy('name')->get();
        return view('admin.acquisitions.create', compact('types', 'authors'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'source_name' => 'required|string|max:255',
        ]);

        AcquisitionSource::create([
            'source_name' => $validatedData['source_name'],
        ]);

        return redirect()->route('admin.acquisitions.index')
            ->with('success', 'Edinme kaynağı başarıyla eklendi.');
    }

    public function show(AcquisitionSource $acquisition)
    {
        $acquisition->load(['sourceType', 'book', 'stocks']);
        return view('admin.acquisitions.show', compact('acquisition'));
    }

    public function edit($id)
    {
        $acquisition = AcquisitionSource::findOrFail($id);
        $types = AcquisitionSourceType::orderBy('name')->get();
        return view('admin.acquisitions.edit', compact('acquisition', 'types'));
    }

    public function update(Request $request, $id)
    {
        $acquisition = AcquisitionSource::findOrFail($id);

        $validatedData = $request->validate([
            'source_name' => 'required|string|max:255',
        ]);

        $acquisition->update([
            'source_name' => $validatedData['source_name'],
        ]);

        // Yazarları güncelle
        if (isset($validatedData['authors'])) {
            $acquisition->authors()->sync($validatedData['authors']);
        }

        return redirect()->route('admin.acquisitions.index')
            ->with('success', 'Edinme kaynağı başarıyla güncellendi.');
    }

    public function destroy($id)
    {
        $acquisition = AcquisitionSource::findOrFail($id);

        if ($acquisition->stocks()->exists()) {
            return redirect()->route('admin.acquisitions.index')
                ->with('error', 'Bu edinme kaynağı stokta kullanıldığı için silinemez.');
        }

        $acquisition->delete();

        return redirect()->route('admin.acquisitions.index')
            ->with('success', 'Edinme kaynağı başarıyla silindi.');
    }
}

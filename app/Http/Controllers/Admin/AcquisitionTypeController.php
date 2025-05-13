<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcquisitionSourceType;
use Illuminate\Http\Request;

class AcquisitionTypeController extends Controller
{
    public function index()
    {
        $types = AcquisitionSourceType::orderBy('name')->get();
        return view('admin.acquisition-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.acquisition-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:acquisition_source_types,name',
            'description' => 'nullable|string'
        ]);

        AcquisitionSourceType::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()
            ->route('admin.acquisition-types.index')
            ->with('success', 'Edinme türü başarıyla eklendi.');
    }

    public function edit(AcquisitionSourceType $type)
    {
        return view('admin.acquisition-types.edit', compact('type'));
    }

    public function update(Request $request, AcquisitionSourceType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:acquisition_source_types,name,' . $type->id,
            'description' => 'nullable|string'
        ]);

        $type->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()
            ->route('admin.acquisition-types.index')
            ->with('success', 'Edinme türü başarıyla güncellendi.');
    }

    public function destroy(AcquisitionSourceType $type)
    {
        // Türe bağlı edinme kaynakları varsa silmeyi engelle
        if ($type->acquisitionSources()->exists()) {
            return back()->with('error', 'Bu edinme türüne bağlı kaynaklar olduğu için silinemez.');
        }

        $type->delete();

        return redirect()
            ->route('admin.acquisition-types.index')
            ->with('success', 'Edinme türü başarıyla silindi.');
    }
} 
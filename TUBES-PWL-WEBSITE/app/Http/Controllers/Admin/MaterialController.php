<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::withCount('artWorks')
            ->orderBy('material_name')
            ->paginate(20);

        return view('admin.materials.index', [
            'title'     => 'Materials',
            'subtitle'  => 'Manage artwork materials',
            'activeNav' => 'materials',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Materials', 'isCurrent' => true],
            ],
            'materials' => $materials,
        ]);
    }

    public function create()
    {
        return view('admin.materials.form', [
            'title'      => 'Create Material',
            'subtitle'   => 'Add a new material',
            'activeNav'  => 'materials',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Materials', 'href' => route('admin.materials.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'material'   => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255|unique:materials,material_name',
        ]);

        Material::create($validated);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material created successfully');
    }

    public function show(Material $material)
    {
        $material->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.materials.show', [
            'title'      => 'Material Details',
            'subtitle'   => $material->material_name,
            'activeNav'  => 'materials',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Materials', 'href' => route('admin.materials.index')],
                ['label' => $material->material_name, 'isCurrent' => true],
            ],
            'material'   => $material,
        ]);
    }

    public function edit(Material $material)
    {
        return view('admin.materials.form', [
            'title'      => 'Edit Material',
            'subtitle'   => 'Update material information',
            'activeNav'  => 'materials',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Materials', 'href' => route('admin.materials.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'material'   => $material,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255|unique:materials,material_name,' . $material->material_id . ',material_id',
        ]);

        $material->update($validated);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material updated successfully');
    }

    public function destroy(Material $material)
    {
        if ($material->artWorks()->exists()) {
            return redirect()->route('admin.materials.index')
                ->with('error', 'Cannot delete material with associated artworks');
        }

        $material->delete();

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material deleted successfully');
    }
}

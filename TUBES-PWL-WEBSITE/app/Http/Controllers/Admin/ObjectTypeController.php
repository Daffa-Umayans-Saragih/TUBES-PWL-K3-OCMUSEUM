<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObjectType;
use Illuminate\Http\Request;

class ObjectTypeController extends Controller
{
    public function index()
    {
        $objectTypes = ObjectType::withCount('artWorks')
            ->orderBy('object_type_name')
            ->paginate(20);

        return view('admin.object-types.index', [
            'title'       => 'Object Types',
            'subtitle'    => 'Manage artwork object types',
            'activeNav'   => 'object-types',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Object Types', 'isCurrent' => true],
            ],
            'objectTypes' => $objectTypes,
        ]);
    }

    public function create()
    {
        return view('admin.object-types.form', [
            'title'       => 'Create Object Type',
            'subtitle'    => 'Add a new object type',
            'activeNav'   => 'object-types',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Object Types', 'href' => route('admin.object-types.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'objectType'  => null,
            'isEdit'      => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'object_type_name' => 'required|string|max:255|unique:object_types,object_type_name',
        ]);

        ObjectType::create($validated);

        return redirect()->route('admin.object-types.index')
            ->with('success', 'Object Type created successfully');
    }

    public function show(ObjectType $objectType)
    {
        $objectType->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.object-types.show', [
            'title'       => 'Object Type Details',
            'subtitle'    => $objectType->object_type_name,
            'activeNav'   => 'object-types',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Object Types', 'href' => route('admin.object-types.index')],
                ['label' => $objectType->object_type_name, 'isCurrent' => true],
            ],
            'objectType'  => $objectType,
        ]);
    }

    public function edit(ObjectType $objectType)
    {
        return view('admin.object-types.form', [
            'title'       => 'Edit Object Type',
            'subtitle'    => 'Update object type information',
            'activeNav'   => 'object-types',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Object Types', 'href' => route('admin.object-types.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'objectType'  => $objectType,
            'isEdit'      => true,
        ]);
    }

    public function update(Request $request, ObjectType $objectType)
    {
        $validated = $request->validate([
            'object_type_name' => 'required|string|max:255|unique:object_types,object_type_name,' . $objectType->type_id . ',type_id',
        ]);

        $objectType->update($validated);

        return redirect()->route('admin.object-types.index')
            ->with('success', 'Object Type updated successfully');
    }

    public function destroy(ObjectType $objectType)
    {
        if ($objectType->artWorks()->exists()) {
            return redirect()->route('admin.object-types.index')
                ->with('error', 'Cannot delete object type with associated artworks');
        }

        $objectType->delete();

        return redirect()->route('admin.object-types.index')
            ->with('success', 'Object Type deleted successfully');
    }
}

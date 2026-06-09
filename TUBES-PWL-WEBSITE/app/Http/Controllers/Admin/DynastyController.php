<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dynasty;
use Illuminate\Http\Request;

class DynastyController extends Controller
{
    public function index()
    {
        $dynasties = Dynasty::withCount('artWorks')
            ->orderBy('dynasty_name')
            ->paginate(20);

        return view('admin.dynasties.index', [
            'title'      => 'Dynasties',
            'subtitle'   => 'Manage artwork dynasties',
            'activeNav'  => 'dynasties',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Dynasties', 'isCurrent' => true],
            ],
            'dynasties'  => $dynasties,
        ]);
    }

    public function create()
    {
        return view('admin.dynasties.form', [
            'title'      => 'Create Dynasty',
            'subtitle'   => 'Add a new dynasty',
            'activeNav'  => 'dynasties',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Dynasties', 'href' => route('admin.dynasties.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'dynasty'    => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dynasty_name' => 'required|string|max:255|unique:dynasties,dynasty_name',
        ]);

        Dynasty::create($validated);

        return redirect()->route('admin.dynasties.index')
            ->with('success', 'Dynasty created successfully');
    }

    public function show(Dynasty $dynasty)
    {
        $dynasty->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.dynasties.show', [
            'title'      => 'Dynasty Details',
            'subtitle'   => $dynasty->dynasty_name,
            'activeNav'  => 'dynasties',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Dynasties', 'href' => route('admin.dynasties.index')],
                ['label' => $dynasty->dynasty_name, 'isCurrent' => true],
            ],
            'dynasty'    => $dynasty,
        ]);
    }

    public function edit(Dynasty $dynasty)
    {
        return view('admin.dynasties.form', [
            'title'      => 'Edit Dynasty',
            'subtitle'   => 'Update dynasty information',
            'activeNav'  => 'dynasties',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Dynasties', 'href' => route('admin.dynasties.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'dynasty'    => $dynasty,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Dynasty $dynasty)
    {
        $validated = $request->validate([
            'dynasty_name' => 'required|string|max:255|unique:dynasties,dynasty_name,' . $dynasty->dynasty_id . ',dynasty_id',
        ]);

        $dynasty->update($validated);

        return redirect()->route('admin.dynasties.index')
            ->with('success', 'Dynasty updated successfully');
    }

    public function destroy(Dynasty $dynasty)
    {
        if ($dynasty->artWorks()->exists()) {
            return redirect()->route('admin.dynasties.index')
                ->with('error', 'Cannot delete dynasty with associated artworks');
        }

        $dynasty->delete();

        return redirect()->route('admin.dynasties.index')
            ->with('success', 'Dynasty deleted successfully');
    }
}

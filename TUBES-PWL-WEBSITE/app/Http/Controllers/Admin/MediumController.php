<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medium;
use Illuminate\Http\Request;

class MediumController extends Controller
{
    public function index()
    {
        $mediums = Medium::withCount('artWorks')
            ->orderBy('medium_name')
            ->paginate(20);

        return view('admin.mediums.index', [
            'title'     => 'Mediums',
            'subtitle'  => 'Manage artwork mediums',
            'activeNav' => 'mediums',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Mediums', 'isCurrent' => true],
            ],
            'mediums'   => $mediums,
        ]);
    }

    public function create()
    {
        return view('admin.mediums.form', [
            'title'      => 'Create Medium',
            'subtitle'   => 'Add a new medium',
            'activeNav'  => 'mediums',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Mediums', 'href' => route('admin.mediums.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'medium'     => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medium_name' => 'required|string|max:255|unique:mediums,medium_name',
        ]);

        Medium::create($validated);

        return redirect()->route('admin.mediums.index')
            ->with('success', 'Medium created successfully');
    }

    public function show(Medium $medium)
    {
        $medium->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.mediums.show', [
            'title'      => 'Medium Details',
            'subtitle'   => $medium->medium_name,
            'activeNav'  => 'mediums',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Mediums', 'href' => route('admin.mediums.index')],
                ['label' => $medium->medium_name, 'isCurrent' => true],
            ],
            'medium'     => $medium,
        ]);
    }

    public function edit(Medium $medium)
    {
        return view('admin.mediums.form', [
            'title'      => 'Edit Medium',
            'subtitle'   => 'Update medium information',
            'activeNav'  => 'mediums',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Mediums', 'href' => route('admin.mediums.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'medium'     => $medium,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Medium $medium)
    {
        $validated = $request->validate([
            'medium_name' => 'required|string|max:255|unique:mediums,medium_name,' . $medium->medium_id . ',medium_id',
        ]);

        $medium->update($validated);

        return redirect()->route('admin.mediums.index')
            ->with('success', 'Medium updated successfully');
    }

    public function destroy(Medium $medium)
    {
        if ($medium->artWorks()->exists()) {
            return redirect()->route('admin.mediums.index')
                ->with('error', 'Cannot delete medium with associated artworks');
        }

        $medium->delete();

        return redirect()->route('admin.mediums.index')
            ->with('success', 'Medium deleted successfully');
    }
}

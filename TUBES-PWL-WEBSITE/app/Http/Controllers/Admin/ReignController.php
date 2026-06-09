<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reign;
use Illuminate\Http\Request;

class ReignController extends Controller
{
    public function index()
    {
        $reigns = Reign::withCount('artWorks')
            ->orderBy('reign_name')
            ->paginate(20);

        return view('admin.reigns.index', [
            'title'      => 'Reigns',
            'subtitle'   => 'Manage artwork reigns',
            'activeNav'  => 'reigns',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Reigns', 'isCurrent' => true],
            ],
            'reigns'     => $reigns,
        ]);
    }

    public function create()
    {
        return view('admin.reigns.form', [
            'title'      => 'Create Reign',
            'subtitle'   => 'Add a new reign',
            'activeNav'  => 'reigns',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Reigns', 'href' => route('admin.reigns.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'reign'      => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reign_name' => 'required|string|max:255|unique:reigns,reign_name',
        ]);

        Reign::create($validated);

        return redirect()->route('admin.reigns.index')
            ->with('success', 'Reign created successfully');
    }

    public function show(Reign $reign)
    {
        $reign->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.reigns.show', [
            'title'      => 'Reign Details',
            'subtitle'   => $reign->reign_name,
            'activeNav'  => 'reigns',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Reigns', 'href' => route('admin.reigns.index')],
                ['label' => $reign->reign_name, 'isCurrent' => true],
            ],
            'reign'      => $reign,
        ]);
    }

    public function edit(Reign $reign)
    {
        return view('admin.reigns.form', [
            'title'      => 'Edit Reign',
            'subtitle'   => 'Update reign information',
            'activeNav'  => 'reigns',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Reigns', 'href' => route('admin.reigns.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'reign'      => $reign,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Reign $reign)
    {
        $validated = $request->validate([
            'reign_name' => 'required|string|max:255|unique:reigns,reign_name,' . $reign->reign_id . ',reign_id',
        ]);

        $reign->update($validated);

        return redirect()->route('admin.reigns.index')
            ->with('success', 'Reign updated successfully');
    }

    public function destroy(Reign $reign)
    {
        if ($reign->artWorks()->exists()) {
            return redirect()->route('admin.reigns.index')
                ->with('error', 'Cannot delete reign with associated artworks');
        }

        $reign->delete();

        return redirect()->route('admin.reigns.index')
            ->with('success', 'Reign deleted successfully');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culture;
use Illuminate\Http\Request;

class CultureController extends Controller
{
    public function index()
    {
        $cultures = Culture::withCount('artWorks')
            ->orderBy('culture_name')
            ->paginate(20);

        return view('admin.cultures.index', [
            'title'      => 'Cultures',
            'subtitle'   => 'Manage artwork cultures',
            'activeNav'  => 'cultures',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Cultures', 'isCurrent' => true],
            ],
            'cultures'   => $cultures,
        ]);
    }

    public function create()
    {
        return view('admin.cultures.form', [
            'title'      => 'Create Culture',
            'subtitle'   => 'Add a new culture',
            'activeNav'  => 'cultures',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Cultures', 'href' => route('admin.cultures.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'culture'    => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'culture_name' => 'required|string|max:255|unique:cultures,culture_name',
        ]);

        Culture::create($validated);

        return redirect()->route('admin.cultures.index')
            ->with('success', 'Culture created successfully');
    }

    public function show(Culture $culture)
    {
        $culture->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.cultures.show', [
            'title'      => 'Culture Details',
            'subtitle'   => $culture->culture_name,
            'activeNav'  => 'cultures',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Cultures', 'href' => route('admin.cultures.index')],
                ['label' => $culture->culture_name, 'isCurrent' => true],
            ],
            'culture'    => $culture,
        ]);
    }

    public function edit(Culture $culture)
    {
        return view('admin.cultures.form', [
            'title'      => 'Edit Culture',
            'subtitle'   => 'Update culture information',
            'activeNav'  => 'cultures',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Cultures', 'href' => route('admin.cultures.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'culture'    => $culture,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Culture $culture)
    {
        $validated = $request->validate([
            'culture_name' => 'required|string|max:255|unique:cultures,culture_name,' . $culture->culture_id . ',culture_id',
        ]);

        $culture->update($validated);

        return redirect()->route('admin.cultures.index')
            ->with('success', 'Culture updated successfully');
    }

    public function destroy(Culture $culture)
    {
        if ($culture->artWorks()->exists()) {
            return redirect()->route('admin.cultures.index')
                ->with('error', 'Cannot delete culture with associated artworks');
        }

        $culture->delete();

        return redirect()->route('admin.cultures.index')
            ->with('success', 'Culture deleted successfully');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classification;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    public function index()
    {
        $classifications = Classification::withCount('artWorks')
            ->orderBy('classification_name')
            ->paginate(20);

        return view('admin.classifications.index', [
            'title'             => 'Classifications',
            'subtitle'          => 'Manage artwork classifications',
            'activeNav'         => 'classifications',
            'breadcrumbs'       => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Classifications', 'isCurrent' => true],
            ],
            'classifications'   => $classifications,
        ]);
    }

    public function create()
    {
        return view('admin.classifications.form', [
            'title'         => 'Create Classification',
            'subtitle'      => 'Add a new classification',
            'activeNav'     => 'classifications',
            'breadcrumbs'   => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Classifications', 'href' => route('admin.classifications.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'classification' => null,
            'isEdit'        => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'classification_name' => 'required|string|max:255|unique:classifications,classification_name',
        ]);

        Classification::create($validated);

        return redirect()->route('admin.classifications.index')
            ->with('success', 'Classification created successfully');
    }

    public function show(Classification $classification)
    {
        $classification->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.classifications.show', [
            'title'         => 'Classification Details',
            'subtitle'      => $classification->classification_name,
            'activeNav'     => 'classifications',
            'breadcrumbs'   => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Classifications', 'href' => route('admin.classifications.index')],
                ['label' => $classification->classification_name, 'isCurrent' => true],
            ],
            'classification' => $classification,
        ]);
    }

    public function edit(Classification $classification)
    {
        return view('admin.classifications.form', [
            'title'         => 'Edit Classification',
            'subtitle'      => 'Update classification information',
            'activeNav'     => 'classifications',
            'breadcrumbs'   => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Classifications', 'href' => route('admin.classifications.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'classification' => $classification,
            'isEdit'        => true,
        ]);
    }

    public function update(Request $request, Classification $classification)
    {
        $validated = $request->validate([
            'classification_name' => 'required|string|max:255|unique:classifications,classification_name,' . $classification->classification_id . ',classification_id',
        ]);

        $classification->update($validated);

        return redirect()->route('admin.classifications.index')
            ->with('success', 'Classification updated successfully');
    }

    public function destroy(Classification $classification)
    {
        if ($classification->artWorks()->exists()) {
            return redirect()->route('admin.classifications.index')
                ->with('error', 'Cannot delete classification with associated artworks');
        }

        $classification->delete();

        return redirect()->route('admin.classifications.index')
            ->with('success', 'Classification deleted successfully');
    }
}

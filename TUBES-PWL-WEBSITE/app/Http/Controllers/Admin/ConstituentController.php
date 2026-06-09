<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Constituent;
use App\Models\Nationality;
use Illuminate\Http\Request;

class ConstituentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $gender = $request->get('gender', '');

        $constituents = Constituent::query()
            ->withCount('artWorks')
            ->when($search, function ($query) use ($search) {
                return $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('alpha_sort', 'like', "%{$search}%");
            })
            ->when($gender, function ($query) use ($gender) {
                return $query->where('gender', $gender);
            })
            ->orderBy('display_name')
            ->paginate(20);

        return view('admin.constituents.index', [
            'title'        => 'Artists',
            'subtitle'     => 'Manage museum artists and constituents',
            'activeNav'    => 'constituents',
            'breadcrumbs'  => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artists', 'isCurrent' => true],
            ],
            'constituents' => $constituents,
            'search'       => $search,
            'gender'       => $gender,
        ]);
    }

    public function create()
    {
        $nationalities = Nationality::orderBy('nationality_name')->get();

        return view('admin.constituents.form', [
            'title'         => 'Create Artist',
            'subtitle'      => 'Add a new artist or constituent',
            'activeNav'     => 'constituents',
            'breadcrumbs'   => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artists', 'href' => route('admin.constituents.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'constituent'   => null,
            'nationalities' => $nationalities,
            'isEdit'        => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name'        => 'required|string|max:255',
            'display_bio'         => 'nullable|string',
            'alpha_sort'          => 'nullable|string|max:255',
            'birth_year'          => 'nullable|integer|min:1000|max:2100',
            'death_year'          => 'nullable|integer|min:1000|max:2100',
            'birth_date_display'  => 'nullable|string|max:255',
            'death_date_display'  => 'nullable|string|max:255',
            'birth_place'         => 'nullable|string|max:255',
            'death_place'         => 'nullable|string|max:255',
            'gender'              => 'nullable|string|in:Male,Female,Unknown',
            'ulan_url'            => 'nullable|url',
            'wikidata_url'        => 'nullable|url',
            'nationality_ids'     => 'nullable|array',
            'nationality_ids.*'   => 'integer|exists:nationalities,nationality_id',
        ]);

        $constituent = Constituent::create($validated);

        // Attach nationalities if provided
        if ($request->has('nationality_ids') && is_array($request->input('nationality_ids'))) {
            $constituent->nationalities()->sync($request->input('nationality_ids'));
        }

        return redirect()->route('admin.constituents.index')
            ->with('success', 'Artist created successfully');
    }

    public function show(Constituent $constituent)
    {
        $constituent->load([
            'artWorks' => function ($query) {
                $query->limit(10);
            },
            'nationalities' => function ($query) {
                $query->orderBy('nationality_name');
            },
        ]);

        return view('admin.constituents.show', [
            'title'       => 'Artist Details',
            'subtitle'    => $constituent->display_name,
            'activeNav'   => 'constituents',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artists', 'href' => route('admin.constituents.index')],
                ['label' => $constituent->display_name, 'isCurrent' => true],
            ],
            'constituent' => $constituent,
        ]);
    }

    public function edit(Constituent $constituent)
    {
        $nationalities = Nationality::orderBy('nationality_name')->get();
        $selectedNationalities = $constituent->nationalities()->pluck('nationality_id')->toArray();

        return view('admin.constituents.form', [
            'title'                   => 'Edit Artist',
            'subtitle'                => 'Update artist information',
            'activeNav'               => 'constituents',
            'breadcrumbs'             => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artists', 'href' => route('admin.constituents.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'constituent'             => $constituent,
            'nationalities'           => $nationalities,
            'selectedNationalities'   => $selectedNationalities,
            'isEdit'                  => true,
        ]);
    }

    public function update(Request $request, Constituent $constituent)
    {
        $validated = $request->validate([
            'display_name'        => 'required|string|max:255',
            'display_bio'         => 'nullable|string',
            'alpha_sort'          => 'nullable|string|max:255',
            'birth_year'          => 'nullable|integer|min:1000|max:2100',
            'death_year'          => 'nullable|integer|min:1000|max:2100',
            'birth_date_display'  => 'nullable|string|max:255',
            'death_date_display'  => 'nullable|string|max:255',
            'birth_place'         => 'nullable|string|max:255',
            'death_place'         => 'nullable|string|max:255',
            'gender'              => 'nullable|string|in:Male,Female,Unknown',
            'ulan_url'            => 'nullable|url',
            'wikidata_url'        => 'nullable|url',
            'nationality_ids'     => 'nullable|array',
            'nationality_ids.*'   => 'integer|exists:nationalities,nationality_id',
        ]);

        $constituent->update($validated);

        // Sync nationalities if provided
        if ($request->has('nationality_ids')) {
            $constituent->nationalities()->sync($request->input('nationality_ids'));
        } else {
            $constituent->nationalities()->detach();
        }

        return redirect()->route('admin.constituents.index')
            ->with('success', 'Artist updated successfully');
    }

    public function destroy(Constituent $constituent)
    {
        if ($constituent->artWorks()->exists()) {
            return redirect()->route('admin.constituents.index')
                ->with('error', 'Cannot delete artist with associated artworks');
        }

        $constituent->nationalities()->detach();
        $constituent->delete();

        return redirect()->route('admin.constituents.index')
            ->with('success', 'Artist deleted successfully');
    }
}

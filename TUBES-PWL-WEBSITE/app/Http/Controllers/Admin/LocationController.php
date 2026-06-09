<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount('artWorks', 'visitSchedules')
            ->orderBy('location_name')
            ->paginate(20);

        return view('admin.locations.index', [
            'title'       => 'Locations',
            'subtitle'    => 'Manage museum locations and galleries',
            'activeNav'   => 'locations',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Locations', 'isCurrent' => true],
            ],
            'locations'   => $locations,
        ]);
    }

    public function create()
    {
        return view('admin.locations.form', [
            'title'       => 'Create Location',
            'subtitle'    => 'Add a new location or gallery',
            'activeNav'   => 'locations',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Locations', 'href' => route('admin.locations.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'location'    => null,
            'isEdit'      => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_name'  => 'required|string|max:255|unique:locations,location_name',
            'address'        => 'nullable|string',
            'capacity_limit' => 'nullable|integer|min:0',
        ]);

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully');
    }

    public function show(Location $location)
    {
        $location->load([
            'artWorks' => function ($query) {
                $query->limit(10);
            },
            'visitSchedules' => function ($query) {
                $query->limit(10);
            },
        ]);

        return view('admin.locations.show', [
            'title'       => 'Location Details',
            'subtitle'    => $location->location_name,
            'activeNav'   => 'locations',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Locations', 'href' => route('admin.locations.index')],
                ['label' => $location->location_name, 'isCurrent' => true],
            ],
            'location'    => $location,
        ]);
    }

    public function edit(Location $location)
    {
        return view('admin.locations.form', [
            'title'       => 'Edit Location',
            'subtitle'    => 'Update location information',
            'activeNav'   => 'locations',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Locations', 'href' => route('admin.locations.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'location'    => $location,
            'isEdit'      => true,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'location_name'  => 'required|string|max:255|unique:locations,location_name,' . $location->location_id . ',location_id',
            'address'        => 'nullable|string',
            'capacity_limit' => 'nullable|integer|min:0',
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully');
    }

    public function destroy(Location $location)
    {
        if ($location->artWorks()->exists() || $location->visitSchedules()->exists()) {
            return redirect()->route('admin.locations.index')
                ->with('error', 'Cannot delete location with associated data');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully');
    }
}

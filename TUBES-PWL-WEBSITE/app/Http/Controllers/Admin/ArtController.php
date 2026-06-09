<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtWork;
use App\Models\ArtWorkImage;
use App\Models\Department;
use App\Models\Location;
use App\Models\ObjectType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $totalArtworks  = ArtWork::count();
        $totalUsers     = User::count();
        $totalOrders    = Order::count();
        $recentArtworks = ArtWork::latest('art_work_id')->take(5)->get();

        return view('admin.dashboard.dashboard', [
            'totalArtworks'  => $totalArtworks,
            'totalUsers'     => $totalUsers,
            'totalOrders'    => $totalOrders,
            'recentArtworks' => $recentArtworks,
        ]);
    }

    /**
     * Display list of all artworks
     */
    public function index()
    {
        $artworks = ArtWork::with('department', 'objectType', 'location', 'constituents', 'mediums')
            ->paginate(20);

        return view('admin.art.art', [
            'artworks' => $artworks,
        ]);
    }

    /**
     * Show create artwork form
     */
    public function create()
    {
        $departments = Department::orderBy('department_name')->get();
        $types       = ObjectType::orderBy('object_type_name')->get();
        $locations   = Location::orderBy('location_name')->get();

        return view('admin.art.create.create', [
            'departments' => $departments,
            'types'       => $types,
            'locations'   => $locations,
        ]);
    }

    /**
     * Store newly created artwork
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'met_object_id'       => 'nullable|string|max:255|unique:art_works,met_object_id',
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'department_id'       => 'required|exists:departments,department_id',
            'type_id'             => 'required|exists:object_types,type_id',
            'location_id'         => 'required|exists:locations,location_id',
            'accession_number'    => 'nullable|string|max:255|unique:art_works,accession_number',
            'accession_year'      => 'nullable|integer|min:1000|max:' . date('Y'),
            'object_date_display' => 'nullable|string|max:255',
            'object_begin_date'   => 'nullable|integer|min:1000|max:' . date('Y'),
            'object_end_date'     => 'nullable|integer|min:1000|max:' . date('Y'),
            'gallery_number'      => 'nullable|string|max:255',
            'images'              => 'nullable|array',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'medium_ids'          => 'nullable|array',
            'medium_ids.*'        => 'exists:mediums,medium_id',
            'constituents'        => 'nullable|array', // e.g., [['constituent_id' => 1, 'role_id' => 2, 'display_order' => 1]]
            'sims'                => 'nullable|array',
            'sims.*.sim_type'     => 'required|in:Signature,Inscription,Marking',
            'sims.*.sim_text'     => 'required|string',
        ]);

        $artwork = ArtWork::create([
            'met_object_id'       => $validated['met_object_id'] ?? null,
            'title'               => $validated['title'],
            'slug'                => Str::slug($validated['title']),
            'description'         => $validated['description'] ?? null,
            'department_id'       => $validated['department_id'],
            'type_id'             => $validated['type_id'],
            'location_id'         => $validated['location_id'],
            'accession_number'    => $validated['accession_number'] ?? null,
            'accession_year'      => $validated['accession_year'] ?? null,
            'object_date_display' => $validated['object_date_display'] ?? null,
            'object_begin_date'   => $validated['object_begin_date'] ?? null,
            'object_end_date'     => $validated['object_end_date'] ?? null,
            'gallery_number'      => $validated['gallery_number'] ?? null,
        ]);

        if (isset($validated['medium_ids'])) {
            $artwork->mediums()->sync($validated['medium_ids']);
        }
        
        if (isset($validated['constituents'])) {
            $syncData = [];
            foreach ($validated['constituents'] as $c) {
                if (isset($c['constituent_id'])) {
                    $syncData[$c['constituent_id']] = [
                        'role_id' => $c['role_id'] ?? null,
                        'prefix_id' => $c['prefix_id'] ?? null,
                        'suffix_id' => $c['suffix_id'] ?? null,
                        'display_order' => $c['display_order'] ?? 1,
                    ];
                }
            }
            $artwork->constituents()->sync($syncData);
        }

        if (isset($validated['sims'])) {
            foreach ($validated['sims'] as $simData) {
                $artwork->artWorkSims()->create($simData);
            }
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('artworks', 'public');

                ArtWorkImage::create([
                    'art_work_id' => $artwork->art_work_id,
                    'url'         => $path,
                    'is_primary'  => $index === 0 ? 1 : 0,
                ]);
            }
        }

        return redirect()->route('admin.art.show', $artwork->art_work_id)
            ->with('success', 'Artwork created successfully!');
    }

    /**
     * Show edit artwork form
     */
    public function edit($id)
    {
        $artwork     = ArtWork::with(['department', 'objectType', 'location', 'constituents', 'mediums', 'artWorkSims'])->where('art_work_id', $id)->firstOrFail();
        $departments = Department::orderBy('department_name')->get();
        $types       = ObjectType::orderBy('object_type_name')->get();
        $locations   = Location::orderBy('location_name')->get();

        return view('admin.art.edit.edit', [
            'artwork'     => $artwork,
            'departments' => $departments,
            'types'       => $types,
            'locations'   => $locations,
        ]);
    }

    /**
     * Update artwork
     */
    public function update(Request $request, $id)
    {
        $artwork = ArtWork::where('art_work_id', $id)->firstOrFail();

        $validated = $request->validate([
            'met_object_id'       => 'nullable|string|max:255|unique:art_works,met_object_id,' . $id . ',art_work_id',
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'department_id'       => 'required|exists:departments,department_id',
            'type_id'             => 'required|exists:object_types,type_id',
            'location_id'         => 'required|exists:locations,location_id',
            'accession_number'    => 'nullable|string|max:255|unique:art_works,accession_number,' . $id . ',art_work_id',
            'accession_year'      => 'nullable|integer|min:1000|max:' . date('Y'),
            'object_date_display' => 'nullable|string|max:255',
            'object_begin_date'   => 'nullable|integer|min:1000|max:' . date('Y'),
            'object_end_date'     => 'nullable|integer|min:1000|max:' . date('Y'),
            'gallery_number'      => 'nullable|string|max:255',
            'images'              => 'nullable|array',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'medium_ids'          => 'nullable|array',
            'medium_ids.*'        => 'exists:mediums,medium_id',
            'constituents'        => 'nullable|array', 
            'sims'                => 'nullable|array',
            'sims.*.sim_type'     => 'required|in:Signature,Inscription,Marking',
            'sims.*.sim_text'     => 'required|string',
        ]);

        $artwork->update([
            'met_object_id'       => $validated['met_object_id'] ?? $artwork->met_object_id,
            'title'               => $validated['title'],
            'slug'                => Str::slug($validated['title']),
            'description'         => $validated['description'] ?? null,
            'department_id'       => $validated['department_id'],
            'type_id'             => $validated['type_id'],
            'location_id'         => $validated['location_id'],
            'accession_number'    => $validated['accession_number'] ?? null,
            'accession_year'      => $validated['accession_year'] ?? null,
            'object_date_display' => $validated['object_date_display'] ?? null,
            'object_begin_date'   => $validated['object_begin_date'] ?? null,
            'object_end_date'     => $validated['object_end_date'] ?? null,
            'gallery_number'      => $validated['gallery_number'] ?? null,
        ]);

        if (isset($validated['medium_ids'])) {
            $artwork->mediums()->sync($validated['medium_ids']);
        }
        
        if (isset($validated['constituents'])) {
            $syncData = [];
            foreach ($validated['constituents'] as $c) {
                if (isset($c['constituent_id'])) {
                    $syncData[$c['constituent_id']] = [
                        'role_id' => $c['role_id'] ?? null,
                        'prefix_id' => $c['prefix_id'] ?? null,
                        'suffix_id' => $c['suffix_id'] ?? null,
                        'display_order' => $c['display_order'] ?? 1,
                    ];
                }
            }
            $artwork->constituents()->sync($syncData);
        }

        if (isset($validated['sims'])) {
            $artwork->artWorkSims()->delete();
            foreach ($validated['sims'] as $simData) {
                $artwork->artWorkSims()->create($simData);
            }
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('artworks', 'public');

                ArtWorkImage::create([
                    'art_work_id' => $artwork->art_work_id,
                    'url'         => $path,
                    'is_primary'  => 0,
                ]);
            }
        }

        return redirect()->route('admin.art.index')
            ->with('success', 'Artwork updated successfully!');
    }

    /**
     * Show artwork details
     */
    public function show($id)
    {
        $artwork = ArtWork::with([
            'department', 'objectType', 'classification', 'creditLine', 'location', 'repository',
            'images', 'measurements', 'references', 'exhibitionHistories',
            'materials', 'mediums', 'tags', 'cultures', 'periods', 'dynasties', 'reigns', 'portfolios',
            'constituents', 'geographies.country', 'geographies.city', 'geographies.geographyType',
            'artWorkSims'
        ])->where('art_work_id', $id)->firstOrFail();

        return view('admin.art.show.show', [
            'artwork' => $artwork,
        ]);
    }

    /**
     * Delete artwork
     */
    public function destroy($id)
    {
        $artwork = ArtWork::where('art_work_id', $id)->firstOrFail();
        $title   = $artwork->title;

        // Delete all images
        foreach ($artwork->images as $image) {
            Storage::disk('public')->delete($image->url);
            $image->delete();
        }

        $artwork->delete();

        return redirect()->route('admin.art.index')
            ->with('success', "Artwork '{$title}' deleted successfully!");
    }

    /**
     * Delete artwork image
     */
    public function deleteImage($imageId)
    {
        $image     = ArtWorkImage::where('image_id', $imageId)->firstOrFail();
        $artworkId = $image->art_work_id;

        // Delete file from storage
        Storage::disk('public')->delete($image->url);
        $image->delete();

        return redirect()->route('admin.art.edit', $artworkId)
            ->with('success', 'Image deleted successfully!');
    }
}

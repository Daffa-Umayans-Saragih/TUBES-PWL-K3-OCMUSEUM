<?php
namespace App\Http\Controllers\Admin;

use App\Models\ArtWork;
use App\Models\Department;
use App\Models\ObjectType;
use App\Models\Location;
use App\Models\Repository;
use App\Models\Classification;
use App\Models\CreditLine;
use App\Models\Material;
use App\Models\Medium;
use App\Models\Constituent;
use App\Models\Tag;
use App\Models\Culture;
use App\Models\Period;
use App\Models\Dynasty;
use App\Models\Reign;
use App\Models\Portfolio;
use App\Models\ConstituentRole;
use App\Models\ConstituentPrefix;
use App\Models\ConstituentSuffix;
use App\Models\GeographyType;
use App\Models\Country;
use App\Models\Region;
use App\Models\Excavation;
use App\Models\River;
use App\Models\State;
use App\Models\County;
use App\Models\City;
use App\Models\Subregion;
use App\Models\Locale;
use App\Models\Locus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtworkController extends Controller
{
    /**
     * Display a listing of artworks
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $departmentId = $request->query('department');
        $classificationId = $request->query('classification');
        $typeId = $request->query('type');
        $sort = $request->query('sort', 'newest');
        $status = $request->query('status', 'active');

        $query = ArtWork::with([
            'department', 'objectType', 'location', 'constituents', 
            'materials', 'mediums', 'images'
        ]);

        // Status filter
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Unified search
        $query->when($search, function ($q, $search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('title', 'like', "%{$search}%")
                   ->orWhere('accession_number', 'like', "%{$search}%")
                   ->orWhere('accession_year', 'like', "%{$search}%")
                   ->orWhere('met_object_id', 'like', "%{$search}%")
                   ->orWhere('slug', 'like', "%{$search}%")
                   ->orWhereHas('constituents', function ($cq) use ($search) {
                       $cq->where('display_name', 'like', "%{$search}%");
                   });
            });
        });

        // Filters
        $query->when($departmentId, function ($q, $departmentId) {
            $q->where('department_id', $departmentId);
        });

        $query->when($classificationId, function ($q, $classificationId) {
            $q->where('classification_id', $classificationId);
        });

        $query->when($typeId, function ($q, $typeId) {
            $q->where('type_id', $typeId);
        });

        // Sorting
        switch ($sort) {
            case 'oldest':
                $query->orderBy('art_work_id', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'accession_year_asc':
                $query->orderBy('accession_year', 'asc');
                break;
            case 'accession_year_desc':
                $query->orderBy('accession_year', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('art_work_id', 'desc');
                break;
        }

        $artworks = $query->paginate(20)->withQueryString();
        
        $totalArtworks = ArtWork::count();
        $totalDepartments = Department::count();
        $onDisplay = ArtWork::where('is_on_view', true)->count();

        // Master lists for the dropdown filters
        $departmentsList = Department::orderBy('department_name')->get();
        $classificationsList = Classification::orderBy('classification_name')->get();
        $objectTypesList = ObjectType::orderBy('object_type_name')->get();
        
        return view('admin.artworks.index', [
            'artworks' => $artworks,
            'totalArtworks' => $totalArtworks,
            'totalDepartments' => $totalDepartments,
            'onDisplay' => $onDisplay,
            'departmentsList' => $departmentsList,
            'classificationsList' => $classificationsList,
            'objectTypesList' => $objectTypesList,
            'title' => 'Artworks',
            'subtitle' => 'Manage all artworks',
            'activeNav' => 'artworks',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artworks', 'isCurrent' => true],
            ],
        ]);
    }

    /**
     * Show the form for creating a new artwork
     */
    public function create()
    {
        return view('admin.artworks.form', [
            'title' => 'Create Artwork',
            'subtitle' => 'Add a new artwork to the collection',
            'activeNav' => 'artworks',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artworks', 'href' => route('admin.artworks.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'artwork' => null,
            'isEdit' => false,
            'departments' => Department::orderBy('department_name')->get(),
            'objectTypes' => ObjectType::orderBy('object_type_name')->get(),
            'locations' => Location::orderBy('location_name')->get(),
            'repositories' => Repository::orderBy('repository_name')->get(),
            'classifications' => Classification::orderBy('classification_name')->get(),
            'creditLines' => CreditLine::orderBy('credit_line_text')->get(),
            'materials' => Material::orderBy('material_name')->get(),
            'mediums' => Medium::orderBy('medium_name')->get(),
            'constituents' => Constituent::orderBy('display_name')->get(),
            'tags' => Tag::orderBy('tag_term')->get(),
            'cultures' => Culture::orderBy('culture_name')->get(),
            'periods' => Period::orderBy('period_name')->get(),
            'dynasties' => Dynasty::orderBy('dynasty_name')->get(),
            'reigns' => Reign::orderBy('reign_name')->get(),
            'portfolios' => Portfolio::orderBy('portfolio_name')->get(),
            'roles' => ConstituentRole::orderBy('role_name')->get(),
            'prefixes' => ConstituentPrefix::orderBy('prefix_name')->get(),
            'suffixes' => ConstituentSuffix::orderBy('suffix_name')->get(),
            'geographyTypes' => GeographyType::orderBy('geography_type_name')->get(),
            'countries' => Country::orderBy('country_name')->get(),
            'regions' => Region::orderBy('region_name')->get(),
            'excavations' => Excavation::orderBy('excavation_name')->get(),
            'rivers' => River::orderBy('river_name')->get(),
            'states' => State::orderBy('state_name')->get(),
            'counties' => County::orderBy('county_name')->get(),
            'cities' => City::orderBy('city_name')->get(),
            'subregions' => Subregion::orderBy('subregion_name')->get(),
            'locales' => Locale::orderBy('locale_name')->get(),
            'loci' => Locus::orderBy('locus_name')->get(),
        ]);
    }

    /**
     * Store a newly created artwork
     */
    public function store(Request $request)
    {
        $this->resolveInlineMasterRecords($request);
        $this->preprocessGeographies($request);

        $validated = $request->validate([
            'met_object_id' => 'required|integer|unique:art_works,met_object_id',
            'title' => 'required|string|max:500',
            'accession_number' => 'required|string|unique:art_works,accession_number',
            'accession_year' => 'nullable|integer|min:1000|max:2100',
            'description' => 'nullable|string',
            'gallery_number' => 'nullable|string|max:50',
            'object_date_display' => 'nullable|string|max:255',
            'object_begin_date' => 'nullable|integer|min:1000|max:2100',
            'object_end_date' => 'nullable|integer|min:1000|max:2100',
            'dimensions_display' => 'nullable|string',
            'rights_and_reproduction' => 'nullable|string',
            'provenance' => 'nullable|string',
            'department_id' => 'required|exists:departments,department_id',
            'type_id' => 'required|exists:object_types,type_id',
            'location_id' => 'required|exists:locations,location_id',
            'repository_id' => 'required|exists:repositories,repository_id',
            'classification_id' => 'nullable|exists:classifications,classification_id',
            'credit_line_id' => 'nullable|exists:credit_lines,credit_line_id',
            'is_on_view' => 'boolean',
            'is_highlight' => 'boolean',
            'is_public_domain' => 'boolean',
            'is_timeline_work' => 'boolean',
            'materials' => 'nullable|array',
            'materials.*' => 'exists:materials,material_id',
            'mediums' => 'nullable|array',
            'mediums.*' => 'exists:mediums,medium_id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,tag_id',
            'cultures' => 'nullable|array',
            'cultures.*' => 'exists:cultures,culture_id',
            'periods' => 'nullable|array',
            'periods.*' => 'exists:periods,period_id',
            'dynasties' => 'nullable|array',
            'dynasties.*' => 'exists:dynasties,dynasty_id',
            'reigns' => 'nullable|array',
            'reigns.*' => 'exists:reigns,reign_id',
            'portfolios' => 'nullable|array',
            'portfolios.*' => 'exists:portfolios,portfolio_id',
            'geographies' => 'nullable|array',
            'geographies.*.geography_type_id' => 'nullable|exists:geography_types,geography_type_id',
            'geographies.*.country_id' => 'nullable|exists:countries,country_id',
            'geographies.*.state_id' => 'nullable|exists:states,state_id',
            'geographies.*.county_id' => 'nullable|exists:counties,county_id',
            'geographies.*.city_id' => 'nullable|exists:cities,city_id',
            'geographies.*.region_id' => 'nullable|exists:regions,region_id',
            'geographies.*.subregion_id' => 'nullable|exists:subregions,subregion_id',
            'geographies.*.locale_id' => 'nullable|exists:locales,locale_id',
            'geographies.*.locus_id' => 'nullable|exists:loci,locus_id',
            'geographies.*.excavation_id' => 'nullable|exists:excavations,excavation_id',
            'geographies.*.river_id' => 'nullable|exists:rivers,river_id',
            'new_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $validated['slug'] = $this->generateUniqueSlug($validated['title']);
            $artwork = ArtWork::create(\Illuminate\Support\Arr::except($validated, ['materials', 'mediums', 'tags', 'cultures', 'periods', 'dynasties', 'reigns', 'portfolios', 'geographies']));

            // Sync M2M relationships
            $this->syncM2MRelationships($artwork, $request);

            // Save constituents with pivot data
            $this->saveConstituents($artwork, $request);

            // Save child records (measurements, references, SIMs)
            $this->saveChildRecords($artwork, $request);

            return redirect()->route('admin.artworks.show', $artwork->art_work_id)
                ->with('success', 'Artwork created successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating artwork: ' . $e->getMessage());
        }
    }

    /**
     * Show the specified artwork
     */
    public function show(ArtWork $artwork)
    {
        $artwork->load([
            'department',
            'objectType',
            'location',
            'repository',
            'classification',
            'creditLine',
            'materials',
            'mediums',
            'constituents',
            'tags',
            'cultures',
            'periods',
            'dynasties',
            'reigns',
            'portfolios',
            'images',
            'measurements',
            'exhibitionHistories',
            'references',
            'geographies'
        ]);

        return view('admin.artworks.show', [
            'title' => 'Artwork Details',
            'subtitle' => $artwork->title ?? 'Untitled',
            'activeNav' => 'artworks',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artworks', 'href' => route('admin.artworks.index')],
                ['label' => $artwork->title ?? 'Untitled', 'isCurrent' => true],
            ],
            'artwork' => $artwork,
        ]);
    }

    /**
     * Show the form for editing the artwork
     */
    public function edit(ArtWork $artwork)
    {
        $artwork->load([
            'materials',
            'mediums',
            'constituents',
            'tags',
            'cultures',
            'periods',
            'dynasties',
            'reigns',
            'portfolios',
            'images',
            'geographies'
        ]);

        return view('admin.artworks.form', [
            'title' => 'Edit Artwork',
            'subtitle' => 'Update artwork information',
            'activeNav' => 'artworks',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Artworks', 'href' => route('admin.artworks.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'artwork' => $artwork,
            'isEdit' => true,
            'departments' => Department::orderBy('department_name')->get(),
            'objectTypes' => ObjectType::orderBy('object_type_name')->get(),
            'locations' => Location::orderBy('location_name')->get(),
            'repositories' => Repository::orderBy('repository_name')->get(),
            'classifications' => Classification::orderBy('classification_name')->get(),
            'creditLines' => CreditLine::orderBy('credit_line_text')->get(),
            'materials' => Material::orderBy('material_name')->get(),
            'mediums' => Medium::orderBy('medium_name')->get(),
            'constituents' => Constituent::orderBy('display_name')->get(),
            'tags' => Tag::orderBy('tag_term')->get(),
            'cultures' => Culture::orderBy('culture_name')->get(),
            'periods' => Period::orderBy('period_name')->get(),
            'dynasties' => Dynasty::orderBy('dynasty_name')->get(),
            'reigns' => Reign::orderBy('reign_name')->get(),
            'portfolios' => Portfolio::orderBy('portfolio_name')->get(),
            'roles' => ConstituentRole::orderBy('role_name')->get(),
            'prefixes' => ConstituentPrefix::orderBy('prefix_name')->get(),
            'suffixes' => ConstituentSuffix::orderBy('suffix_name')->get(),
            'geographyTypes' => GeographyType::orderBy('geography_type_name')->get(),
            'countries' => Country::orderBy('country_name')->get(),
            'regions' => Region::orderBy('region_name')->get(),
            'excavations' => Excavation::orderBy('excavation_name')->get(),
            'rivers' => River::orderBy('river_name')->get(),
            'states' => State::orderBy('state_name')->get(),
            'counties' => County::orderBy('county_name')->get(),
            'cities' => City::orderBy('city_name')->get(),
            'subregions' => Subregion::orderBy('subregion_name')->get(),
            'locales' => Locale::orderBy('locale_name')->get(),
            'loci' => Locus::orderBy('locus_name')->get(),
        ]);
    }

    /**
     * Update the specified artwork
     */
    public function update(Request $request, ArtWork $artwork)
    {
        $this->resolveInlineMasterRecords($request);
        $this->preprocessGeographies($request);

        $validated = $request->validate([
            'met_object_id' => 'required|integer|unique:art_works,met_object_id,' . $artwork->art_work_id . ',art_work_id',
            'title' => 'required|string|max:500',
            'accession_number' => 'required|string|unique:art_works,accession_number,' . $artwork->art_work_id . ',art_work_id',
            'accession_year' => 'nullable|integer|min:1000|max:2100',
            'description' => 'nullable|string',
            'gallery_number' => 'nullable|string|max:50',
            'object_date_display' => 'nullable|string|max:255',
            'object_begin_date' => 'nullable|integer|min:1000|max:2100',
            'object_end_date' => 'nullable|integer|min:1000|max:2100',
            'dimensions_display' => 'nullable|string',
            'rights_and_reproduction' => 'nullable|string',
            'provenance' => 'nullable|string',
            'department_id' => 'required|exists:departments,department_id',
            'type_id' => 'required|exists:object_types,type_id',
            'location_id' => 'required|exists:locations,location_id',
            'repository_id' => 'required|exists:repositories,repository_id',
            'classification_id' => 'nullable|exists:classifications,classification_id',
            'credit_line_id' => 'nullable|exists:credit_lines,credit_line_id',
            'is_on_view' => 'boolean',
            'is_highlight' => 'boolean',
            'is_public_domain' => 'boolean',
            'is_timeline_work' => 'boolean',
            'materials' => 'nullable|array',
            'materials.*' => 'exists:materials,material_id',
            'mediums' => 'nullable|array',
            'mediums.*' => 'exists:mediums,medium_id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,tag_id',
            'cultures' => 'nullable|array',
            'cultures.*' => 'exists:cultures,culture_id',
            'periods' => 'nullable|array',
            'periods.*' => 'exists:periods,period_id',
            'dynasties' => 'nullable|array',
            'dynasties.*' => 'exists:dynasties,dynasty_id',
            'reigns' => 'nullable|array',
            'reigns.*' => 'exists:reigns,reign_id',
            'portfolios' => 'nullable|array',
            'portfolios.*' => 'exists:portfolios,portfolio_id',
            'geographies' => 'nullable|array',
            'geographies.*.geography_type_id' => 'nullable|exists:geography_types,geography_type_id',
            'geographies.*.country_id' => 'nullable|exists:countries,country_id',
            'geographies.*.state_id' => 'nullable|exists:states,state_id',
            'geographies.*.county_id' => 'nullable|exists:counties,county_id',
            'geographies.*.city_id' => 'nullable|exists:cities,city_id',
            'geographies.*.region_id' => 'nullable|exists:regions,region_id',
            'geographies.*.subregion_id' => 'nullable|exists:subregions,subregion_id',
            'geographies.*.locale_id' => 'nullable|exists:locales,locale_id',
            'geographies.*.locus_id' => 'nullable|exists:loci,locus_id',
            'geographies.*.excavation_id' => 'nullable|exists:excavations,excavation_id',
            'geographies.*.river_id' => 'nullable|exists:rivers,river_id',
            'new_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            if (empty($artwork->slug)) {
                $validated['slug'] = $this->generateUniqueSlug($validated['title']);
            }
            $artwork->update(\Illuminate\Support\Arr::except($validated, ['materials', 'mediums', 'tags', 'cultures', 'periods', 'dynasties', 'reigns', 'portfolios', 'geographies']));

            // Sync M2M relationships
            $this->syncM2MRelationships($artwork, $request);

            // Save constituents with pivot data
            $this->saveConstituents($artwork, $request);

            // Save child records (measurements, references, SIMs)
            $this->saveChildRecords($artwork, $request);

            return redirect()->route('admin.artworks.show', $artwork->art_work_id)
                ->with('success', 'Artwork updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating artwork: ' . $e->getMessage());
        }
    }

    public function destroy(ArtWork $artwork)
    {
        try {
            $artwork->delete();
            return redirect()->route('admin.artworks.index')
                ->with('success', 'Artwork soft-deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.artworks.index')
                ->with('error', 'Error soft-deleting artwork: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $artwork = ArtWork::onlyTrashed()->findOrFail($id);
            $artwork->restore();
            return redirect()->route('admin.artworks.index', ['status' => 'trashed'])
                ->with('success', 'Artwork restored successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.artworks.index', ['status' => 'trashed'])
                ->with('error', 'Error restoring artwork: ' . $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        try {
            $artwork = ArtWork::onlyTrashed()->findOrFail($id);

            \Illuminate\Support\Facades\DB::transaction(function () use ($artwork) {
                // Detach all Many-to-Many relationships
                $artwork->materials()->detach();
                $artwork->mediums()->detach();
                $artwork->constituents()->detach();
                $artwork->tags()->detach();
                $artwork->cultures()->detach();
                $artwork->periods()->detach();
                $artwork->dynasties()->detach();
                $artwork->reigns()->detach();
                $artwork->portfolios()->detach();

                // Delete all One-to-Many child records
                $artwork->measurements()->forceDelete();
                $artwork->images()->forceDelete();
                $artwork->geographies()->forceDelete();
                $artwork->exhibitionHistories()->forceDelete();
                $artwork->references()->forceDelete();
                $artwork->artWorkSims()->forceDelete();

                // Finally force delete the parent artwork
                $artwork->forceDelete();
            });

            return redirect()->route('admin.artworks.index', ['status' => 'trashed'])
                ->with('success', 'Artwork permanently deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.artworks.index', ['status' => 'trashed'])
                ->with('error', 'Error permanently deleting artwork: ' . $e->getMessage());
        }
    }

    /**
     * Sync many-to-many relationships
     */
    protected function syncM2MRelationships(ArtWork $artwork, Request $request)
    {
        // Sync simple M2M relationships
        $artwork->materials()->sync($request->input('materials') ?? []);
        $artwork->mediums()->sync($request->input('mediums') ?? []);
        $artwork->tags()->sync($request->input('tags') ?? []);
        $artwork->cultures()->sync($request->input('cultures') ?? []);
        $artwork->periods()->sync($request->input('periods') ?? []);
        $artwork->dynasties()->sync($request->input('dynasties') ?? []);
        $artwork->reigns()->sync($request->input('reigns') ?? []);
        $artwork->portfolios()->sync($request->input('portfolios') ?? []);
    }



    /**
     * Save/update constituents with pivot data (roles, prefixes, suffixes)
     */
    protected function saveConstituents(ArtWork $artwork, Request $request)
    {
        // 1. Resolve inline Constituent if typed
        $constituentId = $request->input('new_constituent_id');
        if ($request->filled('new_constituent')) {
            $name = trim($request->input('new_constituent'));
            $record = Constituent::whereRaw('LOWER(display_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Constituent::create([
                    'display_name' => $name,
                    'alpha_sort' => $name,
                ]);
            }
            $constituentId = $record->constituent_id;
        }

        // Only proceed if a constituent is selected or created
        if ($constituentId) {
            // 2. Resolve inline Role if typed
            $roleId = $request->input('new_constituent_role') ?: null;
            if ($request->filled('new_role')) {
                $roleName = trim($request->input('new_role'));
                $record = ConstituentRole::whereRaw('LOWER(role_name) = ?', [strtolower($roleName)])->first();
                if (!$record) {
                    $record = ConstituentRole::create(['role_name' => $roleName]);
                }
                $roleId = $record->role_id;
            }

            // Fallback default role (required by unique index / DB constraint) if empty
            if (!$roleId) {
                // If role is still empty, we get or create a default role "Contributor"
                $defaultRole = ConstituentRole::whereRaw('LOWER(role_name) = ?', ['contributor'])->first();
                if (!$defaultRole) {
                    $defaultRole = ConstituentRole::create(['role_name' => 'Contributor']);
                }
                $roleId = $defaultRole->role_id;
            }

            // 3. Resolve inline Prefix if typed
            $prefixId = $request->input('new_constituent_prefix') ?: null;
            if ($request->filled('new_prefix')) {
                $prefixName = trim($request->input('new_prefix'));
                $record = ConstituentPrefix::whereRaw('LOWER(prefix_name) = ?', [strtolower($prefixName)])->first();
                if (!$record) {
                    $record = ConstituentPrefix::create(['prefix_name' => $prefixName]);
                }
                $prefixId = $record->prefix_id;
            }

            // 4. Resolve inline Suffix if typed
            $suffixId = $request->input('new_constituent_suffix') ?: null;
            if ($request->filled('new_suffix')) {
                $suffixName = trim($request->input('new_suffix'));
                $record = ConstituentSuffix::whereRaw('LOWER(suffix_name) = ?', [strtolower($suffixName)])->first();
                if (!$record) {
                    $record = ConstituentSuffix::create(['suffix_name' => $suffixName]);
                }
                $suffixId = $record->suffix_id;
            }

            // Pivot Table Unique Index Exists Check
            $exists = $artwork->constituents()
                ->wherePivot('constituent_id', $constituentId)
                ->wherePivot('role_id', $roleId)
                ->exists();

            if (!$exists) {
                $artwork->constituents()->attach($constituentId, [
                    'role_id' => $roleId,
                    'prefix_id' => $prefixId,
                    'suffix_id' => $suffixId,
                    'display_order' => $artwork->constituents()->count() + 1
                ]);
            }
        }
    }

    /**
     * Resolve new inline master records
     */
    private function resolveInlineMasterRecords(Request $request): void
    {
        // 1. Classification
        if ($request->filled('new_classification')) {
            $name = trim($request->input('new_classification'));
            $record = Classification::whereRaw('LOWER(classification_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Classification::create(['classification_name' => $name]);
            }
            $request->merge(['classification_id' => $record->classification_id]);
        }

        // 2. Location
        if ($request->filled('new_location')) {
            $name = trim($request->input('new_location'));
            $record = Location::whereRaw('LOWER(location_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Location::create(['location_name' => $name]);
            }
            $request->merge(['location_id' => $record->location_id]);
        }

        // 3. Repository
        if ($request->filled('new_repository')) {
            $name = trim($request->input('new_repository'));
            $record = Repository::whereRaw('LOWER(repository_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Repository::create(['repository_name' => $name]);
            }
            $request->merge(['repository_id' => $record->repository_id]);
        }

        // 4. Object Type (type_id)
        if ($request->filled('new_object_type')) {
            $name = trim($request->input('new_object_type'));
            $record = ObjectType::whereRaw('LOWER(object_type_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = ObjectType::create(['object_type_name' => $name]);
            }
            $request->merge(['type_id' => $record->type_id]);
        }

        // 5. Department
        if ($request->filled('new_department')) {
            $name = trim($request->input('new_department'));
            $record = Department::whereRaw('LOWER(department_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Department::create(['department_name' => $name]);
            }
            $request->merge(['department_id' => $record->department_id]);
        }

        // 6. Credit Line
        if ($request->filled('new_credit_line')) {
            $name = trim($request->input('new_credit_line'));
            $record = CreditLine::whereRaw('LOWER(credit_line_text) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = CreditLine::create(['credit_line_text' => $name]);
            }
            $request->merge(['credit_line_id' => $record->credit_line_id]);
        }

        // 7. Materials (Many-to-Many inline creation)
        if ($request->filled('new_material')) {
            $name = trim($request->input('new_material'));
            $record = Material::whereRaw('LOWER(material_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Material::create(['material_name' => $name]);
            }
            $materials = (array) $request->input('materials', []);
            $materials[] = $record->material_id;
            $request->merge(['materials' => array_unique($materials)]);
        }

        // 8. Mediums (Many-to-Many inline creation)
        if ($request->filled('new_medium')) {
            $name = trim($request->input('new_medium'));
            $record = Medium::whereRaw('LOWER(medium_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Medium::create(['medium_name' => $name]);
            }
            $mediums = (array) $request->input('mediums', []);
            $mediums[] = $record->medium_id;
            $request->merge(['mediums' => array_unique($mediums)]);
        }

        // 9. Tags (Many-to-Many inline creation)
        if ($request->filled('new_tag')) {
            $name = trim($request->input('new_tag'));
            $record = Tag::whereRaw('LOWER(tag_term) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Tag::create(['tag_term' => $name]);
            }
            $tags = (array) $request->input('tags', []);
            $tags[] = $record->tag_id;
            $request->merge(['tags' => array_unique($tags)]);
        }

        // 10. Cultures (Many-to-Many inline creation)
        if ($request->filled('new_culture')) {
            $name = trim($request->input('new_culture'));
            $record = Culture::whereRaw('LOWER(culture_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Culture::create(['culture_name' => $name]);
            }
            $cultures = (array) $request->input('cultures', []);
            $cultures[] = $record->culture_id;
            $request->merge(['cultures' => array_unique($cultures)]);
        }

        // 11. Periods (Many-to-Many inline creation)
        if ($request->filled('new_period')) {
            $name = trim($request->input('new_period'));
            $record = Period::whereRaw('LOWER(period_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Period::create(['period_name' => $name]);
            }
            $periods = (array) $request->input('periods', []);
            $periods[] = $record->period_id;
            $request->merge(['periods' => array_unique($periods)]);
        }

        // 12. Dynasties (Many-to-Many inline creation)
        if ($request->filled('new_dynasty')) {
            $name = trim($request->input('new_dynasty'));
            $record = Dynasty::whereRaw('LOWER(dynasty_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Dynasty::create(['dynasty_name' => $name]);
            }
            $dynasties = (array) $request->input('dynasties', []);
            $dynasties[] = $record->dynasty_id;
            $request->merge(['dynasties' => array_unique($dynasties)]);
        }

        // 13. Reigns (Many-to-Many inline creation)
        if ($request->filled('new_reign')) {
            $name = trim($request->input('new_reign'));
            $record = Reign::whereRaw('LOWER(reign_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Reign::create(['reign_name' => $name]);
            }
            $reigns = (array) $request->input('reigns', []);
            $reigns[] = $record->reign_id;
            $request->merge(['reigns' => array_unique($reigns)]);
        }

        // 14. Portfolios (Many-to-Many inline creation)
        if ($request->filled('new_portfolio')) {
            $name = trim($request->input('new_portfolio'));
            $record = Portfolio::whereRaw('LOWER(portfolio_name) = ?', [strtolower($name)])->first();
            if (!$record) {
                $record = Portfolio::create(['portfolio_name' => $name]);
            }
            $portfolios = (array) $request->input('portfolios', []);
            $portfolios[] = $record->portfolio_id;
            $request->merge(['portfolios' => array_unique($portfolios)]);
        }
    }

    /**
     * Generate a unique slug for an artwork
     */
    private function generateUniqueSlug(string $title, ?int $excludeArtworkId = null): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($title);
        if (empty($baseSlug)) {
            $baseSlug = 'artwork';
        }
        
        $slug = $baseSlug;
        $counter = 2;
        
        while (true) {
            $query = ArtWork::where('slug', $slug);
            if ($excludeArtworkId !== null) {
                $query->where('art_work_id', '!=', $excludeArtworkId);
            }
            
            if (!$query->exists()) {
                return $slug;
            }
            
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    /**
     * Save child records for measurements, references, and SIMs (Safe Replace Strategy)
     */
    protected function saveChildRecords(ArtWork $artwork, Request $request)
    {
        // 1. Safe Replace: Force-delete existing child records to prevent database bloat
        $artwork->measurements()->forceDelete();
        $artwork->references()->forceDelete();
        $artwork->artWorkSims()->forceDelete();

        // 2. Save Measurements
        if ($request->has('measurements') && is_array($request->input('measurements'))) {
            $order = 1;
            foreach ($request->input('measurements') as $row) {
                $name = isset($row['name']) ? trim($row['name']) : '';
                $val = isset($row['value']) ? trim($row['value']) : '';
                $unit = isset($row['unit']) ? trim($row['unit']) : '';
                $type = isset($row['type']) ? trim($row['type']) : null;

                // Ignore completely empty rows
                if ($name === '' && $val === '' && $unit === '') {
                    continue;
                }

                // Validation: if partially filled, Name, Value, and Unit are required
                if ($name === '' || $val === '' || $unit === '') {
                    throw new \Exception("Measurement Name, Value, and Unit are required when a row is filled.");
                }
                if (!is_numeric($val)) {
                    throw new \Exception("Measurement Value must be a numeric value.");
                }

                $artwork->measurements()->create([
                    'measurement_type' => $type ?: null,
                    'measurement_name' => $name,
                    'measurement_value' => $val,
                    'measurement_unit' => $unit,
                    'display_order' => $order++,
                ]);
            }
        }

        // 3. Save References
        if ($request->has('references') && is_array($request->input('references'))) {
            $order = 1;
            foreach ($request->input('references') as $row) {
                $text = isset($row['text']) ? trim($row['text']) : '';

                // Ignore completely empty rows
                if ($text === '') {
                    continue;
                }

                $artwork->references()->create([
                    'reference_text' => $text,
                    'display_order' => $order++,
                ]);
            }
        }

        // 4. Save SIMs
        if ($request->has('sims') && is_array($request->input('sims'))) {
            foreach ($request->input('sims') as $row) {
                $text = isset($row['text']) ? trim($row['text']) : '';
                $type = isset($row['type']) ? trim($row['type']) : '';

                // Ignore completely empty rows
                if ($text === '') {
                    continue;
                }

                if ($type === '') {
                    throw new \Exception("SIM Type is required when SIM Text is filled.");
                }
                if (!in_array($type, ['Signature', 'Inscription', 'Marking'])) {
                    throw new \Exception("SIM Type must be one of: Signature, Inscription, Marking.");
                }

                $artwork->artWorkSims()->create([
                    'sim_type' => $type,
                    'sim_text' => $text,
                ]);
            }
        }

        // 5. Save Exhibition Histories (Safe Replace)
        $artwork->exhibitionHistories()->forceDelete();

        if ($request->has('exhibition_histories') && is_array($request->input('exhibition_histories'))) {
            $order = 1;
            foreach ($request->input('exhibition_histories') as $row) {
                $title = isset($row['title']) ? trim($row['title']) : '';
                $venue = isset($row['venue']) ? trim($row['venue']) : '';
                $city = isset($row['city']) ? trim($row['city']) : '';
                $dateDisplay = isset($row['date_display']) ? trim($row['date_display']) : '';
                $startDate = isset($row['start_date']) ? trim($row['start_date']) : '';
                $endDate = isset($row['end_date']) ? trim($row['end_date']) : '';
                $catalogue = isset($row['catalogue']) ? trim($row['catalogue']) : '';
                $notes = isset($row['notes']) ? trim($row['notes']) : '';

                // Ignore completely empty rows
                if ($title === '' && $venue === '' && $city === '' && $dateDisplay === '' && $startDate === '' && $endDate === '' && $catalogue === '' && $notes === '') {
                    continue;
                }

                // If partially filled, exhibition_title is required
                if ($title === '') {
                    throw new \Exception("Exhibition Title is required when an exhibition history row is filled.");
                }

                // Validate Date formats if filled
                if ($startDate !== '' && !strtotime($startDate)) {
                    throw new \Exception("Start Date must be a valid date format.");
                }
                if ($endDate !== '' && !strtotime($endDate)) {
                    throw new \Exception("End Date must be a valid date format.");
                }

                $artwork->exhibitionHistories()->create([
                    'exhibition_title' => $title,
                    'venue_name' => $venue ?: null,
                    'city_name' => $city ?: null,
                    'exhibition_date_display' => $dateDisplay ?: null,
                    'start_date' => $startDate ?: null,
                    'end_date' => $endDate ?: null,
                    'catalogue_reference' => $catalogue ?: null,
                    'exhibition_notes' => $notes ?: null,
                    'display_order' => $order++,
                ]);
            }
        }

        // 6. Save Artwork Images (Safe Replace)
        $artwork->images()->forceDelete();

        if ($request->has('images') && is_array($request->input('images'))) {
            $validRows = [];
            $primaryIndex = $request->input('primary_image_index', null);

            foreach ($request->input('images') as $index => $row) {
                $mode = isset($row['mode']) ? $row['mode'] : 'url';
                $url = '';

                if ($mode === 'file') {
                    $file = $request->file("images.$index.file");
                    // If file is uploaded, process it
                    if ($file && $file->isValid()) {
                        $path = $file->store('artworks', 'public');
                        $url = \Illuminate\Support\Facades\Storage::url($path);
                    }
                } elseif ($mode === 'url') {
                    $url = isset($row['url']) ? trim($row['url']) : '';
                }

                // Ignore completely empty rows
                if ($url === '') {
                    continue;
                }

                $validRows[] = [
                    'original_index' => $index,
                    'image_url' => $url,
                ];
            }

            // Determine primary index
            $selectedPrimaryIdx = -1;
            if (count($validRows) > 0) {
                if ($primaryIndex !== null && $primaryIndex !== '') {
                    foreach ($validRows as $vIdx => $vRow) {
                        if ($vRow['original_index'] == $primaryIndex) {
                            $selectedPrimaryIdx = $vIdx;
                            break;
                        }
                    }
                }
                if ($selectedPrimaryIdx === -1) {
                    $selectedPrimaryIdx = 0;
                }
            }

            $order = 1;
            foreach ($validRows as $vIdx => $vRow) {
                $isPrimary = ($vIdx === $selectedPrimaryIdx);

                $artwork->images()->create([
                    'image_url' => $vRow['image_url'],
                    'is_primary' => $isPrimary,
                    'display_order' => $order++,
                ]);
            }
        }

        // 7. Save Geographies (Safe Replace)
        $this->saveGeographies($artwork, $request);
    }

    /**
     * Preprocess geography smart add inputs to resolve or create master records
     */
    private function preprocessGeographies(Request $request): void
    {
        if ($request->has('geographies') && is_array($request->input('geographies'))) {
            $geographies = $request->input('geographies');
            $updated = false;

            foreach ($geographies as $index => $row) {
                // Preprocess geography_type
                $geographyTypeId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\GeographyType::class,
                    'geography_type_name',
                    'geography_type_id',
                    $row['geography_type_new'] ?? null,
                    isset($row['geography_type_id']) && is_numeric($row['geography_type_id']) ? (int)$row['geography_type_id'] : null
                );

                // Preprocess country
                $countryId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\Country::class,
                    'country_name',
                    'country_id',
                    $row['country_new'] ?? null,
                    isset($row['country_id']) && is_numeric($row['country_id']) ? (int)$row['country_id'] : null
                );

                // Preprocess state (requires country_id, fallback to Unknown country)
                $stateCountryId = $countryId;
                if (!$stateCountryId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $stateCountryId = $fallbackCountry->country_id;
                }
                $stateId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\State::class,
                    'state_name',
                    'state_id',
                    $row['state_new'] ?? null,
                    isset($row['state_id']) && is_numeric($row['state_id']) ? (int)$row['state_id'] : null,
                    ['country_id' => $stateCountryId]
                );

                // Preprocess county (requires state_id, fallback to Unknown state)
                $countyStateId = $stateId;
                if (!$countyStateId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $fallbackState = \App\Models\State::firstOrCreate([
                        'state_name' => 'Unknown',
                        'country_id' => $fallbackCountry->country_id
                    ]);
                    $countyStateId = $fallbackState->state_id;
                }
                $countyId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\County::class,
                    'county_name',
                    'county_id',
                    $row['county_new'] ?? null,
                    isset($row['county_id']) && is_numeric($row['county_id']) ? (int)$row['county_id'] : null,
                    ['state_id' => $countyStateId]
                );

                // Preprocess city (requires state_id, fallback to Unknown state)
                $cityStateId = $stateId;
                if (!$cityStateId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $fallbackState = \App\Models\State::firstOrCreate([
                        'state_name' => 'Unknown',
                        'country_id' => $fallbackCountry->country_id
                    ]);
                    $cityStateId = $fallbackState->state_id;
                }
                $cityId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\City::class,
                    'city_name',
                    'city_id',
                    $row['city_new'] ?? null,
                    isset($row['city_id']) && is_numeric($row['city_id']) ? (int)$row['city_id'] : null,
                    ['state_id' => $cityStateId]
                );

                // Preprocess region (requires country_id, fallback to Unknown country)
                $regionCountryId = $countryId;
                if (!$regionCountryId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $regionCountryId = $fallbackCountry->country_id;
                }
                $regionId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\Region::class,
                    'region_name',
                    'region_id',
                    $row['region_new'] ?? null,
                    isset($row['region_id']) && is_numeric($row['region_id']) ? (int)$row['region_id'] : null,
                    ['country_id' => $regionCountryId]
                );

                // Preprocess subregion (requires region_id, fallback to Unknown region)
                $subregionRegionId = $regionId;
                if (!$subregionRegionId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $fallbackRegion = \App\Models\Region::firstOrCreate([
                        'region_name' => 'Unknown',
                        'country_id' => $fallbackCountry->country_id
                    ]);
                    $subregionRegionId = $fallbackRegion->region_id;
                }
                $subregionId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\Subregion::class,
                    'subregion_name',
                    'subregion_id',
                    $row['subregion_new'] ?? null,
                    isset($row['subregion_id']) && is_numeric($row['subregion_id']) ? (int)$row['subregion_id'] : null,
                    ['region_id' => $subregionRegionId]
                );

                // Preprocess locale (requires subregion_id, fallback to Unknown subregion)
                $localeSubregionId = $subregionId;
                if (!$localeSubregionId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $fallbackRegion = \App\Models\Region::firstOrCreate([
                        'region_name' => 'Unknown',
                        'country_id' => $fallbackCountry->country_id
                    ]);
                    $fallbackSubregion = \App\Models\Subregion::firstOrCreate([
                        'subregion_name' => 'Unknown',
                        'region_id' => $fallbackRegion->region_id
                    ]);
                    $localeSubregionId = $fallbackSubregion->subregion_id;
                }
                $localeId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\Locale::class,
                    'locale_name',
                    'locale_id',
                    $row['locale_new'] ?? null,
                    isset($row['locale_id']) && is_numeric($row['locale_id']) ? (int)$row['locale_id'] : null,
                    ['subregion_id' => $localeSubregionId]
                );

                // Preprocess locus (requires locale_id, fallback to Unknown locale)
                $locusLocaleId = $localeId;
                if (!$locusLocaleId) {
                    $fallbackCountry = \App\Models\Country::firstOrCreate(['country_name' => 'Unknown']);
                    $fallbackRegion = \App\Models\Region::firstOrCreate([
                        'region_name' => 'Unknown',
                        'country_id' => $fallbackCountry->country_id
                    ]);
                    $fallbackSubregion = \App\Models\Subregion::firstOrCreate([
                        'subregion_name' => 'Unknown',
                        'region_id' => $fallbackRegion->region_id
                    ]);
                    $fallbackLocale = \App\Models\Locale::firstOrCreate([
                        'locale_name' => 'Unknown',
                        'subregion_id' => $fallbackSubregion->subregion_id
                    ]);
                    $locusLocaleId = $fallbackLocale->locale_id;
                }
                $locusId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\Locus::class,
                    'locus_name',
                    'locus_id',
                    $row['locus_new'] ?? null,
                    isset($row['locus_id']) && is_numeric($row['locus_id']) ? (int)$row['locus_id'] : null,
                    ['locale_id' => $locusLocaleId]
                );

                // Preprocess excavation
                $excavationId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\Excavation::class,
                    'excavation_name',
                    'excavation_id',
                    $row['excavation_new'] ?? null,
                    isset($row['excavation_id']) && is_numeric($row['excavation_id']) ? (int)$row['excavation_id'] : null
                );

                // Preprocess river
                $riverId = $this->resolveOrCreateGeographyMaster(
                    \App\Models\River::class,
                    'river_name',
                    'river_id',
                    $row['river_new'] ?? null,
                    isset($row['river_id']) && is_numeric($row['river_id']) ? (int)$row['river_id'] : null
                );

                $geographies[$index]['geography_type_id'] = $geographyTypeId;
                $geographies[$index]['country_id'] = $countryId;
                $geographies[$index]['state_id'] = $stateId;
                $geographies[$index]['county_id'] = $countyId;
                $geographies[$index]['city_id'] = $cityId;
                $geographies[$index]['region_id'] = $regionId;
                $geographies[$index]['subregion_id'] = $subregionId;
                $geographies[$index]['locale_id'] = $localeId;
                $geographies[$index]['locus_id'] = $locusId;
                $geographies[$index]['excavation_id'] = $excavationId;
                $geographies[$index]['river_id'] = $riverId;
                $updated = true;
            }

            if ($updated) {
                $request->merge(['geographies' => $geographies]);
            }
        }
    }

    /**
     * Resolve or create a master geography record case-insensitively
     */
    private function resolveOrCreateGeographyMaster(string $modelClass, string $nameField, string $pkField, ?string $newValue, ?int $existingId, array $extraAttributes = []): ?int
    {
        $newValue = trim($newValue ?? '');
        if ($newValue !== '') {
            // Use withTrashed() to ensure we find and reuse soft-deleted master records case-insensitively
            $query = $modelClass::withTrashed()->whereRaw("LOWER($nameField) = ?", [strtolower($newValue)]);
            
            // Scope lookup by parent IDs to handle hierarchy unique constraints (e.g. parent_id + name)
            foreach (['country_id', 'state_id', 'region_id', 'subregion_id', 'locale_id'] as $parentKey) {
                if (isset($extraAttributes[$parentKey])) {
                    $query->where($parentKey, $extraAttributes[$parentKey]);
                }
            }

            $record = $query->first();
            if ($record) {
                // If it was soft-deleted, restore it to active status
                if (method_exists($record, 'trashed') && $record->trashed()) {
                    $record->restore();
                }
            } else {
                $record = $modelClass::create(array_merge([$nameField => $newValue], $extraAttributes));
            }
            return $record->$pkField;
        }
        return $existingId ?: null;
    }

    /**
     * Save child geography records using the Safe Replace Strategy
     */
    protected function saveGeographies(ArtWork $artwork, Request $request): void
    {
        $artwork->geographies()->forceDelete();

        if ($request->has('geographies') && is_array($request->input('geographies'))) {
            foreach ($request->input('geographies') as $row) {
                $geographyTypeId = isset($row['geography_type_id']) && is_numeric($row['geography_type_id']) ? (int)$row['geography_type_id'] : null;
                $countryId = isset($row['country_id']) && is_numeric($row['country_id']) ? (int)$row['country_id'] : null;
                $stateId = isset($row['state_id']) && is_numeric($row['state_id']) ? (int)$row['state_id'] : null;
                $countyId = isset($row['county_id']) && is_numeric($row['county_id']) ? (int)$row['county_id'] : null;
                $cityId = isset($row['city_id']) && is_numeric($row['city_id']) ? (int)$row['city_id'] : null;
                $regionId = isset($row['region_id']) && is_numeric($row['region_id']) ? (int)$row['region_id'] : null;
                $subregionId = isset($row['subregion_id']) && is_numeric($row['subregion_id']) ? (int)$row['subregion_id'] : null;
                $localeId = isset($row['locale_id']) && is_numeric($row['locale_id']) ? (int)$row['locale_id'] : null;
                $locusId = isset($row['locus_id']) && is_numeric($row['locus_id']) ? (int)$row['locus_id'] : null;
                $excavationId = isset($row['excavation_id']) && is_numeric($row['excavation_id']) ? (int)$row['excavation_id'] : null;
                $riverId = isset($row['river_id']) && is_numeric($row['river_id']) ? (int)$row['river_id'] : null;

                // Ignore completely empty row
                if (!$geographyTypeId && !$countryId && !$stateId && !$countyId && !$cityId && !$regionId && !$subregionId && !$localeId && !$locusId && !$excavationId && !$riverId) {
                    continue;
                }

                $artwork->geographies()->create([
                    'geography_type_id' => $geographyTypeId,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'county_id' => $countyId,
                    'city_id' => $cityId,
                    'region_id' => $regionId,
                    'subregion_id' => $subregionId,
                    'locale_id' => $localeId,
                    'locus_id' => $locusId,
                    'excavation_id' => $excavationId,
                    'river_id' => $riverId,
                ]);
            }
        }
    }
}



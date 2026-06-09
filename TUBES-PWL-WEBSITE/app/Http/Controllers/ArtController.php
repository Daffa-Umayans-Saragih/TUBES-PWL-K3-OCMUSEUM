<?php
namespace App\Http\Controllers;

use App\Models\ArtWork;
use App\Models\Department;
use App\Models\Location;
use App\Models\Material;
use App\Models\Medium;
use App\Models\ObjectType;
use Illuminate\Http\Request;

class ArtController extends Controller
{
    private function getDepartments()
    {
        return Department::orderBy('department_name')->get(['department_id', 'department_name']);
    }

    private function getDepartmentNavigationItems(): array
    {
        $items = [
            ['name' => 'African Art in The Michael C. Rockefeller Wing', 'image' => asset('images/african-wings.jpg')],
            ['name' => 'The American Wing', 'image' => asset('images/american-wing.jpg')],
            ['name' => 'Ancient American Art in The Michael C. Rockefeller Wing', 'image' => asset('images/ancient-american.jpg')],
            ['name' => 'Ancient West Asian Art', 'image' => asset('images/ancient-westAsian.jpg')],
            ['name' => 'Arms and Armor', 'image' => asset('images/arms-and-armor.jpg')],
            ['name' => 'Asian Art', 'image' => asset('images/asian-art.jpg')],
            ['name' => 'The Costume Institute', 'image' => asset('images/costume-institute.jpg')],
            ['name' => 'Drawings and Prints', 'image' => asset('images/drawing-and-prints.jpg')],
            ['name' => 'Egyptian Art', 'image' => asset('images/egyptian-art.jpg')],
            ['name' => 'European Paintings', 'image' => asset('images/europhean-painting.jpg')],
            ['name' => 'European Sculpture and Decorative Arts', 'image' => asset('images/european-sculpture.jpg')],
            ['name' => 'Greek and Roman Art', 'image' => asset('images/greek-and-roman.jpg')],
            ['name' => 'Islamic Art', 'image' => asset('images/islamic-art.jpg')],
            ['name' => 'The Robert Lehman Collection', 'image' => asset('images/robert-lehman.jpg')],
            ['name' => 'Thomas j. Watson Library', 'image' => asset('images/thomas-j-watson.jpg')],
            ['name' => 'Medieval Art and The Cloisters', 'image' => asset('images/medieval-art.jpg')],
            ['name' => 'Modern and Contemporary Art', 'image' => asset('images/modern-contemporary.jpg')],
            ['name' => 'Musical instruments', 'image' => asset('images/musical-instrument.jpg')],
            ['name' => 'Oceanic Art in The Michael C. Rokcefeller Wing', 'image' => asset('images/oceanic-art.jpg')],
            ['name' => 'Photographs', 'image' => asset('images/photographs.jpg')],
        ];

        return array_map(function (array $item) {
            $item['href'] = route('art.search') . '?department%5B%5D=' . urlencode($item['name']);
            $item['alt']  = $item['name'];

            return $item;
        }, $items);
    }

    /**
     * Get filter dropdown data
     */
    private function getFilterData()
    {
        $objectFilters = collect()
            ->merge(ObjectType::orderBy('object_type_name')->get(['type_id', 'object_type_name'])->map(function ($item) {
                return [
                    'value' => $item->object_type_name,
                    'label' => $item->object_type_name,
                    'group' => 'Object type',
                ];
            }))
            ->merge(Material::orderBy('material_name')->get(['material_id', 'material_name'])->map(function ($item) {
                return [
                    'value' => $item->material_name,
                    'label' => $item->material_name,
                    'group' => 'Material',
                ];
            }))
            ->merge(Medium::orderBy('medium_name')->get(['medium_id', 'medium_name'])->map(function ($item) {
                return [
                    'value' => $item->medium_name,
                    'label' => $item->medium_name,
                    'group' => 'Medium',
                ];
            }))
            ->unique('value')
            ->values();

        return [
            'departments'   => $this->getDepartments(),
            'types'         => ObjectType::orderBy('object_type_name')->get(['type_id', 'object_type_name']),
            'locations'     => Location::orderBy('location_name')->get(['location_id', 'location_name']),
            'objectFilters' => $objectFilters,
        ];
    }

    /**
     * Display a listing of all artworks / collections with search and filters
     */
    public function index()
    {
        $artworks = ArtWork::query()
            ->with(['department', 'objectType', 'location', 'images'])
            ->paginate(12);

        $filterData = $this->getFilterData();

        return view('ordinary.art.art', array_merge([
            'artworks'                  => $artworks,
            'title'                     => 'Art Collections',
            'search_query'              => '',
            'departmentNavigationItems' => $this->getDepartmentNavigationItems(),
        ], $filterData));
    }

    /**
     * Display a specific artwork detail page
     */
    public function show($id)
    {
        $artwork = ArtWork::with(['department', 'objectType', 'location', 'images', 'references', 'exhibitionHistories', 'artWorkSims'])
            ->where('art_work_id', $id)
            ->firstOrFail();

        return view('ordinary.art.show.show', [
            'artwork' => $artwork,
            'title'   => 'Artwork Detail',
        ]);
    }

    /**
     * Search and filter artworks with complex criteria
     */
    public function search(Request $request)
    {
        $query = ArtWork::query()
            ->with(['department', 'objectType', 'location', 'images', 'constituents', 'cultures', 'creditLine', 'mediums']);
        $departments          = $this->getDepartments();
        $validDepartmentNames = $departments->pluck('department_name')->map(fn($value) => (string) $value);

        // Canonical Field Mapping
        $fieldMap = [
            'all'           => 'All Fields',
            'artist'        => 'Artist / Culture',
            'title'         => 'Title',
            'description'   => 'Description',
            'gallery'       => 'Gallery',
            'object_number' => 'Object Number',
            'credit_line'   => 'Credit Line',
        ];

        // Read Search State
        $keyword           = $request->input('q');
        $currentField      = $request->input('field', 'all');
        $currentFieldLabel = $fieldMap[$currentField] ?? 'All Fields';

        if ($keyword) {
            $query->where(function ($q) use ($keyword, $currentField) {
                if ($currentField === 'title' || $currentField === 'all') {
                    $q->orWhere('title', 'like', "%{$keyword}%");
                }
                if ($currentField === 'description' || $currentField === 'all') {
                    $q->orWhere('description', 'like', "%{$keyword}%");
                }
                if ($currentField === 'credit_line' || $currentField === 'all') {
                    $q->orWhereHas('creditLine', function ($sub) use ($keyword) {
                        $sub->where('credit_line_text', 'like', "%{$keyword}%");
                    });
                }
                if ($currentField === 'gallery' || $currentField === 'all') {
                    $q->orWhere('gallery_number', 'like', "%{$keyword}%");
                }
                if ($currentField === 'object_number' || $currentField === 'all') {
                    $q->orWhere('accession_number', 'like', "%{$keyword}%");
                }
                if ($currentField === 'artist' || $currentField === 'all') {
                    $q->orWhereHas('constituents', function ($sub) use ($keyword) {
                        $sub->where('display_name', 'like', "%{$keyword}%");
                    })->orWhereHas('cultures', function ($sub) use ($keyword) {
                        $sub->where('culture_name', 'like', "%{$keyword}%");
                    });
                }
            });
        }

        // Canonical checkbox filters (UI states from the search page)
        $showOnlyHighlights = $request->boolean('highlights') || $request->boolean('highlights_adv');
        if ($showOnlyHighlights) {
            $query->where('is_highlight', true);
        }

        $showOnlyOnView = $request->boolean('on_view') || $request->boolean('on_view_adv');
        if ($showOnlyOnView) {
            $query->where('is_on_view', true);
        }

        if ($request->boolean('has_image')) {
            $query->whereHas('images');
        }

        if ($request->boolean('open_access')) {
            $query->where('is_public_domain', true);
        }

        if ($request->boolean('has_3d')) {
            $query->where(function ($subQuery) {
                $subQuery->whereNotNull('object_url')
                    ->orWhereNotNull('object_wikidata_url');
            });
        }

        $selectedObjectTerms = collect($request->input('object_type', []))
            ->filter()
            ->map(fn($value) => trim((string) $value))
            ->unique()
            ->values();

        if ($selectedObjectTerms->isNotEmpty()) {
            $query->where(function ($subQuery) use ($selectedObjectTerms) {
                $subQuery->whereHas('objectType', function ($relation) use ($selectedObjectTerms) {
                    $relation->whereIn('object_type_name', $selectedObjectTerms->all());
                })
                    ->orWhereHas('materials', function ($relation) use ($selectedObjectTerms) {
                        $relation->whereIn('material_name', $selectedObjectTerms->all());
                    })
                    ->orWhereHas('mediums', function ($relation) use ($selectedObjectTerms) {
                        $relation->whereIn('medium_name', $selectedObjectTerms->all());
                    });
            });
        }

        $selectedMediumIds = collect($request->input('medium', []))
            ->filter()
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values();

        if ($selectedMediumIds->isNotEmpty()) {
            $query->whereHas('mediums', function ($relation) use ($selectedMediumIds) {
                $relation->whereIn('mediums.medium_id', $selectedMediumIds->all());
            });
        }

        $selectedDepartments = collect($request->input('department', []))
            ->filter()
            ->map(fn($value) => trim((string) $value))
            ->unique()
            ->values();

        $selectedDepartments = $selectedDepartments->filter(function ($departmentName) use ($validDepartmentNames) {
            return $validDepartmentNames->contains($departmentName);
        })->values();

        if ($selectedDepartments->isNotEmpty()) {
            $query->whereHas('department', function ($relation) use ($selectedDepartments) {
                $relation->whereIn('department_name', $selectedDepartments->all());
            });
        }

        $selectedLocations = collect($request->input('location', []))
            ->filter()
            ->map(fn($value) => trim((string) $value))
            ->unique()
            ->values();

        if ($selectedLocations->isNotEmpty()) {
            $query->whereHas('location', function ($relation) use ($selectedLocations) {
                $relation->whereIn('location_name', $selectedLocations->all());
            });
        }

        // Filter by department_id
        $departmentId = $request->input('department_id');
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Filter by object type_id
        $typeId = $request->input('type_id');
        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        // Filter by year range - object_begin_date
        $yearStart = $request->input('year_start');
        if ($yearStart) {
            $query->where('object_end_date', '>=', (int) $yearStart);
        }

        // Filter by year range - object_end_date
        $yearEnd = $request->input('year_end');
        if ($yearEnd) {
            $query->where('object_begin_date', '<=', (int) $yearEnd);
        }

        $sort = $request->input('sort', 'relevance');
        switch ($sort) {
            case 'date_newest':
                $query->orderByDesc('object_begin_date')->orderByDesc('art_work_id');
                break;
            case 'date_oldest':
                $query->orderBy('object_begin_date')->orderBy('art_work_id');
                break;
            case 'artist':
                $query->orderByRaw(
                    "COALESCE((select c.display_name from constituents c inner join art_work_constituents awc on awc.constituent_id = c.constituent_id where awc.art_work_id = art_works.art_work_id order by awc.display_order asc, c.display_name asc limit 1), title) asc"
                );
                break;
            case 'title':
                $query->orderBy('title');
                break;
            default:
                $query->orderByDesc('art_work_id');
                break;
        }

        // Execute query with pagination
        $artworks = $query->paginate(12)->appends($request->query());

        $filterData = $this->getFilterData();

        return view('ordinary.art.search.search', array_merge([
            'artworks'             => $artworks,
            'totalResults'         => $artworks->total(),
            'title'                => 'Search Results',
            'search_query'         => $keyword ?? '',
            'currentField'         => $currentField,
            'currentFieldLabel'    => $currentFieldLabel,
            'currentSort'          => $request->input('sort', 'relevance'),
            'selected_departments' => $selectedDepartments->all(),
            'selected_type'        => $typeId,
            'selected_mediums'     => $selectedMediumIds->all(),
        ], $filterData));
    }

    /**
     * Display curatorial areas / departments page
     */
    public function curatorialAreas()
    {
        return view('ordinary.art.curatorial-areas', [
            'departmentNavigationItems' => $this->getDepartmentNavigationItems(),
        ]);
    }
}

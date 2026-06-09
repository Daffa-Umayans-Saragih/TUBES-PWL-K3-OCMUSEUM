<?php
namespace App\Http\Controllers;

use App\Http\Requests\FilterArtworkRequest;
use App\Models\ArtWork;
use App\Models\Department;
use App\Models\ObjectType;
use Illuminate\Support\Facades\Cache;

class ArtWorkController extends Controller
{
    private $cacheTTL = 3600;

    public function index(FilterArtworkRequest $request)
    {
        $perPage  = $request->input('per_page', 24);
        $cacheKey = $request->getCacheKey();

        $fetchData = function () use ($request, $perPage) {
            $query = ArtWork::query()
                ->with([
                    'department',
                    'creditLine',
                    'images' => function ($q) {
                        $q->where('is_primary', true);
                    },
                    'constituents',
                    'mediums',
                ])
                ->whereHas('images', function ($q) {
                    $q->where('is_primary', true);
                })
                ->when($request->filled('department_id'), function ($q) use ($request) {
                    return $q->where('department_id', $request->input('department_id'));
                })
                ->when($request->filled('type_id'), function ($q) use ($request) {
                    return $q->where('type_id', $request->input('type_id'));
                })
                ->when($request->filled('object_begin_date') && $request->filled('object_end_date'), function ($q) use ($request) {
                    $yearStart = intval($request->input('object_begin_date'));
                    $yearEnd   = intval($request->input('object_end_date'));
                    return $q->orWhereBetween('object_begin_date', [$yearStart, $yearEnd])
                        ->orWhereBetween('object_end_date', [$yearStart, $yearEnd]);
                })
                ->when($request->filled('search'), function ($q) use ($request) {
                    $searchTerm = '%' . $request->input('search') . '%';
                    return $q->where(function ($query) use ($searchTerm) {
                        $query->where('title', 'LIKE', $searchTerm)
                            ->orWhere('description', 'LIKE', $searchTerm);
                    });
                })
                ->orderBy('art_work_id', 'DESC');

            $total    = $query->count();
            $artworks = $query->paginate($perPage);

            return [
                'artworks' => $artworks,
                'total'    => $total,
            ];
        };

        $data = app()->environment('testing')
            ? $fetchData()
            : Cache::remember($cacheKey, $this->cacheTTL, $fetchData);

        $departments = app()->environment('testing')
            ? Department::all()
            : Cache::remember('departments_all', $this->cacheTTL, function () {return Department::all();});

        $types = app()->environment('testing')
            ? ObjectType::all()
            : Cache::remember('types_all', $this->cacheTTL, function () {return ObjectType::all();});

        $activeFilters = [
            'department_id'     => $request->input('department_id'),
            'type_id'           => $request->input('type_id'),
            'object_begin_date' => $request->input('object_begin_date'),
            'object_end_date'   => $request->input('object_end_date'),
            'search'            => $request->input('search'),
        ];

        $hasActiveFilters = collect($activeFilters)->filter()->isNotEmpty();

        return view('ordinary.art.catalog.catalog', [
            'artworks'         => $data['artworks'],
            'departments'      => $departments,
            'types'            => $types,
            'activeFilters'    => $activeFilters,
            'hasActiveFilters' => $hasActiveFilters,
            'totalResults'     => $data['total'],
        ]);
    }

    public function show($slug)
    {
        try {
            $artwork = Cache::remember('artwork_' . $slug, $this->cacheTTL, function () use ($slug) {
                return ArtWork::where('slug', $slug)
                    ->with([
                        'department', 'objectType', 'classification', 'creditLine', 'location', 'repository',
                        'images', 'measurements', 'references', 'exhibitionHistories',
                        'materials', 'mediums', 'tags', 'cultures', 'periods', 'dynasties', 'reigns', 'portfolios',
                        'constituents.nationalities', 'geographies.country', 'geographies.city', 'geographies.geographyType',
                        'artWorkSims',
                    ])
                    ->firstOrFail();
            });

            $alreadyDisplayedIds = [$artwork->art_work_id];

            // 1. Same Artist
            $sameArtistWorks = collect();
            $artist = $artwork->constituents->first();
            if ($artist) {
                $sameArtistWorks = ArtWork::query()
                    ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                    ->whereHas('constituents', function ($q) use ($artist) {
                        $q->where('constituents.constituent_id', $artist->constituent_id);
                    })
                    ->whereHas('images')
                    ->with([
                        'department',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit(6)
                    ->get();
                $alreadyDisplayedIds = array_merge($alreadyDisplayedIds, $sameArtistWorks->pluck('art_work_id')->all());
            }

            // 2. Same Medium
            $sameMediumWorks = collect();
            $medium = $artwork->mediums->first();
            if ($medium) {
                $sameMediumWorks = ArtWork::query()
                    ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                    ->whereHas('mediums', function ($q) use ($medium) {
                        $q->where('mediums.medium_id', $medium->medium_id);
                    })
                    ->whereHas('images')
                    ->with([
                        'department',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit(6)
                    ->get();
                $alreadyDisplayedIds = array_merge($alreadyDisplayedIds, $sameMediumWorks->pluck('art_work_id')->all());
            }

            // 3. Same Department
            $sameDeptWorks = collect();
            if ($artwork->department_id) {
                $sameDeptWorks = ArtWork::query()
                    ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                    ->where('department_id', $artwork->department_id)
                    ->whereHas('images')
                    ->with([
                        'department',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit(6)
                    ->get();
                $alreadyDisplayedIds = array_merge($alreadyDisplayedIds, $sameDeptWorks->pluck('art_work_id')->all());
            }

            // 4. Same Culture
            $sameCultureWorks = collect();
            $culture = $artwork->cultures->first();
            if ($culture) {
                $sameCultureWorks = ArtWork::query()
                    ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                    ->whereHas('cultures', function ($q) use ($culture) {
                        $q->where('cultures.culture_id', $culture->culture_id);
                    })
                    ->whereHas('images')
                    ->with([
                        'department',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit(6)
                    ->get();
                $alreadyDisplayedIds = array_merge($alreadyDisplayedIds, $sameCultureWorks->pluck('art_work_id')->all());
            }

            // 5. Same Period
            $samePeriodWorks = collect();
            $period = $artwork->periods->first();
            if ($period) {
                $samePeriodWorks = ArtWork::query()
                    ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                    ->whereHas('periods', function ($q) use ($period) {
                        $q->where('periods.period_id', $period->period_id);
                    })
                    ->whereHas('images')
                    ->with([
                        'department',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit(6)
                    ->get();
                $alreadyDisplayedIds = array_merge($alreadyDisplayedIds, $samePeriodWorks->pluck('art_work_id')->all());
            }

            // 6. Same Classification
            $sameClassWorks = collect();
            if ($artwork->classification_id) {
                $sameClassWorks = ArtWork::query()
                    ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                    ->where('classification_id', $artwork->classification_id)
                    ->whereHas('images')
                    ->with([
                        'department',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit(6)
                    ->get();
                $alreadyDisplayedIds = array_merge($alreadyDisplayedIds, $sameClassWorks->pluck('art_work_id')->all());
            }

            // 7. Smart Similarity
            $scoreParts = [];
            $bindings = [];

            if ($artwork->department_id) {
                $scoreParts[] = "CASE WHEN department_id = ? THEN 5 ELSE 0 END";
                $bindings[] = $artwork->department_id;
            }
            
            $mediumIds = $artwork->mediums->pluck('medium_id')->filter()->all();
            if (!empty($mediumIds)) {
                $placeholders = implode(',', array_fill(0, count($mediumIds), '?'));
                $scoreParts[] = "(SELECT COUNT(*) * 4 FROM art_work_mediums WHERE art_work_id = art_works.art_work_id AND medium_id IN ($placeholders))";
                $bindings = array_merge($bindings, $mediumIds);
            }

            if ($artwork->classification_id) {
                $scoreParts[] = "CASE WHEN classification_id = ? THEN 3 ELSE 0 END";
                $bindings[] = $artwork->classification_id;
            }

            $cultureIds = $artwork->cultures->pluck('culture_id')->filter()->all();
            if (!empty($cultureIds)) {
                $placeholders = implode(',', array_fill(0, count($cultureIds), '?'));
                $scoreParts[] = "(SELECT COUNT(*) * 3 FROM art_work_cultures WHERE art_work_id = art_works.art_work_id AND culture_id IN ($placeholders))";
                $bindings = array_merge($bindings, $cultureIds);
            }

            $periodIds = $artwork->periods->pluck('period_id')->filter()->all();
            if (!empty($periodIds)) {
                $placeholders = implode(',', array_fill(0, count($periodIds), '?'));
                $scoreParts[] = "(SELECT COUNT(*) * 2 FROM art_work_periods WHERE art_work_id = art_works.art_work_id AND period_id IN ($placeholders))";
                $bindings = array_merge($bindings, $periodIds);
            }

            $tagIds = $artwork->tags->pluck('tag_id')->filter()->all();
            if (!empty($tagIds)) {
                $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
                $scoreParts[] = "(SELECT COUNT(*) * 2 FROM art_work_tags WHERE art_work_id = art_works.art_work_id AND tag_id IN ($placeholders))";
                $bindings = array_merge($bindings, $tagIds);
            }

            if ($artwork->type_id) {
                $scoreParts[] = "CASE WHEN type_id = ? THEN 1 ELSE 0 END";
                $bindings[] = $artwork->type_id;
            }

            $scoreSql = empty($scoreParts) ? "0" : implode(" + ", $scoreParts);

            // Fetch high-scoring related artworks excluding already displayed
            $subQuery = ArtWork::query()
                ->whereNotIn('art_work_id', $alreadyDisplayedIds)
                ->whereHas('images')
                ->selectRaw("art_works.*, ($scoreSql) as similarity_score", $bindings);

            $relatedArtworks = ArtWork::withTrashed()
                ->fromSub($subQuery, 'scored_artworks')
                ->where('similarity_score', '>', 0)
                ->orderByDesc('similarity_score')
                ->with([
                    'department',
                    'classification',
                    'cultures',
                    'images' => function ($query) {
                        $query->where('is_primary', true);
                    },
                    'constituents',
                ])
                ->limit(6)
                ->get();

            // Fallback: if similarity yields fewer than 4 items, backfill with same department excluding already displayed
            if ($relatedArtworks->count() < 4 && $artwork->department_id) {
                $fallbackCount = 6 - $relatedArtworks->count();
                $fallback = ArtWork::query()
                    ->whereNotIn('art_work_id', array_merge($alreadyDisplayedIds, $relatedArtworks->pluck('art_work_id')->all()))
                    ->where('department_id', $artwork->department_id)
                    ->whereHas('images')
                    ->with([
                        'department',
                        'classification',
                        'cultures',
                        'images' => function ($query) {
                            $query->where('is_primary', true);
                        },
                        'constituents',
                    ])
                    ->limit($fallbackCount)
                    ->get();
                
                $relatedArtworks = $relatedArtworks->concat($fallback);
            }

            return view('ordinary.art.detail.detail', compact(
                'artwork',
                'sameArtistWorks',
                'sameMediumWorks',
                'sameDeptWorks',
                'sameCultureWorks',
                'samePeriodWorks',
                'sameClassWorks',
                'relatedArtworks'
            ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Artwork not found');
        }
    }
}

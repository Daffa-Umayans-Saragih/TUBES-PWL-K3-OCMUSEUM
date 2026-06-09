@extends('layouts.app')

@section('title', 'Search for Art - OC Collection')

@push('styles')
    @vite(['resources/css/ordinary/art/search/search.css'])
@endpush

@section('content')
    <div class="search-art-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ url('/') }}">🏠</a>
            <span>/</span>
            <a href="{{ route('art.index') }}">OC Collection</a>
            <span>/</span>
            <span class="current">Search Art</span>
        </nav>

        <!-- Header -->
        <h1 class="page-title">Search for Art</h1>

        @php
            $searchQuery = request('q');
            $queryParams = request()->query();

            $normalizeList = function ($value) {
                return array_values(array_filter(array_map('strval', (array) $value), function ($item) {
                    return trim($item) !== '';
                }));
            };

            $removeKeys = function (array $params, array $keys) {
                foreach ($keys as $key) {
                    unset($params[$key]);
                }

                return $params;
            };

            $removeValue = function (array $params, string $key, $value) {
                $current = $params[$key] ?? [];

                if (!is_array($current)) {
                    $current = [$current];
                }

                $current = array_values(array_filter($current, function ($item) use ($value) {
                    return (string) $item !== (string) $value;
                }));

                if (empty($current)) {
                    unset($params[$key]);
                } else {
                    $params[$key] = $current;
                }

                return $params;
            };

            $buildSearchUrl = function (array $params) use ($searchQuery) {
                $params = array_filter($params, function ($value) {
                    if (is_array($value)) {
                        return !empty($value);
                    }

                    return $value !== null && $value !== '';
                });

                $params['q'] = $searchQuery;
                $params = array_filter($params, function ($value, $key) {
                    if ($key === 'q') {
                        return $value !== null && $value !== '';
                    }

                    return true;
                }, ARRAY_FILTER_USE_BOTH);

                return url('/art/collection/search') . (empty($params) ? '' : '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
            };

            $selectedHighlights = request()->boolean('highlights') || request()->boolean('highlights_adv');
            $selectedOnView = request()->boolean('on_view') || request()->boolean('on_view_adv');
            $selectedHasImage = request()->boolean('has_image');
            $selectedOpenAccess = request()->boolean('open_access');
            $selectedHas3d = request()->boolean('has_3d');
            $selectedYearStart = request()->filled('year_start');
            $selectedYearEnd = request()->filled('year_end');

            $selectedObjectTypes = $normalizeList(request()->input('object_type', []));
            $selectedMediums = $normalizeList(request()->input('medium', []));
            $selectedLocations = $normalizeList(request()->input('location', []));
            $selectedDepartments = $normalizeList(request()->input('department', []));

            $showOnlyCount = (int) $selectedHighlights + (int) $selectedOnView;
            $imagesCount = (int) $selectedHasImage + (int) $selectedOpenAccess + (int) $selectedHas3d;
            $dateCount = (int) ($selectedYearStart || $selectedYearEnd);
            $objectCount = count($selectedObjectTypes) + count($selectedMediums);
            $locationCount = count($selectedLocations);
            $departmentCount = count($selectedDepartments);

            $activeFilterChips = [];

            foreach ($selectedObjectTypes as $value) {
                $activeFilterChips[] = [
                    'label' => $value,
                    'href' => $buildSearchUrl($removeValue($removeKeys($queryParams, ['page']), 'object_type', $value)),
                ];
            }

            foreach ($selectedLocations as $value) {
                $activeFilterChips[] = [
                    'label' => $value,
                    'href' => $buildSearchUrl($removeValue($removeKeys($queryParams, ['page']), 'location', $value)),
                ];
            }

            foreach ($selectedDepartments as $value) {
                $activeFilterChips[] = [
                    'label' => $value,
                    'href' => $buildSearchUrl($removeValue($removeKeys($queryParams, ['page']), 'department', $value)),
                ];
            }

            foreach ($selectedMediums as $value) {
                $activeFilterChips[] = [
                    'label' => $value,
                    'href' => $buildSearchUrl($removeValue($removeKeys($queryParams, ['page']), 'medium', $value)),
                ];
            }

            if ($selectedHighlights) {
                $activeFilterChips[] = [
                    'label' => 'Highlights',
                    'href' => $buildSearchUrl($removeKeys($queryParams, ['page', 'highlights', 'highlights_adv'])),
                ];
            }

            if ($selectedOnView) {
                $activeFilterChips[] = [
                    'label' => 'On view',
                    'href' => $buildSearchUrl($removeKeys($queryParams, ['page', 'on_view', 'on_view_adv'])),
                ];
            }

            if ($selectedHasImage) {
                $activeFilterChips[] = [
                    'label' => 'Has image',
                    'href' => $buildSearchUrl($removeKeys($queryParams, ['page', 'has_image'])),
                ];
            }

            if ($selectedOpenAccess) {
                $activeFilterChips[] = [
                    'label' => 'Has Open Access image',
                    'href' => $buildSearchUrl($removeKeys($queryParams, ['page', 'open_access'])),
                ];
            }

            if ($selectedHas3d) {
                $activeFilterChips[] = [
                    'label' => 'Has 3D image',
                    'href' => $buildSearchUrl($removeKeys($queryParams, ['page', 'has_3d'])),
                ];
            }

            if ($selectedYearStart || $selectedYearEnd) {
                $yearParts = [];

                if ($selectedYearStart) {
                    $yearParts[] = request('year_start');
                }

                if ($selectedYearEnd) {
                    $yearParts[] = request('year_end');
                }

                $activeFilterChips[] = [
                    'label' => 'Year: ' . implode(' - ', $yearParts),
                    'href' => $buildSearchUrl($removeKeys($queryParams, ['page', 'year_start', 'year_end'])),
                ];
            }

            $clearAllUrl = $buildSearchUrl(['q' => $searchQuery]);
        @endphp

        <!-- Search Bar -->
        <div class="search-bar-wrapper">
            <div class="search-bar">
                <!-- Field Dropdown -->
                <div class="field-dropdown">
                    <button type="button" class="dropdown-toggle" id="fieldDropdownToggle">
                        <span id="selectedField">{{ $currentFieldLabel }}</span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="dropdown-menu" id="fieldDropdownMenu">
                        @php
                            $fields = [
                                'all' => 'All Fields',
                                'artist' => 'Artist / Culture',
                                'title' => 'Title',
                                'description' => 'Description',
                                'gallery' => 'Gallery',
                                'object_number' => 'Object Number',
                                'credit_line' => 'Credit Line'
                            ];
                        @endphp
                        @foreach($fields as $val => $label)
                            <a href="#" class="dropdown-item {{ ($currentField ?? 'all') === $val ? 'active' : '' }}"
                                data-field="{{ $val }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>

                <!-- Hidden field to track search category -->
                <input type="hidden" name="field" id="selectedFieldInput" value="{{ $currentField ?? 'all' }}">

                <!-- Search Input -->
                <input type="text" class="search-input" id="searchInput" placeholder="Search all fields"
                    value="{{ request('q', '') }}">

                <!-- Search Button -->
                <button type="button" class="search-btn" id="searchBtn">
                    🔍
                </button>
            </div>

            <!-- Quick Filters -->
            <div class="quick-filters">
                <button type="button" class="filters-btn" id="filtersToggle">
                    Filters
                    <span class="filters-arrow">▼</span>
                </button>

                <div class="filter-checkboxes">
                    <label class="checkbox-label">
                        <input type="checkbox" name="highlights" {{ request('highlights') || request('highlights_adv') ? 'checked' : '' }}>
                        <span>Highlights</span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="on_view" {{ request('on_view') || request('on_view_adv') ? 'checked' : '' }}>
                        <span>On view</span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="has_image" {{ request('has_image') ? 'checked' : '' }}>
                        <span>Has image</span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="open_access" {{ request('open_access') ? 'checked' : '' }}>
                        <span>Has Open Access image</span>
                        <span class="info-icon" title="Open Access images">ⓘ</span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="has_3d" {{ request('has_3d') ? 'checked' : '' }}>
                        <span>Has 3D image</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Main Content Layout (Sidebar + Results) -->
        <div class="search-content-layout" id="searchContentLayout">
            <!-- Advanced Filters Sidebar -->
            <div class="filters-panel" id="filtersPanel">
                <div class="filters-sidebar">
                    <div class="filter-section expanded">
                        <h3 class="filter-section-title">
                            <span>Show only</span>
                            @if($showOnlyCount > 0)
                                <span class="filter-count-badge">({{ $showOnlyCount }})</span>
                            @endif
                            <span class="section-arrow rotated">▼</span>
                        </h3>
                        <div class="filter-options">
                            <label class="filter-option">
                                <input type="checkbox" name="highlights_adv" value="1" {{ request('highlights') || request('highlights_adv') ? 'checked' : '' }}>
                                <span>Highlights</span>
                                <span class="count">(2,804)</span>
                            </label>
                            <label class="filter-option">
                                <input type="checkbox" name="on_view_adv" value="1" {{ request('on_view') || request('on_view_adv') ? 'checked' : '' }}>
                                <span>On view</span>
                                <span class="count">(45,500)</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-section-title">
                            <span>Images</span>
                            @if($imagesCount > 0)
                                <span class="filter-count-badge">({{ $imagesCount }})</span>
                            @endif
                            <span class="section-arrow">▼</span>
                        </h3>
                        <div class="filter-options">
                            <label class="filter-option">
                                <input type="checkbox" name="has_image" value="1" {{ request('has_image') ? 'checked' : '' }}>
                                <span>Has image</span>
                                <span class="count">(369,695)</span>
                            </label>
                            <label class="filter-option">
                                <input type="checkbox" name="open_access" value="1" {{ request('open_access') ? 'checked' : '' }}>
                                <span>Has Open Access image</span>
                                <span class="count">(259,682)</span>
                            </label>
                            <label class="filter-option">
                                <input type="checkbox" name="has_3d" value="1" {{ request('has_3d') ? 'checked' : '' }}>
                                <span>Has 3D image</span>
                                <span class="count">(142)</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-section-title">
                            <span>Date</span>
                            @if($dateCount > 0)
                                <span class="filter-count-badge">({{ $dateCount }})</span>
                            @endif
                            <span class="section-arrow">▼</span>
                        </h3>
                        <div class="filter-options">
                            <div class="date-filter-group">
                                <div class="date-input-wrapper">
                                    <label>From</label>
                                    <input type="text" name="year_start" class="date-input" placeholder="-400,000"
                                        value="{{ request('year_start') }}">
                                </div>
                                <div class="date-input-wrapper">
                                    <label>To</label>
                                    <input type="text" name="year_end" class="date-input" placeholder="Present"
                                        value="{{ request('year_end') }}">
                                </div>
                            </div>
                            <p class="date-helper-text">For BCE dates, enter a negative number. E.g. For 2000 BCE, enter
                                -2000.</p>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-section-title">
                            <span>Object type / material</span>
                            @if($objectCount > 0)
                                <span class="filter-count-badge">({{ $objectCount }})</span>
                            @endif
                            <span class="section-arrow">▼</span>
                        </h3>
                        <div class="filter-options scrollable-filter">
                            @forelse(($objectFilters ?? []) as $option)
                                <label class="filter-option">
                                    <input type="checkbox" name="object_type[]" value="{{ $option['value'] }}" {{ in_array((string) $option['value'], array_map('strval', request('object_type', []))) ? 'checked' : '' }}>
                                    <span>{{ $option['label'] }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-gray-500">No object types or materials available.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-section-title">
                            <span>Geographic location</span>
                            @if($locationCount > 0)
                                <span class="filter-count-badge">({{ $locationCount }})</span>
                            @endif
                            <span class="section-arrow">▼</span>
                        </h3>
                        <div class="filter-options scrollable-filter">
                            @forelse(($locations ?? []) as $location)
                                <label class="filter-option">
                                    <input type="checkbox" name="location[]" value="{{ $location->location_name }}" {{ in_array((string) $location->location_name, array_map('strval', request('location', []))) ? 'checked' : '' }}>
                                    <span>{{ $location->location_name }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-gray-500">No locations available.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-section-title">
                            <span>Department</span>
                            @if($departmentCount > 0)
                                <span class="filter-count-badge">({{ $departmentCount }})</span>
                            @endif
                            <span class="section-arrow">▼</span>
                        </h3>
                        <div class="filter-options scrollable-filter">
                            @forelse(($departments ?? []) as $department)
                                <label class="filter-option">
                                    <input type="checkbox" name="department[]" value="{{ $department->department_name }}" {{ in_array((string) $department->department_name, array_map('strval', request('department', []))) ? 'checked' : '' }}>
                                    <span>{{ $department->department_name }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-gray-500">No departments available.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

            <!-- Results Area -->
            <div class="results-area">
                @if(!empty($activeFilterChips))
                    <div class="active-filters-bar">
                        <div class="active-filters-label">Selected filters</div>
                        <div class="active-filter-chips">
                            @foreach($activeFilterChips as $chip)
                                <a href="{{ $chip['href'] }}" class="active-filter-chip">
                                    <span>{{ $chip['label'] }}</span>
                                    <span aria-hidden="true">×</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ $clearAllUrl }}" class="clear-all-link">Clear all</a>
                    </div>
                @endif

                <!-- Results Info -->
                <div class="results-info">
                    <div class="results-count">
                        1-42 of {{ number_format($totalResults ?? 534724) }} results
                    </div>

                    <div class="sort-wrapper">
                        <label for="sortBy">Sort by:</label>
                        <select id="sortBy" class="sort-select">
                            <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevance
                            </option>
                            <option value="date_newest" {{ request('sort') == 'date_newest' ? 'selected' : '' }}>Date (Newest)
                            </option>
                            <option value="date_oldest" {{ request('sort') == 'date_oldest' ? 'selected' : '' }}>Date (Oldest)
                            </option>
                            <option value="artist" {{ request('sort') == 'artist' ? 'selected' : '' }}>Artist</option>
                            <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title</option>
                        </select>
                    </div>
                </div>

                <!-- Art Grid -->
                <div class="art-grid">
                    @forelse($artworks ?? [] as $artwork)
                        <a href="{{ $artwork->slug ? route('artwork.show', $artwork->slug) : '#' }}" class="art-card" {!! !$artwork->slug ? 'onclick="return false;" style="cursor: default;"' : '' !!}>
                            <div class="art-image-wrapper">
                                <img src="{{ $artwork->resolved_image_url ?? asset('images/placeholder.jpg') }}"
                                    alt="{{ $artwork->title }}" class="art-image">
                            </div>
                            <div class="art-info">
                                <h3 class="art-title">{{ $artwork->title }}</h3>
                                <p class="art-artist">
                                    {{ $artwork->constituents->first()->display_name ?? ($artwork->cultures->first()->culture_name ?? 'Unknown') }}
                                </p>
                                <p class="art-date">{{ $artwork->object_date_display ?? 'Unknown' }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <h3>No artworks found</h3>
                            <p>Try adjusting your search or filters to find what you're looking for.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(isset($artworks) && $artworks->hasPages())
                    <div class="pagination">
                        {{ $artworks->links() }}
                    </div>
                @endif
            </div> <!-- End Results Area -->
        </div> <!-- End Main Content Layout -->
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Field Dropdown Toggle
            const fieldDropdownToggle = document.getElementById('fieldDropdownToggle');
            const fieldDropdownMenu = document.getElementById('fieldDropdownMenu');
            const selectedField = document.getElementById('selectedField');
            const searchInput = document.getElementById('searchInput');

            fieldDropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                fieldDropdownMenu.classList.toggle('show');
                this.classList.toggle('active');
            });

            // Field Selection
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            const selectedFieldInput = document.getElementById('selectedFieldInput');

            dropdownItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const field = this.dataset.field;
                    const text = this.textContent;

                    // Update UI
                    selectedField.textContent = text;
                    searchInput.placeholder = `Search ${text.toLowerCase()}`;

                    // Update Hidden Input
                    if (selectedFieldInput) {
                        selectedFieldInput.value = field;
                    }

                    // Update Active State
                    dropdownItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');

                    // Close Menu
                    fieldDropdownMenu.classList.remove('show');
                    fieldDropdownToggle.classList.remove('active');
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (!fieldDropdownToggle.contains(e.target) && !fieldDropdownMenu.contains(e.target)) {
                    fieldDropdownMenu.classList.remove('show');
                    fieldDropdownToggle.classList.remove('active');
                }
            });

            // Filters Panel Toggle
            const filtersToggle = document.getElementById('filtersToggle');
            const filtersPanel = document.getElementById('filtersPanel');
            const filtersArrow = document.querySelector('.filters-arrow');

            filtersToggle.addEventListener('click', function () {
                filtersPanel.classList.toggle('show');
                filtersArrow.classList.toggle('rotated');
                this.classList.toggle('active');
            });

            // Filter Section Toggle (untuk accordion)
            const sectionTitles = document.querySelectorAll('.filter-section-title');
            sectionTitles.forEach(title => {
                title.addEventListener('click', function () {
                    const section = this.parentElement;
                    const arrow = this.querySelector('.section-arrow');

                    section.classList.toggle('expanded');
                    if (arrow) {
                        arrow.classList.toggle('rotated');
                    }
                });
            });

            // Search Form Submission
            const searchBtn = document.getElementById('searchBtn');
            const searchForm = document.querySelector('.search-bar');
            const filterParamMap = {
                highlights: 'highlights',
                highlights_adv: 'highlights',
                on_view: 'on_view',
                on_view_adv: 'on_view',
                has_image: 'has_image',
                open_access: 'open_access',
                has_3d: 'has_3d'
            };

            function applyFiltersToUrl(url) {
                Object.values(filterParamMap).forEach((param) => {
                    url.searchParams.delete(param);
                });

                Object.entries(filterParamMap).forEach(([inputName, paramName]) => {
                    const checkboxes = document.querySelectorAll(`input[name="${inputName}"]`);
                    const isChecked = Array.from(checkboxes).some((checkbox) => checkbox.checked);

                    if (isChecked) {
                        url.searchParams.set(paramName, '1');
                    }
                });

                ['object_type[]', 'medium[]', 'department[]', 'location[]'].forEach((inputName) => {
                    url.searchParams.delete(inputName);
                    document.querySelectorAll(`input[name="${inputName}"]:checked`).forEach((checkbox) => {
                        url.searchParams.append(inputName, checkbox.value);
                    });
                });

                const yearStart = document.querySelector('input[name="year_start"]');
                const yearEnd = document.querySelector('input[name="year_end"]');

                if (yearStart && yearStart.value.trim()) {
                    url.searchParams.set('year_start', yearStart.value.trim());
                } else {
                    url.searchParams.delete('year_start');
                }

                if (yearEnd && yearEnd.value.trim()) {
                    url.searchParams.set('year_end', yearEnd.value.trim());
                } else {
                    url.searchParams.delete('year_end');
                }
            }

            searchBtn.addEventListener('click', function () {
                performSearch();
            });

            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            function performSearch() {
                const query = searchInput.value.trim();
                const field = selectedFieldInput ? selectedFieldInput.value : 'all';

                const url = new URL(window.location.href);

                // Update Query
                if (query) {
                    url.searchParams.set('q', query);
                } else {
                    url.searchParams.delete('q');
                }

                // Update Field
                if (field && field !== 'all') {
                    url.searchParams.set('field', field);
                } else {
                    url.searchParams.delete('field');
                }

                applyFiltersToUrl(url);

                // Reset pagination on new search
                url.searchParams.delete('page');

                window.location.href = url.toString();
            }

            // Sort Change Handler
            const sortBy = document.getElementById('sortBy');
            sortBy.addEventListener('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', this.value);
                applyFiltersToUrl(url);
                window.location.href = url.toString();
            });
        });
    </script>
@endpush
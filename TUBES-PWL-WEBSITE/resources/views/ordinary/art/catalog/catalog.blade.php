@extends('layouts.app')

@push('styles')
    @vite('resources/css/app.css')
    @vite('resources/css/ordinary/art/catalog/catalog.css')
@endpush

@section('title', 'Explore Collection - OC Museum')

@section('content')
    <div class="container">
        <div class="collection-header">
            <h1>Explore Collection</h1>
            <p>Discover thousands of artworks from across the globe</p>
        </div>

        @php dump('CATALOG BLADE DUMP:', optional($artworks)->toArray()); @endphp
        @if($hasActiveFilters)
            <div class="active-filters">
                <span class="active-filters-label">Active Filters:</span>
                @if($activeFilters['search'])
                    <span class="filter-badge">
                        Search: "{{ $activeFilters['search'] }}"
                        <a href="/art/collection?{{ http_build_query(array_filter(array_merge(request()->query(), ['search' => null]))) }}"
                            class="filter-badge-remove" title="Remove">✕</a>
                    </span>
                @endif
                @if($activeFilters['department_id'])
                    @php
                        $deptName = optional($departments->firstWhere('department_id', $activeFilters['department_id']))->name ?? 'Unknown';
                    @endphp
                    <span class="filter-badge">
                        Department: {{ $deptName }}
                        <a href="/art/collection?{{ http_build_query(array_filter(array_merge(request()->query(), ['department_id' => null]))) }}"
                            class="filter-badge-remove" title="Remove">✕</a>
                    </span>
                @endif
                @if($activeFilters['type_id'])
                    @php
                        $typeName = optional($types->firstWhere('type_id', $activeFilters['type_id']))->name ?? 'Unknown';
                    @endphp
                    <span class="filter-badge">
                        Type: {{ $typeName }}
                        <a href="/art/collection?{{ http_build_query(array_filter(array_merge(request()->query(), ['type_id' => null]))) }}"
                            class="filter-badge-remove" title="Remove">✕</a>
                    </span>
                @endif
                @if($activeFilters['geo_id'])
                    @php
                        $geoName = optional($geoLocations->firstWhere('geo_id', $activeFilters['geo_id']))->name ?? 'Unknown';
                    @endphp
                    <span class="filter-badge">
                        Geography: {{ $geoName }}
                        <a href="/art/collection?{{ http_build_query(array_filter(array_merge(request()->query(), ['geo_id' => null]))) }}"
                            class="filter-badge-remove" title="Remove">✕</a>
                    </span>
                @endif
                @if($activeFilters['object_begin_date'] || $activeFilters['object_end_date'])
                    <span class="filter-badge">
                        Years: {{ $activeFilters['object_begin_date'] ?? 'Start' }} -
                        {{ $activeFilters['object_end_date'] ?? 'End' }}
                        <a href="/art/collection?{{ http_build_query(array_filter(array_merge(request()->query(), ['object_begin_date' => null, 'object_end_date' => null]))) }}"
                            class="filter-badge-remove" title="Remove">✕</a>
                    </span>
                @endif
            </div>
        @endif

        <div class="collection-container">
            <!-- Filters Section -->
            <aside class="filters-section">
                <h3 class="filter-title">Filters</h3>
                <form method="GET" action="/art/collection">
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Title or artist..."
                            value="{{ request('search') }}">
                    </div>

                    <div class="filter-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="type_id">Object Type</label>
                        <select id="type_id" name="type_id">
                            <option value="">All Types</option>
                            @foreach($types ?? [] as $type)
                                <option value="{{ $type->type_id }}" {{ request('type_id') == $type->type_id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="geo_id">Geography</label>
                        <select id="geo_id" name="geo_id">
                            <option value="">All Locations</option>
                            @foreach($geoLocations ?? [] as $geo)
                                <option value="{{ $geo->geo_id }}" {{ request('geo_id') == $geo->geo_id ? 'selected' : '' }}>
                                    {{ $geo->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="object_begin_date">Year From</label>
                        <input type="number" id="object_begin_date" name="object_begin_date" placeholder="e.g., 1800"
                            value="{{ request('object_begin_date') }}">
                    </div>

                    <div class="filter-group">
                        <label for="object_end_date">Year To</label>
                        <input type="number" id="object_end_date" name="object_end_date" placeholder="e.g., 1900"
                            value="{{ request('object_end_date') }}">
                    </div>

                    <button type="submit" class="filter-button">Apply Filters</button>
                    <a href="/art/collection" class="clear-filters">Clear All</a>
                </form>
            </aside>

            <!-- Grid Section -->
            <section>
                @if($artworks->count() > 0)
                    <div class="results-header">
                        <span class="results-count">Showing <strong>{{ $artworks->count() }}</strong> of
                            <strong>{{ $totalResults }}</strong> artworks</span>
                        @if($hasActiveFilters)
                            <a href="/art/collection" class="reset-all-button">Reset All Filters</a>
                        @endif
                    </div>
                    <div class="grid-container">
                        @foreach($artworks as $artwork)
                            <a href="/art/{{ $artwork->slug }}" class="artwork-card">
                                <div class="artwork-card-wrapper">
                                    @php
                                        $primaryImage = $artwork->images->firstWhere('is_primary', true) ?? $artwork->images->first();
                                    @endphp
                                    @if($primaryImage)
                                        <img src="{{ $primaryImage->resolved_url }}" alt="{{ $artwork->title }}" class="artwork-image">
                                    @else
                                        <div class="artwork-image" style="background-color: #e8e8e8;"></div>
                                    @endif
                                </div>
                                <div class="artwork-info">
                                    <h3 class="artwork-title">{{ $artwork->title }}</h3>
                                    @if($artwork->constituents->isNotEmpty())
                                        <p class="artwork-artist">{{ $artwork->constituents->pluck('display_name')->join(', ') }}</p>
                                    @endif
                                    @if($artwork->object_date_display)
                                        <p class="artwork-year">{{ $artwork->object_date_display }}</p>
                                    @elseif($artwork->object_begin_date || $artwork->object_end_date)
                                        <p class="artwork-year">
                                            @if($artwork->object_begin_date && $artwork->object_end_date)
                                                {{ $artwork->object_begin_date }} – {{ $artwork->object_end_date }}
                                            @elseif($artwork->object_begin_date)
                                                {{ $artwork->object_begin_date }}
                                            @else
                                                {{ $artwork->object_end_date }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($artworks->hasPages())
                        <div class="pagination-container">
                            @if($artworks->onFirstPage())
                                <span class="pagination-link disabled">← Previous</span>
                            @else
                                <a href="{{ $artworks->previousPageUrl() }}" class="pagination-link">← Previous</a>
                            @endif

                            @for($i = 1; $i <= $artworks->lastPage(); $i++)
                                @if($i === $artworks->currentPage())
                                    <span class="pagination-link active">{{ $i }}</span>
                                @else
                                    <a href="{{ $artworks->url($i) }}" class="pagination-link">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($artworks->hasMorePages())
                                <a href="{{ $artworks->nextPageUrl() }}" class="pagination-link">Next →</a>
                            @else
                                <span class="pagination-link disabled">Next →</span>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="grid-container">
                        <div class="empty-state-container">
                            <div class="empty-state-icon">◻</div>
                            <h2 class="empty-state-title">No Artworks Found</h2>
                            <p class="empty-state-description">
                                @if($hasActiveFilters)
                                    We couldn't find any artworks matching your filters. Try adjusting your search criteria or
                                    exploring different categories.
                                @else
                                    No artworks are currently available. Please check back soon.
                                @endif
                            </p>
                            @if($hasActiveFilters)
                                <a href="/art/collection" class="empty-state-link">Clear All Filters</a>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
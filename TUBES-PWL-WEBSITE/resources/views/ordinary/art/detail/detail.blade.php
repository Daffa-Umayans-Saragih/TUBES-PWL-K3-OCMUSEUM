@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/art/show/show.css')
@endpush

@section('content')

@php
    /* ── Images ─────────────────────────────────────────── */
    $primaryImage = $artwork->images->firstWhere('is_primary', true) ?? $artwork->images->first();
    $allImages    = $artwork->images ?? collect();

    /* ── Resolve image URL ── */
    $resolveUrl = function($img) {
        if (!$img) return null;
        return $img->resolved_url;
    };

    /* ── Constituents ─────────────────────────────────── */
    $allConstituents = $artwork->constituents ?? collect();
    $makers          = $allConstituents->filter(fn($c) => strtolower($c->role ?? '') === 'maker');
    $artists         = $allConstituents->filter(fn($c) => strtolower($c->role ?? '') === 'artist');
    $displayArtists  = $artists->isNotEmpty() ? $artists : ($makers->isNotEmpty() ? $makers : $allConstituents);

    /* ── Date ─────────────────────────────────────────── */
    $dateStr = $artwork->object_date_display ?? '';
    if (!$dateStr) {
        $ys = $artwork->object_begin_date;
        $ye = $artwork->object_end_date;
        if ($ys && $ye && $ys !== $ye) $dateStr = $ys . '–' . $ye;
        elseif ($ys) $dateStr = (string)$ys;
        elseif ($ye) $dateStr = (string)$ye;
    }

    /* ── Gallery text ─────────────────────────────────── */
    $galleryText = '';
    if (!empty($artwork->location)) {
        $galleryText = is_object($artwork->location) ? ($artwork->location->location_name ?? '') : (string)$artwork->location;
        if (!empty($artwork->gallery_number)) $galleryText .= ', Gallery ' . $artwork->gallery_number;
    }

    /* ── Taxonomy ────────────────────────────────────── */
    $cultures        = ($artwork->cultures  ?? collect())->pluck('culture_name')->filter()->join(', ');
    $mediums         = ($artwork->mediums   ?? collect())->pluck('medium_name')->filter()->join(', ');
    $classificationN = $artwork->classification?->classification_name ?? '';
    $departmentName  = $artwork->department?->department_name ?? '';
    $creditLine      = $artwork->creditLine?->credit_line_text ?? '';
    $objectNumber    = $artwork->accession_number ?? '';

    /* ── Dimensions ─────────────────────────────────── */
    $dimensions = '';
    if (!empty($artwork->dimensions_display)) {
        $dimensions = $artwork->dimensions_display;
    } else {
        $meas = $artwork->measurements ?? collect();
        if ($meas->isNotEmpty()) {
            $dimensions = $meas->map(fn($m) =>
                trim(($m->element_name ? $m->element_name . ': ' : '') . ($m->element_description ?? ''))
            )->filter()->join('; ');
        }
    }

    /* ── SIMs ────────────────────────────────────────── */
    $sims         = $artwork->artWorkSims ?? collect();
    $signatures   = $sims->where('sim_type', 'Signature');
    $inscriptions = $sims->where('sim_type', 'Inscription');
    $markings     = $sims->where('sim_type', 'Marking');
    $hasSims      = $sims->isNotEmpty();

    /* ── Exhibition histories ─────────────────────────── */
    $exhibitions = ($artwork->exhibitionHistories ?? collect())->sortBy('display_order')->values();

    /* ── References ──────────────────────────────────── */
    $references = ($artwork->references ?? collect())
        ->sortBy('display_order')
        ->filter(fn($r) => trim($r->reference_text ?? '') !== '')
        ->values();

    /* ── Provenance ──────────────────────────────────── */
    $provenance = trim($artwork->provenance ?? '');

    /* ── Visibility flags ────────────────────────────── */
    $showSims       = $hasSims;
    $showProvenance = $provenance !== '';
    $showExhibition = $exhibitions->isNotEmpty();
    $showReferences = $references->isNotEmpty();

    /* ── Connected Discovery Sections ── */
    $artist  = $artwork->constituents->first();
    $medium  = $artwork->mediums->first();
    $culture = $artwork->cultures->first();
    $period  = $artwork->periods->first();

    $discoverySections = [
        [
            'title' => $artist ? "More from " . $artist->display_name : "",
            'items' => $sameArtistWorks ?? collect(),
            'badge' => "Same Artist",
        ],
        [
            'title' => $medium ? "Explore More in " . $medium->medium_name : "",
            'items' => $sameMediumWorks ?? collect(),
            'badge' => $medium ? $medium->medium_name : "",
        ],
        [
            'title' => $artwork->department ? "More from " . $artwork->department->department_name : "",
            'items' => $sameDeptWorks ?? collect(),
            'badge' => $artwork->department ? $artwork->department->department_name : "",
        ],
        [
            'title' => $culture ? "Explore " . $culture->culture_name . " Culture" : "",
            'items' => $sameCultureWorks ?? collect(),
            'badge' => $culture ? $culture->culture_name : "",
        ],
        [
            'title' => $period ? "More from the " . $period->period_name : "",
            'items' => $samePeriodWorks ?? collect(),
            'badge' => $period ? $period->period_name : "",
        ],
        [
            'title' => $artwork->classification ? "More " . Str::plural($artwork->classification->classification_name) : "",
            'items' => $sameClassWorks ?? collect(),
            'badge' => $artwork->classification ? $artwork->classification->classification_name : "",
        ],
        [
            'title' => "You May Also Like",
            'items' => $relatedArtworks ?? collect(),
            'badge' => "Smart Similarity Match",
        ],
    ];
@endphp

<div class="met-page">

    {{-- ══ BREADCRUMB ══ --}}
    <div class="met-breadcrumb-bar">
        <div class="met-wrap">
            <nav class="met-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('art.index') }}">Collection</a>
                <span>/</span>
                <span>{{ Str::limit($artwork->title, 80) }}</span>
            </nav>
        </div>
    </div>

    {{-- ══ HERO ══ --}}
    <section class="met-hero">
        <div class="met-wrap met-hero__grid">

            {{-- LEFT: Info --}}
            <div class="met-hero__info">

                <h1 class="met-title">{{ $artwork->title }}</h1>

                <div class="met-artist-block">
                    @forelse($displayArtists as $c)
                        <div class="met-artist-line">
                            @if(!empty($c->role) && !in_array(strtolower($c->role), ['artist', '']))
                                <span class="met-role">{{ $c->role }}:</span>
                            @endif
                            @if(!empty($c->display_name) && strtolower($c->display_name) !== 'unknown artist')
                                <a href="{{ route('art.search', ['q' => $c->display_name]) }}" class="met-artist-link">
                                    <span class="met-artist-name">{{ $c->display_name }}</span>
                                </a>
                            @else
                                <span class="met-artist-name">{{ $c->display_name ?? 'Unknown Artist' }}</span>
                            @endif
                            @if(!empty($c->display_bio))
                                <span class="met-artist-nat">({{ Str::limit($c->display_bio, 60) }})</span>
                            @endif
                        </div>
                    @empty
                        <div class="met-artist-line"><span class="met-artist-name">Unknown Artist</span></div>
                    @endforelse
                </div>

                <div class="met-quickmeta">
                    @foreach(['Date' => $dateStr, 'Culture' => $cultures, 'Medium' => $mediums, 'Department' => $departmentName] as $qLabel => $qVal)
                        @if($qVal)
                            <div class="met-qm-row">
                                <span class="met-qm-label">{{ $qLabel }}</span>
                                <span class="met-qm-val">
                                    @if($qLabel === 'Department')
                                        <a href="{{ route('art.search', ['department[]' => $qVal]) }}" style="color: var(--met-black); text-decoration: underline; text-decoration-color: #bbb; text-underline-offset: 3px; cursor: pointer; transition: text-decoration-color 0.15s;" onmouseover="this.style.textDecorationColor='var(--met-black)';" onmouseout="this.style.textDecorationColor='#bbb';">{{ $qVal }}</a>
                                    @else
                                        {{ $qVal }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    @endforeach
                    @if($galleryText)
                        <div class="met-qm-row">
                            <span class="met-qm-icon">●</span>
                            <span class="met-qm-val met-qm-val--gallery">On view at <strong>{{ $galleryText }}</strong></span>
                        </div>
                    @endif
                </div>

                <div class="met-chips-container">
                    <span class="met-chips-title">Explore Connections</span>
                    <div class="met-chips-list">
                        @if($artwork->department)
                            <a href="{{ route('art.search', ['department[]' => $artwork->department->department_name]) }}" class="met-chip">
                                Gallery: {{ $artwork->department->department_name }}
                            </a>
                        @endif
                        @if($artwork->classification)
                            <a href="{{ route('art.search', ['object_type[]' => $artwork->classification->classification_name]) }}" class="met-chip">
                                Classification: {{ $artwork->classification->classification_name }}
                            </a>
                        @endif
                        @foreach($artwork->mediums as $med)
                            <a href="{{ route('art.search', ['object_type[]' => $med->medium_name]) }}" class="met-chip">
                                Medium: {{ $med->medium_name }}
                            </a>
                        @endforeach
                        @foreach($artwork->cultures as $cul)
                            <a href="{{ route('art.search', ['q' => $cul->culture_name]) }}" class="met-chip">
                                Culture: {{ $cul->culture_name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if(!empty($artwork->description))
                    <div class="met-desc" id="met-desc">
                        <div class="met-desc__body" id="met-desc-body">
                            {!! nl2br(e($artwork->description)) !!}
                        </div>
                        <button class="met-desc__toggle" id="met-desc-toggle" onclick="
                            var b=document.getElementById('met-desc-body');
                            var t=document.getElementById('met-desc-toggle');
                            b.classList.toggle('met-desc__body--open');
                            t.textContent=b.classList.contains('met-desc__body--open')?'View less ↑':'View more ↓';
                        ">View more ↓</button>
                    </div>
                @endif

                <div class="met-hero-actions">
                    <button class="met-btn" onclick="navigator.share?navigator.share({title:'{{ addslashes($artwork->title) }}',url:window.location.href}):(navigator.clipboard.writeText(window.location.href),alert('Link copied!'))">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        Share
                    </button>
                    <a href="{{ route('art.index') }}" class="met-btn met-btn--ghost">← Back to Collection</a>
                </div>

            </div>

            {{-- RIGHT: Gallery --}}
            <div class="met-hero__gallery" id="met-gallery">

                <div class="met-imgframe" id="met-imgframe" onclick="metLightboxOpen()">
                    @if($primaryImage)
                        <img id="met-mainimg" src="{{ $resolveUrl($primaryImage) }}" alt="{{ $artwork->title }}" class="met-mainimg">
                        <span class="met-imgframe__zoom">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                        </span>
                    @else
                        <div class="met-no-img">No image available</div>
                    @endif
                </div>

                <div class="met-imgbar">
                    <div>
                        @if($artwork->is_public_domain)
                            <span class="met-pd">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/><path fill="white" d="M9 12l2 2 4-4"/></svg>
                                Public Domain
                            </span>
                        @endif
                    </div>
                    <div class="met-imgbar__actions">
                        <button class="met-icon-btn" title="Download" onclick="metDownload()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        </button>
                        <button class="met-icon-btn" title="Fullscreen" onclick="metLightboxOpen()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                        </button>
                    </div>
                </div>

                @if($allImages->count() > 1)
                    <div class="met-thumbs">
                        @foreach($allImages as $img)
                            @php $u = $resolveUrl($img); @endphp
                            <button class="met-thumb" onclick="metSetImg('{{ $u }}')" type="button" data-url="{{ $u }}">
                                <img src="{{ $u }}" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="met-lightbox" id="met-lightbox" style="display:none" onclick="metLightboxClose()">
                    <button class="met-lightbox__close" onclick="metLightboxClose()">✕</button>
                    <img id="met-lightbox-img" src="{{ $resolveUrl($primaryImage) }}" class="met-lightbox__img" onclick="event.stopPropagation()">
                </div>

            </div>

        </div>
    </section>

    {{-- ══ ARTWORK DETAILS ══ --}}
    <section class="met-details">
        <div class="met-wrap">
            <h2 class="met-section-title">Artwork Details</h2>
            <div class="met-details__layout">

                {{-- Sidenav --}}
                <nav class="met-sidenav" id="met-sidenav" aria-label="Artwork sections">
                    <button class="met-sidenav__btn met-sidenav__btn--active" data-tab="overview"   onclick="metTab('overview')">Overview</button>
                    @if($showSims)
                        <button class="met-sidenav__btn" data-tab="sims"       onclick="metTab('sims')">Signatures, Inscriptions &amp; Markings</button>
                    @endif
                    @if($showProvenance)
                        <button class="met-sidenav__btn" data-tab="provenance" onclick="metTab('provenance')">Provenance</button>
                    @endif
                    @if($showExhibition)
                        <button class="met-sidenav__btn" data-tab="exhibition" onclick="metTab('exhibition')">Exhibition History</button>
                    @endif
                    @if($showReferences)
                        <button class="met-sidenav__btn" data-tab="references" onclick="metTab('references')">References</button>
                    @endif
                </nav>

                {{-- Panels --}}
                <div class="met-panel">

                    {{-- OVERVIEW --}}
                    <div class="met-tab-panel" id="tab-overview">
                        <dl class="met-dl">
                            @php
                                $rows = [
                                    'Title'                 => $artwork->title,
                                    'Artist / Maker'        => $allConstituents->isNotEmpty()
                                        ? $allConstituents->map(fn($c) => $c->display_name . ($c->display_bio ? ' (' . Str::limit($c->display_bio, 60) . ')' : ''))->join("\n")
                                        : null,
                                    'Date'                  => $dateStr ?: null,
                                    'Culture'               => $cultures ?: null,
                                    'Medium'                => $mediums ?: null,
                                    'Dimensions'            => $dimensions ?: null,
                                    'Classification'        => $classificationN ?: null,
                                    'Credit Line'           => $creditLine ?: null,
                                    'Object Number'         => $objectNumber ?: null,
                                    'Curatorial Department' => $departmentName ?: null,
                                    'Gallery'               => $galleryText ?: null,
                                ];
                            @endphp
                            @foreach($rows as $rowLabel => $rowValue)
                                @if($rowValue)
                                    <div class="met-dl__row">
                                        <dt class="met-dl__dt">{{ $rowLabel }}</dt>
                                        <dd class="met-dl__dd">
                                            @if($rowLabel === 'Curatorial Department')
                                                <a href="{{ route('art.search', ['department[]' => $rowValue]) }}" style="color: var(--met-black); text-decoration: underline; text-decoration-color: #bbb; text-underline-offset: 3px; cursor: pointer; transition: text-decoration-color 0.15s;" onmouseover="this.style.textDecorationColor='var(--met-black)';" onmouseout="this.style.textDecorationColor='#bbb';">{{ $rowValue }}</a>
                                            @elseif($rowLabel === 'Classification')
                                                <a href="{{ route('art.search', ['object_type[]' => $rowValue]) }}" style="color: var(--met-black); text-decoration: underline; text-decoration-color: #bbb; text-underline-offset: 3px; cursor: pointer; transition: text-decoration-color 0.15s;" onmouseover="this.style.textDecorationColor='var(--met-black)';" onmouseout="this.style.textDecorationColor='#bbb';">{{ $rowValue }}</a>
                                            @else
                                                @foreach(explode("\n", $rowValue) as $ln)
                                                    {{ $ln }}<br>
                                                @endforeach
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>

                    {{-- SIM --}}
                    @if($showSims)
                    <div class="met-tab-panel" id="tab-sims" style="display:none">
                        <div class="met-sim-section">
                            @foreach(['Signature' => $signatures, 'Inscription' => $inscriptions, 'Marking' => $markings] as $simLabel => $simGroup)
                                @if($simGroup->isNotEmpty())
                                    <div class="met-sim-group">
                                        <h3 class="met-sim-group__heading">{{ Str::plural($simLabel) }}</h3>
                                        @foreach($simGroup as $sim)
                                            <div class="met-sim-entry">
                                                <span class="met-sim-badge met-sim-badge--{{ strtolower($simLabel) }}">{{ $simLabel }}</span>
                                                <div class="met-sim-text">{!! nl2br(e($sim->sim_text)) !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- PROVENANCE --}}
                    @if($showProvenance)
                    <div class="met-tab-panel" id="tab-provenance" style="display:none">
                        <div class="met-provenance-wrap">
                            <p class="met-provenance-note">
                                The following is the ownership history of this work from the time of its creation or earliest recorded existence to its acquisition by Our Civilization Museum of Art.
                            </p>
                            <div class="met-provenance-body" id="met-prov-body">
                                <pre class="met-provenance-pre">{{ $provenance }}</pre>
                            </div>
                            <button class="met-provenance-toggle" id="met-prov-toggle" onclick="
                                var b=document.getElementById('met-prov-body');
                                var t=document.getElementById('met-prov-toggle');
                                b.classList.toggle('met-provenance-body--open');
                                t.textContent=b.classList.contains('met-provenance-body--open')?'↑ Show less':'↓ Show full provenance';
                            ">↓ Show full provenance</button>
                        </div>
                    </div>
                    @endif

                    {{-- EXHIBITION HISTORY --}}
                    @if($showExhibition)
                    <div class="met-tab-panel" id="tab-exhibition" style="display:none">
                        <p class="met-section-subtitle">{{ $exhibitions->count() }} exhibition{{ $exhibitions->count() !== 1 ? 's' : '' }} on record</p>
                        <ol class="met-timeline">
                            @foreach($exhibitions as $ex)
                                <li class="met-timeline__item">
                                    <div class="met-timeline__dot"></div>
                                    <div class="met-timeline__body">
                                        <strong class="met-timeline__title">{{ $ex->exhibition_title ?? '—' }}</strong>
                                        @if(!empty($ex->venue_name) || !empty($ex->city_name))
                                            <span class="met-timeline__venue">{{ implode(', ', array_filter([$ex->venue_name ?? '', $ex->city_name ?? ''])) }}</span>
                                        @endif
                                        @if(!empty($ex->exhibition_date_display))
                                            <span class="met-timeline__date">{{ $ex->exhibition_date_display }}</span>
                                        @elseif(!empty($ex->start_date) || !empty($ex->end_date))
                                            <span class="met-timeline__date">
                                                {{ $ex->start_date ? \Carbon\Carbon::parse($ex->start_date)->format('M j, Y') : '' }}
                                                @if($ex->start_date && $ex->end_date) – @endif
                                                {{ $ex->end_date ? \Carbon\Carbon::parse($ex->end_date)->format('M j, Y') : '' }}
                                            </span>
                                        @endif
                                        @if(!empty($ex->catalogue_reference))
                                            <span class="met-timeline__cat">Cat. {{ $ex->catalogue_reference }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    @endif

                    {{-- REFERENCES --}}
                    @if($showReferences)
                    <div class="met-tab-panel" id="tab-references" style="display:none">
                        <p class="met-section-subtitle">{{ $references->count() }} reference{{ $references->count() !== 1 ? 's' : '' }} on record</p>
                        <ol class="met-biblist">
                            @foreach($references as $ref)
                                <li class="met-biblist__item">
                                    <div class="met-biblist__text">{!! nl2br(e($ref->reference_text)) !!}</div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    @endif

                </div>{{-- /panel --}}
            </div>
        </div>
    </section>

    {{-- ══ EXPLORE CONNECTED DISCOVERY TABS ══ --}}
    @php
        $tabs = [
            [
                'key' => 'related',
                'label' => 'Related',
                'items' => $relatedArtworks ?? collect(),
                'badge' => 'Smart Similarity Match',
            ],
            [
                'key' => 'artist',
                'label' => 'By Artist',
                'items' => $sameArtistWorks ?? collect(),
                'badge' => 'Same Artist',
            ],
            [
                'key' => 'medium',
                'label' => 'In the same medium',
                'items' => $sameMediumWorks ?? collect(),
                'badge' => $medium ? $medium->medium_name : 'Medium Match',
            ],
            [
                'key' => 'period',
                'label' => 'Same time & place',
                'items' => $samePeriodWorks ?? collect(),
                'badge' => $period ? $period->period_name : 'Period Match',
            ],
            [
                'key' => 'culture',
                'label' => 'Same culture',
                'items' => $sameCultureWorks ?? collect(),
                'badge' => $culture ? $culture->culture_name : 'Culture Match',
            ],
            [
                'key' => 'classification',
                'label' => 'Same classification',
                'items' => $sameClassWorks ?? collect(),
                'badge' => $artwork->classification ? $artwork->classification->classification_name : 'Classification Match',
            ],
        ];

        // Filter tabs that have actual items
        $activeTabs = array_filter($tabs, function($t) {
            return $t['items']->isNotEmpty();
        });
    @endphp

    @if(!empty($activeTabs))
        <section class="met-more">
            <div class="met-wrap">
                <h2 class="met-section-title">More Artwork</h2>

                {{-- Tab navigation buttons --}}
                <div class="met-more-tabs-nav">
                    @foreach($activeTabs as $t)
                        <button class="met-more-tab-btn" id="more-tab-btn-{{ $t['key'] }}" onclick="switchMoreTab('{{ $t['key'] }}')">
                            {{ $t['label'] }}
                        </button>
                    @endforeach
                </div>

                {{-- Tab content panels with horizontal cards --}}
                <div class="met-more-tabs-panels">
                    @foreach($activeTabs as $t)
                        <div class="met-more-tab-panel" id="more-panel-{{ $t['key'] }}" style="display: none;">
                            <div class="met-horizontal-cards-grid">
                                @foreach($t['items'] as $rel)
                                    @php 
                                        $ri = $rel->images->firstWhere('is_primary', true) ?? $rel->images->first(); 
                                        $relArtist = $rel->constituents->first()?->display_name;
                                    @endphp
                                    <a href="{{ route('artwork.show', $rel->slug) }}" class="met-horizontal-card">
                                        <div class="met-horizontal-card__img">
                                            @if($ri)
                                                <img src="{{ $resolveUrl($ri) }}" alt="{{ $rel->title }}" loading="lazy">
                                            @else
                                                <div class="met-horizontal-card__noimg">No image</div>
                                            @endif
                                        </div>
                                        <div class="met-horizontal-card__body">
                                            <div class="met-horizontal-card__top">
                                                <span class="met-connection-badge">Because of: {{ $t['badge'] }}</span>
                                                <p class="met-horizontal-card__title">{{ $rel->title }}</p>
                                                @if($relArtist)
                                                    <p class="met-horizontal-card__artist">{{ $relArtist }}</p>
                                                @else
                                                    <p class="met-horizontal-card__artist">Unknown Artist</p>
                                                @endif
                                            </div>
                                            @if($rel->department)
                                                <div class="met-horizontal-card__bottom">
                                                    <p class="met-horizontal-card__dept">{{ $rel->department->department_name }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</div>

@push('scripts')
<script>
/* ── Vanilla Tab System ── */
function metTab(id) {
    // Deactivate all panels
    document.querySelectorAll('.met-tab-panel').forEach(function(p) {
        p.style.display = 'none';
    });
    // Deactivate all nav buttons
    document.querySelectorAll('.met-sidenav__btn').forEach(function(b) {
        b.classList.remove('met-sidenav__btn--active');
    });
    // Activate target panel
    var panel = document.getElementById('tab-' + id);
    if (panel) panel.style.display = 'block';
    // Activate target button
    var btn = document.querySelector('[data-tab="' + id + '"]');
    if (btn) btn.classList.add('met-sidenav__btn--active');
}

/* ── More Artwork Dynamic Tabs ── */
function switchMoreTab(tabKey) {
    // Hide all more panels
    document.querySelectorAll('.met-more-tab-panel').forEach(function(p) {
        p.style.display = 'none';
    });
    // Deactivate all tab buttons
    document.querySelectorAll('.met-more-tab-btn').forEach(function(b) {
        b.classList.remove('met-more-tab-btn--active');
    });
    // Show selected panel
    var panel = document.getElementById('more-panel-' + tabKey);
    if (panel) panel.style.display = 'block';
    // Activate selected button
    var btn = document.getElementById('more-tab-btn-' + tabKey);
    if (btn) btn.classList.add('met-more-tab-btn--active');
}

// Auto-activate the first available tab on DOM content load
document.addEventListener('DOMContentLoaded', function() {
    var firstBtn = document.querySelector('.met-more-tab-btn');
    if (firstBtn) {
        firstBtn.click();
    }
});

/* ── Gallery ── */
var _metCurrentImg = document.getElementById('met-mainimg') ? document.getElementById('met-mainimg').src : '';

function metSetImg(url) {
    _metCurrentImg = url;
    var img = document.getElementById('met-mainimg');
    if (img) img.src = url;
    var lbImg = document.getElementById('met-lightbox-img');
    if (lbImg) lbImg.src = url;
    // Update thumb active state
    document.querySelectorAll('.met-thumb').forEach(function(t) {
        t.classList.toggle('met-thumb--on', t.dataset.url === url);
    });
}

function metLightboxOpen() {
    var lb = document.getElementById('met-lightbox');
    if (lb) lb.style.display = 'flex';
}

function metLightboxClose() {
    var lb = document.getElementById('met-lightbox');
    if (lb) lb.style.display = 'none';
}

function metDownload() {
    var img = document.getElementById('met-mainimg');
    if (!img) return;
    var a = document.createElement('a');
    a.href = img.src; a.download = ''; a.target = '_blank'; a.click();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') metLightboxClose();
});
</script>
@endpush

@endsection
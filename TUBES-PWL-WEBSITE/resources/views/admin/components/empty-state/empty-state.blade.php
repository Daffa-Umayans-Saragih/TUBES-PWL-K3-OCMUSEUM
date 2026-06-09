{{--
Reusable Empty State Component (Enterprise Admin)
Variants: default, analytics, compact
Usage:
@include('admin.components.empty-state.empty-state', [
    'icon' => 'default', // or 'analytics', 'compact'
    'title' => 'No Data',
    'description' => 'There is currently no data to display.',
    'cta' => 'Add New',
    'ctaHref' => '#',
    'variant' => 'default', // or 'analytics', 'compact'
])
--}}

@php
    $variant = $variant ?? 'default';
@endphp

<div class="empty-state empty-state--{{ $variant }}" role="region" aria-label="Empty state">
    <span class="icon-placeholder icon-placeholder--lg empty-state__icon" aria-hidden="true"></span>
    <h2 class="empty-state__title">{{ $title ?? 'No Data' }}</h2>
    <p class="empty-state__desc">{{ $description ?? 'There is currently no data to display.' }}</p>
    @if (!empty($cta) && !empty($ctaHref))
        <a href="{{ $ctaHref }}" class="empty-state__cta" tabindex="0">{{ $cta }}</a>
    @endif
</div>

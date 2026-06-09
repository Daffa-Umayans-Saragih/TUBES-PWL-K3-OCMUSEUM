<!-- Stat Card Component -->
<div class="stat-card stat-card--{{ $color ?? 'default' }}">
    <div class="stat-card__icon">
        {{ $icon }}
    </div>
    <div class="stat-card__content">
        <div class="stat-card__label">{{ $title }}</div>
        <div class="stat-card__value">{{ $value }}</div>
        <div class="stat-card__trend">{{ $trend ?? '' }}</div>
    </div>
</div>

{{--
    Icon Placeholder Usage:
    <span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
    Use for toolbar action icons, scalable and themeable.
--}}
<div class="admin-toolbar" role="region" aria-label="Page toolbar">
    <div class="admin-toolbar__main">
        @include('admin.components.toolbar.breadcrumbs', ['items' => $breadcrumbs ?? []])

        <div class="admin-toolbar__titles">
            <div class="admin-toolbar__title-row">
                <h1 tabindex="0">{{ $title ?? 'Page Title' }}</h1>
                @if (!empty($status))
                    <span class="status-chip status-chip--{{ $status['tone'] ?? 'active' }}" tabindex="0">
                        {{ $status['label'] ?? 'Status' }}
                    </span>
                @endif
            </div>
            @if (!empty($subtitle))
                <p class="admin-toolbar__subtitle">{{ $subtitle }}</p>
            @endif
            @if (!empty($badges))
                <div class="admin-toolbar__badges">
                    @foreach ($badges as $badge)
                        <span class="badge badge--{{ $badge['tone'] ?? 'neutral' }}" tabindex="0">
                            {{ $badge['label'] ?? 'Badge' }}
                        </span>
                    @endforeach
                </div>
            @endif
            @if (!empty($filters))
                <div class="admin-toolbar__filters">
                    @foreach ($filters as $filter)
                        <span class="admin-pill admin-pill--soft" tabindex="0">{{ $filter }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if (!empty($actions))
        <div class="admin-toolbar__actions" role="group" aria-label="Toolbar actions">
            @foreach ($actions as $action)
                <button class="admin-button admin-button--{{ $action['variant'] ?? 'ghost' }}" type="button" tabindex="0">
                    <span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
                    <span>{{ $action['label'] ?? 'Action' }}</span>
                </button>
            @endforeach
        </div>
    @endif
</div>

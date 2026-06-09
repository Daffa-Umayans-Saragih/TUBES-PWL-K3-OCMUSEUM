@if (!empty($items))
<nav class="admin-breadcrumbs" aria-label="Breadcrumb">
    <ol class="admin-breadcrumbs__list">
        @foreach ($items as $item)
            <li class="admin-breadcrumbs__item{{ !empty($item['isCurrent']) ? ' is-current' : '' }}">
                @if (!empty($item['href']) && empty($item['isCurrent']))
                    <a class="admin-breadcrumbs__link" href="{{ $item['href'] }}">
                        {{ $item['label'] ?? 'Item' }}
                    </a>
                @else
                    <span class="admin-breadcrumbs__current">{{ $item['label'] ?? 'Item' }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif

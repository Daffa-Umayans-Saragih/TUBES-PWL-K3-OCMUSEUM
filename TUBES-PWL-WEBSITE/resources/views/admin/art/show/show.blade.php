@extends('layouts.admin')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/admin/art/show/show.css')
@endpush

@section('title', $artwork->title . ' - Admin - MET Museum')
@section('page_title', $artwork->title)

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1>{{ $artwork->title }}</h1>
        <div class="subtitle">Object Number: {{ $artwork->object_number }}</div>
    </div>

    <div class="artwork-content">
        <!-- Left: Images -->
        <div class="artwork-images">
            @if($artwork->images && $artwork->images->count() > 0)
                <div class="image-gallery">
                    <div class="image-gallery-main">
                        <img id="mainImage" src="{{ asset('storage/' . $artwork->images->first()->url) }}" alt="{{ $artwork->title }}">
                    </div>
                    <div class="image-gallery-thumbnails">
                        @foreach($artwork->images as $image)
                            <div class="image-gallery-thumb @if($loop->first) active @endif" data-image-url="{{ asset('storage/' . $image->url) }}" onclick="changeImage(this)">
                                <img src="{{ asset('storage/' . $image->url) }}" alt="{{ $artwork->title }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="no-image-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <p>No images available</p>
                </div>
            @endif
        </div>

        <!-- Right: Basic Information -->
        <div>
            <div class="details-grid">
                <div class="info-group">
                    <span class="info-label">Description</span>
                    <div class="info-value">
                        {{ \Illuminate\Support\Str::limit($artwork->description, 300, '...') }}
                    </div>
                </div>

                <div class="info-group">
                    <span class="info-label">Department</span>
                    <div class="info-value">
                        {{ $artwork->department?->name ?? 'Not specified' }}
                    </div>
                </div>

                <div class="info-group">
                    <span class="info-label">Object Type</span>
                    <div class="info-value">
                        {{ $artwork->objectType?->name ?? 'Not specified' }}
                    </div>
                </div>

                <div class="info-group">
                    <span class="info-label">Dating</span>
                    <div class="info-value">
                        @if($artwork->object_date_display)
                            <strong>{{ $artwork->object_date_display }}</strong>
                        @elseif($artwork->object_begin_date && $artwork->object_end_date)
                            <strong>{{ $artwork->object_begin_date }} – {{ $artwork->object_end_date }}</strong>
                        @elseif($artwork->object_begin_date)
                            <strong>{{ $artwork->object_begin_date }}</strong>
                        @else
                            Not specified
                        @endif
                    </div>
                </div>

                <div class="info-group">
                    <span class="info-label">Locations</span>
                    <div class="info-value">
                        @if($artwork->geographies->isNotEmpty())
                            @foreach($artwork->geographies as $geo)
                                <div><strong>{{ optional($geo->geographyType)->name ?? 'Geographic' }}:</strong> {{ implode(', ', array_filter([optional($geo->city)->name, optional($geo->state)->name, optional($geo->country)->name])) }}</div>
                            @endforeach
                        @endif
                        @if($artwork->location)
                            <div><strong>Museum:</strong> {{ $artwork->location->name }}</div>
                        @endif
                        @if($artwork->geographies->isEmpty() && !$artwork->location)
                            Not specified
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions-box">
        <div class="action-buttons">
            <a href="{{ route('admin.art.edit', $artwork->art_work_id) }}" class="btn-primary">Edit</a>
            <a href="{{ route('admin.art.index') }}" class="btn-secondary">Back to List</a>
            <form action="{{ route('admin.art.destroy', $artwork->art_work_id) }}" method="POST" style="flex: 1; min-width: 150px;" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" style="width: 100%; margin: 0;">Delete</button>
            </form>
        </div>
    </div>

    <!-- Metadata -->
    <div style="background-color: #fafafa; padding: 2rem; border: 1px solid #e0e0e0;">
        <h3 style="font-size: 1.1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1.5rem; margin-top: 0;">Metadata</h3>

        <table class="metadata-table">
            <tr>
                <td class="metadata-label">Object Number</td>
                <td>{{ $artwork->object_number }}</td>
            </tr>
            <tr>
                <td class="metadata-label">Slug</td>
                <td><code style="background-color: #f5f5f5; padding: 0.3rem 0.6rem; font-size: 0.85rem;">{{ $artwork->slug }}</code></td>
            </tr>
            <tr>
                <td class="metadata-label">Created</td>
                <td>
                    {{ $artwork->created_at->format('F d, Y') }}
                    <span class="timestamp">{{ $artwork->created_at->diffForHumans() }}</span>
                </td>
            </tr>
            <tr>
                <td class="metadata-label">Last Modified</td>
                <td>
                    {{ $artwork->updated_at->format('F d, Y H:i') }}
                    <span class="timestamp">{{ $artwork->updated_at->diffForHumans() }}</span>
                </td>
            </tr>
            @if($artwork->constituents && $artwork->constituents->count() > 0)
                <tr>
                    <td class="metadata-label">Attribution</td>
                    <td>
                        @foreach($artwork->constituents as $constituent)
                            <div class="badge">{{ $constituent->display_name }} ({{ optional($constituent->pivot->role)->name ?? 'Unknown Role' }})</div>
                        @endforeach
                    </td>
                </tr>
            @endif
            @if($artwork->artWorkSims && $artwork->artWorkSims->isNotEmpty())
                <tr>
                    <td class="metadata-label">SIM Data</td>
                    <td>
                        @foreach($artwork->artWorkSims as $sim)
                            <div style="margin-bottom: 0.25rem;">
                                <strong>{{ $sim->sim_type }}:</strong> {{ $sim->sim_text }}
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endif
        </table>
    </div>
</div>

<script>
    function changeImage(element) {
        const mainImage = document.getElementById('mainImage');
        const imageUrl = element.getAttribute('data-image-url');
        mainImage.src = imageUrl;

        // Update active thumbnail
        document.querySelectorAll('.image-gallery-thumb').forEach(thumb => {
            thumb.classList.remove('active');
        });
        element.classList.add('active');
    }
</script>
@endsection

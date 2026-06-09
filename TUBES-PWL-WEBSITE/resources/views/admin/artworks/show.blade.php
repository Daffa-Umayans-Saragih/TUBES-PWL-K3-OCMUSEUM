@extends('admin.layout.layout')

@section('admin-title')
    {{ $title }}
@endsection

@section('admin-content')
<div class="admin-page-section">
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1>{{ $artwork->title ?? 'Untitled' }}</h1>
                <p class="page-subtitle">{{ $subtitle }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.artworks.edit', $artwork->art_work_id) }}" class="btn btn-primary">
                    ✏️ Edit Artwork
                </a>
                <a href="{{ route('admin.artworks.index') }}" class="btn btn-secondary">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 2rem; padding: 1rem 1.25rem; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size: 1.25rem;">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 2rem; padding: 1rem 1.25rem; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size: 1.25rem;">⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Quick Info -->
    <div class="quick-info">
        <div class="info-card">
            <strong>Accession Number:</strong>
            <span>{{ $artwork->accession_number }}</span>
        </div>
        <div class="info-card">
            <strong>MET Object ID:</strong>
            <span>{{ $artwork->met_object_id }}</span>
        </div>
        <div class="info-card">
            <strong>Department:</strong>
            <span>{{ $artwork->department?->department_name ?? 'N/A' }}</span>
        </div>
        <div class="info-card">
            <strong>Status:</strong>
            <span class="status-badge {{ $artwork->is_on_view ? 'status-active' : 'status-inactive' }}">
                {{ $artwork->is_on_view ? '✓ On View' : 'Not Displayed' }}
            </span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-grid">
        <!-- Left Column: Image -->
        <div class="detail-column-left">
            <div class="detail-section">
                <h3 class="section-title">Image</h3>
                
                @if($artwork->images->isNotEmpty())
                    <div class="image-gallery">
                        @php $primaryImage = $artwork->images->firstWhere('is_primary', true) ?? $artwork->images->first(); @endphp
                        
                        @if($primaryImage)
                            <div class="main-image">
                                <img src="{{ $primaryImage->resolved_url }}" alt="{{ $artwork->title }}" class="artwork-image">
                                <p class="image-label">
                                    @if($primaryImage->is_primary) 
                                        <span class="badge badge-primary">Primary Image</span>
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if($artwork->images->count() > 1)
                            <div class="image-thumbnails">
                                <p class="thumbnails-label">Additional Images ({{ $artwork->images->count() - 1 }}):</p>
                                @foreach($artwork->images->where('image_id', '!=', $primaryImage?->image_id) as $image)
                                    <div class="thumbnail">
                                        <img src="{{ $image->resolved_url }}" alt="Thumbnail" class="thumb-img">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="no-image">
                        <p>📷 No images available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Details -->
        <div class="detail-column-right">
            <!-- Basic Information -->
            <div class="detail-section">
                <h3 class="section-title">Basic Information</h3>
                
                <div class="detail-item">
                    <strong>Title:</strong>
                    <span>{{ $artwork->title ?? 'N/A' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Description:</strong>
                    <p>{{ $artwork->description ?? 'No description available' }}</p>
                </div>

                <div class="detail-item">
                    <strong>Accession Year:</strong>
                    <span>{{ $artwork->accession_year ?? 'N/A' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Gallery Number:</strong>
                    <span>{{ $artwork->gallery_number ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Dating & Classification -->
            <div class="detail-section">
                <h3 class="section-title">Dating & Classification</h3>

                <div class="detail-item">
                    <strong>Date:</strong>
                    <span>{{ $artwork->object_date_display ?? $artwork->object_begin_date ?? 'N/A' }}</span>
                </div>

                @if($artwork->objectType)
                    <div class="detail-item">
                        <strong>Object Type:</strong>
                        <span>{{ $artwork->objectType?->object_type_name ?? 'N/A' }}</span>
                    </div>
                @endif

                @if($artwork->classification)
                    <div class="detail-item">
                        <strong>Classification:</strong>
                        <span>{{ $artwork->classification?->classification_name ?? 'N/A' }}</span>
                    </div>
                @endif

                <div class="detail-item">
                    <strong>Dimensions:</strong>
                    <p>{{ $artwork->dimensions_display ?? 'Not specified' }}</p>
                </div>
            </div>

            <!-- Location & Repository -->
            <div class="detail-section">
                <h3 class="section-title">Location & Repository</h3>

                @if($artwork->location)
                    <div class="detail-item">
                        <strong>Location:</strong>
                        <span>{{ $artwork->location?->location_name ?? 'N/A' }}</span>
                    </div>
                @endif

                @if($artwork->repository)
                    <div class="detail-item">
                        <strong>Repository:</strong>
                        <span>{{ $artwork->repository?->repository_name ?? 'N/A' }}</span>
                    </div>
                @endif

                @if($artwork->creditLine)
                    <div class="detail-item">
                        <strong>Credit Line:</strong>
                        <span>{{ $artwork->creditLine?->credit_line_text ?? 'N/A' }}</span>
                    </div>
                @endif
            </div>

            <!-- Flags -->
            <div class="detail-section">
                <h3 class="section-title">Display Flags</h3>

                <div class="flags-grid">
                    <div class="flag-item {{ $artwork->is_on_view ? 'active' : '' }}">
                        {{ $artwork->is_on_view ? '✓' : '✗' }} On View
                    </div>
                    <div class="flag-item {{ $artwork->is_highlight ? 'active' : '' }}">
                        {{ $artwork->is_highlight ? '✓' : '✗' }} Highlight
                    </div>
                    <div class="flag-item {{ $artwork->is_public_domain ? 'active' : '' }}">
                        {{ $artwork->is_public_domain ? '✓' : '✗' }} Public Domain
                    </div>
                    <div class="flag-item {{ $artwork->is_timeline_work ? 'active' : '' }}">
                        {{ $artwork->is_timeline_work ? '✓' : '✗' }} Timeline Work
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Relationships Section -->
    <div class="relationships-section">
        <!-- Artists/Constituents -->
        @if($artwork->constituents->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">👤 Artists & Contributors</h3>
                <div class="relationship-list">
                    @foreach($artwork->constituents as $constituent)
                        <div class="relationship-item">
                            <strong>{{ $constituent->display_name }}</strong>
                            @if($constituent->pivot->role_id)
                                <span class="badge badge-info">{{ $constituent->pivot->role?->role_name ?? 'Role' }}</span>
                            @endif
                            @if($constituent->birth_year || $constituent->death_year)
                                <small>({{ $constituent->birth_year ?? '?' }} - {{ $constituent->death_year ?? '?' }})</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Materials -->
        @if($artwork->materials->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">🎨 Materials</h3>
                <div class="badge-list">
                    @foreach($artwork->materials as $material)
                        <span class="badge badge-secondary">{{ $material->material_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Mediums -->
        @if($artwork->mediums->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">🖼️ Mediums</h3>
                <div class="badge-list">
                    @foreach($artwork->mediums as $medium)
                        <span class="badge badge-secondary">{{ $medium->medium_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Cultures -->
        @if($artwork->cultures->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">🌍 Cultures</h3>
                <div class="badge-list">
                    @foreach($artwork->cultures as $culture)
                        <span class="badge badge-secondary">{{ $culture->culture_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Periods -->
        @if($artwork->periods->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">⏰ Periods</h3>
                <div class="badge-list">
                    @foreach($artwork->periods as $period)
                        <span class="badge badge-secondary">{{ $period->period_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Dynasties -->
        @if($artwork->dynasties->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">👑 Dynasties</h3>
                <div class="badge-list">
                    @foreach($artwork->dynasties as $dynasty)
                        <span class="badge badge-secondary">{{ $dynasty->dynasty_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Reigns -->
        @if($artwork->reigns->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">🏰 Reigns</h3>
                <div class="badge-list">
                    @foreach($artwork->reigns as $reign)
                        <span class="badge badge-secondary">{{ $reign->reign_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Portfolios -->
        @if($artwork->portfolios->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">📚 Portfolios</h3>
                <div class="badge-list">
                    @foreach($artwork->portfolios as $portfolio)
                        <span class="badge badge-secondary">{{ $portfolio->portfolio_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tags -->
        @if($artwork->tags->isNotEmpty())
            <div class="relationship-block">
                <h3 class="relationship-title">#️⃣ Tags</h3>
                <div class="badge-list">
                    @foreach($artwork->tags as $tag)
                        <span class="badge badge-tag">{{ $tag->tag_term }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Additional Information -->
    @if($artwork->provenance || $artwork->rights_and_reproduction)
        <div class="additional-section">
            <h3 class="section-title">Additional Information</h3>

            @if($artwork->provenance)
                <div class="additional-item">
                    <strong>Provenance:</strong>
                    <p>{{ $artwork->provenance }}</p>
                </div>
            @endif

            @if($artwork->rights_and_reproduction)
                <div class="additional-item">
                    <strong>Rights & Reproduction:</strong>
                    <p>{{ $artwork->rights_and_reproduction }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Delete Button -->
    <div class="delete-section">
        <form action="{{ route('admin.artworks.destroy', $artwork->art_work_id) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this artwork? It will be moved to the Trash/Soft Deleted list, and can be restored later.')">
                🗑️ Delete Artwork
            </button>
        </form>
    </div>
</div>

<style>
.admin-page-section {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.quick-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.info-card {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #2196F3;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-card strong {
    color: #333;
    font-size: 0.9rem;
}

.info-card span {
    color: #666;
    font-size: 1rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.detail-column-left, .detail-column-right {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.detail-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 1rem 0;
    border-bottom: 2px solid #2196F3;
    padding-bottom: 0.5rem;
}

.detail-item {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.detail-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-item strong {
    display: block;
    color: #333;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.detail-item span {
    color: #666;
}

.detail-item p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.image-gallery {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.main-image {
    background: #f5f5f5;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}

.artwork-image {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.image-label {
    margin: 0.75rem 0 0 0;
}

.image-thumbnails {
    border-top: 1px solid #e0e0e0;
    padding-top: 1rem;
}

.thumbnails-label {
    font-size: 0.9rem;
    color: #666;
    margin: 0 0 0.5rem 0;
}

.image-thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    background: #f5f5f5;
}

.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.no-image {
    background: #f5f5f5;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    color: #999;
    font-size: 2rem;
}

.flags-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.flag-item {
    padding: 0.75rem;
    background: #f5f5f5;
    border-radius: 4px;
    text-align: center;
    color: #999;
    font-weight: 600;
    border: 2px solid #e0e0e0;
}

.flag-item.active {
    background: #d4edda;
    color: #155724;
    border-color: #155724;
}

.relationships-section {
    margin-bottom: 2rem;
}

.relationship-block {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.relationship-title {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 1rem 0;
    border-bottom: 2px solid #2196F3;
    padding-bottom: 0.5rem;
}

.relationship-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.relationship-item {
    padding: 0.75rem;
    background: #f9f9f9;
    border-left: 3px solid #2196F3;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.badge-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.badge-primary {
    background-color: #2196F3;
    color: white;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-secondary {
    background-color: #e0e0e0;
    color: #333;
}

.badge-tag {
    background-color: #ffc107;
    color: #333;
}

.additional-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.additional-item {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.additional-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.additional-item strong {
    display: block;
    color: #333;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.additional-item p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.delete-section {
    background: #fff5f5;
    border: 1px solid #f5c6cb;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #2196F3;
    color: white;
}

.btn-primary:hover {
    background-color: #1976D2;
}

.btn-secondary {
    background-color: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background-color: #e0e0e0;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.status-active {
    background-color: #d4edda;
    color: #155724;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .flags-grid {
        grid-template-columns: 1fr;
    }

    .header-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

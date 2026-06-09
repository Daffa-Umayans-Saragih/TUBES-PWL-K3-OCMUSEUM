@extends('layouts.admin')

@push('styles')
@vite('resources/css/admin/dashboard/modern.css')
@endpush

@section('title', 'Artworks - MET Museum Admin')
@section('page_title', 'Artworks Management')

@section('content')
<div class="museum-dashboard">
    <!-- STATISTICS CARDS -->
    <div class="stats-grid compact">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Total Artworks</span>
                <h3 class="stat-value">{{ $stats['total_artworks'] }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Collections</span>
                <h3 class="stat-value">{{ $stats['total_departments'] }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Images</span>
                <h3 class="stat-value">{{ $stats['total_images'] }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Artists</span>
                <h3 class="stat-value">{{ $stats['total_artists'] }}</h3>
            </div>
        </div>
    </div>

    <!-- CONTROLS & FILTERS -->
    <div class="artworks-controls">
        <div class="control-group search">
            <input type="text" id="searchInput" placeholder="Search artworks by title or artist..." 
                   value="{{ $search }}" class="search-input">
            <i class="bi bi-search"></i>
        </div>

        <div class="control-group filter">
            <select id="departmentFilter" class="filter-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $department == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="control-group sort">
            <select id="sortBy" class="filter-select">
                <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Latest Added</option>
                <option value="oldest" {{ $sortBy === 'oldest' ? 'selected' : '' }}>Oldest</option>
                <option value="title_az" {{ $sortBy === 'title_az' ? 'selected' : '' }}>Title A-Z</option>
                <option value="title_za" {{ $sortBy === 'title_za' ? 'selected' : '' }}>Title Z-A</option>
            </select>
        </div>

        <div class="control-group actions">
            <button class="btn-action" id="filterBtn" onclick="applyFilters()">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <button class="btn-action secondary" onclick="resetFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </div>

        <div class="control-group spacer"></div>

        <div class="control-group actions">
            <button class="btn-action primary" onclick="openAddArtworkModal()">
                <i class="bi bi-plus-lg"></i> Add Artwork
            </button>
        </div>
    </div>

    <!-- VIEW TOGGLE -->
    <div class="view-toggle">
        <button class="view-btn active" data-view="grid" onclick="switchView('grid')">
            <i class="bi bi-grid-3x3-gap"></i>
        </button>
        <button class="view-btn" data-view="list" onclick="switchView('list')">
            <i class="bi bi-list-ul"></i>
        </button>
    </div>

    <!-- GRID VIEW -->
    <div class="artworks-grid view-grid active">
        @forelse($artworks as $artwork)
            <div class="artwork-card" data-artwork-id="{{ $artwork->art_work_id }}">
                <div class="artwork-image">
                    @if($artwork->images->first())
                        <img src="{{ $artwork->images->first()->resolved_url }}" 
                             alt="{{ $artwork->title }}">
                    @else
                        <div class="no-image">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                    <div class="image-overlay">
                        <button class="btn-overlay" onclick="viewArtworkDetails({{ $artwork->art_work_id }})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-overlay" onclick="editArtwork({{ $artwork->art_work_id }})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-overlay danger" onclick="deleteArtwork({{ $artwork->art_work_id }})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="artwork-info">
                    <h4>{{ $artwork->title }}</h4>
                    <p class="artwork-meta">
                        {{ $artwork->department->name ?? 'Unknown Department' }}
                    </p>
                    @if($artwork->constituents->first())
                        <p class="artwork-artist">
                            By {{ $artwork->constituents->first()->name }}
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1/-1;">
                <i class="bi bi-inbox"></i>
                <h4>No Artworks Found</h4>
                <p>Start by adding your first artwork to the collection</p>
                <button class="btn-primary" onclick="openAddArtworkModal()">Add Artwork</button>
            </div>
        @endforelse
    </div>

    <!-- LIST VIEW -->
    <div class="table-card view-list">
        <div class="table-responsive">
            <table class="data-table artworks-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Artist</th>
                        <th>Images</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($artworks as $artwork)
                        <tr class="artwork-row" data-artwork-id="{{ $artwork->art_work_id }}">
                            <td class="cell-title">
                                <strong>{{ $artwork->title }}</strong>
                            </td>
                            <td>
                                <span class="badge">{{ $artwork->department->name ?? 'Unknown' }}</span>
                            </td>
                            <td>
                                @if($artwork->constituents->first())
                                    {{ $artwork->constituents->first()->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="cell-center">
                                <span class="badge badge-info">{{ $artwork->images->count() }}</span>
                            </td>
                            <td>{{ $artwork->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="View" onclick="viewArtworkDetails({{ $artwork->art_work_id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit" onclick="editArtwork({{ $artwork->art_work_id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-icon danger" title="Delete" onclick="deleteArtwork({{ $artwork->art_work_id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-cell">No artworks found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    @if($artworks->hasPages())
        <div class="pagination-container">
            {{ $artworks->appends(request()->query())->links() }}
        </div>
    @endif

    <!-- MODAL: ADD/EDIT ARTWORK -->
    <div class="modal modal-lg" id="artworkModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Artwork</h3>
                <button class="modal-close" onclick="closeModal('artworkModal')">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form id="artworkForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="artworkTitle">Title *</label>
                        <input type="text" id="artworkTitle" name="title" class="form-input" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="artworkDept">Department *</label>
                            <select id="artworkDept" name="department_id" class="form-input" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="artworkYear">Year Created</label>
                            <input type="number" id="artworkYear" name="year_created" class="form-input" min="1000" max="2100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="artworkArtist">Artist Name</label>
                        <input type="text" id="artworkArtist" name="artist_name" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="artworkDesc">Description</label>
                        <textarea id="artworkDesc" name="description" class="form-input" rows="5"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="artworkImages">Images</label>
                        <div class="file-upload">
                            <input type="file" id="artworkImages" name="images[]" multiple accept="image/*" 
                                   onchange="previewImages()">
                            <div class="upload-icon">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <p>Click to upload or drag and drop</p>
                            <small>PNG, JPG, GIF up to 5MB</small>
                        </div>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('artworkModal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        Save Artwork
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: VIEW ARTWORK DETAILS -->
    <div class="modal modal-lg" id="viewArtworkModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Artwork Details</h3>
                <button class="modal-close" onclick="closeModal('viewArtworkModal')">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body" id="artworkDetailsContent">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    document.getElementById('artworkForm').addEventListener('submit', saveArtwork);

    // Search and filter
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') applyFilters();
    });
});

function openAddArtworkModal() {
    document.getElementById('modalTitle').textContent = 'Add New Artwork';
    document.getElementById('artworkForm').reset();
    document.getElementById('imagePreview').innerHTML = '';
    openModal('artworkModal');
}

function editArtwork(artworkId) {
    // Load artwork data and open edit modal
    fetch(`/admin/artworks/${artworkId}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('artworkTitle').value = data.title;
            document.getElementById('artworkDept').value = data.department_id;
            document.getElementById('artworkYear').value = data.date_created || '';
            document.getElementById('artworkDesc').value = data.description || '';
            document.getElementById('modalTitle').textContent = 'Edit Artwork';
            
            // Store ID for update
            document.getElementById('artworkForm').dataset.artworkId = artworkId;
            
            openModal('artworkModal');
        })
        .catch(err => alert('Error loading artwork data'));
}

function saveArtwork(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const artworkId = this.dataset.artworkId;
    const url = artworkId 
        ? `/admin/artworks/${artworkId}`
        : '/admin/artworks';
    const method = artworkId ? 'POST' : 'POST';

    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error saving artwork');
    });
}

function deleteArtwork(artworkId) {
    if (confirm('Are you sure you want to delete this artwork?')) {
        fetch(`/admin/artworks/${artworkId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function viewArtworkDetails(artworkId) {
    fetch(`/admin/artworks/${artworkId}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('artworkDetailsContent').innerHTML = html;
            openModal('viewArtworkModal');
        });
}

function previewImages() {
    const files = document.getElementById('artworkImages').files;
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    for (let file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const dept = document.getElementById('departmentFilter').value;
    const sort = document.getElementById('sortBy').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (dept) params.append('department', dept);
    if (sort) params.append('sort_by', sort);

    window.location.href = `{{ route('admin.dashboard.artworks') }}?${params.toString()}`;
}

function resetFilters() {
    window.location.href = '{{ route('admin.dashboard.artworks') }}';
}

function switchView(view) {
    document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-view="${view}"]`).classList.add('active');

    document.querySelectorAll('.artworks-grid, .view-list').forEach(el => el.classList.remove('active'));
    document.querySelector(`.view-${view}`).classList.add('active');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}
</script>
@endpush

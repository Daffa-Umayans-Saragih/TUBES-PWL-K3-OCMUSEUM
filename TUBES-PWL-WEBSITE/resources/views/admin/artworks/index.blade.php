@extends('admin.layout.layout')

@section('admin-title')
    Artwork Management
@endsection

@section('admin-content')
<div class="admin-page-section">
    <div class="page-header" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700;">Artwork Management</h1>
                <p class="page-subtitle" style="margin: 0.25rem 0 0 0; font-size: 0.95rem; color: #666;">Manage museum collection and artwork catalog</p>
            </div>
            <div>
                <a href="{{ route('admin.artworks.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 4px; background-color: #2196F3; color: white; transition: background-color 0.2s;">
                    <span>➕</span> Create Artwork
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

    <!-- Quick Stats -->
    <div class="artworks-kpi-grid">
        <!-- Total Artworks -->
        <div class="artworks-kpi-card">
            <div class="kpi-icon-box kpi-icon-violet">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-palette"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">TOTAL ARTWORKS</div>
                <div class="kpi-value">{{ $totalArtworks ?? 0 }}</div>
                <div class="kpi-caption">in collection</div>
            </div>
        </div>
        
        <!-- Departments -->
        <div class="artworks-kpi-card">
            <div class="kpi-icon-box kpi-icon-bronze">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-landmark"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">DEPARTMENTS</div>
                <div class="kpi-value">{{ $totalDepartments ?? 0 }}</div>
                <div class="kpi-caption">categories</div>
            </div>
        </div>

        <!-- On Display -->
        <div class="artworks-kpi-card">
            <div class="kpi-icon-box kpi-icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">ON DISPLAY</div>
                <div class="kpi-value">{{ $onDisplay ?? 0 }}</div>
                <div class="kpi-caption">visible</div>
            </div>
        </div>
    </div>

    <!-- Artworks Grid -->
    <section class="table-section">
        <h2 class="section-title">Artwork Collection</h2>

        <!-- Unified Search, Filter, and Sort Bar -->
        <form action="{{ route('admin.artworks.index') }}" method="GET" style="margin-bottom: 2rem; background: #f8f9fa; border: 1px solid #e9ecef; padding: 1.25rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
                <!-- Global Search -->
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="search" style="font-size: 0.8rem; font-weight: 600; color: #495057;">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search artworks..." style="padding: 0.5rem 0.75rem; font-size: 0.9rem; border: 1px solid #ced4da; border-radius: 4px; background: white; transition: border-color 0.15s ease-in-out;">
                </div>

                <!-- Department Filter -->
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="department" style="font-size: 0.8rem; font-weight: 600; color: #495057;">Department</label>
                    <select name="department" id="department" style="padding: 0.5rem 0.75rem; font-size: 0.9rem; border: 1px solid #ced4da; border-radius: 4px; background: white; cursor: pointer; transition: border-color 0.15s ease-in-out;">
                        <option value="">All Departments</option>
                        @foreach($departmentsList as $dept)
                            <option value="{{ $dept->department_id }}" {{ request('department') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Classification Filter -->
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="classification" style="font-size: 0.8rem; font-weight: 600; color: #495057;">Classification</label>
                    <select name="classification" id="classification" style="padding: 0.5rem 0.75rem; font-size: 0.9rem; border: 1px solid #ced4da; border-radius: 4px; background: white; cursor: pointer; transition: border-color 0.15s ease-in-out;">
                        <option value="">All Classifications</option>
                        @foreach($classificationsList as $class)
                            <option value="{{ $class->classification_id }}" {{ request('classification') == $class->classification_id ? 'selected' : '' }}>
                                {{ $class->classification_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Object Type Filter -->
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="type" style="font-size: 0.8rem; font-weight: 600; color: #495057;">Object Type</label>
                    <select name="type" id="type" style="padding: 0.5rem 0.75rem; font-size: 0.9rem; border: 1px solid #ced4da; border-radius: 4px; background: white; cursor: pointer; transition: border-color 0.15s ease-in-out;">
                        <option value="">All Object Types</option>
                        @foreach($objectTypesList as $ot)
                            <option value="{{ $ot->type_id }}" {{ request('type') == $ot->type_id ? 'selected' : '' }}>
                                {{ $ot->object_type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Order -->
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="sort" style="font-size: 0.8rem; font-weight: 600; color: #495057;">Sort By</label>
                    <select name="sort" id="sort" style="padding: 0.5rem 0.75rem; font-size: 0.9rem; border: 1px solid #ced4da; border-radius: 4px; background: white; cursor: pointer; transition: border-color 0.15s ease-in-out;">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                        <option value="accession_year_desc" {{ request('sort') == 'accession_year_desc' ? 'selected' : '' }}>Accession Year (Newest)</option>
                        <option value="accession_year_asc" {{ request('sort') == 'accession_year_asc' ? 'selected' : '' }}>Accession Year (Oldest)</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="status" style="font-size: 0.8rem; font-weight: 600; color: #495057;">Status</label>
                    <select name="status" id="status" style="padding: 0.5rem 0.75rem; font-size: 0.9rem; border: 1px solid #ced4da; border-radius: 4px; background: white; cursor: pointer; transition: border-color 0.15s ease-in-out;">
                        <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="trashed" {{ request('status') == 'trashed' ? 'selected' : '' }}>Soft Deleted</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Artworks</option>
                    </select>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 600; border-radius: 4px; background-color: #2196F3; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; transition: background-color 0.2s;">
                        Filter
                    </button>
                    <a href="{{ route('admin.artworks.index') }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 600; border-radius: 4px; background-color: #e0e0e0; color: #333; border: none; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color 0.2s;">
                        Reset Filters
                    </a>
                </div>
            </div>
        </form>
        
        @forelse($artworks as $artwork)
            @if($loop->first)
                <div class="artworks-grid">
            @endif
            
            <!-- Artwork Card -->
            <div class="artwork-card" style="position: relative;">
                <!-- Main Link Wrapper to prevent nested anchor issue -->
                @if($artwork->trashed())
                    <div class="artwork-card-main-link" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%; opacity: 0.85;">
                @else
                    <a href="{{ route('admin.artworks.show', $artwork->art_work_id) }}" class="artwork-card-main-link" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%; transition: all 0.2s ease;">
                @endif
                    <!-- Image Container -->
                    <div class="artwork-image-container">
                        @php
                            $primaryImage = $artwork->images->firstWhere('is_primary', true) ?? $artwork->images->first();
                            $imageUrl = $primaryImage?->resolved_url;
                        @endphp
                        
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $artwork->title }}" class="artwork-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="artwork-image-fallback" style="display: none;">
                                <span>📷</span>
                                <p>Image unavailable</p>
                            </div>
                        @else
                            <div class="artwork-image-fallback">
                                <span>📷</span>
                                <p>No image</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Card Info -->
                    <div class="artwork-card-info" style="flex-grow: 1;">
                        <!-- Title -->
                        <h3 class="artwork-title">{{ $artwork->title ?? 'Untitled' }}</h3>
                        
                        <!-- Accession Number -->
                        @if($artwork->accession_number)
                            <p class="artwork-accession">{{ $artwork->accession_number }}</p>
                        @endif
                        
                        <!-- Artist -->
                        <p class="artwork-artist">
                            <strong>Artist:</strong>
                            @if($artwork->constituents->isNotEmpty())
                                {{ $artwork->constituents->pluck('display_name')->join(', ') }}
                            @else
                                <span style="color: #999;">Unknown</span>
                            @endif
                        </p>
                        
                        <!-- Department -->
                        <p class="artwork-department">
                            <strong>Department:</strong> {{ $artwork->department?->department_name ?? 'N/A' }}
                        </p>
                        
                        <!-- Date -->
                        <p class="artwork-date">
                            <strong>Date:</strong>
                            @if($artwork->object_date_display)
                                {{ $artwork->object_date_display }}
                            @elseif($artwork->object_begin_date)
                                {{ $artwork->object_begin_date }}
                            @else
                                <span style="color: #999;">N/A</span>
                            @endif
                        </p>
                    </div>
                @if($artwork->trashed())
                    </div>
                @else
                    </a>
                @endif

                <!-- Status & Action Buttons -->
                <div class="artwork-status-actions" style="padding: 0 1.2rem 1.2rem 1.2rem; margin-top: auto; background: white; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.8rem; border-top: 1px solid #eee;">
                        <div>
                            @if($artwork->trashed())
                                <span class="status-badge status-danger" style="background-color: #ffebee; color: #c62828;">🗑️ In Trash</span>
                            @elseif($artwork->is_on_view)
                                <span class="status-badge status-active">✓ On View</span>
                            @else
                                <span class="status-badge status-inactive">Not Displayed</span>
                            @endif
                        </div>
                        <div class="card-buttons" style="display: flex; gap: 0.4rem; align-items: center;">
                            @if($artwork->trashed())
                                <form action="{{ route('admin.artworks.restore', $artwork->art_work_id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-small-action" style="background: #e8f5e9; color: #2e7d32; border: none; font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer; transition: all 0.2s;" title="Restore Artwork">
                                        <span>🔄</span> Restore
                                    </button>
                                </form>
                                @if(auth()->user()->isSuperAdmin())
                                <form action="{{ route('admin.artworks.force-delete', $artwork->art_work_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('WARNING: This will permanently delete the artwork \'{{ addslashes($artwork->title) }}\' along with all its related records (images, measurements, geographies, etc.)! This action CANNOT BE UNDONE. Proceed?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-small-action" style="background: #ffebee; color: #c62828; border: none; font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer; transition: all 0.2s;" title="Permanently Delete">
                                        <span>🚨</span> Perm Delete
                                    </button>
                                </form>
                                @endif
                            @else
                                <a href="{{ route('admin.artworks.show', $artwork->art_work_id) }}" class="btn-small-action btn-view" style="text-decoration: none; font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 4px; background: #e3f2fd; color: #0d47a1; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; transition: all 0.2s;" title="View Detail">
                                    <span>👁️</span> View
                                </a>
                                <a href="{{ route('admin.artworks.edit', $artwork->art_work_id) }}" class="btn-small-action btn-edit" style="text-decoration: none; font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 4px; background: #efebe9; color: #4e342e; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; transition: all 0.2s;" title="Edit Artwork">
                                    <span>✏️</span> Edit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @if($loop->last)
                </div>
            @endif
        @empty
            <div style="text-align: center; padding: 4rem 2rem; color: #666; background: #f9f9f9; border-radius: 8px; border: 2px dashed #ddd; max-width: 600px; margin: 2rem auto;">
                <p style="font-size: 3rem; margin-bottom: 1rem; margin-top: 0;">📭</p>
                <h3 style="font-size: 1.25rem; color: #333; margin-bottom: 0.5rem; font-weight: 600;">No artworks found in the collection yet</h3>
                <p style="margin-bottom: 1.5rem; font-size: 0.9rem; color: #888; line-height: 1.5;">Start by adding artwork records to the catalog system to manage the museum collection.</p>
                <a href="{{ route('admin.artworks.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 4px; background-color: #2196F3; color: white; transition: background-color 0.2s;">
                    <span>➕</span> Create First Artwork
                </a>
            </div>
        @endforelse
        
        <!-- Pagination -->
        @if($artworks->hasPages())
            <div style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.85rem; color: #666;">
                    Showing {{ $artworks->firstItem() }} to {{ $artworks->lastItem() }} of {{ $artworks->total() }} artworks
                </div>
                <div>{{ $artworks->links() }}</div>
            </div>
        @endif
    </section>
</div>

<style>
.admin-page-section { max-width: 1400px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; }
.page-subtitle { font-size: 0.95rem; color: #666; margin: 0; }
.section-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 1.5rem; }

/* Artworks KPI Stats */
.artworks-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.artworks-kpi-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.artworks-kpi-card:hover {
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.06), 0 4px 12px -3px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
    border-color: rgba(203, 213, 225, 0.9);
}

.kpi-icon-box {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kpi-icon-violet { background: #eef2ff; color: #6366f1; border: 2px solid #e0e7ff; }
.kpi-icon-bronze { background: #fef3c7; color: #d97706; border: 2px solid #fde68a; }
.kpi-icon-green { background: #ecfdf5; color: #10b981; border: 2px solid #d1fae5; }

.kpi-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.kpi-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.02em;
    margin-bottom: 0.25rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.kpi-caption {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #94a3b8;
}

/* Artwork Grid */
.table-section { background: white; border-radius: 8px; padding: 1.5rem; }

.artworks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Artwork Card */
.artwork-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.artwork-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #2196F3;
}

/* Image Container */
.artwork-image-container {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    background-color: #f5f5f5;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.artwork-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background-color: #f5f5f5;
}

.artwork-image-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f5f5, #e8e8e8);
    color: #999;
}

.artwork-image-fallback span {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.artwork-image-fallback p {
    margin: 0;
    font-size: 0.9rem;
}

/* Card Info */
.artwork-card-info {
    padding: 1.2rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.artwork-title {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    line-height: 1.3;
    word-break: break-word;
}

.artwork-accession {
    margin: 0 0 0.8rem 0;
    font-size: 0.8rem;
    color: #999;
    font-weight: 500;
}

.artwork-artist,
.artwork-department,
.artwork-date {
    margin: 0.4rem 0;
    font-size: 0.85rem;
    color: #555;
    line-height: 1.4;
}

.artwork-artist strong,
.artwork-department strong,
.artwork-date strong {
    color: #333;
    font-weight: 600;
}

.artwork-status {
    margin-top: auto;
    padding-top: 0.8rem;
    border-top: 1px solid #e0e0e0;
}

/* Status Badges */
.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
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

/* Responsive */
@media (max-width: 768px) {
    .artworks-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
    
    .artwork-card-info {
        padding: 1rem;
    }
    
    .artwork-title {
        font-size: 0.95rem;
    }
}

@media (max-width: 480px) {
    .artworks-grid {
        grid-template-columns: 1fr;
    }
}

/* Premium Buttons & Usability Fixes */
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
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
}
.artwork-card-main-link {
    transition: background-color 0.2s ease;
}
.artwork-card-main-link:hover {
    background-color: #fafafa;
}
.btn-small-action {
    transition: all 0.2s ease;
}
.btn-small-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
.btn-view:hover {
    background-color: #bbdefb !important;
}
.btn-edit:hover {
    background-color: #d7ccc8 !important;
}
</style>
@endsection

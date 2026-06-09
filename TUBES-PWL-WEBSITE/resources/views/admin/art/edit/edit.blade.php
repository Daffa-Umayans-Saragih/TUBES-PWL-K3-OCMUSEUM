@extends('layouts.admin')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/admin/art/edit/edit.css')
@endpush

@section('title', 'Edit Artwork - Admin - MET Museum')
@section('page_title', 'Edit Artwork')

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1>Edit Artwork</h1>
        <div class="subtitle">Modified: {{ optional($artwork->updated_at)->format('M d, Y H:i') }}</div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>⚠ Please fix the following errors:</strong>
            <ul class="error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-container">
        <form action="{{ route('admin.art.update', $artwork->art_work_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="info-box">
                <strong>Object Number:</strong> {{ $artwork->accession_number }}
                <br><strong>Slug:</strong> {{ $artwork->slug }}
                <br><strong>Created:</strong> {{ optional($artwork->created_at)->format('M d, Y H:i') }}
            </div>

            <!-- Basic Information -->
            <div class="section-title">Basic Information</div>

            <div class="form-group @error('title') error @enderror">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $artwork->title) }}" required>
                @error('title') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="form-group @error('description') error @enderror">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required>{{ old('description', $artwork->description) }}</textarea>
                @error('description') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <!-- Classification -->
            <div class="section-title">Classification</div>

            <div class="form-group grid-2">
                <div class="field @error('department_id') error @enderror">
                    <label for="department_id">Department *</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" @selected(old('department_id', $artwork->department_id) == $dept->department_id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="error-message">{{ $message }}</span> @enderror
                </div>

                <div class="field @error('type_id') error @enderror">
                    <label for="type_id">Object Type *</label>
                    <select id="type_id" name="type_id" required>
                        <option value="">Select Type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->type_id }}" @selected(old('type_id', $artwork->type_id) == $type->type_id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('type_id') <span class="error-message">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Location -->
            <div class="section-title">Location</div>

            <div class="form-group grid-2">
                {{--
                <div class="field @error('geo_location_id') error @enderror">
                    <label for="geo_location_id">Geographic Location</label>
                    <select id="geo_location_id" name="geo_location_id">
                        <option value="">Select Geographic Location</option>
                        @foreach($geoLocations as $geo)
                            <option value="{{ $geo->geo_id }}" @selected(old('geo_location_id', $artwork->geo_id) == $geo->geo_id)>
                                {{ $geo->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('geo_location_id') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                --}}

                <div class="field @error('location_id') error @enderror">
                    <label for="location_id">Museum Location</label>
                    <select id="location_id" name="location_id">
                        <option value="">Select Museum Location</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->location_id }}" @selected(old('location_id', $artwork->location_id) == $loc->location_id)>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id') <span class="error-message">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Taxonomies & Attribution (ID Based for Sync) -->
            <div class="section-title">Taxonomies & Attribution</div>
            <div class="form-group grid-2">
                <div class="field">
                    <label>Medium IDs</label>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        @php
                            $mediumIds = $artwork->mediums->pluck('medium_id')->toArray();
                        @endphp
                        <input type="number" name="medium_ids[]" placeholder="Medium ID" value="{{ $mediumIds[0] ?? '' }}" min="1">
                        <input type="number" name="medium_ids[]" placeholder="Medium ID" value="{{ $mediumIds[1] ?? '' }}" min="1">
                        <input type="number" name="medium_ids[]" placeholder="Medium ID" value="{{ $mediumIds[2] ?? '' }}" min="1">
                    </div>
                    <small class="form-helper-text">Enter valid Medium IDs to attach.</small>
                </div>
                
                <div class="field">
                    <label>Primary Constituent (Attribution)</label>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        @php
                            $firstConst = $artwork->constituents->first();
                        @endphp
                        <input type="number" name="constituents[0][constituent_id]" placeholder="Constituent ID" value="{{ optional($firstConst)->constituent_id }}" min="1">
                        <input type="number" name="constituents[0][role_id]" placeholder="Role ID (e.g. 1 for Artist)" value="{{ optional(optional($firstConst)->pivot)->role_id }}" min="1">
                        <input type="number" name="constituents[0][display_order]" placeholder="Display Order" value="{{ optional(optional($firstConst)->pivot)->display_order ?? 1 }}" min="1">
                    </div>
                    <small class="form-helper-text">Enter Constituent ID and Role ID to attach.</small>
                </div>
            </div>

            <!-- Dating -->
            <div class="section-title">Dating</div>

            <div class="form-group grid-2">
                <div class="field @error('object_begin_date') error @enderror">
                    <label for="object_begin_date">Year Start</label>
                    <input type="number" id="object_begin_date" name="object_begin_date" value="{{ old('object_begin_date', $artwork->object_begin_date) }}" min="0" max="9999">
                    @error('object_begin_date') <span class="error-message">{{ $message }}</span> @enderror
                </div>

                <div class="field @error('object_end_date') error @enderror">
                    <label for="object_end_date">Year End</label>
                    <input type="number" id="object_end_date" name="object_end_date" value="{{ old('object_end_date', $artwork->object_end_date) }}" min="0" max="9999">
                    @error('object_end_date') <span class="error-message">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Object Number -->
            <div class="section-title">Accession Information</div>

            <div class="form-group @error('accession_number') error @enderror">
                <label for="accession_number">Object Number *</label>
                <input type="text" id="accession_number" name="accession_number" value="{{ old('accession_number', $artwork->accession_number) }}" required>
                <small class="form-helper-text">Must be unique. Example: 1998.242</small>
                @error('accession_number') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <!-- Images -->
            <div class="section-title">Images</div>

            @if($artwork->images && $artwork->images->count() > 0)
                <div class="mb-2rem">
                    <label class="form-section-label">Existing Images</label>
                    <div class="image-preview-grid">
                        @foreach($artwork->images as $image)
                            <div class="image-preview-item">
                                <img src="{{ $image->resolved_url }}" alt="Artwork image">
                                <form action="{{ route('admin.art.image.delete', $image->image_id) }}" method="POST" class="form-inline" onsubmit="return confirm('Delete this image?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="image-delete-btn" title="Delete image">✕</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-group @error('images') error @enderror">
                <label>Upload New Images</label>
                <div class="file-upload-area" id="uploadArea">
                    <div class="file-upload-icon">📸</div>
                    <div class="file-upload-text">
                        <p class="mb-0-5rem">Drag images here or click to select</p>
                        <small class="text-muted-light">JPEG, PNG, GIF - Max 5MB per image</small>
                    </div>
                </div>
                <input type="file" id="images" name="images[]" class="file-upload-input" multiple accept="image/*">
                <div id="imagePreview" class="image-preview-grid"></div>
                @error('images') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.art.index') }}" class="btn-secondary">Cancel</a>
                <form action="{{ route('admin.art.destroy', $artwork->art_work_id) }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone?');" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete Artwork</button>
                </form>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle file upload area
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('images');
    const imagePreview = document.getElementById('imagePreview');

    // Click to upload
    uploadArea.addEventListener('click', () => fileInput.click());

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        fileInput.files = e.dataTransfer.files;
        displayPreview();
    });

    // File selection
    fileInput.addEventListener('change', displayPreview);

    function displayPreview() {
        imagePreview.innerHTML = '';
        const files = fileInput.files;

        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const item = document.createElement('div');
                item.className = 'image-preview-item';
                item.innerHTML = `<img src="${e.target.result}" alt="Preview ${index + 1}">`;
                imagePreview.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endsection



@extends('admin.layout.layout')

@section('admin-title')
    {{ $title }}
@endsection

@section('admin-content')
<div class="admin-page-section">
    <div class="page-header">
        <h1>{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <!-- Form -->
    <div class="form-container">
        <form action="{{ $isEdit ? route('admin.artworks.update', $artwork->art_work_id) : route('admin.artworks.store') }}" 
              method="POST" class="admin-form" id="artworkForm" enctype="multipart/form-data" novalidate>
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success" style="padding: 1rem; border-radius: 4px; margin-bottom: 1rem; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; font-weight: 500;">
                    <strong>✅ Success:</strong> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" style="padding: 1rem; border-radius: 4px; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; font-weight: 500;">
                    <strong>⚠️ Error:</strong> {{ session('error') }}
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <!-- TEMPORARY SAFE DEBUG: Laravel Validation Errors (inspect DOM or view page source to see) -->
                <!-- Raw Validation Failures JSON: {{ json_encode($errors->toArray()) }} -->
                
                <div class="alert alert-danger" style="padding: 1rem; border-radius: 4px; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                    <strong>⚠️ Validation Errors:</strong> There were validation errors. Please check the fields below.
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- SECTION 1: BASIC INFORMATION -->
            <div class="form-section">
                <h3 class="section-title">Basic Information</h3>

                <!-- MET Object ID -->
                <div class="form-group">
                    <label for="met_object_id" class="form-label">MET Object ID <span class="required">*</span></label>
                    <input type="number" id="met_object_id" name="met_object_id" class="form-control @error('met_object_id') is-invalid @enderror"
                        placeholder="Enter MET Museum object ID"
                        value="{{ old('met_object_id', $artwork?->met_object_id ?? '') }}" required>
                    @error('met_object_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Title -->
                <div class="form-group">
                    <label for="title" class="form-label">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                        placeholder="Enter artwork title"
                        value="{{ old('title', $artwork?->title ?? '') }}" required>
                    @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Accession Number & Year -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="accession_number" class="form-label">Accession Number <span class="required">*</span></label>
                        <input type="text" id="accession_number" name="accession_number" class="form-control @error('accession_number') is-invalid @enderror"
                            placeholder="e.g. 1997.219.4"
                            value="{{ old('accession_number', $artwork?->accession_number ?? '') }}" required>
                        @error('accession_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="accession_year" class="form-label">Accession Year</label>
                        <input type="number" id="accession_year" name="accession_year" class="form-control @error('accession_year') is-invalid @enderror"
                            placeholder="e.g. 1997"
                            value="{{ old('accession_year', $artwork?->accession_year ?? '') }}"
                            min="1000" max="2100">
                        @error('accession_year')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                        placeholder="Enter artwork description" rows="4">{{ old('description', $artwork?->description ?? '') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Gallery Number -->
                <div class="form-group">
                    <label for="gallery_number" class="form-label">Gallery Number</label>
                    <input type="text" id="gallery_number" name="gallery_number" class="form-control @error('gallery_number') is-invalid @enderror"
                        placeholder="Gallery location"
                        value="{{ old('gallery_number', $artwork?->gallery_number ?? '') }}">
                    @error('gallery_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 2: DATING & DIMENSIONS -->
            <div class="form-section">
                <h3 class="section-title">Dating & Dimensions</h3>

                <!-- Object Date Display -->
                <div class="form-group">
                    <label for="object_date_display" class="form-label">Date Display (Text)</label>
                    <input type="text" id="object_date_display" name="object_date_display" class="form-control @error('object_date_display') is-invalid @enderror"
                        placeholder="e.g. ca. 1810"
                        value="{{ old('object_date_display', $artwork?->object_date_display ?? '') }}">
                    @error('object_date_display')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Date Range -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="object_begin_date" class="form-label">Begin Date (Year)</label>
                        <input type="number" id="object_begin_date" name="object_begin_date" class="form-control @error('object_begin_date') is-invalid @enderror"
                            placeholder="Starting year"
                            value="{{ old('object_begin_date', $artwork?->object_begin_date ?? '') }}"
                            min="1000" max="2100">
                        @error('object_begin_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="object_end_date" class="form-label">End Date (Year)</label>
                        <input type="number" id="object_end_date" name="object_end_date" class="form-control @error('object_end_date') is-invalid @enderror"
                            placeholder="Ending year"
                            value="{{ old('object_end_date', $artwork?->object_end_date ?? '') }}"
                            min="1000" max="2100">
                        @error('object_end_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Dimensions -->
                <div class="form-group">
                    <label for="dimensions_display" class="form-label">Dimensions (Text)</label>
                    <textarea id="dimensions_display" name="dimensions_display" class="form-control @error('dimensions_display') is-invalid @enderror"
                        placeholder="e.g. H. 25 1/2 in. (64.8 cm)" rows="2">{{ old('dimensions_display', $artwork?->dimensions_display ?? '') }}</textarea>
                    @error('dimensions_display')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 3: CLASSIFICATION & LOCATION -->
            <div class="form-section">
                <h3 class="section-title">Classification & Location</h3>

                <!-- Department -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="department_id" class="form-label" style="margin: 0;">Department <span class="required">*</span></label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNew('department')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                    </div>
                    
                    <div id="department_select_group">
                        <select id="department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" 
                                    {{ old('department_id', $artwork?->department_id) == $dept->department_id ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="department_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_department" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Department Name <span class="required">*</span></label>
                        <input type="text" id="new_department" name="new_department" class="form-control @error('new_department') is-invalid @enderror" placeholder="Enter new department name" value="{{ old('new_department') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNew('department', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_department')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('department_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Object Type -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="type_id" class="form-label" style="margin: 0;">Object Type <span class="required">*</span></label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNew('object_type')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                    </div>
                    
                    <div id="object_type_select_group">
                        <select id="type_id" name="type_id" class="form-control @error('type_id') is-invalid @enderror" required>
                            <option value="">-- Select Object Type --</option>
                            @foreach($objectTypes as $type)
                                <option value="{{ $type->type_id }}"
                                    {{ old('type_id', $artwork?->type_id) == $type->type_id ? 'selected' : '' }}>
                                    {{ $type->object_type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="object_type_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_object_type" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Object Type Name <span class="required">*</span></label>
                        <input type="text" id="new_object_type" name="new_object_type" class="form-control @error('new_object_type') is-invalid @enderror" placeholder="Enter new object type name" value="{{ old('new_object_type') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNew('object_type', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_object_type')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('type_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Classification -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="classification_id" class="form-label" style="margin: 0;">Classification</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNew('classification')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                    </div>
                    
                    <div id="classification_select_group">
                        <select id="classification_id" name="classification_id" class="form-control @error('classification_id') is-invalid @enderror">
                            <option value="">-- Select Classification --</option>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->classification_id }}"
                                    {{ old('classification_id', $artwork?->classification_id) == $classification->classification_id ? 'selected' : '' }}>
                                    {{ $classification->classification_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="classification_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_classification" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Classification Name <span class="required">*</span></label>
                        <input type="text" id="new_classification" name="new_classification" class="form-control @error('new_classification') is-invalid @enderror" placeholder="Enter new classification name" value="{{ old('new_classification') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNew('classification', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_classification')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('classification_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Location -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="location_id" class="form-label" style="margin: 0;">Location <span class="required">*</span></label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNew('location')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                    </div>
                    
                    <div id="location_select_group">
                        <select id="location_id" name="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->location_id }}"
                                    {{ old('location_id', $artwork?->location_id) == $location->location_id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="location_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_location" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Location Name <span class="required">*</span></label>
                        <input type="text" id="new_location" name="new_location" class="form-control @error('new_location') is-invalid @enderror" placeholder="Enter new location name" value="{{ old('new_location') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNew('location', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_location')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('location_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Repository -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="repository_id" class="form-label" style="margin: 0;">Repository <span class="required">*</span></label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNew('repository')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                    </div>
                    
                    <div id="repository_select_group">
                        <select id="repository_id" name="repository_id" class="form-control @error('repository_id') is-invalid @enderror" required>
                            <option value="">-- Select Repository --</option>
                            @foreach($repositories as $repo)
                                <option value="{{ $repo->repository_id }}"
                                    {{ old('repository_id', $artwork?->repository_id) == $repo->repository_id ? 'selected' : '' }}>
                                    {{ $repo->repository_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="repository_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_repository" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Repository Name <span class="required">*</span></label>
                        <input type="text" id="new_repository" name="new_repository" class="form-control @error('new_repository') is-invalid @enderror" placeholder="Enter new repository name" value="{{ old('new_repository') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNew('repository', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_repository')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('repository_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Credit Line -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="credit_line_id" class="form-label" style="margin: 0;">Credit Line</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNew('credit_line')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                    </div>
                    
                    <div id="credit_line_select_group">
                        <select id="credit_line_id" name="credit_line_id" class="form-control @error('credit_line_id') is-invalid @enderror">
                            <option value="">-- Select Credit Line --</option>
                            @foreach($creditLines as $credit)
                                <option value="{{ $credit->credit_line_id }}"
                                    {{ old('credit_line_id', $artwork?->credit_line_id) == $credit->credit_line_id ? 'selected' : '' }}>
                                    {{ $credit->credit_line_text }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="credit_line_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_credit_line" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Credit Line Text <span class="required">*</span></label>
                        <input type="text" id="new_credit_line" name="new_credit_line" class="form-control @error('new_credit_line') is-invalid @enderror" placeholder="Enter new credit line text" value="{{ old('new_credit_line') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNew('credit_line', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_credit_line')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('credit_line_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 4: PHYSICAL ATTRIBUTES -->
            <div class="form-section">
                <h3 class="section-title">Physical Attributes</h3>

                <!-- Materials -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="materials" class="form-label" style="margin: 0;">Materials</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('material')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Material</button>
                    </div>
                    
                    <select id="materials" name="materials[]" class="form-control @error('materials') is-invalid @enderror" 
                        multiple size="8">
                        @foreach($materials as $material)
                            <option value="{{ $material->material_id }}"
                                {{ in_array($material->material_id, old('materials', $artwork?->materials->pluck('material_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $material->material_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple</small>

                    <div id="material_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_material" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Material Name <span class="required">*</span></label>
                        <input type="text" id="new_material" name="new_material" class="form-control @error('new_material') is-invalid @enderror" placeholder="Enter new material name" value="{{ old('new_material') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('material', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_material')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('materials')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Mediums -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="mediums" class="form-label" style="margin: 0;">Mediums</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('medium')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Medium</button>
                    </div>

                    <select id="mediums" name="mediums[]" class="form-control @error('mediums') is-invalid @enderror"
                        multiple size="8">
                        @foreach($mediums as $medium)
                            <option value="{{ $medium->medium_id }}"
                                {{ in_array($medium->medium_id, old('mediums', $artwork?->mediums->pluck('medium_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $medium->medium_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple</small>

                    <div id="medium_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_medium" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Medium Name <span class="required">*</span></label>
                        <input type="text" id="new_medium" name="new_medium" class="form-control @error('new_medium') is-invalid @enderror" placeholder="Enter new medium name" value="{{ old('new_medium') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('medium', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_medium')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('mediums')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 5: CULTURAL & HISTORICAL CONTEXT -->
            <div class="form-section">
                <h3 class="section-title">Cultural & Historical Context</h3>

                <!-- Cultures -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="cultures" class="form-label" style="margin: 0;">Cultures</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('culture')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Culture</button>
                    </div>
                    
                    <select id="cultures" name="cultures[]" class="form-control @error('cultures') is-invalid @enderror"
                        multiple size="6">
                        @foreach($cultures as $culture)
                            <option value="{{ $culture->culture_id }}"
                                {{ in_array($culture->culture_id, old('cultures', $artwork?->cultures->pluck('culture_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $culture->culture_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple</small>

                    <div id="culture_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_culture" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Culture Name <span class="required">*</span></label>
                        <input type="text" id="new_culture" name="new_culture" class="form-control @error('new_culture') is-invalid @enderror" placeholder="Enter new culture name" value="{{ old('new_culture') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('culture', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_culture')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('cultures')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Periods -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="periods" class="form-label" style="margin: 0;">Periods</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('period')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Period</button>
                    </div>

                    <select id="periods" name="periods[]" class="form-control @error('periods') is-invalid @enderror"
                        multiple size="6">
                        @foreach($periods as $period)
                            <option value="{{ $period->period_id }}"
                                {{ in_array($period->period_id, old('periods', $artwork?->periods->pluck('period_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $period->period_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple</small>

                    <div id="period_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_period" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Period Name <span class="required">*</span></label>
                        <input type="text" id="new_period" name="new_period" class="form-control @error('new_period') is-invalid @enderror" placeholder="Enter new period name" value="{{ old('new_period') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('period', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_period')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('periods')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Dynasties -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="dynasties" class="form-label" style="margin: 0;">Dynasties</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('dynasty')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Dynasty</button>
                    </div>

                    <select id="dynasties" name="dynasties[]" class="form-control @error('dynasties') is-invalid @enderror"
                        multiple size="6">
                        @foreach($dynasties as $dynasty)
                            <option value="{{ $dynasty->dynasty_id }}"
                                {{ in_array($dynasty->dynasty_id, old('dynasties', $artwork?->dynasties->pluck('dynasty_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $dynasty->dynasty_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple</small>

                    <div id="dynasty_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_dynasty" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Dynasty Name <span class="required">*</span></label>
                        <input type="text" id="new_dynasty" name="new_dynasty" class="form-control @error('new_dynasty') is-invalid @enderror" placeholder="Enter new dynasty name" value="{{ old('new_dynasty') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('dynasty', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_dynasty')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('dynasties')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Reigns -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="reigns" class="form-label" style="margin: 0;">Reigns</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('reign')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Reign</button>
                    </div>

                    <select id="reigns" name="reigns[]" class="form-control @error('reigns') is-invalid @enderror"
                        multiple size="6">
                        @foreach($reigns as $reign)
                            <option value="{{ $reign->reign_id }}"
                                {{ in_array($reign->reign_id, old('reigns', $artwork?->reigns->pluck('reign_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $reign->reign_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple</small>

                    <div id="reign_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_reign" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Reign Name <span class="required">*</span></label>
                        <input type="text" id="new_reign" name="new_reign" class="form-control @error('new_reign') is-invalid @enderror" placeholder="Enter new reign name" value="{{ old('new_reign') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('reign', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_reign')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('reigns')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Tags -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="tags" class="form-label" style="margin: 0;">Tags</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('tag')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Tag</button>
                    </div>

                    <select id="tags" name="tags[]" class="form-control @error('tags') is-invalid @enderror"
                        multiple size="6">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->tag_id }}"
                                {{ in_array($tag->tag_id, old('tags', $artwork?->tags->pluck('tag_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $tag->tag_term }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple</small>

                    <div id="tag_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_tag" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Tag Term <span class="required">*</span></label>
                        <input type="text" id="new_tag" name="new_tag" class="form-control @error('new_tag') is-invalid @enderror" placeholder="Enter new tag term" value="{{ old('new_tag') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('tag', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_tag')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('tags')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Portfolios -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="portfolios" class="form-label" style="margin: 0;">Portfolios</label>
                        <button type="button" class="btn-toggle-new" onclick="toggleAddNewM2M('portfolio')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Portfolio</button>
                    </div>

                    <select id="portfolios" name="portfolios[]" class="form-control @error('portfolios') is-invalid @enderror"
                        multiple size="6">
                        @foreach($portfolios as $portfolio)
                            <option value="{{ $portfolio->portfolio_id }}"
                                {{ in_array($portfolio->portfolio_id, old('portfolios', $artwork?->portfolios->pluck('portfolio_id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $portfolio->portfolio_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl to select multiple</small>

                    <div id="portfolio_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                        <label for="new_portfolio" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Portfolio Name <span class="required">*</span></label>
                        <input type="text" id="new_portfolio" name="new_portfolio" class="form-control @error('new_portfolio') is-invalid @enderror" placeholder="Enter new portfolio name" value="{{ old('new_portfolio') }}">
                        <button type="button" class="btn btn-secondary" onclick="toggleAddNewM2M('portfolio', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                        @error('new_portfolio')
                            <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('portfolios')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 5A: CONSTITUENTS (ARTISTS & CONTRIBUTORS) -->
            <div class="form-section">
                <h3 class="section-title">Artists & Contributors</h3>
                <p style="color: #666; margin-bottom: 1rem;">Add artists, architects, photographers, and other contributors to this artwork.</p>

                <!-- Existing Constituents -->
                @if($isEdit && $artwork && $artwork->constituents->isNotEmpty())
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 1rem;">Current Contributors ({{ $artwork->constituents->count() }})</h4>
                        <div class="constituents-list">
                            @foreach($artwork->constituents as $constituent)
                                <div class="constituent-item">
                                    <div class="constituent-info">
                                        <strong>{{ $constituent->display_name }}</strong>
                                        @if($constituent->birth_year || $constituent->death_year)
                                            <small>({{ $constituent->birth_year ?? '?' }}-{{ $constituent->death_year ?? '?' }})</small>
                                        @endif
                                    </div>
                                    <div class="constituent-controls">
                                        <select class="form-control-small" style="width: 120px; display: inline-block;">
                                            <option value="">Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->role_id }}" {{ $constituent->pivot->role_id == $role->role_id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-small" onclick="alert('Constituent editing in form will be implemented. For now, use the show page.')">
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Add New Constituent -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                    <h4 style="margin-bottom: 1rem;">Add Contributor</h4>
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="constituent_select" class="form-label" style="margin: 0;">Select Contributor</label>
                            <button type="button" class="btn-toggle-new" onclick="toggleAddNewConstituent('constituent')" style="background: none; border: none; color: #2196F3; font-size: 0.85rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Contributor</button>
                        </div>
                        
                        <div id="constituent_select_group">
                            <select id="constituent_select" name="new_constituent_id" class="form-control @error('new_constituent_id') is-invalid @enderror">
                                <option value="">-- Select a contributor --</option>
                                @foreach($constituents as $constituent)
                                    <option value="{{ $constituent->constituent_id }}" {{ old('new_constituent_id') == $constituent->constituent_id ? 'selected' : '' }}>
                                        {{ $constituent->display_name }}
                                        @if($constituent->birth_year || $constituent->death_year)
                                            ({{ $constituent->birth_year ?? '?' }}-{{ $constituent->death_year ?? '?' }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('new_constituent_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="constituent_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                            <label for="constituent_input" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Contributor Display Name <span class="required">*</span></label>
                            <input type="text" id="constituent_input" name="new_constituent" class="form-control @error('new_constituent') is-invalid @enderror" placeholder="Enter display name" value="{{ old('new_constituent') }}">
                            <button type="button" class="btn btn-secondary" onclick="toggleAddNewConstituent('constituent', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                            @error('new_constituent')
                                <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- Role -->
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label for="constituent_role_select" class="form-label" style="margin: 0;">Role</label>
                                <button type="button" class="btn-toggle-new" onclick="toggleAddNewConstituent('constituent_role')" style="background: none; border: none; color: #2196F3; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Role</button>
                            </div>

                            <div id="constituent_role_select_group">
                                <select id="constituent_role_select" name="new_constituent_role" class="form-control @error('new_constituent_role') is-invalid @enderror">
                                    <option value="">-- No Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->role_id }}" {{ old('new_constituent_role') == $role->role_id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                                @error('new_constituent_role')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="constituent_role_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                                <label for="constituent_role_input" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Role Name <span class="required">*</span></label>
                                <input type="text" id="constituent_role_input" name="new_role" class="form-control @error('new_role') is-invalid @enderror" placeholder="Enter role name" value="{{ old('new_role') }}">
                                <button type="button" class="btn btn-secondary" onclick="toggleAddNewConstituent('constituent_role', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                                @error('new_role')
                                    <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Prefix -->
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label for="constituent_prefix_select" class="form-label" style="margin: 0;">Prefix</label>
                                <button type="button" class="btn-toggle-new" onclick="toggleAddNewConstituent('constituent_prefix')" style="background: none; border: none; color: #2196F3; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Prefix</button>
                            </div>

                            <div id="constituent_prefix_select_group">
                                <select id="constituent_prefix_select" name="new_constituent_prefix" class="form-control @error('new_constituent_prefix') is-invalid @enderror">
                                    <option value="">-- No Prefix --</option>
                                    @foreach($prefixes as $prefix)
                                        <option value="{{ $prefix->prefix_id }}" {{ old('new_constituent_prefix') == $prefix->prefix_id ? 'selected' : '' }}>{{ $prefix->prefix_name }}</option>
                                    @endforeach
                                </select>
                                @error('new_constituent_prefix')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="constituent_prefix_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                                <label for="constituent_prefix_input" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Prefix Name <span class="required">*</span></label>
                                <input type="text" id="constituent_prefix_input" name="new_prefix" class="form-control @error('new_prefix') is-invalid @enderror" placeholder="Enter prefix name" value="{{ old('new_prefix') }}">
                                <button type="button" class="btn btn-secondary" onclick="toggleAddNewConstituent('constituent_prefix', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                                @error('new_prefix')
                                    <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Suffix -->
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label for="constituent_suffix_select" class="form-label" style="margin: 0;">Suffix</label>
                                <button type="button" class="btn-toggle-new" onclick="toggleAddNewConstituent('constituent_suffix')" style="background: none; border: none; color: #2196F3; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New Suffix</button>
                            </div>

                            <div id="constituent_suffix_select_group">
                                <select id="constituent_suffix_select" name="new_constituent_suffix" class="form-control @error('new_constituent_suffix') is-invalid @enderror">
                                    <option value="">-- No Suffix --</option>
                                    @foreach($suffixes as $suffix)
                                        <option value="{{ $suffix->suffix_id }}" {{ old('new_constituent_suffix') == $suffix->suffix_id ? 'selected' : '' }}>{{ $suffix->suffix_name }}</option>
                                    @endforeach
                                </select>
                                @error('new_constituent_suffix')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="constituent_suffix_new_group" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 4px;">
                                <label for="constituent_suffix_input" class="form-label" style="font-size: 0.8rem; color: #555; display: block; margin-bottom: 0.25rem;">New Suffix Name <span class="required">*</span></label>
                                <input type="text" id="constituent_suffix_input" name="new_suffix" class="form-control @error('new_suffix') is-invalid @enderror" placeholder="Enter suffix name" value="{{ old('new_suffix') }}">
                                <button type="button" class="btn btn-secondary" onclick="toggleAddNewConstituent('constituent_suffix', false)" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px;">Cancel</button>
                                @error('new_suffix')
                                    <span class="invalid-feedback" style="display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 7: ADDITIONAL INFORMATION -->
            <div class="form-section">
                <h3 class="section-title">Additional Information</h3>

                <!-- Provenance -->
                <div class="form-group">
                    <label for="provenance" class="form-label">Provenance</label>
                    <textarea id="provenance" name="provenance" class="form-control @error('provenance') is-invalid @enderror"
                        placeholder="Artwork ownership history" rows="3">{{ old('provenance', $artwork?->provenance ?? '') }}</textarea>
                    @error('provenance')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Rights & Reproduction -->
                <div class="form-group">
                    <label for="rights_and_reproduction" class="form-label">Rights & Reproduction</label>
                    <textarea id="rights_and_reproduction" name="rights_and_reproduction" class="form-control @error('rights_and_reproduction') is-invalid @enderror"
                        placeholder="Copyright and reproduction information" rows="3">{{ old('rights_and_reproduction', $artwork?->rights_and_reproduction ?? '') }}</textarea>
                    @error('rights_and_reproduction')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- SECTION 7B: MEASUREMENTS -->
            <div class="form-section">
                <h3 class="section-title">Measurements</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">Add measurements such as dimensions (Height, Width, Depth), weight, etc. Completely empty rows will be ignored.</p>
                
                <table class="table" id="measurements_table" style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 0.5rem; width: 25%;">Type (e.g., Dimension)</th>
                            <th style="padding: 0.5rem; width: 25%;">Name (e.g., Height) <span class="required">*</span></th>
                            <th style="padding: 0.5rem; width: 20%;">Value <span class="required">*</span></th>
                            <th style="padding: 0.5rem; width: 20%;">Unit (e.g., cm) <span class="required">*</span></th>
                            <th style="padding: 0.5rem; width: 10%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="measurements_tbody">
                        @php
                            $oldMeasurements = old('measurements');
                            $measurementsData = [];
                            if (is_array($oldMeasurements)) {
                                foreach ($oldMeasurements as $i => $row) {
                                    $measurementsData[] = (object)[
                                        'measurement_type' => $row['type'] ?? '',
                                        'measurement_name' => $row['name'] ?? '',
                                        'measurement_value' => $row['value'] ?? '',
                                        'measurement_unit' => $row['unit'] ?? '',
                                    ];
                                }
                            } elseif ($isEdit && isset($artwork) && $artwork->measurements->isNotEmpty()) {
                                $measurementsData = $artwork->measurements;
                            }
                        @endphp
                        
                        @forelse($measurementsData as $index => $m)
                            <tr class="measurement-row" data-index="{{ $index }}" style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="measurements[{{ $index }}][type]" class="form-control" placeholder="Type" value="{{ $m->measurement_type }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="measurements[{{ $index }}][name]" class="form-control" placeholder="Name" value="{{ $m->measurement_name }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="number" step="any" name="measurements[{{ $index }}][value]" class="form-control" placeholder="Value" value="{{ $m->measurement_value }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="measurements[{{ $index }}][unit]" class="form-control" placeholder="Unit" value="{{ $m->measurement_unit }}">
                                </td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeMeasurementRow(this)" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="measurement-row" data-index="0" style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="measurements[0][type]" class="form-control" placeholder="Type" value="">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="measurements[0][name]" class="form-control" placeholder="Name" value="">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="number" step="any" name="measurements[0][value]" class="form-control" placeholder="Value" value="">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="measurements[0][unit]" class="form-control" placeholder="Unit" value="">
                                </td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeMeasurementRow(this)" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary btn-sm" onclick="addMeasurementRow()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">+ Add Measurement</button>
            </div>

            <!-- SECTION 7C: REFERENCES -->
            <div class="form-section">
                <h3 class="section-title">References</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">Add bibliographic references, citations, or publications related to this artwork. Completely empty rows will be ignored.</p>
                
                <div id="references_container" style="margin-bottom: 1rem;">
                    @php
                        $oldReferences = old('references');
                        $referencesData = [];
                        if (is_array($oldReferences)) {
                            foreach ($oldReferences as $i => $row) {
                                $referencesData[] = (object)[
                                    'reference_text' => $row['text'] ?? '',
                                ];
                            }
                        } elseif ($isEdit && isset($artwork) && $artwork->references->isNotEmpty()) {
                            $referencesData = $artwork->references;
                        }
                    @endphp
                    
                    @forelse($referencesData as $index => $r)
                        <div class="reference-row" data-index="{{ $index }}" style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                            <input type="text" name="references[{{ $index }}][text]" class="form-control" placeholder="Reference citation text" value="{{ $r->reference_text }}" style="flex: 1;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeReferenceRow(this)" style="padding: 0.4rem 0.6rem;">Delete</button>
                        </div>
                    @empty
                        <div class="reference-row" data-index="0" style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                            <input type="text" name="references[0][text]" class="form-control" placeholder="Reference citation text" value="" style="flex: 1;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeReferenceRow(this)" style="padding: 0.4rem 0.6rem;">Delete</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addReferenceRow()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">+ Add Reference</button>
            </div>

            <!-- SECTION 7D: SIGNATURES, INSCRIPTIONS & MARKINGS (SIMs) -->
            <div class="form-section">
                <h3 class="section-title">Signatures, Inscriptions & Markings (SIMs)</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">Add signatures, inscriptions, markings, or other texts physically present on the artwork. Completely empty rows will be ignored.</p>
                
                <table class="table" id="sims_table" style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 0.5rem; width: 30%;">Type <span class="required">*</span></th>
                            <th style="padding: 0.5rem; width: 60%;">Text <span class="required">*</span></th>
                            <th style="padding: 0.5rem; width: 10%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sims_tbody">
                        @php
                            $oldSims = old('sims');
                            $simsData = [];
                            if (is_array($oldSims)) {
                                foreach ($oldSims as $i => $row) {
                                    $simsData[] = (object)[
                                        'sim_type' => $row['type'] ?? '',
                                        'sim_text' => $row['text'] ?? '',
                                    ];
                                }
                            } elseif ($isEdit && isset($artwork) && $artwork->artWorkSims->isNotEmpty()) {
                                $simsData = $artwork->artWorkSims;
                            }
                        @endphp
                        
                        @forelse($simsData as $index => $s)
                            <tr class="sim-row" data-index="{{ $index }}" style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.5rem;">
                                    <select name="sims[{{ $index }}][type]" class="form-control">
                                        <option value="">-- Select Type --</option>
                                        <option value="Signature" {{ $s->sim_type == 'Signature' ? 'selected' : '' }}>Signature</option>
                                        <option value="Inscription" {{ $s->sim_type == 'Inscription' ? 'selected' : '' }}>Inscription</option>
                                        <option value="Marking" {{ $s->sim_type == 'Marking' ? 'selected' : '' }}>Marking</option>
                                    </select>
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="sims[{{ $index }}][text]" class="form-control" placeholder="SIM text content" value="{{ $s->sim_text }}">
                                </td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSimRow(this)" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="sim-row" data-index="0" style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.5rem;">
                                    <select name="sims[0][type]" class="form-control">
                                        <option value="">-- Select Type --</option>
                                        <option value="Signature">Signature</option>
                                        <option value="Inscription">Inscription</option>
                                        <option value="Marking">Marking</option>
                                    </select>
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="sims[0][text]" class="form-control" placeholder="SIM text content" value="">
                                </td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSimRow(this)" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary btn-sm" onclick="addSimRow()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">+ Add SIM</button>
            </div>

            <!-- SECTION 7E: EXHIBITION HISTORIES -->
            <div class="form-section">
                <h3 class="section-title">Exhibition Histories</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">Add exhibition history records. Completely empty rows will be ignored. Exhibition Title is required if a row is filled.</p>
                
                <div id="exhibitions_container" style="margin-bottom: 1rem;">
                    @php
                        $oldExhibitions = old('exhibition_histories');
                        $exhibitionsData = [];
                        if (is_array($oldExhibitions)) {
                            foreach ($oldExhibitions as $i => $row) {
                                $exhibitionsData[] = (object)[
                                    'exhibition_title' => $row['title'] ?? '',
                                    'venue_name' => $row['venue'] ?? '',
                                    'city_name' => $row['city'] ?? '',
                                    'exhibition_date_display' => $row['date_display'] ?? '',
                                    'start_date' => $row['start_date'] ?? '',
                                    'end_date' => $row['end_date'] ?? '',
                                    'catalogue_reference' => $row['catalogue'] ?? '',
                                    'exhibition_notes' => $row['notes'] ?? '',
                                ];
                            }
                        } elseif ($isEdit && isset($artwork) && $artwork->exhibitionHistories->isNotEmpty()) {
                            foreach ($artwork->exhibitionHistories as $eh) {
                                $exhibitionsData[] = (object)[
                                    'exhibition_title' => $eh->exhibition_title,
                                    'venue_name' => $eh->venue_name,
                                    'city_name' => $eh->city_name,
                                    'exhibition_date_display' => $eh->exhibition_date_display,
                                    'start_date' => $eh->start_date ? $eh->start_date->format('Y-m-d') : '',
                                    'end_date' => $eh->end_date ? $eh->end_date->format('Y-m-d') : '',
                                    'catalogue_reference' => $eh->catalogue_reference,
                                    'exhibition_notes' => $eh->exhibition_notes,
                                ];
                            }
                        }
                    @endphp

                    @forelse($exhibitionsData as $index => $eh)
                        <div class="exhibition-row" data-index="{{ $index }}" style="border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background-color: #fafafa; position: relative;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeExhibitionRow(this)" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem; margin-top: 1rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Exhibition Title <span class="required">*</span></label>
                                    <input type="text" name="exhibition_histories[{{ $index }}][title]" class="form-control" placeholder="Title of Exhibition" value="{{ $eh->exhibition_title }}">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Venue Name</label>
                                    <input type="text" name="exhibition_histories[{{ $index }}][venue]" class="form-control" placeholder="Venue Location" value="{{ $eh->venue_name }}">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">City Name</label>
                                    <input type="text" name="exhibition_histories[{{ $index }}][city]" class="form-control" placeholder="City" value="{{ $eh->city_name }}">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Date Display</label>
                                    <input type="text" name="exhibition_histories[{{ $index }}][date_display]" class="form-control" placeholder="e.g. Autumn 2026" value="{{ $eh->exhibition_date_display }}">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Start Date</label>
                                    <input type="date" name="exhibition_histories[{{ $index }}][start_date]" class="form-control" value="{{ $eh->start_date }}">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">End Date</label>
                                    <input type="date" name="exhibition_histories[{{ $index }}][end_date]" class="form-control" value="{{ $eh->end_date }}">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Catalogue Reference</label>
                                    <input type="text" name="exhibition_histories[{{ $index }}][catalogue]" class="form-control" placeholder="Catalogue Ref" value="{{ $eh->catalogue_reference }}">
                                </div>
                            </div>
                            <div>
                                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Exhibition Notes</label>
                                <textarea name="exhibition_histories[{{ $index }}][notes]" class="form-control" rows="2" placeholder="Any additional notes...">{{ $eh->exhibition_notes }}</textarea>
                            </div>
                        </div>
                    @empty
                        <div class="exhibition-row" data-index="0" style="border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background-color: #fafafa; position: relative;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeExhibitionRow(this)" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem; margin-top: 1rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Exhibition Title <span class="required">*</span></label>
                                    <input type="text" name="exhibition_histories[0][title]" class="form-control" placeholder="Title of Exhibition" value="">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Venue Name</label>
                                    <input type="text" name="exhibition_histories[0][venue]" class="form-control" placeholder="Venue Location" value="">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">City Name</label>
                                    <input type="text" name="exhibition_histories[0][city]" class="form-control" placeholder="City" value="">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Date Display</label>
                                    <input type="text" name="exhibition_histories[0][date_display]" class="form-control" placeholder="e.g. Autumn 2026" value="">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Start Date</label>
                                    <input type="date" name="exhibition_histories[0][start_date]" class="form-control" value="">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">End Date</label>
                                    <input type="date" name="exhibition_histories[0][end_date]" class="form-control" value="">
                                </div>
                                <div>
                                    <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Catalogue Reference</label>
                                    <input type="text" name="exhibition_histories[0][catalogue]" class="form-control" placeholder="Catalogue Ref" value="">
                                </div>
                            </div>
                            <div>
                                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Exhibition Notes</label>
                                <textarea name="exhibition_histories[0][notes]" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addExhibitionRow()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">+ Add Exhibition History</button>
            </div>

            <!-- SECTION 7F: ARTWORK IMAGES -->
            <div class="form-section">
                <h3 class="section-title">Artwork Images</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">Add artwork images. You can upload a file OR provide an image URL. Exactly one image must be selected as Primary.</p>

                <div id="images_container" style="margin-bottom: 1rem;">
                    @php
                        $oldImages = old('images');
                        $oldPrimary = old('primary_image_index');
                        $imagesData = [];
                        
                        if (is_array($oldImages)) {
                            foreach ($oldImages as $i => $row) {
                                $url = $row['url'] ?? '';
                                $mode = $row['mode'] ?? 'url';
                                $resolved = '';
                                if ($url) {
                                    if (str_starts_with($url, 'http')) {
                                        $resolved = $url;
                                    } elseif (str_starts_with($url, '/storage/') || str_starts_with($url, 'storage/')) {
                                        $resolved = asset(ltrim($url, '/'));
                                    } else {
                                        $resolved = asset('storage/' . ltrim($url, '/'));
                                    }
                                }
                                $imagesData[] = (object)[
                                    'mode' => $mode,
                                    'image_url' => $url,
                                    'resolved_url' => $resolved,
                                    'is_primary' => ($oldPrimary !== null && $oldPrimary == $i),
                                ];
                            }
                        } elseif ($isEdit && isset($artwork) && $artwork->images->isNotEmpty()) {
                            foreach ($artwork->images as $img) {
                                $imagesData[] = (object)[
                                    'mode' => 'url',
                                    'image_url' => $img->image_url,
                                    'resolved_url' => $img->resolved_url,
                                    'is_primary' => $img->is_primary,
                                ];
                            }
                        }
                    @endphp

                    @forelse($imagesData as $index => $img)
                        <div class="image-row" data-index="{{ $index }}" style="border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; background-color: #fafafa; position: relative;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeImageRow(this)" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                            
                            <!-- Mode Toggle -->
                            <div class="input-mode-toggle" style="margin-bottom: 1rem; padding: 0.75rem; background: #fff; border-radius: 8px; border: 1px solid #e0e0e0; display: inline-flex; gap: 1.5rem;">
                                <span style="font-weight: bold; color: #555;">Image Source:</span>
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #2196F3; font-weight: 600;">
                                    <input type="radio" name="images[{{ $index }}][mode]" value="file" {{ (!isset($img->mode) || $img->mode === 'file') ? 'checked' : '' }} onchange="toggleImageInputMode(this, {{ $index }})" style="accent-color: #2196F3;"> Upload File
                                </label>
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #555; font-weight: 600;">
                                    <input type="radio" name="images[{{ $index }}][mode]" value="url" {{ (isset($img->mode) && $img->mode === 'url') ? 'checked' : '' }} onchange="toggleImageInputMode(this, {{ $index }})" style="accent-color: #555;"> Use URL
                                </label>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1rem;">
                                <!-- Upload Local File -->
                                <div id="group-file-{{ $index }}" class="form-group dropzone-container" style="background: #fff; padding: 1.5rem; border-radius: 8px; border: 2px dashed #ccc; transition: all 0.3s; text-align: center; cursor: pointer; position: relative; {{ (isset($img->mode) && $img->mode === 'url') ? 'opacity: 0.5; pointer-events: none;' : '' }}"
                                     ondragover="handleDragOver(event, this)"
                                     ondragleave="handleDragLeave(event, this)"
                                     ondrop="handleFileDrop(event, {{ $index }}, this)"
                                     onclick="document.getElementById('file-input-{{ $index }}').click()">
                                    <div style="pointer-events: none;">
                                        <svg style="width: 48px; height: 48px; color: #2196F3; margin-bottom: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        <p style="margin: 0; font-weight: bold; color: #333;">Drag & Drop image here</p>
                                        <p style="margin: 0; color: #666; font-size: 0.9rem;">or click to browse</p>
                                        <p id="file-name-{{ $index }}" style="margin-top: 0.5rem; color: #2196F3; font-size: 0.85rem; font-weight: bold; word-break: break-all;"></p>
                                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">Accepted: JPG, PNG, WEBP. Max size: 5MB.</small>
                                    </div>
                                    <input type="file" id="file-input-{{ $index }}" name="images[{{ $index }}][file]" class="form-control" accept=".jpg,.jpeg,.png,.webp" onchange="handleFileSelect(this, {{ $index }})" {{ (isset($img->mode) && $img->mode === 'url') ? 'disabled' : '' }} style="display: none;">
                                </div>

                                <!-- OR Enter URL -->
                                <div id="group-url-{{ $index }}" class="form-group" style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px dashed #ccc; transition: all 0.3s; {{ (!isset($img->mode) || $img->mode === 'file') ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                                    <label class="form-label" style="font-weight: bold; color: #555;">OR Image URL</label>
                                    <input type="text" name="images[{{ $index }}][url]" class="form-control image-url-input" placeholder="https://example.com/image.jpg" value="{{ $img->image_url }}" onkeyup="updateRowImagePreview(this, {{ $index }})" onchange="updateRowImagePreview(this, {{ $index }})" {{ (!isset($img->mode) || $img->mode === 'file') ? 'disabled' : '' }}>
                                    <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">Must be a direct image link.</small>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 2rem; align-items: start;">
                                <div style="flex: 1;">
                                    <img id="img-preview-{{ $index }}" class="img-preview" src="{{ $img->resolved_url }}" style="max-height: 150px; border-radius: 4px; border: 1px solid #ddd; display: {{ $img->resolved_url ? 'block' : 'none' }};" onerror="this.style.display='none'">
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; padding-top: 0.5rem;">
                                    <input type="radio" name="primary_image_index" value="{{ $index }}" class="form-check-input primary-radio" {{ $img->is_primary ? 'checked' : '' }} style="margin-top: 0; width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: #2196F3;">
                                    <label class="form-check-label" style="font-weight: bold; cursor: pointer; margin-bottom: 0;">Set as Primary Image</label>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="image-row" data-index="0" style="border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; background-color: #fafafa; position: relative;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeImageRow(this)" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                            
                            <!-- Mode Toggle -->
                            <div class="input-mode-toggle" style="margin-bottom: 1rem; padding: 0.75rem; background: #fff; border-radius: 8px; border: 1px solid #e0e0e0; display: inline-flex; gap: 1.5rem;">
                                <span style="font-weight: bold; color: #555;">Image Source:</span>
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #2196F3; font-weight: 600;">
                                    <input type="radio" name="images[0][mode]" value="file" checked onchange="toggleImageInputMode(this, 0)" style="accent-color: #2196F3;"> Upload File
                                </label>
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #555; font-weight: 600;">
                                    <input type="radio" name="images[0][mode]" value="url" onchange="toggleImageInputMode(this, 0)" style="accent-color: #555;"> Use URL
                                </label>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1rem;">
                                <!-- Upload Local File -->
                                <div id="group-file-0" class="form-group dropzone-container" style="background: #fff; padding: 1.5rem; border-radius: 8px; border: 2px dashed #ccc; transition: all 0.3s; text-align: center; cursor: pointer; position: relative;"
                                     ondragover="handleDragOver(event, this)"
                                     ondragleave="handleDragLeave(event, this)"
                                     ondrop="handleFileDrop(event, 0, this)"
                                     onclick="document.getElementById('file-input-0').click()">
                                    <div style="pointer-events: none;">
                                        <svg style="width: 48px; height: 48px; color: #2196F3; margin-bottom: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        <p style="margin: 0; font-weight: bold; color: #333;">Drag & Drop image here</p>
                                        <p style="margin: 0; color: #666; font-size: 0.9rem;">or click to browse</p>
                                        <p id="file-name-0" style="margin-top: 0.5rem; color: #2196F3; font-size: 0.85rem; font-weight: bold; word-break: break-all;"></p>
                                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">Accepted: JPG, PNG, WEBP. Max size: 5MB.</small>
                                    </div>
                                    <input type="file" id="file-input-0" name="images[0][file]" class="form-control" accept=".jpg,.jpeg,.png,.webp" onchange="handleFileSelect(this, 0)" style="display: none;">
                                </div>

                                <!-- OR Enter URL -->
                                <div id="group-url-0" class="form-group" style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px dashed #ccc; transition: all 0.3s; opacity: 0.5; pointer-events: none;">
                                    <label class="form-label" style="font-weight: bold; color: #555;">OR Image URL</label>
                                    <input type="text" name="images[0][url]" class="form-control image-url-input" placeholder="https://example.com/image.jpg" value="" onkeyup="updateRowImagePreview(this, 0)" onchange="updateRowImagePreview(this, 0)" disabled>
                                    <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">Must be a direct image link.</small>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 2rem; align-items: start;">
                                <div style="flex: 1;">
                                    <img id="img-preview-0" class="img-preview" src="" style="max-height: 150px; border-radius: 4px; border: 1px solid #ddd; display: none;" onerror="this.style.display='none'">
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; padding-top: 0.5rem;">
                                    <input type="radio" name="primary_image_index" value="0" class="form-check-input primary-radio" checked style="margin-top: 0; width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: #2196F3;">
                                    <label class="form-check-label" style="font-weight: bold; cursor: pointer; margin-bottom: 0;">Set as Primary Image</label>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addImageRow()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">+ Add Image</button>
            </div>

            <!-- SECTION 7G: GEOGRAPHIES -->
            <div class="form-section">
                <h3 class="section-title">Geographies</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">Add geography records (Excavation, River, Country, Region, etc.). All fields are optional. Completely empty rows will be ignored.</p>

                <div id="geographies_container" style="margin-bottom: 1rem;">
                    @php
                        $oldGeographies = old('geographies');
                        $geographiesData = [];
                        
                        if (is_array($oldGeographies)) {
                            foreach ($oldGeographies as $i => $row) {
                                $geographiesData[] = (object)[
                                    'geography_type_id' => $row['geography_type_id'] ?? '',
                                    'geography_type_new' => $row['geography_type_new'] ?? '',
                                    'country_id' => $row['country_id'] ?? '',
                                    'country_new' => $row['country_new'] ?? '',
                                    'state_id' => $row['state_id'] ?? '',
                                    'state_new' => $row['state_new'] ?? '',
                                    'county_id' => $row['county_id'] ?? '',
                                    'county_new' => $row['county_new'] ?? '',
                                    'city_id' => $row['city_id'] ?? '',
                                    'city_new' => $row['city_new'] ?? '',
                                    'region_id' => $row['region_id'] ?? '',
                                    'region_new' => $row['region_new'] ?? '',
                                    'subregion_id' => $row['subregion_id'] ?? '',
                                    'subregion_new' => $row['subregion_new'] ?? '',
                                    'locale_id' => $row['locale_id'] ?? '',
                                    'locale_new' => $row['locale_new'] ?? '',
                                    'locus_id' => $row['locus_id'] ?? '',
                                    'locus_new' => $row['locus_new'] ?? '',
                                    'excavation_id' => $row['excavation_id'] ?? '',
                                    'excavation_new' => $row['excavation_new'] ?? '',
                                    'river_id' => $row['river_id'] ?? '',
                                    'river_new' => $row['river_new'] ?? '',
                                ];
                            }
                        } elseif ($isEdit && isset($artwork) && $artwork->geographies->isNotEmpty()) {
                            foreach ($artwork->geographies as $g) {
                                $geographiesData[] = (object)[
                                    'geography_type_id' => $g->geography_type_id,
                                    'geography_type_new' => '',
                                    'country_id' => $g->country_id,
                                    'country_new' => '',
                                    'state_id' => $g->state_id,
                                    'state_new' => '',
                                    'county_id' => $g->county_id,
                                    'county_new' => '',
                                    'city_id' => $g->city_id,
                                    'city_new' => '',
                                    'region_id' => $g->region_id,
                                    'region_new' => '',
                                    'subregion_id' => $g->subregion_id,
                                    'subregion_new' => '',
                                    'locale_id' => $g->locale_id,
                                    'locale_new' => '',
                                    'locus_id' => $g->locus_id,
                                    'locus_new' => '',
                                    'excavation_id' => $g->excavation_id,
                                    'excavation_new' => '',
                                    'river_id' => $g->river_id,
                                    'river_new' => '',
                                ];
                            }
                        }
                    @endphp

                    @forelse($geographiesData as $index => $g)
                        <div class="geography-row" data-index="{{ $index }}" style="border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; background-color: #fafafa; position: relative; border-left: 4px solid #2196F3;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeGeographyRow(this)" style="position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                            
                            <!-- Group 1: Core & Global (2 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
                                <!-- Geography Type -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Geography Type</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'geography_type')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][geography_type_id]" class="form-control geo-select">
                                            <option value="">-- Select Type --</option>
                                            @foreach($geographyTypes as $gt)
                                                <option value="{{ $gt->geography_type_id }}" {{ $g->geography_type_id == $gt->geography_type_id ? 'selected' : '' }}>{{ $gt->geography_type_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][geography_type_new]" class="form-control geo-new-input" placeholder="New Type Name" value="{{ $g->geography_type_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'geography_type', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Country -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Country</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'country')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][country_id]" class="form-control geo-select">
                                            <option value="">-- Select Country --</option>
                                            @foreach($countries as $c)
                                                <option value="{{ $c->country_id }}" {{ $g->country_id == $c->country_id ? 'selected' : '' }}>{{ $c->country_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][country_new]" class="form-control geo-new-input" placeholder="New Country Name" value="{{ $g->country_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'country', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 2: Political Hierarchy (3 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
                                <!-- State -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">State / Province</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'state')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][state_id]" class="form-control geo-select">
                                            <option value="">-- Select State --</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st->state_id }}" {{ $g->state_id == $st->state_id ? 'selected' : '' }}>{{ $st->state_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][state_new]" class="form-control geo-new-input" placeholder="New State Name" value="{{ $g->state_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'state', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- County -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">County</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'county')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][county_id]" class="form-control geo-select">
                                            <option value="">-- Select County --</option>
                                            @foreach($counties as $co)
                                                <option value="{{ $co->county_id }}" {{ $g->county_id == $co->county_id ? 'selected' : '' }}>{{ $co->county_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][county_new]" class="form-control geo-new-input" placeholder="New County Name" value="{{ $g->county_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'county', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- City -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">City</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'city')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][city_id]" class="form-control geo-select">
                                            <option value="">-- Select City --</option>
                                            @foreach($cities as $ci)
                                                <option value="{{ $ci->city_id }}" {{ $g->city_id == $ci->city_id ? 'selected' : '' }}>{{ $ci->city_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][city_new]" class="form-control geo-new-input" placeholder="New City Name" value="{{ $g->city_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'city', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 3: Archaeological Hierarchy (4 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
                                <!-- Region -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Region</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'region')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][region_id]" class="form-control geo-select">
                                            <option value="">-- Select Region --</option>
                                            @foreach($regions as $r)
                                                <option value="{{ $r->region_id }}" {{ $g->region_id == $r->region_id ? 'selected' : '' }}>{{ $r->region_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][region_new]" class="form-control geo-new-input" placeholder="New Region Name" value="{{ $g->region_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'region', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Subregion -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Subregion</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'subregion')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][subregion_id]" class="form-control geo-select">
                                            <option value="">-- Select Subregion --</option>
                                            @foreach($subregions as $sub)
                                                <option value="{{ $sub->subregion_id }}" {{ $g->subregion_id == $sub->subregion_id ? 'selected' : '' }}>{{ $sub->subregion_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][subregion_new]" class="form-control geo-new-input" placeholder="New Subregion Name" value="{{ $g->subregion_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'subregion', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Locale -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Locale</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'locale')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][locale_id]" class="form-control geo-select">
                                            <option value="">-- Select Locale --</option>
                                            @foreach($locales as $loc)
                                                <option value="{{ $loc->locale_id }}" {{ $g->locale_id == $loc->locale_id ? 'selected' : '' }}>{{ $loc->locale_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][locale_new]" class="form-control geo-new-input" placeholder="New Locale Name" value="{{ $g->locale_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'locale', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Locus -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Locus</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'locus')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][locus_id]" class="form-control geo-select">
                                            <option value="">-- Select Locus --</option>
                                            @foreach($loci as $lc)
                                                <option value="{{ $lc->locus_id }}" {{ $g->locus_id == $lc->locus_id ? 'selected' : '' }}>{{ $lc->locus_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][locus_new]" class="form-control geo-new-input" placeholder="New Locus Name" value="{{ $g->locus_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'locus', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 4: Specific Features (2 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                <!-- Excavation -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Excavation</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'excavation')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][excavation_id]" class="form-control geo-select">
                                            <option value="">-- Select Excavation --</option>
                                            @foreach($excavations as $e)
                                                <option value="{{ $e->excavation_id }}" {{ $g->excavation_id == $e->excavation_id ? 'selected' : '' }}>{{ $e->excavation_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][excavation_new]" class="form-control geo-new-input" placeholder="New Excavation Name" value="{{ $g->excavation_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'excavation', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- River -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">River</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'river')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[{{ $index }}][river_id]" class="form-control geo-select">
                                            <option value="">-- Select River --</option>
                                            @foreach($rivers as $rv)
                                                <option value="{{ $rv->river_id }}" {{ $g->river_id == $rv->river_id ? 'selected' : '' }}>{{ $rv->river_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[{{ $index }}][river_new]" class="form-control geo-new-input" placeholder="New River Name" value="{{ $g->river_new ?? '' }}">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'river', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="geography-row" data-index="0" style="border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; background-color: #fafafa; position: relative; border-left: 4px solid #2196F3;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeGeographyRow(this)" style="position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                            
                            <!-- Group 1: Core & Global (2 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
                                <!-- Geography Type -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Geography Type</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'geography_type')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][geography_type_id]" class="form-control geo-select">
                                            <option value="">-- Select Type --</option>
                                            @foreach($geographyTypes as $gt)
                                                <option value="{{ $gt->geography_type_id }}">{{ $gt->geography_type_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][geography_type_new]" class="form-control geo-new-input" placeholder="New Type Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'geography_type', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Country -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Country</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'country')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][country_id]" class="form-control geo-select">
                                            <option value="">-- Select Country --</option>
                                            @foreach($countries as $c)
                                                <option value="{{ $c->country_id }}">{{ $c->country_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][country_new]" class="form-control geo-new-input" placeholder="New Country Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'country', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 2: Political Hierarchy (3 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
                                <!-- State -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">State / Province</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'state')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][state_id]" class="form-control geo-select">
                                            <option value="">-- Select State --</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st->state_id }}">{{ $st->state_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][state_new]" class="form-control geo-new-input" placeholder="New State Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'state', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- County -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">County</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'county')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][county_id]" class="form-control geo-select">
                                            <option value="">-- Select County --</option>
                                            @foreach($counties as $co)
                                                <option value="{{ $co->county_id }}">{{ $co->county_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][county_new]" class="form-control geo-new-input" placeholder="New County Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'county', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- City -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">City</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'city')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][city_id]" class="form-control geo-select">
                                            <option value="">-- Select City --</option>
                                            @foreach($cities as $ci)
                                                <option value="{{ $ci->city_id }}">{{ $ci->city_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][city_new]" class="form-control geo-new-input" placeholder="New City Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'city', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 3: Archaeological Hierarchy (4 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
                                <!-- Region -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Region</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'region')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][region_id]" class="form-control geo-select">
                                            <option value="">-- Select Region --</option>
                                            @foreach($regions as $r)
                                                <option value="{{ $r->region_id }}">{{ $r->region_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][region_new]" class="form-control geo-new-input" placeholder="New Region Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'region', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Subregion -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Subregion</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'subregion')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][subregion_id]" class="form-control geo-select">
                                            <option value="">-- Select Subregion --</option>
                                            @foreach($subregions as $sub)
                                                <option value="{{ $sub->subregion_id }}">{{ $sub->subregion_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][subregion_new]" class="form-control geo-new-input" placeholder="New Subregion Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'subregion', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Locale -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Locale</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'locale')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][locale_id]" class="form-control geo-select">
                                            <option value="">-- Select Locale --</option>
                                            @foreach($locales as $loc)
                                                <option value="{{ $loc->locale_id }}">{{ $loc->locale_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][locale_new]" class="form-control geo-new-input" placeholder="New Locale Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'locale', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- Locus -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Locus</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'locus')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][locus_id]" class="form-control geo-select">
                                            <option value="">-- Select Locus --</option>
                                            @foreach($loci as $lc)
                                                <option value="{{ $lc->locus_id }}">{{ $lc->locus_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][locus_new]" class="form-control geo-new-input" placeholder="New Locus Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'locus', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 4: Specific Features (2 columns) -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                <!-- Excavation -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Excavation</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'excavation')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][excavation_id]" class="form-control geo-select">
                                            <option value="">-- Select Excavation --</option>
                                            @foreach($excavations as $e)
                                                <option value="{{ $e->excavation_id }}">{{ $e->excavation_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][excavation_new]" class="form-control geo-new-input" placeholder="New Excavation Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'excavation', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>

                                <!-- River -->
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">River</label>
                                        <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'river')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                                    </div>
                                    <div class="select-grp">
                                        <select name="geographies[0][river_id]" class="form-control geo-select">
                                            <option value="">-- Select River --</option>
                                            @foreach($rivers as $rv)
                                                <option value="{{ $rv->river_id }}">{{ $rv->river_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                                        <input type="text" name="geographies[0][river_new]" class="form-control geo-new-input" placeholder="New River Name" value="">
                                        <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'river', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addGeographyRow()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">+ Add Geography</button>
            </div>

            <!-- SECTION 8: FLAGS -->
            <div class="form-section">
                <h3 class="section-title">Display Flags</h3>

                <!-- Flags -->
                <div class="form-flags">
                    <div class="form-check">
                        <input type="checkbox" id="is_on_view" name="is_on_view" class="form-check-input" value="1"
                            {{ old('is_on_view', $artwork?->is_on_view) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_on_view">
                            On View (Currently displayed in museum)
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="is_highlight" name="is_highlight" class="form-check-input" value="1"
                            {{ old('is_highlight', $artwork?->is_highlight) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_highlight">
                            Highlight (Featured artwork)
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="is_public_domain" name="is_public_domain" class="form-check-input" value="1"
                            {{ old('is_public_domain', $artwork?->is_public_domain) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public_domain">
                            Public Domain (Allowed for reproduction)
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="is_timeline_work" name="is_timeline_work" class="form-check-input" value="1"
                            {{ old('is_timeline_work', $artwork?->is_timeline_work) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_timeline_work">
                            Timeline Work (Include in timeline)
                        </label>
                    </div>
                </div>
            </div>

            <!-- SECTION 9: SUBMIT BUTTONS -->
            <div class="form-section">
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? '💾 Update Artwork' : '➕ Create Artwork' }}
                    </button>
                    <a href="{{ route('admin.artworks.index') }}" class="btn btn-secondary">
                        ← Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.admin-page-section {
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
}

.form-container {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.admin-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-section {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.form-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 1rem 0;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.required {
    color: #dc3545;
    font-weight: bold;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.75rem;
    font-size: 0.95rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #2196F3;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-flags {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-check {
    display: flex;
    align-items: center;
}

.form-check-input {
    margin-right: 0.75rem;
    cursor: pointer;
    width: 18px;
    height: 18px;
}

.form-check-label {
    cursor: pointer;
    margin: 0;
}

.form-text {
    display: block;
    font-size: 0.85rem;
    color: #666;
    margin-top: 0.25rem;
}

.form-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
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

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert ul {
    padding-left: 1.25rem;
}

.alert li {
    margin-bottom: 0.25rem;
}

/* Image Management Styles */
.existing-images {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1rem;
}

.image-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    align-items: flex-start;
}

.image-preview {
    flex-shrink: 0;
    width: 150px;
    height: 150px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.image-info {
    flex-grow: 1;
    min-width: 0;
}

.image-primary-checkbox {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem 0.75rem;
    background: #f0f0f0;
    border-radius: 4px;
    font-size: 0.9rem;
}

.image-primary-checkbox:hover {
    background: #e0e0e0;
}

.btn-small {
    padding: 0.4rem 0.75rem;
    font-size: 0.85rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn-danger-small {
    background-color: #dc3545;
    color: white;
}

.btn-danger-small:hover {
    background-color: #c82333;
}

/* Constituent Management Styles */
.constituents-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1rem;
}

.constituent-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    gap: 1rem;
}

.constituent-info {
    flex-grow: 1;
}

.constituent-info strong {
    display: block;
    color: #333;
    margin-bottom: 0.25rem;
}

.constituent-info small {
    color: #999;
}

.constituent-controls {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.form-control-small {
    padding: 0.4rem 0.5rem;
    font-size: 0.85rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-buttons {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
    }

    .image-item {
        flex-direction: column;
    }

    .image-preview {
        width: 100%;
    }
}

/* Validation Feedback Styling */
.is-invalid {
    border-color: #dc3545 !important;
    background-color: #fff8f8 !important;
}
.invalid-feedback {
    display: block !important;
    color: #dc3545 !important;
    font-size: 0.85rem !important;
    margin-top: 0.25rem !important;
    font-weight: 500 !important;
}
</style>

<script>
function toggleAddNew(field, show = true) {
    const selectGroup = document.getElementById(field + '_select_group');
    const newGroup = document.getElementById(field + '_new_group');
    const selectEl = document.getElementById(field === 'object_type' ? 'type_id' : (field === 'credit_line' ? 'credit_line_id' : field + '_id'));
    const newInput = document.getElementById('new_' + field);

    if (show) {
        if (selectGroup) selectGroup.style.display = 'none';
        if (newGroup) newGroup.style.display = 'block';
        if (selectEl) {
            selectEl.value = '';
            selectEl.removeAttribute('required');
        }
        if (newInput) {
            newInput.setAttribute('required', 'required');
            newInput.focus();
        }
    } else {
        if (selectGroup) selectGroup.style.display = 'block';
        if (newGroup) newGroup.style.display = 'none';
        if (newInput) {
            newInput.value = '';
            newInput.removeAttribute('required');
        }
        if (selectEl) {
            if (['location_id', 'type_id', 'repository_id', 'department_id'].includes(selectEl.id)) {
                selectEl.setAttribute('required', 'required');
            }
        }
    }
}

function toggleAddNewM2M(field, show = true) {
    const newGroup = document.getElementById(field + '_new_group');
    const newInput = document.getElementById('new_' + field);

    if (show) {
        if (newGroup) newGroup.style.display = 'block';
        if (newInput) {
            newInput.setAttribute('required', 'required');
            newInput.focus();
        }
    } else {
        if (newGroup) newGroup.style.display = 'none';
        if (newInput) {
            newInput.value = '';
            newInput.removeAttribute('required');
        }
    }
}

function toggleAddNewConstituent(field, show = true) {
    const selectGroup = document.getElementById(field + '_select_group');
    const newGroup = document.getElementById(field + '_new_group');
    const selectEl = document.getElementById(field + '_select');
    const newInput = document.getElementById(field + '_input');

    if (show) {
        if (selectGroup) selectGroup.style.display = 'none';
        if (newGroup) newGroup.style.display = 'block';
        if (selectEl) {
            selectEl.value = '';
            selectEl.removeAttribute('required');
        }
        if (newInput) {
            newInput.setAttribute('required', 'required');
            newInput.focus();
        }
    } else {
        if (selectGroup) selectGroup.style.display = 'block';
        if (newGroup) newGroup.style.display = 'none';
        if (newInput) {
            newInput.value = '';
            newInput.removeAttribute('required');
        }
    }
}

// Dynamic Measurement Rows
let measurementIndex = document.querySelectorAll('.measurement-row').length;
function addMeasurementRow() {
    const tbody = document.getElementById('measurements_tbody');
    const tr = document.createElement('tr');
    tr.className = 'measurement-row';
    tr.dataset.index = measurementIndex;
    tr.style.borderBottom = '1px solid #eee';
    tr.innerHTML = `
        <td style="padding: 0.5rem;">
            <input type="text" name="measurements[${measurementIndex}][type]" class="form-control" placeholder="Type" value="">
        </td>
        <td style="padding: 0.5rem;">
            <input type="text" name="measurements[${measurementIndex}][name]" class="form-control" placeholder="Name" value="">
        </td>
        <td style="padding: 0.5rem;">
            <input type="number" step="any" name="measurements[${measurementIndex}][value]" class="form-control" placeholder="Value" value="">
        </td>
        <td style="padding: 0.5rem;">
            <input type="text" name="measurements[${measurementIndex}][unit]" class="form-control" placeholder="Unit" value="">
        </td>
        <td style="padding: 0.5rem; text-align: center;">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMeasurementRow(this)" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
        </td>
    `;
    tbody.appendChild(tr);
    measurementIndex++;
}
function removeMeasurementRow(button) {
    const row = button.closest('.measurement-row');
    const tbody = document.getElementById('measurements_tbody');
    if (tbody.querySelectorAll('.measurement-row').length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('input').forEach(input => input.value = '');
    }
}

// Dynamic Reference Rows
let referenceIndex = document.querySelectorAll('.reference-row').length;
function addReferenceRow() {
    const container = document.getElementById('references_container');
    const div = document.createElement('div');
    div.className = 'reference-row';
    div.dataset.index = referenceIndex;
    div.style.display = 'flex';
    div.style.gap = '0.5rem';
    div.style.alignItems = 'center';
    div.style.marginBottom = '0.5rem';
    div.innerHTML = `
        <input type="text" name="references[${referenceIndex}][text]" class="form-control" placeholder="Reference citation text" value="" style="flex: 1;">
        <button type="button" class="btn btn-danger btn-sm" onclick="removeReferenceRow(this)" style="padding: 0.4rem 0.6rem;">Delete</button>
    `;
    container.appendChild(div);
    referenceIndex++;
}
function removeReferenceRow(button) {
    const row = button.closest('.reference-row');
    const container = document.getElementById('references_container');
    if (container.querySelectorAll('.reference-row').length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('input').forEach(input => input.value = '');
    }
}

// Dynamic SIM Rows
let simIndex = document.querySelectorAll('.sim-row').length;
function addSimRow() {
    const tbody = document.getElementById('sims_tbody');
    const tr = document.createElement('tr');
    tr.className = 'sim-row';
    tr.dataset.index = simIndex;
    tr.style.borderBottom = '1px solid #eee';
    tr.innerHTML = `
        <td style="padding: 0.5rem;">
            <select name="sims[${simIndex}][type]" class="form-control">
                <option value="">-- Select Type --</option>
                <option value="Signature">Signature</option>
                <option value="Inscription">Inscription</option>
                <option value="Marking">Marking</option>
            </select>
        </td>
        <td style="padding: 0.5rem;">
            <input type="text" name="sims[${simIndex}][text]" class="form-control" placeholder="SIM text content" value="">
        </td>
        <td style="padding: 0.5rem; text-align: center;">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeSimRow(this)" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
        </td>
    `;
    tbody.appendChild(tr);
    simIndex++;
}
function removeSimRow(button) {
    const row = button.closest('.sim-row');
    const tbody = document.getElementById('sims_tbody');
    if (tbody.querySelectorAll('.sim-row').length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('input, select').forEach(el => el.value = '');
    }
}

// Dynamic Exhibition History Rows
let exhibitionIndex = document.querySelectorAll('.exhibition-row').length;
function addExhibitionRow() {
    const container = document.getElementById('exhibitions_container');
    const div = document.createElement('div');
    div.className = 'exhibition-row';
    div.dataset.index = exhibitionIndex;
    div.style = 'border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background-color: #fafafa; position: relative;';
    div.innerHTML = `
        <button type="button" class="btn btn-danger btn-sm" onclick="removeExhibitionRow(this)" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem; margin-top: 1rem;">
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Exhibition Title <span class="required">*</span></label>
                <input type="text" name="exhibition_histories[${exhibitionIndex}][title]" class="form-control" placeholder="Title of Exhibition" value="">
            </div>
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Venue Name</label>
                <input type="text" name="exhibition_histories[${exhibitionIndex}][venue]" class="form-control" placeholder="Venue Location" value="">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">City Name</label>
                <input type="text" name="exhibition_histories[${exhibitionIndex}][city]" class="form-control" placeholder="City" value="">
            </div>
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Date Display</label>
                <input type="text" name="exhibition_histories[${exhibitionIndex}][date_display]" class="form-control" placeholder="e.g. Autumn 2026" value="">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Start Date</label>
                <input type="date" name="exhibition_histories[${exhibitionIndex}][start_date]" class="form-control" value="">
            </div>
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">End Date</label>
                <input type="date" name="exhibition_histories[${exhibitionIndex}][end_date]" class="form-control" value="">
            </div>
            <div>
                <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Catalogue Reference</label>
                <input type="text" name="exhibition_histories[${exhibitionIndex}][catalogue]" class="form-control" placeholder="Catalogue Ref" value="">
            </div>
        </div>
        <div>
            <label class="form-label" style="font-weight: 500; font-size: 0.85rem;">Exhibition Notes</label>
            <textarea name="exhibition_histories[${exhibitionIndex}][notes]" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
        </div>
    `;
    container.appendChild(div);
    exhibitionIndex++;
}
function removeExhibitionRow(button) {
    const row = button.closest('.exhibition-row');
    const container = document.getElementById('exhibitions_container');
    if (container.querySelectorAll('.exhibition-row').length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('input, textarea').forEach(el => el.value = '');
    }
}

// Dynamic Artwork Image Rows
let imageIndex = document.querySelectorAll('.image-row').length;
function toggleImageInputMode(radioBtn, index) {
    const mode = radioBtn.value;
    const groupFile = document.getElementById(`group-file-${index}`);
    const groupUrl = document.getElementById(`group-url-${index}`);
    const fileInput = groupFile.querySelector('input[type="file"]');
    const urlInput = groupUrl.querySelector('input[type="text"]');
    const imgPreview = document.getElementById(`img-preview-${index}`);

    if (mode === 'file') {
        // Enable File
        groupFile.style.opacity = '1';
        groupFile.style.pointerEvents = 'auto';
        fileInput.disabled = false;
        
        // Disable URL
        groupUrl.style.opacity = '0.5';
        groupUrl.style.pointerEvents = 'none';
        urlInput.disabled = true;
        urlInput.value = ''; // Clear URL input
    } else if (mode === 'url') {
        // Enable URL
        groupUrl.style.opacity = '1';
        groupUrl.style.pointerEvents = 'auto';
        urlInput.disabled = false;
        
        // Disable File
        groupFile.style.opacity = '0.5';
        groupFile.style.pointerEvents = 'none';
        fileInput.disabled = true;
        fileInput.value = ''; // Clear File input
    }
    
    // Clear preview image when switching modes
    imgPreview.src = '';
    imgPreview.style.display = 'none';
}

function addImageRow() {
    const container = document.getElementById('images_container');
    const div = document.createElement('div');
    div.className = 'image-row';
    div.dataset.index = imageIndex;
    div.style = 'border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; background-color: #fafafa; position: relative;';
    div.innerHTML = `
        <button type="button" class="btn btn-danger btn-sm" onclick="removeImageRow(this)" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
        
        <!-- Mode Toggle -->
        <div class="input-mode-toggle" style="margin-bottom: 1rem; padding: 0.75rem; background: #fff; border-radius: 8px; border: 1px solid #e0e0e0; display: inline-flex; gap: 1.5rem;">
            <span style="font-weight: bold; color: #555;">Image Source:</span>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #2196F3; font-weight: 600;">
                <input type="radio" name="images[${imageIndex}][mode]" value="file" checked onchange="toggleImageInputMode(this, ${imageIndex})" style="accent-color: #2196F3;"> Upload File
            </label>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #555; font-weight: 600;">
                <input type="radio" name="images[${imageIndex}][mode]" value="url" onchange="toggleImageInputMode(this, ${imageIndex})" style="accent-color: #555;"> Use URL
            </label>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1rem;">
            <!-- Upload Local File -->
            <div id="group-file-${imageIndex}" class="form-group dropzone-container" style="background: #fff; padding: 1.5rem; border-radius: 8px; border: 2px dashed #ccc; transition: all 0.3s; text-align: center; cursor: pointer; position: relative;"
                 ondragover="handleDragOver(event, this)"
                 ondragleave="handleDragLeave(event, this)"
                 ondrop="handleFileDrop(event, ${imageIndex}, this)"
                 onclick="document.getElementById('file-input-' + ${imageIndex}).click()">
                <div style="pointer-events: none;">
                    <svg style="width: 48px; height: 48px; color: #2196F3; margin-bottom: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <p style="margin: 0; font-weight: bold; color: #333;">Drag & Drop image here</p>
                    <p style="margin: 0; color: #666; font-size: 0.9rem;">or click to browse</p>
                    <p id="file-name-${imageIndex}" style="margin-top: 0.5rem; color: #2196F3; font-size: 0.85rem; font-weight: bold; word-break: break-all;"></p>
                    <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">Accepted: JPG, PNG, WEBP. Max size: 5MB.</small>
                </div>
                <input type="file" id="file-input-${imageIndex}" name="images[${imageIndex}][file]" class="form-control" accept=".jpg,.jpeg,.png,.webp" onchange="handleFileSelect(this, ${imageIndex})" style="display: none;">
            </div>

            <!-- OR Enter URL -->
            <div id="group-url-${imageIndex}" class="form-group" style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px dashed #ccc; transition: all 0.3s; opacity: 0.5; pointer-events: none;">
                <label class="form-label" style="font-weight: bold; color: #555;">OR Image URL</label>
                <input type="text" name="images[${imageIndex}][url]" class="form-control image-url-input" placeholder="https://example.com/image.jpg" value="" onkeyup="updateRowImagePreview(this, ${imageIndex})" onchange="updateRowImagePreview(this, ${imageIndex})" disabled>
                <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">Must be a direct image link.</small>
            </div>
        </div>
        
        <div style="display: flex; gap: 2rem; align-items: start;">
            <div style="flex: 1;">
                <img id="img-preview-${imageIndex}" class="img-preview" src="" style="max-height: 150px; border-radius: 4px; border: 1px solid #ddd; display: none;" onerror="this.style.display='none'">
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; padding-top: 0.5rem;">
                <input type="radio" name="primary_image_index" value="${imageIndex}" class="form-check-input primary-radio" style="margin-top: 0; width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: #2196F3;">
                <label class="form-check-label" style="font-weight: bold; cursor: pointer; margin-bottom: 0;">Set as Primary Image</label>
            </div>
        </div>
    `;
    container.appendChild(div);
    imageIndex++;
}

function removeImageRow(button) {
    const row = button.closest('.image-row');
    const container = document.getElementById('images_container');
    
    const wasChecked = row.querySelector('.primary-radio').checked;
    
    if (container.querySelectorAll('.image-row').length > 1) {
        row.remove();
        if (wasChecked) {
            const remainingRadios = container.querySelectorAll('.primary-radio');
            if (remainingRadios.length > 0) {
                remainingRadios[0].checked = true;
            }
        }
    } else {
        row.querySelectorAll('input').forEach(el => {
            if (el.type === 'text') el.value = '';
            if (el.type === 'file') el.value = '';
        });
        row.querySelector('.img-preview').style.display = 'none';
        row.querySelector('.img-preview').src = '';
    }
}

function previewRowImage(input, index) {
    const preview = document.getElementById('img-preview-' + index);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        const urlInput = document.querySelector('input[name="images[' + index + '][url]"]');
        if (urlInput && urlInput.value) {
            preview.src = urlInput.value;
            preview.style.display = 'block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
}

function handleDragOver(e, container) {
    e.preventDefault();
    container.style.backgroundColor = '#e3f2fd';
    container.style.borderColor = '#2196F3';
}

function handleDragLeave(e, container) {
    e.preventDefault();
    container.style.backgroundColor = '#fff';
    container.style.borderColor = '#ccc';
}

function handleFileDrop(e, index, container) {
    e.preventDefault();
    container.style.backgroundColor = '#fff';
    container.style.borderColor = '#ccc';
    
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const fileInput = document.getElementById('file-input-' + index);
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(fileInput, index);
    }
}

function handleFileSelect(input, index) {
    const fileNameElement = document.getElementById('file-name-' + index);
    if (input.files && input.files.length > 0) {
        fileNameElement.textContent = input.files[0].name;
    } else {
        fileNameElement.textContent = '';
    }
    previewRowImage(input, index);
}

function updateRowImagePreview(input, index) {
    const fileInput = document.querySelector('input[name="images[' + index + '][file]"]');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        return; // Prioritize local file preview
    }
    const preview = document.getElementById('img-preview-' + index);
    const url = input.value.trim();
    if (url) {
        preview.src = url;
        preview.style.display = 'block';
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }
}

// Dynamic Geographies Handlers
let geographyIndex = document.querySelectorAll('.geography-row').length;
function addGeographyRow() {
    const container = document.getElementById('geographies_container');
    const div = document.createElement('div');
    div.className = 'geography-row';
    div.dataset.index = geographyIndex;
    div.style = 'border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; background-color: #fafafa; position: relative; border-left: 4px solid #2196F3;';
    div.innerHTML = `
        <button type="button" class="btn btn-danger btn-sm" onclick="removeGeographyRow(this)" style="position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
        
        <!-- Group 1: Core & Global (2 columns) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
            <!-- Geography Type -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Geography Type</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'geography_type')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][geography_type_id]" class="form-control geo-select">
                        <option value="">-- Select Type --</option>
                        @foreach($geographyTypes as $gt)
                            <option value="{{ $gt->geography_type_id }}">{{ $gt->geography_type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][geography_type_new]" class="form-control geo-new-input" placeholder="New Type Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'geography_type', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- Country -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Country</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'country')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][country_id]" class="form-control geo-select">
                        <option value="">-- Select Country --</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->country_id }}">{{ $c->country_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][country_new]" class="form-control geo-new-input" placeholder="New Country Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'country', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Group 2: Political Hierarchy (3 columns) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
            <!-- State -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">State / Province</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'state')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][state_id]" class="form-control geo-select">
                        <option value="">-- Select State --</option>
                        @foreach($states as $st)
                            <option value="{{ $st->state_id }}">{{ $st->state_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][state_new]" class="form-control geo-new-input" placeholder="New State Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'state', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- County -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">County</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'county')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][county_id]" class="form-control geo-select">
                        <option value="">-- Select County --</option>
                        @foreach($counties as $co)
                            <option value="{{ $co->county_id }}">{{ $co->county_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][county_new]" class="form-control geo-new-input" placeholder="New County Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'county', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- City -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">City</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'city')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][city_id]" class="form-control geo-select">
                        <option value="">-- Select City --</option>
                        @foreach($cities as $ci)
                            <option value="{{ $ci->city_id }}">{{ $ci->city_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][city_new]" class="form-control geo-new-input" placeholder="New City Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'city', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Group 3: Archaeological Hierarchy (4 columns) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #eee;">
            <!-- Region -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Region</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'region')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][region_id]" class="form-control geo-select">
                        <option value="">-- Select Region --</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_id }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][region_new]" class="form-control geo-new-input" placeholder="New Region Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'region', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- Subregion -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Subregion</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'subregion')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][subregion_id]" class="form-control geo-select">
                        <option value="">-- Select Subregion --</option>
                        @foreach($subregions as $sub)
                            <option value="{{ $sub->subregion_id }}">{{ $sub->subregion_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][subregion_new]" class="form-control geo-new-input" placeholder="New Subregion Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'subregion', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- Locale -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Locale</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'locale')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][locale_id]" class="form-control geo-select">
                        <option value="">-- Select Locale --</option>
                        @foreach($locales as $loc)
                            <option value="{{ $loc->locale_id }}">{{ $loc->locale_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][locale_new]" class="form-control geo-new-input" placeholder="New Locale Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'locale', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- Locus -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Locus</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'locus')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][locus_id]" class="form-control geo-select">
                        <option value="">-- Select Locus --</option>
                        @foreach($loci as $lc)
                            <option value="{{ $lc->locus_id }}">{{ $lc->locus_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][locus_new]" class="form-control geo-new-input" placeholder="New Locus Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'locus', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Group 4: Specific Features (2 columns) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <!-- Excavation -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">Excavation</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'excavation')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][excavation_id]" class="form-control geo-select">
                        <option value="">-- Select Excavation --</option>
                        @foreach($excavations as $e)
                            <option value="{{ $e->excavation_id }}">{{ $e->excavation_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][excavation_new]" class="form-control geo-new-input" placeholder="New Excavation Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'excavation', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>

            <!-- River -->
            <div class="form-group" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <label class="form-label" style="margin: 0; font-size: 0.85rem; font-weight: 600;">River</label>
                    <button type="button" class="btn-toggle-geo" onclick="toggleGeoNew(this, 'river')" style="background: none; border: none; color: #2196F3; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">+ Add New</button>
                </div>
                <div class="select-grp">
                    <select name="geographies[\${geographyIndex}][river_id]" class="form-control geo-select">
                        <option value="">-- Select River --</option>
                        @foreach($rivers as $rv)
                            <option value="{{ $rv->river_id }}">{{ $rv->river_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="new-grp" style="display: none; margin-top: 0.25rem;">
                    <input type="text" name="geographies[\${geographyIndex}][river_new]" class="form-control geo-new-input" placeholder="New River Name" value="">
                    <button type="button" class="btn btn-secondary" onclick="toggleGeoNew(this, 'river', false)" style="margin-top: 0.25rem; padding: 0.15rem 0.35rem; font-size: 0.7rem; border-radius: 4px;">Cancel</button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(div);
    geographyIndex++;
}

function removeGeographyRow(button) {
    const row = button.closest('.geography-row');
    const container = document.getElementById('geographies_container');
    if (container.querySelectorAll('.geography-row').length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('select, input').forEach(el => el.value = '');
        row.querySelectorAll('.new-grp').forEach(el => el.style.display = 'none');
        row.querySelectorAll('.select-grp').forEach(el => el.style.display = 'block');
    }
}

function toggleGeoNew(el, field, show = true) {
    const formGroup = el.closest('.form-group');
    const selectGrp = formGroup.querySelector('.select-grp');
    const newGrp = formGroup.querySelector('.new-grp');
    const selectEl = formGroup.querySelector('.geo-select');
    const newInput = formGroup.querySelector('.geo-new-input');

    if (show) {
        selectGrp.style.display = 'none';
        newGrp.style.display = 'block';
        selectEl.value = '';
        newInput.focus();
    } else {
        selectGrp.style.display = 'block';
        newGrp.style.display = 'none';
        newInput.value = '';
    }
}

window.addEventListener('DOMContentLoaded', () => {
    ['classification', 'location', 'repository', 'object_type', 'department', 'credit_line'].forEach(field => {
        const input = document.getElementById('new_' + field);
        if (input && input.value.trim() !== '') {
            toggleAddNew(field, true);
        }
    });

    ['material', 'medium', 'tag', 'culture', 'period', 'dynasty', 'reign', 'portfolio'].forEach(field => {
        const input = document.getElementById('new_' + field);
        if (input && input.value.trim() !== '') {
            toggleAddNewM2M(field, true);
        }
    });

    ['constituent', 'constituent_role', 'constituent_prefix', 'constituent_suffix'].forEach(field => {
        const input = document.getElementById(field + '_input');
        if (input && input.value.trim() !== '') {
            toggleAddNewConstituent(field, true);
        }
    });

    // Toggle Geographies smart add panel if prefilled by old()
    document.querySelectorAll('.geography-row').forEach(row => {
        ['geography_type', 'country', 'state', 'county', 'city', 'region', 'subregion', 'locale', 'locus', 'excavation', 'river'].forEach(field => {
            const input = row.querySelector(`[name$="[${field}_new]"]`);
            if (input && input.value.trim() !== '') {
                const btn = input.closest('.form-group').querySelector('.btn-toggle-geo');
                if (btn) toggleGeoNew(btn, field, true);
            }
        });
    });
});
</script>
@endsection

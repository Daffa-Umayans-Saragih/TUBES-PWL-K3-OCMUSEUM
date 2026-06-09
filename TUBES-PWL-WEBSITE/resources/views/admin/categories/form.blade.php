@extends('admin.layout.layout')

@section('admin-title')
    {{ $title }}
@endsection

@section('admin-content')
<div class="orders-section" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #333;">{{ $title }}</h1>
        <p class="page-subtitle" style="font-size: 0.95rem; color: #666; margin: 0;">{{ $subtitle }}</p>
    </div>

    <!-- Form Container -->
    <div class="table-wrapper" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <!-- Category Name -->
            <div style="display: flex; flex-direction: column;">
                <label for="name" style="font-weight: 600; margin-bottom: 0.5rem; color: #333; font-size: 0.95rem;">
                    Category Name <span style="color: #d32f2f;">*</span>
                </label>
                <input type="text" id="name" name="name" 
                    placeholder="e.g. Magical, Inspired, Peaceful"
                    value="{{ old('name', $category?->name ?? '') }}" 
                    style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; outline: none; transition: border-color 0.2s;"
                    required>
                @error('name')
                    <span style="color: #d32f2f; font-size: 0.85rem; margin-top: 0.5rem;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Status Checkbox -->
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <input type="checkbox" id="active" name="active" value="1"
                    style="width: 1.15rem; height: 1.15rem; cursor: pointer; accent-color: #2196F3;"
                    {{ old('active', $category?->active ?? true) ? 'checked' : '' }}>
                <label for="active" style="font-weight: 600; color: #333; font-size: 0.95rem; margin: 0; cursor: pointer;">
                    Active (Visible on Guest Submission Form)
                </label>
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" style="background-color: #2196F3; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    {{ $isEdit ? 'Update Category' : 'Create Category' }}
                </button>
                <a href="{{ route('admin.categories.index') }}" style="background-color: #f5f5f5; color: #555; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-size: 0.95rem; font-weight: 600; border: 1px solid #ddd; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

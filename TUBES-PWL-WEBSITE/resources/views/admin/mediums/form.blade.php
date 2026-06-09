@extends('admin.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="admin-section">
        <div class="admin-breadcrumbs">
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb['isCurrent'] ?? false)
                    <span class="breadcrumb-current">{{ $breadcrumb['label'] }}</span>
                @else
                    <a href="{{ $breadcrumb['href'] }}" class="breadcrumb-link">{{ $breadcrumb['label'] }}</a>
                @endif
            @endforeach
        </div>

        <div class="admin-form-header">
            <h2 class="admin-title">{{ $title }}</h2>
            <p class="admin-subtitle">{{ $subtitle }}</p>
        </div>

        <div class="admin-form-container">
            <form action="{{ $isEdit ? route('admin.mediums.update', $medium) : route('admin.mediums.store') }}" method="POST" class="admin-form">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="form-group">
                    <label for="medium_name" class="form-label">Medium Name <span class="required">*</span></label>
                    <input type="text" id="medium_name" name="medium_name" class="form-control @error('medium_name') is-invalid @enderror"
                        placeholder="Enter medium name"
                        value="{{ old('medium_name', $medium?->medium_name ?? '') }}" required>
                    @error('medium_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
                        <a href="{{ route('admin.mediums.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    @include('admin.styles.shared-styles')
@endsection

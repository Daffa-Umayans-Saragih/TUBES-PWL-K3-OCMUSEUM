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
            <form action="{{ $isEdit ? route('admin.reigns.update', $reign) : route('admin.reigns.store') }}" method="POST" class="admin-form">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="form-group">
                    <label for="reign_name" class="form-label">Reign Name <span class="required">*</span></label>
                    <input type="text" id="reign_name" name="reign_name" class="form-control @error('reign_name') is-invalid @enderror"
                        placeholder="Enter reign name"
                        value="{{ old('reign_name', $reign?->reign_name ?? '') }}" required>
                    @error('reign_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
                        <a href="{{ route('admin.reigns.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    @include('admin.styles.shared-styles')
@endsection

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
            <form action="{{ $isEdit ? route('admin.tags.update', $tag) : route('admin.tags.store') }}" method="POST" class="admin-form">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="form-group">
                    <label for="tag_name" class="form-label">Tag Name <span class="required">*</span></label>
                    <input type="text" id="tag_name" name="tag_name" class="form-control @error('tag_name') is-invalid @enderror"
                        placeholder="Enter tag name"
                        value="{{ old('tag_name', $tag?->tag_name ?? '') }}" required>
                    @error('tag_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="aat_url" class="form-label">AAT URL</label>
                    <input type="url" id="aat_url" name="aat_url" class="form-control @error('aat_url') is-invalid @enderror"
                        placeholder="https://example.com"
                        value="{{ old('aat_url', $tag?->aat_url ?? '') }}">
                    @error('aat_url')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="wikidata_url" class="form-label">Wikidata URL</label>
                    <input type="url" id="wikidata_url" name="wikidata_url" class="form-control @error('wikidata_url') is-invalid @enderror"
                        placeholder="https://example.com"
                        value="{{ old('wikidata_url', $tag?->wikidata_url ?? '') }}">
                    @error('wikidata_url')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    @include('admin.styles.shared-styles')
@endsection

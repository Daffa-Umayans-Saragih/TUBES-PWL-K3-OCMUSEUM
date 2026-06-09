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

        <div class="admin-detail-header">
            <div>
                <h2 class="admin-title">{{ $title }}</h2>
                <p class="admin-subtitle">{{ $subtitle }}</p>
            </div>
            <div class="detail-actions">
                <a href="{{ route('admin.classifications.edit', $classification) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('admin.classifications.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="admin-detail-container">
            <div class="detail-card">
                <h3 class="detail-card-title">Classification Information</h3>
                <div class="detail-content">
                    <div class="detail-row">
                        <div class="detail-label">Classification Name</div>
                        <div class="detail-value">{{ $classification->classification_name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Total Artworks</div>
                        <div class="detail-value">{{ $classification->artWorks->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h3 class="detail-card-title">Associated Artworks</h3>
                <div class="detail-content">
                    @if ($classification->artWorks->count())
                        <table class="admin-table">
                            <thead>
                                <tr><th>Title</th><th>Accession</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($classification->artWorks as $artwork)
                                    <tr>
                                        <td>{{ $artwork->title }}</td>
                                        <td>{{ $artwork->accession_number }}</td>
                                        <td><a href="{{ route('admin.artworks.show', $artwork) }}" class="btn-sm btn-info">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No artworks found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    @include('admin.styles.shared-styles')
@endsection

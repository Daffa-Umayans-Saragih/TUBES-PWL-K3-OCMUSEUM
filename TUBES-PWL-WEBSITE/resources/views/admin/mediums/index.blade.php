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

        <div class="admin-header">
            <div>
                <h2 class="admin-title">{{ $title }}</h2>
                <p class="admin-subtitle">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('admin.mediums.create') }}" class="btn-primary">+ Create Medium</a>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success"><strong>Success!</strong> {{ $message }}</div>
        @endif
        @if ($message = Session::get('error'))
            <div class="alert alert-danger"><strong>Error!</strong> {{ $message }}</div>
        @endif

        <div class="admin-table-container">
            @if ($mediums->count())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Medium Name</th>
                            <th>Artworks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mediums as $medium)
                            <tr>
                                <td><strong>{{ $medium->medium_name }}</strong></td>
                                <td><span class="badge">{{ $medium->art_works_count }}</span></td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.mediums.show', $medium) }}" class="btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.mediums.edit', $medium) }}" class="btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.mediums.destroy', $medium) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="admin-pagination">{{ $mediums->links() }}</div>
            @else
                <div class="admin-empty-state">
                    <p>No mediums found</p>
                    <a href="{{ route('admin.mediums.create') }}" class="btn-primary">Create First</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('styles')
    @include('admin.styles.shared-styles')
@endsection

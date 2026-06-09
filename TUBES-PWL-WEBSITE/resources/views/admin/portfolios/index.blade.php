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
            <a href="{{ route('admin.portfolios.create') }}" class="btn-primary">+ Create Portfolio</a>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success"><strong>Success!</strong> {{ $message }}</div>
        @endif
        @if ($message = Session::get('error'))
            <div class="alert alert-danger"><strong>Error!</strong> {{ $message }}</div>
        @endif

        <div class="admin-table-container">
            @if ($portfolios->count())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Portfolio Name</th>
                            <th>Artworks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($portfolios as $portfolio)
                            <tr>
                                <td><strong>{{ $portfolio->portfolio_name }}</strong></td>
                                <td><span class="badge">{{ $portfolio->art_works_count }}</span></td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.portfolios.show', $portfolio) }}" class="btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.portfolios.edit', $portfolio) }}" class="btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.portfolios.destroy', $portfolio) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="admin-pagination">{{ $portfolios->links() }}</div>
            @else
                <div class="admin-empty-state">
                    <p>No portfolios found</p>
                    <a href="{{ route('admin.portfolios.create') }}" class="btn-primary">Create First</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('styles')
    @include('admin.styles.shared-styles')
@endsection

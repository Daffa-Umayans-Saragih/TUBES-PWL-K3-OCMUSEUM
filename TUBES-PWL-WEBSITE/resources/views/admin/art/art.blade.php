@extends('layouts.admin')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/admin/art/art.css')
@endpush

@section('title', 'Artworks - Admin - MET Museum')
@section('page_title', 'Artworks')

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Artworks</h1>
        <a href="{{ route('admin.art.create') }}" class="btn-primary">+ Add Artwork</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($artworks->count() > 0)
        <table class="artworks-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Year</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($artworks as $artwork)
                    <tr>
                        <td>
                            <a href="{{ route('admin.art.show', $artwork->art_work_id) }}" class="artwork-title">
                                {{ Str::limit($artwork->title, 50) }}
                            </a>
                        </td>
                        <td>{{ $artwork->department?->name ?? 'N/A' }}</td>
                        <td>{{ $artwork->objectType?->name ?? 'N/A' }}</td>
                        <td>
                            @if($artwork->object_date_display)
                                {{ $artwork->object_date_display }}
                            @elseif($artwork->object_begin_date && $artwork->object_end_date)
                                {{ $artwork->object_begin_date }} – {{ $artwork->object_end_date }}
                            @elseif($artwork->object_begin_date)
                                {{ $artwork->object_begin_date }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $artwork->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.art.edit', $artwork->art_work_id) }}" class="btn-small btn-edit">Edit</a>
                                <form action="{{ route('admin.art.destroy', $artwork->art_work_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-small btn-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($artworks->hasPages())
            <div class="pagination-container">
                @if($artworks->onFirstPage())
                    <span class="pagination-link disabled">← Previous</span>
                @else
                    <a href="{{ $artworks->previousPageUrl() }}" class="pagination-link">← Previous</a>
                @endif

                @for($i = 1; $i <= $artworks->lastPage(); $i++)
                    @if($i === $artworks->currentPage())
                        <span class="pagination-link active">{{ $i }}</span>
                    @else
                        <a href="{{ $artworks->url($i) }}" class="pagination-link">{{ $i }}</a>
                    @endif
                @endfor

                @if($artworks->hasMorePages())
                    <a href="{{ $artworks->nextPageUrl() }}" class="pagination-link">Next →</a>
                @else
                    <span class="pagination-link disabled">Next →</span>
                @endif
            </div>
        @endif
    @else
        <div style="text-align: center; padding: 3rem; color: #999;">
            <p>No artworks found</p>
            <a href="{{ route('admin.art.create') }}" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Create First Artwork</a>
        </div>
    @endif
</div>
@endsection

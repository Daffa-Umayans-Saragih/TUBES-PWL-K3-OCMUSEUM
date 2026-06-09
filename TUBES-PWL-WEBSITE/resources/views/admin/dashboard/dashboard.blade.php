@extends('layouts.admin')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/admin/dashboard/dashboard.css')
@endpush

@section('title', 'Admin Dashboard - MET Museum')
@section('page_title', 'Dashboard')

@section('content')
<div class="admin-container">
    <!-- Statistics -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $totalArtworks }}</div>
            <div class="stat-label">Total Artworks</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $totalUsers }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $totalOrders }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="admin-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="{{ route('admin.art.index') }}" class="btn-primary">View All Artworks</a>
            <a href="{{ route('admin.art.create') }}" class="btn-primary">Add New Artwork</a>
        </div>
    </div>

    <!-- Recent Artworks -->
    @if($recentArtworks->count() > 0)
        <div class="recent-artworks">
            <h2>Recently Added Artworks</h2>
            <table class="artworks-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentArtworks as $artwork)
                        <tr>
                            <td>
                                <a href="{{ route('admin.art.show', $artwork->art_work_id) }}" class="artwork-link">
                                    {{ $artwork->title }}
                                </a>
                            </td>
                            <td>{{ $artwork->department?->name ?? 'N/A' }}</td>
                            <td>{{ $artwork->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.art.edit', $artwork->art_work_id) }}" style="color: #666; text-decoration: none; font-size: 0.85rem;">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

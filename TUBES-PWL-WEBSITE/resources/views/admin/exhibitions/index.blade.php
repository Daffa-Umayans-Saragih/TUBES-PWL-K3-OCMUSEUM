@extends('admin.layout.layout')

@section('admin-title')
    Exhibition Management
@endsection

@section('admin-content')
<div class="admin-page-section">
    <div class="page-header">
        <h1>Exhibition Management</h1>
        <p class="page-subtitle">Plan and track exhibitions</p>
    </div>

    <div class="quick-stats-grid">
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Exhibitions',
            'value' => $totalExhibitions ?? 0,
            'icon' => '🏛️',
            'trend' => 'all time',
            'color' => 'primary'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Current',
            'value' => $currentExhibitions ?? 0,
            'icon' => '✓',
            'trend' => 'active',
            'color' => 'success'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Upcoming',
            'value' => $upcomingExhibitions ?? 0,
            'icon' => '📅',
            'trend' => 'scheduled',
            'color' => 'info'
        ])
    </div>

    <section class="table-section">
        <h2 class="section-title">Exhibitions</h2>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Artworks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exhibitions ?? [] as $exhibition)
                        <tr>
                            <td><strong>{{ $exhibition['title'] ?? 'Untitled' }}</strong></td>
                            <td>{{ $exhibition['start_date'] ?? 'N/A' }}</td>
                            <td>{{ $exhibition['end_date'] ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $exhibition['status'] ?? 'draft' }}">
                                    {{ ucfirst($exhibition['status'] ?? 'draft') }}
                                </span>
                            </td>
                            <td>{{ $exhibition['artwork_count'] ?? 0 }}</td>
                            <td class="actions">
                                <button class="action-btn">Edit</button>
                                <button class="action-btn">View</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<style>
.admin-page-section { max-width: 1200px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; }
.page-subtitle { font-size: 0.95rem; color: #666; margin: 0; }
.section-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 1.5rem; }
.quick-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.table-section { background: white; border-radius: 8px; padding: 1.5rem; }
.table-wrapper { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; }
.data-table td { padding: 1rem; border-bottom: 1px solid #e0e0e0; }
.data-table tbody tr:hover { background-color: #f9f9f9; }
.status-badge { padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600; }
.status-active { background-color: #d4edda; color: #155724; }
.status-draft { background-color: #e2e3e5; color: #383d41; }
.status-published { background-color: #d1ecf1; color: #0c5460; }
.actions { display: flex; gap: 0.5rem; }
.action-btn { padding: 0.4rem 0.8rem; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.8rem; transition: all 0.3s ease; }
.action-btn:hover { border-color: #2196F3; color: #2196F3; }
</style>
@endsection

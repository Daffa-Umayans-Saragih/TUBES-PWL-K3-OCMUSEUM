
@extends('admin.layout.layout')

@section('admin-title')
    Analytics
@endsection

@section('admin-content')
<div class="admin-page-section">
    <div class="page-header">
        <h1>Analytics Dashboard</h1>
        <p class="page-subtitle">Track revenue, visitors, and capacity trends</p>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats-grid">
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Revenue',
            'value' => '$' . number_format($totalRevenue ?? 0, 2),
            'icon' => '💰',
            'trend' => 'all time',
            'color' => 'success'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Visitors',
            'value' => $totalVisitors ?? 0,
            'icon' => '👥',
            'trend' => 'tracked',
            'color' => 'info'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Avg Ticket Price',
            'value' => '$' . number_format($avgTicketPrice ?? 0, 2),
            'icon' => '🎫',
            'trend' => 'average',
            'color' => 'primary'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Conversion Rate',
            'value' => $conversionRate ?? '0' . '%',
            'icon' => '📈',
            'trend' => 'trend',
            'color' => 'warning'
        ])
    </div>

    <!-- Analytics Sections -->
    <section class="analytics-card">
        <h2 class="section-title">Revenue Analytics</h2>
        <p class="section-description">Track revenue trends and payment flows</p>
        <!-- Placeholder for charts -->
        <div style="height: 300px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-weight: 500;">Charts will be displayed here</div>
    </section>

    <section class="analytics-card">
        <h2 class="section-title">Visitor Analytics</h2>
        <p class="section-description">Track traffic and ticket sales patterns</p>
        <div style="height: 300px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-weight: 500;">Charts will be displayed here</div>
    </section>

    <section class="analytics-card">
        <h2 class="section-title">Key Metrics</h2>
        <p class="section-description">Important performance indicators</p>
        <div class="metrics-grid">
            <div class="metric-item">
                <span class="metric-label">Tickets Sold</span>
                <span class="metric-value">{{ $ticketsSold ?? 0 }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Total Orders</span>
                <span class="metric-value">{{ $totalOrders ?? 0 }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Avg Order Value</span>
                <span class="metric-value">${{ number_format($avgOrderValue ?? 0, 2) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Refund Rate</span>
                <span class="metric-value">{{ $refundRate ?? '0' }}%</span>
            </div>
        </div>
    </section>
</div>

<style>
.admin-page-section { max-width: 1200px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; }
.page-subtitle { font-size: 0.95rem; color: #666; margin: 0; }
.section-title { font-size: 1.1rem; font-weight: 600; margin: 0 0 0.5rem 0; }
.section-description { font-size: 0.85rem; color: #666; margin: 0 0 1rem 0; }
.quick-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem; }

.analytics-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.metric-item {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    background-color: #f5f5f5;
    border-radius: 6px;
    text-align: center;
}

.metric-label {
    font-size: 0.85rem;
    color: #666;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2196F3;
}
</style>
@endsection

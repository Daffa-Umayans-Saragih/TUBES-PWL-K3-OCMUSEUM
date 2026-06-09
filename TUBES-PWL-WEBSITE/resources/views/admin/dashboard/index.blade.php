@extends('admin.layout.layout')

@section('admin-title')
    Admin Dashboard
@endsection

@section('admin-content')
<div class="admin-dashboard">
    <!-- Top Section: Key Statistics -->
    <section class="analytics-section overview-section">
        <h2 class="section-title">Quick Overview</h2>
                <div class="qo-grid">
            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Today's Revenue</div>
                    <div class="qo-value">${{ number_format($totalRevenueToday ?? 0, 2) }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">today</span>
                        <span class="qo-trend qo-trend-up">↑ 12.5%</span>
                    </div>
                </div>
            </div>

            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Total Revenue</div>
                    <div class="qo-value">${{ number_format($totalRevenue ?? 0, 2) }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">all time</span>
                        <span class="qo-trend qo-trend-up">↑ 8.3%</span>
                    </div>
                </div>
            </div>

            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-amber">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 11v2"/><path d="M13 17v2"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Tickets Sold (Today)</div>
                    <div class="qo-value">{{ $ticketsSoldToday ?? 0 }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">tickets</span>
                        <span class="qo-trend qo-trend-up">↑ 5.2%</span>
                    </div>
                </div>
            </div>

            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Today's Orders</div>
                    <div class="qo-value">{{ $todayOrders ?? 0 }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">today</span>
                        <span class="qo-trend qo-trend-up">↑ 15.8%</span>
                    </div>
                </div>
            </div>

            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Total Orders</div>
                    <div class="qo-value">{{ $totalOrders ?? 0 }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">all time</span>
                        <span class="qo-trend qo-trend-up">↑ 3.1%</span>
                    </div>
                </div>
            </div>

            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Pending Orders</div>
                    <div class="qo-value">{{ $pendingOrders ?? 0 }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">pending payment</span>
                        <span class="qo-trend qo-trend-down">↓ 3.2%</span>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole(['admin', 'superadmin']))
            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-violet">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Total Users</div>
                    <div class="qo-value">{{ $totalUsers ?? 0 }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">users</span>
                        <span class="qo-trend qo-trend-up">↑ 7.6%</span>
                    </div>
                </div>
            </div>

            <div class="qo-card">
                <div class="qo-card-header">
                    <div class="qo-icon-box qo-icon-pink">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="5.5" r="2.5"/><circle cx="6.5" cy="9.5" r="2.5"/><circle cx="8.5" cy="16.5" r="2.5"/><circle cx="15.5" cy="15.5" r="2.5"/><path d="M20.5 12A8.5 8.5 0 0 1 12 20.5C7.3 20.5 3.5 16.7 3.5 12S7.3 3.5 12 3.5c2.4 0 4.6 1 6.1 2.6l-3.6 4.4"/></svg>
                    </div>
                </div>
                <div class="qo-card-body">
                    <div class="qo-label">Total Artworks</div>
                    <div class="qo-value">{{ $totalArtworks ?? 0 }}</div>
                    <div class="qo-footer">
                        <span class="qo-subtext">artworks</span>
                        <span class="qo-trend qo-trend-up">↑ 9.4%</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- Dashboard Navigation Grid -->
    <section class="dashboard-grid">
        <h2 class="section-title">Quick Access</h2>
        <div class="dashboard-grid-container">
            <!-- Tickets Management Card -->
            <a href="{{ route('admin.tickets.index') }}" class="dashboard-card">
                <div class="dashboard-card__icon">🎫</div>
                <div class="dashboard-card__content">
                    <h3 class="dashboard-card__title">Tickets</h3>
                    <p class="dashboard-card__description">Manage ticket sales & stock</p>
                </div>
                <div class="dashboard-card__arrow">→</div>
            </a>

            <!-- Orders Management Card -->
            <a href="{{ route('admin.orders.index') }}" class="dashboard-card">
                <div class="dashboard-card__icon">📦</div>
                <div class="dashboard-card__content">
                    <h3 class="dashboard-card__title">Orders</h3>
                    <p class="dashboard-card__description">View & scan orders</p>
                </div>
                <div class="dashboard-card__arrow">→</div>
            </a>

            <!-- Payments Management Card -->
            <a href="{{ route('admin.payments.index') }}" class="dashboard-card">
                <div class="dashboard-card__icon">💳</div>
                <div class="dashboard-card__content">
                    <h3 class="dashboard-card__title">Payments</h3>
                    <p class="dashboard-card__description">Process & refund payments</p>
                </div>
                <div class="dashboard-card__arrow">→</div>
            </a>

            @if(auth()->user()->hasRole(['admin', 'superadmin']))
            <!-- Analytics Card -->
            <a href="{{ route('admin.ticket-analytics.index') }}" class="dashboard-card">
                <div class="dashboard-card__icon">📈</div>
                <div class="dashboard-card__content">
                    <h3 class="dashboard-card__title">Analytics</h3>
                    <p class="dashboard-card__description">View detailed reports</p>
                </div>
                <div class="dashboard-card__arrow">→</div>
            </a>

            <!-- Artworks Management Card -->
            <a href="{{ route('admin.artworks.index') }}" class="dashboard-card">
                <div class="dashboard-card__icon">🎨</div>
                <div class="dashboard-card__content">
                    <h3 class="dashboard-card__title">Artworks</h3>
                    <p class="dashboard-card__description">Manage collection items</p>
                </div>
                <div class="dashboard-card__arrow">→</div>
            </a>
            <!-- Users Management Card -->
            <a href="{{ route('admin.users.index') }}" class="dashboard-card">
                <div class="dashboard-card__icon">👥</div>
                <div class="dashboard-card__content">
                    <h3 class="dashboard-card__title">Users</h3>
                    <p class="dashboard-card__description">Manage system users</p>
                </div>
                <div class="dashboard-card__arrow">→</div>
            </a>
            @endif


        </div>
    </section>
</div>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.analytics-section {
    margin-bottom: 3rem;
}

.analytics-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.dashboard-grid {
    margin-top: 3rem;
}

.dashboard-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.dashboard-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.dashboard-card:hover {
    border-color: #2196F3;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.15);
    transform: translateY(-2px);
}

.dashboard-card__icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.dashboard-card__content {
    flex: 1;
    min-width: 0;
}

.dashboard-card__title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: #333;
}

.dashboard-card__description {
    font-size: 0.875rem;
    color: #666;
    margin: 0;
}

.dashboard-card__arrow {
    font-size: 1.5rem;
    color: #2196F3;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.dashboard-card:hover .dashboard-card__arrow {
    transform: translateX(4px);
}

@media (max-width: 768px) {
    .analytics-cards-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
    }
    
    .dashboard-grid-container {
        grid-template-columns: 1fr;
    }
    
    .dashboard-card {
        padding: 1rem;
        gap: 1rem;
    }
    
    .dashboard-card__icon {
        font-size: 2rem;
    }
}
/* Scoped Quick Overview CSS */
.qo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.qo-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.qo-card:hover {
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.08), 0 4px 12px -3px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
    border-color: rgba(203, 213, 225, 0.9);
}

.qo-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Specific pastel variants */
.qo-icon-green { background: #ecfdf5; color: #10b981; }
.qo-icon-blue { background: #eff6ff; color: #3b82f6; }
.qo-icon-amber { background: #fffbeb; color: #f59e0b; }
.qo-icon-violet { background: #f5f3ff; color: #8b5cf6; }
.qo-icon-pink { background: #fdf2f8; color: #ec4899; }
.qo-icon-orange { background: #fff7ed; color: #f97316; }

.qo-card-body {
    display: flex;
    flex-direction: column;
}

.qo-label {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.375rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.qo-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
    line-height: 1.1;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.qo-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.75rem;
}

.qo-subtext {
    font-size: 0.75rem;
    font-weight: 500;
    color: #94a3b8;
    text-transform: lowercase;
}

.qo-trend {
    font-size: 0.75rem;
    font-weight: 600;
}

.qo-trend-up { color: #10b981; }
.qo-trend-down { color: #ef4444; }

@media (max-width: 768px) {
    .qo-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }
}
</style>
@endsection

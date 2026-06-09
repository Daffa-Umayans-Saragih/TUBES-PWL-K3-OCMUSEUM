@extends('admin.layout.layout')

@section('admin-title')
    Ticket Sales Analytics Dashboard
@endsection

@section('admin-content')
<div class="ticket-analytics-dashboard">
    
    <!-- Top Toolbar with Filters -->
    @include('admin.ticket-analytics.components.filter-bar')
    
    <!-- Overview Analytics Cards -->
    <section class="analytics-section overview-section">
        <h2 class="section-title">Overview Analytics</h2>
        <div class="analytics-cards-grid">
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Total Revenue Today',
                'value' => '₹' . number_format($totalRevenueToday, 0),
                'icon' => '💰',
                'trend' => '+12.5%',
                'color' => 'primary'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Total Revenue This Month',
                'value' => '₹' . number_format($totalRevenueMonth, 0),
                'icon' => '📈',
                'trend' => '+8.3%',
                'color' => 'success'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Tickets Sold Today',
                'value' => $ticketsSoldToday,
                'icon' => '🎫',
                'trend' => '+5.2%',
                'color' => 'info'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Total Visitors',
                'value' => $totalVisitors,
                'icon' => '👥',
                'trend' => '+15.8%',
                'color' => 'warning'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Pending Payments',
                'value' => $pendingPayments,
                'icon' => '⏳',
                'trend' => '-3.2%',
                'color' => 'danger'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Conversion Rate',
                'value' => $conversionRate . '%',
                'icon' => '🎯',
                'trend' => '+2.1%',
                'color' => 'success'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Active Sessions',
                'value' => $activeVisitSessions,
                'icon' => '🏛️',
                'trend' => 'Today',
                'color' => 'info'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Sold Out Sessions',
                'value' => $soldOutSessions,
                'icon' => '🚫',
                'trend' => 'Alert',
                'color' => 'danger'
            ])
        </div>
    </section>
    
    <!-- Revenue Analytics -->
    <section class="analytics-section revenue-section">
        <h2 class="section-title">Revenue Analytics</h2>
        <div class="revenue-charts-container">
            <!-- Revenue Trend Chart -->
            <div class="chart-card large">
                <div class="chart-header">
                    <h3>Revenue Trend (Last 30 Days)</h3>
                    <span class="chart-legend">₹ Amount</span>
                </div>
                <div class="chart-body">
                    <canvas id="revenueTrendChart" data-revenue="{{ json_encode($revenueTrend) }}"></canvas>
                </div>
            </div>
            
            <!-- Monthly Revenue Chart -->
            <div class="chart-card large">
                <div class="chart-header">
                    <h3>Monthly Revenue (Last 12 Months)</h3>
                    <span class="chart-legend">₹ Amount</span>
                </div>
                <div class="chart-body">
                    <canvas id="monthlyRevenueChart" data-revenue="{{ json_encode($monthlyRevenue) }}"></canvas>
                </div>
            </div>
            
            <!-- Payment Status Breakdown -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Payment Status</h3>
                </div>
                <div class="chart-body">
                    <canvas id="paymentStatusChart" data-payment-status="{{ json_encode($paymentStatusBreakdown) }}"></canvas>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Ticket Sales Analytics -->
    <section class="analytics-section ticket-section">
        <h2 class="section-title">Ticket Sales Analytics</h2>
        <div class="ticket-charts-container">
            <!-- Ticket Sales Trend -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Sales Trend (Last 7 Days)</h3>
                </div>
                <div class="chart-body">
                    <canvas id="ticketSalesTrendChart" data-sales="{{ json_encode($ticketSalesTrend) }}"></canvas>
                </div>
            </div>
            
            <!-- Best Selling Tickets Table -->
            <div class="chart-card large">
                <div class="chart-header">
                    <h3>Top Selling Tickets</h3>
                </div>
                <div class="table-body">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Ticket Type</th>
                                <th>Sold</th>
                                <th>Revenue</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bestSellingTickets as $ticket)
                                @php
                                    $totalSold = $bestSellingTickets->sum('total_sold');
                                    $share = ($ticket['total_sold'] / $totalSold) * 100;
                                @endphp
                                <tr>
                                    <td class="ticket-type-cell">
                                        <span class="ticket-type-badge">{{ $ticket['type_name'] }}</span>
                                    </td>
                                    <td><strong>{{ $ticket['total_sold'] }}</strong></td>
                                    <td>₹{{ number_format($ticket['revenue'], 0) }}</td>
                                    <td>
                                        <div class="progress-bar-small">
                                            <div class="progress-fill" style="width: {{ $share }}%"></div>
                                        </div>
                                        <span class="progress-text">{{ round($share, 1) }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-cell">No ticket sales data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Ticket Type Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Distribution</h3>
                </div>
                <div class="chart-body">
                    <canvas id="ticketDistributionChart" data-distribution="{{ json_encode($ticketTypeDistribution) }}"></canvas>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Capacity & Visitor Analytics -->
    <section class="analytics-section capacity-section">
        <h2 class="section-title">Capacity & Visitor Analytics</h2>
        
        <!-- Capacity Overview Table -->
        <div class="table-card">
            <div class="table-header">
                <h3>Schedule Capacity Overview</h3>
            </div>
            <div class="table-responsive">
                <table class="analytics-table capacity-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Sold</th>
                            <th>Remaining</th>
                            <th>Occupancy Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($capacityOverview as $schedule)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($schedule['date'])->format('M d, Y') }}</td>
                                <td>{{ $schedule['location'] }}</td>
                                <td><strong>{{ $schedule['capacity'] }}</strong></td>
                                <td>{{ $schedule['sold'] }}</td>
                                <td>
                                    <span class="remaining-badge @if($schedule['remaining'] <= 0) sold-out @endif">
                                        {{ $schedule['remaining'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="occupancy-bar">
                                        <div class="occupancy-fill" style="width: {{ $schedule['occupancy_rate'] }}%"></div>
                                    </div>
                                    <span class="occupancy-text">{{ $schedule['occupancy_rate'] }}%</span>
                                </td>
                                <td>
                                    @if($schedule['is_sold_out'])
                                        <span class="badge-danger">🔴 Sold Out</span>
                                    @else
                                        <span class="badge-success">🟢 Available</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-cell">No schedule data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Visitor Analytics -->
        <div class="visitor-analytics-container">
            <div class="visitor-card">
                <div class="visitor-icon">👥</div>
                <div class="visitor-content">
                    <div class="visitor-label">Repeat Visitors</div>
                    <div class="visitor-value">{{ $repeatVisitors }}</div>
                </div>
            </div>
            
            <div class="visitor-card">
                <div class="visitor-icon">🔐</div>
                <div class="visitor-content">
                    <div class="visitor-label">Registered Users</div>
                    <div class="visitor-value">{{ $visitorTypes->get('registered', 0) }}</div>
                </div>
            </div>
            
            <div class="visitor-card">
                <div class="visitor-icon">👤</div>
                <div class="visitor-content">
                    <div class="visitor-label">Guest Visitors</div>
                    <div class="visitor-value">{{ $visitorTypes->get('guest', 0) }}</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- QR Ticket Validation -->
    <section class="analytics-section validation-section">
        <h2 class="section-title">QR Ticket Validation Analytics</h2>
        <div class="validation-container">
            <!-- Validation Success Rate -->
            <div class="validation-card large">
                <div class="validation-header">
                    <h3>Validation Success Rate</h3>
                </div>
                <div class="validation-body">
                    <div class="validation-circle">
                        <svg viewBox="0 0 100 100" class="circle-chart">
                            <circle cx="50" cy="50" r="45" class="circle-background"></circle>
                            <circle cx="50" cy="50" r="45" class="circle-progress" 
                                    style="stroke-dashoffset: {{ 282.7 * (1 - $validationSuccessRate/100) }}"></circle>
                        </svg>
                        <div class="circle-content">
                            <div class="circle-value">{{ $validationSuccessRate }}%</div>
                            <div class="circle-label">Success Rate</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ticket Status Breakdown -->
            <div class="validation-card">
                <div class="validation-header">
                    <h3>Ticket Status</h3>
                </div>
                <div class="validation-body">
                    <canvas id="ticketStatusChart" data-status="{{ json_encode($ticketStatusBreakdown) }}"></canvas>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Latest Transactions -->
    <section class="analytics-section transactions-section">
        <h2 class="section-title">Latest Transactions</h2>
        
        <!-- Latest Orders -->
        <div class="transactions-container">
            <div class="transaction-card">
                <div class="transaction-header">
                    <h3>Latest Orders</h3>
                </div>
                <div class="table-responsive">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>User Role</th>
                                <th>Tickets</th>
                                <th>Scan Progress</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestTransactions as $transaction)
                                <tr>
                                    <td><strong>#{{ $transaction['order_id'] }}</strong></td>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-name">{{ $transaction['user_name'] }}</div>
                                            <div class="customer-email">{{ $transaction['user_email'] }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: {{ $transaction['user_role'] === 'Admin' ? '#ffebee' : ($transaction['user_role'] === 'User' ? '#e3f2fd' : '#f3e5f5') }}; color: {{ $transaction['user_role'] === 'Admin' ? '#c62828' : ($transaction['user_role'] === 'User' ? '#0d47a1' : '#4a148c') }}; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">
                                            {{ $transaction['user_role'] }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction['ticket_count'] }}</td>
                                    <td>
                                        @php
                                            $used  = $transaction['used_count'] ?? 0;
                                            $total = $transaction['ticket_count'] ?? 0;
                                            $pct   = $total > 0 ? round(($used/$total)*100) : 0;
                                            $pillClass = $used >= $total && $total > 0 ? 'progress-pill progress-full'
                                                       : ($used > 0 ? 'progress-pill progress-partial' : 'progress-pill progress-none');
                                        @endphp
                                        <span class="{{ $pillClass }}">
                                            {{ $used }} / {{ $total }}
                                        </span>
                                    </td>
                                    <td><strong>₹{{ number_format($transaction['total_amount'], 0) }}</strong></td>
                                    <td>
                                        <span class="badge-{{ strtolower($transaction['payment_status']) }}">
                                            {{ ucfirst($transaction['payment_status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-{{ strtolower($transaction['order_status']) }}">
                                            {{ ucfirst($transaction['order_status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction['created_at']->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="empty-cell">No transactions available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Latest Payments -->
            <div class="transaction-card">
                <div class="transaction-header">
                    <h3>Latest Payments</h3>
                </div>
                <div class="table-responsive">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>User Role</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestPayments as $payment)
                                <tr>
                                    <td><strong>#{{ $payment['id'] }}</strong></td>
                                    <td>#{{ $payment['order_id'] }}</td>
                                    <td>{{ $payment['user_name'] }}</td>
                                    <td>
                                        <span class="badge" style="background: {{ $payment['user_role'] === 'Admin' ? '#ffebee' : ($payment['user_role'] === 'User' ? '#e3f2fd' : '#f3e5f5') }}; color: {{ $payment['user_role'] === 'Admin' ? '#c62828' : ($payment['user_role'] === 'User' ? '#0d47a1' : '#4a148c') }}; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">
                                            {{ $payment['user_role'] }}
                                        </span>
                                    </td>
                                    <td><strong>₹{{ number_format($payment['amount'], 0) }}</strong></td>
                                    <td>{{ $payment['method'] }}</td>
                                    <td>
                                        <span class="badge-{{ strtolower($payment['status']) }}">
                                            {{ ucfirst($payment['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment['created_at']->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-cell">No payments available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/admin/ticket-analytics/index.js')
@endpush

@push('styles')
@vite('resources/css/admin/ticket-analytics/index.css')
@endpush

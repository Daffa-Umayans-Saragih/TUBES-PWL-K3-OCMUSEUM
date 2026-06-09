@extends('layouts.admin')

@push('styles')
@vite('resources/css/admin/dashboard/modern.css')
@endpush

@section('title', 'Transactions - MET Museum Admin')
@section('page_title', 'Transactions Management')

@section('content')
<div class="museum-dashboard">
    <!-- TRANSACTIONS SECTION -->
    
    <!-- STATISTICS CARDS -->
    <div class="stats-grid compact">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Total Transactions</span>
                <h3 class="stat-value">{{ $stats['total_transactions'] }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Total Revenue</span>
                <h3 class="stat-value">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Tickets Sold</span>
                <h3 class="stat-value">{{ $stats['total_tickets_sold'] }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Completed</span>
                <h3 class="stat-value">{{ $stats['completed_count'] }}</h3>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
            <div class="stat-info">
                <span class="stat-label">Pending</span>
                <h3 class="stat-value">{{ $stats['pending_count'] }}</h3>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="charts-grid">
        <!-- Weekly Sales Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h4>Weekly Sales Trend</h4>
            </div>
            <div class="chart-body">
                <canvas id="weeklySalesChart"></canvas>
            </div>
        </div>

        <!-- Monthly Sales Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h4>Monthly Sales (Last 12 Months)</h4>
            </div>
            <div class="chart-body">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- FILTERS & SEARCH -->
    <div class="transactions-controls">
        <div class="control-group search">
            <input type="text" id="searchInput" placeholder="Search by Order ID or Customer name..." 
                   value="{{ $search }}" class="search-input">
            <i class="bi bi-search"></i>
        </div>

        <div class="control-group filter">
            <select id="statusFilter" class="filter-select">
                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ $filter === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ $filter === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $filter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="control-group date-range">
            <input type="date" id="dateFrom" class="date-input" value="{{ $dateFrom }}">
            <span>to</span>
            <input type="date" id="dateTo" class="date-input" value="{{ $dateTo }}">
        </div>

        <div class="control-group actions">
            <button class="btn-action" id="filterBtn" onclick="applyFilters()">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <button class="btn-action secondary" onclick="resetFilters()">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
            <button class="btn-action secondary" onclick="exportTransactions()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>

    <!-- TRANSACTIONS TABLE -->
    <div class="table-card">
        <div class="table-header">
            <h4>Transaction History</h4>
            <span class="record-count">{{ $transactions->total() }} records</span>
        </div>

        <div class="table-responsive">
            <table class="data-table transactions-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Ticket Type</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr class="trans-row" data-order-id="{{ $trans->order_id }}">
                            <td class="cell-id">
                                <strong>#{{ $trans->order_code }}</strong>
                            </td>
                            <td>{{ $trans->order_date->format('M d, Y') }}</td>
                            <td>
                                <div class="customer-cell">
                                    <strong>{{ $trans->user->name ?? 'Guest' }}</strong>
                                    @if($trans->user)
                                        <small>{{ $trans->user->email }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $types = $trans->orderDetails->map(function($od) {
                                        return $od->ticket->ticketType->name ?? 'Unknown';
                                    })->unique()->implode(', ');
                                @endphp
                                {{ $types }}
                            </td>
                            <td class="cell-center">{{ $trans->orderDetails->sum('quantity') }}</td>
                            <td class="cell-amount">
                                <strong>Rp {{ number_format($trans->total_amount, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($trans->payment)
                                    <span class="badge badge-info">{{ ucfirst($trans->payment->payment_method ?? 'N/A') }}</span>
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-{{ strtolower($trans->status) }}">
                                    {{ ucfirst($trans->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="View Details" onclick="viewTransactionDetails({{ $trans->order_id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Print" onclick="printTransaction({{ $trans->order_id }})">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-cell">
                                <i class="bi bi-inbox"></i>
                                <p>No transactions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($transactions->hasPages())
            <div class="pagination-container">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: TRANSACTION DETAILS -->
    <div class="modal modal-lg" id="transactionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Transaction Details</h3>
                <button class="modal-close" onclick="closeModal('transactionModal')">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Weekly Sales Chart
    const weeklySalesData = @json($weeklySales);
    const weeklySalesCtx = document.getElementById('weeklySalesChart');
    
    if (weeklySalesCtx) {
        new Chart(weeklySalesCtx, {
            type: 'bar',
            data: {
                labels: weeklySalesData.map(d => d.label),
                datasets: [{
                    label: 'Revenue',
                    data: weeklySalesData.map(d => d.value),
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.8)',
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(155, 89, 182, 0.8)',
                        'rgba(230, 126, 34, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(52, 152, 219, 0.8)',
                        'rgba(46, 204, 113, 0.8)',
                    ],
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Sales Chart
    const monthlySalesData = @json($monthlySales);
    const monthlySalesCtx = document.getElementById('monthlySalesChart');
    
    if (monthlySalesCtx) {
        new Chart(monthlySalesCtx, {
            type: 'line',
            data: {
                labels: monthlySalesData.map(d => d.label),
                datasets: [{
                    label: 'Monthly Revenue',
                    data: monthlySalesData.map(d => d.value),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }
}

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const filter = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (filter !== 'all') params.append('filter', filter);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    window.location.href = `{{ route('admin.dashboard.transactions') }}?${params.toString()}`;
}

function resetFilters() {
    window.location.href = '{{ route('admin.dashboard.transactions') }}';
}

function exportTransactions() {
    const filter = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    const params = new URLSearchParams();
    if (filter !== 'all') params.append('filter', filter);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    window.location.href = `{{ route('admin.dashboard.export-transactions') }}?${params.toString()}`;
}

function viewTransactionDetails(orderId) {
    // Load transaction details via AJAX
    fetch(`/admin/transactions/${orderId}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('transactionDetails').innerHTML = html;
            openModal('transactionModal');
        })
        .catch(err => alert('Error loading transaction details'));
}

function printTransaction(orderId) {
    window.open(`/admin/transactions/${orderId}/print`, '_blank');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}
</script>
@endpush

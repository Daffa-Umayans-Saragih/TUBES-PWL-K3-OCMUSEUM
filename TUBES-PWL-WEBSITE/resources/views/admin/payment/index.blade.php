@extends('admin.layout.layout')

@section('admin-title')
    Payment Management
@endsection

@section('admin-content')
<div class="payment-dashboard">
    
    <!-- Page Header -->
    <div class="payment-header">
        <div class="header-content">
            <h1 class="page-title">💳 Payment Management</h1>
            <p class="page-subtitle">Manage and track payment transactions</p>
        </div>
    </div>
    
    <!-- Search & Filter Bar -->
    <div class="payment-filter-section">
        <div class="search-bar" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; background: white; padding: 1.25rem; border-radius: var(--radius-lg); border: 2px solid var(--color-border); align-items: center; justify-content: space-between; flex-wrap: wrap; box-shadow: var(--shadow-sm);">
            <form action="{{ route('admin.payment.index') }}" method="GET" style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap; margin: 0; width: 100%;">
                <!-- Preserve existing status filter in search form if set -->
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or order code..." style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); font-size: 0.9rem;">
                </div>
                
                <button type="submit" class="filter-tab active" style="padding: 0.6rem 1.5rem; cursor: pointer; border: none; font-size: 0.9rem;">
                    🔍 Search
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.payment.index') }}" class="filter-tab" style="padding: 0.6rem 1.5rem; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: var(--color-text);">
                        ❌ Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="filter-tabs">
            @foreach(['All' => 'All Payments', 'Pending' => 'Pending', 'Paid' => 'Paid', 'Failed' => 'Failed', 'Refunded' => 'Refunded'] as $status => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $status, 'page' => 1]) }}"
                   class="filter-tab {{ strtolower($filterStatus) === strtolower($status) ? 'active' : '' }}"
                   style="text-decoration: none;">
                    <span class="tab-label">{{ $label }}</span>
                    <span class="tab-count">
                        @if($status === 'All')
                            {{ $totalPayments }}
                        @elseif($status === 'Pending')
                            {{ $pendingCount }}
                        @elseif($status === 'Paid')
                            {{ $completedCount }}
                        @elseif($status === 'Failed')
                            {{ $failedCount }}
                        @elseif($status === 'Refunded')
                            {{ $refundedCount }}
                        @else
                            0
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    
    <!-- Analytics Cards -->
    <section class="payment-analytics-section">
        <div class="analytics-cards-grid">
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Total Payments',
                'value' => $totalPayments,
                'icon' => '📊',
                'trend' => 'Transactions',
                'color' => 'primary'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Total Revenue',
                'value' => '₹' . number_format($totalRevenue, 0),
                'icon' => '💰',
                'trend' => 'Completed',
                'color' => 'success'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Pending Amount',
                'value' => '₹' . number_format($pendingAmount, 0),
                'icon' => '⏳',
                'trend' => $pendingCount . ' pending',
                'color' => 'warning'
            ])
            
            @include('admin.ticket-analytics.components.stat-card', [
                'title' => 'Average Amount',
                'value' => '₹' . number_format($averageAmount, 0),
                'icon' => '📈',
                'trend' => 'Per transaction',
                'color' => 'info'
            ])
        </div>
    </section>
    
    <!-- Payments Table -->
    <section class="payment-table-section">
        <div class="section-header">
            <h2 class="section-title">Latest Payments</h2>
            <div class="header-actions">
                <span class="table-info">Showing {{ $paymentsList->count() }} of {{ $totalPayments }} payments</span>
            </div>
        </div>
        
        <div class="table-wrapper">
            @if($paymentsList->count() > 0)
                <table class="analytics-table payment-table">
                    <thead>
                        <tr>
                            <th class="col-payment-id">Payment ID</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-ticket-type">Ticket Type</th>
                            <th class="col-amount">Amount</th>
                            <th class="col-status">Payment Status</th>
                            <th class="col-ticket-usage">Ticket Usage</th>
                            <th class="col-date">Date</th>
                            <th class="col-actions" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $order = $payment->order;
                                $customer = $order->user ?? $order->guest;
                                $ticketCount = $order->tickets->where('status', '!=', 'cancelled')->count();
                                $ticketType = $order->tickets
                                    ->where('status', '!=', 'cancelled')
                                    ->first()
                                    ?->ticketAvailability
                                    ?->ticketType
                                    ?->name ?? 'N/A';
                                
                                $usageStatus = 'Pending';
                                $usageBadgeClass = 'pending';
                                $totalTickets = $ticketCount;
                                $usedTickets = $order->tickets->where('status', 'used')->count();
                                
                                if ($totalTickets === 0) {
                                    $usageStatus = 'Cancelled';
                                    $usageBadgeClass = 'danger';
                                } elseif ($usedTickets === $totalTickets) {
                                    $usageStatus = 'Used';
                                    $usageBadgeClass = 'success';
                                } elseif ($usedTickets > 0) {
                                    $usageStatus = "Used {$usedTickets}/{$totalTickets}";
                                    $usageBadgeClass = 'info';
                                }
                            @endphp
                            <tr class="table-row">
                                <td class="col-payment-id">
                                    <span class="payment-id-badge">{{ $payment->payment_id ?? 'N/A' }}</span>
                                </td>
                                <td class="col-customer">
                                    <div class="customer-cell">
                                        <div class="customer-name">{{ $customer?->name ?? 'Guest' }}</div>
                                        <div class="customer-email">{{ $customer?->email ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="col-ticket-type">
                                    <span class="ticket-type-badge">{{ $ticketType }}</span>
                                </td>
                                <td class="col-amount">
                                    <span class="amount">₹{{ number_format($payment->amount, 0) }}</span>
                                </td>
                                <td class="col-status">
                                    <span class="badge badge-{{ strtolower($payment->payment_status) }}">
                                        {{ $payment->payment_status }}
                                    </span>
                                </td>
                                <td class="col-ticket-usage">
                                    <span class="badge badge-{{ $usageBadgeClass }}">
                                        {{ $usageStatus }}
                                    </span>
                                </td>
                                <td class="col-date">
                                    <span class="date">{{ $payment->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="col-actions" style="text-align: right;">
                                    @php
                                        $canRefund = strtolower($payment->payment_status) === 'paid' && $usedTickets === 0;
                                    @endphp
                                    @if($canRefund)
                                        <form action="{{ route('admin.payment.refund', $payment->payment_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to refund this order? This will cancel all tickets and mark the payment as refunded.');" style="margin: 0; display: inline;">
                                            @csrf
                                            <button type="submit" class="filter-tab active" style="padding: 0.4rem 0.8rem; background-color: var(--color-danger, #ef4444); color: white; border: none; font-size: 0.8rem; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s ease;">
                                                ↩️ Refund
                                            </button>
                                        </form>
                                    @elseif(strtolower($payment->payment_status) === 'refunded')
                                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">↩️ Refunded</span>
                                    @elseif(strtolower($payment->payment_status) === 'paid' && $usedTickets > 0)
                                        <button disabled class="filter-tab" title="Refund unavailable: some tickets already used" style="padding: 0.4rem 0.8rem; background-color: #cbd5e1; color: #94a3b8; border: none; font-size: 0.8rem; border-radius: var(--radius-sm); cursor: not-allowed; opacity: 0.7;">
                                            ↩️ Refund
                                        </button>
                                    @else
                                        <span style="font-size: 0.8rem; color: #94a3b8;">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Pagination -->
                @if($payments->hasPages())
                    <div class="pagination-wrapper">
                        {{ $payments->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No payments found</h3>
                    <p>No payments match your current filter. Try selecting a different status.</p>
                </div>
            @endif
        </div>
    </section>
    
    <!-- Orders/Transactions Table -->
    <section class="orders-table-section">
        <div class="section-header">
            <h2 class="section-title">Latest Transactions</h2>
            <div class="header-actions">
                <span class="table-info">Recent orders</span>
            </div>
        </div>
        
        <div class="table-wrapper">
            @if($payments->count() > 0)
                <table class="analytics-table orders-table">
                    <thead>
                        <tr>
                            <th class="col-order-id">Order ID</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-total">Total Amount</th>
                            <th class="col-payment-status">Payment Status</th>
                            <th class="col-order-status">Order Status</th>
                            <th class="col-tickets">Tickets</th>
                            <th class="col-date">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments->take(10) as $payment)
                            @php
                                $order = $payment->order;
                                $customer = $order->user ?? $order->guest;
                                $ticketCount = $order->tickets->where('status', '!=', 'cancelled')->count();
                            @endphp
                            <tr class="table-row">
                                <td class="col-order-id">
                                    <span class="order-id-badge">{{ $order->order_id ?? 'N/A' }}</span>
                                </td>
                                <td class="col-customer">
                                    <div class="customer-cell">
                                        <div class="customer-name">{{ $customer?->name ?? 'Guest' }}</div>
                                        <div class="customer-email">{{ $customer?->email ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="col-total">
                                    <span class="amount">₹{{ number_format($order->total_amount, 0) }}</span>
                                </td>
                                <td class="col-payment-status">
                                    <span class="badge badge-{{ strtolower($payment->payment_status) }}">
                                        {{ $payment->payment_status }}
                                    </span>
                                </td>
                                <td class="col-order-status">
                                    <span class="badge badge-{{ strtolower($order->status) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="col-tickets">
                                    <span class="ticket-count">{{ $ticketCount }} ticket{{ $ticketCount !== 1 ? 's' : '' }}</span>
                                </td>
                                <td class="col-date">
                                    <span class="date">{{ $order->created_at->format('M d, Y') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No transactions found</h3>
                    <p>No recent transactions to display.</p>
                </div>
            @endif
        </div>
    </section>
    
</div>

@push('styles')
    @vite(['resources/css/admin/payment/index.css'])
@endpush

@push('scripts')
    @vite(['resources/js/admin/payment/index.js'])
@endpush

@endsection

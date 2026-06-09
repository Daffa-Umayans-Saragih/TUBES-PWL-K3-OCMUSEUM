@extends('admin.layout.layout')

@section('admin-title')
    Order Management & Scanning
@endsection

@section('admin-content')
<div class="orders-section">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Order Management</h1>
        <p class="page-subtitle">Scan and process orders | Track ticket usage</p>
    </div>

    <!-- Quick Stats -->
    <div class="orders-kpi-grid">
        <!-- Total Orders -->
        <div class="orders-kpi-card">
            <div class="kpi-icon-box kpi-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">TOTAL ORDERS</div>
                <div class="kpi-value">{{ $totalOrders ?? 0 }}</div>
                <div class="kpi-caption">today</div>
            </div>
        </div>
        
        <!-- Pending Orders -->
        <div class="orders-kpi-card">
            <div class="kpi-icon-box kpi-icon-amber">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">PENDING ORDERS</div>
                <div class="kpi-value">{{ $pendingOrders ?? 0 }}</div>
                <div class="kpi-caption">awaiting scan</div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="orders-kpi-card">
            <div class="kpi-icon-box kpi-icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">COMPLETED ORDERS</div>
                <div class="kpi-value">{{ $completedOrders ?? 0 }}</div>
                <div class="kpi-caption">scanned</div>
            </div>
        </div>
    </div>

    <!-- QR Scan Interface -->
    <section class="scan-section">
        <div class="scan-card">
            <div class="scan-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                    <path d="M3 14h3v3H3z" />
                    <path d="M6 17h1v4H3v-3" />
                    <path d="M10 21h1" />
                    <path d="M10 14h1v3" />
                    <path d="M10 3h1" />
                    <path d="M10 10h1" />
                    <path d="M3 10h1" />
                </svg>
            </div>
            
            <h2 class="scan-title">QR Ticket Scanner</h2>
            <p class="scan-subtitle">Scan ticket QR code or enter ticket ID manually</p>

            <div class="scan-input-container">
                <svg class="scan-input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input 
                    type="text" 
                    id="scanInput" 
                    class="scan-input" 
                    placeholder="Scan QR code or enter ticket ID..."
                    autofocus
                >
                <div class="scan-keyboard-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2" ry="2"/><path d="M6 8h.001"/><path d="M10 8h.001"/><path d="M14 8h.001"/><path d="M18 8h.001"/><path d="M8 12h.001"/><path d="M12 12h.001"/><path d="M16 12h.001"/><path d="M7 16h10"/></svg>
                </div>
            </div>

            <div id="searchStatus" class="search-status" style="display: none;"></div>

            <div class="scan-divider">
                <span>OR</span>
            </div>

            <button type="button" class="btn-camera" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                Use Camera to Scan
            </button>

            <div class="scan-status-badge">
                <div class="scan-status-dot"></div>
                <span class="scan-status-text">Scanner Ready</span>
                <span class="scan-status-divider">|</span>
                <span class="scan-status-desc">Point camera at QR code to scan automatically</span>
            </div>
        </div>
    </section>

    <!-- Scan Result / Order Details -->
    <section class="order-details-section" id="orderDetailsSection" style="display: none;">
        <h2 class="section-title">Order Details</h2>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-id">Order #<span id="detailOrderId">-</span></div>
                    <div class="order-code" id="detailOrderCode">-</div>
                </div>
                <div class="order-status-wrapper">
                    <span class="order-status" id="detailOrderStatus">Pending</span>
                    <div id="scanProgressBadge" class="scan-progress-badge" style="display:none;">
                        <span id="scanProgressText">0 / 0 scanned</span>
                    </div>
                </div>
            </div>

            <div class="order-info-grid">
                <div class="order-info-item">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value" id="detailCustomerName">-</span>
                </div>
                <div class="order-info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value" id="detailEmail">-</span>
                </div>
                <div class="order-info-item">
                    <span class="info-label">Order Date</span>
                    <span class="info-value" id="detailOrderDate">-</span>
                </div>
                <div class="order-info-item">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value" id="detailTotal">-</span>
                </div>
            </div>

            <!-- Current Ticket Info -->
            <div class="current-ticket-section">
                <h3 class="tickets-header">📌 Current Ticket</h3>
                <div class="current-ticket-card">
                    <div class="ticket-detail-grid">
                        <div class="ticket-detail-item">
                            <span class="detail-label">Ticket ID</span>
                            <span class="detail-value" id="currentTicketId">-</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="detail-label">QR Code</span>
                            <span class="detail-value mono" id="currentQrCode">-</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="detail-label">Ticket Type</span>
                            <span class="detail-value" id="currentTicketType">-</span>
                        </div>
                        <div class="ticket-detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span id="currentTicketStatus" class="ticket-status-badge">-</span>
                            </span>
                        </div>
                    </div>

                    <!-- Validation Actions -->
                    <div class="validation-actions" id="validationActions">
                        <button class="btn btn-validate" id="validateBtn" onclick="validateCurrentTicket()">
                            ✓ Mark as USED
                        </button>
                        <button class="btn btn-secondary" onclick="resetScan()">
                            Clear & Scan Another
                        </button>
                    </div>
                </div>
            </div>

            <!-- All Tickets in Order -->
            <h3 class="tickets-header">🎟️ All Tickets in Order</h3>
            <div class="tickets-list" id="ticketsList">
                <!-- Tickets will be populated here -->
            </div>

            <!-- Scanning Progress -->
            <div class="scan-progress">
                <h3 class="progress-label">Validation Progress</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                </div>
                <p class="progress-text"><span id="usedCount">0</span> / <span id="totalTickets">0</span> tickets validated</p>
            </div>
        </div>
    </section>

    <!-- Recent Orders Table -->
    <section class="orders-table-section">
        <h2 class="section-title">Recent Orders</h2>
        <div class="table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Tickets</th>
                        <th>Progress</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                        <tr>
                            <td><strong>#{{ $order['order_id'] ?? 'N/A' }}</strong></td>
                            <td><code>{{ $order['order_code'] ?? 'N/A' }}</code></td>
                            <td>{{ $order['customer_name'] ?? 'Guest' }}</td>
                            <td><span class="ticket-badge">{{ $order['ticket_count'] ?? 0 }}</span></td>
                            <td>
                                @php
                                    $used = $order['used_count'] ?? 0;
                                    $total = $order['ticket_count'] ?? 0;
                                @endphp
                                @if($total > 0)
                                    <span class="progress-pill progress-{{ $used >= $total ? 'full' : ($used > 0 ? 'partial' : 'none') }}">
                                        {{ $used }} / {{ $total }}
                                    </span>
                                @else
                                    <span class="progress-pill progress-none">—</span>
                                @endif
                            </td>
                            <td>${{ $order['total'] ?? '0.00' }}</td>
                            <td>
                                @php
                                    $status = $order['status'] ?? 'pending';
                                    $usedC = $order['used_count'] ?? 0;
                                    $totalC = $order['ticket_count'] ?? 0;
                                    $displayStatus = $status;
                                    if ($status === 'paid' && $usedC > 0 && $usedC < $totalC) {
                                        $displayStatus = 'partial';
                                    }
                                @endphp
                                <span class="status-badge status-{{ strtolower($displayStatus) }}">
                                    {{ $displayStatus === 'partial' ? 'Partial' : ucfirst(str_replace('_', ' ', $displayStatus)) }}
                                </span>
                            </td>
                            <td>{{ $order['date'] ?? 'N/A' }}</td>
                            <td>
                                <button 
                                    class="action-btn" 
                                    onclick="openOrder('{{ $order['order_id'] ?? '' }}', this)"
                                    title="View order #{{ $order['order_id'] ?? '' }} details"
                                >
                                    View Order
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<style>
* {
    box-sizing: border-box;
}

.orders-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: #333;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #333;
}

.orders-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.orders-kpi-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.orders-kpi-card:hover {
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.06), 0 4px 12px -3px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
    border-color: rgba(203, 213, 225, 0.9);
}

.kpi-icon-box {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kpi-icon-blue { background: #eff6ff; color: #3b82f6; border: 2px solid #dbeafe; }
.kpi-icon-amber { background: #fffbeb; color: #f59e0b; border: 2px solid #fef3c7; }
.kpi-icon-green { background: #ecfdf5; color: #10b981; border: 2px solid #d1fae5; }

.kpi-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.kpi-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.02em;
    margin-bottom: 0.25rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.kpi-caption {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #94a3b8;
}

/* QR Scan Interface Modern Redesign */
.scan-section {
    margin-bottom: 2.5rem;
    display: flex;
    justify-content: center;
}

.scan-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 22px;
    padding: 3rem 2.5rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.03), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
    width: 100%;
    max-width: 700px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.scan-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: #eff6ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    border: 4px solid #f8fafc;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
}

.scan-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.02em;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.scan-subtitle {
    font-size: 0.9375rem;
    color: #64748b;
    margin: 0 0 2rem 0;
}

.scan-input-container {
    width: 100%;
    max-width: 500px;
    position: relative;
    margin-bottom: 1.5rem;
}

.scan-input-icon {
    position: absolute;
    left: 1.125rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.scan-keyboard-icon {
    position: absolute;
    right: 1.125rem;
    top: 50%;
    transform: translateY(-50%);
    color: #cbd5e1;
}

.scan-input {
    width: 100%;
    padding: 1.125rem 3rem;
    font-size: 1.0625rem;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    color: #1e293b;
    background-color: #ffffff;
    transition: all 0.2s ease;
    outline: none;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.scan-input::placeholder {
    color: #94a3b8;
}

.scan-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.search-status {
    width: 100%;
    max-width: 500px;
    margin: 0 auto 1.5rem auto;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    text-align: center;
}

.search-status.loading { background-color: #f0f9ff; color: #0284c7; }
.search-status.error { background-color: #fef2f2; color: #dc2626; }
.search-status.success { background-color: #f0fdf4; color: #16a34a; }

.scan-divider {
    display: flex;
    align-items: center;
    text-align: center;
    width: 100%;
    max-width: 300px;
    margin: 0.5rem 0 1.5rem 0;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.scan-divider::before,
.scan-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}

.scan-divider::before { margin-right: 1rem; }
.scan-divider::after { margin-left: 1rem; }

.btn-camera {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: transparent;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    color: #3b82f6;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 2.5rem;
}

.btn-camera:hover:not(:disabled) {
    background: #f8fafc;
    border-color: #94a3b8;
}

.btn-camera:disabled {
    color: #94a3b8;
    border-color: #e2e8f0;
    cursor: not-allowed;
    background: #f8fafc;
}

.scan-status-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.875rem 1.5rem;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
}

.scan-status-dot {
    width: 10px;
    height: 10px;
    background-color: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.scan-status-text {
    font-weight: 600;
    color: #10b981;
    font-size: 0.875rem;
}

.scan-status-divider {
    color: #cbd5e1;
}

.scan-status-desc {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Order Details Section */
.order-details-section {
    margin-bottom: 2rem;
}

.order-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.order-id {
    font-size: 1.25rem;
    font-weight: 700;
    color: #333;
}

.order-code {
    font-size: 0.85rem;
    color: #999;
    font-family: 'Monaco', 'Courier New', monospace;
    margin-top: 0.25rem;
}

.order-status-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

.order-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    background-color: #fff3cd;
    color: #856404;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: background-color 0.3s, color 0.3s;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.order-info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.8rem;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
}

/* Current Ticket Section */
.current-ticket-section {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.current-ticket-card {
    background: white;
    padding: 1.5rem;
    border-radius: 6px;
    border-left: 4px solid #2196F3;
}

.ticket-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.ticket-detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.75rem;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
}

.detail-value.mono {
    font-family: 'Monaco', 'Courier New', monospace;
    color: #1976D2;
}

.ticket-status-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.ticket-status-badge.valid {
    background-color: #c8e6c9;
    color: #2e7d32;
}

.ticket-status-badge.used {
    background-color: #ffcdd2;
    color: #c62828;
}

.ticket-status-badge.expired {
    background-color: #ffe0b2;
    color: #e65100;
}

/* Scan Progress Badge in order header */
.scan-progress-badge {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 1px solid #90caf9;
    border-radius: 8px;
    padding: 0.5rem 1.25rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: #1565c0;
    text-align: center;
    min-width: 130px;
}

/* Progress pill in orders table */
.progress-pill {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
}
.progress-none  { background: #f5f5f5; color: #9e9e9e; }
.progress-partial { background: #fff3e0; color: #e65100; }
.progress-full  { background: #e8f5e9; color: #2e7d32; }

.status-partial {
    background-color: #fff3e0;
    color: #e65100;
}

/* Ticket items for new statuses */
.ticket-item.expired {
    background-color: #fff3e0;
    border-left-color: #ff9800;
    opacity: 0.8;
}
.ticket-status.expired {
    background-color: #ffe0b2;
    color: #e65100;
}


/* Validation Actions */
.validation-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    flex: 1;
    min-width: 150px;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-validate {
    background-color: #4CAF50;
    color: white;
}

.btn-validate:hover:not(:disabled) {
    background-color: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.btn-secondary {
    background-color: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

.btn-scan-next {
    background: linear-gradient(135deg, #1976D2, #0d47a1);
    color: white;
    border: none;
    animation: pulse-scan 1.5s ease-in-out infinite;
}

.btn-scan-next:hover:not(:disabled) {
    background: linear-gradient(135deg, #0d47a1, #082d74);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
    animation: none;
}

@keyframes pulse-scan {
    0%, 100% { box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.4); }
    50%       { box-shadow: 0 0 0 6px rgba(25, 118, 210, 0); }
}



/* Tickets Section */
.tickets-header {
    font-weight: 600;
    margin: 2rem 0 1rem 0;
    color: #333;
    font-size: 1rem;
}

.tickets-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.ticket-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background-color: #f5f5f5;
    border-radius: 6px;
    border-left: 4px solid #2196F3;
    transition: all 0.3s ease;
}

.ticket-item.used {
    background-color: #e8f5e9;
    border-left-color: #4CAF50;
}

.ticket-item.cancelled {
    background-color: #fafafa;
    border-left-color: #bdbdbd;
    opacity: 0.7;
}

/* Currently selected / active ticket in the list */
.ticket-item.active-ticket {
    background-color: #e3f2fd;
    border-left-color: #1565c0;
    box-shadow: 0 0 0 2px rgba(21, 101, 192, 0.25);
}

/* Hover effect for selectable (valid) ticket items */
.ticket-item.valid:not(.active-ticket):hover {
    background-color: #ede7f6;
    border-left-color: #7b1fa2;
    cursor: pointer;
    transform: translateX(2px);
}

/* Select / Current hint label inside each ticket row */
.ticket-select-hint {
    font-size: 0.72rem;
    color: #7b1fa2;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0.8;
}

.ticket-select-hint.current {
    color: #1565c0;
    opacity: 1;
}

.ticket-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.ticket-type {
    font-weight: 600;
    color: #333;
}

.ticket-code-small {
    font-size: 0.8rem;
    color: #999;
    font-family: 'Monaco', 'Courier New', monospace;
}

.ticket-status {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.ticket-status.valid {
    background-color: #c8e6c9;
    color: #2e7d32;
}

.ticket-status.used {
    background-color: #ffcdd2;
    color: #c62828;
}

.ticket-status.cancelled {
    background-color: #eeeeee;
    color: #616161;
}

.ticket-status.expired {
    background-color: #ffe0b2;
    color: #e65100;
}

/* Scanning Progress */
.scan-progress {
    background-color: #f5f5f5;
    padding: 1.5rem;
    border-radius: 6px;
    margin-top: 2rem;
}

.progress-label {
    font-weight: 600;
    margin: 0 0 0.75rem 0;
    color: #333;
}

.progress-bar {
    width: 100%;
    height: 28px;
    background-color: #e0e0e0;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    transition: width 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    min-width: 30px;
}

.progress-text {
    margin: 0;
    text-align: center;
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

/* Orders Table */
.orders-table-section {
    margin-top: 3rem;
}

.table-wrapper {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background-color: #f5f5f5;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
    font-size: 0.9rem;
}

.orders-table td {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
    font-size: 0.9rem;
}

.orders-table tbody tr:hover {
    background-color: #f9f9f9;
}

.orders-table code {
    background-color: #f5f5f5;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 0.85rem;
    color: #1976D2;
}

.ticket-badge {
    display: inline-block;
    background-color: #e3f2fd;
    color: #1976D2;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85rem;
}

.status-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending_payment,
.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-paid,
.status-completed {
    background-color: #d4edda;
    color: #155724;
}

.status-expired,
.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

.action-btn {
    padding: 0.5rem 1rem;
    background-color: #2196F3;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background-color: #1976D2;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(33, 150, 243, 0.3);
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #999;
}

@media (max-width: 768px) {
    .orders-section {
        padding: 1rem 0.5rem;
    }

    .page-header h1 {
        font-size: 1.5rem;
    }

    .scan-interface {
        padding: 2rem 1rem;
    }

    .order-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .order-info-grid {
        grid-template-columns: 1fr;
    }

    .ticket-detail-grid {
        grid-template-columns: 1fr;
    }

    .validation-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        min-width: unset;
    }

    .orders-table {
        font-size: 0.8rem;
    }

    .orders-table th, 
    .orders-table td {
        padding: 0.75rem 0.5rem;
    }

    .scan-input {
        max-width: 100%;
    }
}
</style>

<script>
let currentSearchData = null;
let currentTicketId = null;

// Listen for Enter key on scan input
document.getElementById('scanInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchTicket();
    }
});

/**
 * Search for ticket
 */
function searchTicket() {
    const input = document.getElementById('scanInput');
    const search = input.value.trim();

    if (!search) {
        showSearchStatus('Please enter a ticket ID or scan QR code', 'error');
        return;
    }

    // Disable input while searching
    input.disabled = true;
    showSearchStatus('Searching...', 'loading');

    // Call API
    fetch('{{ route("admin.orders.search-ticket") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ search: search })
    })
    .then(response => response.json())
    .then(data => {
        input.disabled = false;

        if (data.success) {
            showSearchStatus('✓ Ticket found!', 'success');
            currentSearchData = data.data;
            currentTicketId = data.data.ticket.ticket_id;
            displayOrderDetails(data.data);
            input.value = '';
        } else {
            showSearchStatus('✗ ' + (data.message || 'Ticket not found'), 'error');
            currentSearchData = null;
            currentTicketId = null;
        }
    })
    .catch(error => {
        input.disabled = false;
        console.error('Error:', error);
        showSearchStatus('✗ Error searching ticket. Please try again.', 'error');
    });
}

/**
 * Show search status message
 */
function showSearchStatus(message, type) {
    const statusEl = document.getElementById('searchStatus');
    statusEl.textContent = message;
    statusEl.className = 'search-status ' + type;
    statusEl.style.display = 'block';

    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            statusEl.style.display = 'none';
        }, 2500);
    }
}

/**
 * Display order and ticket details
 */
function displayOrderDetails(data) {
    const ticket = data.ticket;
    const order = data.order;
    const allTickets = data.all_tickets;

    // Show details section
    document.getElementById('orderDetailsSection').style.display = 'block';

    // Order info
    document.getElementById('detailOrderId').textContent = order.order_id;
    document.getElementById('detailOrderCode').textContent = 'Code: ' + order.order_code;
    document.getElementById('detailCustomerName').textContent = order.customer_name;
    document.getElementById('detailEmail').textContent = order.customer_email;
    document.getElementById('detailOrderDate').textContent = order.order_date;
    document.getElementById('detailTotal').textContent = '$' + order.total_amount;
    document.getElementById('detailOrderStatus').textContent = order.status.replace('_', ' ').toUpperCase();

    // Current ticket info
    document.getElementById('currentTicketId').textContent = '#' + ticket.ticket_id;
    document.getElementById('currentQrCode').textContent = ticket.qr_code;
    document.getElementById('currentTicketType').textContent = ticket.type;
    updateTicketStatusBadge('currentTicketStatus', ticket.status);

    // Validation button visibility
    const validateBtn = document.getElementById('validateBtn');
    if (ticket.is_used) {
        validateBtn.disabled = true;
        validateBtn.textContent = '⚠️ Already Used';
    } else {
        validateBtn.disabled = false;
        validateBtn.textContent = '✓ Mark as USED';
    }

    // List all tickets in order
    displayAllTickets(allTickets);

    // Update progress
    updateProgress(allTickets);

    // Scroll to details
    document.getElementById('orderDetailsSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Display all tickets in the order as a selectable list.
 * Each ticket can be clicked to set it as the Current Ticket for validation.
 */
function displayAllTickets(tickets) {
    const listEl = document.getElementById('ticketsList');
    listEl.innerHTML = '';

    if (!tickets || tickets.length === 0) {
        listEl.innerHTML = '<p style="color: #999; text-align: center;">No tickets found</p>';
        return;
    }

    tickets.forEach(ticket => {
        const ticketEl = document.createElement('div');
        const isCurrentTicket = (ticket.ticket_id === currentTicketId);
        const isSelectable = (ticket.status === 'valid');

        ticketEl.className = 'ticket-item ' + ticket.status + (isCurrentTicket ? ' active-ticket' : '');
        ticketEl.dataset.ticketId = ticket.ticket_id;

        const statusBadge = getStatusBadgeHtml(ticket.status);
        const usedInfo    = ticket.used_at ? ` · Used: ${ticket.used_at}` : '';
        const selectHint  = isSelectable && !isCurrentTicket
            ? '<span class="ticket-select-hint">Click to select →</span>'
            : (isCurrentTicket ? '<span class="ticket-select-hint current">● Current</span>' : '');

        ticketEl.innerHTML = `
            <div class="ticket-info">
                <div>
                    <div class="ticket-type">🎟️ ${ticket.type}</div>
                    <div class="ticket-code-small">ID: ${ticket.ticket_id} | QR: ${ticket.qr_code}${usedInfo}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                ${selectHint}
                <span class="ticket-status ${ticket.status}">${statusBadge}</span>
            </div>
        `;

        // Make valid (non-used) tickets clickable to select as current ticket
        if (isSelectable && !isCurrentTicket) {
            ticketEl.style.cursor = 'pointer';
            ticketEl.addEventListener('click', () => selectTicket(ticket));
        }

        listEl.appendChild(ticketEl);
    });
}

/**
 * Select a specific ticket from the All Tickets list as the Current Ticket.
 * Updates the Current Ticket panel without a new server round-trip.
 */
function selectTicket(ticket) {
    if (!currentSearchData) return;

    // Update the globally tracked current ticket
    currentTicketId = ticket.ticket_id;
    currentSearchData.ticket = ticket;

    // Update Current Ticket info panel
    document.getElementById('currentTicketId').textContent   = '#' + ticket.ticket_id;
    document.getElementById('currentQrCode').textContent     = ticket.qr_code;
    document.getElementById('currentTicketType').textContent = ticket.type;
    updateTicketStatusBadge('currentTicketStatus', ticket.status);

    // Reset validate button
    const validateBtn = document.getElementById('validateBtn');
    validateBtn.disabled    = false;
    validateBtn.textContent = '✓ Mark as USED';

    // Hide "Scan Next" button — user is manually selecting
    const scanNextBtn = document.getElementById('scanNextBtn');
    if (scanNextBtn) scanNextBtn.style.display = 'none';

    // Re-render ticket list to update active highlight
    displayAllTickets(currentSearchData.all_tickets);

    // Scroll Current Ticket into view
    document.getElementById('currentTicketId')
        .closest('.ticket-detail-item')
        ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    showSearchStatus(`Ticket #${ticket.ticket_id} selected as current ticket.`, 'info');
}

/**
 * Get status badge HTML
 */
function getStatusBadgeHtml(status) {
    const badges = {
        'valid':     '✓ Valid',
        'used':      '✓ Used',
        'cancelled': '✗ Cancelled',
        'expired':   '⏰ Expired',
    };
    return badges[status] || status;
}

/**
 * Update ticket status badge
 */
function updateTicketStatusBadge(elementId, status) {
    const el = document.getElementById(elementId);
    el.textContent = getStatusBadgeHtml(status);
    el.className = 'ticket-status-badge ' + status;
}

/**
 * Update progress bar
 */
function updateProgress(allTickets) {
    if (!allTickets) return;

    const total     = allTickets.length;
    const used      = allTickets.filter(t => t.status === 'used').length;
    const cancelled = allTickets.filter(t => t.status === 'cancelled' || t.status === 'expired').length;
    const effective = total - cancelled; // non-cancelled tickets
    const percentage = effective > 0 ? Math.round((used / effective) * 100) : 0;

    document.getElementById('usedCount').textContent    = used;
    document.getElementById('totalTickets').textContent = total;

    const progressFill = document.getElementById('progressFill');
    progressFill.style.width = percentage + '%';
    progressFill.textContent = percentage > 10 ? percentage + '%' : '';

    // Update header progress badge
    const badge = document.getElementById('scanProgressBadge');
    const badgeText = document.getElementById('scanProgressText');
    badge.style.display = 'block';
    badgeText.textContent = `${used} / ${effective} scanned`;

    // Determine header order status label
    const orderStatusEl = document.getElementById('detailOrderStatus');
    if (used === 0) {
        orderStatusEl.style.backgroundColor = '#fff3cd';
        orderStatusEl.style.color           = '#856404';
    } else if (used < effective) {
        orderStatusEl.style.backgroundColor = '#fff3e0';
        orderStatusEl.style.color           = '#e65100';
    } else {
        orderStatusEl.style.backgroundColor = '#d4edda';
        orderStatusEl.style.color           = '#155724';
    }
}

/**
 * Validate (mark as used) current ticket
 */
function validateCurrentTicket() {
    if (!currentTicketId) {
        alert('No ticket selected');
        return;
    }

    if (!confirm('Mark this ticket as USED?')) {
        return;
    }

    const btn = document.getElementById('validateBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Processing...';

    fetch('{{ route("admin.orders.validate-ticket") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ticket_id: currentTicketId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSearchStatus('✓ ' + data.message, 'success');

            if (currentSearchData) {
                currentSearchData.ticket.status = 'used';
                currentSearchData.ticket.used_at = data.data.used_at;
                currentSearchData.ticket.is_used = true;

                // Update the ticket in the all_tickets list
                const updatedTickets = currentSearchData.all_tickets.map(t => {
                    if (t.ticket_id === currentTicketId) {
                        return { ...t, status: 'used', used_at: data.data.used_at };
                    }
                    return t;
                });
                currentSearchData.all_tickets = updatedTickets;

                // Update display
                updateTicketStatusBadge('currentTicketStatus', 'used');
                btn.disabled = true;
                btn.textContent = '✅ Marked as USED';

                // Show "Scan Next Ticket" button for quick multi-ticket scanning
                const scanNextBtn = document.getElementById('scanNextBtn');
                if (scanNextBtn) scanNextBtn.style.display = 'flex';

                // Refresh all tickets display
                displayAllTickets(updatedTickets);

                // Update progress
                updateProgress(updatedTickets);
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to validate ticket'));
            btn.disabled = false;
            btn.textContent = '✓ Mark as USED';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error validating ticket');
        btn.disabled = false;
        btn.textContent = '✓ Mark as USED';
    });
}

/**
 * Reset scan and clear details.
 * FIX: always re-enable the input field to prevent scanner from getting stuck.
 * Also resets the validate button to its initial ready state.
 */
function resetScan() {
    const input = document.getElementById('scanInput');
    input.value    = '';
    input.disabled = false;   // ← ensure input is always re-enabled

    // Hide the order details panel
    document.getElementById('orderDetailsSection').style.display = 'none';
    document.getElementById('scanProgressBadge').style.display   = 'none';
    document.getElementById('searchStatus').style.display         = 'none';

    // Reset validate button + hide scan-next button
    const validateBtn = document.getElementById('validateBtn');
    if (validateBtn) {
        validateBtn.disabled    = false;
        validateBtn.textContent = '✓ Mark as USED';
    }
    const scanNextBtn = document.getElementById('scanNextBtn');
    if (scanNextBtn) scanNextBtn.style.display = 'none';

    // Clear state
    currentSearchData = null;
    currentTicketId   = null;
    input.focus();
}

/**
 * Open an order from the table row.
 *
 * CRITICAL: sends { order_id: N } as a DEDICATED param to the backend (PATH A),
 * NOT as a generic `search` string. This bypasses the QR/ticket_id lookup chain
 * entirely, preventing the collision where ticket #12 shadows Order #12.
 */
function openOrder(orderId, btn) {
    // 1. Full state reset — clears all prior order state
    resetScan();

    // 2. Brief visual feedback on the clicked row button
    if (btn) {
        const originalText = btn.textContent;
        btn.textContent = 'Opening...';
        btn.disabled    = true;
        setTimeout(() => {
            btn.textContent = originalText;
            btn.disabled    = false;
        }, 1500);
    }

    const input = document.getElementById('scanInput');
    input.disabled = false;

    // 3. Show loading status
    showSearchStatus('Loading Order #' + orderId + '...', 'loading');

    // 4. POST with order_id as a dedicated parameter (bypasses QR/ticket_id search)
    fetch('{{ route("admin.orders.search-ticket") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order_id: parseInt(orderId, 10) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSearchStatus('✓ Order #' + orderId + ' loaded', 'success');
            currentSearchData = data.data;
            currentTicketId   = data.data.ticket.ticket_id;
            displayOrderDetails(data.data);
        } else {
            showSearchStatus('✗ ' + (data.message || 'Order not found'), 'error');
            currentSearchData = null;
            currentTicketId   = null;
        }
    })
    .catch(error => {
        console.error('openOrder error:', error);
        showSearchStatus('✗ Error loading order. Please try again.', 'error');
    });

    // 5. Scroll to the scan/result section
    document.querySelector('.scan-section')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Focus search input and set value (used by QR scanner Enter key flow).
 * Resets state first to prevent order lock-in.
 */
function focusAndSearch(value) {
    resetScan();
    const input    = document.getElementById('scanInput');
    input.disabled = false;
    input.value    = value;
    input.focus();
    searchTicket();
}
</script>

@endsection

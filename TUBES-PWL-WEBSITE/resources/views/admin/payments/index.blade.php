
@extends('admin.layout.layout')

@section('admin-title')
    Payment Management
@endsection

@section('admin-content')
<div class="payments-section">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Payment Management</h1>
        <p class="page-subtitle">Check, cancel, and refund payments</p>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats-grid">
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Payments',
            'value' => $totalPayments ?? 0,
            'icon' => '💳',
            'trend' => 'processed',
            'color' => 'primary'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Pending Payments',
            'value' => $pendingPayments ?? 0,
            'icon' => '⏳',
            'trend' => 'awaiting',
            'color' => 'warning'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Refund Requests',
            'value' => $refundRequests ?? 0,
            'icon' => '🔄',
            'trend' => 'pending review',
            'color' => 'info'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Revenue',
            'value' => '$' . number_format($totalRevenue ?? 0, 2),
            'icon' => '💰',
            'trend' => 'all time',
            'color' => 'success'
        ])
    </div>

    <!-- Payments Table -->
    <section class="payments-table-section">
        <h2 class="section-title">Recent Payments</h2>
        <div class="table-wrapper">
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments ?? [] as $payment)
                        <tr>
                            <td><strong>{{ $payment['payment_id'] ?? 'N/A' }}</strong></td>
                            <td>{{ $payment['order_id'] ?? 'N/A' }}</td>
                            <td>{{ $payment['customer_name'] ?? 'Guest' }}</td>
                            <td>${{ number_format($payment['amount'] ?? 0, 2) }}</td>
                            <td>{{ $payment['method'] ?? 'Card' }}</td>
                            <td>
                                <span class="status-badge status-{{ $payment['status'] ?? 'pending' }}">
                                    {{ ucfirst($payment['status'] ?? 'pending') }}
                                </span>
                            </td>
                            <td>{{ $payment['date'] ?? 'N/A' }}</td>
                            <td class="actions-cell">
                                @if($payment['status'] === 'completed')
                                    <button class="action-btn action-refund" onclick="showRefundModal('{{ $payment['payment_id'] }}')">Refund</button>
                                @elseif($payment['status'] === 'pending')
                                    <button class="action-btn action-cancel" onclick="cancelPayment('{{ $payment['payment_id'] }}')">Cancel</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Refund Requests Section -->
    <section class="refund-section">
        <h2 class="section-title">Refund Requests</h2>
        <div class="refund-cards">
            @foreach($refundQueue ?? [] as $refund)
                <div class="refund-card">
                    <div class="refund-header">
                        <div class="refund-id">Refund Request #{{ $refund['request_id'] ?? 'N/A' }}</div>
                        <span class="refund-status">{{ ucfirst($refund['status'] ?? 'pending') }}</span>
                    </div>
                    
                    <div class="refund-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">{{ $refund['order_id'] ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer:</span>
                            <span class="detail-value">{{ $refund['customer_name'] ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">{{ $refund['customer_email'] ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value" style="color: #2196F3; font-weight: 700;">{{ $refund['amount'] ?? '$0.00' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason:</span>
                            <span class="detail-value">{{ $refund['reason'] ?? 'Not specified' }}</span>
                        </div>
                    </div>

                    <div class="refund-actions">
                        <button class="btn btn-secondary" onclick="rejectRefund('{{ $refund['request_id'] }}')">Reject</button>
                        <button class="btn btn-success" onclick="approveRefund('{{ $refund['request_id'] }}')">Approve & Send Email</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>

<!-- Refund Modal -->
<div class="modal" id="refundModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Process Refund</h2>
            <button class="modal-close" onclick="closeRefundModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="refundForm" onsubmit="submitRefund(event)">
                <div class="form-group">
                    <label class="form-label">Payment ID</label>
                    <input type="text" id="refundPaymentId" readonly class="form-input" />
                </div>

                <div class="form-group">
                    <label class="form-label">Refund Amount</label>
                    <input type="number" id="refundAmount" step="0.01" class="form-input" required />
                </div>

                <div class="form-group">
                    <label class="form-label">Reason for Refund</label>
                    <select id="refundReason" class="form-input" required>
                        <option value="">Select reason...</option>
                        <option value="customer_request">Customer Request</option>
                        <option value="duplicate_charge">Duplicate Charge</option>
                        <option value="order_cancelled">Order Cancelled</option>
                        <option value="system_error">System Error</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea id="refundNotes" class="form-input" rows="3"></textarea>
                </div>

                <div class="info-box">
                    <h3 class="info-box__title">Refund Email Will Include:</h3>
                    <ul class="info-box__list">
                        <li>Refund amount: <strong id="refundAmountDisplay">$0.00</strong></li>
                        <li>Ticket details from the original order</li>
                        <li>Refund processing timeline</li>
                        <li>Payment method details</li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.payments-section {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
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
}

.quick-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.payments-table-section {
    margin-bottom: 3rem;
}

.table-wrapper {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.payments-table {
    width: 100%;
    border-collapse: collapse;
}

.payments-table th {
    background-color: #f5f5f5;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

.payments-table td {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.payments-table tbody tr:hover {
    background-color: #f9f9f9;
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-completed {
    background-color: #d4edda;
    color: #155724;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-failed {
    background-color: #f8d7da;
    color: #721c24;
}

.status-refunded {
    background-color: #d1ecf1;
    color: #0c5460;
}

.actions-cell {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.4rem 0.8rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.action-refund {
    background-color: #2196F3;
    color: white;
}

.action-refund:hover {
    background-color: #1976D2;
}

.action-cancel {
    background-color: #f44336;
    color: white;
}

.action-cancel:hover {
    background-color: #da190b;
}

.refund-section {
    margin-bottom: 3rem;
}

.refund-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.refund-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
}

.refund-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.refund-id {
    font-weight: 600;
}

.refund-status {
    padding: 0.35rem 0.75rem;
    background-color: #fff3cd;
    color: #856404;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.refund-details {
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.9rem;
}

.detail-label {
    color: #666;
    font-weight: 600;
}

.detail-value {
    color: #333;
}

.refund-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    flex: 1;
    padding: 0.6rem 1rem;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-secondary {
    background-color: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background-color: #e0e0e0;
}

.btn-success {
    background-color: #4CAF50;
    color: white;
}

.btn-success:hover {
    background-color: #45a049;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 90%;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95rem;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #2196F3;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.info-box {
    background-color: #f5f5f5;
    border-left: 4px solid #2196F3;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
}

.info-box__title {
    font-weight: 600;
    margin: 0 0 0.75rem 0;
}

.info-box__list {
    margin: 0;
    padding-left: 1.5rem;
    font-size: 0.9rem;
}

.info-box__list li {
    margin-bottom: 0.5rem;
    color: #555;
}

.form-actions {
    display: flex;
    gap: 1rem;
}

@media (max-width: 768px) {
    .refund-cards {
        grid-template-columns: 1fr;
    }
    
    .actions-cell {
        flex-direction: column;
    }
    
    .refund-actions {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
    }
}
</style>

<script>
function showRefundModal(paymentId) {
    document.getElementById('refundPaymentId').value = paymentId;
    document.getElementById('refundModal').classList.add('active');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.remove('active');
}

function submitRefund(event) {
    event.preventDefault();
    const amount = document.getElementById('refundAmount').value;
    const reason = document.getElementById('refundReason').value;
    const notes = document.getElementById('refundNotes').value;
    
    console.log('Processing refund:', {
        amount,
        reason,
        notes
    });
    
    // Send refund email (implementation)
    closeRefundModal();
}

function cancelPayment(paymentId) {
    if (confirm('Are you sure you want to cancel this payment?')) {
        console.log('Cancelling payment:', paymentId);
    }
}

function approveRefund(requestId) {
    if (confirm('Send refund approval email to customer?')) {
        console.log('Approving refund:', requestId);
    }
}

function rejectRefund(requestId) {
    if (confirm('Are you sure you want to reject this refund request?')) {
        console.log('Rejecting refund:', requestId);
    }
}

document.getElementById('refundAmount')?.addEventListener('change', function() {
    document.getElementById('refundAmountDisplay').textContent = '$' + parseFloat(this.value || 0).toFixed(2);
});
</script>
@endsection

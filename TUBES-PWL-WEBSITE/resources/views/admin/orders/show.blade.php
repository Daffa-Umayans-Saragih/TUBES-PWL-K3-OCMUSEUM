@extends('admin.layout.layout')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0" style="color: #333;">{{ $title }}</h1>
            <p class="text-muted mt-1">{{ $subtitle }}</p>
        </div>
        <div class="col-auto d-flex gap-2">
            @if($order->order_status === 'completed')
                <button type="button" class="btn btn-secondary" disabled title="Completed orders cannot be cancelled" style="cursor: not-allowed;">
                    ✓ Completed
                </button>
            @elseif($order->order_status === 'cancelled')
                <button type="button" class="btn btn-secondary" disabled style="cursor: not-allowed;">
                    ✗ Cancelled
                </button>
            @else
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    🚫 Cancel Order
                </button>
            @endif

            <a href="{{ route('admin.orders.edit', $order->order_id) }}" class="btn btn-warning">
                ✎ Edit
            </a>
            @if($order->order_status !== 'completed' && $order->order_status !== 'cancelled')
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    🗑 Delete
                </button>
            @endif
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                ← Back to Orders
            </a>
        </div>
    </div>

    <!-- Breadcrumbs -->
    @if($breadcrumbs)
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-light px-3 py-2 rounded">
                @foreach($breadcrumbs as $crumb)
                    @if($crumb['isCurrent'] ?? false)
                        <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $crumb['href'] }}" style="color: #2196F3;">{{ $crumb['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    <!-- Quick Info Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card info-card" style="border-left: 4px solid #2196F3;">
                <div class="card-body">
                    <h6 class="card-title text-muted">Order Code</h6>
                    <p class="card-text h5">{{ $order->order_code }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card info-card" style="border-left: 4px solid #4CAF50;">
                <div class="card-body">
                    <h6 class="card-title text-muted">Order Type</h6>
                    <p class="card-text h5">
                        <span class="badge" style="background-color: #4CAF50;">{{ ucfirst($order->order_type) }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card info-card" style="border-left: 4px solid #FF9800;">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Amount</h6>
                    <p class="card-text h5">${{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card info-card" style="border-left: 4px solid #f44336;">
                <div class="card-body">
                    <h6 class="card-title text-muted">Status</h6>
                    <p class="card-text h5">
                        @if($order->order_status === 'pending_payment')
                            <span class="badge bg-warning text-dark">Pending Payment</span>
                        @elseif($order->order_status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($order->order_status === 'completed')
                            <span class="badge bg-info text-dark">Completed</span>
                        @elseif($order->order_status === 'expired')
                            <span class="badge bg-secondary">Expired</span>
                        @elseif($order->order_status === 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @else
                            <span class="badge bg-primary">{{ ucfirst($order->order_status) }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light" style="border-bottom: 2px solid #e0e0e0;">
            <h5 class="mb-0" style="color: #333;">Customer Information</h5>
        </div>
        <div class="card-body">
            @if($order->user)
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Customer Type:</strong> Registered User</p>
                        <p><strong>Name:</strong> {{ $order->user->profile?->first_name ?? 'N/A' }} {{ $order->user->profile?->last_name ?? '' }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>User ID:</strong> <code>{{ $order->user->user_id }}</code></p>
                        <p><strong>Joined:</strong> {{ $order->user->created_at?->format('M d, Y') }}</p>
                    </div>
                </div>
            @elseif($order->guest)
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Customer Type:</strong> Guest</p>
                        <p><strong>Name:</strong> {{ $order->guest?->guest_name ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $order->guest?->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Guest ID:</strong> <code>{{ $order->guest->guest_id }}</code></p>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">No customer information available</div>
            @endif
        </div>
    </div>

    <!-- Order Details -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light" style="border-bottom: 2px solid #e0e0e0;">
            <h5 class="mb-0" style="color: #333;">Order Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Order Date:</strong> {{ $order->order_date->format('M d, Y H:i') }}</p>
                    <p><strong>Created At:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    @if($order->expired_at)
                        <p><strong>Expires At:</strong> {{ $order->expired_at->format('M d, Y H:i') }}</p>
                    @else
                        <p><strong>Expires At:</strong> <span class="text-muted">No expiration set</span></p>
                    @endif
                    <p><strong>Updated At:</strong> {{ $order->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Tickets -->
    @if($order->tickets && $order->tickets->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light" style="border-bottom: 2px solid #e0e0e0;">
                <h5 class="mb-0" style="color: #333;">Related Tickets ({{ $order->tickets->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket ID</th>
                                <th>Type</th>
                                <th>QR Code</th>
                                <th>Status</th>
                                <th>Used At</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->tickets as $ticket)
                                <tr>
                                    <td><code>{{ $ticket->ticket_id }}</code></td>
                                    <td>
                                        @if($ticket->ticketAvailability?->ticketType)
                                            {{ $ticket->ticketAvailability->ticketType->ticket_type_name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td><code style="font-size: 0.8rem;">{{ substr($ticket->qr_code, 0, 12) }}...</code></td>
                                    <td>
                                        @if($ticket->status === 'used')
                                            <span class="badge bg-secondary">Used</span>
                                        @elseif($ticket->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($ticket->status === 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($ticket->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->used_at?->format('M d, Y H:i') ?? '—' }}</td>
                                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Related Payments -->
    @if($order->payments && $order->payments->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light" style="border-bottom: 2px solid #e0e0e0;">
                <h5 class="mb-0" style="color: #333;">Related Payments ({{ $order->payments->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Payment ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->payments as $payment)
                                <tr>
                                    <td><code>{{ $payment->payment_id }}</code></td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                    <td>
                                        @if($payment->payment_status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($payment->payment_status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($payment->payment_status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($payment->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->payment_date?->format('M d, Y H:i') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Membership Information -->
    @if($order->membership)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light" style="border-bottom: 2px solid #e0e0e0;">
                <h5 class="mb-0" style="color: #333;">Membership Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Membership ID:</strong> <code>{{ $order->membership->membership_id }}</code></p>
                        <p><strong>Status:</strong> 
                            @if($order->membership->membership_status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($order->membership->membership_status === 'inactive')
                                <span class="badge bg-secondary">Inactive</span>
                            @else
                                <span class="badge bg-info">{{ ucfirst($order->membership->membership_status) }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Membership Type:</strong> {{ $order->membership->membership_type ?? 'N/A' }}</p>
                        <p><strong>Created At:</strong> {{ $order->membership->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order?</p>
                <p class="text-muted"><strong>Order Code:</strong> {{ $order->order_code }}</p>
                @if($order->order_status === 'paid')
                    <p class="text-warning"><strong>Note:</strong> Since this order is PAID, the payment status will be updated to <strong>Refunded</strong>.</p>
                @else
                    <p class="text-info"><strong>Note:</strong> The order status will be updated to Cancelled.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form action="{{ route('admin.orders.cancel', $order->order_id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order?</p>
                <p class="text-muted"><strong>Order Code:</strong> {{ $order->order_code }}</p>
                <p class="text-danger"><strong>This action will cancel the order and refund any payments (preserving database history).</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.orders.destroy', $order->order_id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .info-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }

    .info-card:hover {
        transform: translateY(-2px);
    }

    .card {
        border: none;
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .table {
        margin-bottom: 0;
    }

    .badge {
        padding: 0.35rem 0.65rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    code {
        background-color: #f5f5f5;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        color: #d63384;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-warning {
        background-color: #FF9800;
        color: white;
    }

    .btn-warning:hover {
        background-color: #F57C00;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-danger {
        background-color: #f44336;
        color: white;
    }

    .btn-danger:hover {
        background-color: #da190b;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }

    .btn-secondary:hover {
        background-color: #e0e0e0;
    }
</style>
@endsection

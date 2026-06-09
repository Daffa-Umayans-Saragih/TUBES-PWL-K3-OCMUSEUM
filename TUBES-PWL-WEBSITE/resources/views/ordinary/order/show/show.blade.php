@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/order/show/show.css')
@endpush

@section('title', $mode === 'list' ? 'My Orders - OC Museum' : 'Order Details - OC Museum')

@section('content')
<div class="order-container">
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($mode === 'list')
        <div class="order-header">
            <h1>My Orders</h1>
            <p>View your past ticket purchases and payment status.</p>
        </div>

        <div class="order-list">
            @forelse($orders as $order)
                <a href="{{ route('order.show.detail', $order->order_id) }}" class="order-card {{ strtolower($order->payment?->payment_status ?? 'pending') }}">
                    <div class="card-header">
                        <span class="order-id">Order Code: {{ substr($order->order_code, 0, 8) }}...</span>
                        <span class="order-status {{ strtolower($order->payment?->payment_status ?? 'pending') }}">
                            {{ $order->payment?->payment_status ?? 'Pending' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="card-row">
                            <span class="label">Date:</span>
                            <span class="value">{{ optional($order->order_date)->format('M d, Y h:i A') ?? 'N/A' }}</span>
                        </div>
                        <div class="card-row">
                            <span class="label">Total Amount:</span>
                            <span class="value">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        View Details &rarr;
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <p>You have no orders yet.</p>
                    <a href="{{ route('ticket.admission') }}" class="btn-primary">Browse Tickets</a>
                </div>
            @endforelse
        </div>
    @elseif($mode === 'detail')
        <div class="order-header detail-header">
            <a href="{{ route('order.show') }}" class="back-link">&larr; Back to Orders</a>
            <h1>Order Details</h1>
            <p>Order Code: {{ $order->order_code }}</p>
        </div>

        @php
            $isMembershipOrder = $order->order_type === 'membership';
            $membership = $order->membership;

            $membershipStatusMap = [
                'verification_pending' => ['label' => 'Waiting for Activation', 'class' => 'pending'],
                'gift_pending_claim' => ['label' => 'Gift Waiting Claim', 'class' => 'gift'],
                'active' => ['label' => 'Active', 'class' => 'active'],
                'expired' => ['label' => 'Expired', 'class' => 'expired'],
                'cancelled' => ['label' => 'Cancelled', 'class' => 'cancelled'],
            ];

            $membershipStatus = $membershipStatusMap[$membership?->membership_status ?? ''] ?? ['label' => ucfirst((string) ($membership?->membership_status ?? 'Pending')), 'class' => 'pending'];
        @endphp

        <div class="detail-layout">
            <div class="detail-sidebar">
                <div class="summary-card">
                    <h3>Summary</h3>
                    <div class="summary-row">
                        <span class="label">Order Date:</span>
                        <span class="value">{{ optional($order->order_date)->format('M d, Y h:i A') ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Order Type:</span>
                        <span class="value">{{ $isMembershipOrder ? 'Membership' : 'Ticket' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Payment Status:</span>
                        <span class="value status-badge {{ strtolower($order->payment?->payment_status ?? 'pending') }}">
                            {{ $order->payment?->payment_status ?? 'Pending' }}
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span class="label">Total:</span>
                        <span class="value">${{ number_format($order->total_amount, 2) }}</span>
                    </div>

                    @if(($order->payment?->payment_status ?? 'Pending') === 'Pending')
                        <form action="{{ route('ticket.checkout.pay', $order->order_id) }}" method="POST" style="margin-top: 15px;">
                            @csrf
                            <button type="submit" class="btn-pay">Simulate Payment (Dev)</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="detail-main">
                @if($isMembershipOrder)
                    <div class="membership-card">
                        <div class="membership-card__header">
                            <div>
                                <p class="membership-eyebrow">Membership Order</p>
                                <h3>Membership Details</h3>
                            </div>
                            <span class="membership-badge {{ $membershipStatus['class'] }}">{{ $membershipStatus['label'] }}</span>
                        </div>

                        <div class="membership-grid">
                            <div class="membership-field">
                                <span class="label">Membership Type</span>
                                <span class="value">Standard Membership</span>
                            </div>
                            <div class="membership-field">
                                <span class="label">Membership Status</span>
                                <span class="value">{{ $membershipStatus['label'] }}</span>
                            </div>
                            <div class="membership-field">
                                <span class="label">Premium Started At</span>
                                <span class="value">{{ optional($membership?->activated_at)->format('M d, Y h:i A') ?? 'Not Activated Yet' }}</span>
                            </div>
                            <div class="membership-field">
                                <span class="label">Premium Expires At</span>
                                <span class="value">{{ optional($membership?->expires_at)->format('M d, Y h:i A') ?? 'Pending Activation' }}</span>
                            </div>
                            <div class="membership-field">
                                <span class="label">Membership Duration</span>
                                <span class="value">1 Month</span>
                            </div>
                            <div class="membership-field">
                                <span class="label">Auto Renewal</span>
                                <span class="value">{{ $membership?->auto_renewal ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="membership-field membership-field--full">
                                <span class="label">Membership For</span>
                                <span class="value">{{ $membership?->is_gift ? 'Gift Membership' : 'For Yourself' }}</span>
                                <span class="helper-text">
                                    @if($membership?->is_gift)
                                        Recipient Email: {{ $membership?->recipient_email ?? $membership?->user?->email ?? 'N/A' }}
                                    @else
                                        {{ $membership?->recipient_email ?? $membership?->user?->email ?? 'N/A' }}
                                    @endif
                                </span>
                            </div>
                            <div class="membership-field membership-field--full">
                                <span class="label">Order Created</span>
                                <span class="value">{{ optional($order->order_date)->format('M d, Y h:i A') ?? 'N/A' }}</span>
                            </div>
                        </div>

                        @if(in_array($membership?->membership_status, ['verification_pending', 'gift_pending_claim'], true))
                            <div class="membership-notice">
                                <strong>Please check your email to activate or claim your membership.</strong>
                            </div>
                        @endif

                        <div class="membership-actions">
                            @if($membership?->membership_status === 'active')
                                <a href="{{ route('membership.index') }}" class="btn-primary btn-primary--membership">View Membership Benefits</a>
                            @else
                                <a href="mailto:membership@metmuseum.org?subject=Resend%20Membership%20Activation%20Email&body=Please%20resend%20my%20membership%20activation%20email%20for%20order%20{{ urlencode($order->order_code) }}" class="btn-secondary btn-secondary--membership">Resend Activation Email</a>
                            @endif
                        </div>
                    </div>
                @else
                    <h3>Tickets</h3>
                    <div class="ticket-list">
                        @forelse($order->tickets as $ticket)
                            <div class="ticket-card">
                                <div class="ticket-info">
                                    <h4>{{ $ticket->ticketAvailability->ticketType->name ?? 'General Admission' }}</h4>
                                    <p class="location">{{ $ticket->ticketAvailability->visitSchedule->location->name ?? 'OC Museum' }}</p>
                                    <p class="date">Visit Date: {{ optional($ticket->ticketAvailability->visitSchedule->visit_date)->format('M d, Y') ?? 'N/A' }}</p>
                                    <p class="status">Status: <strong class="status-badge {{ strtolower($ticket->status) }}">{{ ucfirst($ticket->status) }}</strong></p>
                                </div>
                                <div class="ticket-qr">
                                    <div class="qr-placeholder">
                                        <div style="margin-bottom: 10px; display: flex; justify-content: center;">
                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->generate($ticket->qr_code) !!}
                                        </div>
                                        {{ $ticket->qr_code }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-tickets">
                                <p>No tickets generated yet.</p>
                                @if(($order->payment?->payment_status ?? 'Pending') === 'Pending')
                                    <small>Tickets will appear here after successful payment.</small>
                                @endif
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

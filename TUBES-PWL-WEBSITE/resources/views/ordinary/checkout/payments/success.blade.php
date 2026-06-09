@extends('layouts.app')

@section('content')
@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/checkout/payments/success.css')
@endpush
<div class="booking-page">
    <div class="booking-header">
        <h1 class="booking-title">{{ $order->payment?->payment_method === 'Membership' ? 'Membership Confirmed' : 'Booking Confirmed' }}</h1>
        <p class="booking-subtitle">
            @if($order->payment?->payment_method === 'Membership')
                Your membership payment has been received.
            @else
                Your order has been created successfully. Please keep your QR codes safe for check-in.
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="alert-banner success">{{ session('success') }}</div>
    @endif

    <div class="success-layout">
        <div class="booking-card">
            <div class="booking-card-title">Order information</div>
            <div class="summary-list">
                <div class="summary-row">
                    <div class="summary-title-line">Order code</div>
                    <div class="summary-subline">{{ $order->order_code }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-title-line">Payment status</div>
                    <div class="summary-subline">
                        <span class="status-badge {{ strtolower($order->payment?->payment_status ?? 'pending') }}">
                            {{ $order->payment?->payment_status ?? 'Pending' }}
                        </span>
                    </div>
                </div>
                <div class="summary-row">
                    <div class="summary-title-line">Order date</div>
                    <div class="summary-subline">{{ optional($order->order_date)->format('F j, Y H:i') }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-title-line">Paid at</div>
                    <div class="summary-subline">{{ optional($order->payment?->paid_at)->format('F j, Y H:i') }}</div>
                </div>
            </div>
        </div>

        @if($order->payment?->payment_method === 'Membership')
            <div class="booking-card">
                <div class="booking-card-title">Membership information</div>
                <div class="summary-list">
                    <div class="summary-row">
                        <div class="summary-title-line">Level</div>
                        <div class="summary-subline">Membership</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-title-line">Type</div>
                        <div class="summary-subline">Standard membership</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-title-line">Status</div>
                        <div class="summary-subline">Paid</div>
                    </div>
                </div>
            </div>
        @else
            <div class="success-grid">
                @foreach($order->tickets as $ticket)
                    <div class="success-ticket">
                        <div class="booking-card-title">{{ $ticket->ticketAvailability->ticketType->name ?? 'Ticket' }}</div>
                        <div class="booking-card-meta">{{ $ticket->ticketAvailability->visitSchedule->location->name ?? '-' }} · {{ optional($ticket->ticketAvailability->visitSchedule->visit_date)->format('F j, Y') }}</div>
                        <div class="booking-card-meta">Status: <span class="status-badge {{ strtolower($ticket->status) }}">{{ ucfirst($ticket->status) }}</span></div>
                        <div class="qr-box">
                            <div style="margin-bottom: 15px; display: flex; justify-content: center;">
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->generate($ticket->qr_code) !!}
                            </div>
                            {{ $ticket->qr_code }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="booking-actions">
            @if($order->payment?->payment_method === 'Membership')
                <a href="{{ route('membership.index') }}" class="booking-btn">Explore memberships</a>
                <a href="{{ route('order.show') }}" class="booking-btn-outline">View My Orders</a>
            @else
                <a href="{{ route('ticket.admission') }}" class="booking-btn">Book another ticket</a>
                <a href="{{ route('order.show') }}" class="booking-btn-outline">View My Orders</a>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Replace the current history state to clear out the payment page from the back stack
    history.replaceState(null, null, window.location.href);
    // Push a new state so there is a "forward" history to pop
    history.pushState(null, null, window.location.href);

    // Intercept back-button navigation
    window.onpopstate = function () {
        window.location.replace("{{ route('order.show') }}");
    };
</script>
@endpush
@endsection

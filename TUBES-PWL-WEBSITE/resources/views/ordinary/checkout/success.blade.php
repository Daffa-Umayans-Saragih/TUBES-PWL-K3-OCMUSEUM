@extends('layouts.app')

@section('content')
@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/checkout/success.css')
@endpush
<div class="booking-page">
    <div class="booking-header">
        <h1 class="booking-title">Booking Confirmed</h1>
        <p class="booking-subtitle">Your order has been created successfully. Please keep your QR codes safe for check-in.</p>
    </div>

    <div class="booking-stepper" aria-label="Booking steps">
        <div class="booking-step">Step 1: Select Date</div>
        <div class="booking-step">Step 2: Select Tickets</div>
        <div class="booking-step">Step 3: Add to Cart</div>
        <div class="booking-step">Step 4: View Cart</div>
        <div class="booking-step">Step 5: Checkout</div>
        <div class="booking-step is-active">Step 6: Confirmation</div>
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
                    <div class="summary-subline"><span class="status-badge {{ strtolower($order->payment_status) }}">{{ ucfirst($order->payment_status) }}</span></div>
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

        <div class="booking-actions">
            <a href="{{ route('ticket.index') }}" class="booking-btn">Book another ticket</a>
            <a href="{{ route('ticket.cart') }}" class="booking-btn-outline">View cart</a>
        </div>
    </div>
</div>
@endsection

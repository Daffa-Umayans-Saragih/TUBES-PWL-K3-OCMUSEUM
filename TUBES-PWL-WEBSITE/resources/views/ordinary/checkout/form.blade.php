@extends('layouts.app')

@section('content')
@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/checkout/form.css')
@endpush
<div class="booking-page">
    <div class="booking-header">
        <h1 class="booking-title">Checkout</h1>
        <p class="booking-subtitle">Complete your booking details to confirm your order.</p>
    </div>

    <div class="booking-stepper" aria-label="Booking steps">
        <div class="booking-step">Step 1: Select Date</div>
        <div class="booking-step">Step 2: Select Tickets</div>
        <div class="booking-step">Step 3: Add to Cart</div>
        <div class="booking-step">Step 4: View Cart</div>
        <div class="booking-step is-active">Step 5: Checkout</div>
        <div class="booking-step">Step 6: Confirmation</div>
    </div>

    @if(session('error'))
        <div class="alert-banner error">{{ session('error') }}</div>
    @endif

    <div class="booking-grid two-col">
        <form action="{{ route('ticket.checkout.process') }}" method="POST" class="booking-card checkout-form">
            @csrf

            <div class="booking-card-title">Visitor details</div>

            <div>
                <label for="name" class="field-label">Name</label>
                <input type="text" id="name" name="name" class="field-input" value="{{ old('name', $customer['name'] ?? '') }}" required>
                @error('name')
                    <div class="field-error-message">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="email" class="field-label">Email</label>
                <input type="email" id="email" name="email" class="field-input" value="{{ old('email', $customer['email'] ?? '') }}" required>
                @error('email')
                    <div class="field-error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="booking-btn">Confirm booking</button>
        </form>

        <aside class="booking-card booking-summary">
            <div class="booking-card-title">Cart summary</div>

            <div class="summary-list">
                @foreach($cartItems as $item)
                    <div class="summary-row">
                        <div class="summary-title-line">
                            <span>{{ $item->ticketAvailability->ticketType->name ?? 'Ticket' }}</span>
                            <span>${{ number_format(((float) ($item->ticketAvailability->ticketType->base_price ?? 0)) * $item->quantity, 2) }}</span>
                        </div>
                        <div class="summary-subline">{{ $item->ticketAvailability->visitSchedule->location->name ?? '-' }} · {{ optional($item->ticketAvailability->visitSchedule->visit_date)->format('F j, Y') }}</div>
                        <div class="summary-subline">Quantity: {{ $item->quantity }}</div>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
</div>
@endsection

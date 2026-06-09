@extends('layouts.app')

@section('content')
@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/checkout/payments/payments.css')
@endpush

<div class="payments-container">
    <header class="payments-header">
        <h1 class="payments-title">Payment and Order Confirmation</h1>
        <p class="payments-subtitle">Review your order and complete the transaction securely.</p>
    </header>

    @if(session('success'))
        <div class="alert-banner success" style="margin-bottom: 20px; padding: 15px; background: #e8f5e9; color: #2e7d32; border-radius: 8px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-banner error" style="margin-bottom: 20px; padding: 15px; background: #ffebee; color: #c62828; border-radius: 8px;">{{ session('error') }}</div>
    @endif

    @if(!empty($errorMessage))
        <div class="alert-banner error" style="margin-bottom: 20px; padding: 15px; background: #ffebee; color: #c62828; border-radius: 8px; font-weight: bold; text-align: center; font-size: 1.1rem;">
            ⚠️ {{ $errorMessage }}
        </div>
    @endif

    @if(!empty($successMessage))
        <div class="alert-banner success" style="margin-bottom: 20px; padding: 15px; background: #e8f5e9; color: #2e7d32; border-radius: 8px; font-weight: bold; text-align: center; font-size: 1.1rem;">
            ✓ {{ $successMessage }}
        </div>
    @endif

    @if(!empty($isExpired) && empty($errorMessage))
        <div class="alert-banner error" style="margin-bottom: 20px; padding: 15px; background: #ffebee; color: #c62828; border-radius: 8px;">
            Cart expired
        </div>
        <script>
            setTimeout(function () {
                window.location.href = "{{ route('home') }}";
            }, 1500);
        </script>
    @endif

    @if($order->payment && $order->payment->payment_status === 'Pending' && empty($isExpired) && empty($errorMessage))
    <form action="{{ route('ticket.checkout.pay', $order->order_id) }}" method="POST">
        @csrf
    @else
    <div>
    @endif

    <div class="payments-layout">
        <div class="payments-main">

            @if(!auth()->check() && $order->payment && $order->payment->payment_status === 'Pending' && empty($isExpired))
            <div class="payments-card card-section" style="margin-bottom: 30px;">
                <h2 class="section-title">Billing Address</h2>
                <div class="billing-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">First Name *</label>
                        <input type="text" name="first_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Last Name *</label>
                        <input type="text" name="last_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Address *</label>
                        <input type="text" name="address" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">City *</label>
                        <input type="text" name="city" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">State/Province *</label>
                        <input type="text" name="state" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Postal Code *</label>
                        <input type="text" name="postal_code" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:14px;">Country *</label>
                        <select name="country" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Select Country...</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="UK">United Kingdom</option>
                            <option value="AU">Australia</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif
            <!-- Billing Section -->
            <div class="payments-card card-section">
                <h2 class="section-title">Billing Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Customer Name</span>
                        <span class="info-value">
                            @if($order->user)
                                {{ $order->user->profile ? ($order->user->profile->first_name . ' ' . $order->user->profile->last_name) : $order->user->name }}
                            @else
                                {{ $order->guest->first_name . ' ' . $order->guest->last_name }}
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">{{ $order->user ? $order->user->email : $order->guest->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Code</span>
                        <span class="info-value" style="font-family: monospace;">{{ $order->order_code }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Status</span>
                        <div>
                            <span class="status-badge {{ $order->payment && $order->payment->payment_status === 'Paid' ? 'status-paid' : 'status-pending' }}">
                                {{ $order->payment?->payment_status ?? 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets / Membership state if Paid -->
            @if($order->payment && $order->payment->payment_status === 'Paid')
                @if($order->payment?->payment_method === 'Membership')
                    <div class="payments-card">
                        <h2 class="section-title">Membership Status</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Level</span>
                                <span class="info-value">Membership</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Type</span>
                                <span class="info-value">Standard</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value">Paid</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="payments-card">
                        <h2 class="section-title">Your Tickets</h2>
                        <div class="qr-grid">
                            @foreach($order->tickets as $ticket)
                                <div class="qr-card">
                                    <div class="item-name" style="font-weight: 700; color: #1a1a1a;">{{ $ticket->ticketAvailability->ticketType->name ?? 'Admission' }}</div>
                                    <div class="item-meta" style="font-size: 0.9rem; color: #666;">{{ $ticket->ticketAvailability->visitSchedule->location->name }}</div>
                                    <div class="item-meta" style="font-size: 0.9rem; color: #666;">{{ optional($ticket->ticketAvailability->visitSchedule->visit_date)->format('F j, Y') }}</div>
                                    <div class="item-meta" style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">Status: <span class="status-badge status-{{ strtolower($ticket->status) }}">{{ ucfirst($ticket->status) }}</span></div>
                                    <div class="qr-code-text">
                                        <div style="margin-bottom: 10px; display: flex; justify-content: center;">
                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($ticket->qr_code) !!}
                                        </div>
                                        {{ $ticket->qr_code }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Order Summary (Right Side) -->
        <aside class="summary-box">
            <div class="payments-card">
                <h2 class="section-title">Order Summary</h2>
                @if($order->payment && $order->payment->payment_status === 'Pending' && empty($isExpired) && !empty($paymentDeadlineAt))
                    <div class="alert-banner" style="margin-bottom: 15px; padding: 12px; background: #fff4e5; color: #8a4b00; border-radius: 8px;">
                        Complete payment in <strong id="payment-countdown">20:00</strong>
                    </div>
                @endif
                <div class="summary-list">
                    @if($order->payment?->payment_method === 'Membership')
                        <div class="summary-item">
                            <div class="item-info">
                                <span class="item-name">Membership</span>
                                <span class="item-meta">Standard membership</span>
                            </div>
                            <span class="item-price">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    @else
                        @foreach($order->orderDetails as $detail)
                            @php
                                $ticket = $detail->ticket;
                                $ticketName = $ticket ? ($ticket->ticketAvailability->ticketType->ticket_type_name ?? 'Admission') : 'Admission';
                                $quantity = $detail->quantity;
                                $price = $detail->unit_price > 0 ? $detail->unit_price : ($ticket ? $ticket->ticketAvailability->ticketType->base_price : 0);
                                $originalPrice = $detail->original_price > 0 ? $detail->original_price : $price;
                                $itemTotal = $price * $quantity;
                            @endphp
                            <div class="summary-item">
                                <div class="item-info">
                                    <span class="item-name">{{ $ticketName }}</span>
                                    <span class="item-meta">
                                        Qty: {{ $quantity }} × 
                                        @if($originalPrice > $price)
                                            <span style="text-decoration: line-through; color: #888; margin-right: 2px;">${{ number_format($originalPrice, 2) }}</span>
                                        @endif
                                        ${{ number_format($price, 2) }}
                                    </span>
                                </div>
                                <span class="item-price">${{ number_format($itemTotal, 2) }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">Total Amount</span>
                        <span class="total-amount">${{ number_format($order->total_amount, 2) }}</span>
                    </div>

                    @if($order->payment && $order->payment->payment_status === 'Pending' && empty($isExpired) && empty($errorMessage))
                        <button type="submit" class="btn-checkout w-full bg-blue-950 hover:bg-blue-900 text-white font-semibold">
                            Checkout ${{ number_format($order->total_amount, 2) }}
                        </button>
                    @else
                        <a href="{{ route('ticket.index') }}" class="btn-checkout" style="background: #333; text-align: center;">
                            Book More Tickets
                        </a>
                    @endif
                </div>
            </div>
        </aside>
    </div>

    @if($order->payment && $order->payment->payment_status === 'Pending' && empty($isExpired) && empty($errorMessage))
    </form>
    @else
    </div>
    @endif
</div>

@if($order->payment && $order->payment->payment_status === 'Pending' && empty($isExpired) && !empty($paymentDeadlineAt))
<script>
    (function () {
        var countdownEl = document.getElementById('payment-countdown');
        if (!countdownEl) {
            return;
        }

        var deadline = new Date("{{ $paymentDeadlineAt->toIso8601String() }}").getTime();

        function redirectExpired() {
            window.location.href = "{{ route('checkout.payments', $order->order_id) }}";
        }

        function render() {
            var now = Date.now();
            var remaining = Math.floor((deadline - now) / 1000);

            if (remaining <= 0) {
                countdownEl.textContent = '00:00';
                redirectExpired();
                return;
            }

            var minutes = Math.floor(remaining / 60);
            var seconds = remaining % 60;
            countdownEl.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        render();
        setInterval(render, 1000);
    })();
</script>
@endif
@endsection

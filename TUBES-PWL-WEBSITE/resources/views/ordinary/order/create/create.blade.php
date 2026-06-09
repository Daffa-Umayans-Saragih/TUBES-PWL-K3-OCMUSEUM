@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/order/create/create.css')
@endpush

@section('title', 'Checkout - OC Museum')

@section('content')
<div class="order-form-container">
    <div class="form-header">
        <h1>Complete Your Order</h1>
        <p>Select ticket quantity and visit date</p>
    </div>

    @if(session('error'))
        <div class="error-message">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error-message">
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('order.store') }}" method="POST">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Select Ticket</div>

            <div class="form-group">
                <label for="ticket_availability_id">Ticket Type *</label>
                <select name="ticket_availability_id" id="ticket_availability_id" required onchange="updateTotal()">
                    <option value="">-- Choose a ticket --</option>
                    @forelse($groupedTickets as $locationId => $locationData)
                        <optgroup label="{{ $locationData['location']->name }}">
                            @foreach($locationData['categories'] as $category => $categoryTickets)
                                @foreach($categoryTickets as $availability)
                                    <option
                                        value="{{ $availability->ticket_availability_id }}"
                                        data-price="{{ $availability->ticketType->base_price }}"
                                        {{ old('ticket_availability_id') == $availability->ticket_availability_id ? 'selected' : '' }}
                                    >
                                        {{ $availability->ticketType->name }} - ${{ number_format($availability->ticketType->base_price, 2) }}
                                    </option>
                                @endforeach
                            @endforeach
                        </optgroup>
                    @empty
                        <option value="">No tickets available</option>
                    @endforelse
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    min="1"
                    value="{{ old('quantity', 1) }}"
                    required
                    onchange="updateTotal()"
                >
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Visit Information</div>

            <div class="form-group">
                <label for="visit_date">Visit Date *</label>
                <input
                    type="date"
                    name="visit_date"
                    id="visit_date"
                    value="{{ old('visit_date') }}"
                    min="{{ today()->toDateString() }}"
                    required
                >
            </div>
        </div>

        <div class="ticket-summary">
            <div class="summary-item">
                <span>Ticket Price:</span>
                <span id="ticketPrice">$0.00</span>
            </div>
            <div class="summary-item">
                <span>Quantity:</span>
                <span id="quantityDisplay">1</span>
            </div>
            <div class="summary-item total">
                <span>Total Amount:</span>
                <span id="totalAmount">$0.00</span>
            </div>
        </div>

        <button type="submit" class="submit-button">Proceed to Payment</button>
    </form>

    <a href="/tickets" class="back-link">← Back to Tickets</a>
</div>

<script>
    function updateTotal() {
        const ticketSelect = document.getElementById('ticket_availability_id');
        const quantityInput = document.getElementById('quantity');
        const ticketOption = ticketSelect.options[ticketSelect.selectedIndex];

        const price = parseFloat(ticketOption.dataset.price) || 0;
        const quantity = parseInt(quantityInput.value) || 1;
        const total = price * quantity;

        document.getElementById('ticketPrice').textContent = '$' + price.toFixed(2);
        document.getElementById('quantityDisplay').textContent = quantity;
        document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
    }

    updateTotal();
</script>
@endsection

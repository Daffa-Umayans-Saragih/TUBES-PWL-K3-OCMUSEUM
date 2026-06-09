@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/checkout/cart/cart.css')
@endpush

@section('content')
<div class="cart-container">
    <div class="cart-progress">
        Select <span class="arrow">&gt;</span> Add <span class="arrow">&gt;</span> Review <span class="arrow">&gt;</span> Pay
    </div>

    <h1 class="cart-page-title">Cart</h1>

    @if(session('success'))
        <div class="alert-banner success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert-banner error">{{ session('error') }}</div>
    @endif

    @if(empty($cartGroups) || count($cartGroups) == 0)
        <div class="empty-cart-state">
            <p>There is no cart</p>
            <a href="{{ route('ticket.index') }}" class="btn-continue-shopping-empty">Continue Shopping</a>
        </div>
    @else
        <div class="cart-layout">
            <div class="cart-left">
                <h2 class="cart-section-title">Visit Information</h2>
                
                @foreach($cartGroups as $group)
                    <div class="cart-group-card" id="cart-group-{{ $group['group_id'] }}">
                        <div class="cart-group-header">
                            <span class="cart-group-title">Admission Tickets</span>
                            <div>
                                <a href="{{ route('cart.group.modify', $group['group_id']) }}" class="btn-remove" style="margin-right: 15px; text-decoration: none;">Modify</a>
                                <button type="button" class="btn-remove" onclick="removeCartGroup({{ $group['group_id'] }})">Remove</button>
                            </div>
                        </div>
                        
                        <div class="cart-group-body">
                            <div class="cart-col-left">
                                <div class="cart-visit-date">{{ $group['visit_date'] }}</div>
                            </div>
                            <div class="cart-col-middle">
                                <div class="cart-ticket-label">General Admission</div>
                                @foreach($group['items'] as $item)
                                    <div class="cart-ticket-type">
                                        {{ $item['ticket_type'] }} Admission @if($item['quantity'] > 1) &times;{{ $item['quantity'] }} @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="cart-col-right">
                                <div class="cart-ticket-label">&nbsp;</div>
                                @foreach($group['items'] as $item)
                                    <div class="cart-ticket-price">
                                        @if(isset($item['original_price']) && $item['original_price'] > $item['price'])
                                            <span style="text-decoration: line-through; color: #888; font-size: 0.9em; margin-right: 5px;">
                                                ${{ number_format($item['original_price'] * $item['quantity'], 2) }}
                                            </span>
                                        @endif
                                        ${{ number_format($item['item_total'], 2) }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="cart-right">
                <div class="cart-summary-box">
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>${{ number_format($globalSubtotal, 2) }}</span>
                    </div>
                    <div class="summary-line total-line">
                        <span>Amount Due</span>
                        <span>${{ number_format($globalSubtotal, 2) }}</span>
                    </div>
                    
                    @if($globalSubtotal > 0)
                        <form action="{{ route('ticket.checkout.process') }}" method="POST">
                            @csrf
                            <button 
    type="submit" 
    class="w-full bg-blue-950 hover:bg-blue-900 text-white font-semibold py-4 rounded transition mb-4"
>Next</button>
                        </form>
                    @else
                        <button disabled class="btn-next disabled">Next</button>
                    @endif
                    
                    <a href="{{ route('ticket.index') }}" class="btn-continue-shopping">Continue Shopping</a>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function removeCartGroup(groupId) {
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var token = csrf ? csrf.getAttribute('content') : '';
        
        fetch('/cart/group/' + groupId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error removing group.');
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('Network error.');
        });
    }
</script>
@endpush
@endsection

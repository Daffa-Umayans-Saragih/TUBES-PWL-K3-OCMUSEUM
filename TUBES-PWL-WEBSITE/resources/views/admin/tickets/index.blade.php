@extends('admin.layout.layout')

@section('admin-title')
    Cashier Ticket Point of Sale
@endsection

@section('admin-content')
<div class="tickets-section">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #333;">Cashier Ticket Sales (POS)</h1>
        <p class="page-subtitle" style="font-size: 0.95rem; color: #666; margin: 0;">Point-of-sale interface for onsite direct ticket purchases</p>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div style="background-color: #e6f4ea; color: #137333; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #c3e6cb; font-weight: 500;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background-color: #fce8e6; color: #c5221f; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #fadbd8; font-weight: 500;">
        {{ session('error') }}
    </div>
    @endif

    <div class="pos-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
        
        <!-- Left Side: Selection & Available Types -->
        <div class="pos-left-pane" style="display: flex; flex-direction: column; gap: 2rem;">
            
            <!-- SECTION 1: Choose Visit Schedule -->
            <section class="management-section" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e0e0e0;">
                <h2 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin: 0 0 1.25rem 0; color: #333;">1. Select Visit Schedule & Location</h2>
                
                <form method="GET" action="{{ route('admin.tickets.index') }}" id="posScheduleForm">
                    <div style="position: relative;">
                        <select name="schedule_id" onchange="document.getElementById('posScheduleForm').submit()" class="form-input" style="width: 100%; padding: 0.85rem 1rem; font-size: 1rem; font-weight: 600; border-color: #2196F3; border-radius: 6px; cursor: pointer; background-color: #f9f9f9;">
                            @forelse($schedules ?? [] as $sch)
                            <option value="{{ $sch->visit_schedule_id }}" @if($selectedSchedule && $selectedSchedule->visit_schedule_id == $sch->visit_schedule_id) selected @endif>
                                📅 {{ $sch->visit_date->format('M d, Y') }} — 🏛 {{ $sch->location->location_name ?? 'Unknown Location' }}
                            </option>
                            @empty
                            <option disabled>No schedules configured in database</option>
                            @endforelse
                        </select>
                    </div>
                </form>
            </section>

            <!-- SECTION 2: Available Ticket Types -->
            <section class="management-section" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e0e0e0;">
                <h2 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin: 0 0 1.25rem 0; color: #333;">2. Select Ticket Quantities</h2>
                
                @if($selectedSchedule)
                @php
                    $companionAvail = collect($availabilities ?? [])->first(fn($a) => strtolower($a['name']) === 'companion');
                    $companionAvailabilityId = $companionAvail ? $companionAvail['ticket_availability_id'] : '';
                    $companionRemaining = $companionAvail ? $companionAvail['remaining'] : 0;
                @endphp
                <div class="ticket-types-pos-list" style="display: flex; flex-direction: column; gap: 1rem;">
                    @forelse($availabilities ?? [] as $avail)
                        @php
                            if (strtolower($avail['name']) === 'companion') continue;
                            $isSoldOut = ($avail['remaining'] <= 0);
                            $isDisabilities = (strtolower($avail['name']) === 'disabilities');
                        @endphp
                        <div class="ticket-pos-card" style="display: flex; flex-direction: column; padding: 1.25rem; border: 1px solid #e0e0e0; border-radius: 8px; transition: all 0.3s ease; background-color: {{ $isSoldOut ? '#fafafa' : 'white' }}; opacity: {{ $isSoldOut ? '0.7' : '1' }}; gap: 1rem;">
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <!-- Left: Info -->
                                <div class="ticket-info-block">
                                    <h3 style="margin: 0 0 0.25rem 0; font-size: 1.05rem; font-weight: 700; color: #333;">
                                        {{ $avail['name'] }}
                                        @if($isDisabilities)
                                        <span style="font-size: 0.8rem; background: #e3f2fd; color: #0d47a1; padding: 0.2rem 0.5rem; border-radius: 4px; margin-left: 0.5rem; font-weight: 600;">+1 Free Companion Available</span>
                                        @endif
                                    </h3>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem;">
                                        <span style="color: #2196F3; font-weight: 700; font-size: 1.1rem;">${{ number_format($avail['price'], 2) }}</span>
                                        <span style="color: #666;">•</span>
                                        @if($avail['remaining'] > 20)
                                        <span style="color: #137333; font-weight: 600;">✓ {{ $avail['remaining'] }} remaining</span>
                                        @elseif($avail['remaining'] >= 1)
                                        <span style="color: #b06000; font-weight: 600;">⚠ {{ $avail['remaining'] }} low stock</span>
                                        @else
                                        <span style="color: #c5221f; font-weight: 700;">✗ Sold Out</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right: Counter -->
                                <div class="counter-block" style="display: flex; align-items: center; gap: 0.75rem;">
                                    @if($isSoldOut)
                                        <span style="color: #c5221f; font-weight: 700; font-size: 0.9rem; padding: 0.5rem 1rem; background: #fce8e6; border-radius: 6px;">SOLD OUT</span>
                                    @else
                                        <button type="button" class="qty-btn" onclick="adjustQty({{ $avail['ticket_availability_id'] }}, -1, {{ $avail['remaining'] }})" style="width: 36px; height: 36px; border-radius: 6px; border: 1px solid #ddd; background: white; font-size: 1.25rem; font-weight: bold; cursor: pointer; transition: all 0.2s ease;">-</button>
                                        
                                        <input type="number" 
                                               id="qty_input_{{ $avail['ticket_availability_id'] }}" 
                                               name="tickets_qty[{{ $avail['ticket_availability_id'] }}]" 
                                               value="0" 
                                               min="0" 
                                               max="{{ $avail['remaining'] }}" 
                                               readonly
                                               data-price="{{ $avail['price'] }}"
                                               data-name="{{ $avail['name'] }}"
                                               data-availability-id="{{ $avail['ticket_availability_id'] }}"
                                               data-is-disabilities="{{ $isDisabilities ? 'true' : 'false' }}"
                                               style="width: 50px; text-align: center; font-size: 1.1rem; font-weight: bold; border: 1px solid #ddd; border-radius: 6px; padding: 0.4rem 0;">
                                               
                                        <button type="button" class="qty-btn" onclick="adjustQty({{ $avail['ticket_availability_id'] }}, 1, {{ $avail['remaining'] }})" style="width: 36px; height: 36px; border-radius: 6px; border: 1px solid #ddd; background: white; font-size: 1.25rem; font-weight: bold; cursor: pointer; transition: all 0.2s ease;">+</button>
                                    @endif
                                </div>
                            </div>

                            @if($isDisabilities && $companionAvailabilityId)
                            <div id="companionSelectionContainer" style="display: none; padding-top: 1rem; border-top: 1px dashed #eee; background-color: #fcfcfc;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; border-radius: 6px; background: #e3f2fd; border-left: 4px solid #1976D2;">
                                    <div>
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #0d47a1;">♿ Accessibility Companion Ticket (FREE)</span>
                                        <div style="font-size: 0.8rem; color: #333;">Quantity must be &le; Disabilities quantity</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <select id="companion_select_dropdown" 
                                                data-price="0"
                                                data-name="Companion"
                                                data-availability-id="{{ $companionAvailabilityId }}"
                                                data-companion-remaining="{{ $companionRemaining }}"
                                                onchange="recalculatePOSSummary()"
                                                style="padding: 0.35rem 0.5rem; font-weight: bold; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; width: 70px; background: white; cursor: pointer;">
                                            <option value="0">0</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    @empty
                        <p style="color: #666; font-style: italic; text-align: center; padding: 2rem;">No ticket types configured for this schedule</p>
                    @endforelse
                </div>
                @else
                <p style="color: #666; font-style: italic; text-align: center; padding: 2rem;">Please select a visit schedule above first</p>
                @endif
            </section>
        </div>

        <!-- Right Side: Order Summary & Checkout -->
        <div class="pos-right-pane">
            <section class="management-section" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; position: sticky; top: 2rem;">
                <h2 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin: 0 0 1.25rem 0; color: #333; border-bottom: 2px solid #f5f5f5; padding-bottom: 0.75rem;">Order Summary</h2>
                
                <form action="{{ route('admin.tickets.checkout') }}" method="POST" id="posCheckoutForm">
                    @csrf
                    @if($selectedSchedule)
                        <input type="hidden" name="visit_schedule_id" value="{{ $selectedSchedule->visit_schedule_id }}">
                    @endif

                    <!-- Selected Location Card -->
                    @if($selectedSchedule)
                    <div style="background: #f9f9f9; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #eee;">
                        <div style="font-weight: bold; color: #333; margin-bottom: 0.25rem;">Onsite Booking For:</div>
                        <div style="color: #2196F3; font-weight: 600;">{{ $selectedSchedule->visit_date->format('l, M d, Y') }}</div>
                        <div style="color: #666; font-size: 0.85rem; margin-top: 0.25rem;">🏛 {{ $selectedSchedule->location->location_name ?? '' }}</div>
                    </div>
                    @endif

                    <!-- Dynamic List of Purchased Items -->
                    <div id="summaryItemsList" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem; min-height: 80px;">
                        <p id="emptySummaryPlaceholder" style="color: #999; font-style: italic; text-align: center; margin: 2rem 0;">No tickets selected yet.</p>
                    </div>

                    <!-- Companion Banner Promo -->
                    <div id="companionBonusPromo" style="display: none; background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 0.85rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <strong style="color: #1b5e20; display: block; margin-bottom: 0.15rem;">♿ Accessibility Companion Rule</strong>
                        <span style="color: #333;">Companion Quantity is strictly restricted to &le; Disabilities quantity.</span>
                    </div>

                    <!-- Totals -->
                    <div style="border-top: 2px dashed #eee; padding-top: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 1.25rem; color: #333;">
                            <span>Total Due:</span>
                            <span id="posTotalDueDisplay" style="color: #2196F3;">$0.00</span>
                        </div>
                    </div>

                    <!-- Hidden inputs dynamically injected by JS before submit -->
                    <div id="hiddenInputsContainer"></div>

                    <!-- Submit Buttons -->
                    <div style="display: flex; gap: 0.75rem;">
                        <button type="button" class="btn btn-secondary" onclick="resetPosQuantities()" style="flex: 1; padding: 0.85rem;">Clear</button>
                        <button type="submit" class="btn btn-primary" id="posSubmitBtn" disabled style="flex: 2; padding: 0.85rem; font-size: 1rem; background-color: #ccc; cursor: not-allowed;">Complete Sale</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

<script>
function adjustQty(availabilityId, delta, remainingStock) {
    const input = document.getElementById('qty_input_' + availabilityId);
    if (!input) return;

    let currentVal = parseInt(input.value) || 0;
    let newVal = currentVal + delta;

    if (newVal < 0) newVal = 0;
    if (newVal > remainingStock) newVal = remainingStock;

    input.value = newVal;

    // Recalculate summary
    recalculatePOSSummary();
}

function recalculatePOSSummary() {
    const listContainer = document.getElementById('summaryItemsList');
    const placeholder = document.getElementById('emptySummaryPlaceholder');
    const hiddenInputs = document.getElementById('hiddenInputsContainer');
    const companionPromo = document.getElementById('companionBonusPromo');
    const totalDisplay = document.getElementById('posTotalDueDisplay');
    const submitBtn = document.getElementById('posSubmitBtn');

    listContainer.innerHTML = '';
    hiddenInputs.innerHTML = '';

    let totalDue = 0.0;
    let totalTicketsSelected = 0;
    let disabilitiesQty = 0;
    let itemsAdded = 0;

    // Read all quantity inputs
    const inputs = document.querySelectorAll('input[id^="qty_input_"]');
    inputs.forEach(input => {
        const qty = parseInt(input.value) || 0;
        if (qty <= 0) return;

        const price = parseFloat(input.getAttribute('data-price')) || 0;
        const name = input.getAttribute('data-name');
        const availabilityId = input.getAttribute('data-availability-id');
        const isDisabilities = input.getAttribute('data-is-disabilities') === 'true';

        if (isDisabilities) {
            disabilitiesQty += qty;
        }

        totalDue += price * qty;
        totalTicketsSelected += qty;
        itemsAdded++;

        // Add to visible summary list
        const itemRow = document.createElement('div');
        itemRow.style.display = 'flex';
        itemRow.style.justifyContent = 'space-between';
        itemRow.style.fontSize = '0.95rem';
        itemRow.style.fontWeight = '500';
        itemRow.innerHTML = `
            <span style="color: #444;">${name} <strong style="color: #666;">× ${qty}</strong></span>
            <span style="font-weight: 700; color: #333;">$${(price * qty).toFixed(2)}</span>
        `;
        listContainer.appendChild(itemRow);

        // Append hidden inputs for secure cashier form post
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `tickets[${availabilityId}]`;
        hiddenInput.value = qty;
        hiddenInputs.appendChild(hiddenInput);
    });

    // Check Disabilities Companion rule
    const companionContainer = document.getElementById('companionSelectionContainer');
    const companionSelect = document.getElementById('companion_select_dropdown');

    if (disabilitiesQty > 0) {
        if (companionContainer && companionSelect) {
            companionContainer.style.display = 'block';
            companionPromo.style.display = 'block';
            
            const companionRemaining = parseInt(companionSelect.getAttribute('data-companion-remaining')) || 0;
            const maxCompanion = Math.min(disabilitiesQty, companionRemaining);
            const currentSelected = parseInt(companionSelect.value) || 0;

            // Re-populate options up to disabilitiesQty
            companionSelect.innerHTML = '';
            for (let i = 0; i <= maxCompanion; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.innerText = i;
                if (i === currentSelected || (i === maxCompanion && currentSelected > maxCompanion)) {
                    opt.selected = true;
                }
                companionSelect.appendChild(opt);
            }

            const selectedCompanionQty = parseInt(companionSelect.value) || 0;
            if (selectedCompanionQty > 0) {
                totalTicketsSelected += selectedCompanionQty;
                itemsAdded++;

                // Add to visible summary list
                const companionRow = document.createElement('div');
                companionRow.style.display = 'flex';
                companionRow.style.justifyContent = 'space-between';
                companionRow.style.fontSize = '0.95rem';
                companionRow.style.fontWeight = '500';
                companionRow.innerHTML = `
                    <span style="color: #137333; font-weight: 600;">♿ Accessibility Companion <strong style="color: #666;">× ${selectedCompanionQty}</strong></span>
                    <span style="font-weight: 700; color: #137333;">FREE</span>
                `;
                listContainer.appendChild(companionRow);

                // Append hidden inputs for secure cashier form post
                const companionAvailabilityId = companionSelect.getAttribute('data-availability-id');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `tickets[${companionAvailabilityId}]`;
                hiddenInput.value = selectedCompanionQty;
                hiddenInputs.appendChild(hiddenInput);
            }
        }
    } else {
        if (companionContainer && companionSelect) {
            companionContainer.style.display = 'none';
            companionPromo.style.display = 'none';
            companionSelect.innerHTML = '<option value="0">0</option>';
            companionSelect.value = '0';
        }
    }

    // Toggle Empty Placeholder & Checkout button availability
    if (itemsAdded === 0) {
        listContainer.appendChild(placeholder);
        submitBtn.disabled = true;
        submitBtn.style.backgroundColor = '#ccc';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        submitBtn.disabled = false;
        submitBtn.style.backgroundColor = '#2196F3';
        submitBtn.style.cursor = 'pointer';
    }

    // Update due display
    totalDisplay.innerText = '$' + totalDue.toFixed(2);
}

function resetPosQuantities() {
    const inputs = document.querySelectorAll('input[id^="qty_input_"]');
    inputs.forEach(input => {
        input.value = 0;
    });
    const companionSelect = document.getElementById('companion_select_dropdown');
    if (companionSelect) {
        companionSelect.value = '0';
    }
    recalculatePOSSummary();
}
</script>

<style>
.qty-btn:hover {
    border-color: #2196F3 !important;
    color: #2196F3 !important;
}

.ticket-pos-card:hover {
    border-color: #2196F3 !important;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.08);
}
</style>
@endsection

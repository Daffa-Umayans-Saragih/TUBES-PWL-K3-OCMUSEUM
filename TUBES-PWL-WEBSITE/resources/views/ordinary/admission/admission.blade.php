@extends('layouts.app')

@push('styles')
@vite('resources/css/ordinary/admission/admission.css')
@endpush

@section('content')
<div class="admission-page">
<div class="admission-shell">

    {{-- Breadcrumb --}}
    <nav class="adm-breadcrumb">
        <a href="#">Select</a><span class="sep">&gt;</span>
        <span class="current">Add</span><span class="sep">&gt;</span>
        <span class="current">Review</span><span class="sep">&gt;</span>
        <span class="current">Pay</span>
    </nav>

    <div class="admission-layout">
        <div class="admission-main">

            @php
                $locationIds = collect();
                if (isset($schedules) && is_iterable($schedules)) {
                    $locationIds = $schedules->pluck('location_id')->unique()->values();
                }
                $locIdA = $locationIds->get(0) ?? 1;
                $locIdB = $locationIds->get(1) ?? 2;

                $groupedSchedules = isset($schedules) ? $schedules->groupBy('location_id') : collect();
            @endphp

            {{-- ============================================ --}}
            {{-- IMAGE 1(1): INTRO HEADER --}}
            {{-- ============================================ --}}
            <section class="section-intro">
                @if(session()->has('modify_cart_group_id'))
                    <div class="alert-banner" style="background-color: #f8f8f8; border-left: 4px solid #c3002f; padding: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #c3002f;">Modifying Booking:</strong> Please reselect your tickets. Your previous selection will be automatically removed from your cart when you confirm this new booking.
                        </div>
                        <a href="{{ route('cart.modify.cancel') }}" class="btn-remove" style="text-decoration: none; padding: 8px 15px; background: #eee; color: #333; font-size: 0.9em; font-weight: bold;">Cancel Modify</a>
                    </div>
                @endif
                <h1 class="intro-title">Admission Tickets</h1>
                <div class="intro-columns">
                    <div class="intro-left">
                        <h3>New York State residents and NY, NJ, and CT students:</h3>
                        <ul>
                            <li>The amount you pay for admission is up to you, but you must pay something.</li>
                            <li>To buy pay-what-you-wish tickets online, you must have a New York State billing address.</li>
                            <li>New Jersey and Connecticut students can only buy pay-what-you-wish tickets in person with valid student ID.</li>
                        </ul>
                        <p>Members: Your card is your ticket. Once inside, proceed to a gallery entrance and present your card.</p>
                    </div>
                    <div class="intro-right">
                        <h3>General Admission Tickets</h3>
                        <p>$30 for adults, $22 for seniors, $17 for students</p>
                        <p>Free for Members, Patrons, and children 12 and under.</p>
                        <p>$22 for visitors with a disability, free for a care partner of a visitor with a disability (in person only).</p>
                        <p>All tickets include exhibitions and same-day entry to both OC locations for the date on your ticket.</p>
                    </div>
                </div>
               <div class="bg-blue-950 text-white p-8 flex items-center justify-between rounded">
    <div class="member-banner-text">
        <h3 class="text-3xl font-bold">Become a Member</h3>
        <p class="mt-2 text-lg">
            Enjoy <strong>unlimited free admission</strong> for you and your guest(s) on every visit.
        </p>
    </div>

    <button class="bg-white text-blue-900 font-semibold px-10 py-4 rounded">
        Join today
    </button>
</div>
            </section>

            {{-- ============================================ --}}
            {{-- IMAGE 1(2): LOCATION SELECTION --}}
            {{-- ============================================ --}}
            <section class="section-location">
                <h2 class="section-heading">1. Choose a location</h2>
                <p class="section-subtext">Choose one of two  locations.</p>
                <div class="location-grid">
                    <article class="location-card" data-location-id="{{ $locIdA }}">
                        <img src="{{ asset('images/visit-guide.jpg') }}" alt="The OC Fifth Avenue" class="location-image">
                        <div class="location-content">
                            <h3 class="location-name">The OC Fifth Avenue</h3>
                            <p class="location-hours">Sunday–Tuesday and Thursday: 10 am–5 pm</p>
                            <p class="location-hours">Friday and Saturday: 10 am–9 pm</p>
                            <p class="location-hours">Closed Wednesday</p>
                        </div>
                    </article>
                    <article class="location-card" data-location-id="{{ $locIdB }}">
                        <img src="{{ asset('images/the met cloisters.avif') }}" alt="The OC Cloisters" class="location-image">
                        <div class="location-content">
                            <h3 class="location-name">The OC Cloisters</h3>
                            <p class="location-hours">Thursday–Tuesday: 10 am–5 pm</p>
                            <p class="location-hours">Closed Wednesday</p>
                        </div>
                    </article>
                </div>
            </section>

            {{-- ============================================ --}}
            {{-- IMAGE 2: DATE SELECTION --}}
            {{-- ============================================ --}}
            <section class="section-date">
                <h2 class="section-heading">2. Select a date</h2>
                <div id="date-placeholder" class="date-placeholder">
                    <a href="javascript:void(0)" id="scroll-to-location">Select a location</a> to see available dates.
                </div>
                <div id="date-content" class="step-hidden">
                    <p class="section-subtext">Please select an available date for your visit. Please note online ticket sales end one hour before closing.</p>
                    <div class="date-pills" id="date-pills-container"></div>
                    <div class="calendar-shell">
                        <div class="calendar-input-row">
                            <input type="text" id="calendar-date-display" readonly class="calendar-input">
                        </div>
                        <div class="calendar-grid" id="calendar-grid"></div>
                        <div class="calendar-legend">
                            <span class="legend-dot"></span> Unavailable
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================ --}}
            {{-- IMAGE 3: TICKET SELECTION --}}
            {{-- ============================================ --}}
            <section class="section-tickets step-hidden" id="section-tickets">
                <h2 class="section-heading">3. Tickets</h2>
                <p class="ticket-context" id="ticket-context-text"></p>
                <div class="ticket-notice">
                    <p>Unlock pay what you wish pricing by entering your valid New York State zip code below.</p>
                    <p>You can adjust your total payment amount after adding the tickets to your cart.</p>
                    <p>Your zip code will be validated at checkout.</p>
                    <p>If you are eligible for pay-what-you-wish tickets but do not have a NY zip code, you can purchase tickets in person with a valid ID.</p>
                    <label class="field-label" for="zip_code">Enter zip code here</label>
                    <input id="zip_code" type="text" class="zip-input" placeholder="">
                </div>
                <p class="ticket-info-text">Up to nine general admission tickets can be purchased <a href="#">here</a>. For information about bringing a group or scheduling a group tour, see <a href="#">Group Visits</a>.</p>
                <div class="ticket-list" id="ticket-list">
                    @php
                        $firstSchedule = $schedules->first();
                        $ticketAvailabilities = $firstSchedule ? $firstSchedule->ticketAvailabilities : [];
                    @endphp
                    @foreach($ticketAvailabilities as $av)
                        @php
                            $origPrice = (float) $av->ticketType->base_price;
                            $effPrice = (float) $av->ticketType->getEffectivePrice(Auth::user());
                            $hasDiscount = $origPrice > $effPrice;
                        @endphp
                        <div class="ticket-row step-hidden"
                             data-id="{{ $av->ticket_availability_id }}"
                             data-ticket-type-id="{{ $av->ticketType->ticket_type_id }}"
                             data-type="{{ strtolower($av->ticketType->name) }}"
                             data-price="{{ $effPrice }}">
                            <div class="ticket-info">
                                <h3>{{ $av->ticketType->ticket_type_name }} Admission</h3>

                                @if(strtolower($av->ticketType->ticket_type_name) === 'child')
                                    <p>12 and under</p>
                                @elseif(strtolower($av->ticketType->ticket_type_name) === 'student')
                                    <p>with valid ID</p>
                                @elseif(strtolower($av->ticketType->ticket_type_name) === 'senior')
                                    <p>65 and over</p>
                                @endif
                            </div>
                            <div class="ticket-price">
                                @if($hasDiscount)
                                    <span style="text-decoration: line-through; color: #888; font-size: 0.9em; margin-right: 5px;">
                                        ${{ number_format($origPrice, 2) }}
                                    </span>
                                    <span>
                                        ${{ number_format($effPrice, 2) }}
                                        <span style="font-size: 0.75em; color: #c3002f; font-weight: bold; display: block; margin-top: 2px;">Member Price</span>
                                    </span>
                                @else
                                    ${{ number_format($origPrice, 2) }}
                                @endif
                            </div>
                            <div class="ticket-control">
                                <button type="button" class="btn-minus">—</button>
                                <span class="qty">0</span>
                                <button type="button" class="btn-plus">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Need Help --}}
            <section class="section-help">
                <h3>Need Help?</h3>
                <p>We're available Monday–Friday by phone and email, 9am–5pm EST. We'll respond to your message as soon as possible during business hours.</p>
            </section>

        </div>

        {{-- SIDEBAR --}}
        <aside class="admission-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <span>Subtotal</span>
                    <span class="sidebar-subtotal" id="sidebar-subtotal">$0.00</span>
                </div>
                <button type="button" class="btn-next" id="btn-next-step" disabled>Next</button>
            </div>
        </aside>
    </div>

</div>
</div>

<script>
console.log("INIT SCRIPT");

document.addEventListener('DOMContentLoaded', function () {
    // ── DATA ──
    var schedulesData = @json($schedules);
    var state = { locationId: null, scheduleId: null, selectedDate: null, calendarRefDate: new Date() };

    // ── DOM REFS ──
    var datePlaceholder = document.getElementById('date-placeholder');
    var dateContent = document.getElementById('date-content');
    var datePillsContainer = document.getElementById('date-pills-container');
    var sectionTickets = document.getElementById('section-tickets');
    var calendarDisplay = document.getElementById('calendar-date-display');
    var calendarGrid = document.getElementById('calendar-grid');
    var ticketContext = document.getElementById('ticket-context-text');
    var btnNext = document.getElementById('btn-next-step');

    // ── HELPERS ──
    function resetTickets() {
        var allRows = document.querySelectorAll('.ticket-row');
        allRows.forEach(function(r) {
            r.classList.add('step-hidden');
            var q = r.querySelector('.qty');
            if (q) q.textContent = '0';
        });
        var sidebarSubtotal = document.getElementById('sidebar-subtotal');
        if (sidebarSubtotal) sidebarSubtotal.textContent = '$0.00';
        if (btnNext) btnNext.disabled = true;
    }

    function resetDate() {
        state.scheduleId = null;
        state.selectedDate = null;
        state.calendarRefDate = new Date();
        if (datePillsContainer) {
            datePillsContainer.querySelectorAll('.date-pill').forEach(function(p) {
                p.classList.remove('active');
            });
            var customPill = document.querySelector('.custom-date-pill');
            if (customPill) {
                customPill.querySelector('.pill-label').textContent = 'Another date';
                customPill.querySelector('.pill-date').textContent = 'Select a date';
            }
        }
    }

    function enforceChildRule() {
        var allRows = document.querySelectorAll('.ticket-row');
        var hasAdult = false;
        var childQtyEl = null;

        allRows.forEach(function(r) {
            if (r.classList.contains('step-hidden')) return;
            var tType = r.dataset.type;
            var qEl = r.querySelector('.qty');
            if (!qEl) return;
            
            var qty = parseInt(qEl.innerText || qEl.textContent || '0', 10);
            
            if (tType === 'adult' || tType === 'student' || tType === 'senior') {
                if (qty > 0) hasAdult = true;
            } else if (tType === 'child') {
                childQtyEl = qEl;
            }
        });

        if (childQtyEl && !hasAdult) {
            childQtyEl.innerText = '0';
            childQtyEl.textContent = '0';
        }
    }

    function updateSubtotal() {
        var total = 0;
        var allRows = document.querySelectorAll('.ticket-row');
        allRows.forEach(function(r) {
            if (r.classList.contains('step-hidden')) return;
            var q = parseInt(r.querySelector('.qty').textContent || '0', 10);
            var p = parseFloat(r.dataset.price || '0');
            total += q * p;
        });
        var sidebarSubtotal = document.getElementById('sidebar-subtotal');
        if (sidebarSubtotal) sidebarSubtotal.textContent = '$' + total.toFixed(2);
        if (btnNext) btnNext.disabled = (total === 0);
    }

    function enforceChildRule() {
        var adultQty = 0, studentQty = 0, seniorQty = 0;
        var childRow = null;
        var allRows = document.querySelectorAll('.ticket-row');
        allRows.forEach(function(r) {
            if (r.classList.contains('step-hidden')) return;
            var q = parseInt(r.querySelector('.qty').textContent || '0', 10);
            if (r.dataset.type === 'adult') adultQty = q;
            if (r.dataset.type === 'student') studentQty = q;
            if (r.dataset.type === 'senior') seniorQty = q;
            if (r.dataset.type === 'child') childRow = r;
        });
        if (childRow && adultQty === 0 && studentQty === 0 && seniorQty === 0) {
            childRow.querySelector('.qty').textContent = '0';
        }
    }

    function normalizeDate(vd) {
        if (typeof vd === 'object' && vd !== null) vd = vd.date ? vd.date.substring(0,10) : String(vd);
        if (typeof vd === 'string' && vd.length > 10) vd = vd.substring(0,10);
        return vd || '';
    }

    function getLocationSchedules(locId) {
        return schedulesData.filter(function(s) {
            return Number(s.location_id) === Number(locId);
        });
    }

    function findScheduleByDate(dateStr) {
        if (!state.locationId) return null;
        var locSchedules = getLocationSchedules(state.locationId);
        for (var i = 0; i < locSchedules.length; i++) {
            if (normalizeDate(locSchedules[i].visit_date) === dateStr) return locSchedules[i];
        }
        return null;
    }

    function getLocationName(locId) {
        var card = document.querySelector('.location-card[data-location-id="' + locId + '"]');
        if (card) return card.querySelector('.location-name').textContent;
        return '';
    }

    function formatDateLong(dateStr) {
        var d = new Date(dateStr + 'T12:00:00');
        var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return days[d.getDay()] + ', ' + months[d.getMonth()] + ' ' + d.getDate();
    }

    function formatDateShort(dateStr) {
        var d = new Date(dateStr + 'T12:00:00');
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return days[d.getDay()] + ', ' + months[d.getMonth()] + ' ' + d.getDate();
    }

    function isToday(dateStr) {
        var today = new Date(); today.setHours(0,0,0,0);
        var d = new Date(dateStr + 'T12:00:00'); d.setHours(0,0,0,0);
        return d.getTime() === today.getTime();
    }

    function isTomorrow(dateStr) {
        var tmrw = new Date(); tmrw.setDate(tmrw.getDate() + 1); tmrw.setHours(0,0,0,0);
        var d = new Date(dateStr + 'T12:00:00'); d.setHours(0,0,0,0);
        return d.getTime() === tmrw.getTime();
    }

    // ── BUILD DATE PILLS ──
    function buildDatePills(locSchedules) {
        datePillsContainer.innerHTML = '';
        var shown = 0;
        for (var i = 0; i < locSchedules.length && shown < 2; i++) {
            var s = locSchedules[i];
            var vd = normalizeDate(s.visit_date);
            var label = isToday(vd) ? 'Today' : (isTomorrow(vd) ? 'Tomorrow' : '');
            if (!label && shown >= 2) continue;
            if (!label) label = formatDateShort(vd).split(',')[0];
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'date-pill';
            btn.dataset.scheduleId = s.visit_schedule_id;
            btn.dataset.locationId = s.location_id;
            btn.dataset.visitDate = vd;
            btn.innerHTML = '<span class="pill-label">' + label + '</span><span class="pill-date">' + formatDateShort(vd) + '</span>';
            datePillsContainer.appendChild(btn);
            shown++;
        }
        // "Another date / Select a date" pill
        var anotherBtn = document.createElement('button');
        anotherBtn.type = 'button';
        anotherBtn.className = 'date-pill custom-date-pill';
        anotherBtn.innerHTML = '<span class="pill-label">Another date</span><span class="pill-date">Select a date</span>';
        datePillsContainer.appendChild(anotherBtn);
        // NO event listeners attached here anymore!
    }

    // ── BUILD CALENDAR ──
    function buildCalendar() {
        if (!calendarGrid) return;
        calendarGrid.innerHTML = '';
        var ref = state.calendarRefDate;
        for (var m = 0; m < 2; m++) {
            var refDate = new Date(ref.getFullYear(), ref.getMonth() + m, 1);
            var year = refDate.getFullYear();
            var month = refDate.getMonth();
            var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            var dayNames = ['S','M','T','W','T','F','S'];

            var monthDiv = document.createElement('div');
            monthDiv.className = 'calendar-month';

            var header = document.createElement('div');
            header.className = 'calendar-month-header';
            if (m === 0) {
                header.innerHTML = '<span class="calendar-arrow prev-month" style="cursor:pointer;">‹</span><h3>' + monthNames[month] + ' ' + year + '</h3>';
            } else {
                header.innerHTML = '<h3>' + monthNames[month] + ' ' + year + '</h3><span class="calendar-arrow next-month" style="cursor:pointer;">›</span>';
            }
            monthDiv.appendChild(header);

            var daysRow = document.createElement('div');
            daysRow.className = 'calendar-days';
            dayNames.forEach(function(d) { var sp = document.createElement('span'); sp.textContent = d; daysRow.appendChild(sp); });
            monthDiv.appendChild(daysRow);

            var datesDiv = document.createElement('div');
            datesDiv.className = 'calendar-dates';
            var firstDay = new Date(year, month, 1).getDay();
            var daysInMonth = new Date(year, month + 1, 0).getDate();
            for (var e = 0; e < firstDay; e++) { datesDiv.appendChild(document.createElement('span')); }
            
            var today = new Date(); today.setHours(0,0,0,0);
            
            for (var d = 1; d <= daysInMonth; d++) {
                var sp = document.createElement('span');
                sp.textContent = d;
                var cellDate = new Date(year, month, d); cellDate.setHours(0,0,0,0);
                
                var yy = cellDate.getFullYear();
                var mm = String(cellDate.getMonth()+1).padStart(2,'0');
                var dd = String(cellDate.getDate()).padStart(2,'0');
                var dateStr = yy + '-' + mm + '-' + dd;
                sp.dataset.date = dateStr;
                
                if (cellDate < today) {
                    sp.className = 'calendar-day is-past disabled';
                } else {
                    sp.className = 'calendar-day is-available';
                }
                
                if (cellDate.getTime() === today.getTime()) {
                    sp.classList.add('today');
                }
                
                // Strictly enforce ONLY ONE selected date globally
                if (state.selectedDate === dateStr) {
                    sp.classList.add('selected', 'is-selected');
                }
                
                datesDiv.appendChild(sp);
            }
            monthDiv.appendChild(datesDiv);
            calendarGrid.appendChild(monthDiv);
        }
        
        if (calendarDisplay && state.selectedDate) {
            var parts = state.selectedDate.split('-');
            if (parts.length === 3) {
                calendarDisplay.value = parts[1] + '/' + parts[2] + '/' + parts[0];
            }
        } else if (calendarDisplay) {
            calendarDisplay.value = '';
        }
    }

    // ── SHOW TICKETS ──
    function showTicketsForSchedule(scheduleId, visitDate) {
        resetTickets(); // Always zeroes out quantities
        sectionTickets.classList.remove('step-hidden');
        var allRows = document.querySelectorAll('.ticket-row');
        allRows.forEach(function(r) {
            r.classList.remove('step-hidden'); // Show the single universal set of rows
        });
        var locName = getLocationName(state.locationId);
        if (ticketContext && visitDate) {
            ticketContext.textContent = "You're visiting the " + locName + " on " + formatDateLong(visitDate) + ".";
        }
        updateSubtotal();
    }

    // ══════════════════════════════════════════
    // GLOBAL SINGLE EVENT LISTENER
    // ══════════════════════════════════════════
    if (!window.admissionGlobalListenerAttached) {
        console.log("Attaching listener");
        document.addEventListener('click', function(e) {
            
            // 1️⃣ LOCATION CLICK
            var card = e.target.closest('.location-card');
            if (card) {
                var locId = parseInt(card.dataset.locationId, 10);
                if (isNaN(locId) || state.locationId === locId) return;

                document.querySelectorAll('.location-card').forEach(function(c) { c.classList.remove('active'); });
                card.classList.add('active');
                state.locationId = locId;

                sectionTickets.classList.add('step-hidden');
                resetTickets();
                resetDate();

                if (datePlaceholder) datePlaceholder.style.display = 'none';
                if (dateContent) dateContent.classList.remove('step-hidden');
                
                var calShell = document.querySelector('.calendar-shell');
                if (calShell) calShell.style.display = 'none';

                var locSchedules = getLocationSchedules(locId);
                buildDatePills(locSchedules);
                buildCalendar();
                return;
            }

            // 2️⃣ SCROLL TO LOCATION link
            if (e.target.closest('#scroll-to-location')) {
                document.querySelector('.section-location').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            // 3️⃣ DATE PILL CLICK
            var pill = e.target.closest('.date-pill');
            if (pill) {
                document.querySelectorAll('.date-pill').forEach(function(p) { p.classList.remove('active'); });
                pill.classList.add('active');
                
                var calShell = document.querySelector('.calendar-shell');
                
                if (pill.classList.contains('custom-date-pill')) {
                    if (calShell) calShell.style.display = 'block';
                } else {
                    if (calShell) calShell.style.display = 'none';
                    
                    document.querySelectorAll('.calendar-day').forEach(function(d) {
                        d.classList.remove('selected', 'is-selected');
                    });
                    
                    var customPill = document.querySelector('.custom-date-pill');
                    if (customPill) {
                        customPill.querySelector('.pill-label').textContent = 'Another date';
                        customPill.querySelector('.pill-date').textContent = 'Select a date';
                    }
                    
                    state.selectedDate = pill.dataset.visitDate;
                    state.scheduleId = parseInt(pill.dataset.scheduleId, 10);
                    showTicketsForSchedule(state.scheduleId, pill.dataset.visitDate);
                }
                return;
            }

            // 4️⃣ CALENDAR CELL CLICK
            var calCell = e.target.closest('.calendar-day');
            if (calCell) {
                if (calCell.classList.contains('disabled') || calCell.classList.contains('is-past')) return;
                
                var dateStr = calCell.dataset.date;
                if (!dateStr) return;
                
                var sched = findScheduleByDate(dateStr);
                if (!sched) {
                    alert('No tickets available for this date.');
                    return;
                }
                
                document.querySelectorAll('.calendar-day').forEach(function(d) { 
                    d.classList.remove('selected', 'is-selected'); 
                });
                calCell.classList.add('selected', 'is-selected');
                
                state.selectedDate = dateStr;
                
                if (calendarDisplay) {
                    var parts = dateStr.split('-');
                    calendarDisplay.value = parts[1] + '/' + parts[2] + '/' + parts[0];
                }
                
                var customPill = document.querySelector('.custom-date-pill');
                if (customPill) {
                    customPill.querySelector('.pill-label').textContent = 'Selected Date';
                    var cd = new Date(dateStr + 'T12:00:00');
                    var cMonths = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                    customPill.querySelector('.pill-date').textContent = cMonths[cd.getMonth()] + ' ' + cd.getDate() + ', ' + cd.getFullYear();
                }
                
                var calShell = document.querySelector('.calendar-shell');
                if (calShell) calShell.style.display = 'none';
                
                state.scheduleId = sched.visit_schedule_id;
                showTicketsForSchedule(sched.visit_schedule_id, dateStr);
                return;
            }
            
            // 4.5️⃣ CALENDAR ARROWS
            var prevArrow = e.target.closest('.prev-month');
            if (prevArrow) {
                state.calendarRefDate.setMonth(state.calendarRefDate.getMonth() - 1);
                buildCalendar();
                return;
            }
            var nextArrow = e.target.closest('.next-month');
            if (nextArrow) {
                state.calendarRefDate.setMonth(state.calendarRefDate.getMonth() + 1);
                buildCalendar();
                return;
            }

            // 5️⃣ TICKET +/- BUTTONS (STRICT MODE)
            
            // PLUS BUTTON
            if (e.target.classList.contains('btn-plus')) {
                const row = e.target.closest('.ticket-row');
                if (!row) return;

                const qtyEl = row.querySelector('.qty');
                let qty = parseInt(qtyEl.innerText || qtyEl.textContent || '0', 10);

                qtyEl.innerText = qty + 1;
                qtyEl.textContent = qty + 1;

                enforceChildRule();
                updateSubtotal();
            }

            // MINUS BUTTON
            if (e.target.classList.contains('btn-minus')) {
                const row = e.target.closest('.ticket-row');
                if (!row) return;

                const qtyEl = row.querySelector('.qty');
                let qty = parseInt(qtyEl.innerText || qtyEl.textContent || '0', 10);

                qtyEl.innerText = Math.max(0, qty - 1);
                qtyEl.textContent = Math.max(0, qty - 1);

                enforceChildRule();
                updateSubtotal();
            }


            // 6️⃣ NEXT BUTTON (Add to Cart)
            var nxtBtn = e.target.closest('#btn-next-step');
            if (nxtBtn && !nxtBtn.disabled) {
                if (!state.scheduleId || !state.locationId) {
                    alert('Please select a location and date first.');
                    return;
                }
                
                var payload = {
                    location_id: state.locationId,
                    visit_schedule_id: state.scheduleId,
                    items: []
                };
                
                var allRowsForSubmit = document.querySelectorAll('.ticket-row');
                allRowsForSubmit.forEach(function(r) {
                    if (r.classList.contains('step-hidden')) return;
                    var q = parseInt(r.querySelector('.qty').textContent || '0', 10);
                    if (q > 0) {
                        payload.items.push({ 
                            ticket_type_id: parseInt(r.dataset.ticketTypeId, 10), 
                            quantity: q 
                        });
                    }
                });
                
                if (payload.items.length === 0) { alert('Please select at least one ticket.'); return; }
                
                var csrf = document.querySelector('meta[name="csrf-token"]');
                var csrfToken = csrf ? csrf.getAttribute('content') : '';
                
                nxtBtn.disabled = true;
                nxtBtn.textContent = 'Adding...';
                
                fetch('/admission/cart/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.href = '/cart'; // Go to cart page
                    } else {
                        alert(data.message || 'Error storing admission.');
                        nxtBtn.disabled = false;
                        nxtBtn.textContent = 'Next';
                    }
                })
                .catch(function(err) {
                    console.error('Fetch error:', err);
                    alert('Network error. Please try again.');
                    nxtBtn.disabled = false;
                    nxtBtn.textContent = 'Next';
                });
                
                return;
            }
        }); // End single listener
        window.admissionGlobalListenerAttached = true;
    }

    // Init
    updateSubtotal();
});
</script>
@endsection



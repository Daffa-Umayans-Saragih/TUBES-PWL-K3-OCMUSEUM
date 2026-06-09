@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/admission/select.css')
@endpush

@section('content')
<div class="admission-select">
@php
    $mode = $location ?? ($date ?? ($tickets ?? false));

    // derive up to two location ids from schedules for the location cards
    $locationIds = collect();
    if (isset($schedules) && is_iterable($schedules)) {
        $locationIds = $schedules->pluck('location_id')->unique()->values();
    }

    $locIdA = $locationIds->get(0) ?? 1;
    $locIdB = $locationIds->get(1) ?? 2;
@endphp

@if($location ?? false)
    <div class="booking-panel booking-panel-location">
        <div class="booking-header section-header">
            <h2 class="booking-title section-title">1. Choose a location</h2>
            <p class="booking-subtitle">Choose one of two OC locations.</p>
        </div>

        <div class="location-grid">
            <article class="location-card" data-location-id="{{ $locIdA }}" style="pointer-events:auto;position:relative;z-index:1;">
                <img src="{{ asset('images/visit-guide.jpg') }}" alt="The OC Fifth Avenue" class="location-image">
                <div class="location-content">
                    <h3 class="location-name">The OC Fifth Avenue</h3>
                    <p class="location-hours">Sunday-Thursday and Thursday: 10 am - 5 pm</p>
                    <p class="location-hours">Friday and Saturday: 10 am - 9 pm</p>
                    <p class="location-hours">Closed Wednesday</p>
                </div>
            </article>

            <article class="location-card" data-location-id="{{ $locIdB }}" style="pointer-events:auto;position:relative;z-index:1;">
                <img src="{{ asset('images/the met cloisters.avif') }}" alt="The OC Cloisters" class="location-image">
                <div class="location-content">
                    <h3 class="location-name">The OC Cloisters</h3>
                    <p class="location-hours">Thursday-Tuesday: 10 am - 5 pm</p>
                    <p class="location-hours">Closed Wednesday</p>
                </div>
            </article>
        </div>
        <div class="booking-footer">
            <button type="button" class="btn btn-primary admission-step-next" disabled>
                Continue to Date Selection →
            </button>
        </div>
    </div>
@elseif($date ?? false)
    <div class="booking-panel booking-panel-date" data-id="{{ isset($schedules) && count($schedules) > 0 ? $schedules->first()->visit_schedule_id : '' }}">
        <div class="booking-header section-header">
            <h2 class="booking-title section-title">2. Select a date</h2>
            <p class="booking-subtitle">Please select an available date for your visit. Please note online ticket sales end one hour before closing.</p>
        </div>

        <div class="date-pills">
            @forelse($schedules->take(3) as $sched)
                <button type="button" class="date-pill" data-schedule-id="{{ $sched->visit_schedule_id }}" data-location-id="{{ $sched->location_id }}">
                    {{ $sched->visit_date->format('l') }}<br>
                    <span>{{ $sched->visit_date->format('F d, Y') }}</span>
                </button>
            @empty
                <button type="button" class="date-pill">No dates available</button>
            @endforelse
        </div>

        <div class="calendar-shell" aria-label="Calendar placeholder">
            <div class="calendar-input-row">
                <input type="text" value="04/27/2026" readonly class="calendar-input">
            </div>

            <div class="calendar-grid">
                <div class="calendar-month">
                    <div class="calendar-month-header">
                        <span class="calendar-arrow">‹</span>
                        <h3>April 2026</h3>
                    </div>
                    <div class="calendar-days">
                        <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                    </div>
                    <div class="calendar-dates">
                        <span></span><span></span><span></span><span></span><span>1</span><span>2</span><span>3</span>
                        <span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span><span>10</span>
                        <span>11</span><span>12</span><span>13</span><span>14</span><span>15</span><span>16</span><span>17</span>
                        <span>18</span><span>19</span><span>20</span><span>21</span><span>22</span><span>23</span><span>24</span>
                        <span>25</span><span>26</span><span class="is-selected">27</span><span>28</span><span>29</span><span>30</span><span></span>
                    </div>
                </div>

                <div class="calendar-month">
                    <div class="calendar-month-header">
                        <h3>May 2026</h3>
                        <span class="calendar-arrow">›</span>
                    </div>
                    <div class="calendar-days">
                        <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                    </div>
                    <div class="calendar-dates">
                        <span></span><span></span><span></span><span></span><span></span><span>1</span><span>2</span>
                        <span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span>
                        <span>10</span><span>11</span><span>12</span><span>13</span><span>14</span><span>15</span><span>16</span>
                        <span>17</span><span>18</span><span>19</span><span>20</span><span>21</span><span>22</span><span>23</span>
                        <span>24</span><span>25</span><span>26</span><span>27</span><span>28</span><span>29</span><span>30</span>
                        <span>31</span>
                    </div>
                </div>
            </div>

            <div class="calendar-legend">
                <span class="legend-item"><span class="legend-dot legend-dot-disabled"></span>Unavailable</span>
            </div>
        </div>
        <div class="booking-footer">
            <button type="button" class="btn btn-primary admission-step-next" disabled>
                Continue to Tickets →
            </button>
        </div>
    </div>
@elseif($tickets ?? false)
    <div class="booking-panel booking-panel-tickets">
        <div class="booking-header section-header">
            <h2 class="booking-title section-title">3. Tickets</h2>
            <p class="booking-subtitle">You&apos;re visiting the The OC Cloisters on Sunday, May 3.</p>
        </div>

        <div class="ticket-notice">
            <p>Unlock pay-what-you-wish pricing by entering your valid New York State zip code below.</p>
            <p>You can adjust your total payment amount after adding the tickets to your cart.</p>
            <p>Your zip code will be validated at checkout.</p>
            <p>If you are eligible for pay-what-you-wish tickets but do not have a NY zip code, you can purchase tickets in person with a valid ID.</p>
            <label class="field-label" for="zip_code">Enter zip code here</label>
            <input id="zip_code" type="text" class="zip-input" value="" readonly>
        </div>

        <div class="ticket-list">
            @foreach($schedules as $schedule)
                @foreach($schedule->ticketAvailabilities as $availability)
                    <div class="ticket-row" data-id="{{ $availability->ticket_availability_id }}" data-schedule-id="{{ $schedule->visit_schedule_id }}" data-type="{{ strtolower($availability->ticketType->name) }}" data-price="{{ $availability->ticketType->base_price }}">
                        <div>
                        <h3>{{ $availability->ticketType->name }} Admission</h3>
                        </div>
                        <div class="ticket-control">
                            <button type="button" class="btn-minus">-</button>
                            <span class="qty">0</span>
                            <button type="button" class="btn-plus">+</button>
                        </div>
                        <div class="ticket-meta">${{ number_format((float) $availability->ticketType->base_price, 2) }}</div>
                    </div>
                @endforeach
            @endforeach
        </div>
        <div class="booking-footer">
            <div id="subtotal">$0.00</div>
            <button type="button" class="btn btn-primary admission-step-next" disabled>
                Add to Cart →
            </button>
        </div>
    </div>
@else
    <div class="booking-panel">
        <p class="text-muted-light">Section placeholder.</p>
    </div>
@endif
</div>
@endsection

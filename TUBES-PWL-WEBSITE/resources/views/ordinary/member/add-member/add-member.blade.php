@extends('layouts.app')

@section('title', 'Membership Information')

@vite('resources/css/ordinary/member/add-member/add-member.css')

@section('content')
<main class="mem-page">

    {{-- Breadcrumb --}}
    <nav class="mem-breadcrumb" aria-label="breadcrumb">
        <ol>
            <li><a href="#">Membership</a></li>
            <li class="sep" aria-hidden="true">›</li>
            <li class="active" aria-current="page">Membership Information</li>
            <li class="sep" aria-hidden="true">›</li>
            <li><a href="#">Review</a></li>
            <li class="sep" aria-hidden="true">›</li>
            <li><a href="#">Pay</a></li>
        </ol>
    </nav>

    <div class="mem-layout">

        {{-- Page Header --}}
        <header class="mem-header">
            <h1 class="mem-title">Membership<br>Information</h1>
            <div class="mem-title-rule"></div>
        </header>

        {{-- Form --}}
        <form action="{{ route('member.add-member.submit') }}" method="POST" id="membershipForm" class="mem-form" novalidate>
            @csrf
            <input type="hidden" name="membership_id" value="{{ old('membership_id', 1) }}">

            {{-- === SECTION: Is This A Gift? === --}}
            <section class="mem-card mem-card--gift" aria-labelledby="giftSectionTitle">
                <h2 class="mem-card__title" id="giftSectionTitle">Is this a gift?</h2>
                <div class="mem-radio-group" role="radiogroup" aria-labelledby="giftSectionTitle">

                    <label class="mem-radio-label" id="label-no">
                        <input
                            type="radio"
                            name="is_gift"
                            value="0"
                            id="giftNo"
                            class="mem-radio-input"
                            checked
                        >
                        <span class="mem-radio-custom" aria-hidden="true"></span>
                        <span class="mem-radio-text">No, this is for me.</span>
                    </label>

                    <label class="mem-radio-label" id="label-yes">
                        <input
                            type="radio"
                            name="is_gift"
                            value="1"
                            id="giftYes"
                            class="mem-radio-input"
                        >
                        <span class="mem-radio-custom" aria-hidden="true"></span>
                        <span class="mem-radio-text">Yes, I would like to buy or renew a gift Membership.</span>
                    </label>

                </div>
            </section>

            {{-- ===================================================== --}}
            {{-- STATE 1: NOT A GIFT (default shown) --}}
            {{-- ===================================================== --}}
            <div id="stateNotGift" class="mem-state mem-state--active">

                {{-- Auto-Renewal Card --}}
                <section class="mem-card mem-card--renewal" aria-labelledby="renewalTitle">
                    <div class="mem-card__accent-bar"></div>
                    <div class="mem-card__inner">
                        <div class="mem-renewal-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        </div>
                        <div class="mem-renewal-body">
                            <h2 class="mem-card__title" id="renewalTitle">
                                 <span class="mem-highlight"></span> Automatic Renewal!
                            </h2>
                            <div class="mem-renewal-desc">

                            </div>
                            <div class="mem-radio-group mem-radio-group--stacked" role="radiogroup" aria-label="Automatic renewal options">
                                <label class="mem-radio-label">
                                    <input type="radio" name="auto_renewal" value="0" class="mem-radio-input" checked>
                                    <span class="mem-radio-custom" aria-hidden="true"></span>
                                    <span class="mem-radio-text">No, thanks. Please remind me to renew.</span>
                                </label>
                                <label class="mem-radio-label">
                                    <input type="radio" name="auto_renewal" value="1" class="mem-radio-input">
                                    <span class="mem-radio-custom" aria-hidden="true"></span>
                                    <span class="mem-radio-text">Yes, renew my support automatically.</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Primary Member --}}
                <section class="mem-card" aria-labelledby="primaryMemberTitle">
                    <h2 class="mem-card__title" id="primaryMemberTitle">Primary Member</h2>
                    <div class="mem-fields">
                        <div class="mem-field">
                            <label class="mem-label" for="first_name">First Name</label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                class="mem-input"
                                value="{{ old('first_name', $user->first_name ?? '') }}"
                                autocomplete="given-name"
                            >
                        </div>
                        <div class="mem-field">
                            <label class="mem-label" for="last_name">Last Name</label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                class="mem-input"
                                value="{{ old('last_name', $user->last_name ?? '') }}"
                                autocomplete="family-name"
                            >
                        </div>
                        <div class="mem-field mem-field--full">
                            <label class="mem-label" for="email">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="mem-input"
                                value="{{ old('email', $user->email ?? '') }}"
                                autocomplete="email"
                            >
                        </div>
                    </div>
                </section>

            </div>{{-- /stateNotGift --}}

            {{-- ===================================================== --}}
            {{-- STATE 2: IS A GIFT (hidden by default) --}}
            {{-- ===================================================== --}}
            <div id="stateIsGift" class="mem-state" aria-hidden="true">

                {{-- Primary Gift Recipient --}}
                <section class="mem-card" aria-labelledby="giftRecipientTitle">
                    <h2 class="mem-card__title" id="giftRecipientTitle">Primary Gift Recipient</h2>
                    <div class="mem-fields">
                        <div class="mem-field">
                            <label class="mem-label" for="gift_first_name">First Name</label>
                            <input
                                type="text"
                                name="gift_first_name"
                                id="gift_first_name"
                                class="mem-input"
                                value="{{ old('gift_first_name') }}"
                                autocomplete="given-name"
                            >
                        </div>
                        <div class="mem-field">
                            <label class="mem-label" for="gift_last_name">Last Name</label>
                            <input
                                type="text"
                                name="gift_last_name"
                                id="gift_last_name"
                                class="mem-input"
                                value="{{ old('gift_last_name') }}"
                                autocomplete="family-name"
                            >
                        </div>
                        <div class="mem-field mem-field--full">
                            <label class="mem-label" for="gift_email">Email</label>
                            <input
                                type="email"
                                name="gift_email"
                                id="gift_email"
                                class="mem-input"
                                value="{{ old('gift_email') }}"
                                autocomplete="email"
                            >
                        </div>
                    </div>
                </section>

                {{-- Gift Recipient Address --}}
                <section class="mem-card" aria-labelledby="giftAddressTitle">
                    <h2 class="mem-card__title" id="giftAddressTitle">Gift Recipient Address</h2>
                    <div class="mem-fields">
                        <div class="mem-field mem-field--full">
                            <label class="mem-label" for="street_address">Street Address</label>
                            <input
                                type="text"
                                name="street_address"
                                id="street_address"
                                class="mem-input"
                                value="{{ old('street_address') }}"
                                autocomplete="street-address"
                            >
                        </div>
                        <div class="mem-field mem-field--full">
                            <label class="mem-label" for="apartment">Apartment/Suite</label>
                            <input
                                type="text"
                                name="apartment"
                                id="apartment"
                                class="mem-input"
                                value="{{ old('apartment') }}"
                                autocomplete="address-line2"
                            >
                        </div>
                        <div class="mem-field">
                            <label class="mem-label" for="city">City</label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                class="mem-input"
                                value="{{ old('city') }}"
                                autocomplete="address-level2"
                            >
                        </div>
                        <div class="mem-field">
                            <label class="mem-label" for="country">Country</label>
                            <div class="mem-select-wrap">
                                <select name="country" id="country" class="mem-select">
                                    <option value="USA" {{ old('country') == 'USA' ? 'selected' : '' }}>USA</option>
                                    <option value="AF" {{ old('country') == 'AF' ? 'selected' : '' }}>Afghanistan</option>
                                    <option value="AL" {{ old('country') == 'AL' ? 'selected' : '' }}>Albania</option>
                                    <option value="DZ" {{ old('country') == 'DZ' ? 'selected' : '' }}>Algeria</option>
                                    <option value="AR" {{ old('country') == 'AR' ? 'selected' : '' }}>Argentina</option>
                                    <option value="AU" {{ old('country') == 'AU' ? 'selected' : '' }}>Australia</option>
                                    <option value="AT" {{ old('country') == 'AT' ? 'selected' : '' }}>Austria</option>
                                    <option value="BE" {{ old('country') == 'BE' ? 'selected' : '' }}>Belgium</option>
                                    <option value="BR" {{ old('country') == 'BR' ? 'selected' : '' }}>Brazil</option>
                                    <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                                    <option value="CN" {{ old('country') == 'CN' ? 'selected' : '' }}>China</option>
                                    <option value="CO" {{ old('country') == 'CO' ? 'selected' : '' }}>Colombia</option>
                                    <option value="HR" {{ old('country') == 'HR' ? 'selected' : '' }}>Croatia</option>
                                    <option value="CZ" {{ old('country') == 'CZ' ? 'selected' : '' }}>Czech Republic</option>
                                    <option value="DK" {{ old('country') == 'DK' ? 'selected' : '' }}>Denmark</option>
                                    <option value="EG" {{ old('country') == 'EG' ? 'selected' : '' }}>Egypt</option>
                                    <option value="FI" {{ old('country') == 'FI' ? 'selected' : '' }}>Finland</option>
                                    <option value="FR" {{ old('country') == 'FR' ? 'selected' : '' }}>France</option>
                                    <option value="DE" {{ old('country') == 'DE' ? 'selected' : '' }}>Germany</option>
                                    <option value="GH" {{ old('country') == 'GH' ? 'selected' : '' }}>Ghana</option>
                                    <option value="GR" {{ old('country') == 'GR' ? 'selected' : '' }}>Greece</option>
                                    <option value="HK" {{ old('country') == 'HK' ? 'selected' : '' }}>Hong Kong</option>
                                    <option value="HU" {{ old('country') == 'HU' ? 'selected' : '' }}>Hungary</option>
                                    <option value="IN" {{ old('country') == 'IN' ? 'selected' : '' }}>India</option>
                                    <option value="ID" {{ old('country') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                    <option value="IE" {{ old('country') == 'IE' ? 'selected' : '' }}>Ireland</option>
                                    <option value="IL" {{ old('country') == 'IL' ? 'selected' : '' }}>Israel</option>
                                    <option value="IT" {{ old('country') == 'IT' ? 'selected' : '' }}>Italy</option>
                                    <option value="JP" {{ old('country') == 'JP' ? 'selected' : '' }}>Japan</option>
                                    <option value="KE" {{ old('country') == 'KE' ? 'selected' : '' }}>Kenya</option>
                                    <option value="MY" {{ old('country') == 'MY' ? 'selected' : '' }}>Malaysia</option>
                                    <option value="MX" {{ old('country') == 'MX' ? 'selected' : '' }}>Mexico</option>
                                    <option value="NL" {{ old('country') == 'NL' ? 'selected' : '' }}>Netherlands</option>
                                    <option value="NZ" {{ old('country') == 'NZ' ? 'selected' : '' }}>New Zealand</option>
                                    <option value="NG" {{ old('country') == 'NG' ? 'selected' : '' }}>Nigeria</option>
                                    <option value="NO" {{ old('country') == 'NO' ? 'selected' : '' }}>Norway</option>
                                    <option value="PK" {{ old('country') == 'PK' ? 'selected' : '' }}>Pakistan</option>
                                    <option value="PE" {{ old('country') == 'PE' ? 'selected' : '' }}>Peru</option>
                                    <option value="PH" {{ old('country') == 'PH' ? 'selected' : '' }}>Philippines</option>
                                    <option value="PL" {{ old('country') == 'PL' ? 'selected' : '' }}>Poland</option>
                                    <option value="PT" {{ old('country') == 'PT' ? 'selected' : '' }}>Portugal</option>
                                    <option value="RO" {{ old('country') == 'RO' ? 'selected' : '' }}>Romania</option>
                                    <option value="RU" {{ old('country') == 'RU' ? 'selected' : '' }}>Russia</option>
                                    <option value="SA" {{ old('country') == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                                    <option value="SG" {{ old('country') == 'SG' ? 'selected' : '' }}>Singapore</option>
                                    <option value="ZA" {{ old('country') == 'ZA' ? 'selected' : '' }}>South Africa</option>
                                    <option value="KR" {{ old('country') == 'KR' ? 'selected' : '' }}>South Korea</option>
                                    <option value="ES" {{ old('country') == 'ES' ? 'selected' : '' }}>Spain</option>
                                    <option value="SE" {{ old('country') == 'SE' ? 'selected' : '' }}>Sweden</option>
                                    <option value="CH" {{ old('country') == 'CH' ? 'selected' : '' }}>Switzerland</option>
                                    <option value="TW" {{ old('country') == 'TW' ? 'selected' : '' }}>Taiwan</option>
                                    <option value="TH" {{ old('country') == 'TH' ? 'selected' : '' }}>Thailand</option>
                                    <option value="TR" {{ old('country') == 'TR' ? 'selected' : '' }}>Turkey</option>
                                    <option value="UA" {{ old('country') == 'UA' ? 'selected' : '' }}>Ukraine</option>
                                    <option value="AE" {{ old('country') == 'AE' ? 'selected' : '' }}>United Arab Emirates</option>
                                    <option value="GB" {{ old('country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="VN" {{ old('country') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                                </select>
                                <span class="mem-select-arrow" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="mem-field">
                            <label class="mem-label" for="state">State</label>
                            <div class="mem-select-wrap">
                                <select name="state" id="state" class="mem-select">
                                    <option value="AL" {{ old('state') == 'AL' ? 'selected' : '' }}>Alabama</option>
                                    <option value="AK" {{ old('state') == 'AK' ? 'selected' : '' }}>Alaska</option>
                                    <option value="AZ" {{ old('state') == 'AZ' ? 'selected' : '' }}>Arizona</option>
                                    <option value="AR" {{ old('state') == 'AR' ? 'selected' : '' }}>Arkansas</option>
                                    <option value="CA" {{ old('state') == 'CA' ? 'selected' : '' }}>California</option>
                                    <option value="CO" {{ old('state') == 'CO' ? 'selected' : '' }}>Colorado</option>
                                    <option value="CT" {{ old('state') == 'CT' ? 'selected' : '' }}>Connecticut</option>
                                    <option value="DE" {{ old('state') == 'DE' ? 'selected' : '' }}>Delaware</option>
                                    <option value="FL" {{ old('state') == 'FL' ? 'selected' : '' }}>Florida</option>
                                    <option value="GA" {{ old('state') == 'GA' ? 'selected' : '' }}>Georgia</option>
                                    <option value="HI" {{ old('state') == 'HI' ? 'selected' : '' }}>Hawaii</option>
                                    <option value="ID" {{ old('state') == 'ID' ? 'selected' : '' }}>Idaho</option>
                                    <option value="IL" {{ old('state') == 'IL' ? 'selected' : '' }}>Illinois</option>
                                    <option value="IN" {{ old('state') == 'IN' ? 'selected' : '' }}>Indiana</option>
                                    <option value="IA" {{ old('state') == 'IA' ? 'selected' : '' }}>Iowa</option>
                                    <option value="KS" {{ old('state') == 'KS' ? 'selected' : '' }}>Kansas</option>
                                    <option value="KY" {{ old('state') == 'KY' ? 'selected' : '' }}>Kentucky</option>
                                    <option value="LA" {{ old('state') == 'LA' ? 'selected' : '' }}>Louisiana</option>
                                    <option value="ME" {{ old('state') == 'ME' ? 'selected' : '' }}>Maine</option>
                                    <option value="MD" {{ old('state') == 'MD' ? 'selected' : '' }}>Maryland</option>
                                    <option value="MA" {{ old('state') == 'MA' ? 'selected' : '' }}>Massachusetts</option>
                                    <option value="MI" {{ old('state') == 'MI' ? 'selected' : '' }}>Michigan</option>
                                    <option value="MN" {{ old('state') == 'MN' ? 'selected' : '' }}>Minnesota</option>
                                    <option value="MS" {{ old('state') == 'MS' ? 'selected' : '' }}>Mississippi</option>
                                    <option value="MO" {{ old('state') == 'MO' ? 'selected' : '' }}>Missouri</option>
                                    <option value="MT" {{ old('state') == 'MT' ? 'selected' : '' }}>Montana</option>
                                    <option value="NE" {{ old('state') == 'NE' ? 'selected' : '' }}>Nebraska</option>
                                    <option value="NV" {{ old('state') == 'NV' ? 'selected' : '' }}>Nevada</option>
                                    <option value="NH" {{ old('state') == 'NH' ? 'selected' : '' }}>New Hampshire</option>
                                    <option value="NJ" {{ old('state') == 'NJ' ? 'selected' : '' }}>New Jersey</option>
                                    <option value="NM" {{ old('state') == 'NM' ? 'selected' : '' }}>New Mexico</option>
                                    <option value="NY" {{ old('state') == 'NY' ? 'selected' : '' }}>New York</option>
                                    <option value="NC" {{ old('state') == 'NC' ? 'selected' : '' }}>North Carolina</option>
                                    <option value="ND" {{ old('state') == 'ND' ? 'selected' : '' }}>North Dakota</option>
                                    <option value="OH" {{ old('state') == 'OH' ? 'selected' : '' }}>Ohio</option>
                                    <option value="OK" {{ old('state') == 'OK' ? 'selected' : '' }}>Oklahoma</option>
                                    <option value="OR" {{ old('state') == 'OR' ? 'selected' : '' }}>Oregon</option>
                                    <option value="PA" {{ old('state') == 'PA' ? 'selected' : '' }}>Pennsylvania</option>
                                    <option value="RI" {{ old('state') == 'RI' ? 'selected' : '' }}>Rhode Island</option>
                                    <option value="SC" {{ old('state') == 'SC' ? 'selected' : '' }}>South Carolina</option>
                                    <option value="SD" {{ old('state') == 'SD' ? 'selected' : '' }}>South Dakota</option>
                                    <option value="TN" {{ old('state') == 'TN' ? 'selected' : '' }}>Tennessee</option>
                                    <option value="TX" {{ old('state') == 'TX' ? 'selected' : '' }}>Texas</option>
                                    <option value="UT" {{ old('state') == 'UT' ? 'selected' : '' }}>Utah</option>
                                    <option value="VT" {{ old('state') == 'VT' ? 'selected' : '' }}>Vermont</option>
                                    <option value="VA" {{ old('state') == 'VA' ? 'selected' : '' }}>Virginia</option>
                                    <option value="WA" {{ old('state') == 'WA' ? 'selected' : '' }}>Washington</option>
                                    <option value="WV" {{ old('state') == 'WV' ? 'selected' : '' }}>West Virginia</option>
                                    <option value="WI" {{ old('state') == 'WI' ? 'selected' : '' }}>Wisconsin</option>
                                    <option value="WY" {{ old('state') == 'WY' ? 'selected' : '' }}>Wyoming</option>
                                </select>
                                <span class="mem-select-arrow" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="mem-field">
                            <label class="mem-label" for="postal_code">Postal Code</label>
                            <input
                                type="text"
                                name="postal_code"
                                id="postal_code"
                                class="mem-input"
                                value="{{ old('postal_code') }}"
                                autocomplete="postal-code"
                            >
                        </div>
                    </div>
                </section>

                {{-- Ship Membership Card --}}
                <section class="mem-card" aria-labelledby="shipCardTitle">
                    <h2 class="mem-card__title" id="shipCardTitle">Ship Membership Card(s) to this address</h2>
                    <div class="mem-radio-group" role="radiogroup" aria-labelledby="shipCardTitle">
                        <label class="mem-radio-label">
                            <input type="radio" name="ship_to" id="shipToRecipient" value="recipient" class="mem-radio-input" checked>
                            <span class="mem-radio-custom" aria-hidden="true"></span>
                            <span class="mem-radio-text">Ship to Gift Recipient</span>
                        </label>
                        <label class="mem-radio-label">
                            <input type="radio" name="ship_to" id="shipToDonor" value="donor" class="mem-radio-input">
                            <span class="mem-radio-custom" aria-hidden="true"></span>
                            <span class="mem-radio-text">Ship to Donor</span>
                        </label>
                    </div>
                </section>

                {{-- Gift Membership Email Confirmation --}}
                <section class="mem-card" aria-labelledby="emailConfirmTitle">
                    <h2 class="mem-card__title" id="emailConfirmTitle">Gift Membership Email Confirmation</h2>
                    <div class="mem-radio-group" role="radiogroup" aria-labelledby="emailConfirmTitle">
                        <label class="mem-radio-label" id="emailConfirmBothLabel">
                            <input type="radio" name="email_confirmation" id="emailConfirmBoth" value="both" class="mem-radio-input">
                            <span class="mem-radio-custom" aria-hidden="true"></span>
                            <span class="mem-radio-text">Send to Gift Recipient and Donor</span>
                        </label>
                        <label class="mem-radio-label">
                            <input type="radio" name="email_confirmation" id="emailConfirmDonor" value="donor" class="mem-radio-input" checked>
                            <span class="mem-radio-custom" aria-hidden="true"></span>
                            <span class="mem-radio-text">Send to Donor only</span>
                        </label>
                    </div>
                    <p id="donorHelperText" style="display: none; margin-top: 0.75rem; font-size: 0.875rem; color: #666; line-height: 1.4;">
                        Since the membership is delivered to the donor, email confirmation will only be sent to the donor to preserve the surprise.
                    </p>
                </section>

            </div>{{-- /stateIsGift --}}

            {{-- Checkout Button --}}
            <div class="mem-actions">
                <button type="submit" class="mem-btn-checkout">
                    <span class="mem-btn-checkout__text">Checkout</span>
                    <span class="mem-btn-checkout__arrow" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
            </div>

        </form>
    </div>{{-- /mem-layout --}}
</main>

@push('scripts')
<script>
(function () {
    'use strict';

    var giftNo      = document.getElementById('giftNo');
    var giftYes     = document.getElementById('giftYes');
    var stateNo     = document.getElementById('stateNotGift');
    var stateYes    = document.getElementById('stateIsGift');

    function switchState(isGift) {
        if (isGift) {
            stateNo.classList.remove('mem-state--active');
            stateNo.setAttribute('aria-hidden', 'true');

            stateYes.classList.add('mem-state--active');
            stateYes.removeAttribute('aria-hidden');
        } else {
            stateYes.classList.remove('mem-state--active');
            stateYes.setAttribute('aria-hidden', 'true');

            stateNo.classList.add('mem-state--active');
            stateNo.removeAttribute('aria-hidden');
        }
    }

    giftNo.addEventListener('change', function () {
        if (this.checked) switchState(false);
    });

    giftYes.addEventListener('change', function () {
        if (this.checked) switchState(true);
    });

    // Init: ensure correct state on page load (handles back-button / old() value)
    if (giftYes.checked) {
        switchState(true);
    } else {
        giftNo.checked = true;
        switchState(false);
    }

    // --- Smart Conditional UX for Shipping & Emails ---
    var shipToRecipient = document.getElementById('shipToRecipient');
    var shipToDonor     = document.getElementById('shipToDonor');
    var emailConfirmBoth = document.getElementById('emailConfirmBoth');
    var emailConfirmDonor = document.getElementById('emailConfirmDonor');
    var donorHelperText = document.getElementById('donorHelperText');
    var emailConfirmBothLabel = document.getElementById('emailConfirmBothLabel');

    function updateEmailConfirmationState() {
        if (!shipToDonor || !emailConfirmBoth) return;
        
        if (shipToDonor.checked) {
            emailConfirmBoth.disabled = true;
            emailConfirmBoth.checked = false;
            emailConfirmDonor.checked = true;
            if (donorHelperText) donorHelperText.style.display = 'block';
            if (emailConfirmBothLabel) emailConfirmBothLabel.style.opacity = '0.5';
        } else {
            emailConfirmBoth.disabled = false;
            if (donorHelperText) donorHelperText.style.display = 'none';
            if (emailConfirmBothLabel) emailConfirmBothLabel.style.opacity = '1';
        }
    }

    if (shipToRecipient) shipToRecipient.addEventListener('change', updateEmailConfirmationState);
    if (shipToDonor) shipToDonor.addEventListener('change', updateEmailConfirmationState);

    updateEmailConfirmationState();
}());
</script>
@endpush

@endsection

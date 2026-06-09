@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/account/account/account.css')
@endpush

@section('title', 'My Account — OC Museum')

@section('content')

@php
    $fullName = trim(($user->profile?->first_name ?? '') . ' ' . ($user->profile?->last_name ?? ''));
    $displayName = $fullName !== '' ? $fullName : ($user->name ?? 'Museum Guest');
    $initials = collect(explode(' ', $displayName))
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
    $addressLine1 = $user->profile?->address1 ?? null;
    $addressLine2 = $user->profile?->address2 ?? null;
    $phoneNumber = $user->profile?->phone_number ?? null;
@endphp

<div class="acc-root">

    {{-- ═══ PAGE HEADER ═══ --}}
    <header class="acc-header">
        <div class="acc-container">
            <div class="acc-header__inner">
                <div class="acc-header__copy">
                    <p class="acc-eyebrow">The OC Museum of Art</p>
                    <h1 class="acc-heading">My Account</h1>
                    <p class="acc-subheading">Manage your membership, address, and preferences.</p>
                </div>
                <nav class="acc-header__nav" aria-label="Account actions">
                    <a href="{{ route('order.show') }}" class="acc-btn acc-btn--ghost">
                        <i class="bi bi-receipt" aria-hidden="true"></i>
                        <span>Orders</span>
                    </a>
                    <a href="{{ route('ticket.index') }}" class="acc-btn acc-btn--primary">
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                        <span>Book Tickets</span>
                    </a>
                </nav>
            </div>
        </div>
        <div class="acc-header__rule" aria-hidden="true"></div>
    </header>

    {{-- ═══ MAIN LAYOUT ═══ --}}
    <main class="acc-main">
        <div class="acc-container">
            <div class="acc-layout">

                {{-- ── SIDEBAR ── --}}
                <aside class="acc-sidebar" aria-label="Profile summary">

                    <div class="acc-panel acc-panel--profile">
                        <div class="acc-avatar" aria-hidden="true">
                            <span>{{ $initials ?: 'U' }}</span>
                        </div>
                        <div class="acc-profile__info">
                            <h2 class="acc-profile__name">{{ $displayName }}</h2>
                            <p class="acc-profile__email">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="acc-panel acc-panel--meta">
                        <ul class="acc-meta-list" role="list">
                            <li class="acc-meta-item">
                                <span class="acc-meta-item__label">
                                    <i class="bi bi-shield-check" aria-hidden="true"></i>Status
                                </span>
                                <span class="acc-meta-item__value acc-meta-item__value--badge">Active</span>
                            </li>
                            <li class="acc-meta-item">
                                <span class="acc-meta-item__label">
                                    <i class="bi bi-person-badge" aria-hidden="true"></i>Account
                                </span>
                                <span class="acc-meta-item__value">Registered</span>
                            </li>
                            <li class="acc-meta-item acc-meta-item--full">
                                <span class="acc-meta-item__label">
                                    <i class="bi bi-envelope" aria-hidden="true"></i>Email
                                </span>
                                <span class="acc-meta-item__value acc-meta-item__value--break">{{ $user->email }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="acc-panel acc-panel--actions">
                        <button type="button"
                                class="acc-btn acc-btn--primary acc-btn--full"
                                data-bs-toggle="modal"
                                data-bs-target="#addressModal">
                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            <span>Edit Address</span>
                        </button>
                        <form action="{{ route('account.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="acc-btn acc-btn--danger acc-btn--full">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>

                </aside>

                {{-- ── CONTENT ── --}}
                <div class="acc-content">

                    {{-- Personal Information --}}
                    <section class="acc-panel acc-panel--section" aria-labelledby="sec-personal">
                        <div class="acc-section-head">
                            <div>
                                <p class="acc-eyebrow acc-eyebrow--sm">Profile Overview</p>
                                <h2 class="acc-section-title" id="sec-personal">Personal Information</h2>
                            </div>
                            <span class="acc-chip">
                                <i class="bi bi-info-circle" aria-hidden="true"></i>Read only
                            </span>
                        </div>

                        <dl class="acc-info-grid">
                            <div class="acc-info-cell">
                                <div class="acc-info-cell__icon" aria-hidden="true">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="acc-info-cell__body">
                                    <dt class="acc-info-cell__label">Full Name</dt>
                                    <dd class="acc-info-cell__value">{{ $displayName }}</dd>
                                </div>
                            </div>
                            <div class="acc-info-cell">
                                <div class="acc-info-cell__icon" aria-hidden="true">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="acc-info-cell__body">
                                    <dt class="acc-info-cell__label">Email Address</dt>
                                    <dd class="acc-info-cell__value acc-info-cell__value--break">{{ $user->email }}</dd>
                                </div>
                            </div>
                            <div class="acc-info-cell">
                                <div class="acc-info-cell__icon" aria-hidden="true">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div class="acc-info-cell__body">
                                    <dt class="acc-info-cell__label">Phone</dt>
                                    <dd class="acc-info-cell__value">{{ $phoneNumber ?: '—' }}</dd>
                                </div>
                            </div>
                            <div class="acc-info-cell">
                                <div class="acc-info-cell__icon" aria-hidden="true">
                                    <i class="bi bi-geo"></i>
                                </div>
                                <div class="acc-info-cell__body">
                                    <dt class="acc-info-cell__label">Address</dt>
                                    <dd class="acc-info-cell__value">{{ $addressLine1 ? 'Saved' : 'Not provided' }}</dd>
                                </div>
                            </div>
                        </dl>
                    </section>

                    {{-- Address --}}
                    <section class="acc-panel acc-panel--section" aria-labelledby="sec-address">
                        <div class="acc-section-head">
                            <div>
                                <p class="acc-eyebrow acc-eyebrow--sm">Address Book</p>
                                <h2 class="acc-section-title" id="sec-address">Primary Address</h2>
                            </div>
                            <button type="button"
                                    class="acc-btn acc-btn--ghost acc-btn--sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addressModal">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                <span>Edit</span>
                            </button>
                        </div>

                        <div class="acc-address-block">
                            @if($addressLine1)
                                <address class="acc-address-text">
                                    {{ $addressLine1 }}
                                    @if($addressLine2)
                                        <br>{{ $addressLine2 }}
                                    @endif
                                </address>
                            @else
                                <p class="acc-address-empty">No address on file.</p>
                            @endif
                        </div>

                        <div class="acc-notice">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Address editing is ready for future backend wiring — your current data is preserved.</span>
                        </div>
                    </section>

                    {{-- Quick Actions --}}
                    <section class="acc-panel acc-panel--section" aria-labelledby="sec-shortcuts">
                        <div class="acc-section-head">
                            <div>
                                <p class="acc-eyebrow acc-eyebrow--sm">Navigation</p>
                                <h2 class="acc-section-title" id="sec-shortcuts">Quick Actions</h2>
                            </div>
                        </div>

                        <nav class="acc-quick-grid" aria-label="Quick links">
                            <a href="{{ route('ticket.cart') }}" class="acc-quick-card">
                                <span class="acc-quick-card__icon" aria-hidden="true">
                                    <i class="bi bi-cart3"></i>
                                </span>
                                <span class="acc-quick-card__label">Open Cart</span>
                                <i class="bi bi-arrow-right acc-quick-card__arrow" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('ticket.admission') }}" class="acc-quick-card">
                                <span class="acc-quick-card__icon" aria-hidden="true">
                                    <i class="bi bi-ticket-perforated"></i>
                                </span>
                                <span class="acc-quick-card__label">Browse Admission</span>
                                <i class="bi bi-arrow-right acc-quick-card__arrow" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('about') }}" class="acc-quick-card">
                                <span class="acc-quick-card__icon" aria-hidden="true">
                                    <i class="bi bi-building"></i>
                                </span>
                                <span class="acc-quick-card__label">About The OC</span>
                                <i class="bi bi-arrow-right acc-quick-card__arrow" aria-hidden="true"></i>
                            </a>
                        </nav>
                    </section>

                </div>
                {{-- /acc-content --}}

            </div>
            {{-- /acc-layout --}}
        </div>
    </main>

</div>
{{-- /acc-root --}}


{{-- ═══ ADDRESS MODAL ═══ --}}
<div class="modal fade" id="addressModal" tabindex="-1"
     aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content acc-modal">

            <div class="acc-modal__header">
                <div>
                    <p class="acc-eyebrow acc-eyebrow--sm">Address Book</p>
                    <h2 class="acc-modal__title" id="addressModalLabel">Edit Primary Address</h2>
                </div>
                <button type="button" class="acc-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            <div class="acc-modal__body">
                <div class="acc-notice acc-notice--modal">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>No update route is currently available. This modal is wired and ready for future integration.</span>
                </div>

                <div class="acc-field">
                    <label class="acc-field__label" for="addressTextarea">Address</label>
                    <textarea class="acc-field__textarea" id="addressTextarea" rows="5" readonly
                    >{{ $addressLine1 ?: '' }}@if($addressLine2)
{{ $addressLine2 }}@endif</textarea>
                </div>
            </div>

            <div class="acc-modal__footer">
                <button type="button" class="acc-btn acc-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="acc-btn acc-btn--primary" disabled
                        title="No update route available">Save Changes</button>
            </div>

        </div>
    </div>
</div>

@endsection

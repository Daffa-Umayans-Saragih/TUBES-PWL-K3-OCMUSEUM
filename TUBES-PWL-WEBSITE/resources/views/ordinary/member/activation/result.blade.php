@extends('layouts.app')

@section('title', $title)

@section('content')
<style>
    .activation-page {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 24px;
        background: #F5F7FA;
    }

    .activation-card {
        background: #fff;
        border: 1px solid #D9E2EC;
        border-radius: 24px;
        max-width: 560px;
        width: 100%;
        overflow: hidden;
        box-shadow: 0 20px 48px rgba(22,16,10,0.10);
        text-align: center;
    }

    .activation-card__header {
        padding: 44px 40px 36px;
    }

    .activation-card__header--success {
        background: linear-gradient(160deg, #082B5B 0%, #103B78 100%);
        color: #fff;
    }

    .activation-card__header--error {
        background: linear-gradient(160deg, #103B78 0%, #082B5B 100%);
        color: #fff;
    }

    .activation-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 32px;
    }

    .activation-icon--success {
        background: rgba(255,255,255,0.18);
    }

    .activation-icon--error {
        background: rgba(255,255,255,0.12);
    }

    .activation-badge {
        display: inline-block;
        font-size: 11px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,0.18);
        color: #fff;
        margin-bottom: 20px;
    }

    .activation-title {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 28px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 12px;
        line-height: 1.25;
    }

    .activation-subtitle {
        font-size: 15px;
        color: rgba(255,255,255,0.82);
        margin: 0;
        line-height: 1.65;
    }

    .activation-card__body {
        padding: 36px 40px 40px;
    }

    .activation-message {
        font-size: 15px;
        color: #1E293B;
        line-height: 1.75;
        margin: 0 0 28px;
    }

    .activation-detail {
        background: #F5F7FA;
        border: 1px solid #D9E2EC;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 28px;
        text-align: left;
    }

    .activation-detail__row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 14px;
        border-bottom: 1px solid #D9E2EC;
    }

    .activation-detail__row:last-child {
        border-bottom: none;
    }

    .activation-detail__label {
        color: #1E293B;
        font-weight: 600;
        letter-spacing: 0.03em;
    }

    .activation-detail__value {
        color: #1E293B;
        font-weight: 700;
    }

    .activation-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .activation-status-badge--active {
        background: #edfaf2;
        color: #166534;
    }

    .activation-status-badge--pending {
        background: #fef9ec;
        color: #92400e;
    }

    .activation-btn {
        display: inline-block;
        background: #082B5B;
        color: #fff;
        text-decoration: none;
        font-size: 15px;
        font-weight: 700;
        padding: 14px 32px;
        border-radius: 999px;
        transition: background 0.2s, box-shadow 0.2s;
        box-shadow: 0 8px 20px rgba(8,43,91,0.22);
    }

    .activation-btn:hover {
        background: #103B78;
        box-shadow: 0 12px 28px rgba(8,43,91,0.32);
        color: #fff;
    }

    .activation-btn--secondary {
        background: #F5F7FA;
        color: #1E293B;
        box-shadow: none;
        border: 1px solid #D9E2EC;
    }

    .activation-btn--secondary:hover {
        background: #D9E2EC;
        color: #1E293B;
        box-shadow: none;
    }

    .activation-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .met-wordmark {
        font-size: 11px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #1E293B;
        margin-top: 28px;
    }
</style>

<div class="activation-page">
    <div class="activation-card">

        {{-- Header --}}
        <div class="activation-card__header {{ $success ? 'activation-card__header--success' : 'activation-card__header--error' }}">
            <div class="activation-icon {{ $success ? 'activation-icon--success' : 'activation-icon--error' }}">
                @if ($success)
                    {{-- Checkmark --}}
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                        <circle cx="18" cy="18" r="18" fill="rgba(255,255,255,0.15)"/>
                        <path d="M10 18.5l5.5 5.5 10-11" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @else
                    {{-- X --}}
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                        <circle cx="18" cy="18" r="18" fill="rgba(255,255,255,0.15)"/>
                        <path d="M13 13l10 10M23 13l-10 10" stroke="#fff" stroke-width="2.8" stroke-linecap="round"/>
                    </svg>
                @endif
            </div>

            <div class="activation-badge">
                Our Civilization
            </div>

            <h1 class="activation-title">{{ $title }}</h1>

            <p class="activation-subtitle">{{ $message }}</p>
        </div>

        {{-- Body --}}
        <div class="activation-card__body">

            @if ($success && isset($membership))
                <div class="activation-detail">
                    @php
                        $status = $membership->membership_status ?? 'active';
                    @endphp

                    <div class="activation-detail__row">
                        <span class="activation-detail__label">Status</span>
                        <span class="activation-status-badge activation-status-badge--active">
                            ✓ Active
                        </span>
                    </div>

                    @if ($membership->expires_at)
                    <div class="activation-detail__row">
                        <span class="activation-detail__label">Valid Until</span>
                        <span class="activation-detail__value">
                            {{ $membership->expires_at->format('F j, Y') }}
                        </span>
                    </div>
                    @endif

                    @if ($membership->auto_renewal)
                    <div class="activation-detail__row">
                        <span class="activation-detail__label">Auto-Renewal</span>
                        <span class="activation-detail__value">Enabled</span>
                    </div>
                    @endif
                </div>

                <div class="activation-actions">
                    <a href="{{ route('home') }}" class="activation-btn">
                        Explore OC
                    </a>
                    <a href="{{ route('ticket.index') }}" class="activation-btn activation-btn--secondary">
                        Buy Tickets
                    </a>
                </div>

            @elseif (! $success)
                <div class="activation-actions">
                    <a href="{{ route('member.add-member') }}" class="activation-btn">
                        Get a Membership
                    </a>
                    <a href="{{ route('home') }}" class="activation-btn activation-btn--secondary">
                        Back to Home
                    </a>
                </div>
            @else
                <div class="activation-actions">
                    <a href="{{ route('home') }}" class="activation-btn">
                        Explore OC
                    </a>
                </div>
            @endif

            <div class="met-wordmark">© Our Civilization</div>
        </div>

    </div>
</div>
@endsection

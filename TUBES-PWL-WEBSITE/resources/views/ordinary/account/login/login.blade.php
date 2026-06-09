@extends('ordinary.account.login.layout')

@push('styles')
@vite('resources/css/ordinary/account/login/login.css')
<style>
  /* Prevent :invalid from showing red on page load */
  input:invalid {
    box-shadow: none !important;
    border-color: #cfcfcf !important;
  }
  /* Show red after user interaction if invalid */
  input:user-invalid {
    border-color: #ef0030 !important;
  }
  /* Preserve error styles when applied by Laravel/JS */
  input.is-invalid, .is-invalid input, .border-red-500 {
    border-color: #ef0030 !important;
  }
</style>
@endpush

@section('content')
<section class="login-shell" aria-labelledby="login-heading">
    <div class="login-panel login-panel-main mb-12 mt-12">
        <h1 id="login-heading" class="login-title">Login</h1>

        <p class="login-lead">Log in or create an account to continue.</p>

        <p class="login-copy">
            If you are a Member, or if your email may already be on record with the Museum, please click the
            button below to check if you already have an account and reset your password.
        </p>

        <a href="{{ route('account.account-check') }}" class="btn btn-outline btn-check">Check for account</a>

        <div class="login-rule"></div>

        @if ($errors->any())
            <div class="login-error-box" role="alert">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                @if (session('account_not_found'))
                    <p class="login-help-text">
                        If you forgot your password click on
                        <a href="{{ route('account.forgot-password') }}">Forgot Password?</a>
                        below to reset it. To register click on
                        <a href="{{ route('account.register') }}">Create an Account</a>.
                    </p>
                @endif
            </div>
        @endif

        <form action="{{ route('account.login.submit') }}" method="POST" class="login-form">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" id="togglePassword" class="password-toggle" aria-label="Show password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <a href="{{ route('account.forgot-password') }}" class="forgot-link">Forgot password?</a>

            <p class="terms-copy">
                By logging in, you agree to our
                <a href="#">Terms of Service</a>
                and
                <a href="#">Privacy Policy</a>
            </p>

            <button type="submit" class="bg-blue-950 hover:bg-blue-900 text-white font-semibold py-2 px-4 rounded mb-3">
  Log in
</button> 
            <a href="{{ route('account.register') }}" class="btn btn-outline btn-create">Create an account</a>
        </form>
    </div>

    <aside class="guest-panel mt-12" aria-labelledby="guest-heading">
        <h2 id="guest-heading" class="guest-title">Guest Checkout</h2>

        <p class="guest-lead">Enter your name and email here.</p>

        {{-- Per-field errors are shown below each input --}}
        <div class="guest-error-summary" id="guest-error-summary" aria-live="polite" style="display:none;"></div>

        <form action="{{ route('guest.login') }}" method="POST" class="guest-form" novalidate data-guest-checkout-form>
            @csrf

            <div class="field">
                <label for="guest-email">Email</label>
                <div class="guest-input-wrap">
                    <input
                        type="email"
                        id="guest-email"
                        name="email"
                        value="{{ old('email') }}"
                        data-guest-field="email"
                        class="@error('email', 'guestCheckout') is-invalid @enderror"
                    >
                    <span class="field-status" aria-hidden="true">i</span>
                </div>
                <div class="field-error-message" id="guest-email-error" data-error-for="email">
                    @error('email', 'guestCheckout'){{ $message }}@enderror
                </div>
            </div>

            <div class="field">
                <label for="guest-confirm-email">Please confirm email address.</label>
                <div class="guest-input-wrap">
                    <input
                        type="email"
                        id="guest-confirm-email"
                        name="confirm_email"
                        value="{{ old('confirm_email') }}"
                        placeholder="Please confirm email address."
                        data-guest-field="confirm_email"
                        class="@error('confirm_email', 'guestCheckout') is-invalid @enderror"
                    >
                    <span class="field-status" aria-hidden="true">i</span>
                </div>
                <div class="field-error-message" id="guest-confirm-email-error" data-error-for="confirm_email">
                    @error('confirm_email', 'guestCheckout'){{ $message }}@enderror
                </div>
            </div>

            <div class="guest-name-row">
                <div class="field">
                    <label for="guest-first-name">First Name</label>
                    <div class="guest-input-wrap">
                        <input
                            type="text"
                            id="guest-first-name"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="First Name"
                            data-guest-field="first_name"
                            class="@error('first_name', 'guestCheckout') is-invalid @enderror"
                        >
                        <span class="field-status" aria-hidden="true">i</span>
                    </div>
                    <div class="field-error-message" id="guest-first-name-error" data-error-for="first_name">
                        @error('first_name', 'guestCheckout'){{ $message }}@enderror
                    </div>
                </div>

                <div class="field">
                    <label for="guest-last-name">Last Name</label>
                    <div class="guest-input-wrap">
                        <input
                            type="text"
                            id="guest-last-name"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="Last Name"
                            data-guest-field="last_name"
                            class="@error('last_name', 'guestCheckout') is-invalid @enderror"
                        >
                        <span class="field-status" aria-hidden="true">i</span>
                    </div>
                    <div class="field-error-message" id="guest-last-name-error" data-error-for="last_name">
                        @error('last_name', 'guestCheckout'){{ $message }}@enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="bg-blue-950 hover:bg-blue-900 text-white font-semibold py-2 px-4 rounded" data-guest-submit>Continue as guest</button>
        </form>
    </aside>
</section>

@push('scripts')
<script src="{{ asset('js/guest-checkout.js') }}?v={{ time() }}" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');

        if (!input || !toggle) {
            return;
        }

        toggle.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            
            if (showing) {
                toggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/>
                    </svg>
                `;
            } else {
                toggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                `;
            }
        });
    });
</script>
@endpush
@endsection

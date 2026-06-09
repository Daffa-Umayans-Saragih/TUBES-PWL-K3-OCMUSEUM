@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/ordinary/account/forgot-password/forgot-password.css')
@endpush

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Forgot Password</h1>
            <p class="auth-subtitle">Reset your password to regain access to your account</p>

            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('account.forgot-password.submit') }}" method="POST" class="auth-form" novalidate>
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') ?? ($email ?? '') }}" required>
                    @error('email')
                        <small class="field-error-message">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
            </form>

            <div class="auth-links">
                <p>Remember your password? <a href="{{ route('account.login') }}" >Sign in</a></p>
                <p>Don't have an account? <a href="{{ route('account.register') }}" >Create one</a></p>
            </div>
        </div>
    </div>
</div>
@endsection

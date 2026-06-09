@extends('layouts.app')

@push('styles')
@vite('resources/css/ordinary/account/auth-form.css')
@endpush

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>{{ $title ?? 'Check for an Account' }}</h1>
            <p class="auth-subtitle">{{ $subtitle ?? 'Enter your information to find your account or register' }}</p>

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

            <form action="{{ $action ?? route('account.account-check') }}" method="POST" class="auth-form" novalidate>
                @csrf

                @if($includeNames ?? true)
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" maxlength="100" required>
                        @error('first_name')
                            <small class="field-error-message">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" maxlength="100" required>
                        @error('last_name')
                            <small class="field-error-message">{{ $message }}</small>
                        @enderror
                    </div>
                @endif

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') ?? session('email') }}" required>
                    @error('email')
                        <small class="field-error-message">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">{{ $buttonText ?? 'Check Account' }}</button>
            </form>

            <div class="auth-links">
                {{ $footerText ?? '' }}
            </div>
        </div>
    </div>
</div>
@endsection

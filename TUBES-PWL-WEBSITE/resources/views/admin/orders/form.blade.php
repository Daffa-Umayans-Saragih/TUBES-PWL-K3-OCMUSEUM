@extends('admin.layout.layout')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0" style="color: #333;">{{ $title }}</h1>
            <p class="text-muted mt-1">{{ $subtitle }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                ← Back to Orders
            </a>
        </div>
    </div>

    <!-- Breadcrumbs -->
    @if($breadcrumbs)
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-light px-3 py-2 rounded">
                @foreach($breadcrumbs as $crumb)
                    @if($crumb['isCurrent'] ?? false)
                        <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $crumb['href'] }}" style="color: #2196F3;">{{ $crumb['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    <!-- Alerts -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form -->
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $isEdit ? route('admin.orders.update', $order->order_id) : route('admin.orders.store') }}" 
                  method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row">
                    <!-- Order Code -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="order_code" class="form-label">Order Code *</label>
                            <input type="text" id="order_code" name="order_code" class="form-control @error('order_code') is-invalid @enderror" 
                                   value="{{ old('order_code', $order?->order_code) }}" required>
                            @error('order_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Order Type -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="order_type" class="form-label">Order Type *</label>
                            <select id="order_type" name="order_type" class="form-control @error('order_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                @foreach($order_types as $value => $label)
                                    <option value="{{ $value }}" {{ old('order_type', $order?->order_type) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('order_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- User -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="user_id" class="form-label">Customer (User)</label>
                            <select id="user_id" name="user_id" class="form-control @error('user_id') is-invalid @enderror">
                                <option value="">-- Select User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" 
                                            {{ old('user_id', $order?->user_id) == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave empty if using guest customer</small>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Guest -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="guest_id" class="form-label">Customer (Guest)</label>
                            <select id="guest_id" name="guest_id" class="form-control @error('guest_id') is-invalid @enderror">
                                <option value="">-- Select Guest --</option>
                                @foreach($guests as $guest)
                                    <option value="{{ $guest->guest_id }}" 
                                            {{ old('guest_id', $order?->guest_id) == $guest->guest_id ? 'selected' : '' }}>
                                        {{ $guest->email }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave empty if using registered user</small>
                            @error('guest_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Order Date -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="order_date" class="form-label">Order Date *</label>
                            <input type="datetime-local" id="order_date" name="order_date" 
                                   class="form-control @error('order_date') is-invalid @enderror" 
                                   value="{{ old('order_date', $order?->order_date?->format('Y-m-d\TH:i')) }}" required>
                            @error('order_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Expired At -->
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="expired_at" class="form-label">Expiration Date</label>
                            <input type="datetime-local" id="expired_at" name="expired_at" 
                                   class="form-control @error('expired_at') is-invalid @enderror" 
                                   value="{{ old('expired_at', $order?->expired_at?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">When this order expires</small>
                            @error('expired_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="form-group mb-4">
                    <label for="total_amount" class="form-label">Total Amount *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="total_amount" name="total_amount" 
                               class="form-control @error('total_amount') is-invalid @enderror" 
                               step="0.01" min="0" 
                               value="{{ old('total_amount', $order?->total_amount) }}" required>
                        @error('total_amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-group d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ $isEdit ? 'Update Order' : 'Create Order' }}
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-control, .form-select {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2196F3;
        box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #2196F3;
        color: white;
    }

    .btn-primary:hover {
        background-color: #1976D2;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }

    .btn-secondary:hover {
        background-color: #e0e0e0;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>
@endsection

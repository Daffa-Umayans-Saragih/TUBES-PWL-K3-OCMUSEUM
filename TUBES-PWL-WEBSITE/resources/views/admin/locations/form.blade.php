@extends('admin.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="admin-section">
        <!-- Breadcrumbs -->
        <div class="admin-breadcrumbs">
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb['isCurrent'] ?? false)
                    <span class="breadcrumb-current">{{ $breadcrumb['label'] }}</span>
                @else
                    <a href="{{ $breadcrumb['href'] }}" class="breadcrumb-link">{{ $breadcrumb['label'] }}</a>
                @endif
            @endforeach
        </div>

        <!-- Header -->
        <div class="admin-form-header">
            <h2 class="admin-title">{{ $title }}</h2>
            <p class="admin-subtitle">{{ $subtitle }}</p>
        </div>

        <!-- Form -->
        <div class="admin-form-container">
            <form action="{{ $isEdit ? route('admin.locations.update', $location) : route('admin.locations.store') }}" method="POST" class="admin-form">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <!-- Location Name -->
                <div class="form-group">
                    <label for="location_name" class="form-label">Location Name <span class="required">*</span></label>
                    <input type="text" id="location_name" name="location_name" class="form-control @error('location_name') is-invalid @enderror"
                        placeholder="Enter location name"
                        value="{{ old('location_name', $location?->location_name ?? '') }}" required>
                    @error('location_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                        placeholder="Enter location address" rows="3">{{ old('address', $location?->address ?? '') }}</textarea>
                    @error('address')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Capacity Limit -->
                <div class="form-group">
                    <label for="capacity_limit" class="form-label">Capacity Limit</label>
                    <input type="number" id="capacity_limit" name="capacity_limit" class="form-control @error('capacity_limit') is-invalid @enderror"
                        placeholder="Enter capacity limit (leave empty for unlimited)"
                        value="{{ old('capacity_limit', $location?->capacity_limit ?? '') }}" min="0">
                    @error('capacity_limit')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="form-group">
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Update Location' : 'Create Location' }}
                        </button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .admin-form-header {
            margin-bottom: 30px;
        }

        .admin-form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        .admin-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .required {
            color: #dc3545;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        textarea.form-control {
            resize: vertical;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.2s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
@endsection

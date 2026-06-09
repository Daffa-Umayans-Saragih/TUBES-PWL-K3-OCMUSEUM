@extends('admin.layout.layout')

@section('admin-title')
    Edit User
@endsection

@section('admin-content')
<div class="admin-page-section" style="max-width: 800px; margin: 0 auto; padding: 2rem 1.5rem;">
    <!-- Breadcrumbs -->
    <div class="admin-breadcrumbs" style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 2rem; font-size: 0.9rem;">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb['isCurrent'] ?? false)
                <span style="color: var(--color-text-muted, #777);">{{ $breadcrumb['label'] }}</span>
            @else
                <a href="{{ $breadcrumb['href'] }}" style="text-decoration: none; color: var(--color-primary, #2196F3); font-weight: 500;">{{ $breadcrumb['label'] }}</a>
                <span style="color: #ccc;">/</span>
            @endif
        @endforeach
    </div>

    <!-- Header -->
    <div class="page-header" style="margin-bottom: 2.5rem; border-bottom: 1px solid #eaeaea; padding-bottom: 1.5rem;">
        <h1 style="font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #111;">{{ $title }}</h1>
        <p style="font-size: 1rem; color: #666; margin: 0;">{{ $subtitle }} (ID: {{ $id }} from {{ $source }})</p>
    </div>

    <!-- Form Container -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden;">
        <form action="{{ route('admin.users.update', $id) }}" method="POST" style="padding: 2.5rem; margin: 0;">
            @csrf
            @method('PUT')

            <input type="hidden" name="source" value="{{ $source }}">

            <!-- Name Fields (Row) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label for="first_name" style="display: block; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.5rem; color: #333;">First Name <span style="color: #f44336;">*</span></label>
                    <input type="text" id="first_name" name="first_name" 
                           value="{{ old('first_name', $firstName) }}" 
                           placeholder="Enter first name" 
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s; {{ !auth()->user()->isSuperAdmin() ? 'background-color: #f5f5f5;' : '' }}" 
                           {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }} required>
                    @error('first_name')
                        <p style="color: #f44336; font-size: 0.85rem; margin: 0.4rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_name" style="display: block; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.5rem; color: #333;">Last Name</label>
                    <input type="text" id="last_name" name="last_name" 
                           value="{{ old('last_name', $lastName) }}" 
                           placeholder="Enter last name" 
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s; {{ !auth()->user()->isSuperAdmin() ? 'background-color: #f5f5f5;' : '' }}"
                           {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                    @error('last_name')
                        <p style="color: #f44336; font-size: 0.85rem; margin: 0.4rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.5rem; color: #333;">Email Address <span style="color: #f44336;">*</span></label>
                <input type="email" id="email" name="email" 
                       value="{{ old('email', $email) }}" 
                       placeholder="Enter email address" 
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s; {{ !auth()->user()->isSuperAdmin() ? 'background-color: #f5f5f5;' : '' }}" 
                       {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }} required>
                @error('email')
                    <p style="color: #f44336; font-size: 0.85rem; margin: 0.4rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role & Privileges (Only visible for non-Guest users) -->
            @if ($source === 'Users')
                <div style="margin-bottom: 2.5rem; padding: 1.25rem; background: #f9f9f9; border-radius: 8px; border: 1px solid #eaeaea;">
                    <h3 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600; color: #333;">Administrative Privileges</h3>
                    <label style="display: flex; align-items: center; gap: 0.75rem; {{ !auth()->user()->isSuperAdmin() ? 'cursor: not-allowed; opacity: 0.7;' : 'cursor: pointer;' }} user-select: none;">
                        <input type="hidden" name="is_admin" value="0" {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                        <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $is_admin) ? 'checked' : '' }} style="width: 18px; height: 18px; {{ !auth()->user()->isSuperAdmin() ? 'cursor: not-allowed;' : 'cursor: pointer;' }}" {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                        <span style="font-size: 0.95rem; color: #444; font-weight: 500;">Grant full administrative access (Is Admin)</span>
                    </label>
                    @error('is_admin')
                        <p style="color: #f44336; font-size: 0.85rem; margin: 0.4rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div style="margin-bottom: 2.5rem; padding: 1.25rem; background: #fafafa; border-radius: 8px; border: 1px solid #eee; color: #666;">
                    <p style="margin: 0; font-size: 0.9rem; font-style: italic;">Note: Guest entities are static accounts stored in the guests database table and do not support role assignment.</p>
                </div>
            @endif

            <!-- Form Actions -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid #eaeaea; padding-top: 2rem;">
                <a href="{{ route('admin.users.index') }}" class="btn-cancel" 
                   style="text-decoration: none; padding: 0.75rem 1.5rem; border: 1px solid #ccc; border-radius: 8px; background: white; color: #555; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    {{ auth()->user()->isSuperAdmin() ? 'Cancel' : 'Back' }}
                </a>
                @if(auth()->user()->isSuperAdmin())
                <button type="submit" 
                        style="padding: 0.75rem 2rem; border: none; border-radius: 8px; background: #2196F3; color: white; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);">
                    Save Changes
                </button>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
    input:focus {
        border-color: #2196F3 !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
    }
</style>
@endsection

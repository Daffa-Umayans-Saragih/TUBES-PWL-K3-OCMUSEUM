@extends('layouts.app')

@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/admin/layout/layout.css')
@endpush

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-logo">
            <h2>MET Admin</h2>
        </div>

        <nav class="admin-nav">
            <li class="admin-nav-item">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                    📊 Dashboard
                </a>
            </li>

            <li class="admin-nav-section">
                <div class="admin-nav-section-title">Collection</div>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.art.index') }}" class="admin-nav-link @if(request()->routeIs('admin.art.*')) active @endif">
                    🎨 Artworks
                </a>
            </li>

            <li class="admin-nav-section">
                <div class="admin-nav-section-title">System</div>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('home') }}" class="admin-nav-link">
                    ← Back to Site
                </a>
            </li>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <h1 style="margin: 0; font-size: 1.5rem;">@yield('admin-title', 'Admin Dashboard')</h1>
            <div class="admin-topbar-user">
                <div class="admin-topbar-user-info">
                    <div class="admin-topbar-user-name">{{ optional(Auth::user()->profile)->first_name ?? 'Admin' }} {{ optional(Auth::user()->profile)->last_name ?? '' }}</div>
                    <div class="admin-topbar-user-role">Administrator</div>
                </div>
                <form action="{{ route('account.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>
        </div>

        <!-- Content Area -->
        <div class="admin-content-wrapper">
            @yield('admin-content')
        </div>
    </div>
</div>

@extends('layouts.admin')

@push('styles')
@vite('resources/css/admin/layout/layout.css')
@endpush

@section('content')
<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-logo">
            <h2>MET Admin</h2>
        </div>

        <nav class="admin-nav">
            <!-- Core Section -->
            <li class="admin-nav-item">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                    📊 Dashboard
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.tickets.index') }}" class="admin-nav-link @if(request()->routeIs('admin.tickets.*')) active @endif">
                    🎫 Tickets
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.orders.index') }}" class="admin-nav-link @if(request()->routeIs('admin.orders.*')) active @endif">
                    📦 Orders
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.payments.index') }}" class="admin-nav-link @if(request()->routeIs('admin.payments.*')) active @endif">
                    💳 Payments
                </a>
            </li>

            <!-- Analytics Section -->
            @if(auth()->user()->hasRole(['admin', 'superadmin']))
            <li class="admin-nav-section">
                <div class="admin-nav-section-title">Analytics</div>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.ticket-analytics.index') }}" class="admin-nav-link @if(request()->routeIs('admin.ticket-analytics.*')) active @endif">
                    📈 Ticket Analytics
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.payment.index') }}" class="admin-nav-link @if(request()->routeIs('admin.payment.*')) active @endif">
                    📊 Payment Dashboard
                </a>
            </li>

            <!-- Collection Section -->
            <li class="admin-nav-section">
                <div class="admin-nav-section-title">Collection</div>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.artworks.index') }}" class="admin-nav-link @if(request()->routeIs('admin.artworks.*')) active @endif">
                    🎨 Artworks
                </a>
            </li>

            <!-- Community Section -->
            <li class="admin-nav-section">
                <div class="admin-nav-section-title">Community</div>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.categories.index') }}" class="admin-nav-link @if(request()->routeIs('admin.categories.*')) active @endif">
                    🏷️ Emotion Categories
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.posts.index') }}" class="admin-nav-link @if(request()->routeIs('admin.posts.*')) active @endif">
                    💬 Visitor Stories
                </a>
            </li>

            <!-- Management Section -->
            <li class="admin-nav-section">
                <div class="admin-nav-section-title">Management</div>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.tickets.management') }}" class="admin-nav-link @if(request()->routeIs('admin.tickets.management')) active @endif">
                    🎫 Ticket Management
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="{{ route('admin.users.index') }}" class="admin-nav-link @if(request()->routeIs('admin.users.*')) active @endif">
                    👥 Users
                </a>
            </li>
            @endif

            <!-- System Section -->
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
    <div class="admin-sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="btn-sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">☰</button>
                <h1 style="margin: 0; font-size: 1.5rem;">@yield('admin-title', 'Admin Dashboard')</h1>
            </div>
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

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.admin-sidebar-overlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }
</script>
@endsection

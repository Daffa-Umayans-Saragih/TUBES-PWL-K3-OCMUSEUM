<!-- Admin Sidebar Navigation Component -->
@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/components/admin-sidebar.css')
@endpush

<aside class="admin-sidebar">
    <!-- Brand -->
    <div class="admin-sidebar-brand">
        <h2>MET ADMIN</h2>
        <div class="subtitle">Museum Management</div>
    </div>

    <!-- Navigation -->
    <nav class="admin-nav">
        <!-- Main Section -->
        <li class="admin-nav-section">
            <div class="admin-nav-section-title">Main</div>
        </li>
        <li class="admin-nav-item">
            <a href="{{ route('admin.dashboard') }}"
               class="admin-nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                <span class="admin-nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Collection Section -->
        <li class="admin-nav-section">
            <div class="admin-nav-section-title">Collection</div>
        </li>
        <li class="admin-nav-item">
            <a href="{{ route('admin.art.index') }}"
               class="admin-nav-link @if(request()->routeIs('admin.art.*')) active @endif">
                <span class="admin-nav-icon">🎨</span>
                <span>Artworks</span>
            </a>
        </li>

        <!-- System Section -->
        <li class="admin-nav-section">
            <div class="admin-nav-section-title">System</div>
        </li>
        <li class="admin-nav-item">
            <a href="{{ route('home') }}" class="admin-nav-link">
                <span class="admin-nav-icon">←</span>
                <span>Back to Site</span>
            </a>
        </li>
    </nav>
</aside>

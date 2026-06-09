<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - MET Museum</title>

@vite('resources/css/app.css')
@vite('resources/css/layouts/admin.css')
@vite('resources/js/app.js')
    @yield('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Component -->
        <x-admin-sidebar />

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <h1 class="admin-topbar-title">@yield('page_title', 'Admin')</h1>
                <div class="admin-topbar-user">
                    <div>
                        <div class="admin-topbar-user-name">{{ Auth::user()->name }}</div>
                        <div class="admin-topbar-user-role">{{ Auth::user()->role_admin ? ucfirst(Auth::user()->role_admin) : 'User' }}</div>
                    </div>
                    <form action="{{ route('account.logout') }}" method="POST" class="form-no-margin">
                        @csrf
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Our Civilization Museum of Art' }}</title>

@vite('resources/css/app.css')
@vite('resources/css/layouts/app.css')
@vite('resources/js/app.js')

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    @include('components.navbar-sub')

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer-sub')

    <!-- Alpine.js — loaded before page scripts so x-data works everywhere -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
</html>


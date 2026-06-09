<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Our Civilization of Art - Collections, Exhibitions, and Visits">
    <title>@yield('title', 'The OC Museum') - OC Museum</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Sora', sans-serif;
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    @include('components.navbar-modern')

    <main>
        @yield('content')
    </main>

    @include('components.footer-modern')
</body>
</html>

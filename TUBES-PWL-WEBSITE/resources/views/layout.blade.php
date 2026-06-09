<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Our Civilization Museum - Collections, Exhibitions, and Visits">

        <title>@yield('title', 'Our Civilization Museum') - OC Museum</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Vite CSS & JS -->
@vite('resources/css/app.css')
@vite('resources/css/layout.css')
@vite('resources/js/app.js')
    </head>
    <body>
        @include('components.navbar')

        <main>
            @yield('content')
        </main>

        @include('components.footer')
    </body>
</html>

<!DOCTYPE html>
<html lang="en">
@extends('layouts.main')

@section('title', 'Home')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>OC Fifth Avenue - Our Civilization of Art</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-white antialiased">
        <div class="max-w-screen-xl mx-auto px-6 md:px-10 py-4 flex items-center bg-white group cursor-default">
            <a href="/" class="text-black group-hover:text-black transition-all duration-200 flex items-center">
                <svg class="w-3 h-3 transition-all duration-200 fill-none hover:fill-black" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>

            <span
                class="mx-2.5 text-black font-extralight text-lg group-hover:text-black transition-colors duration-200">/</span>

            <a href="{{ route('plan-your-visit.index') }}" class="no-underline">
                <h1 class="text-[13px] font-semibold text-black tracking-tight no-underline hover:underline cursor-pointer">
                    Plan Your Visit
                </h1>
            </a>

            <span
                class="mx-2.5 text-black font-extralight text-lg group-hover:text-black transition-colors duration-200">/</span>

            <h1 class="text-[13px] font-bold text-black tracking-tight cursor-text">
                OC Fifth Avenue
            </h1>
        </div>

        <section class="main-section">
            <!-- Bagian Title -->
            <h1 class="main-title">
                OC Fifth Avenue
            </h1>

            <!-- Bagian Subtitle -->
            <p class="main-subtitle">
                Over 5,000 years of art from around the world.
            </p>
        </section>

        <div class="max-w-screen-xl mx-auto px-6 md:px-10">

            <!-- card gallery closures -->
            <div class=" rounded-md flex items-start gap-2">
                {{-- Ikon Info --}}


            </div>

            <!-- section locations and hours -->
            <section id="locations" class="py-8">
                <h3 class="section-title">Locations and Hours</h3>
                <!-- the met fifth avenue -->
                <div class="location-flex-container">
                    {{-- Sisi Kiri --}}
                    <div class="location-left-side">
                        <img src="{{ asset('images/visit-location.avif') }}" class="location-main-img">

                        <div class="address-map-wrapper">
                            <p class="visit-address">1000 Fifth Avenue, New York, NY 10028</p>
                        </div>
                    </div>
                    <!-- jadwal buka -->
                    <div class="location-right-side">
                        <div class="status-open-box">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>The museum is open today 10 am–5 pm</p>
                        </div>

                        <div class="hours-table">
                            <div class="hours-row"><span>Sunday</span><span>10 am–5 pm</span></div>
                            <div class="hours-row"><span>Monday</span><span>10 am–5 pm</span></div>
                            <div class="hours-row"><span>Tuesday</span><span>10 am–5 pm</span></div>
                            <div class="hours-row"><span>Wednesday</span><span>Closed</span></div>
                            <div class="hours-row"><span>Thursday</span><span>10 am–5 pm</span></div>
                            <div class="hours-row"><span>Friday</span><span>10 am–9 pm</span></div>
                            <div class="hours-row"><span>Saturday</span><span>10 am–9 pm</span></div>
                        </div>

                        <p class="closure-note mb-4">
                            Closed Thanksgiving Day, December 25, January 1, and the first Monday in May.
                        </p>
                    </div>
                </div>
                <div class="button-ticket-container-group">
                    <button onclick="window.location='{{ route('ticket.admission') }}'"
                        class="bg-blue-950 hover:bg-blue-900 text-white font-semibold py-2 px-4 rounded mb-3 btn-ticket-responsive">
                        Buy tickets
                    </button>
                </div>
            </section>

            <!-- Baris 2: Teks Kiri, Gambar Kanan (Selang-seling) -->
            <div class="cloisters-card">
                <!-- Di layar besar (md), gambar ini muncul terakhir (di kanan) -->
                <div class="md:order-last">
                    <img src="{{ asset('images/the met cloisters.avif') }}" alt="Group Visit" class="cloisters-image">
                </div>
                <!-- Di layar besar (md), teks ini muncul pertama -->
                <div class="lg:order-first">
                    <h3 class="cloisters-title">OC Cloisters</h3>
                    <p class="cloisters-desc">
                        See information about visiting OC Cloisters, including directions, admission prices, and dining
                        options.
                    </p>
                    <a href="{{ route('cloister.learn.more') }}" class="footer-action-link">
                        Read more <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>

    </body>
@endsection

</html>
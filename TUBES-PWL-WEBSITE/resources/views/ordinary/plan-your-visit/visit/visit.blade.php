<!DOCTYPE html>
<html lang="en">
@extends('layouts.main')

@section('title', 'Home')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Plan Your Visit - Our Civilization</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @push('styles')
            @vite('resources/css/ordinary/plan-your-visit/visit/visit.css')
        @endpush
    </head>

    <!-- Navbar -->

    <body class="bg-white antialiased">
        <div class="max-w-screen-xl mx-auto px-6 md:px-10 py-4 flex items-center bg-white group cursor-default">
            <a href="/" class="text-gblack group-hover:text-black transition-all duration-200 flex items-center">
                <svg class="w-3 h-3 transition-all duration-200 fill-none hover:fill-black" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>

            <span
                class="mx-2.5 text-black font-extralight text-lg group-hover:text-black transition-colors duration-200">/</span>

            <h1 class="text-[13px] font-semibold text-black tracking-tight">
                Plan Your Visit
            </h1>
        </div>

        <!-- Hero Image -->
        <img src="{{ asset('images/museumasli.avif') }}" alt="Our Civilization Entrance"
            class="w-full h-[250px] md:h-[450px] lg:h-[550px] object-cover">

        <div class="max-w-screen-xl mx-auto px-6 md:px-10">

            <!-- Sub judul -->
            <div class="py-6">
                <h2 class="page-main-title">Plan Your Visit</h2>
            </div>

            <!-- scroll bar -->
            <div class="jump-to-container top-0 bg-white z-10">
                <div class="scroll-menu no-scrollbar">
                    <span class="text-gray-400 font-normal font-semibold">Jump to:</span>
                    <a href="#locations" class="jump-link active">Locations and Hours</a>
                    <a href="#tickets" class="jump-link">Tickets</a>
                    <a href="#visiting" class="jump-link">Visiting Guides</a>
                    <a href="#Membership" class="jump-link">Membership</a>
                </div>
            </div>

            <!-- section locations and hours -->
            <section id="locations" class="py-8">
                <h3 class="section-title">Locations and Hours</h3>

                <div class="mt-5">
                    <h4 class="location-name">The OC Fifth Avenue</h4>
                    <p class="location-desc">Over 5,000 years of art from around the world.</p>
                    <a href="{{ route('learn.more') }}" class="clickable-link mt-1 inline-block">Learn more about The OC
                        Fifth Avenue</a>
                </div>

                <!-- the oc fifth avenue -->
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

                        <p class="closure-note">
                            Closed Thanksgiving Day, December 25, January 1, and the first Monday in May.
                        </p>
                    </div>
                </div>
                <!-- the oc cloisters -->
                <div class="mt-5">
                    <h4 class="location-name">The OC Cloisters</h4>
                    <p class="location-desc">Art, architecture, and gardens of medieval Europe.</p>
                    <a href="{{ route('cloister.learn.more') }}" class="clickable-link mt-1 inline-block">Learn more about
                        The OC Cloisters</a>
                </div>

                <div class="location-flex-container">
                    <div class="location-left-side">
                        <img src="{{ asset('images/the met cloisters.avif') }}" class="location-main-img">

                        <div class="address-map-wrapper">
                            <p class="visit-address">99 Margaret Corbin Drive, New York, NY 10040</p>
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
                            <div class="hours-row"><span>Friday</span><span>10 am–5 pm</span></div>
                            <div class="hours-row"><span>Saturday</span><span>10 am–5 pm</span></div>
                        </div>

                        <p class="closure-note">
                            Closed Thanksgiving Day, December 25, and January 1.
                        </p>
                    </div>
                </div>

                <!-- section tickets -->
                <div class="ticket-flex-container">
                    <div class="ticket-left-side">
                        <section id="tickets" class="py-8">
                            <h3 class="section-title">Tickets</h3>

                            <div class="mt-5">
                                <h4 class="ticket-name">Suggested Admission</h4>
                                <p class="ticket-desc">New York State residents and New York, New Jersey, and Connecticut
                                    students:
                                    the amount you pay for admission is up to you, but you must pay something ($00.01
                                    minimum
                                    per
                                    ticket).</p>
                                <!-- card pay what you wish -->
                                <div
                                    class="bg-[#eeeded] border border-[#F0EFEF] p-6 my-8 rounded-md flex items-start gap-2">
                                    <h4 class="text-base font-normal text-gray-600">To buy pay-what-you-wish tickets online,
                                        you
                                        must have a New York State billing address. New Jersey and Connecticut students can
                                        only
                                        buy
                                        pay-what-you-wish tickets in person with valid student ID. Accepted forms of
                                        residency
                                        verification include New York State driver’s license, New York State identification
                                        card,
                                        IDNYC, current bill or statement with a New York State address, student ID, and New
                                        York
                                        library card.</h4>
                                </div>

                                <!-- card general admission -->
                                <div class="status-ticket-box">
                                    <p>General Admission</p>
                                </div>
                                <div class="ticket-row"><span>NY residents</span><span class="text-black font-medium">Pay
                                        what
                                        you
                                        wish</span></div>
                                <div class="ticket-row"><span>NY, NJ, and CT students</span><span
                                        class="text-black font-medium">Pay
                                        what you wish</span></div>
                                <div class="ticket-row"><span>Adults</span><span class="text-black font-medium">$30</span>
                                </div>
                                <div class="ticket-row"><span>Seniors (65 and over)</span><span
                                        class="text-black  font-medium">$22</span></div>
                                <div class="ticket-row"><span>Visitors with a disability (in-person only)</span><span
                                        class="text-black font-medium">$22</span></div>
                                <div class="ticket-row"><span>Students</span><span class="text-black font-medium">$17</span>
                                </div>
                                <div class="ticket-row"><span>Children (12 and under)</span><span
                                        class="text-black font-medium">Free</span>
                                </div>
                                <div class="ticket-row"><span>Members and Patrons</span><span
                                        class="text-black font-medium">Free</span>
                                </div>
                                <div class="ticket-row"><span>Care partner of a visitor with a disability (in-person
                                        only)</span><span class="text-black font-medium">Free</span></div>
                            </div>
                    </div>

                    <!-- informasi tambahan tiket -->
                    <div class="ticket-right-side">
                        <div class="mt-20">
                            <h4 class="section-subtitle">Booking online</h4>
                            <p class="booking-desc">Tickets can be purchased online, but advance tickets are not
                                required. By purchasing your tickets online, you may proceed directly to any
                                gallery
                                entrance.</p>
                        </div>

                        <div class="mt-5">
                            <h4 class="section-subtitle">Include with your ticket</h4>
                            <p class="booking-desc">All tickets include exhibitions and same-day entry to both
                                OC
                                locations for the date printed on your ticket.</p>
                        </div>

                        <div class="mt-5">
                            <h4 class="section-subtitle">Member benefits</h4>
                            <p class="booking-desc">Members and Patrons enjoy free and unlimited entry. Scan
                                your
                                Membership card at any gallery entrance for admission. Need help? Visit the
                                Membership desk in the Great Hall. Membership starts at $90 per year. <a
                                    href="{{ route('membership.index') }}" class="clickable-link">Learn more about
                                    Membership.</a></p>
                        </div>
                    </div>
                </div>
            </section>

            <!--button buy tickets and become a member -->
            <div class="button-container-group">
                <button onclick="window.location='{{ route('ticket.admission') }}'"
                    class="btn-responsive bg-[#07244a] text-white">
                    Buy tickets
                </button>
                <button onclick="window.location='{{ route('membership.index') }}'" class="btn-responsive-secondary">
                    Become a Member
                </button>
            </div>
            </section>

            <!-- visiting guide -->
            <section id="visiting" class="max-w-7xl mx-auto px-4 py-10">
                <h3 class="section-title text-2xl font-bold mb-6">Visiting Guides</h3>
                <!-- images and descriptions -->
                <div class="visiting-container">
                    <div class="visiting-left-side cursor-pointer"
                        onclick="window.location='{{ route('visit.accessibility') }}'">
                        <img src="{{ asset('images/visit-guide.jpg') }}" class="visiting-main-img">
                        <h4 class="visiting-title">Accessibility</h4>
                        <p class="visiting-desc">The Museum is committed to making its collection, buildings, programs, and
                            services accessible to all audiences.</p>
                    </div>
                </div>
            </section>

            <!-- Membership -->
            <section id="Membership" class="membership-section">
                <div class="membership-container">

                    <div class="membership-content">
                        <h3 class="membership-title">Membership</h3>

                        <div class="membership-item">
                            <h4 class="membership-subtitle">Discover more of The OC</h4>
                            <p class="membership-description">
                                Become a OC Member and enjoy an enhanced experience with unlimited free
                                admission, special access to exhibitions, and invitations to Members-only events.
                            </p>
                            <button onclick="window.location='{{ route('membership.index') }}'" class="membership-btn">
                                <span>Join today</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="membership-image-wrapper">
                        <img src="{{ asset('images/member-image.avif') }}" class="membership-img" alt="Membership">
                    </div>

                </div>
            </section>
        </div>
        </div>

        </div>

    </body>
@endsection

</html>
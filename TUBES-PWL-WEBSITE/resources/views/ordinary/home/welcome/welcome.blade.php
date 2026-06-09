@extends('layouts.main')

@section('title', 'Home')

@section('content')
    <section class="relative min-h-[calc(100vh-6rem)] flex items-center justify-center bg-cover bg-center bg-no-repeat"
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('{{ asset('images/WhatsApp Image 2026-05-09 at 02.03.14.jpg')}}');">

        <div class="w-full max-w-4xl px-6 py-24 text-center relative z-10">
            <h1 class="text-6xl md:text-8xl font-serif font-bold tracking-tight text-white leading-tight">
                Welcome to Our Civilization
            </h1>

            <div class="mt-12">
                <a href="{{ route('plan-your-visit.index') }}"
                    class="inline-flex items-center justify-center border-2 border-white px-8 py-4 text-lg font-bold text-white transition hover:bg-white hover:text-blue-900">
                    Plan your visit
                </a>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">

            <section id="Views" class="max-w-full mx-auto py-10">
                <div class="px-5">
                    <div class="view-map-wrapper">
                        <a href="{{ route('art.search', ['on_view' => 1]) }}" class="hover:underline">
                            <h3 class="section-title text-2xl font-bold">Now on View</h3>
                        </a>
                        <a href="{{ route('art.search', ['on_view' => 1]) }}" class="view-map-link">
                            View all
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tombol Navigasi Kiri -->
                <button onclick="document.getElementById('carousel').scrollBy({left: -800, behavior: 'smooth'})"
                    class="nav-btn left-2 lg:left-6 hidden lg:flex opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Tombol Navigasi Kanan -->
                <button onclick="document.getElementById('carousel').scrollBy({left: 800, behavior: 'smooth'})"
                    class="nav-btn right-2 lg:right-6 hidden lg:flex opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div class="view-container">
                    <div id="carousel" class="image-scroller">
                        @forelse($nowOnView as $item)
                        <div class="scroller-item">
                            <a href="{{ route('art.show', $item->art_work_id) }}" class="block hover:opacity-90 transition">
                            <div class="image-wrapper">
                                <img src="{{ $item->resolved_image_url ?? asset('images/image1.jpg') }}" alt="{{ $item->title }}">
                            </div>
                            <div class="content-wrapper">
                                <h4 class="view-title">{{ \Illuminate\Support\Str::limit($item->title, 60) }}</h4>
                                <p class="view-desc">
                                    @if($item->constituents->isNotEmpty())
                                        {{ $item->constituents->first()->display_name }}
                                    @elseif($item->object_date_display)
                                        {{ $item->object_date_display }}
                                    @else
                                        On View
                                    @endif
                                </p>
                            </div>
                            </a>
                        </div>
                        @empty
                        <p class="text-gray-500">No artworks on view currently.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="bg-white py-16">
                <div class="max-w-7xl mx-auto px-6">
                    <h2 class="text-3xl font-semibold text-gray-900">Locations and Hours</h2>

                    <div class="mt-10 grid gap-8 lg:grid-cols-2">
                        <article class="overflow-hidden rounded-3xl bg-white shadow-sm">
                            <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                                <img src="{{ asset('images/visit-location.avif')}}" alt="The Met Fifth Avenue"
                                    class="h-full w-full object-cover">
                            </div>
                            
                        <a href="{{ route('learn.more') }}" class="block hover:opacity-90 transition">
                            <div class="p-8">
                                <h3 class="text-2xl font-semibold text-gray-900">The OC Fifth Avenue</h3>

                                <p class="mt-4 text-sm text-gray-600">
                                    <strong>Hours:</strong> Sunday–Tuesday and Thursday: 10 am–5 pm
                                </p>

                                <p class="text-sm text-gray-600">
                                    <strong>Extended Hours:</strong> Friday and Saturday: 10 am–9 pm
                                </p>

                                <p class="text-sm text-gray-600">
                                    <strong>Closed:</strong> Wednesday
                                </p>

                                <p class="mt-4 text-xs text-gray-500">
                                    Closed Thanksgiving Day, December 25, January 1, and the first Monday in May.
                                </p>
                            </a>
                        </div>
                        </article>

                        <article class="overflow-hidden rounded-3xl bg-white shadow-sm">
                            <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                                <img src="{{ asset('images/the met cloisters.avif') }}" alt="The Met Cloisters"
                                    class="h-full w-full object-cover">
                            </div>
                            
                        <a href="{{ route('cloister.learn.more') }}" class="block hover:opacity-90 transition">
                            <div class="p-8">
                                <h3 class="text-2xl font-semibold text-gray-900">The OC Cloisters</h3>

                                <p class="mt-4 text-sm text-gray-600">
                                    <strong>Hours:</strong> Thursday–Tuesday: 10 am–5 pm
                                </p>

                                <p class="mt-4 text-xs text-gray-500">
                                    Closed Thanksgiving Day, December 25, and January 1.
                                </p>
                            </a>
                        </div>
                        </article>
                    </div>
                </div>
            </section>

            <section class="bg-white py-16">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="grid gap-16 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                        <div class="max-w-xl">
                            <h3 class="text-2xl font-semibold text-gray-900">Become a Member Today</h3>
                            <p class="mt-6 text-lg text-gray-600 leading-8">Members get special access to Raphael: Sublime
                                Poetry
                                with Weekend Member Mornings and special Evening Viewings. Join by June 28 for a chance to
                                win the
                                ultimate keepsake: a curator-signed exhibition catalogue.</p>
                            <a href="{{ route('member.membership') }}"
                                class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-gray-900 hover:text-blue-900">
                                Join now
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                        <div class="overflow-hidden rounded-sm bg-black">
                            <img src="{{ asset('images/becomeMember.jpg')}}"
                                alt="Circular painting" class="h-full w-full object-cover">
                        </div>
                    </div>
                </div>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const track = document.getElementById('now-on-view-track');
                    const prev = document.getElementById('now-on-view-prev');
                    const next = document.getElementById('now-on-view-next');
                    const cards = track.querySelectorAll('article');
                    let index = 0;
                    const visibleCount = 3;
                    const total = cards.length;

                    function updatePosition() {
                        const cardWidth = cards[0].getBoundingClientRect().width + 24;
                        const distance = Math.min(index, total - visibleCount) * cardWidth;
                        track.style.transform = `translateX(-${distance}px)`;
                    }

                    prev.addEventListener('click', () => {
                        index = Math.max(index - 1, 0);
                        updatePosition();
                    });

                    next.addEventListener('click', () => {
                        index = Math.min(index + 1, total - visibleCount);
                        updatePosition();
                    });
                });
            </script>
@endsection
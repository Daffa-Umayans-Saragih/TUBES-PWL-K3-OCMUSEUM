@extends('layouts.main')

@section('title', 'Curatorial Areas')

@section('content')

    <!-- BREADCRUMB -->
    <div class="w-full border-b border-[#e5e5e5] bg-white">
        <div class="max-w-[1600px] mx-auto px-16 h-[70px] flex items-center gap-3">

            <!-- HOME -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px] text-black hover:fill-black" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 10.75L12 3l9 7.75V20a1 1 0 01-1 1h-5.5v-6h-5v6H4a1 1 0 01-1-1v-9.25z" />
            </svg>

            <span class="text-[#777]">/</span>

            <a href="{{ route('about') }}" class="text-[14px] font-semibold text-black hover:underline">
                About OC
            </a>

            <span class="text-[#777]">/</span>

            <a href="#" class="text-[14px] font-semibold text-black">
                Departments
            </a>

        </div>

        <!-- MAIN CONTENT -->
        <div class="max-w-[1560px] mx-auto px-16 pt-2 pb-19">

            <!-- TITLE -->
            <h1 class="text-[40px] tracking-[-2px] leading-none font-semibold text-[#111]">
                Departments
            </h1>

            <!-- LINE -->
            <div class="w-full h-[1px] bg-[#dddddd] mt-16"></div>

            <!-- JUMP -->
            <div class="flex items-center gap-12 mt-12 text-[15px]">

                <span class="text-[#666]">
                    Jump to:
                </span>

                <a href="#" class="font-semibold text-[#111]">
                    Curatorial
                </a>

                <a href="#" class="text-[#111] hover:underline">
                    Conservation and Scientific Research
                </a>

            </div>

            <!-- CURATORIAL -->
            <div class="mt-10">

                <h2 class="text-[35px] tracking-[-1.5px] leading-none font-medium text-[#111]">
                    Curatorial
                </h2>

                <!-- DESC -->
                <p class="mt-4 text-[20px] leading-[1.8] text-[#5c5c5c] font-light max-w-[1180px]">
                    Curatorial departments study, exhibit, and care for over two million objects in
                    <br />
                    OC Collection.
                </p>

                <!-- GRID -->
                <div class="grid grid-cols-4 gap-x-8 gap-y-24 mt-14">

                    <a href="{{ route('art.search', ['department[]' => 'Egyptian Art']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/egyptian-art.jpg') }}" alt="Egyptian Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Egyptian Art
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'European Paintings']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/europhean-painting.jpg') }}" alt="European Paintings"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            European Paintings
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'European Sculpture and Decorative Arts']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/european-sculpture.jpg') }}"
                                alt="European Sculpture and Decorative Arts"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            European Sculpture and Decorative Arts
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Greek and Roman Art']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/greek-and-roman.jpg') }}" alt="Greek and Roman Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Greek and Roman Art
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Islamic Art']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/islamic-art.jpg') }}" alt="Islamic Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Islamic Art
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'The Robert Lehman Collection']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/robert-lehman.jpg') }}" alt="The Robert Lehman Collection"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            The Robert Lehman Collection
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Thomas J. Watson Library']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/thomas-j-watson.jpg') }}" alt="Thomas J. Watson Library"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Thomas J. Watson Library
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Medieval Art and The Cloisters']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/medieval-art.jpg') }}" alt="Medieval Art and The Cloisters"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Medieval Art and The Cloisters
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Modern and Contemporary Art']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/modern-contemporary.jpg') }}" alt="Modern and Contemporary Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Modern and Contemporary Art
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Musical Instruments']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/musical-instrument.jpg') }}" alt="Musical Instruments"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Musical Instruments
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Oceanic Art in The Michael C. Rockefeller Wing']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/oceanic-art.jpg') }}"
                                alt="Oceanic Art in The Michael C. Rockefeller Wing"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Oceanic Art in The Michael C. Rockefeller Wing
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Photographs']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/photographs.jpg') }}" alt="Photographs"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Photographs
                        </h4>
                    </a>
                </div>
            </div>

@endsection
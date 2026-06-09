@extends('layouts.main')

@section('title', 'OC Collection')

@section('content')

    <section class="w-full bg-white font-sans">

        <!-- BREADCRUMB -->
        <div class="w-full border-b border-[#e5e5e5] bg-white">
            <div class="max-w-[1600px] mx-auto px-16 h-[84px] flex items-center gap-4">

                <!-- HOME ICON -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px] text-black hover:fill-black" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10.75L12 3l9 7.75V20a1 1 0 01-1 1h-5.5v-6h-5v6H4a1 1 0 01-1-1v-9.25z" />
                </svg>

                <span class="text-black text-[13px] leading-none">/</span>

                <a href="{{ route('art.index') }}" class="text-[14px] font-semibold text-black hover:underline">
                    OC Collection
                </a>

            </div>
        </div>

        <!-- HERO IMAGE -->
        <div class="w-full overflow-hidden">
            <img src="{{ asset('images/met-collection.avif')}}" alt="The OC Collection"
                class="w-full h-[250px] md:h-[250px] lg:h-[550px] object-cover" />
        </div>

        <!-- CONTENT -->
        <div class="max-w-[1600px] mx-auto px-16 pt-10 pb-20">

            <h1 class="text-[88px] md:text-[36px] font-bold text-black tracking-tight leading-none">
                OC Collection
            </h1>
            <p class="mt-2 text-[20px] lg:text-[26px] text-black max-w-[1200px] font-light">
                Travel around the world and across 5,000 years of history through 490,000+ works of art.
            </p>

        </div>

        <section class="w-full bg-white font-sans">

            <div class="max-w-[1550px] mx-auto px-16">

                <!-- SEARCH -->
                <form action="{{ route('art.search') }}" method="GET"
                    class="flex items-center w-full border border-[#cfcfcf]  bg-white h-[58px] rounded-md">

                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search The Collection"
                        class="flex-1 h-full px-6 text-[16px] text-gray-600 placeholder:text-[#9b9b9b] font-extralight outline-none bg-transparent" />

                    <button type="submit"
                        class="w-[90px] h-full border-l border-[#cfcfcf] flex items-center justify-center hover:bg-[#f0f0f0] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#666]" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </button>

                </form>

                <!-- QUICK LINKS -->
                <div class="flex items-center gap-3 mt-7 flex-wrap">

                    <a href="{{ route('art.search') }}"
                        class="flex items-center gap-2 text-[16px] text-[#111] font-medium hover:underline">
                        Advanced Collection Search
                    </a>
                    <span class="text-[25px] leading-none text-[#444]">›</span>

                    <a href="{{ route('art.index') }}"
                        class="flex items-center gap-2 text-[16px] text-[#111] font-medium hover:underline">
                        Highlight
                    </a>
                    <span class="text-[25px] leading-none text-[#444]">›</span>

                    <a href="{{ route('art.index') }}"
                        class="flex items-center gap-2 text-[16px] text-[#111] font-medium hover:underline">
                        Public Domain Images
                    </a>
                    <span class="text-[25px] leading-none text-[#444]">›</span>

                </div>

                <!-- LINE -->
                <div class="w-full h-[1px] bg-[#dddddd] mt-8"></div>

                <!-- TITLE -->
                <h3 class="text-[27px] font-semibold text-black tracking-[-1px] mt-12 mb-2">
                    Browse the Art
                </h3>

                <!-- GRID -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16 mb-20">

                    <a href="{{ route('art.search', ['department[]' => 'African Art in The Michael C. Rockefeller Wing']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/african-wings.jpg') }}" alt="African Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            African Art in The Michael C. Rockefeller Wing
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'The American Wing']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/american-wing.jpg') }}" alt="The American Wing"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>
                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            The American Wing
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Ancient American Art in The Michael C. Rockefeller Wing']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/ancient-american.jpg') }}" alt="Ancient American Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Ancient American Art in The Michael C. Rockefeller Wing
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Ancient West Asian Art']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/ancient-westAsian.jpg') }}" alt="Ancient West Asian Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Ancient West Asian Art
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Arms and Armor']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/arms-and-armor.jpg') }}" alt="Arms and Armor"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Arms and Armor
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Asian Art']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/asian-art.jpg') }}" alt="Asian Art"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Asian Art
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'The Costume Institute']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/costume-institute.jpg') }}" alt="The Costume Institute"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            The Costume Institute
                        </h4>
                    </a>

                    <a href="{{ route('art.search', ['department[]' => 'Drawings and Prints']) }}"
                        class="group cursor-pointer block text-black hover:text-black hover:no-underline">
                        <div class="overflow-hidden">
                            <img src="{{ asset('images/drawing-and-prints.jpg') }}" alt="Drawings and Prints"
                                class="w-full h-[200px] object-cover transition duration-500" />
                        </div>

                        <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                            Drawings and Prints
                        </h4>
                    </a>

                    <!-- SECOND ROW -->

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

        </section>


@endsection
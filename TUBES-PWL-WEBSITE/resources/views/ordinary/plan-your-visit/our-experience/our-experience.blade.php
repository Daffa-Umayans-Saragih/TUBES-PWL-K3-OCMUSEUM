<!DOCTYPE html>
<html lang="en">
@extends('layouts.main')

@section('title', 'Our Experience')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Our Experience - Our Civilization of Art</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-white antialiased">
        <nav class="max-w-screen-xl mx-auto px-6 md:px-10 py-4 flex items-center bg-white" aria-label="Breadcrumb">
            <!-- Icon Home -->
            <a href="/" class="text-black hover:text-black transition-colors duration-200">
                <svg class="w-3 h-3 transition-all duration-200 fill-none hover:fill-black" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>

            <!-- Separator -->
            <span class="mx-2.5 text-black font-extralight text-sm">/</span>

            <!-- Link Plan Your Visit -->
            <a href="/plan-your-visit" class="text-black text-xs hover:underline tracking-wide transition-all duration-200">
                Plan Your Visit
            </a>

            <!-- Separator -->
            <span class="mx-2.5 text-gray-400 font-extralight text-sm">/</span>

            <!-- Active Page -->
            <span class="text-black text-xs font-semibold">Our Experience</span>
        </nav>

        <section class="experience-hero">
            <h1 class="experience-title">Our Experience</h1>
            <p class="experience-subtitle">
                Explore and share the emotional highlights, curatorial memories, and personal connections made by visitors
                worldwide.
            </p>
        </section>

        <!-- Main Content Layout Grid -->
        <div class="experience-grid">

            <!-- LEFT SIDE: Feed and Form -->
            <div class="lg:col-span-2">

                <!-- Notifications -->
                @if(session('success'))
                    <div
                        class="mb-8 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-semibold flex items-center gap-2">
                        <span>✓</span> {{ session('success') }}
                    </div>
                @endif

                <!-- POST FORM -->
                @auth
                    <form action="{{ route('visit.our-experience.store') }}" method="POST" enctype="multipart/form-data"
                        class="bg-white border border-gray-200 rounded-lg p-6 mb-8 hover:shadow-md transition-shadow duration-200">
                        @csrf
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Share Your Museum Journey</h3>

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                            <input type="text" name="title" id="title" required placeholder="Give your story a title"
                                class="w-full border border-gray-200 rounded-lg p-3 text-sm focus:outline-none focus:ring-1 focus:ring-black @error('title') border-red-500 @enderror"
                                value="{{ old('title') }}">
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">How did you feel?
                                (Emotion Category)</label>
                            <select name="category_id" id="category_id"
                                class="w-full border border-gray-200 rounded-lg p-3 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->category_id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="body" class="block text-sm font-semibold text-gray-700 mb-2">Your Experience
                                Story</label>
                            <textarea name="body" id="body" rows="4" required
                                placeholder="Tell us about the exhibit that moved you, what inspired you, or the thoughts you had during your visit..."
                                class="w-full border border-gray-200 rounded-lg p-3 text-sm focus:outline-none focus:ring-1 focus:ring-black @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                            @error('body')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="featured_img" class="block text-sm font-semibold text-gray-700 mb-2">Featured Image
                                (Optional)</label>
                            <input type="file" name="featured_img" id="featured_img" accept=".jpg,.jpeg,.png,.webp"
                                class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:outline-none focus:ring-1 focus:ring-black @error('featured_img') border-red-500 @enderror">
                            @error('featured_img')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Accepts JPG, PNG, WEBP up to 2MB.</p>
                        </div>

                        <button type="submit"
                            class="bg-black hover:bg-gray-800 text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-colors duration-200">
                            Share Story
                        </button>
                    </form>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8 text-center">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Want to share your story?</h3>
                        <p class="text-sm text-gray-600 mb-4">Log in or become a member to share your thoughts on our visitor
                            experience wall.</p>
                        <a href="{{ route('login') }}"
                            class="inline-block bg-black hover:bg-gray-800 text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-colors duration-200">
                            Log In to Share
                        </a>
                    </div>
                @endauth

                <!-- EMOTION FILTER PILLS -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <a href="{{ route('visit.our-experience') }}"
                        class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-colors duration-200 {{ !$selectedCategoryId ? 'bg-black text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        All Stories
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('visit.our-experience', ['category_id' => $cat->category_id]) }}"
                            class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-colors duration-200 {{ $selectedCategoryId == $cat->category_id ? 'bg-black text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                <!-- COMMUNITY EXPERIENCE LIST -->
                <div class="space-y-6">
                    @forelse($posts as $post)
                        <div
                            class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200 relative overflow-hidden {{ $post->user->premium_ended_at && \Carbon\Carbon::parse($post->user->premium_ended_at)->isFuture() ? 'border-amber-200' : '' }}">

                            <!-- Top header element -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center font-bold text-gray-800">
                                        {{ substr(optional($post->user->profile)->first_name ?? 'V', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 flex items-center gap-2">
                                            {{ optional($post->user->profile)->first_name ?? 'Visitor' }}
                                            {{ optional($post->user->profile)->last_name ?? '' }}

                                            @if($post->user->premium_ended_at && \Carbon\Carbon::parse($post->user->premium_ended_at)->isFuture())
                                                <span
                                                    class="bg-amber-50 text-amber-800 border border-amber-200 text-[10px] px-2 py-0.5 rounded-full font-bold flex items-center gap-1 shadow-sm">
                                                    👑 GOLD MEMBER
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $post->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Emotion badge -->
                                <span
                                    class="bg-gray-100 text-gray-800 border border-gray-200 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                    {{ $post->category->name }}
                                </span>
                            </div>

                            <!-- Post content body -->
                            @if($post->featured_img)
                                <div class="mb-4 rounded-lg overflow-hidden border border-gray-100">
                                    <img src="{{ asset('storage/' . $post->featured_img) }}" alt="{{ $post->title }}"
                                        class="w-full h-auto object-cover max-h-80">
                                </div>
                            @endif
                            <h4 class="font-bold text-lg text-gray-900 mb-2">{{ $post->title }}</h4>
                            <p class="text-gray-700 leading-relaxed text-sm whitespace-pre-line">
                                {{ $post->body }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-gray-50 border border-gray-100 rounded-lg">
                            <p class="text-gray-500 text-sm">No stories shared under this emotion yet. Be the first to share!
                            </p>
                        </div>
                    @endforelse

                    <!-- Pagination links -->
                    <div class="mt-8">
                        {{ $posts->links() }}
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDE: Sidebar and Support Info -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-6 sticky top-6">
                    <h3 class="sidebar-contact-title">Experience Wall</h3>
                    <p class="sidebar-contact-body mb-4">
                        Our Civilization of Art is more than collections—it is the combined connection, emotion, and
                        interpretation of our global guests.
                    </p>
                    <p class="text-xs text-gray-500 leading-relaxed mb-4">
                        All stories are moderated. Please maintain respect and share stories directly relevant to your
                        visits and reflections of the museum.
                    </p>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <p class="text-sm font-semibold text-black">
                            Admin Assistance:
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Email: <a href="mailto:support@ocmuseum.org"
                                class="experience-inline-link">support@ocmuseum.org</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </body>
@endsection

</html>
@props(['items' => []])

<div class="grid grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-24">
    @foreach($items as $item)
        <a href="{{ $item['href'] }}" class="group block cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-black/70 focus-visible:ring-offset-4">
            <div class="overflow-hidden">
                <img src="{{ $item['image'] }}" alt="{{ $item['alt'] ?? $item['name'] }}" class="w-full h-[200px] object-cover transition duration-500 group-hover:scale-[1.03]" />
            </div>

            <h4 class="text-[18px] leading-[1.25] text-[#111] mt-5 font-semibold">
                {{ $item['name'] }}
            </h4>
        </a>
    @endforeach
</div>

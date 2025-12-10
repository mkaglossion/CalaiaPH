@props([
    'package' => null,
    'title' => null,
    'image' => null,
    'features' => [],
    'price' => null,
    'details' => [],
    'gallery' => []
])

<div class="bg-white rounded-[28px] shadow-lg border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group">
    {{-- Inset framed image --}}
    <div class="px-5 pt-5">
        <div class="relative overflow-hidden ring-1 ring-black/5 shadow-sm bg-white">
            <img
                src="{{ $image }}"
                class="h-64 md:h-72 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                alt="{{ $title }} package"
                loading="lazy"
            >
        </div>
    </div>

    {{-- Body --}}
    <div class="px-8 pt-6 pb-8">
        {{-- Title --}}
        <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide text-center" style="color:#97655A; font-size: 25px;">
            @if(strtolower($title) === 'nature experience')
                NATURE<br>EXPERIENCE
            @else
                {{ strtoupper($title) }}
            @endif
        </h3>

        {{-- Divider (subtle sage like the mock) --}}
        <div class="h-px w-full bg-[#dfe6d5] mb-5"></div>

        {{-- Features (compact, centered list) --}}
        <ul class="space-y-2 text-base md:text-lg uppercase tracking-widest text-center" style="color:#8f6a60; font-size: 13px;">
            @foreach($features as $feature)
                <li>{{ strtoupper($feature) }}</li>
            @endforeach
        </ul>

        {{-- CTA --}}
        <div class="mt-7 flex justify-center">
            <button
                type="button"
                class="px-6 py-2.5 rounded-full text-white text-xs md:text-sm font-semibold tracking-wider shadow
                       transition-all duration-300 hover:-translate-y-0.5"
                style="background-color:#8f5d54;"
                onmouseover="this.style.backgroundColor='#a0675a'"
                onmouseout="this.style.backgroundColor='#8f5d54'"
                data-title="{{ $title }}"
                data-price="{{ $price }}"
                data-details='@json($details)'
                data-gallery='@json($gallery)'
            >
                LEARN MORE
            </button>
        </div>
    </div>
</div>
<x-page-layout
    title="{{ $texts['title'] ?? 'CalaiaPH' }}"
    description="{{ $texts['description'] ?? 'Welcome to CalaiaPH, your ultimate destination for farm stays, day tours, and outdoor adventures. Experience nature, relaxation, and unforgettable memories.' }}"
    keywords="{{ $texts['keywords'] ?? 'farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience' }}"
    active-header="home"
>
    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- Check Availability --}}
    <x-check-availability />

    {{-- Event Experience --}}
    <x-event-experience />

    {{-- Testimonials --}}
    <x-testimonials-cards />

    {{-- Share Your Experience Button --}}
    <div class="text-center my-8">
        <a href="{{ route('testimonials.create') }}" class="inline-block px-6 py-3 bg-[#7a7d52] text-white font-medium rounded-lg hover:bg-[#6b6e45] transition-colors duration-300">
            Share Your Experience
        </a>
    </div>

    {{-- Weather Forecast--}}
    <x-weather />

    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>

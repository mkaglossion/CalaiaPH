<x-page-layout
    title="{{ $texts['title'] ?? 'CalaiaPH' }}"
    description="{{ $texts['description'] ?? 'Welcome to CalaiaPH, your ultimate destination for farm stays, day tours, and outdoor adventures. Experience nature, relaxation, and unforgettable memories.' }}"
    keywords="{{ $texts['keywords'] ?? 'farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience' }}"
    active-header="home"
>
    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- Check Availability --}}
    <x-check-availability :inventory="$availability" :totalQuantity="$totalQuantity" />

    {{-- Event Experience --}}
    <x-event-experience />

    {{-- Testimonials --}}
    <x-testimonials-cards />

    {{-- Weather Forecast--}}
    <x-weather />

    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>

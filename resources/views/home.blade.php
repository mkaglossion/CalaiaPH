<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $texts['description'] ?? 'Welcome to CalaiaPH, your ultimate destination for farm stays, day tours, and outdoor adventures. Experience nature, relaxation, and unforgettable memories.' }}">
    <meta name="keywords" content="{{ $texts['keywords'] ?? 'farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience' }}">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>{{ $texts['title'] ?? 'CalaiaPH' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  </head>
    <body class="bg-[#f5efe7]">
      <div class="flex flex-col items-center justify-center px-4">
        @if (session('success'))
            <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
                {{ session('success') }}
            </div>
        @endif
      </div>
    <x-header active="home" />

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

     {{-- Check Availability --}}
     <x-check-availability />
    {{-- Event Experience --}}
    <x-event-experience />
    {{-- Testimonials --}}
    <x-testimonials-cards />
     {{-- Weather Forecast--}}
     <x-weather />
     {{-- Floating Buttons --}}
     <x-floating-buttons />
     {{-- Footer --}}
     <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>

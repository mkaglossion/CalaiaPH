<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  </head>
  <body class="bg-white-100">
      <div class="flex flex-col items-center justify-center px-4">
        @if (session('success'))
            <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
                {{ session('success') }}
            </div>
        @endif
      </div>
    <x-header active="home" />
    {{-- Hero Slider --}}
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="swiper mySwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
      <div class="swiper-wrapper">
        <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
          <img src="{{ asset('assets/images/carousel/home-slide-1.png') }}" alt="Home Slide 1" class="w-full h-full object-cover object-center" loading="lazy">
          <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
          <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
            <div class="text-center px-4">
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4" style="font-family: gotham; text-transform: uppercase; font-size: 3.5rem;">Welcome to CalaiaPH</h2>
              <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family: 'vila zilka seriff', serif; font-style: italic; font-size: 1.2rem;">Experience the beauty of nature</p>
            </div>
          </div>
        </div>
        <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
          <img src="{{ asset('assets/images/carousel/home-slide-2.png') }}" alt="Home Slide 2" class="w-full h-full object-cover object-center" loading="lazy">
          <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
          <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
            <div class="text-center px-4">
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4" style="font-family: gotham; text-transform: uppercase; font-size: 3.5rem;">Adventure Awaits</h2>
              <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family: 'vila zilka seriff', serif; font-style: italic; font-size: 1.2rem;">Discover exciting activities</p>
            </div>
          </div>
        </div>
        <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
          <img src="{{ asset('assets/images/carousel/home-slide-3.png') }}" alt="Home Slide 3" class="w-full h-full object-cover object-center" loading="lazy">
          <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
          <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
            <div class="text-center px-4">
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4" style="font-family: gotham; text-transform: uppercase; font-size: 3.5rem;">Relax and Unwind</h2>
              <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family: 'vila zilka seriff', serif; font-style: italic; font-size: 1.2rem;">Perfect staycation destination</p>
            </div>
          </div>
        </div>
      </div>
      <div class="swiper-pagination hidden sm:block" aria-label="Slider pagination"></div>
    </div>

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @verbatim
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const swiper = new Swiper('.mySwiper', {
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        loop: true,
        effect: 'fade',
        fadeEffect: {
          crossFade: true
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        keyboard: {
          enabled: true,
          onlyInViewport: false,
        },
        a11y: {
          prevSlideMessage: 'Previous slide',
          nextSlideMessage: 'Next slide',
          firstSlideMessage: 'This is the first slide',
          lastSlideMessage: 'This is the last slide',
          paginationBulletMessage: 'Go to slide {{index}}',
        },
      });
    });
    </script>
    @endverbatim
     {{-- Check Availability --}}
     <x-check-availability />
    {{-- Event Experience --}}
    <x-event-experience />
    {{-- Feature Cards --}}
    <x-feature-cards />
     {{-- Weather Forecast--}}
    <x-weather />
    {{-- Footer --}}
    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>

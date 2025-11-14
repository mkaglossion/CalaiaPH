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
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="swiper mySwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]" data-slides-count="{{ count($swiperImages) }}">
      <div class="swiper-wrapper">
        @if(count($swiperImages) > 0)
          @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
          @foreach($swiperImages as $index => $image)
          <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
            <img
              src="{{ asset('storage/' . $image->image_path) }}"
              alt="{{ $image->title ?? 'Slide Image' }}"
              class="w-full h-full object-cover object-center"
              loading="lazy"
            >
            <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
            <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
              <div class="text-center px-4">
                @if(isset($texts_array[$index]))
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase"
                      style="font-family: gotham; font-size: 3.5rem;">
                    {{ ucfirst($labels_array[$index]) }}
                  </h2>
                  <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase"
                    style="font-family: 'Vilaka Modern Serif', serif; font-style: italic; font-size: 1.2rem;">
                    {{ $texts_array[$index] }}
                  </p>
                @endif
              </div>
            </div>
          </div>
          @endforeach
        @endif
        @if(count($swiperImages) == 0 && (!$texts || count($texts) == 0))
          <div class="swiper-slide relative flex items-center justify-center bg-gray-200 overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center z-20 text-gray-600">
              <div class="text-center px-4">
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase"
                    style="font-family: gotham; font-size: 3.5rem;">
                  No Image Available
                </h2>
              san   <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase"
                  style="font-family: 'Vilaka Modern Serif', serif; font-style: italic; font-size: 1.2rem;">
                  Images and texts will be added soon
                </p>
              </div>
            </div>
          </div>
        @endif
      </div>
      <div class="swiper-pagination hidden sm:block" aria-label="Slider pagination"></div>
    </div>


    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @php $paginationMessage = 'Go to slide {{index}}'; @endphp

    @verbatim
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const slidesCount = parseInt(document.querySelector('.mySwiper').dataset.slidesCount);
      const swiper = new Swiper('.mySwiper', {
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        loop: slidesCount > 1,
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
          paginationBulletMessage: '{{ $paginationMessage }}',
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
     {{-- Floating Buttons --}}
     <x-floating-buttons />
     {{-- Footer --}}
     <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>

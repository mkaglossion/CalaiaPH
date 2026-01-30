<!doctype html>
<html lang="en">
<head>
  @include('layouts.partials.favicon')
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Explore exciting day tours at CalaiaPH. From adventure packages to family fun, discover thrilling outdoor activities and nature experiences tailored for every group.">
  <meta name="keywords" content="day tours, adventure packages, outdoor activities, nature tours, CalaiaPH, family fun, group activities">
  <meta name="author" content="CalaiaPH">
  <title>CalaiaPH - Day Tour</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

</head>

<body class="bg-white">
{{-- flash message --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <x-flash-message type="success" :message="session('success')" />
      @endif
    </div>
  <x-header active="daytour" />
    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

  {{-- Day Tour Packages Section --}}
  <main class="py-12 md:py-16 lg:py-20" style="background-color: #f5efe7;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- Section Header --}}
      <!--<div class="text-center mb-12 md:mb-16">
        <h2 class="text-3xl font-bold text-[#8f5d54] mb-4 uppercase">
          Choose Your Adventure
        </h2>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Discover the perfect day tour experience tailored to your interests. From thrilling adventures to peaceful nature escapes, we have something for everyone.
        </p>
      </div>-->

      {{-- Packages Section --}}
      {{-- Day Tour Cards Component (matches mock) --}}

      <div class="space-y-16">
        {{-- Packages Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
          @foreach($packages as $package)
            <div class="bg-white rounded-[28px] shadow-lg border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group">
              {{-- Inset framed image --}}
              <div class="px-5 pt-5">
                <div class="relative overflow-hidden ring-1 ring-black/5 shadow-sm bg-white">
                  <img
                    src="{{ $package->image ?: asset('images/default.jpg') }}"
                    class="h-64 md:h-72 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                    alt="{{ $package->name }} package"
                    loading="lazy"
                  >
                </div>
              </div>

              {{-- Body --}}
              <div class="px-8 pt-6 pb-8">
                {{-- Title --}}
                 <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide text-center" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                   @php
                     $title = is_array($package) ? $package['title'] : $package->name;
                   @endphp
                   @if(strtolower($title) === 'nature experience')
                     NATURE<br>EXPERIENCE
                   @else
                     {{ strtoupper($title) }}
                   @endif
                 </h3>

                {{-- Description --}}
                <p class="text-center mb-4" style="color:#97655A; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                  {!! nl2br($package->description ?? '') !!}
                </p>

                {{-- Divider (subtle sage like the mock) --}}
                <div class="h-px w-full bg-[#dfe6d5] mb-5"></div>

                {{-- CTA --}}
                <div class="mt-7 flex justify-center">
                  <button
                    type="button"
                    class="px-6 py-2.5 rounded-full text-white text-xs md:text-sm font-semibold tracking-wider shadow
                           transition-all duration-300 hover:-translate-y-0.5 bg-brand hover:bg-brand/90"
                    data-title="{{ $package->name }}"
                    data-price="{{ $package->price ? 'PHP ' . number_format($package->price, 2) . ' per person' : 'Price not set' }}"
                    data-details='@json($package->activities->pluck("name")->toArray())'
                    data-daytour-id="{{ $package->id }}"
                    data-image-api-url="/day-tour-swiper-images/daytour"
                  >
                    LEARN MORE
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Information Section (kept; slight polish to match palette) --}}
        <div class="bg-linear-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl">
          <div class="text-center mb-8">
            <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
              <i class="bi bi-info-circle-fill mr-3 text-3xl"></i>
              {{ $infoTitle ?? 'Important Information' }}
            </h4>
            <p class="text-lg/7 opacity-90" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">{{ $infoSubtitle ?? 'Everything you need to know before your visit' }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($infoItems ?? [] as $info)
              <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
                <i class="bi {{ $info['icon'] ?? 'bi-info-circle' }} text-xl mr-4 mt-1 shrink-0"></i>
                <span class="text-sm md:text-base leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">{{ $info['text'] ?? '' }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </main>

  {{-- Dynamic Details Modal --}}
  <x-package-modal id="detailsModal" :price="true" buttonText="Book Now" buttonAction="bookNow" imageApiUrl="/day-tour-swiper-images/daytour" />

  {{-- Floating Buttons --}}
  <x-floating-buttons />
  {{-- Footer --}}
  <x-footer />

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <script>
  // Image cache for instant modal loading
  const imageCache = new Map();

  // Make cache globally available
  window.imageCache = imageCache;

  // Preload images for a specific daytour
  async function preloadDaytourImages(daytourId) {
    if (imageCache.has(daytourId)) {
      return imageCache.get(daytourId);
    }

    // Prevent multiple simultaneous requests
    imageCache.set(daytourId, 'loading');

    try {
      // Use the same API URL as the modal
      const apiUrl = '/day-tour-swiper-images/daytour/' + daytourId;
      const response = await fetch(apiUrl);
      const images = await response.json();
      imageCache.set(daytourId, images);
      return images;
    } catch (error) {
      console.error('Error preloading images:', error);
      imageCache.set(daytourId, []);
      return [];
    }
  }

  // Preload all images on page load (aggressive preloading for instant display)
  async function preloadAllImages() {
    const buttons = document.querySelectorAll('button[data-daytour-id]');
    const uniqueDaytourIds = [...new Set(Array.from(buttons).map(button => button.dataset.daytourId))];

    // Preload all images immediately for instant modal display
    const allPromises = uniqueDaytourIds.map(daytourId => preloadDaytourImages(daytourId));

    try {
      await Promise.all(allPromises);
      console.log('All daytour images preloaded successfully');
    } catch (error) {
      console.error('Error preloading images:', error);
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Start preloading images immediately
    preloadAllImages();

    // Initialize modal functionality
    initializeModal();

    // Initialize modal swipers (same as staycation)
    initModalSwipers();
  });

  function initModalSwipers(){
    if(!window.Swiper) return;
    document.querySelectorAll(".swiper[data-swiper='modal']").forEach((el)=>{
      try{
        if(!el.swiper){
          const swiper = new Swiper(el, {
            slidesPerView: 1,
            loop: true,
            navigation: { nextEl: el.querySelector('[data-custom-next]'), prevEl: el.querySelector('[data-custom-prev]') },
            pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
          });
        }
      }catch(e){ console.warn('Modal swiper init failed', e); }
    });
  }

  let lastFocusedElement = null;

  function initializeModal() {
    const buttons = document.querySelectorAll('button[data-title]');

    buttons.forEach(button => {
      // Preload images on hover for instant loading (with improved caching check)
      button.addEventListener('mouseenter', function() {
        const daytourId = this.dataset.daytourId;
        if (daytourId && (!imageCache.has(daytourId) || imageCache.get(daytourId) === 'loading')) {
          preloadDaytourImages(daytourId);
        }
      });

      button.addEventListener('click', function() {
        lastFocusedElement = this;
        // Check if openPackageModal function is available
        if (typeof openPackageModal === 'function') {
          openPackageModal(this);
        } else {
          console.error('openPackageModal function not found. Modal component may not be loaded.');
        }
      });
    });
  }
  </script>
</body>
</html>
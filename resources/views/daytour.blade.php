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

  <style>
    /* Custom Swiper pagination bullets */
    .swiper-pagination-bullet {
      background-color: #a75e3e !important;
    }
    .swiper-pagination-bullet-active {
      background-color: #a75e3e !important;
    }
    
    /* Improve image rendering clarity */
    .image-rendering-optimizeContrast {
      image-rendering: -webkit-optimize-contrast;
      image-rendering: -moz-crisp-edges;
      image-rendering: crisp-edges;
      image-rendering: pixelated;
      transform: translateZ(0);
      will-change: transform;
    }
  </style>

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

      {{-- Packages Grid --}}
        {{-- Packages Slider (Large Card like mock) --}}
        <div class="w-full">
          <div class="relative group">
            <div class="swiper" id="daytourPackagesSwiper">
              <div class="swiper-wrapper">
                @foreach($packages as $package)
                  @php
                    $title = is_array($package) ? $package['title'] : $package->name;
                    $displayTitle = (strtolower($title) === 'nature experience')
                      ? "NATURE EXPERIENCE"
                      : strtoupper($title);
                  @endphp

                  <div class="swiper-slide">
                    {{-- CENTERED LARGE CARD --}}
                    <div class="group overflow-hidden rounded-[10px] bg-white shadow-md">
                      <div class="relative w-full overflow-hidden
                                  h-87.5 sm:h-120 md:h-150 lg:h-175">

                        @php
                          $imgSrc = $package->image ?: asset('images/default.jpg');
                          $imgSrc2x = null;
                          // Serve larger, higher quality images with sharpening for better clarity
                          if (is_string($imgSrc) && str_contains($imgSrc, 'res.cloudinary.com')) {
                              $imgSrc = preg_replace('/\/upload\//', '/upload/w_2500,q_auto:best,f_auto,e_sharpen:100/', $imgSrc, 1);
                              $imgSrc2x = preg_replace('/\/upload\//', '/upload/w_3000,q_auto:best,f_auto,e_sharpen:100/', $package->image, 1);
                          }
                        @endphp

                        <img
                          src="{{ $imgSrc }}"
                          @if($imgSrc2x)
                            srcset="{{ $imgSrc }} 1x, {{ $imgSrc2x }} 2x"
                          @endif
                          alt="{{ $package->name }} package"
                          decoding="async"
                          class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.02] image-rendering-optimizeContrast"
                        >

                        {{-- bottom overlay like mock --}}
                        <div class="absolute inset-0 bg-linear-to-t from-black/35 via-black/5 to-transparent"></div>

                        {{-- Title + Learn more button (bottom) --}}
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                          <div class="flex items-end justify-between">
                            <h3 class="whitespace-pre-line uppercase font-extrabold tracking-wide leading-[0.95]
                                       text-white drop-shadow-sm text-[26px] sm:text-[32px] md:text-[40px] max-w-[70%]">
                              {{ $displayTitle }}
                            </h3>
                            <button
                              type="button"
                              class="text-white text-[12px] tracking-[.12em] uppercase cursor-pointer transition no-underline hover:underline"
                              data-title="{{ $package->name }}"
                              data-price="{{ $package->price ? 'PHP ' . number_format($package->price, 2) . ' per person' : 'Price not set' }}"
                              data-details='@json($package->activities->pluck("name")->toArray())'
                              data-daytour-id="{{ $package->id }}"
                              data-image-api-url="/day-tour-swiper-images/daytour"
                              style="background: none; border: none; padding: 0; font: inherit;"
                            >
                              Learn more >>
                            </button>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              {{-- pagination bullets --}}
              <div class="swiper-pagination mt-6"></div>
            </div>

            {{-- Left Arrow Inside Slider --}}
            <button type="button" class="daytour-swiper-prev absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" aria-label="Previous">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            {{-- Right Arrow Inside Slider --}}
            <button type="button" class="daytour-swiper-next absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" aria-label="Next">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>

        {{-- Information Section (kept; slight polish to match palette) --}}
        <div class="bg-linear-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl mt-16 md:mt-20 lg:mt-24">
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

    // ✅ Packages swiper
    initPackagesSwiper();
  });

  function initModalSwipers(){
    if(!window.Swiper) return;
    document.querySelectorAll(".swiper[data-swiper='modal']").forEach((el)=>{
      try{
        if(!el.swiper){
          // Count actual slides and determine if loop should be enabled
          const slides = el.querySelectorAll('.swiper-slide');
          const totalSlides = slides.length;
          const enableLoop = totalSlides >= 2; // Need at least 2 slides for loop
          
          const swiper = new Swiper(el, {
            slidesPerView: 1,
            loop: enableLoop,
            navigation: { nextEl: el.querySelector('[data-custom-next]'), prevEl: el.querySelector('[data-custom-prev]') },
            pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
          });
        }
      }catch(e){ console.warn('Modal swiper init failed', e); }
    });
  }

  function initPackagesSwiper(){
    if(!window.Swiper) return;

    const el = document.querySelector('#daytourPackagesSwiper');
    if(!el) return;

    const parentRelative = el.closest('.relative');
    
    // Count actual slides and determine if loop should be enabled
    const slides = el.querySelectorAll('.swiper-slide');
    const totalSlides = slides.length;
    const slidesPerView = 1;
    const slidesPerGroup = 1;
    
    // Enable loop only if there are enough slides (slidesPerView + slidesPerGroup)
    const enableLoop = totalSlides >= (slidesPerView + slidesPerGroup);

    new Swiper(el, {
      slidesPerView: slidesPerView,
      spaceBetween: 24,
      loop: enableLoop,
      navigation: {
        nextEl: parentRelative ? parentRelative.querySelector('.daytour-swiper-next') : null,
        prevEl: parentRelative ? parentRelative.querySelector('.daytour-swiper-prev') : null,
      },
      pagination: {
        el: el.querySelector('.swiper-pagination'),
        clickable: true
      }
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

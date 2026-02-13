@php
  use App\Helpers\CloudinaryHelper;

  /**
   * Helper (optional): if CloudinaryHelper::optimize doesn't support these params yet,
   * you can update it later. For now, we still call it the same way you do.
   */

  // Dynamic info section data
  $infoTitle = $texts['info_title'] ?? 'Important Information';
  $infoSubtitle = $texts['info_subtitle'] ?? 'Everything you need to know before your visit';
  $infoItems = isset($texts['info_items']) ? json_decode($texts['info_items'], true) : [];
@endphp

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
    .swiper-pagination-bullet,
    .swiper-pagination-bullet-active {
      background-color: #a75e3e !important;
    }

    /* ✅ Better photo clarity: avoid pixelated/crisp-edges */
    .photo-clarity {
      image-rendering: auto;
      -webkit-font-smoothing: antialiased;
      transform: translateZ(0);
      backface-visibility: hidden;
      will-change: transform;
    }

    .image-preview-modal img {
      max-width: 90vw;
      max-height: 85vh;
      object-fit: contain;
    }
    .image-preview-overlay {
      backdrop-filter: blur(8px);
    }
  </style>

  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>

<body class="bg-white">
  <div class="flex flex-col items-center justify-center px-4">
    @if (session('success'))
      <x-flash-message type="success" :message="session('success')" />
    @endif
  </div>

  <x-header active="daytour" />
  <x-hero-slider :images="$swiperImages" :texts="array_diff_key($texts, ['info_items' => ''])" />

  <main class="py-12 md:py-16 lg:py-20" style="background-color: #f5efe7;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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

                  $rawImg = $package->image ?: asset('images/default.jpg');

                  $isCloudinary = is_string($rawImg) && str_contains($rawImg, 'res.cloudinary.com');

                  /**
                   * ✅ Use new CloudinaryHelper for better clarity
                   * - dpr_auto for retina screens
                   * - c_fill,g_auto for consistent cropping
                   * - Sharpen for large images
                   */
                  if ($isCloudinary) {
                      $imgSrc = CloudinaryHelper::optimize($rawImg, 1280, true, true, 'good');
                      $imgSrcset = CloudinaryHelper::srcset($rawImg, [768, 1280, 1920, 2500], true, true, 'good');
                      $imgPreview = CloudinaryHelper::optimize($rawImg, 2500, true, true, 'good');
                  } else {
                      $imgSrc = $rawImg;
                      $imgSrcset = null;
                      $imgPreview = $rawImg;
                  }
                @endphp

                <div class="swiper-slide">
                  <div class="group overflow-hidden rounded-[10px] bg-white shadow-md">
                    <div class="relative w-full overflow-hidden h-87.5 sm:h-120 md:h-150 lg:h-175">

                      <div
                        class="relative h-full w-full cursor-pointer"
                        onclick="openImagePreview('{{ $imgPreview }}')"
                      >
                        <img
                          src="{{ $imgSrc }}"
                          @if($imgSrcset)
                            srcset="{{ $imgSrcset }}"
                          @endif
                          sizes="(max-width: 640px) 95vw, (max-width: 1024px) 90vw, 1200px"
                          alt="{{ $package->name }} package"
                          loading="lazy"
                          decoding="async"
                          fetchpriority="low"
                          width="1200"
                          height="675"
                          class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.02] photo-clarity"
                        >
                      </div>

                      <div class="absolute inset-0 bg-linear-to-t from-black/35 via-black/5 to-transparent"></div>

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

            <div class="swiper-pagination mt-6"></div>
          </div>

          <button type="button" class="daytour-swiper-prev absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" aria-label="Previous">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <button type="button" class="daytour-swiper-next absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" aria-label="Next">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- Information Section --}}
      <div class="bg-linear-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl mt-16 md:mt-20 lg:mt-24">
        <div class="text-center mb-8">
          <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
            <i class="bi bi-info-circle-fill mr-3 text-3xl"></i>
            {{ $infoTitle ?? 'Important Information' }}
          </h4>
          <p class="text-lg/7 opacity-90" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
            {{ $infoSubtitle ?? 'Everything you need to know before your visit' }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          @foreach($infoItems ?? [] as $info)
            <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
              <i class="bi {{ $info['icon'] ?? 'bi-info-circle' }} text-xl mr-4 mt-1 shrink-0"></i>
              <span class="text-sm md:text-base leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                {{ $info['text'] ?? '' }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </main>

  <x-package-modal id="detailsModal" :price="true" buttonText="Book Now" buttonAction="bookNow" imageApiUrl="/day-tour-swiper-images/daytour" />
  <x-floating-buttons />
  <x-footer />

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <script>
    // Image cache for instant modal loading
    const imageCache = new Map();
    window.imageCache = imageCache;

    async function preloadDaytourImages(daytourId) {
      if (imageCache.has(daytourId) && imageCache.get(daytourId) !== 'loading') {
        return imageCache.get(daytourId);
      }

      imageCache.set(daytourId, 'loading');

      try {
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

    async function preloadAllImages() {
      const buttons = document.querySelectorAll('button[data-daytour-id]');
      const uniqueIds = [...new Set([...buttons].map(b => b.dataset.daytourId).filter(Boolean))];

      try {
        await Promise.all(uniqueIds.map(id => preloadDaytourImages(id)));
        console.log('All daytour images preloaded successfully');
      } catch (error) {
        console.error('Error preloading images:', error);
      }
    }

    function initModalSwipers(){
      if(!window.Swiper) return;
      document.querySelectorAll(".swiper[data-swiper='modal']").forEach((el)=>{
        try{
          if(!el.swiper){
            const totalSlides = el.querySelectorAll('.swiper-slide').length;
            const enableLoop = totalSlides >= 2;

            new Swiper(el, {
              slidesPerView: 1,
              loop: enableLoop,
              navigation: {
                nextEl: el.querySelector('[data-custom-next]'),
                prevEl: el.querySelector('[data-custom-prev]')
              },
              pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
            });
          }
        } catch(e){
          console.warn('Modal swiper init failed', e);
        }
      });
    }

    function initPackagesSwiper(){
      if(!window.Swiper) return;

      const el = document.querySelector('#daytourPackagesSwiper');
      if(!el) return;

      const parentRelative = el.closest('.relative');
      const totalSlides = el.querySelectorAll('.swiper-slide').length;

      const slidesPerView = 1;
      const slidesPerGroup = 1;
      const enableLoop = totalSlides >= (slidesPerView + slidesPerGroup);

      new Swiper(el, {
        slidesPerView,
        spaceBetween: 24,
        loop: enableLoop,
        navigation: {
          nextEl: parentRelative ? parentRelative.querySelector('.daytour-swiper-next') : null,
          prevEl: parentRelative ? parentRelative.querySelector('.daytour-swiper-prev') : null,
        },
        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true }
      });
    }

    let lastFocusedElement = null;

    function initializeModal() {
      const buttons = document.querySelectorAll('button[data-title]');

      buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
          const daytourId = this.dataset.daytourId;
          if (!daytourId) return;

          if (!imageCache.has(daytourId) || imageCache.get(daytourId) === 'loading') {
            preloadDaytourImages(daytourId);
          }
        });

        button.addEventListener('click', function() {
          lastFocusedElement = this;
          if (typeof openPackageModal === 'function') {
            openPackageModal(this);
          } else {
            console.error('openPackageModal function not found. Modal component may not be loaded.');
          }
        });
      });
    }

    // ✅ Image Preview Modal
    let previewModal = null;
    let previewImg = null;

    function openImagePreview(imageUrl) {
      if (!previewModal) {
        previewModal = document.createElement('div');
        previewModal.id = 'imagePreviewModal';
        previewModal.className = 'fixed inset-0 bg-black/80 image-preview-overlay flex items-center justify-center z-[9999] hidden';
        previewModal.innerHTML = `
          <button type="button" onclick="closeImagePreview()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 p-2" aria-label="Close preview">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div class="image-preview-modal">
            <img id="previewImg" src="" alt="Preview" class="rounded-lg shadow-2xl" />
          </div>
        `;
        document.body.appendChild(previewModal);
        previewImg = document.getElementById('previewImg');

        previewModal.addEventListener('click', function(e) {
          if (e.target === previewModal) closeImagePreview();
        });

        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') closeImagePreview();
        });
      }

      previewImg.src = imageUrl;
      previewModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeImagePreview() {
      if (previewModal) {
        previewModal.classList.add('hidden');
        document.body.style.overflow = '';
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      preloadAllImages();
      initializeModal();
      initModalSwipers();
      initPackagesSwiper();
    });
  </script>
</body>
</html>

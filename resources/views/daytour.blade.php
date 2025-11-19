<!doctype html>
<html lang="en">
<head>
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
</head>

<body class="bg-white">
  {{-- Success Message --}}
  @if (session('success'))
    <div class="flex justify-center px-4 py-4">
      <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg max-w-md w-full text-center shadow-sm" role="alert">
        {{ session('success') }}
      </div>
  @endif
  <x-header active="daytour" />
    {{-- Hero Slider --}}
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="swiper on heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]" data-slides-count="{{ count($swiperImages) }}">
    <div class="swiper-wrapper">
        @if(count($swiperImages) > 0)
          @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
          @foreach($swiperImages as $index => $image)
        <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
          <img
              src="{{ $image->image_path }}"
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
        @else
        <div class="swiper-slide relative flex items-center justify-center bg-gray-200 overflow-hidden">
          <div class="absolute inset-0 flex items-center justify-center z-20 text-gray-600">
            <div class="text-center px-4">
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase"
                  style="font-family: gotham; font-size: 3.5rem;">
                No Image Available
              </h2>
              <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase"
                style="font-family: 'Vilaka Modern Serif', serif; font-style: italic; font-size: 1.2rem;">
                Images will be added soon
              </p>
            </div>
          </div>
        </div>
        @endif
    </div>
  </div>

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
      @php
        // Title → price text to mirror your getPackagePrice() map
        $priceMap = [
          'Adventure Package'  => 'PHP 1,500 per person',
          'Family Fun Package' => 'PHP 2,000 per family',
          'Nature Experience'  => 'PHP 1,200 per person',
        ];
      @endphp

      <div class="space-y-16">
        {{-- Packages Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
          @foreach($packages as $package)
            <div class="bg-white rounded-[28px] shadow-lg border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group">
              {{-- Inset framed image --}}
              <div class="px-5 pt-5">
                <div class="relative overflow-hidden ring-1 ring-black/5 shadow-sm bg-white">
                  <img
                    src="{{ asset($package['image']) }}"
                    class="h-64 md:h-72 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                    alt="{{ $package['title'] }} package"
                    loading="lazy"
                  >
                </div>
              </div>

              {{-- Body --}}
              <div class="px-8 pt-6 pb-8">
                {{-- Title --}}
                 <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide text-center" style="color:#97655A;">
                   @if($package['title'] === 'Nature Experience')
                     NATURE<br>EXPERIENCE
                   @else
                     {{ strtoupper($package['title']) }}
                   @endif
                 </h3>


                {{-- Divider (subtle sage like the mock) --}}
                <div class="h-px w-full bg-[#dfe6d5] mb-5"></div>

                {{-- Features (compact, centered list) --}}
                <ul class="space-y-3 mb-7 text-center">
                  @foreach($package['features'] as $feature)
                    <li class="text-xs md:text-sm tracking-wide"
                        style="color:#8f5d54; font-family:gotham;">
                      {{ strtoupper($feature) }}
                    </li>
                  @endforeach
                </ul>

                {{-- CTA --}}
                <div class="flex justify-center">
                  <button
                    type="button"
                    class="px-6 py-2.5 rounded-full text-white text-xs md:text-sm font-semibold tracking-wider shadow
                           transition-all duration-300 hover:-translate-y-0.5"
                    style="background-color:#8f5d54;"
                    onmouseover="this.style.backgroundColor='#a0675a'"
                    onmouseout="this.style.backgroundColor='#8f5d54'"
                    data-title="{{ $package['title'] }}"
                    data-details='@json($package["details"])'
                    data-gallery='@json(array_map(fn($img) => asset($img), $package["gallery"]))'
                  >
                    LEARN MORE
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Information Section (kept; slight polish to match palette) --}}
        <div class="bg-gradient-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl">
          <div class="text-center mb-8">
            <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center"
                style="font-family:gotham;">
              <i class="bi bi-info-circle-fill mr-3 text-3xl"></i>
              Important Information
            </h4>
            <p class="text-lg/7 opacity-90">Everything you need to know before your visit</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach([
              ['icon' => 'bi-clock',           'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
              ['icon' => 'bi-calendar-check',  'text' => 'Advance booking recommended for guaranteed slots'],
              ['icon' => 'bi-person',          'text' => 'Children under 3 years old enter for free'],
              ['icon' => 'bi-people',          'text' => 'Group discounts available for 10+ persons'],
              ['icon' => 'bi-backpack',        'text' => 'Wear comfy clothing and footwear for outdoor activities'],
              ['icon' => 'bi-cup-straw',       'text' => 'Food and beverages available for purchase on site'],
            ] as $info)
              <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
                <i class="bi {{ $info['icon'] }} text-xl mr-4 mt-1 flex-shrink-0"></i>
                <span class="text-sm md:text-base leading-relaxed">{{ $info['text'] }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </main>

  {{-- Dynamic Details Modal --}}
  <div id="detailsModal" class="fixed inset-0 bg-transparent flex items-center justify-center p-2 md:p-4 pt-16 md:pt-4 hidden z-50">
    <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[95vh] md:max-h-[90vh] flex flex-col border border-gray-300 mx-2 sm:mx-4">
      {{-- Modal Header --}}
      <div class="p-4 md:p-6 border-b border-gray-300 flex-shrink-0">
        <div class="flex justify-between items-center">
          <h3 id="detailsModalLabel" class="text-xl md:text-2xl font-bold text-gray-800"></h3>
          <button type="button" class="text-gray-500 hover:text-gray-700 transition-colors" onclick="closeModal()" aria-label="Close modal">
            <i class="bi bi-x-lg text-xl md:text-2xl"></i>
          </button>
        </div>
      </div>

      {{-- Modal Body --}}
      <div class="p-4 md:p-6 flex-1 overflow-y-auto" id="detailsModalBody">
        {{-- Swiper Carousel --}}
        <div class="swiper relative packageSwiper mb-4 md:mb-6 rounded-2xl overflow-hidden" data-swiper="modal">
          <div class="swiper-wrapper" id="swiperWrapper"></div>
          <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
        </div>
        {{-- Package Details --}}
        <div id="packageDetails" class="space-y-3"></div>

        {{-- Package Price --}}
        <div id="packagePrice" class="mt-4 p-4 bg-gradient-to-r from-[#8f5d54] to-[#a0675a] text-white rounded-xl text-center shadow-lg">
          <div class="flex items-center justify-center mb-1">
            <i class="bi bi-tag-fill text-lg mr-2"></i>
            <span class="text-xs font-medium uppercase tracking-wide">Starting from</span>
          </div>
          <p class="text-lg md:text-xl font-bold font-gotham" id="priceDisplay"></p>
        </div>
      </div>

      {{-- Modal Footer --}}
      <div class="p-4 md:p-6 border-t border-gray-300 flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-4 justify-center flex-shrink-0">
        <button type="button" id="bookNowBtn" class="bg-[#8f5d54] text-white px-6 py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto font-semibold">
          Book Now
        </button>
        <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">
          Close
        </button>
      </div>
    </div>
  </div>

  {{-- Floating Buttons --}}
  <x-floating-buttons />
  {{-- Footer --}}
  <x-footer />

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const slidesCount = parseInt(document.querySelector('.heroSwiper').dataset.slidesCount);
    // Initialize hero swiper
    const heroSwiper = new Swiper('.heroSwiper', {
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      loop: slidesCount > 1,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      keyboard: {
        enabled: true,
      },
      a11y: {
        prevSlideMessage: 'Previous slide',
        nextSlideMessage: 'Next slide',
      },
    });

    // Initialize modal functionality
    initializeModal();
  });

  let packageSwiper = null;
  let lastFocusedElement = null;

  function initializeModal() {
    document.querySelectorAll('button[data-title]').forEach(button => {
      button.addEventListener('click', function() {
        lastFocusedElement = this;
        const title = this.getAttribute('data-title');
        const details = JSON.parse(this.getAttribute('data-details'));
        const gallery = JSON.parse(this.getAttribute('data-gallery'));
        viewDetails(title, details, gallery);
      });
    });
  }

  function viewDetails(title, descriptionParagraphs, imageUrls) {
    // Destroy previous swiper instance if exists
    if (packageSwiper) {
      packageSwiper.destroy();
      packageSwiper = null;
    }

    // Build carousel slides
    const swiperWrapper = document.getElementById('swiperWrapper');
    swiperWrapper.innerHTML = imageUrls.map((url, index) => `
      <div class="swiper-slide">
        <img src="${url}" class="w-full h-64 md:h-96 object-cover" alt="${title} - Image ${index + 1}" loading="lazy">
      </div>
    `).join('');

    // Build package details
    const packageDetails = document.getElementById('packageDetails');
    packageDetails.innerHTML = descriptionParagraphs.map(p => `
      <p class="flex items-start text-gray-700">
        <i class="bi bi-check-circle-fill text-blue-500 mr-3 mt-1 flex-shrink-0"></i>
        <span class="flex-1">${p}</span>
      </p>
    `).join('');

    // Update modal title and price
    document.getElementById('detailsModalLabel').textContent = `${title} Details`;
    document.getElementById('priceDisplay').textContent = getPackagePrice(title);

    // Initialize package swiper
    packageSwiper = new Swiper('.packageSwiper', {
      slidesPerView: 1,
      loop: true,
      navigation: { nextEl: '[data-custom-next]', prevEl: '[data-custom-prev]' },
    });

    // Custom dots
    const dotsEl = document.querySelector('[data-custom-dots]');
    const numSlides = imageUrls.length;
    function updateDots() {
      dotsEl.innerHTML = '';
      for(let i=0; i<numSlides; i++){
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'h-2.5 rounded-full ' + (i === packageSwiper.realIndex ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
        b.setAttribute('role','tab');
        b.setAttribute('aria-label', `Go to slide ${i+1}`);
        b.addEventListener('click', ()=> packageSwiper.slideToLoop(i));
        dotsEl.appendChild(b);
      }
    }
    updateDots();
    packageSwiper.on('slideChange', updateDots);

    // Set up book now button
    document.getElementById('bookNowBtn').onclick = function() {
      bookTour(title, getPackagePrice(title));
    };

    // Show modal
    const modal = document.getElementById('detailsModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Focus management for accessibility
    modal.focus();
  }

  function closeModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';

    if (packageSwiper) {
      packageSwiper.destroy();
      packageSwiper = null;
    }

    if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
  }

  function getPackagePrice(packageName) {
    const prices = {
      'Adventure Package': 'PHP 1,500 per person',
      'Family Fun Package': 'PHP 2,000 per family',
      'Nature Experience': 'PHP 1,200 per person'
    };
    return prices[packageName] || 'PHP 0';
  }

  function bookTour(packageName, price) {
    const url = '{{ route("booking.create") }}?type=daytour&package=' + encodeURIComponent(packageName) + '&price=' + encodeURIComponent(price);
    window.location.href = url;
  }

  // Close modal on outside click and Escape key
  document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('detailsModal').classList.contains('hidden')) {
      closeModal();
    }
  });
  </script>
</body>
</html>
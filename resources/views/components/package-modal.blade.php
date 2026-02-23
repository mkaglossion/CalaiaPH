{{-- resources/views/components/package-modal.blade.php --}}
@props([
    'id' => 'detailsModal',
    'price' => true,
    'buttonText' => 'Book Now',
    'buttonAction' => 'bookNow',
    'csrfToken' => csrf_token(),
    'cartRoute' => route('cart.add'),
    'bookingRoute' => route('booking.create'),
    'appendParams' => true,
    'imageApiUrl' => null,
    'customButtonAction' => null
])

{{-- ✅ MODAL MARKUP - Best Practices Implementation --}}
<div
  id="{{ $id }}"
  x-data="packageModal()"
  x-cloak
  x-show="modalOpen"
  @keydown.escape.window="closeModal()"
  class="fixed inset-0 z-9999 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity duration-300 ease-in-out"
  role="dialog"
  aria-modal="true"
  aria-labelledby="{{ $id }}Label"
  aria-describedby="{{ $id }}Description"
>
  <div
    @click.away="closeModal()"
    class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden relative transition-all duration-300 ease-in-out transform scale-95 x-cloak:scale-75"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
  >
    {{-- Close Button - Accessible Design --}}
    <button
      type="button"
      @click="closeModal()"
      class="absolute top-4 right-4 p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand/50 focus:ring-offset-2 z-10"
      aria-label="Close package details modal"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>

    {{-- Header Section --}}
    <div class="px-6 py-5 border-b border-gray-100">
      <h3 id="{{ $id }}Label" class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight">
        Package Details
      </h3>
      <p id="{{ $id }}Description" class="sr-only">
        View detailed information about the selected package including features, images, and pricing.
      </p>
    </div>

    {{-- Swiper Carousel --}}
    <div class="px-6 py-4">
      <div class="packageSwiper swiper rounded-xl overflow-hidden" data-swiper="modal">
        <div id="{{ $id }}Wrapper" class="swiper-wrapper">
          {{-- Slides will be injected by JavaScript --}}
          <div class="swiper-slide flex items-center justify-center bg-gray-100 h-64 sm:h-80 md:h-96">
            <div class="text-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand mx-auto mb-2"></div>
              <span class="text-gray-600 text-sm">Loading images...</span>
            </div>
          </div>
        </div>

        <div class="swiper-pagination"></div>

        {{-- Custom Navigation Buttons --}}
        <button type="button"
          class="absolute top-1/2 -translate-y-1/2 left-3 z-10 flex items-center justify-center w-10 h-10 rounded-full bg-black/60 text-white shadow-lg hover:bg-black/75 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/50"
          data-custom-prev
          aria-label="Previous slide">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <button type="button"
          class="absolute top-1/2 -translate-y-1/2 right-3 z-10 flex items-center justify-center w-10 h-10 rounded-full bg-black/60 text-white shadow-lg hover:bg-black/75 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/50"
          data-custom-next
          aria-label="Next slide">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>

    {{-- Dynamic Content Body --}}
    <div class="px-6 py-5">
      <div data-slot class="space-y-4 text-gray-700 text-sm sm:text-base">
        {{-- Content will be injected by JavaScript --}}
      </div>
    </div>

    {{-- Footer Section --}}
    <div class="px-6 py-5 border-t border-gray-100 bg-gray-50/50">
      @if($price)
        <div id="{{ $id }}PriceDisplay" class="mb-5 text-lg sm:text-xl font-bold text-gray-900">
          {{-- Price will be set by JavaScript --}}
        </div>
      @else
        <div id="{{ $id }}PriceDisplay" class="hidden"></div>
      @endif

      <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
        <button
          type="button"
          @click="closeModal()"
          class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
        >
          Close
        </button>

        <button
          id="{{ $id }}Btn"
          type="button"
          class="px-6 py-3 rounded-xl bg-brand text-white font-semibold hover:bg-brand/90 transition-all duration-200 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-brand/50 focus:ring-offset-2"
        >
          {{ $buttonText }}
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ✅ SCRIPT --}}
@once
<script>
  // Package Modal Management - Best Practices Implementation
  window.packageSwipers = window.packageSwipers || {};
  window.currentDaytourId = null;
  window.currentImageApiUrl = null;

  // Alpine.js data for modal - Improved State Management
  window.packageModal = function () {
    return {
      modalOpen: false,
      isLoading: false,

      openModal() {
        this.modalOpen = true;
        this.isLoading = false;
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = 'var(--scrollbar-width, 0px)';
      },

      closeModal() {
        this.modalOpen = false;
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        cleanupSwiper('{{ $id }}');
        window.currentDaytourId = null;
        window.currentImageApiUrl = null;
      }
    };
  };

  // Calculate scrollbar width for proper body padding
  function calculateScrollbarWidth() {
    const scrollDiv = document.createElement('div');
    scrollDiv.style.cssText = 'width: 100px; height: 100px; overflow: scroll; position: absolute; top: -9999px;';
    document.body.appendChild(scrollDiv);
    const scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
    document.body.removeChild(scrollDiv);
    return scrollbarWidth;
  }

  // Set CSS variable for scrollbar width
  document.documentElement.style.setProperty('--scrollbar-width', calculateScrollbarWidth() + 'px');

  // ✅ OPEN modal - Enhanced Error Handling & Performance
  window.openPackageModal = function(button) {
    const title = button.dataset.title || '';
    const details = JSON.parse(button.dataset.details || '[]');
    const daytourId = button.dataset.daytourId || null;
    const description = button.dataset.description || '';
    const price = button.dataset.price || '';
    const imageApiUrl = button.dataset.imageApiUrl || '{{ $imageApiUrl }}';

    window.currentDaytourId = daytourId;
    window.currentImageApiUrl = imageApiUrl;

    const modalElement = document.getElementById('{{ $id }}');
    if (modalElement) {
      if (modalElement._x_dataStack && modalElement._x_dataStack[0]) {
        modalElement._x_dataStack[0].openModal();
      } else {
        console.warn('Alpine.js instance not found for modal');
      }
    }

    // Show cached images first for instant loading
    let cachedImages = [];
    if (window.imageCache && daytourId && window.imageCache.has(daytourId)) {
      const entry = window.imageCache.get(daytourId);
      if (Array.isArray(entry)) cachedImages = entry;
    }

    showPackageDetails(title, details, cachedImages, description, price);

    // Fetch images asynchronously if not in cache
    if (typeof window.preloadDaytourImages === 'function' && daytourId) {
      window.preloadDaytourImages(daytourId).then((imgs) => {
        if (Array.isArray(imgs) && imgs.length) {
          updateModalImages(imgs);
        }
      }).catch((error) => {
        console.error('Error loading images:', error);
      });
    }
  };

  // ✅ Close helper - Improved Reliability
  window.closePackageModal = function() {
    const modalElement = document.getElementById('{{ $id }}');
    if (!modalElement) return;

    if (modalElement._x_dataStack && modalElement._x_dataStack[0]) {
      modalElement._x_dataStack[0].closeModal();
    } else {
      console.warn('Alpine.js instance not found, falling back to manual close');
      cleanupSwiper('{{ $id }}');
      window.currentDaytourId = null;
      window.currentImageApiUrl = null;
    }
  };

  // Cleanup Swiper instance - Prevent memory leaks
  function cleanupSwiper(modalId) {
    if (window.packageSwipers[modalId]) {
      try {
        window.packageSwipers[modalId].destroy(true, true);
      } catch (error) {
        console.warn('Error destroying swiper:', error);
      }
      window.packageSwipers[modalId] = null;
    }
  }

  // Get Swiper wrapper - Improved DOM traversal
  function getSwiperWrapper(modalId) {
    return document.getElementById(modalId + 'Wrapper') ||
           document.querySelector(`#${modalId} .packageSwiper .swiper-wrapper`);
  }

  // Build carousel slides - Enhanced XSS protection & video handling
  function buildCarouselSlides(modalId, imageUrls) {
    const wrapper = getSwiperWrapper(modalId);
    const swiperEl = document.querySelector(`#${modalId} .packageSwiper`);

    if (!wrapper) {
      console.error('Swiper wrapper not found for modalId:', modalId);
      return;
    }

    if (!imageUrls || imageUrls.length === 0) {
      wrapper.innerHTML = `
        <div class="swiper-slide flex items-center justify-center bg-gray-100 h-64 sm:h-80 md:h-96">
          <div class="text-center text-gray-600">
            <div class="text-lg font-semibold mb-2">No Images Available</div>
            <div class="text-sm">Images will be added soon</div>
          </div>
        </div>
      `;
      if (swiperEl) swiperEl.dataset.slidesCount = '0';
      return;
    }

    wrapper.innerHTML = imageUrls.map((url, index) => {
      const isVideo = url.includes('/video/');
      const safeUrl = encodeURI(decodeURI(url)).replace(/[^\w\-\./:]/g, '');
      const altText = `Slide ${index + 1}`;

      return `
        <div class="swiper-slide">
          ${isVideo ? `
            <video 
              src="${safeUrl}" 
              class="w-full h-64 sm:h-80 md:h-96 object-cover" 
              autoplay 
              muted 
              loop 
              playsinline 
              poster="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
              alt="${altText}"
            ></video>
          ` : `
            <img 
              src="${safeUrl}" 
              class="w-full h-64 sm:h-80 md:h-96 object-cover" 
              loading="lazy" 
              decoding="async" 
              alt="${altText}"
            >
          `}
        </div>
      `;
    }).join('');

    if (swiperEl) swiperEl.dataset.slidesCount = String(imageUrls.length);
  }

  // Initialize Swiper - Enhanced Configuration
  function initializeSwiper(modalId, imageUrls = []) {
    const swiperEl = document.querySelector(`#${modalId} .packageSwiper`);
    if (!swiperEl || !window.Swiper) return;

    // Cleanup existing swiper
    if (swiperEl.swiper) {
      try {
        swiperEl.swiper.destroy(true, true);
      } catch (error) {
        console.warn('Error destroying existing swiper:', error);
      }
    }

    const slidesCount = parseInt(swiperEl.dataset.slidesCount || '0', 10) || imageUrls.length;

    window.packageSwipers[modalId] = new Swiper(swiperEl, {
      slidesPerView: 1,
      loop: slidesCount > 1,
      effect: slidesCount > 1 ? 'fade' : 'slide',
      fadeEffect: { crossFade: true },
      autoplay: { 
        delay: 10000, 
        disableOnInteraction: false,
        pauseOnMouseEnter: true
      },
      speed: 500,
      navigation: {
        nextEl: swiperEl.querySelector('[data-custom-next]'),
        prevEl: swiperEl.querySelector('[data-custom-prev]')
      },
      pagination: {
        el: swiperEl.querySelector('.swiper-pagination'),
        clickable: true,
        dynamicBullets: slidesCount > 3
      },
      a11y: {
        enabled: true,
        prevSlideMessage: 'Previous slide',
        nextSlideMessage: 'Next slide'
      },
      keyboard: {
        enabled: true,
        onlyInViewport: true
      }
    });
  }

  // Update modal content - Enhanced accessibility
  function updateModalContent(modalId, title, price) {
    const titleEl = document.getElementById(modalId + 'Label');
    if (titleEl) {
      titleEl.textContent = `${title} Details`;
    } else {
      console.error('Title element not found for modalId:', modalId);
    }

    const priceEl = document.getElementById(modalId + 'PriceDisplay');
    if (priceEl) {
      priceEl.textContent = price || '';
    } else {
      console.error('Price display element not found for modalId:', modalId);
    }
  }

  // Build dynamic content - Enhanced XSS protection
  function buildDynamicContent(modalId, title, details, description) {
    const slot = document.querySelector(`#${modalId} [data-slot]`);
    if (!slot) return;

    let html = '';

    if (details && details.length) {
      html += `<h4 class="text-base sm:text-lg font-bold" style="color:#a75e3e;">${title} Experience</h4>`;
      html += `<div class="space-y-2">`;
      
      details.forEach(d => {
        // Enhanced XSS protection
        const escapedDetail = document.createElement('div');
        escapedDetail.textContent = d;
        html += `<p class="flex items-start gap-2"><span class="mt-0.5 text-brand"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></span><span>${escapedDetail.innerHTML}</span></p>`;
      });
      
      html += `</div>`;
    } else if (description) {
      html += `<h4 class="text-base sm:text-lg font-bold" style="color:#97655A;">${title} Details</h4>`;
      
      // Enhanced XSS protection
      const escapedDescription = document.createElement('div');
      escapedDescription.textContent = description;
      html += `<p>${escapedDescription.innerHTML}</p>`;
    }

    slot.innerHTML = html;
  }

  // Setup button action - Improved error handling
  function setupButtonAction(modalId) {
    const btn = document.getElementById(modalId + 'Btn');
    if (!btn) {
      console.error('Button element not found for modalId:', modalId);
      return;
    }

    btn.onclick = function() {
      const action = '{{ $buttonAction }}';
      const custom = '{{ $customButtonAction }}';

      if (custom && typeof window[custom] === 'function') {
        try {
          return window[custom]();
        } catch (error) {
          console.error('Error executing custom button action:', error);
        }
      }

      if (action === 'addToCart') {
        return handleAddToCart(modalId);
      }

      if (action === 'bookNow') {
        return handleBookNow(modalId);
      }

      console.warn('Unknown button action:', action);
    };
  }

  // Show package details - Enhanced flow
  function showPackageDetails(title, details, imageUrls, description, price = null) {
    const modalId = '{{ $id }}';
    cleanupSwiper(modalId);
    buildCarouselSlides(modalId, imageUrls);
    initializeSwiper(modalId, imageUrls);
    updateModalContent(modalId, title, price);
    buildDynamicContent(modalId, title, details, description);
    setupButtonAction(modalId);
  }

  // Update modal images - Optimized
  function updateModalImages(imageUrls) {
    const modalId = '{{ $id }}';
    if (!imageUrls || !Array.isArray(imageUrls)) return;

    cleanupSwiper(modalId);
    buildCarouselSlides(modalId, imageUrls);
    initializeSwiper(modalId, imageUrls);
  }

  // Handle add to cart - Enhanced security
  function handleAddToCart(modalId) {
    const title = (document.getElementById(modalId + 'Label')?.textContent || '').replace(' Details', '');
    const image = document.querySelector(`#${modalId} .packageSwiper img`)?.src || '';

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ $cartRoute }}';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ $csrfToken }}';
    form.appendChild(csrf);

    const isDining = title.toLowerCase().includes('menu');

    const fields = {
      id: title.toLowerCase().replace(/\s+/g, '-'),
      title,
      price: isDining ? '3500.00' : '0.00',
      image,
      quantity: '1',
      isDining: isDining ? '1' : '0'
    };

    Object.entries(fields).forEach(([key, value]) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  }

  // Handle book now - Enhanced URL handling
  function handleBookNow(modalId) {
    const title = (document.getElementById(modalId + 'Label')?.textContent || '').replace(' Details', '');
    const price = document.getElementById(modalId + 'PriceDisplay')?.textContent || 'PHP 0';

    const isDining = title.toLowerCase().includes('menu') || title.toLowerCase().includes('cook');
    const type = isDining ? 'staycation' : 'daytour';

    let url = '{{ route('booking.calendar') }}' + '?type=' + type;
    
    @if($appendParams)
    const params = new URLSearchParams({
      package: title,
      price: price
    });
    url += '&' + params.toString();
    @endif

    window.location.href = url;
  }
</script>
@endonce

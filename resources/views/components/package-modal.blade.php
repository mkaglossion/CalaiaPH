{{-- Reusable Package Modal Component --}}
@props([
    'id' => 'detailsModal',
    'images' => [],
    'price' => null,
    'buttonText' => 'Add to Cart',
    'buttonAction' => 'addToCart',
    'csrfToken' => csrf_token(),
    'cartRoute' => route('cart.add'),
    'bookingRoute' => route('booking.create'),
    'appendParams' => true,
    'imageApiUrl' => null,
    'customButtonAction' => null
])

{{-- Swiper CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

{{-- Modal Container --}}
<div id="{{ $id }}" x-data="packageModal()" :class="{ 'flex': modalOpen, 'hidden': !modalOpen }" class="hidden fixed inset-0 bg-black/50 items-center justify-center p-2 md:p-4 pt-16 md:pt-4 z-50" @keydown.escape.window="closeModal()" @click.self="closeModal()">
  <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[95vh] md:max-h-[90vh] flex flex-col border border-gray-300 mx-2 sm:mx-4">

    {{-- Modal Header --}}
    <div class="p-4 md:p-6 border-b border-gray-300 shrink-0">
      <div class="flex justify-between items-center">
        <h3 id="{{ $id }}Label" class="text-xl md:text-2xl font-bold text-gray-800" style="font-size: 25px; font-family: 'Gotham Book', sans-serif; color: #8f5d54;"></h3>
        <button type="button" class="text-gray-500 hover:text-gray-700 transition-colors" @click="closeModal()" aria-label="Close modal">
          <i class="bi bi-x-lg text-xl md:text-2xl"></i>
        </button>
      </div>
    </div>

    {{-- Modal Body --}}
    <div class="p-4 md:p-6 flex-1 overflow-y-auto" id="{{ $id }}Body">

      {{-- Image Carousel --}}
      <div class="swiper relative packageSwiper mb-4 md:mb-6 rounded-2xl min-h-75" data-slides-count="0" data-swiper="modal">
        <div class="swiper-wrapper rounded-2xl overflow-hidden" id="{{ $id }}Wrapper">
          {{-- Images will be loaded here dynamically --}}
        </div>

        {{-- Navigation Buttons --}}
        <x-slider-arrows-brand />

        {{-- Pagination Dots --}}
        <div class="swiper-pagination"></div>
      </div>

      {{-- Additional Content --}}
      <div data-slot>
        {{ $slot }}
      </div>

      {{-- Price Display --}}
      @if($price)
        <div id="{{ $id }}Price" class="mt-4 p-4 bg-linear-to-r from-[#8f5d54] to-[#a0675a] text-white rounded-xl text-center shadow-lg">
          <div class="flex items-center justify-center mb-1">
            <i class="bi bi-tag-fill text-lg mr-2"></i>
            <span class="text-xs font-medium uppercase tracking-wide">Starting from</span>
          </div>
          <p class="text-lg md:text-xl font-bold" id="{{ $id }}PriceDisplay">{{ $price }}</p>
        </div>
      @endif
    </div>

    {{-- Modal Footer --}}
    <div class="p-4 md:p-6 border-t border-gray-300 flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-4 justify-end shrink-0">
      <button type="button" id="{{ $id }}Btn" class="bg-brand text-white px-6 py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto font-semibold">
        {{ $buttonText }}
      </button>
      <button type="button" @click="closeModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">
        Close
      </button>
    </div>
  </div>
</div>

{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
// Package Modal Management
let packageSwipers = {};
let currentDaytourId = null;
let currentImageApiUrl = null;

// Alpine.js data for modal
function packageModal() {
  return {
    modalOpen: false,
    openModal() {
      this.modalOpen = true;
      document.body.style.overflow = 'hidden';
      // Also update classes as fallback
      const modalEl = document.getElementById('{{ $id }}');
      if (modalEl) {
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
      }
    },
    closeModal() {
      this.modalOpen = false;
      document.body.style.overflow = '';
      cleanupSwiper('{{ $id }}');
      currentDaytourId = null;
      currentImageApiUrl = null;
      // Also update classes as fallback
      const modalEl = document.getElementById('{{ $id }}');
      if (modalEl) {
        modalEl.classList.remove('flex');
        modalEl.classList.add('hidden');
      }
    }
  }
}

// Make openModal globally available
window.openPackageModal = function(button) {
  const title = button.dataset.title;
  const details = JSON.parse(button.dataset.details || '[]');
  const daytourId = button.dataset.daytourId;
  const description = button.dataset.description || '';
  const price = button.dataset.price;
  const imageApiUrl = button.dataset.imageApiUrl || '{{ $imageApiUrl }}';

  // Store current daytour ID for modal functionality
  currentDaytourId = daytourId;
  currentImageApiUrl = imageApiUrl;

  // Get the modal instance and open it
  const modalElement = document.getElementById('{{ $id }}');

  if (modalElement) {
    // Try Alpine.js first
    if (modalElement._x_dataStack && modalElement._x_dataStack[0]) {
      const modalData = modalElement._x_dataStack[0];
      modalData.openModal();
    } else {
      // Fallback: directly manipulate the element
      modalElement.classList.remove('hidden');
      modalElement.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }
  }

  // Check if images are already cached for instant display
  let cachedImages = [];
  if (window.imageCache && window.imageCache.has(daytourId)) {
    const cacheEntry = window.imageCache.get(daytourId);
    if (Array.isArray(cacheEntry) && cacheEntry.length > 0) {
      cachedImages = cacheEntry;
    }
  }

  // Always show modal with cached images immediately (no loading state)
  showPackageDetails(title, details, cachedImages, description, price);
};

window.closeModal = function() {
  const modalElement = document.getElementById('{{ $id }}');
  if (modalElement) {
    // Try Alpine.js first
    if (modalElement._x_dataStack && modalElement._x_dataStack[0]) {
      const modalData = modalElement._x_dataStack[0];
      modalData.closeModal();
    } else {
      // Fallback: directly manipulate the element
      modalElement.classList.remove('flex');
      modalElement.classList.add('hidden');
      document.body.style.overflow = '';
      cleanupSwiper('{{ $id }}');
      currentDaytourId = null;
      currentImageApiUrl = null;
    }
  }
};

window.closeModal = closeModal;

function updateModalImages(imageUrls) {
  const modalId = '{{ $id }}';
  const swiperInstance = packageSwipers[modalId];
  const swiperEl = document.querySelector('#' + modalId + ' .packageSwiper');

  if (swiperInstance && imageUrls.length > 0) {
    // If swiper exists and we have images, update slides smoothly
    const swiperWrapper = document.getElementById(modalId + 'Wrapper');

    // Build new slides with hero-slider structure
    const newSlides = imageUrls.map((url, index) => `
      <div class="swiper-slide relative flex items-center justify-center overflow-hidden">
        ${url.includes('/video/') ?
          `<video src="${url}" class="w-full h-64 md:h-96 object-cover" autoplay muted loop loading="lazy"></video>` :
          `<img src="${url}"
               alt="Slide Image ${index + 1}"
               class="w-full h-64 md:h-96 object-cover"
               loading="lazy">`
        }
      </div>
    `).join('');

    swiperWrapper.innerHTML = newSlides;

    // Update slides count
    if (swiperEl) swiperEl.dataset.slidesCount = imageUrls.length.toString();

    // Update swiper
    swiperInstance.update();
    swiperInstance.updateSlides();
    swiperInstance.updateSlidesClasses();

    // Reset to first slide smoothly
    swiperInstance.slideTo(0, 300);
  } else {
    // Fallback: recreate swiper if it doesn't exist
    cleanupSwiper(modalId);
    buildCarouselSlides(modalId, imageUrls);
    requestAnimationFrame(() => {
      initializeSwiper(modalId, imageUrls);
      setupCustomDots(modalId, imageUrls);
    });
  }
}

function showPackageDetails(title, details, imageUrls, description, price = null) {
  const modalId = '{{ $id }}';

  // Clean up previous swiper instance
  cleanupSwiper(modalId);

  // Build carousel slides
  buildCarouselSlides(modalId, imageUrls);

  // Initialize swiper with correct images
  initializeSwiper(modalId, imageUrls);

  // Update modal content
  updateModalContent(modalId, title, price);

  // Build dynamic content
  buildDynamicContent(modalId, title, details, description);

  // Setup button action
  setupButtonAction(modalId);
}

function cleanupSwiper(modalId) {
  if (packageSwipers[modalId]) {
    packageSwipers[modalId].destroy();
    packageSwipers[modalId] = null;
  }
}

function buildCarouselSlides(modalId, imageUrls) {
  const swiperWrapper = document.getElementById(modalId + 'Wrapper');
  const swiperEl = document.querySelector('#' + modalId + ' .packageSwiper');

  if (!imageUrls || imageUrls.length === 0) {
    swiperWrapper.innerHTML = `
      <div class="swiper-slide relative flex items-center justify-center bg-gray-200 overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center z-20 text-gray-600">
          <div class="text-center px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase"
                style="font-family: 'Gotham Book', sans-serif;">
              No Image Available
            </h2>
            <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase"
              style="font-family: 'Gotham Book', sans-serif; font-style: italic;">
              Images will be added soon
            </p>
          </div>
        </div>
      </div>
    `;
    if (swiperEl) swiperEl.dataset.slidesCount = '0';
    return;
  }

  swiperWrapper.innerHTML = imageUrls.map((url, index) => `
    <div class="swiper-slide relative flex items-center justify-center overflow-hidden">
      ${url.includes('/video/') ?
        `<video src="${url}" class="w-full h-64 md:h-96 object-cover" autoplay muted loop loading="lazy"></video>` :
        `<img src="${url}"
             alt="Slide Image ${index + 1}"
             class="w-full h-64 md:h-96 object-cover"
             loading="lazy">`
      }
    </div>
  `).join('');

  if (swiperEl) swiperEl.dataset.slidesCount = imageUrls.length.toString();
}

function updateModalContent(modalId, title, price) {
  // Update title
  document.getElementById(modalId + 'Label').textContent = `${title} Details`;

  // Update price if provided
  if (price && document.getElementById(modalId + 'PriceDisplay')) {
    document.getElementById(modalId + 'PriceDisplay').textContent = price;
  }
}

function buildDynamicContent(modalId, title, details, description) {
  const slotContainer = document.querySelector(`#${modalId} [data-slot]`) ||
                       document.querySelector(`#${modalId}Body > :not(.swiper):not([id$="Price"])`);

  if (!slotContainer) return;

  // Create dynamic content
  let contentHTML = '';

  if (details && details.length > 0) {
    contentHTML += `
      <div class="space-y-4">
        <h4 class="text-lg font-semibold mb-3" style="color:#a75e3e;">${title} Experience</h4>
        <div class="space-y-3">
    `;

    details.forEach(detail => {
      contentHTML += `
        <p class="flex items-start text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
          <i class="bi bi-check-circle-fill text-[#a75e3e] mr-3 mt-1 shrink-0"></i>
          <span class="flex-1">${detail}</span>
        </p>
      `;
    });

    contentHTML += `
        </div>
      </div>
    `;
  } else if (description) {
    contentHTML = `
      <div class="space-y-4">
        <h4 class="text-lg font-semibold mb-3" style="color:#97655A;">${title} Details</h4>
        <p class="text-gray-700" style="font-family: 'Gotham Book', sans-serif;">${description}</p>
      </div>
    `;
  }

  slotContainer.innerHTML = contentHTML;
}

function initializeSwiper(modalId, imageUrls = []) {
  // Self-contained swiper initialization for modal
  const swiperEl = document.querySelector('#' + modalId + ' .packageSwiper');
  if (swiperEl && window.Swiper) {
    // Destroy existing swiper if it exists
    if (swiperEl.swiper) {
      swiperEl.swiper.destroy();
    }

    const slidesCount = parseInt(swiperEl.dataset.slidesCount) || imageUrls.length;

    // Create new swiper instance with hero-slider configuration
    packageSwipers[modalId] = new Swiper(swiperEl, {
      autoplay: {
        delay: 10000,
        disableOnInteraction: false,
      },
      loop: slidesCount > 1,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      keyboard: {
        enabled: true,
        onlyInViewport: false,
      },
      navigation: {
        nextEl: swiperEl.querySelector('[data-custom-next]'),
        prevEl: swiperEl.querySelector('[data-custom-prev]')
      },
      pagination: {
        el: swiperEl.querySelector('.swiper-pagination'),
        clickable: true
      },
      a11y: {
        prevSlideMessage: 'Previous slide',
        nextSlideMessage: 'Next slide',
        firstSlideMessage: 'This is the first slide',
        lastSlideMessage: 'This is the last slide',
      },
    });
  }
}

function setupCustomDots(modalId, imageUrls) {
  // Custom dots are now handled by swiper-pagination
  // This function is kept for backward compatibility but does nothing
}

function setupButtonAction(modalId) {
  document.getElementById(modalId + 'Btn').onclick = function() {
    const buttonAction = '{{ $buttonAction }}';
    const customButtonAction = '{{ $customButtonAction }}';

    if (customButtonAction) {
      // Use custom button action if provided
      if (typeof window[customButtonAction] === 'function') {
        window[customButtonAction]();
      } else {
        console.error('Custom button action function not found:', customButtonAction);
      }
    } else if (buttonAction === 'addToCart') {
      handleAddToCart();
    } else if (buttonAction === 'bookNow') {
      handleBookNow();
    }
  };
}

// Cart and Booking Functions
function handleAddToCart() {
  const title = document.getElementById('{{ $id }}Label').textContent.replace(' Details', '');
  const image = document.querySelector('.packageSwiper img')?.src || '';

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ $cartRoute }}';

  const csrfToken = document.createElement('input');
  csrfToken.type = 'hidden';
  csrfToken.name = '_token';
  csrfToken.value = '{{ $csrfToken }}';
  form.appendChild(csrfToken);

  const isDining = title.toLowerCase().includes('menu');

  const fields = {
    'id': title.toLowerCase().replace(/\s+/g, '-'),
    'title': title,
    'price': isDining ? '3500.00' : '0.00',
    'image': image,
    'quantity': '1',
    'isDining': isDining ? '1' : '0'
  };

  for (const [name, value] of Object.entries(fields)) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
}

function handleBookNow() {
  const title = document.getElementById('{{ $id }}Label').textContent.replace(' Details', '');
  const price = document.getElementById('{{ $id }}PriceDisplay')?.textContent || 'PHP 0';

  const isDining = title.toLowerCase().includes('menu') || title.toLowerCase().includes('cook');
  const type = isDining ? 'dining' : 'daytour';

  let url = '{{ $bookingRoute }}';
  if ({{ $appendParams ? 'true' : 'false' }}) {
    url += '?type=' + type + '&package=' + encodeURIComponent(title) + '&price=' + encodeURIComponent(price);
  }
  window.location.href = url;
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
  // Modal initialization if needed
});
</script>

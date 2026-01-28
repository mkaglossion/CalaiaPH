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
    'appendParams' => true
])

{{-- Modal Container --}}
<div id="{{ $id }}" class="fixed inset-0 bg-transparent flex items-center justify-center p-2 md:p-4 pt-16 md:pt-4 opacity-0 pointer-events-none z-50 transition-opacity duration-300">
  <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[95vh] md:max-h-[90vh] flex flex-col border border-gray-300 mx-2 sm:mx-4">

    {{-- Modal Header --}}
    <div class="p-4 md:p-6 border-b border-gray-300 shrink-0">
      <div class="flex justify-between items-center">
        <h3 id="{{ $id }}Label" class="text-xl md:text-2xl font-bold text-gray-800" style="font-size: 25px; font-family: 'Gotham Book', sans-serif; color: #8f5d54;"></h3>
        <button type="button" class="text-gray-500 hover:text-gray-700 transition-colors" onclick="closeModal('{{ $id }}')" aria-label="Close modal">
          <i class="bi bi-x-lg text-xl md:text-2xl"></i>
        </button>
      </div>
    </div>

    {{-- Modal Body --}}
    <div class="p-4 md:p-6 flex-1 overflow-y-auto" id="{{ $id }}Body">

      {{-- Image Carousel --}}
      <div class="swiper relative packageSwiper mb-4 md:mb-6 rounded-2xl min-h-75" data-swiper="modal">
        <div class="swiper-wrapper rounded-2xl overflow-hidden" id="{{ $id }}Wrapper"></div>

        {{-- Navigation Buttons --}}
        <button type="button" id="{{ $id }}-prev" aria-label="Previous slide"
                class="swiper-button-prev-custom absolute top-1/2 -translate-y-1/2 left-4 z-20 grid place-items-center w-10 h-10 rounded-full bg-[rgba(167,94,62,0.1)] text-brand border-2 border-brand hover:bg-brand hover:text-white transition-all duration-300"
                data-custom-prev>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button type="button" id="{{ $id }}-next" aria-label="Next slide"
                class="swiper-button-next-custom absolute top-1/2 -translate-y-1/2 right-4 z-20 grid place-items-center w-10 h-10 rounded-full bg-[rgba(167,94,62,0.1)] text-brand border-2 border-brand hover:bg-brand hover:text-white transition-all duration-300"
                data-custom-next>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        {{-- Custom Dots --}}
        <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
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
          <p class="text-lg md:text-xl font-bold font-gotham" id="{{ $id }}PriceDisplay">{{ $price }}</p>
        </div>
      @endif
    </div>

    {{-- Modal Footer --}}
    <div class="p-4 md:p-6 border-t border-gray-300 flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-4 justify-end shrink-0">
      <button type="button" id="{{ $id }}Btn" class="bg-brand text-white px-6 py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto font-semibold">
        {{ $buttonText }}
      </button>
      <button type="button" onclick="closeModal('{{ $id }}')" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">
        Close
      </button>
    </div>
  </div>
</div>

<script>
// Package Modal Management
let packageSwipers = {};

// Modal Functions
function openModal(button) {
  const title = button.dataset.title;
  const details = JSON.parse(button.dataset.details || '[]');
  const images = JSON.parse(button.dataset.images || '[]');
  const description = button.dataset.description || '';
  const price = button.dataset.price;
  showPackageDetails(title, details, images, description, price);
}

function showPackageDetails(title, details, imageUrls, description, price = null) {
  const modalId = '{{ $id }}';

  // Clean up previous swiper instance
  cleanupSwiper(modalId);

  // Build carousel slides
  buildCarouselSlides(modalId, imageUrls);

  // Update modal content
  updateModalContent(modalId, title, price);

  // Build dynamic content
  buildDynamicContent(modalId, title, details, description);

  // Initialize swiper
  initializeSwiper(modalId);

  // Setup custom dots
  setupCustomDots(modalId, imageUrls);

  // Setup button action
  setupButtonAction(modalId);

  // Show modal
  showModal(modalId);
}

function cleanupSwiper(modalId) {
  if (packageSwipers[modalId]) {
    packageSwipers[modalId].destroy();
    packageSwipers[modalId] = null;
  }
}

function buildCarouselSlides(modalId, imageUrls) {
  const swiperWrapper = document.getElementById(modalId + 'Wrapper');
  swiperWrapper.innerHTML = imageUrls.map((url, index) => `
    <div class="swiper-slide">
      <img src="${url}" class="w-full h-64 md:h-96 object-cover" alt="Image ${index + 1}" loading="lazy">
    </div>
  `).join('');
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

function initializeSwiper(modalId) {
  packageSwipers[modalId] = new Swiper('.packageSwiper', {
    slidesPerView: 1,
    loop: true,
    watchOverflow: false,
    navigation: {
      nextEl: '#' + modalId + '-next',
      prevEl: '#' + modalId + '-prev'
    },
  });
}

function setupCustomDots(modalId, imageUrls) {
  const dotsEl = document.querySelector('.packageSwiper [data-custom-dots]');
  const numSlides = imageUrls.length;

  function updateDots() {
    dotsEl.innerHTML = '';
    for (let i = 0; i < numSlides; i++) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'h-2.5 rounded-full ' +
        (i === packageSwipers[modalId].realIndex ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
      button.setAttribute('role', 'tab');
      button.setAttribute('aria-label', `Go to slide ${i + 1}`);
      button.addEventListener('click', () => packageSwipers[modalId].slideToLoop(i));
      dotsEl.appendChild(button);
    }
  }

  updateDots();
  packageSwipers[modalId].on('slideChange', updateDots);
}

function setupButtonAction(modalId) {
  document.getElementById(modalId + 'Btn').onclick = function() {
    const buttonAction = '{{ $buttonAction }}';
    if (buttonAction === 'addToCart') {
      handleAddToCart();
    } else if (buttonAction === 'bookNow') {
      handleBookNow();
    }
  };
}

function showModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  document.body.style.overflow = 'hidden';
  modal.focus();
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  document.body.style.overflow = '';

  cleanupSwiper(modalId);
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
  const modal = document.getElementById('{{ $id }}');

  // Close on outside click
  modal.addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal('{{ $id }}');
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('opacity-100')) {
      closeModal('{{ $id }}');
    }
  });
});
</script>

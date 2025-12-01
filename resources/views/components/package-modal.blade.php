{{-- Reusable Package Modal Component --}}
@props([
    'id' => 'detailsModal',
    'title' => '',
    'details' => [],
    'images' => [],
    'price' => null,
    'buttonText' => 'Add to Cart',
    'buttonAction' => 'addToCart',
    'csrfToken' => csrf_token(),
    'cartRoute' => route('cart.add'),
    'bookingRoute' => route('booking.create')
])

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
      {{-- Swiper Carousel --}}
      <div class="swiper relative packageSwiper mb-4 md:mb-6 rounded-2xl overflow-hidden" data-swiper="modal">
        <div class="swiper-wrapper" id="{{ $id }}Wrapper"></div>
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
      <div id="{{ $id }}Details" class="space-y-3"></div>

      {{-- Package Price (if provided) --}}
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
      <button type="button" id="{{ $id }}Btn" class="bg-[#97655A] text-white px-6 py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto font-semibold">
        {{ $buttonText }}
      </button>
      <button type="button" onclick="closeModal('{{ $id }}')" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">
        Close
      </button>
    </div>
  </div>
</div>

<script>
let packageSwipers = {};

function openModal{{ $id }}(title, details, images, price = null) {
  viewDetails{{ $id }}(title, details, images, price);
}

function viewDetails{{ $id }}(title, descriptionParagraphs, imageUrls, price = null) {
  const modalId = '{{ $id }}';
  // Destroy previous swiper instance if exists
  if (packageSwipers[modalId]) {
    packageSwipers[modalId].destroy();
    packageSwipers[modalId] = null;
  }

  // Build carousel slides
  const swiperWrapper = document.getElementById(modalId + 'Wrapper');
  swiperWrapper.innerHTML = imageUrls.map((url, index) => `
    <div class="swiper-slide">
      <img src="${url}" class="w-full h-64 md:h-96 object-cover" alt="${title} - Image ${index + 1}" loading="lazy">
    </div>
  `).join('');

  // Build package details
  const packageDetails = document.getElementById(modalId + 'Details');
  packageDetails.innerHTML = descriptionParagraphs.map(p => `
    <p class="flex items-start text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
      <i class="bi bi-check-circle-fill text-[#97655A] mr-3 mt-1 flex-shrink-0"></i>
      <span class="flex-1">${p}</span>
    </p>
  `).join('');

  // Update modal title
  document.getElementById(modalId + 'Label').textContent = `${title} Details`;

  // Update price if provided
  if (price && document.getElementById(modalId + 'PriceDisplay')) {
    document.getElementById(modalId + 'PriceDisplay').textContent = price;
  }

  // Initialize package swiper
  packageSwipers[modalId] = new Swiper('.packageSwiper', {
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
      b.className = 'h-2.5 rounded-full ' + (i === packageSwipers[modalId].realIndex ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
      b.setAttribute('role','tab');
      b.setAttribute('aria-label', `Go to slide ${i+1}`);
      b.addEventListener('click', ()=> packageSwipers[modalId].slideToLoop(i));
      dotsEl.appendChild(b);
    }
  }
  updateDots();
  packageSwipers[modalId].on('slideChange', updateDots);

  // Set up button action
  document.getElementById(modalId + 'Btn').onclick = function() {
    if ('{{ $buttonAction }}' === 'addToCart') {
      addToCart(title, imageUrls[0] || '');
    } else if ('{{ $buttonAction }}' === 'bookNow') {
      bookTour(title, price || 'PHP 0');
    }
  };

  // Show modal
  const modal = document.getElementById(modalId);
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  document.body.style.overflow = 'hidden';

  // Focus management for accessibility
  modal.focus();
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  document.body.style.overflow = '';

  if (packageSwipers[modalId]) {
    packageSwipers[modalId].destroy();
    packageSwipers[modalId] = null;
  }
}

function addToCart(title, image) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ $cartRoute }}';

  const csrfToken = document.createElement('input');
  csrfToken.type = 'hidden';
  csrfToken.name = '_token';
  csrfToken.value = '{{ $csrfToken }}';
  form.appendChild(csrfToken);

  const fields = {
    'id': title.toLowerCase().replace(/\s+/g, '-'),
    'title': title,
    'price': '1500.00',
    'image': image,
    'quantity': '1'
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

function bookTour(packageName, price) {
  const url = '{{ $bookingRoute }}?type=daytour&package=' + encodeURIComponent(packageName) + '&price=' + encodeURIComponent(price);
  window.location.href = url;
}

// Close modal on outside click and Escape key
document.getElementById('{{ $id }}').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal('{{ $id }}');
  }
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && document.getElementById('{{ $id }}').classList.contains('opacity-100')) {
    closeModal('{{ $id }}');
  }
});
</script>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Day Tour</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css'])
  <link href="https://fonts.googleapis.com/css2?family=Vila+Zilka+Serif:ital@0;1&family=Gotham:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <x-header active="daytour" />
    {{-- Hero Slider --}}
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="swiper mySwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
      <div class="swiper-wrapper">
        @forelse ($swiperImages as $image)
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
                @if($image->title)
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase" 
                      style="font-family: gotham; font-size: 3.5rem;">
                    {{ $image->title }}
                  </h2>
                @endif
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" 
                  style="font-family: 'vila zilka serif', serif; font-style: italic; font-size: 1.2rem;">
                  Experience the beauty of nature
                </p>
              </div>
            </div>
          </div>
        @empty
          <div class="swiper-slide flex items-center justify-center bg-gray-200 text-gray-600">
            <p>No images available</p>
          </div>
        @endforelse
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

  {{-- Footer --}}
  <x-footer />

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize hero swiper
    const heroSwiper = new Swiper('.heroSwiper', {
      loop: true,
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
      spaceBetween: 0,
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
        renderBullet: function (index, className) {
          return `<span class="${className} !w-6 !h-1 !bg-white !opacity-50 !rounded-none !mx-1"></span>`;
        },
      },
    });

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
      'Adventure Package': '₱1,500',
      'Family Fun Package': '₱2,000',
      'Nature Experience': '₱1,200'
    };
    return prices[packageName] || '₱0';
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

  <style>
  .font-gotham {
    font-family: 'Gotham', sans-serif;
  }
  .font-vila-zilka {
    font-family: 'Vila Zilka Serif', serif;
  }

  /* Swiper custom styles */
  .swiper-button-next,
  .swiper-button-prev {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 50%;
    padding: 8px;
  }
  .swiper-button-next:after,
  .swiper-button-prev:after {
    font-size: 16px;
    font-weight: bold;
  }
  </style>
</body>
</html>
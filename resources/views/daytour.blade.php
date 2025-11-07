<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Day Tour</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>

<body class="bg-white">
  {{-- Success Message --}}
  <div class="flex flex-col items-center justify-center px-4 py-4">
    @if (session('success'))
      <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
        {{ session('success') }}
      </div>
    @endif
  </div>

  {{-- Header --}}
  <x-header active="daytour" />

  {{-- Hero Slider --}}
  <div class="swiper mySwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
    <div class="swiper-wrapper">
      {{-- Slide 1 --}}
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        <img src="{{ asset('assets/images/carousel/slides CALAIA-01.png') }}" alt="Day Tour Slide 1" class="w-full h-full object-cover object-center" loading="lazy">
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4" style="font-family: gotham; text-transform: uppercase; font-size: 3.5rem;">Day Tour Adventures</h2>
            <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family: 'vila zilka seriff', serif; font-style: italic; font-size: 1.2rem;">Explore the ranch</p>
          </div>
        </div>
      </div>

      {{-- Slide 2 --}}
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        <img src="{{ asset('assets/images/carousel/slides CALAIA-02.png') }}" alt="Day Tour Slide 2" class="w-full h-full object-cover object-center" loading="lazy">
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4" style="font-family: gotham; text-transform: uppercase; font-size: 3.5rem;">Guided Tours</h2>
            <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family: 'vila zilka seriff', serif; font-style: italic; font-size: 1.2rem;">Discover nature's beauty</p>
          </div>
        </div>
      </div>

      {{-- Slide 3 --}}
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        <img src="{{ asset('assets/images/carousel/slides CALAIA-03.png') }}" alt="Day Tour Slide 3" class="w-full h-full object-cover object-center" loading="lazy">
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4" style="font-family: gotham; text-transform: uppercase; font-size: 3.5rem;">Memorable Experiences</h2>
            <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family: 'vila zilka seriff', serif; font-style: italic; font-size: 1.2rem;">Create lasting memories</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Day Tour Packages Section --}}
  <main class="py-8 md:py-12 bg-gray-50">
    <section class="max-w-6xl mx-auto px-4">
      {{-- Daytour Cards Component --}}
      <x-daytour-cards />
    </section>
  </main>

  {{-- Dynamic Details Modal --}}
  <div class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center p-2 md:p-4 pt-16 md:pt-4 hidden" id="detailsModal">
    <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[95vh] md:max-h-[90vh] flex flex-col border border-gray-300 mx-2 sm:mx-4">
      {{-- Modal Header --}}
      <div class="p-4 md:p-6 border-b border-gray-300 flex-shrink-0">
        <div class="flex justify-between items-center">
          <h3 class="text-xl md:text-2xl font-bold" id="detailsModalLabel"></h3>
          <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeModal()">
            <i class="bi bi-x-lg text-xl md:text-2xl"></i>
          </button>
        </div>
      </div>

      {{-- Modal Body --}}
      <div class="p-4 md:p-6 flex-1 overflow-y-auto" id="detailsModalBody">
        {{-- Swiper Carousel --}}
        <div class="swiper packageSwiper mb-4 md:mb-6" style="position: relative;">
          <div class="swiper-wrapper" id="swiperWrapper"></div>
          {{-- Navigation arrows --}}
          <div class="swiper-button-next md:w-[30px] md:h-[30px] md:-mt-[15px]" style="color: rgba(255, 255, 255, 0.8); border-radius: 50%; width: 20px; height: 20px; margin-top: -10px; right: 5px; background: transparent;"></div>
          <div class="swiper-button-prev md:w-[30px] md:h-[30px] md:-mt-[15px]" style="color: rgba(255, 255, 255, 0.8); border-radius: 50%; width: 20px; height: 20px; margin-top: -10px; left: 5px; background: transparent;"></div>
          {{-- Pagination --}}
          <div class="swiper-pagination" style="bottom: 5px;"></div>
        </div>
        {{-- Package Details --}}
        <div id="packageDetails"></div>
      </div>

      {{-- Modal Footer --}}
      <div class="p-4 md:p-6 border-t border-gray-300 flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-4 justify-center flex-shrink-0">
        <button type="button" class="bg-[#8f5d54] text-white px-4 md:px-6 py-2 md:py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto" id="bookNowBtn">Book Now</button>
        <button type="button" class="bg-gray-500 text-white px-4 md:px-6 py-2 md:py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto" onclick="closeModal()">Close</button>
      </div>
    </div>
  </div>

  {{-- Footer --}}
  <x-footer />

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  {{-- Swiper Initialization --}}
  @verbatim
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const swiper = new Swiper('.mySwiper', {
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      loop: true,
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
        paginationBulletMessage: 'Go to slide {{index}}',
      },
    });
  });
  </script>
  @endverbatim

  {{-- Modal Functionality --}}
  <script>
  let packageSwiper;
  let lastFocusedElement;

  // Initialize modal event listeners
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('button[data-title]').forEach(button => {
      button.addEventListener('click', function() {
        lastFocusedElement = this;
        const title = this.getAttribute('data-title');
        const details = JSON.parse(this.getAttribute('data-details'));
        const gallery = JSON.parse(this.getAttribute('data-gallery'));
        viewDetails(title, details, gallery);
      });
    });
  });

  // Open modal with package details
  function viewDetails(title, descriptionParagraphs, imageUrls) {
    // Destroy previous swiper instance if exists
    if (packageSwiper) {
      packageSwiper.destroy();
    }

    // Build carousel slides
    let slidesHtml = '';
    imageUrls.forEach((url, index) => {
      slidesHtml += `
        <div class="swiper-slide relative">
          <img src="${url}" class="w-full h-64 md:h-96 object-cover rounded-2xl" alt="${title} - Image ${index + 1}">
        </div>
      `;
    });
    document.getElementById('swiperWrapper').innerHTML = slidesHtml;

    // Build package details
    let descriptionHtml = descriptionParagraphs.map(p => `
      <p class="flex items-start mb-3">
        <i class="bi bi-check-circle-fill text-blue-500 mr-3 mt-1 flex-shrink-0"></i>
        <span>${p}</span>
      </p>
    `).join('');
    document.getElementById('packageDetails').innerHTML = descriptionHtml;
    document.getElementById('detailsModalLabel').textContent = title + ' Details';

    // Initialize swiper
    packageSwiper = new Swiper('.packageSwiper', {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
        renderBullet: function (index, className) {
          return '<span class="' + className + '" style="width: 20px; height: 3px; background: white; opacity: 0.5; border-radius: 0; margin: 0 2px; display: inline-block;"></span>';
        },
        renderBulletActive: function (index, className) {
          return '<span class="' + className + ' swiper-pagination-bullet-active" style="width: 20px; height: 3px; background: white; opacity: 1; border-radius: 0; margin: 0 2px; display: inline-block;"></span>';
        }
      },
      breakpoints: {
        640: {
          spaceBetween: 30,
        }
      }
    });

    // Set up book now button
    document.getElementById('bookNowBtn').onclick = function() {
      bookTour(title, getPackagePrice(title));
    };

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Prevent scroll events from bubbling
    const modalBody = document.getElementById('detailsModalBody');
    const preventScroll = function(e) {
      e.stopPropagation();
    };
    modalBody.addEventListener('wheel', preventScroll);
    modalBody.addEventListener('touchmove', preventScroll);
    modalBody._preventScroll = preventScroll;

    // Show modal
    document.getElementById('detailsModal').classList.remove('hidden');
    document.getElementById('detailsModal').focus();
  }

  // Close modal
  function closeModal() {
    // Restore body scroll
    document.body.style.overflow = '';

    // Remove scroll prevention listeners
    const modalBody = document.getElementById('detailsModalBody');
    if (modalBody._preventScroll) {
      modalBody.removeEventListener('wheel', modalBody._preventScroll);
      modalBody.removeEventListener('touchmove', modalBody._preventScroll);
      delete modalBody._preventScroll;
    }

    // Hide modal
    document.getElementById('detailsModal').classList.add('hidden');
    if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
  }

  // Get package price
  function getPackagePrice(packageName) {
    const prices = {
      'Adventure Package': 1500,
      'Family Fun Package': 2000,
      'Nature Experience': 1200
    };
    return prices[packageName] || 0;
  }

  // Handle booking
  function bookTour(packageName, price) {
    console.log(`Booking function called with package: ${packageName}, price: ₱${price.toLocaleString()}`);
    alert(`Booking ${packageName} for ₱${price.toLocaleString()}. You will be redirected to the booking form.`);
    window.location.href = '{{ url("/booking") }}?type=daytour&package=' + encodeURIComponent(packageName) + '&price=' + price;
  }

  // Close modal on outside click
  document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });

  // Handle keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (!document.getElementById('detailsModal').classList.contains('hidden')) {
      if (e.key === 'Escape') {
        closeModal();
      } else if (e.key === 'Tab') {
        const modal = document.getElementById('detailsModal');
        const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) {
          if (document.activeElement === firstElement) {
            lastElement.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === lastElement) {
            firstElement.focus();
            e.preventDefault();
          }
        }
      }
    }
  });
  </script>
</body>
</html>

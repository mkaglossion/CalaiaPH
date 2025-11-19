<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- Basic SEO --}}
    <meta name="description" content="Calaia Farm — Food & Beverage: Set menus, special menus, and snack bar options for groups and families." />
    <meta name="keywords" content="Calaia Farm, CalaiaPH, food, beverage, set menus, special menus, snack bar, farm to table" />
    <meta name="author" content="CalaiaPH" />

    <title>CalaiaPH - Food & Beverage</title>

    {{-- Tailwind via Vite --}}
    @vite(['resources/css/app.css'])

    {{-- Icons / Swiper --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  </head>

  <body class="bg-[#f5efe7]">
    <x-header active="dining" />

    <div class="min-h-screen">

      {{-- Hero Slider (matches dining.blade.php exactly) --}}
      <div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
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

      {{-- CARDS SECTION (updated to match mock) --}}
      <section id="set-menu" class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">

          {{-- Card 1 --}}
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100">
            {{-- inset image frame --}}
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ asset('images/carousel/SET MENUS2.JPG') }}"
                alt="Set Menus"
                class="w-full h-64 sm:h-72 md:h-80 object-cover"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 text-3xl font-extrabold uppercase tracking-wide" style="color:#97655A;">
                ADVENTURE PACKAGE
              </h3>

              <button
                data-title="ADVENTURE PACKAGE"
                data-details='["Ready-made meals for families, couples, or groups","Breakfast, lunch, and dinner options"]'
                data-images='["{{ asset('images/carousel/SET MENUS2.JPG') }}","{{ asset('images/carousel/SET MEALS.jpg') }}","{{ asset('images/carousel/menu1.jpg') }}"]'
                onclick="openModal(this)"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4"
                style="background:#97655A;color:#ffffff;">
                LEARN MORE
              </button>
            </div>
          </div>

          {{-- Card 2 --}}
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100">
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ asset('images/carousel/menu1.jpg') }}"
                alt="Special Menus"
                class="w-full h-64 sm:h-72 md:h-80 object-cover"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 text-3xl font-extrabold uppercase tracking-wide" style="color:#97655A;">
                FAMILY FUN PACKAGE
              </h3>

              <button
                data-title="FAMILY FUN PACKAGE"
                data-details='["Harvest Your Own – Pick and cook farm-fresh ingredients","Cochinillo – Whole roast pig (pre-order required)"]'
                data-images='["{{ asset('images/carousel/menu1.jpg') }}","{{ asset('images/carousel/menu2.jpg') }}","{{ asset('images/carousel/menu4.jpg') }}"]'
                onclick="openModal(this)"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4"
                style="background:#97655A;color:#ffffff;">
                LEARN MORE
              </button>
            </div>
          </div>

          {{-- Card 3 --}}
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100">
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ asset('images/carousel/menu3.jpg') }}"
                alt="Snack Bar"
                class="w-full h-64 sm:h-72 md:h-80 object-cover"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 text-3xl font-extrabold uppercase tracking-wide" style="color:#97655A;">
                NATURE EXPERIENCE
              </h3>

              <button
                data-title="NATURE EXPERIENCE"
                data-details='["All-day light snacks and drinks","Includes smoothies, juice, sandwiches, and more"]'
                data-images='["{{ asset('images/carousel/menu3.jpg') }}","{{ asset('images/carousel/menu1.jpg') }}","{{ asset('images/carousel/menu4.jpg') }}"]'
                onclick="openModal(this)"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4"
                style="background:#97655A;color:#ffffff;">
                LEARN MORE
              </button>
            </div>
          </div>

        </div>
      </section>

      {{-- FLOATING BUTTONS (optional component) --}}
      {{-- <x-floating-buttons /> --}}

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
          </div>

          {{-- Modal Footer --}}
          <div class="p-4 md:p-6 border-t border-gray-300 flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-4 justify-center flex-shrink-0">
            <button type="button" id="bookNowBtn" class="bg-[#97655A] text-white px-6 py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto font-semibold">
              Book Now
            </button>
            <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">
              Close
            </button>
          </div>
        </div>
      </div>

    </div>

    {{-- Floating Buttons --}}
    <x-floating-buttons />
    <x-footer />

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Initialize hero swiper
      const heroSwiper = new Swiper('.heroSwiper', {
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
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
    });

    let packageSwiper = null;
    let lastFocusedElement = null;

    function openModal(button) {
      const title = button.dataset.title;
      const details = JSON.parse(button.dataset.details);
      const images = JSON.parse(button.dataset.images);
      viewDetails(title, details, images);
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
          <i class="bi bi-check-circle-fill text-[#97655A] mr-3 mt-1 flex-shrink-0"></i>
          <span class="flex-1">${p}</span>
        </p>
      `).join('');

      // Update modal title
      document.getElementById('detailsModalLabel').textContent = `${title} Details`;

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
        window.location.href = '{{ url("/booking") }}';
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

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
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  </head>

  <body class="bg-[#f5efe7]">
  {{-- flash message --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <x-flash-message type="success" :message="session('success')" />
      @endif
    </div>
    <x-header active="dining" />

      {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

      {{-- CARDS SECTION (updated to match mock) --}}
      <section id="set-menu" class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">

          {{-- Card 1 --}}
          @php
              $menu1Images = isset($menus[0]) && $menus[0]->swiperImages->count() > 0
                  ? $menus[0]->swiperImages->pluck('image_path')->toArray()
                  : [asset('images/carousel/menu1.jpg'), asset('images/carousel/SET MEALS.jpg'), asset('images/carousel/menu1.jpg')];
          @endphp
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
            {{-- inset image frame --}}
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ isset($menus[0]) ? ($menus[0]->swiperImages->first()->image_path ?? asset('images/carousel/menu1.jpg')) : asset('images/carousel/menu1.jpg') }}"
                alt="{{ $menus[0]->name ?? 'Set Menus' }}"
                class="w-full h-64 sm:h-72 md:h-80 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 font-extrabold uppercase tracking-wide" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[0]->name ?? 'SET MENUS' }}
              </h3>
              <p class="mb-2" style="color:#97655A; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[0]->name ?? 'SET MENUS' }} ARE AVAILABLE FOR GROUPS AND FAMILIES.
              </p>

              <button
                data-title="{{ $menus[0]->name ?? 'SET MENUS' }}"
                data-details='["Ready-made meals for families, couples, or groups","Breakfast, lunch, and dinner options"]'
                data-images='@json($menu1Images)'
                onclick="openModal(this)"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4"
                style="background:#97655A;color:#ffffff;">
                LEARN MORE
              </button>
            </div>
          </div>

          {{-- Card 2 --}}
          @php
              $menu2Images = isset($menus[1]) && $menus[1]->swiperImages->count() > 0
                  ? $menus[1]->swiperImages->pluck('image_path')->toArray()
                  : [asset('images/carousel/menu2.jpg'), asset('images/carousel/menu2.jpg'), asset('images/carousel/menu4.jpg')];
          @endphp
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ isset($menus[1]) ? ($menus[1]->swiperImages->first()->image_path ?? asset('images/carousel/menu2.jpg')) : asset('images/carousel/menu2.jpg') }}"
                alt="{{ $menus[1]->name ?? 'Special Menus' }}"
                class="w-full h-64 sm:h-72 md:h-80 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 font-extrabold uppercase tracking-wide" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[1]->name ?? 'SPECIAL MENUS' }}
              </h3>
              <p class="mb-2" style="color:#97655A; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[1]->name ?? 'SPECIAL MENUS' }} ARE AVAILABLE FOR GROUPS AND FAMILIES.
              </p>

              <button
                data-title="{{ $menus[1]->name ?? 'SPECIAL MENUS' }}"
                data-details='["Harvest Your Own – Pick and cook farm-fresh ingredients","Cochinillo – Whole roast pig (pre-order required)"]'
                data-images='@json($menu2Images)'
                onclick="openModal(this)"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4"
                style="background:#97655A;color:#ffffff;">
                LEARN MORE
              </button>
            </div>
          </div>

          {{-- Card 3 --}}
          @php
              $menu3Images = isset($menus[2]) && $menus[2]->swiperImages->count() > 0
                  ? $menus[2]->swiperImages->pluck('image_path')->toArray()
                  : [asset('images/carousel/menu3.jpg'), asset('images/carousel/menu1.jpg'), asset('images/carousel/menu4.jpg')];
          @endphp
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ isset($menus[2]) ? ($menus[2]->swiperImages->first()->image_path ?? asset('images/carousel/menu3.jpg')) : asset('images/carousel/menu3.jpg') }}"
                alt="{{ $menus[2]->name ?? 'Snack Bar' }}"
                class="w-full h-64 sm:h-72 md:h-80 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 font-extrabold uppercase tracking-wide" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[2]->name ?? 'SNACK BAR' }}
              </h3>
              <p class="mb-2" style="color:#97655A; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[2]->name ?? 'SNACK BAR' }} IS AVAILABLE FOR GROUPS AND FAMILIES.
              </p>

              <button
                data-title="{{ $menus[2]->name ?? 'SNACK BAR' }}"
                data-details='["All-day light snacks and drinks","Includes smoothies, juice, sandwiches, and more"]'
                data-images='@json($menu3Images)'
                onclick="openModal(this)"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4"
                style="background:#97655A;color:#ffffff;">
                LEARN MORE
              </button>
            </div>
          </div>

        </div>
      </section>

      {{-- Dynamic Details Modal --}}
      <x-package-modal id="detailsModal" />

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

    function openModal(button) {
      const title = button.dataset.title;
      const details = JSON.parse(button.dataset.details);
      const images = JSON.parse(button.dataset.images);
      openModaldetailsModal(title, details, images);
    }
    </script>
  </body>
</html>

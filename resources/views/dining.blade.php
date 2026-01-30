<!doctype html>
<html lang="en">
  <head>
    @include('layouts.partials.favicon')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- Basic SEO --}}
    <meta name="description" content="Calaia Farm — Food & Beverage: Set menus, special menus, and snack bar options for groups and families." />
    <meta name="keywords" content="Calaia Farm, CalaiaPH, food, beverage, set menus, special menus, snack bar, farm to table" />
    <meta name="author" content="CalaiaPH" />

    <title>CalaiaPH - Food & Beverage</title>

    {{-- Tailwind via Vite --}}
    @vite(['resources/css/app.css'])

    {{-- Custom Fonts --}}
    <style>
      body {
        font-family: 'Gotham Book', sans-serif;
      }

      /* Custom Swiper pagination bullets */
      .swiper-pagination-bullet {
        background-color: #a75e3e !important;
      }
      .swiper-pagination-bullet-active {
        background-color: #a75e3e !important;
      }
    </style>

    {{-- Icons / Swiper --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  </head>

  <body class="bg-linear-to-b from-[#f5efe7] to-[#ede3d8]">
  {{-- flash message --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <x-flash-message type="success" :message="session('success')" />
      @endif
    </div>
    <x-header active="dining" />

      {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

      {{-- Mess Hall Image Section --}}
      <section class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <img src="{{ asset('images/mess-hall.jpg') }}" alt="Mess Hall" class="w-full h-96 object-cover">
            <div class="p-6 text-center">
              <h2 class="text-3xl font-bold text-[#97655A] mb-4" style="font-family: 'Gotham Book', sans-serif;">Mess Hall</h2>
              <p class="text-gray-700" style="font-family: 'Gotham Book', sans-serif;">Experience our spacious and elegant mess hall, perfect for group dining and special occasions.</p>
            </div>

            {{-- CARDS SECTION (updated to match mock) --}}
            <div class="p-6">
              {{-- Swiper Carousel --}}
              <div class="swiper messHallSwiper relative">
                <div class="swiper-wrapper">

          @foreach($messHallFoods as $index => $food)
          @php
              $foodImages = $food->swiperImages->pluck('image_path')->toArray();
          @endphp
          <div class="swiper-slide">
            <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2 mx-2 relative">
              {{-- Stock Availability Badge --}}
              @if($food->is_inventory_managed)
                @if($food->stock_quantity <= 0)
                  <div class="absolute top-4 right-4 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                    Out of Stock
                  </div>
                @elseif($food->stock_quantity <= 5)
                  <div class="absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                    Only {{ $food->stock_quantity }} left
                  </div>
                @else
                  <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                    In Stock
                  </div>
                @endif
              @endif
              {{-- inset image frame --}}
              <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
                @if($food->swiperImages->first() || $food->image)
                  <img
                    src="{{ $food->swiperImages->first() ? $food->swiperImages->first()->image_path : (str_starts_with($food->image ?? '', 'http') ? $food->image : asset('storage/' . $food->image)) }}"
                    alt="{{ $food->name }}"
                    class="w-full aspect-4/3 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                    loading="lazy"
                  >
                @else
                  <div class="w-full aspect-4/3 bg-gray-100 flex items-center justify-center">
                    <span class="text-gray-500">No Image</span>
                  </div>
                @endif
              </div>

              <div class="px-6 pb-6 pt-1 text-center">
                <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A]" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                  {{ $food->name }}
                </h3>
                <p class="mb-2 text-[#97655A] text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  {!! nl2br($food->description ?? '') !!}
                </p>
                <p class="text-sm font-bold text-[#97655A] mb-3">PHP {{ $food->price ?? 'Contact for pricing' }}</p>

                <button
                  data-title="{{ $food->name }}"
                  data-price="PHP {{ $food->price ?? 'Contact for pricing' }}"
                  data-details='@json($food->texts->where("section", "details")->pluck("content")->toArray())'
                  data-images='@json($foodImages)'
                  data-description="{{ $food->description ?? 'Delicious food option' }}"
                  onclick="openPackageModal(this)"
                  aria-label="Learn more about {{ $food->name }}"
                  class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4 bg-brand text-white hover:bg-brand transition-colors focus:ring-2 focus:ring-brand focus:outline-none {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                  {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'disabled' : '' }}>
                  LEARN MORE
                </button>
              </div>
            </div>
          </div>
          @endforeach

                </div>
                <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow" data-custom-prev>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
                <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow" data-custom-next>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Cook Your Own Section --}}
      <section class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto">
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <img src="{{ asset('images/cook-your-own.jpg') }}" alt="Cook Your Own" class="w-full h-96 object-cover">
            <div class="p-6">
              <h2 class="text-3xl font-bold text-[#97655A] mb-6 text-center" style="font-family: 'Gotham Book', sans-serif;">Cook Your Own</h2>
              <p class="text-gray-700 mb-6 text-center" style="font-family: 'Gotham Book', sans-serif;">Experience the joy of cooking your own fresh ingredients from our farm. Choose from our available options:</p>

              {{-- Swiper Carousel --}}
              <div class="swiper cookYourOwnSwiper relative">
                <div class="swiper-wrapper">
                  @foreach($cookYourOwnFoods as $index => $food)
                  @php
                      $foodImages = $food->swiperImages->pluck('image_path')->toArray();
                  @endphp
                  <div class="swiper-slide">
                    <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2 mx-2 relative">
                      {{-- Stock Availability Badge --}}
                      @if($food->is_inventory_managed)
                        @if($food->stock_quantity <= 0)
                          <div class="absolute top-4 right-4 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                            Out of Stock
                          </div>
                        @elseif($food->stock_quantity <= 5)
                          <div class="absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                            Only {{ $food->stock_quantity }} left
                          </div>
                        @else
                          <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                            In Stock
                          </div>
                        @endif
                      @endif
                      {{-- inset image frame --}}
                      <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10 mb-4">
                        @if($food->swiperImages->first() || $food->image)
                          <img
                            src="{{ $food->swiperImages->first() ? $food->swiperImages->first()->image_path : (str_starts_with($food->image ?? '', 'http') ? $food->image : asset('storage/' . $food->image)) }}"
                            alt="{{ $food->name }}"
                            class="w-full aspect-4/3 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                            loading="lazy"
                          >
                        @else
                          <div class="w-full aspect-4/3 bg-gray-100 flex items-center justify-center">
                            <span class="text-gray-500">No Image</span>
                          </div>
                        @endif
                      </div>

                      <div class="px-6 pb-6 pt-1 text-center">
                        <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A]" style="font-size: 20px; font-family: 'Gotham Book', sans-serif;">
                          {{ $food->name }}
                        </h3>
                        <p class="mb-2 text-[#97655A] text-xs" style="font-family: 'Gotham Book', sans-serif;">
                          {!! nl2br($food->description ?? 'Delicious option') !!}
                        </p>
                        <p class="text-sm font-bold text-[#97655A] mb-3">PHP {{ $food->price ?? 'Contact for pricing' }}</p>

                        <button
                          data-title="{{ $food->name }}"
                          data-price="PHP {{ $food->price ?? 'Contact for pricing' }}"
                          data-details='@json($food->texts->where("section", "details")->pluck("content")->toArray())'
                          data-images='@json($foodImages)'
                          data-description="{{ $food->description ?? 'Delicious food option' }}"
                          onclick="openPackageModal(this)"
                          aria-label="Learn more about {{ $food->name }}"
                          class="inline-flex items-center justify-center rounded-full px-8 py-2 text-sm font-semibold uppercase tracking-wide shadow-md mt-2 bg-brand text-white hover:bg-brand transition-colors focus:ring-2 focus:ring-brand focus:outline-none {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                          {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'disabled' : '' }}>
                          LEARN MORE
                        </button>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow" data-custom-prev>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
                <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow" data-custom-next>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Dynamic Details Modal --}}
      <x-package-modal id="detailsModal" :price="false" :buttonText="'Book Now'" :buttonAction="'bookNow'" :bookingRoute="route('booking.calendar')" :appendParams="false">
      </x-package-modal>

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

      // Initialize cook your own swiper
      const cookYourOwnSwiper = new Swiper('.cookYourOwnSwiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        navigation: {
          nextEl: '.cookYourOwnSwiper [data-custom-next]',
          prevEl: '.cookYourOwnSwiper [data-custom-prev]'
        },
        pagination: {
          el: '.cookYourOwnSwiper [data-custom-dots]',
          clickable: true
        },
        breakpoints: {
          640: { slidesPerView: 1 },
          768: { slidesPerView: 2 },
          1024: { slidesPerView: 3 }
        }
      });

      // Initialize mess hall swiper
      const messHallSwiper = new Swiper('.messHallSwiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        navigation: {
          nextEl: '.messHallSwiper [data-custom-next]',
          prevEl: '.messHallSwiper [data-custom-prev]'
        },
        pagination: {
          el: '.messHallSwiper [data-custom-dots]',
          clickable: true
        },
        breakpoints: {
          640: { slidesPerView: 1 },
          768: { slidesPerView: 2 },
          1024: { slidesPerView: 3 }
        }
      });
    });

    </script>
  </body>
</html>

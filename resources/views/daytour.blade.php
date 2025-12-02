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
{{-- flash message --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <x-flash-message type="success" :message="session('success')" />
      @endif
    </div>
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
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase"
                      style="font-family: gotham;">
                    {{ ucfirst($labels_array[$index]) }}
                  </h2>
                  <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase"
                    style="font-family: 'Vilaka Modern Serif', serif; font-style: italic;">
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
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                No Image Available
              </h2>
              <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase" style="font-size: 13px; font-family: 'Gotham Book', sans-serif; font-style: italic;">
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

      <div class="space-y-16">
        {{-- Packages Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
          @foreach($packages as $package)
            <div class="bg-white rounded-[28px] shadow-lg border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group">
              {{-- Inset framed image --}}
              <div class="px-5 pt-5">
                <div class="relative overflow-hidden ring-1 ring-black/5 shadow-sm bg-white">
                  <img
                    src="{{ asset(is_array($package) ? $package['image'] : ($package->swiperImages->first()->image_path ?? 'images/default.jpg')) }}"
                    class="h-64 md:h-72 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                    alt="{{ is_array($package) ? $package['title'] : $package->name }} package"
                    loading="lazy"
                  >
                </div>
              </div>

              {{-- Body --}}
              <div class="px-8 pt-6 pb-8">
                {{-- Title --}}
                 <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide text-center" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                   @php
                     $title = is_array($package) ? $package['title'] : $package->name;
                   @endphp
                   @if(strtolower($title) === 'nature experience')
                     NATURE<br>EXPERIENCE
                   @else
                     {{ strtoupper($title) }}
                   @endif
                 </h3>


                {{-- Divider (subtle sage like the mock) --}}
                <div class="h-px w-full bg-[#dfe6d5] mb-5"></div>

                {{-- Features (compact, centered list) --}}
                <ul class="space-y-2 text-base md:text-lg uppercase tracking-widest text-center" style="color:#8f6a60; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                  @foreach(is_array($package) ? $package['features'] : $package->activities->pluck('name') as $feature)
                    <li>
                      {{ strtoupper($feature) }}
                    </li>
                  @endforeach
                </ul>

                {{-- CTA --}}
                <div class="mt-7 flex justify-center">
                  <button
                    type="button"
                    class="px-6 py-2.5 rounded-full text-white text-xs md:text-sm font-semibold tracking-wider shadow
                           transition-all duration-300 hover:-translate-y-0.5"
                    style="background-color:#8f5d54;"
                    onmouseover="this.style.backgroundColor='#a0675a'"
                    onmouseout="this.style.backgroundColor='#8f5d54'"
                    data-title="{{ $title }}"
                    data-price="{{ is_array($package) ? 'PHP 1,500 per person' : $package->price }}"
                    data-details='@json(is_array($package) ? $package["details"] : explode("\n", $package->description ?? ""))'
                    data-gallery='@json(is_array($package) ? array_map(fn($img) => asset($img), $package["gallery"]) : $package->swiperImages->pluck("image_path")->map(fn($path) => asset($path))->toArray())'
                  >
                    LEARN MORE
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Information Section (kept; slight polish to match palette) --}}
        <div class="bg-linear-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl">
          <div class="text-center mb-8">
            <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
              <i class="bi bi-info-circle-fill mr-3 text-3xl"></i>
              Important Information
            </h4>
            <p class="text-lg/7 opacity-90" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Everything you need to know before your visit</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach([
              ['icon' => 'bi-clock',           'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
              ['icon' => 'bi-calendar-check',  'text' => 'Advance booking recommended for guaranteed slots'],
              ['icon' => 'bi-person',          'text' => 'Children under 3 years old enter for free'],
              ['icon' => 'bi-people',          'text' => 'Group discounts available for 10+ persons'],
              ['icon' => 'bi-person',        'text' => 'Wear comfy clothing and footwear for outdoor activities'],
              ['icon' => 'bi-cup-straw',       'text' => 'Food and beverages available for purchase on site'],
            ] as $info)
              <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
                <i class="bi {{ $info['icon'] }} text-xl mr-4 mt-1 shrink-0"></i>
                <span class="text-sm md:text-base leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">{{ $info['text'] }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </main>

  {{-- Dynamic Details Modal --}}
  <x-package-modal id="detailsModal" :price="true" buttonText="Book Now" buttonAction="bookNow" />

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

  let lastFocusedElement = null;

  function initializeModal() {
    document.querySelectorAll('button[data-title]').forEach(button => {
      button.addEventListener('click', function() {
        lastFocusedElement = this;
        const title = this.getAttribute('data-title');
        const details = JSON.parse(this.getAttribute('data-details'));
        const gallery = JSON.parse(this.getAttribute('data-gallery'));
        const price = this.getAttribute('data-price');
        openModaldetailsModal(title, details, gallery, price);
      });
    });
  }
  </script>
</body>
</html>
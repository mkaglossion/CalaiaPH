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

    {{-- Custom Fonts --}}
    <style>
      body {
        font-family: 'Gotham Book', sans-serif;
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

      {{-- CARDS SECTION (updated to match mock) --}}
      <section id="set-menu" class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">

          {{-- Card 1 --}}
          @php
              $menu1Images = isset($menus[0]) && $menus[0]->swiperImages->count() > 0
                  ? $menus[0]->swiperImages->pluck('image_path')->toArray()
                  : [asset('images/carousel/menu1.jpg'), asset('images/carousel/SET MEALS.jpg'), asset('images/carousel/menu1.jpg')];
          @endphp
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2">
            {{-- inset image frame --}}
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ isset($menus[0]) ? ($menus[0]->swiperImages->first()->image_path ?? asset('images/carousel/menu1.jpg')) : asset('images/carousel/menu1.jpg') }}"
                alt="{{ $menus[0]->name ?? 'Set Menus' }}"
                class="w-full aspect-4/3 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A]" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[0]->name ?? 'SET MENUS' }}
              </h3>
              <p class="mb-2 text-[#97655A] text-xs" style="font-family: 'Gotham Book', sans-serif;">
                {{ $menus[0]->name ?? 'SET MENUS' }} ARE AVAILABLE FOR GROUPS AND FAMILIES.
              </p>

              <button
                data-title="{{ $menus[0]->name ?? 'SET MENUS' }}"
                data-price="PHP 5,980.00 (3–4 Person), 8,790.00 (5–6 Person), 12,900.00 (7–9 Person)"
                data-details='["Ready-made meals for families, couples, or groups","Breakfast, lunch, and dinner options"]'
                data-images='@json($menu1Images)'
                onclick="openModal(this)"
                aria-label="Learn more about {{ $menus[0]->name ?? 'SET MENUS' }}"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4 bg-[#97655A] text-white hover:bg-[#8a5a4a] transition-colors focus:ring-2 focus:ring-[#97655A] focus:outline-none">
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
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2">
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ isset($menus[1]) ? ($menus[1]->swiperImages->first()->image_path ?? asset('images/carousel/menu2.jpg')) : asset('images/carousel/menu2.jpg') }}"
                alt="{{ $menus[1]->name ?? 'Special Menus' }}"
                class="w-full aspect-4/3 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A]" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[1]->name ?? 'SPECIAL MENUS' }}
              </h3>
              <p class="mb-2 text-[#97655A] text-xs" style="font-family: 'Gotham Book', sans-serif;">
                {{ $menus[1]->name ?? 'SPECIAL MENUS' }} ARE AVAILABLE FOR GROUPS AND FAMILIES.
              </p>

              <button
                data-title="{{ $menus[1]->name ?? 'SPECIAL MENUS' }}"
                data-price="PHP 3,500 per person"
                data-details='["Harvest Your Own – Pick and cook farm-fresh ingredients","Cochinillo – Whole roast pig (pre-order required)"]'
                data-images='@json($menu2Images)'
                onclick="openModal(this)"
                aria-label="Learn more about {{ $menus[1]->name ?? 'SPECIAL MENUS' }}"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4 bg-[#97655A] text-white hover:bg-[#8a5a4a] transition-colors focus:ring-2 focus:ring-[#97655A] focus:outline-none">
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
          <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2 min-h-[500px]">
            <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
              <img
                src="{{ isset($menus[2]) ? ($menus[2]->swiperImages->first()->image_path ?? asset('images/carousel/menu3.jpg')) : asset('images/carousel/menu3.jpg') }}"
                alt="{{ $menus[2]->name ?? 'Snack Bar' }}"
                class="w-full aspect-4/3 object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                loading="lazy"
              >
            </div>

            <div class="px-6 pb-6 pt-1 text-center">
              <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A]" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                {{ $menus[2]->name ?? 'SNACK BAR' }}
              </h3>
              <p class="mb-2 text-[#97655A] text-xs" style="font-family: 'Gotham Book', sans-serif;">
                {{ $menus[2]->name ?? 'SNACK BAR' }} IS AVAILABLE FOR GROUPS AND FAMILIES.
              </p>

              <button
                data-title="{{ $menus[2]->name ?? 'SNACK BAR' }}"
                data-price="PHP 500 per person"
                data-details='["All-day light snacks and drinks","Includes smoothies, juice, sandwiches, and more"]'
                data-images='@json($menu3Images)'
                onclick="openModal(this)"
                aria-label="Learn more about {{ $menus[2]->name ?? 'SNACK BAR' }}"
                class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-4 bg-[#97655A] text-white hover:bg-[#8a5a4a] transition-colors focus:ring-2 focus:ring-[#97655A] focus:outline-none">
                LEARN MORE
              </button>
            </div>
          </div>

        </div>
      </section>

      {{-- Section Divider --}}
      <div class="container mx-auto px-4">
        <hr class="border-t border-gray-300 my-8">
      </div>

      {{-- Menu Details Section --}}
      <section id="menu-details" class="container mx-auto px-4 pb-16" x-data="{ activeTab: 'all' }">
        <div class="max-w-6xl mx-auto">
          <h2 class="text-3xl font-bold text-center mb-8 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Menu Details</h2>

          {{-- Filter Buttons --}}
          <div class="flex justify-center mb-8 flex-wrap gap-2">
            <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-[#97655A] text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-3 rounded-lg font-semibold transition-colors" style="font-family: 'Gotham Book', sans-serif;">All</button>
            <button @click="activeTab = 'lunch'" :class="activeTab === 'lunch' ? 'bg-[#97655A] text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-3 rounded-lg font-semibold transition-colors" style="font-family: 'Gotham Book', sans-serif;">Lunch</button>
            <button @click="activeTab = 'merienda'" :class="activeTab === 'merienda' ? 'bg-[#97655A] text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-3 rounded-lg font-semibold transition-colors" style="font-family: 'Gotham Book', sans-serif;">Merienda</button>
            <button @click="activeTab = 'dinner'" :class="activeTab === 'dinner' ? 'bg-[#97655A] text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-3 rounded-lg font-semibold transition-colors" style="font-family: 'Gotham Book', sans-serif;">Dinner</button>
            <button @click="activeTab = 'breakfast'" :class="activeTab === 'breakfast' ? 'bg-[#97655A] text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-3 rounded-lg font-semibold transition-colors" style="font-family: 'Gotham Book', sans-serif;">Breakfast</button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Lunch --}}
            <div class="bg-white rounded-2xl shadow-lg p-6" x-show="activeTab === 'lunch' || activeTab === 'all'" x-transition>
              <h3 class="text-2xl font-bold mb-4 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Lunch</h3>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 1</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Grilled Chicken</li>
                  <li>Grilled Pork</li>
                  <li>Grilled Vegetables</li>
                  <li>Steamed Rice</li>
                  <li>Calacatchara</li>
                  <li>Salted Egg</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2" style="font-family: 'Gotham Book', sans-serif;">Set Menu 2</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Coconut Milk Chicken Curry</li>
                  <li>Grilled Tuna Panga</li>
                  <li>Caesar Salad</li>
                  <li>Steamed Rice</li>
                  <li>Calacatchara</li>
                  <li>Salted Egg</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2" style="font-family: 'Gotham Book', sans-serif;">Set Menu 3</h4>
                <ul class="list-disc list-inside text-sm space-y-1" style="font-family: 'Gotham Book', sans-serif;">
                  <li>Beef Sausages on Buns</li>
                  <li>Condiments: Ketchup, Mustard, Mayo, Pickle Relish</li>
                  <li>Choice of Spanish Sardine Pasta or Creamy Tomato Pasta</li>
                </ul>
              </div>
            </div>

            {{-- Merienda --}}
            <div class="bg-white rounded-2xl shadow-lg p-6" x-show="activeTab === 'merienda' || activeTab === 'all'" x-transition>
              <h3 class="text-2xl font-bold mb-4 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Merienda</h3>

              <div class="mb-6">
                <h4 class="font-semibold mb-2" style="font-family: 'Gotham Book', sans-serif;">Set Menu 1</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Local Bakery Breads</li>
                  <li>Seasonal Fruit</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 2</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Ginataan Bilo Bilo</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 3</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Classic Tomato Basil Bruschetta</li>
                </ul>
              </div>
            </div>

            {{-- Dinner --}}
            <div class="bg-white rounded-2xl shadow-lg p-6" x-show="activeTab === 'dinner' || activeTab === 'all'" x-transition>
              <h3 class="text-2xl font-bold mb-4 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Dinner</h3>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 1</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Pork BBQ</li>
                  <li>Sinigang na Salmon</li>
                  <li>Steamed Rice</li>
                  <li>Puto (rice cake)</li>
                  <li>Calacatchara</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 2</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Pork Adobo</li>
                  <li>Grilled Shrimp Skewers</li>
                  <li>Ginataan Kalabasa (Squash)</li>
                  <li>Steamed Rice</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 3</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Bistek Tagalog</li>
                  <li>Grilled Tuna Panga</li>
                  <li>Caesar Salad</li>
                  <li>Steamed Rice</li>
                  <li>Calacatchara</li>
                  <li>Salted Egg</li>
                </ul>
              </div>
            </div>

            {{-- Breakfast --}}
            <div class="bg-white rounded-2xl shadow-lg p-6" x-show="activeTab === 'breakfast' || activeTab === 'all'" x-transition>
              <h3 class="text-2xl font-bold mb-4 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Breakfast</h3>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 1</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Pork Tocino</li>
                  <li>Dried Local Fish</li>
                  <li>Scrambled Eggs</li>
                  <li>Pandesal or local bread</li>
                  <li>Garlic Rice</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 2</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Beef Tapa</li>
                  <li>Spanish-style Sardines</li>
                  <li>Scrambled Eggs</li>
                  <li>Pandesal or local bread</li>
                  <li>Garlic Rice</li>
                </ul>
              </div>

              <div class="mb-6">
                <h4 class="font-semibold mb-2">Set Menu 3</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Corned Beef</li>
                  <li>Dried Local fish</li>
                  <li>Scrambled Eggs</li>
                  <li>Pandesal or local bread</li>
                  <li>Garlic Rice</li>
                </ul>
              </div>
            </div>
          </div>

          {{-- Pricing --}}
          <div class="mt-12 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl font-bold mb-4 text-center text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Price / Set Menu Price</h3>
            <ul class="list-disc list-inside text-lg space-y-2 text-center" style="font-family: 'Gotham Book', sans-serif;">
              <li>3–4 Person – 5,980.00</li>
              <li>5–6 Person – 8,790.00</li>
              <li>7–9 Person – 12,900.00</li>
            </ul>
          </div>

          {{-- Inclusions --}}
          <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Inclusions</h3>
            <p class="text-sm" style="font-family: 'Gotham Book', sans-serif;">Disposable cutlery (non-disposable upon request) Drinking water and ice</p>
          </div>

          {{-- Beverages Included --}}
          <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">Beverages Included</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>Filtered Drinking Water</li>
              <li>Lunch & Merienda: Juice with Ice</li>
              <li>Breakfast: Brewed Coffee, Hot Lemongrass Tea, Hot Chocolate, Fresh Milk</li>
              <li>Dinner: Juice with Ice</li>
            </ul>
          </div>

          {{-- NEW KUROBOTA Lechon --}}
          <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-center text-[#97655A]" style="font-family: 'Gotham Book', sans-serif;">NEW KUROBOTA Lechon</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="font-semibold mb-2" style="font-family: 'Gotham Book', sans-serif;">Kurobota x Native</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Live price/weight P850.00/kg</li>
                  <li>Dressed price/weight P1100.00/kg</li>
                </ul>
              </div>

              <div>
                <h4 class="font-semibold mb-2" style="font-family: 'Gotham Book', sans-serif;">Kurobota Pure</h4>
                <ul class="list-disc list-inside text-sm space-y-1">
                  <li>Live price/weight P1200.00/kg</li>
                  <li>Dressed price/weight P1500.00/kg</li>
                </ul>
              </div>
            </div>

            <p class="text-sm mt-4" style="font-family: 'Gotham Book', sans-serif;"><strong>Onsite Cooking fee:</strong> 10% per kg</p>
          </div>
        </div>
      </section>

      {{-- Dynamic Details Modal --}}
      <x-package-modal id="detailsModal" :price="true" />

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

    </script>
  </body>
</html>

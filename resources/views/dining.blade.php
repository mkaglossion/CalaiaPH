<x-page-layout 
    title="CalaiaPH - Food & Beverage"
    description="Calaia Farm — Food & Beverage: Set menus, special menus, and snack bar options for groups and families."
    keywords="Calaia Farm, CalaiaPH, food, beverage, set menus, special menus, snack bar, farm to table"
    activeHeader="dining"
    bodyClass="bg-linear-to-b from-[#f5efe7] to-[#ede3d8]"
>
    @push('styles')
        <style>
          /* Custom Swiper pagination bullets */
          .swiper-pagination-bullet {
            background-color: #a75e3e !important;
          }
          .swiper-pagination-bullet-active {
            background-color: #a75e3e !important;
          }
        </style>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush

      {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

      {{-- Mess Hall Image Section --}}
      <section class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            @if(isset($diningCategoryImages['messhall']))
              <img src="{{ $diningCategoryImages['messhall']->image_path }}" alt="Mess Hall" class="w-full h-96 object-cover">
            @else
              <img src="{{ asset('images/mess-hall.jpg') }}" alt="Mess Hall" class="w-full h-96 object-cover">
            @endif
            <div class="p-6 text-center">
              <h2 class="text-3xl font-bold text-[#97655A] mb-4">Mess Hall</h2>
              <p class="text-gray-700">Experience our spacious and elegant mess hall, perfect for group dining and special occasions.</p>
            </div>

            {{-- CARDS SECTION (updated to match mock) --}}
            <div class="p-6" x-data="{ showAll: false }">
              {{-- 4-Column Grid Layout --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="messHallGrid">

          @foreach($messHallFoods->sortBy('name') as $index => $food)
          @php
              $foodImages = $food->swiperImages->pluck('image_path')->toArray();
              $categoryColors = [
                  'Buffet' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                  'Pastry' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-800'],
                  'Chinese' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                  'Japanese' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                  'International' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                  'Lounge and Bar' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                  'Grill and Steakhouse' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                  'Seafood' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800'],
              ];
              $cat = $categoryColors[$food->category] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
          @endphp
          <div class="h-full" x-show="showAll || {{ $index }} < 8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2 h-full flex flex-col relative">
              {{-- Category Badge --}}
              <div class="absolute top-4 left-4 {{ $cat['bg'] }} {{ $cat['text'] }} px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                {{ ucfirst(str_replace('_', ' ', $food->category)) }}
              </div>

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
              {{-- inset image frame - show food image from Food Management ONLY --}}
              <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10">
                @if(!empty($food->image))
                  <img
                    src="{{ str_starts_with($food->image, 'http') ? $food->image : asset('storage/' . $food->image) }}"
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

              <div class="px-6 pb-6 pt-1 text-center flex flex-col grow">
                <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A] section-heading">
                  {{ $food->name }}
                </h3>
                <p class="mb-2 text-[#97655A] text-xs grow">
                  {!! nl2br($food->description ?? '') !!}
                </p>
                <p class="text-sm font-bold text-[#97655A] mb-3">PHP {{ $food->price ?? 'Contact for pricing' }}</p>

                <button
                  data-title="{{ $food->name }}"
                  data-food-id="{{ $food->id }}"
                  data-max-stock="{{ $food->stock_quantity ?? 100 }}"
                  data-price="{{ $food->price ?? 0 }}"
                  onclick="window.openDiningReservationModal(this)"
                  aria-label="Book {{ $food->name }}"
                  class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md mt-auto bg-brand text-white hover:bg-brand transition-colors focus:ring-2 focus:ring-brand focus:outline-none {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                  {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'disabled' : '' }}>
                  Book Now
                </button>
              </div>
            </div>
          </div>
          @endforeach

              </div>

              {{-- View All / Show Less Buttons --}}
              <div class="text-center mt-8 flex justify-center gap-4">
                <button 
                  x-show="!showAll && {{ $messHallFoods->count() }} > 8"
                  @click="showAll = true; setTimeout(() => document.getElementById('messHallGrid').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                  class="text-[#97655A] font-semibold underline hover:text-[#7a4d45] transition-colors">
                  View All
                </button>
                <button 
                  x-show="showAll"
                  @click="showAll = false; setTimeout(() => document.getElementById('messHallGrid').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                  class="text-[#97655A] font-semibold underline hover:text-[#7a4d45] transition-colors">
                  Show Less
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Cook Your Own Section --}}
      <section class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto">
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            @if(isset($diningCategoryImages['cook_your_own']))
              <img src="{{ $diningCategoryImages['cook_your_own']->image_path }}" alt="Cook Your Own" class="w-full h-96 object-cover">
            @else
              <img src="{{ asset('images/cook-your-own.jpg') }}" alt="Cook Your Own" class="w-full h-96 object-cover">
            @endif
            <div class="p-6" x-data="{ showAll: false }">
              <h2 class="text-3xl font-bold text-[#97655A] mb-6 text-center">Cook Your Own</h2>
              <p class="text-gray-700 mb-6 text-center">Experience the joy of cooking your own fresh ingredients from our farm. Choose from our available options:</p>

              {{-- 4-Column Grid Layout --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="cookYourOwnGrid">
                @foreach($cookYourOwnFoods->sortBy('name') as $index => $food)
                @php
                    $foodImages = $food->swiperImages->pluck('image_path')->toArray();
                    $categoryColors = [
                        'Buffet' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                        'Pastry' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-800'],
                        'Chinese' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                        'Japanese' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                        'International' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                        'Lounge and Bar' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                        'Grill and Steakhouse' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                        'Seafood' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800'],
                    ];
                    $cat = $categoryColors[$food->category] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                @endphp
                <div class="h-full" x-show="showAll || {{ $index }} < 8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="bg-white rounded-2xl shadow-[0_6px_16px_rgba(0,0,0,0.08)] border border-gray-100 group hover:shadow-2xl hover:shadow-[#97655A]/20 transition-all duration-500 hover:-translate-y-2 h-full flex flex-col relative">
                  {{-- Category Badge --}}
                  <div class="absolute top-4 left-4 {{ $cat['bg'] }} {{ $cat['text'] }} px-3 py-1 rounded-full text-xs font-semibold shadow-lg z-10">
                    {{ ucfirst(str_replace('_', ' ', $food->category)) }}
                  </div>

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
                  {{-- inset image frame - show food image from Food Management ONLY --}}
                  <div class="m-3 rounded-xl overflow-hidden bg-[#97655A]/10 mb-4">
                    @if(!empty($food->image))
                      <img
                        src="{{ str_starts_with($food->image, 'http') ? $food->image : asset('storage/' . $food->image) }}"
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

                  <div class="px-6 pb-6 pt-1 text-center flex flex-col grow">
                    <h3 class="my-4 font-extrabold uppercase tracking-wide text-[#97655A] dining-subheading">
                      {{ $food->name }}
                    </h3>
                    <p class="mb-2 text-[#97655A] text-xs grow">
                      {!! nl2br($food->description ?? 'Delicious option') !!}
                    </p>
                    <p class="text-sm font-bold text-[#97655A] mb-3">PHP {{ $food->price ?? 'Contact for pricing' }}</p>

                    <button
                      data-title="{{ $food->name }}"
                      data-food-id="{{ $food->id }}"
                      data-max-stock="{{ $food->stock_quantity ?? 100 }}"
                      data-price="{{ $food->price ?? 0 }}"
                      onclick="window.openDiningReservationModal(this)"
                      aria-label="Book {{ $food->name }}"
                      class="inline-flex items-center justify-center rounded-full px-8 py-2 text-sm font-semibold uppercase tracking-wide shadow-md mt-auto bg-brand text-white hover:bg-brand transition-colors focus:ring-2 focus:ring-brand focus:outline-none {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                      {{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'disabled' : '' }}>
                      Book Now
                    </button>
                  </div>
                </div>
                </div>
                @endforeach
              </div>

              {{-- View All / Show Less Buttons --}}
              <div class="text-center mt-8 flex justify-center gap-4">
                <button 
                  x-show="!showAll && {{ $cookYourOwnFoods->count() }} > 8"
                  @click="showAll = true; setTimeout(() => document.getElementById('cookYourOwnGrid').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                  class="text-[#97655A] font-semibold underline hover:text-[#7a4d45] transition-colors">
                  View All
                </button>
                <button 
                  x-show="showAll"
                  @click="showAll = false; setTimeout(() => document.getElementById('cookYourOwnGrid').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                  class="text-[#97655A] font-semibold underline hover:text-[#7a4d45] transition-colors">
                  Show Less
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Dynamic Details Modal --}}
      <x-package-modal id="detailsModal" :price="false" :buttonText="'Book Now'" :buttonAction="'bookNow'" :bookingRoute="route('booking.calendar') . '?type=staycation'" :appendParams="true">
      </x-package-modal>

      {{-- Dining Reservation Modal --}}
      <x-dining-reservation-modal />

    </div>

    {{-- Floating Buttons --}}
    <x-floating-buttons />

    @push('scripts')
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
    @endpush
</x-page-layout>

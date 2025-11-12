{{-- Day Tour Cards Component (matches mock) --}}
@php
  // Title → price text to mirror your getPackagePrice() map
  $priceMap = [
    'Adventure Package'  => 'PHP 1,500 per person',
    'Family Fun Package' => 'PHP 2,000 per family',
    'Nature Experience'  => 'PHP 1,200 per person',
  ];
@endphp

<div class="space-y-16">
  {{-- Packages Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
    @foreach($packages as $package)
      <div class="bg-white rounded-[28px] shadow-lg border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group">
        {{-- Inset framed image --}}
        <div class="px-5 pt-5">
          <div class="relative overflow-hidden ring-1 ring-black/5 shadow-sm bg-white">
            <img
              src="{{ asset($package['image']) }}"
              class="h-64 md:h-72 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
              alt="{{ $package['title'] }} package"
              loading="lazy"
            >
          </div>
        </div>

        {{-- Body --}}
        <div class="px-8 pt-6 pb-8">
          {{-- Title --}}
           <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide text-center" style="color:#97655A;">
             @if($package['title'] === 'Nature Experience')
               NATURE<br>EXPERIENCE
             @else
               {{ strtoupper($package['title']) }}
             @endif
           </h3>


          {{-- Divider (subtle sage like the mock) --}}
          <div class="h-px w-full bg-[#dfe6d5] mb-5"></div>

          {{-- Features (compact, centered list) --}}
          <ul class="space-y-3 mb-7 text-center">
            @foreach($package['features'] as $feature)
              <li class="text-xs md:text-sm tracking-wide"
                  style="color:#8f5d54; font-family:gotham;">
                {{ strtoupper($feature) }}
              </li>
            @endforeach
          </ul>

          {{-- CTA --}}
          <div class="flex justify-center">
            <button
              type="button"
              class="px-6 py-2.5 rounded-full text-white text-xs md:text-sm font-semibold tracking-wider shadow
                     transition-all duration-300 hover:-translate-y-0.5"
              style="background-color:#8f5d54;"
              onmouseover="this.style.backgroundColor='#a0675a'"
              onmouseout="this.style.backgroundColor='#8f5d54'"
              data-title="{{ $package['title'] }}"
              data-details='@json($package["details"])'
              data-gallery='@json(array_map(fn($img) => asset($img), $package["gallery"]))'
            >
              LEARN MORE
            </button>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Information Section (kept; slight polish to match palette) --}}
  <div class="bg-gradient-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl">
    <div class="text-center mb-8">
      <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center"
          style="font-family:gotham;">
        <i class="bi bi-info-circle-fill mr-3 text-3xl"></i>
        Important Information
      </h4>
      <p class="text-lg/7 opacity-90">Everything you need to know before your visit</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      @foreach([
        ['icon' => 'bi-clock',           'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
        ['icon' => 'bi-calendar-check',  'text' => 'Advance booking recommended for guaranteed slots'],
        ['icon' => 'bi-person',          'text' => 'Children under 3 years old enter for free'],
        ['icon' => 'bi-people',          'text' => 'Group discounts available for 10+ persons'],
        ['icon' => 'bi-backpack',        'text' => 'Wear comfy clothing and footwear for outdoor activities'],
        ['icon' => 'bi-cup-straw',       'text' => 'Food and beverages available for purchase on site'],
      ] as $info)
        <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
          <i class="bi {{ $info['icon'] }} text-xl mr-4 mt-1 flex-shrink-0"></i>
          <span class="text-sm md:text-base leading-relaxed">{{ $info['text'] }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>

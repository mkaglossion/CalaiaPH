<x-page-layout activeHeader="staycation">



    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- Main --}}
    <main id="main" class="" x-data="staycationPage()" x-on:open-staycation-modal.window="openModal($event.detail)">
      {{-- Cards patterned to provided design --}}
      <section id="glamping" class="container mx-auto max-w-6xl px-4 py-10">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

          {{-- Card: Glamping Tent --}}
          <article class="relative mx-auto w-full max-w-md rounded-[22px] bg-white p-5 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2" style="box-shadow:0 10px 16px rgba(0,0,0,0.08);">
            <div class="rounded-xl bg-white overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              @php
                $glampingStaycation = $staycations->firstWhere('name', 'Standard Glamping');
                $glampingImage = null;
                if ($glampingStaycation) {
                  // Prefer explicit image field set from admin panel
                  if (!empty($glampingStaycation->image)) {
                    $glampingImage = $glampingStaycation->image;
                  } elseif ($glampingStaycation->swiperImages && $glampingStaycation->swiperImages->count() > 0) {
                    $firstImg = $glampingStaycation->swiperImages->first();
                    $glampingImage = $firstImg->image_url ?? $firstImg->image_path ?? null;
                  }
                }
                // Normalize to absolute URL in blade (if stored as relative)
                if ($glampingImage) {
                  if (strpos($glampingImage, '//') === 0) $glampingImage = 'https:' . $glampingImage;
                  elseif (!preg_match('#^https?://#', $glampingImage)) $glampingImage = url($glampingImage);
                }
              @endphp
              @if($glampingImage)
                <img src="{{ $glampingImage }}" alt="Glamping Tent" data-initial-src="{{ $glampingImage }}" class="h-56 sm:h-64 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" onerror="this.onerror=null; this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
              @else
                <div class="h-56 sm:h-64 w-full bg-gray-100 flex items-center justify-center text-gray-400">No image uploaded</div>
              @endif
            </div>
            <div class="px-4 py-7 text-center">
              <h3 class="mb-4 font-extrabold uppercase tracking-wide staycation-heading">Glamping Tent</h3>
              <div class="mx-auto my-4 h-px w-full" style="background:#dce8d6;"></div>
              <ul class="space-y-2 text-xs uppercase tracking-widest" style="color:#8f6a60;">
                <li>LUXURY TENT SETUP</li>
                <li>GOURMET LUNCH SERVICE</li>
                <li>PRIVATE ATV EXPERIENCE</li>
                <li>GUIDED NATURE TOUR</li>
                <li>CUSTOM TEAM BUILDING</li>
              </ul>
              <div class="mt-7">
                <button type="button" @click="openModal('glamping')" class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md" style="background:#a75e3e;color:#ffffff;" onmouseover="this.style.backgroundColor='#a75e3e'" onmouseout="this.style.backgroundColor='#a75e3e'">LEARN MORE</button>
              </div>
            </div>
          </article>

          {{-- Card: Camping --}}
          <article class="relative mx-auto w-full max-w-md rounded-[22px] bg-white p-5 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2" style="box-shadow:0 10px 16px rgba(0,0,0,0.08);">
            <div class="rounded-xl ring-1 ring-black/5 shadow-sm bg-white overflow-hidden">
              @php
                // Prefer explicit image field from admin for Camping (Car Camping / BYOT), then swiperImages
                $campingStaycation = $staycations->firstWhere('name', 'Car Camping')
                  ?? $staycations->first(function($s) {
                    return str_contains(strtolower($s->name), 'byot') || str_contains(strtolower($s->name), 'camping');
                  });
                $campingImage = null;
                if ($campingStaycation) {
                  if (!empty($campingStaycation->image)) {
                    $campingImage = $campingStaycation->image;
                  } elseif ($campingStaycation->swiperImages && $campingStaycation->swiperImages->count() > 0) {
                    $first = $campingStaycation->swiperImages->first();
                    $campingImage = $first->image_url ?? $first->image_path ?? null;
                  }
                }
                // Normalize absolute URL
                if ($campingImage) {
                  if (strpos($campingImage, '//') === 0) $campingImage = 'https:' . $campingImage;
                  elseif (!preg_match('#^https?://#', $campingImage)) $campingImage = url($campingImage);
                }
              @endphp
              @if($campingImage)
                <img src="{{ $campingImage }}" data-initial-src="{{ $campingImage }}" alt="Camping" class="h-56 sm:h-64 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" onerror="this.onerror=null; this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
              @else
                <div class="h-56 sm:h-64 w-full bg-gray-100 flex items-center justify-center text-gray-400">No image uploaded</div>
              @endif
            </div>
            <div class="px-4 py-7 text-center">
              <h3 class="mb-4 font-extrabold uppercase tracking-wide staycation-heading">Camping</h3>
              <div class="mx-auto my-4 h-px w-full" style="background:#dce8d6;"></div>
              <ul class="space-y-2 text-xs uppercase tracking-widest" style="color:#8f6a60;">
                <li>TENT SETUP ASSISTANCE</li>
                <li>CAMPFIRE BUILDING</li>
                <li>STARGAZING SESSION</li>
                <li>BASIC NATURE WALK</li>
                <li>FARM EXPLORATION</li>
              </ul>
              <div class="mt-7">
                <button type="button" @click="openModal('camping')" class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md" style="background:#a75e3e;color:#ffffff;" onmouseover="this.style.backgroundColor='#a75e3e'" onmouseout="this.style.backgroundColor='#a75e3e'">LEARN MORE</button>
              </div>
            </div>
          </article>

        </div>
      </section>

      {{-- Glamping Options Modal --}}
      <div x-show="glampingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeGlampingModal()" @click.self="closeGlampingModal()" style="display:none;">
        <div class="w-full max-w-7xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A;"><i class="bi bi-stars me-2"></i>Glamping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeGlampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#6b7280;color:white"><h4 class="text-lg md:text-xl font-semibold flex items-center gap-2"><i class="bi bi-house-fill"></i> Standard Glamping</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'Standard Glamping'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'Standard Glamping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
                  <x-slider-arrows />
                  <div class="swiper-pagination"></div>
                  <div class="swiper-pagination"></div>
                  <!-- Swiper dots always present for Standard Glamping -->
                </div>
                <div class="flex items-center justify-between">
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starts at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">Standard Glamping</span>
                </div>
                @php
                    $staycation = $staycations->firstWhere('name', 'Standard Glamping');
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#6b7280" @click="book('{{ url('/booking/staycation?type=staycation&package=Standard%20Glamping&price=' . ($roomDetail['price'] ?? 0)) }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- Premium Glamping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#00848d;color:white"><h4 class="text-lg md:text-xl font-semibold flex items-center gap-2"><i class="bi bi-star-fill"></i> Premium Glamping</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'Premium Glamping'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'Premium Glamping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
                  <x-slider-arrows />
                  <div class="swiper-pagination"></div>
                  <!-- Swiper built-in dots for Premium Glamping -->
                </div>
                 <div class="flex items-center justify-between">
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starts at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">Premium Glamping</span>
                 </div>
                @php
                    $staycation = $staycations->firstWhere('name', 'Premium Glamping');
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#00848d" @click="book('{{ url('/booking/staycation?type=staycation&package=Premium%20Glamping&price=' . ($roomDetail['price'] ?? 0)) }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeGlampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      {{-- Camping Options Modal --}}
      <div x-show="campingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeCampingModal()" @click.self="closeCampingModal()" style="display:none;">
        <div class="w-full max-w-7xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-4 md:px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A;"><i class="bi bi-house me-2"></i>Camping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeCampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
            {{-- Dome Tent --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#00848d;color:white"><h4 class="text-lg md:text-xl font-semibold flex items-center gap-2" style=""><i class="bi bi-tent"></i>Dome Tent</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'Dome Tent'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'Dome Tent' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
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
                  <div class="swiper-pagination"></div>
                </div>
                <div class="flex items-center justify-between">
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starts at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">Comfort</span>
                </div>
                @php
                    $staycation = $staycations->firstWhere('name', 'Dome Tent');
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#00848d" @click="book('{{ url('/booking/staycation?type=camping&package=Dome%20Tent&price=' . ($roomDetail['price'] ?? 0)) }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- Bell Tent --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#7a7d52;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-house-fill"></i>Bell Tent</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'Bell Tent'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'Bell Tent' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
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
                  <div class="swiper-pagination"></div>
                </div>
                <div class="flex items-center justify-between">
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starts at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">Spacious</span>
                </div>
                @php
                    $staycation = $staycations->firstWhere('name', 'Bell Tent');
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#7a7d52" @click="book('{{ url('/booking/staycation?type=camping&package=Bell%20Tent&price=' . ($roomDetail['price'] ?? 0)) }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- BYOT --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#843c23;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-backpack"></i>BYOT</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'BYOT'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'BYOT' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
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
                  <div class="swiper-pagination"></div>
                </div>
                <div class="flex items-center justify-between">
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starts at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dcfce7;color:#166534">Budget</span>
                </div>
                @php
                    $staycation = $staycations->firstWhere('name', 'BYOT');
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#843c23" @click="book('{{ url('/booking/staycation?type=camping&package=BYOT&price=' . ($roomDetail['price'] ?? 0)) }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- Car Camping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#db8636;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-car-front-fill"></i>Car Camping</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'Car Camping'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'Car Camping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
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
                  <div class="swiper-pagination"></div>
                </div>
                <div class="flex items-center justify-between">
                   @php
                     $staycation = $staycations->firstWhere('name', 'Car Camping');
                     $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? null) : null;
                   @endphp
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starts at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#fef3c7;color:#7c2d12">Convenient</span>
                </div>
                @php
                    $staycation = $staycations->firstWhere('name', 'Car Camping');
                     $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#db8636" @click="book('{{ url('/booking/staycation?type=camping&package=Car%20Camping&price=' . ($roomDetail['price'] ?? 0)) }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

          </div>
          <div class="px-4 md:px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeCampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      {{-- VR Preview (Pannellum) --}}
      <div x-show="vrOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80" @keydown.escape.window="closeVR()" @click.self="closeVR()" style="display:none;">
        <div class="relative w-full max-w-300 mx-4 bg-black rounded-2xl shadow-2xl overflow-hidden">
          {{-- Header --}}
          <div class="flex items-center justify-between px-6 py-4" style="background:rgba(255,255,255,0.05)">
            <h3 class="text-white font-semibold"><i class="bi bi-eye me-2"></i>Virtual Reality Preview</h3>
            <button class="p-2 rounded hover:bg-white/10 text-white" @click="closeVR()"><i class="bi bi-x-lg"></i></button>
          </div>

            {{-- Car Camping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#db8636;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-car-front-fill"></i>Car Camping</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php $staycation = $staycations->firstWhere('name', 'Car Camping'); @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-64 object-cover" alt="{{ $img->title ?? 'Car Camping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @endforeach
                  </div>
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
                  <div class="swiper-pagination"></div>
                </div>
                <div class="flex items-center justify-between">
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['seasonType']) && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight text-blue-700">{{ $roomDetails[$staycation->id]['seasonType'] }}: ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#fef9c3;color:#a16207">Adventure</span>
                </div>
              <p id="vr-info-description" class="text-sm text-white/80">Description</p>
              <div class="mt-2 flex gap-2">
                <span class="text-xs px-2 py-1 rounded bg-blue-600">360° View</span>
                <span class="text-xs px-2 py-1 rounded bg-green-600">HD Quality</span>
                <span class="text-xs px-2 py-1 rounded bg-cyan-600">Interactive</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </main>

    {{-- Floating Buttons --}} 
    <x-floating-buttons />


    @php
    $vrData = [
        'glamping' => [
            'images' => [
                ['src' => \App\Helpers\ImageHelper::src('assets/images/carousel/Glamping-1.jpg'), 'title' => 'Glamping Tent Interior', 'desc' => 'Luxurious tent setup with all amenities'],
                ['src' => \App\Helpers\ImageHelper::src('assets/images/carousel/staycation.png'), 'title' => 'Glamping Area View', 'desc' => 'Beautiful surroundings of the glamping site'],
                ['src' => \App\Helpers\ImageHelper::src('assets/images/carousel/Glamping-1.jpg'), 'title' => 'Glamping Facilities', 'desc' => 'Access to premium facilities and services']
            ],
            'info' => ['title' => 'Glamping VR Experience', 'desc' => 'Immerse yourself in the luxury of glamping']
        ],
        'camping' => [
            'images' => [
                ['src' => \App\Helpers\ImageHelper::src('assets/images/carousel/BYOT-2.jpg'), 'title' => 'Camping Site Overview', 'desc' => 'Spacious camping area in nature'],
                ['src' => \App\Helpers\ImageHelper::src('assets/images/carousel/Car-camping3.jpg'), 'title' => 'Camping Facilities', 'desc' => 'Common areas and amenities for campers'],
                ['src' => \App\Helpers\ImageHelper::src('assets/images/carousel/games-grounds4.jpg'), 'title' => 'Camping Activities', 'desc' => 'Fun activities available at the campsite']
            ],
            'info' => ['title' => 'Camping VR Experience', 'desc' => 'Explore the natural camping environment']
        ]
    ];
    @endphp

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Pass PHP data to JavaScript --}}
    <script>
      window.vrData = <?php echo json_encode($vrData); ?>;
    </script>

    <script>
      // ---------- Alpine component ----------
      function staycationPage(){
        return {
          // modals 
          glampingOpen: false,
          campingOpen: false,
          vrOpen: false,
          vrType: '',
          vrCurrentIndex: 0,
          vrData: {},
          pannellumViewer: null,
          book(url){
            const calendarUrl = new URL('{{ route('booking.calendar') }}', window.location.origin);
            calendarUrl.searchParams.set('type', 'staycation');

            // Preserve selected package hint from the original button URL if present.
            try {
              const originalUrl = new URL(url, window.location.origin);
              const packageName = originalUrl.searchParams.get('package');
              if (packageName) {
                calendarUrl.searchParams.set('package', packageName);
              }
            } catch (e) {
              // Ignore malformed fallback URLs and continue to calendar.
            }

            window.location.href = calendarUrl.toString();
          },
          openModal(which){
            if(which==='glamping') this.glampingOpen = true; else this.campingOpen = true;
            document.body.style.overflow = 'hidden';
          },
          closeGlampingModal(){
            this.glampingOpen = false;
            document.body.style.overflow = '';
          },
          closeCampingModal(){
            this.campingOpen = false;
            document.body.style.overflow = '';
          },
          openVR(type){
            this.vrType = type;
            this.vrData = this.getVRData(type);
            this.vrCurrentIndex = 0;
            this.vrOpen = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.initVRViewer());
          },
          closeVR(){
            this.vrOpen = false;
            document.body.style.overflow = '';
            if(this.pannellumViewer) {
              this.pannellumViewer.destroy();
              this.pannellumViewer = null;
            }
          },
          vrPrev(){
            if(this.vrCurrentIndex > 0){
              this.vrCurrentIndex--;
              this.updateVRViewer();
            }
          },
          vrNext(){
            if(this.vrCurrentIndex < this.vrData.images.length - 1){
              this.vrCurrentIndex++;
              this.updateVRViewer();
            }
          },
          vrFullscreen(){
            if(this.pannellumViewer) this.pannellumViewer.toggleFullscreen();
          },
          toggleVRInfo(){
            const overlay = document.getElementById('vr-info-overlay');
            overlay.style.display = overlay.style.display === 'none' ? 'block' : 'none';
          },
          shareVR(){
            // Placeholder for share functionality
            alert('Share functionality not implemented yet.');
          },
          getVRData(type){
            return window.vrData[type];
          },
          initVRViewer(){
            if(typeof pannellum !== 'undefined'){
              this.pannellumViewer = pannellum.viewer('vr-media-container', {
                type: 'equirectangular',
                panorama: this.vrData.images[this.vrCurrentIndex].src,
                autoLoad: true,
                compass: false,
                hotSpots: []
              });
              this.updateVRCounter();
              this.updateVRInfo();
            }
          },
          updateVRViewer(){
            if(this.pannellumViewer){
              this.pannellumViewer.setPanorama(this.vrData.images[this.vrCurrentIndex].src);
              this.updateVRCounter();
              this.updateVRInfo();
            }
          },
          updateVRCounter(){
            document.getElementById('vr-counter').textContent = `${this.vrCurrentIndex + 1} / ${this.vrData.images.length}`;
          },
          updateVRInfo(){
            document.getElementById('vr-info-title').textContent = this.vrData.images[this.vrCurrentIndex].title;
            document.getElementById('vr-info-description').textContent = this.vrData.images[this.vrCurrentIndex].desc;
          },
          openGallery(type, option){
            this.galleryType = type;
            this.galleryOption = option;
            const data = this.getGalleryData(type + '-' + option);
            this.galleryImages = data.images || [];
            this.galleryCurrentIndex = 0;
            this.galleryOpen = true;
            document.body.style.overflow = 'hidden';
          },
          closeGallery(){
            this.galleryOpen = false;
            document.body.style.overflow = '';
          },
          galleryPrev(){
            if(this.galleryCurrentIndex > 0) this.galleryCurrentIndex--;
          },
          galleryNext(){
            if(this.galleryCurrentIndex < this.galleryImages.length - 1) this.galleryCurrentIndex++;
          },
          getGalleryData(key){
            return window.galleryData[key] || {};
          }
        }
      }


      function initModalSwipers(){
        if(!window.Swiper) return;
        document.querySelectorAll(".swiper[data-swiper='modal']").forEach((el)=>{
          try{
            if(!el.swiper){
              // Count actual slides and determine if loop should be enabled
              const slides = el.querySelectorAll('.swiper-slide');
              const totalSlides = slides.length;
              const enableLoop = totalSlides >= 2; // Need at least 2 slides for loop
              
              const swiper = new Swiper(el, {
                slidesPerView: 1,
                loop: enableLoop,
                navigation: { nextEl: el.querySelector('[data-custom-next]'), prevEl: el.querySelector('[data-custom-prev]') },
                pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
              });
              // Custom dots (if you want to keep custom, otherwise rely on Swiper's built-in)
              const dotsEl = el.querySelector('.swiper-pagination');
              function updateDots() {
                if (!dotsEl) return;
                const slides = el.querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate)');
                dotsEl.innerHTML = '';
                for(let i=0; i<slides.length; i++){
                  const b = document.createElement('button');
                  b.type = 'button';
                  b.className = 'h-2.5 rounded-full mx-1 ' + (i === swiper.realIndex ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
                  b.setAttribute('role','tab');
                  b.setAttribute('aria-label', `Go to slide ${i+1}`);
                  b.addEventListener('click', ()=> swiper.slideToLoop(i));
                  dotsEl.appendChild(b);
                }
              }
              updateDots();
              swiper.on('slideChange', updateDots);
            }
          }catch(e){ console.warn('Modal swiper init failed', e); }
        });
      }

      document.addEventListener('DOMContentLoaded', function(){
        initModalSwipers();
        document.querySelectorAll('a[href^="#"]').forEach(a => {
          a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (!id || id === '#') return;
            const target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          });
        });
      });
    </script>
    <script>
      // Periodically fetch the latest Glamping image from live booking site
      (function(){
        const endpoints = [
          { selector: 'img[alt="Glamping Tent"]', internal: '/api/internal/glamping-image', external: '/api/external/glamping-image' },
          { selector: 'img[alt="Camping"]', internal: '/api/internal/camping-image' }
        ];

        async function fetchJson(path){
          try{
            const url = window.location.origin + path;
            const res = await fetch(url, {cache: 'no-store'});
            if (!res.ok) return null;
            return await res.json();
          } catch(e){ return null; }
        }

        async function updateImageFor(entry){
          try{
            let json = await fetchJson(entry.internal);
            if ((!json || !json.image) && entry.external) {
              json = await fetchJson(entry.external);
            }
            if (!json || !json.image) return;
            const img = document.querySelector(entry.selector);
            if (!img) return;
            const newSrc = json.image + '?_=' + Date.now();
            if (img.getAttribute('data-initial-src') === newSrc) return;
            img.src = newSrc;
            img.setAttribute('data-initial-src', newSrc);
          }catch(e){ console.warn('Image update failed for', entry.selector, e); }
        }

        function refreshAllImages(){
          endpoints.forEach(e => updateImageFor(e));
        }
        // Initial fetch shortly after DOM ready
        window.addEventListener('load', function(){ updateGlampingImage(); setInterval(updateGlampingImage, 30000); });
      })();
    </script>
  </body>
</html>

</x-page-layout>

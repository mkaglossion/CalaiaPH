<x-page-layout activeHeader="staycation" :include-staycation-script="true">



    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- Main --}}
    <main id="main" class="" x-data="staycationPage()" x-on:open-staycation-modal.window="openModal($event.detail)">
      <section id="staycation-rooms" class="container mx-auto max-w-7xl px-4 py-10">
        <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.3em]" style="color:#8f6a60;">{{ $staycationPageLabel ?? 'Staycation Options' }}</p>
            <h2 class="mt-2 font-extrabold uppercase tracking-wide staycation-heading text-3xl md:text-5xl">{{ $staycationPageTitle ?? 'All Glamping & Camping' }}</h2>
          </div>
          <p class="max-w-2xl text-sm leading-6 text-gray-600 md:text-base">
            {{ $staycationPageIntro ?? 'Browse every room directly on the page. The category modals are still available below if you want the full feature list and gallery.' }}
          </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          @foreach($staycations as $staycation)
            @php
              $roomDetail = $roomDetails[$staycation->id] ?? [];
              $roomType = strtolower(trim((string) ($roomDetail['type'] ?? $staycation->type ?? 'camping')));
              $roomTypeColor = $roomType === 'glamping' ? '#a75e3e' : '#00848d';
              $roomTypeBg = $roomType === 'glamping' ? 'rgba(167,94,62,0.10)' : 'rgba(0,132,141,0.10)';
              $roomDescription = $roomDetail['description'] ?? '';
              $roomStatus = strtolower((string) ($roomDetail['status'] ?? 'available'));
              $roomStatusLabel = ucfirst($roomStatus);
              $roomStatusBg = $roomStatus === 'limited' ? '#fef3c7' : ($roomStatus === 'unavailable' ? '#fee2e2' : '#dcfce7');
              $roomStatusColor = $roomStatus === 'limited' ? '#92400e' : ($roomStatus === 'unavailable' ? '#991b1b' : '#166534');
              $currentPrice = $roomDetail['currentPrice'] ?? null;
              $bookingUrl = url('/booking/staycation?' . http_build_query([
                  'type' => $roomType ?: 'camping',
                  'package' => $staycation->name,
                  'price' => $roomDetail['price'] ?? 0,
              ]));
              $featuresText = $staycation->texts->firstWhere('section', 'features');
              $featureLines = preg_split('/\r\n|\r|\n/', (string) ($featuresText->content ?? '')) ?: [];
              $features = array_values(array_filter(array_map('trim', $featureLines)));
            @endphp

            <article class="flex h-full min-h-190 flex-col overflow-hidden rounded-[22px] border border-gray-200 bg-white shadow-[0_10px_16px_rgba(0,0,0,0.08)] transition-all duration-500 hover:-translate-y-1 hover:border-gray-300 hover:shadow-2xl">
              <div class="relative">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @forelse(($staycation->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" alt="{{ $img->title ?? $staycation->name }}" class="h-80 w-full object-cover sm:h-88 lg:h-104" onerror="this.onerror=null; this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                      </div>
                    @empty
                      <div class="swiper-slide">
                        <div class="flex h-80 w-full items-center justify-center bg-gray-100 text-gray-400 sm:h-88 lg:h-104">No image uploaded</div>
                      </div>
                    @endforelse
                  </div>
                  <x-slider-arrows />
                  <div class="swiper-pagination"></div>
                </div>

                <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide" style="background:{{ $roomTypeBg }}; color:{{ $roomTypeColor }};">
                    {{ ucfirst($roomType) }}
                  </span>
                  <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide" style="background:{{ $roomStatusBg }}; color:{{ $roomStatusColor }};">
                    {{ $roomStatusLabel }}
                  </span>
                </div>
              </div>

              <div class="flex flex-1 flex-col space-y-4 p-5">
                <div>
                  <h3 class="font-extrabold capitalize text-2xl text-black">{{ $staycation->name }}</h3>
                  <p class="mt-2 text-sm leading-6 text-gray-600">{{ $roomDescription }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div class="rounded-2xl bg-gray-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-widest text-gray-500">Starting at</p>
                    <div class="mt-1 flex items-baseline gap-1 font-semibold" style="color:#7a7d52;">
                      @if($currentPrice)
                        <span class="text-xl leading-none">&#8369;{{ number_format($currentPrice) }}</span>
                        <span class="text-sm text-gray-500">/night</span>
                      @else
                        <span class="text-sm text-gray-500">No active rate</span>
                      @endif
                    </div>
                  </div>
                  <div class="rounded-2xl bg-gray-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-widest text-gray-500">Availability</p>
                    <p class="mt-1 font-semibold text-gray-900">
                      {{ isset($roomDetail['available_quantity']) ? $roomDetail['available_quantity'] : 'N/A' }}
                    </p>
                  </div>
                </div>

                @if(!empty($features))
                  <div class="flex-1 max-h-72 overflow-y-auto pr-1">
                    <x-staycation-features
                      :features="$features"
                      :showHeader="true"
                      ulClass="space-y-1"
                      liClass="px-3 py-2 bg-gray-50 rounded text-sm text-gray-700"
                      headerClass="text-xl font-bold mb-3"
                    />
                  </div>
                @endif

                <div class="mt-auto flex flex-col gap-3 sm:flex-row">
                  <button type="button" class="inline-flex flex-1 items-center justify-center rounded-full px-5 py-3 text-sm font-semibold uppercase tracking-wide text-white" style="background:{{ $roomTypeColor }};" @click="book(@js($bookingUrl))">Book now</button>
                  <button type="button" class="inline-flex flex-1 items-center justify-center rounded-full px-5 py-3 text-sm font-semibold uppercase tracking-wide text-white" style="background:#6b7280;" @click="openVR(@js($staycation?->id), @js($staycation?->name), @js($roomType === 'glamping' ? 'glamping' : 'camping'))">VR Preview</button>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </section>

      @php
        $roomGroups = $staycations->groupBy(function ($staycation) use ($roomDetails) {
          return strtolower(trim((string) ($roomDetails[$staycation->id]['type'] ?? $staycation->type ?? 'camping')));
        });
        $glampingRooms = $roomGroups->get('glamping', collect());
        $campingRooms = $roomGroups->get('camping', collect());
        $modalConfigs = [
          [
            'openFlag' => 'glampingOpen',
            'closeMethod' => 'closeGlampingModal',
            'title' => 'Glamping Options',
            'titleIcon' => 'bi bi-stars',
            'titleColor' => '#97655A',
            'bodyClass' => 'p-6 grid grid-cols-1 gap-6 lg:grid-cols-2',
            'roomType' => 'glamping',
            'rooms' => $glampingRooms->values(),
            'themes' => [
              ['button' => '#6b7280', 'badgeBg' => '#e5e7eb', 'badgeColor' => '#374151'],
              ['button' => '#00848d', 'badgeBg' => '#dbeafe', 'badgeColor' => '#1d4ed8'],
            ],
          ],
          [
            'openFlag' => 'campingOpen',
            'closeMethod' => 'closeCampingModal',
            'title' => 'Camping Options',
            'titleIcon' => 'bi bi-house',
            'titleColor' => '#97655A',
            'bodyClass' => 'p-4 grid grid-cols-1 gap-4 md:p-6 md:gap-6 lg:grid-cols-2',
            'roomType' => 'camping',
            'rooms' => $campingRooms->values(),
            'themes' => [
              ['button' => '#00848d', 'badgeBg' => '#dbeafe', 'badgeColor' => '#1d4ed8'],
              ['button' => '#7a7d52', 'badgeBg' => '#e5e7eb', 'badgeColor' => '#374151'],
              ['button' => '#843c23', 'badgeBg' => '#dcfce7', 'badgeColor' => '#166534'],
              ['button' => '#db8636', 'badgeBg' => '#fef3c7', 'badgeColor' => '#7c2d12'],
            ],
          ],
        ];
      @endphp

      @foreach($modalConfigs as $modalConfig)
        <div x-show="{{ $modalConfig['openFlag'] }}" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="{{ $modalConfig['closeMethod'] }}()" @click.self="{{ $modalConfig['closeMethod'] }}()" style="display:none;">
          <div class="max-h-[90vh] w-full max-w-7xl overflow-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-4 py-4 md:px-6" style="border-color: rgba(128,128,128,0.3);">
              <h3 class="text-2xl font-bold" style="color:{{ $modalConfig['titleColor'] }};"><i class="{{ $modalConfig['titleIcon'] }} me-2"></i>{{ $modalConfig['title'] }}</h3>
              <button class="rounded p-2 hover:bg-gray-100" @click="{{ $modalConfig['closeMethod'] }}()"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="{{ $modalConfig['bodyClass'] }}">
              @forelse($modalConfig['rooms'] as $index => $staycation)
                @php
                  $roomDetail = $roomDetails[$staycation->id] ?? [];
                  $bookingUrl = url('/booking/staycation?' . http_build_query([
                    'type' => $roomDetail['type'] ?? $modalConfig['roomType'],
                    'package' => $staycation->name,
                    'price' => $roomDetail['price'] ?? 0,
                  ]));
                  $featuresText = $staycation->texts->firstWhere('section', 'features');
                  $featureLines = preg_split('/\r\n|\r|\n/', (string) ($featuresText->content ?? '')) ?: [];
                  $features = array_values(array_filter(array_map('trim', $featureLines)));
                  $themeOptions = $modalConfig['themes'];
                  $theme = $themeOptions[$index % count($themeOptions)];
                  $currentPrice = $roomDetail['currentPrice'] ?? null;
                @endphp

                <div class="overflow-hidden rounded-xl" style="border: 1px solid rgba(128,128,128,0.5);">
                  <div class="p-4 space-y-4">
                    <div class="swiper relative" data-swiper="modal">
                      <div class="swiper-wrapper">
                        @forelse(($staycation->swiperImages ?? []) as $img)
                          <div class="swiper-slide">
                            <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="h-80 w-full object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? $staycation->name }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                          </div>
                        @empty
                          <div class="swiper-slide">
                            <div class="flex h-80 w-full items-center justify-center bg-gray-100 text-gray-400 sm:h-80 md:h-104">No image uploaded</div>
                          </div>
                        @endforelse
                      </div>
                      <x-slider-arrows />
                      <div class="swiper-pagination"></div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                      <div class="text-left">
                        @if($currentPrice)
                          <div class="font-bold leading-tight" style="color:#7a7d52">Starting at &#8369;{{ number_format($currentPrice) }} <span class="text-xs text-gray-500">/night</span></div>
                        @else
                          <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                        @endif
                      </div>
                      <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:{{ $theme['badgeBg'] }};color:{{ $theme['badgeColor'] }};">{{ $staycation->name }}</span>
                    </div>

                    @if(!empty($features))
                      <x-staycation-features :features="$features" />
                    @endif

                    <div class="flex gap-2">
                      <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:{{ $theme['button'] }}" @click="book(@js($bookingUrl))">Book Now</button>
                      <button type="button" class="flex-1 rounded-lg border px-4 py-2 font-semibold" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation->id), @js($staycation->name), @js($modalConfig['roomType']))"><i class="bi bi-eye me-1"></i>VR Preview</button>
                    </div>
                  </div>
                </div>
              @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">
                  No rooms available in this category yet.
                </div>
              @endforelse
            </div>

            <div class="flex justify-end border-t px-4 py-4 md:px-6" style="border-color: rgba(128,128,128,0.3);">
              <button type="button" @click="{{ $modalConfig['closeMethod'] }}()" class="w-full rounded-2xl bg-gray-500 px-6 py-3 font-semibold text-white transition-colors duration-300 hover:bg-gray-600 md:w-auto">Close</button>
            </div>
          </div>
        </div>
      @endforeach

      @if(false)
      {{-- Glamping Options Modal --}}
      <div x-show="glampingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeGlampingModal()" @click.self="closeGlampingModal()" style="display:none;">
        <div class="w-full max-w-7xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A;"><i class="bi bi-stars me-2"></i>Glamping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeGlampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php
                      $staycation = $glampingRooms->values()->get(0);
                      $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? []) : [];
                      $bookingUrl = url('/booking/staycation?' . http_build_query([
                        'type' => 'staycation',
                        'package' => $staycation?->name ?? 'Standard Glamping',
                        'price' => $roomDetail['price'] ?? 0,
                      ]));
                    @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-80 object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? 'Standard Glamping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starting at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">{{ $staycation?->name ?? 'Glamping Room' }}</span>
                </div>
                @php
                    // Reuse the room selected above so the modal stays aligned with admin order
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#6b7280" @click="book(@js($bookingUrl))">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation?->id), @js($staycation?->name ?? 'Standard Glamping'), 'glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- Premium Glamping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php
                      $staycation = $glampingRooms->values()->get(1);
                      $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? []) : [];
                      $bookingUrl = url('/booking/staycation?' . http_build_query([
                        'type' => 'staycation',
                        'package' => $staycation?->name ?? 'Premium Glamping',
                        'price' => $roomDetail['price'] ?? 0,
                      ]));
                    @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-80 object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? 'Premium Glamping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starting at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">{{ $staycation?->name ?? 'Glamping Room' }}</span>
                 </div>
                @php
                    // Reuse the room selected above so the modal stays aligned with admin order
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#00848d" @click="book(@js($bookingUrl))">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation?->id), @js($staycation?->name ?? 'Premium Glamping'), 'glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
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
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php
                      $staycation = $campingRooms->values()->get(0);
                      $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? []) : [];
                      $bookingUrl = url('/booking/staycation?' . http_build_query([
                        'type' => 'staycation',
                        'package' => $staycation?->name ?? 'Dome Tent',
                        'price' => $roomDetail['price'] ?? 0,
                      ]));
                    @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-80 object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? 'Dome Tent' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starting at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">{{ $staycation?->name ?? 'Camping Room' }}</span>
                </div>
                @php
                    // Reuse the room selected above so the modal stays aligned with admin order
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#00848d" @click="book(@js($bookingUrl))">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation?->id), @js($staycation?->name ?? 'Dome Tent'), 'camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- Bell Tent --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php
                      $staycation = $campingRooms->values()->get(1);
                      $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? []) : [];
                      $bookingUrl = url('/booking/staycation?' . http_build_query([
                        'type' => 'staycation',
                        'package' => $staycation?->name ?? 'Bell Tent',
                        'price' => $roomDetail['price'] ?? 0,
                      ]));
                    @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-80 object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? 'Bell Tent' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starting at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">{{ $staycation?->name ?? 'Camping Room' }}</span>
                </div>
                @php
                    // Reuse the room selected above so the modal stays aligned with admin order
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#7a7d52" @click="book(@js($bookingUrl))">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation?->id), @js($staycation?->name ?? 'Bell Tent'), 'camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- BYOT --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php
                      $staycation = $campingRooms->values()->get(2);
                      $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? []) : [];
                      $bookingUrl = url('/booking/staycation?' . http_build_query([
                        'type' => 'staycation',
                        'package' => $staycation?->name ?? 'BYOT',
                        'price' => $roomDetail['price'] ?? 0,
                      ]));
                    @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-80 object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? 'BYOT' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starting at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dcfce7;color:#166534">{{ $staycation?->name ?? 'Camping Room' }}</span>
                </div>
                @php
                    // Reuse the room selected above so the modal stays aligned with admin order
                    $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#843c23" @click="book(@js($bookingUrl))">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation?->id), @js($staycation?->name ?? 'BYOT'), 'camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            {{-- Car Camping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    @php
                      $staycation = $campingRooms->values()->get(3);
                      $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? []) : [];
                      $bookingUrl = url('/booking/staycation?' . http_build_query([
                        'type' => 'staycation',
                        'package' => $staycation?->name ?? 'Car Camping',
                        'price' => $roomDetail['price'] ?? 0,
                      ]));
                    @endphp
                    @foreach(($staycation?->swiperImages ?? []) as $img)
                      <div class="swiper-slide">
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-full h-80 object-cover sm:h-80 md:h-104" alt="{{ $img->title ?? 'Car Camping' }}" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
                     // Reuse the room selected above so the modal stays aligned with admin order
                     $roomDetail = $staycation ? ($roomDetails[$staycation->id] ?? null) : null;
                   @endphp
                   <div class="text-left">
                     @if($staycation && isset($roomDetails[$staycation->id]['currentPrice']) && $roomDetails[$staycation->id]['currentPrice'])
                       <div class="font-bold leading-tight" style="color:#7a7d52">Starting at ₱{{ number_format($roomDetails[$staycation->id]['currentPrice']) }} <span class="text-xs text-gray-500">/night</span></div>
                     @else
                       <div class="font-bold leading-tight text-red-700">No pricing set for current season</div>
                     @endif
                   </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#fef3c7;color:#7c2d12">{{ $staycation?->name ?? 'Camping Room' }}</span>
                </div>
                @php
                     // Reuse the room selected above so the modal stays aligned with admin order
                     $featuresText = $staycation ? $staycation->texts->firstWhere('section', 'features') : null;
                    $features = $featuresText ? explode("\n", $featuresText->content) : [];
                @endphp
                <x-staycation-features :features="$features" />
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#db8636" @click="book(@js($bookingUrl))">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR(@js($staycation?->id), @js($staycation?->name ?? 'Car Camping'), 'camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

          </div>
          <div class="px-4 md:px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeCampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      @endif

      {{-- VR Preview --}}
      <div x-show="vrOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80" @keydown.escape.window="closeVR()" @click.self="closeVR()" style="display:none;">
        <div class="relative mx-3 flex max-h-[93dvh] min-h-0 w-[min(96vw,66rem)] flex-col overflow-hidden rounded-2xl bg-black shadow-2xl sm:mx-4">
          {{-- Header --}}
          <div class="flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4" style="background:rgba(255,255,255,0.05)">
            <h3 class="text-white font-semibold"><i class="bi bi-eye me-2"></i>360 Panorama Preview</h3>
            <button class="p-2 rounded hover:bg-white/10 text-white" @click="closeVR()"><i class="bi bi-x-lg"></i></button>
          </div>

            <div class="flex-1 min-h-0 overflow-y-auto rounded-xl border border-white/10">
              <div class="px-4 py-2" :style="vrHeaderStyle">
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-2">
                    <i :class="vrHeaderIcon"></i>
                    <span id="vr-room-title" class="font-semibold" x-text="vrData.roomName || vrData.title || ''"></span>
                  </div>
                  <span id="vr-counter" class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-semibold text-white"></span>
                </div>
              </div>
              <div class="space-y-4 p-3 sm:p-4">
                <div x-show="(vrData.images || []).length > 0" class="relative h-[clamp(19rem,39vh,27rem)] w-full overflow-hidden rounded-xl bg-black sm:h-[clamp(20rem,39vh,29rem)] lg:h-[clamp(22rem,42vh,33rem)]">
                  <div
                    x-ref="vrPanoramaViewport"
                    class="relative h-full w-full overflow-hidden"
                    style="touch-action:none;"
                    @pointerdown.prevent="startVRPanorama($event)"
                    @pointermove.prevent="moveVRPanorama($event)"
                    @pointerup.prevent="endVRPanorama()"
                    @pointercancel.prevent="endVRPanorama()"
                    @lostpointercapture="endVRPanorama()"
                  >
                    <div
                      class="absolute inset-0"
                      style="will-change: background-position;"
                      :style="getVRPanoramaStyle()"
                    ></div>
                    <div class="absolute inset-x-0 top-0 h-24 bg-linear-to-b from-black/50 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 h-24 bg-linear-to-t from-black/50 to-transparent"></div>
                    <div x-show="getCurrentVRImage()?.isPanoramic" class="absolute left-3 top-3 rounded-full bg-black/55 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/80">
                      Drag to look around
                    </div>
                    <button
                      type="button"
                      aria-label="Previous scene"
                      class="absolute left-3 top-1/2 z-10 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-black/60 text-white shadow transition hover:bg-black/75"
                      @click.stop="prevVRScene()"
                      x-show="(vrData.images || []).length > 1"
                    >
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                    <button
                      type="button"
                      aria-label="Next scene"
                      class="absolute right-3 top-1/2 z-10 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-black/60 text-white shadow transition hover:bg-black/75"
                      @click.stop="nextVRScene()"
                      x-show="(vrData.images || []).length > 1"
                    >
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </div>
                </div>
                <div x-show="!(vrData.images || []).length" class="flex h-[clamp(19rem,39vh,27rem)] items-center justify-center rounded-xl border border-dashed border-white/10 bg-white/5 px-4 py-6 text-sm text-white/60 sm:h-[clamp(20rem,39vh,29rem)] lg:h-[clamp(22rem,42vh,33rem)]">
                  No VR media uploaded yet.
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4 sm:p-5">
                  <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                   <div class="text-left">
                     <template x-if="vrCurrentPrice">
                       <div>
                         <div class="font-bold leading-tight text-cyan-300">Starting at &#8369;<span x-text="new Intl.NumberFormat('en-US').format(vrCurrentPrice)"></span> <span class="text-xs text-gray-400">/night</span></div>
                       </div>
                     </template>
                     <template x-if="!vrCurrentPrice">
                       <div class="font-bold leading-tight text-red-300">No pricing set for current season</div>
                     </template>
                   </div>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" :style="vrBadgeStyle" x-text="vrBadgeLabel"></span>
                  </div>
                  <div class="mt-3">
                    <p id="vr-info-title" class="text-base font-semibold text-white sm:text-lg"></p>
                    <p id="vr-info-description" class="mt-1 text-sm leading-6 text-white/80"></p>
                  </div>
                  <div x-show="(vrData.images || []).length > 0" class="mt-3 flex flex-wrap gap-2">
                    <span class="text-xs px-2 py-1 rounded bg-blue-600">360 View</span>
                    <span class="text-xs px-2 py-1 rounded bg-green-600">HD Quality</span>
                    <span class="text-xs px-2 py-1 rounded bg-cyan-600">Interactive</span>
                  </div>
                </div>
              </div>
          </div>

        </div>
      </div>

    </main>

    {{-- Floating Buttons --}} 
    <x-floating-buttons />

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Pass PHP data to JavaScript --}}
    <script>
      window.vrData = @json($vrData);
      window.roomDetails = @json($roomDetails);
    </script>

    <script>
      // ---------- Alpine component ----------
      function staycationPage(){
        return {
          // modals 
          glampingOpen: false,
          campingOpen: false,
          vrOpen: false,
          vrCurrentIndex: 0,
          vrData: {},
          vrCurrentPrice: null,
          vrBadgeLabel: '',
          vrBadgeStyle: '',
          vrHeaderStyle: '',
          vrHeaderIcon: 'bi bi-eye',
          vrPanoramaOffset: 50,
          vrPanoramaDragging: false,
          vrPanoramaPointerId: null,
          vrPanoramaStartX: 0,
          vrPanoramaStartOffset: 50,
          vrSwiper: null,
          vrSwiperListener: null,
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
          resolveVrPrice(roomId){
            const detail = window.roomDetails?.[roomId] || {};
            const directPrice = Number(detail.currentPrice);
            if (Number.isFinite(directPrice) && directPrice > 0) {
              return directPrice;
            }

            const configs = Array.isArray(detail.price_configs) ? detail.price_configs : [];
            for (const config of configs) {
              const rates = config?.rates || {};
              for (const key of ['nonPeak', 'peak', 'superPeak']) {
                const rate = Number(rates?.[key] || 0);
                if (Number.isFinite(rate) && rate > 0) {
                  return rate;
                }
              }
            }

            const basePrice = Number(detail.price || 0);
            if (Number.isFinite(basePrice) && basePrice > 0) {
              return basePrice;
            }

            return null;
          },
          openVR(roomId, roomName, type){
            const data = this.getVRData(roomId, type);
            this.vrData = {
              ...data,
              roomName: roomName || data.roomName || data.title || '',
              roomType: type,
            };
            this.vrCurrentPrice = this.resolveVrPrice(roomId);
            this.vrBadgeLabel = this.vrData.badgeLabel || (type === 'glamping' ? 'Glamping' : 'Camping');
            this.vrBadgeStyle = this.vrData.badgeStyle || (type === 'glamping'
              ? 'background:rgba(167,94,62,0.10);color:#a75e3e'
              : 'background:rgba(0,132,141,0.10);color:#00848d');
            this.vrHeaderStyle = this.vrData.headerStyle || (type === 'glamping'
              ? 'background:#a75e3e;color:white'
              : 'background:#00848d;color:white');
            this.vrHeaderIcon = this.vrData.headerIcon || (type === 'glamping' ? 'bi bi-house-heart-fill' : 'bi bi-tree-fill');
            this.vrCurrentIndex = 0;
            this.resetVRPanorama();
            this.vrOpen = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
              this.updateVRCounter();
              this.updateVRInfo();
            });
          },
          closeVR(){
            this.vrOpen = false;
            document.body.style.overflow = '';
            this.vrSwiper = null;
            this.vrSwiperListener = null;
            this.vrData = {};
            this.vrCurrentIndex = 0;
            this.vrCurrentPrice = null;
            this.vrBadgeLabel = '';
            this.vrBadgeStyle = '';
            this.vrHeaderStyle = '';
            this.vrHeaderIcon = 'bi bi-eye';
            this.resetVRPanorama();
            this.updateVRCounter();
            this.updateVRInfo();
          },
          getVRData(roomId, type){
            return window.vrData?.[roomId] || window.vrData?.[type] || {};
          },
          getCurrentVRImage(){
            return this.vrData.images?.[this.vrCurrentIndex] || {};
          },
          getVRPanoramaStyle(){
            const currentImage = this.getCurrentVRImage();
            const src = currentImage?.src || '';
            const isPanoramic = Boolean(currentImage?.isPanoramic);

            if (!src) {
              return {
                backgroundColor: '#000000'
              };
            }

            return {
              backgroundImage: `url(${JSON.stringify(src)})`,
              backgroundRepeat: 'no-repeat',
              backgroundSize: isPanoramic ? 'auto 100%' : 'cover',
              backgroundPosition: isPanoramic ? `${this.vrPanoramaOffset}% center` : 'center center'
            };
          },
          resetVRPanorama(){
            this.vrPanoramaOffset = 50;
            this.vrPanoramaDragging = false;
            this.vrPanoramaPointerId = null;
            this.vrPanoramaStartX = 0;
            this.vrPanoramaStartOffset = 50;
          },
          startVRPanorama(event){
            const currentImage = this.getCurrentVRImage();
            if (!(this.vrData.images || []).length || !currentImage?.isPanoramic) {
              return;
            }

            this.vrPanoramaDragging = true;
            this.vrPanoramaPointerId = event.pointerId;
            this.vrPanoramaStartX = event.clientX;
            this.vrPanoramaStartOffset = this.vrPanoramaOffset;

            try {
              event.currentTarget?.setPointerCapture?.(event.pointerId);
            } catch (e) {}
          },
          moveVRPanorama(event){
            if (!this.vrPanoramaDragging || !this.getCurrentVRImage()?.isPanoramic) {
              return;
            }

            const viewport = this.$refs.vrPanoramaViewport;
            const width = viewport?.getBoundingClientRect?.().width || 1;
            const deltaPercent = ((event.clientX - this.vrPanoramaStartX) / width) * 100;
            const nextOffset = this.vrPanoramaStartOffset + deltaPercent;
            this.vrPanoramaOffset = Math.max(0, Math.min(100, nextOffset));
          },
          endVRPanorama(){
            this.vrPanoramaDragging = false;
            this.vrPanoramaPointerId = null;
          },
          setVRScene(index){
            const scenes = Array.isArray(this.vrData.images) ? this.vrData.images : [];
            if (!scenes.length) {
              return;
            }

            const nextIndex = Math.max(0, Math.min(index, scenes.length - 1));
            this.vrCurrentIndex = nextIndex;
            this.resetVRPanorama();
            this.updateVRCounter();
            this.updateVRInfo();
          },
          prevVRScene(){
            const scenes = Array.isArray(this.vrData.images) ? this.vrData.images : [];
            if (scenes.length < 2) {
              return;
            }

            const nextIndex = this.vrCurrentIndex <= 0 ? scenes.length - 1 : this.vrCurrentIndex - 1;
            this.setVRScene(nextIndex);
          },
          nextVRScene(){
            const scenes = Array.isArray(this.vrData.images) ? this.vrData.images : [];
            if (scenes.length < 2) {
              return;
            }

            const nextIndex = this.vrCurrentIndex >= scenes.length - 1 ? 0 : this.vrCurrentIndex + 1;
            this.setVRScene(nextIndex);
          },
          bindVRSwiper(){
            const el = document.querySelector('[data-vr-swiper]');
            if (!el || !el.swiper) {
              return;
            }

            const swiper = el.swiper;
            if (!this.vrSwiperListener) {
              this.vrSwiperListener = () => {
                const activeIndex = this.vrSwiper?.params?.loop ? this.vrSwiper.realIndex : this.vrSwiper?.activeIndex;
                this.vrCurrentIndex = Number.isFinite(activeIndex) ? activeIndex : 0;
                this.updateVRCounter();
                this.updateVRInfo();
              };
            }

            if (this.vrSwiper && this.vrSwiper !== swiper && this.vrSwiperListener) {
              try {
                this.vrSwiper.off('slideChange', this.vrSwiperListener);
              } catch (e) {}
            }

            this.vrSwiper = swiper;
            swiper.off('slideChange', this.vrSwiperListener);
            swiper.on('slideChange', this.vrSwiperListener);
            swiper.update();
            if (swiper.params?.loop && typeof swiper.slideToLoop === 'function') {
              swiper.slideToLoop(0, 0, false);
            } else if (typeof swiper.slideTo === 'function') {
              swiper.slideTo(0, 0, false);
            }
            this.vrCurrentIndex = 0;
            this.updateVRCounter();
            this.updateVRInfo();
          },
          updateVRCounter(){
            const counterEl = document.getElementById('vr-counter');
            if (!counterEl) return;
            const imageCount = this.vrData.images?.length || 0;
            counterEl.textContent = imageCount > 0 ? `${this.vrCurrentIndex + 1} / ${imageCount}` : '';
          },
          updateVRInfo(){
            const currentImage = this.getCurrentVRImage();
            const fallbackDesc = this.vrData.description || '';
            const roomName = this.vrData.roomName || this.vrData.title || '';
            const rawSlideTitle = String(currentImage.title || '').trim();
            const hasCustomSlideTitle = rawSlideTitle && !/^slide\s+\d+$/i.test(rawSlideTitle);
            const slideDesc = (currentImage.desc || '').trim();
            const roomTitleEl = document.getElementById('vr-room-title');
            const titleEl = document.getElementById('vr-info-title');
            const descEl = document.getElementById('vr-info-description');

            if (roomTitleEl) {
              roomTitleEl.textContent = roomName;
            }

            if (titleEl) {
              const titleText = hasCustomSlideTitle ? rawSlideTitle : '';
              titleEl.textContent = titleText;
              titleEl.style.display = titleText ? '' : 'none';
            }

            if (descEl) {
              const descText = slideDesc && !/^slide\s+\d+$/i.test(slideDesc) ? slideDesc : fallbackDesc;
              descEl.textContent = descText;
              descEl.style.display = descText ? '' : 'none';
            }
          }
        }
      }


      function initModalSwipers(){
        if(!window.Swiper) return;
        document.querySelectorAll(".swiper[data-swiper='modal']").forEach((el)=>{
          try{
            const slides = el.querySelectorAll('.swiper-slide');
            const totalSlides = slides.length;
            if (!totalSlides) {
              if (el.swiper) {
                el.swiper.destroy(true, true);
              }
              return;
            }

            if (el.swiper) {
              el.swiper.update();
              return;
            }

            const enableLoop = totalSlides >= 2;
            const swiper = new Swiper(el, {
              slidesPerView: 1,
              loop: enableLoop,
              autoplay: false,
              navigation: { nextEl: el.querySelector('[data-custom-next]'), prevEl: el.querySelector('[data-custom-prev]') },
              pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
            });
            const dotsEl = el.querySelector('.swiper-pagination');
            function updateDots() {
              if (!dotsEl) return;
              const realSlides = el.querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate)');
              dotsEl.innerHTML = '';
              for(let i=0; i<realSlides.length; i++){
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'mx-1 h-2.5 rounded-full ' + (i === swiper.realIndex ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
                b.setAttribute('role','tab');
                b.setAttribute('aria-label', `Go to slide ${i+1}`);
                b.addEventListener('click', ()=> swiper.slideToLoop(i));
                dotsEl.appendChild(b);
              }
            }
            updateDots();
            swiper.on('slideChange', updateDots);
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
  </body>
</html>

</x-page-layout>

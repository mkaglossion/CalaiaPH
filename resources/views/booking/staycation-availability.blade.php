{{-- resources/views/booking/staycation-availability.blade.php --}}
<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @php
        $enablePageRealtime = config('broadcasting.default') === 'pusher'
            && filled(config('broadcasting.connections.pusher.key'))
            && filled(config('broadcasting.connections.pusher.options.cluster'))
            && !in_array(request()->getHost(), ['127.0.0.1', 'localhost']);
    @endphp

    @if($enablePageRealtime)
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.5.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
        <script>
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                encrypted: true
            });
        </script>
    @endif

    <style>
        [x-cloak]{display:none!important;}
        .cards-grid-responsive {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (max-width: 767px) {
            .mobile-layout-mode-1 .cards-grid-responsive {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .mobile-layout-mode-2 .cards-grid-responsive {
                grid-template-columns: minmax(0, 1fr);
            }
            .mobile-layout-mode-3 .cards-grid-responsive {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 0;
                padding-bottom: 0;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .mobile-layout-mode-3 .cards-grid-responsive::-webkit-scrollbar {
                display: none;
            }
            .mobile-layout-mode-3 .cards-grid-responsive > * {
                flex: 0 0 100%;
                max-width: 100%;
                scroll-snap-align: start;
            }
            .mobile-layout-mode-3 .mobile-carousel-dots {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 0.35rem;
                margin-top: 0.75rem;
            }
            .mobile-layout-mode-3 .mobile-carousel-dot {
                width: 0.375rem;
                height: 0.375rem;
                border-radius: 9999px;
                background: #cbd5e1;
                border: 0;
                padding: 0;
                transition: all 0.2s ease;
            }
            .mobile-layout-mode-3 .mobile-carousel-dot.is-active {
                width: 1.125rem;
                background: #8f5d54;
            }
        }
        @media (min-width: 640px) {
            .cards-grid-responsive { gap: 1rem; }
        }
        @media (min-width: 768px) {
            .cards-grid-responsive { gap: 1.5rem; }
        }
        .room-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        @media (min-width: 430px) {
            .room-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .mobile-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .mobile-card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8rem;
        }
        @media (min-width: 640px) {
            .mobile-clamp-3 {
                display: block;
                -webkit-line-clamp: initial;
                overflow: visible;
            }
            .mobile-card-title {
                display: block;
                -webkit-line-clamp: initial;
                min-height: 0;
            }
        }

        .flatpickr-day { border-radius: 6px; }
        .fp-avail-high { background:#d4edda !important; color:#155724 !important; }
        .fp-avail-low  { background:#fff3cd !important; color:#856404 !important; }
        .fp-not-avail  { background:#f8d7da !important; color:#721c24 !important; }
        .fp-invalid    { background:#d3d3d3 !important; color:#6c757d !important; }

        .fp-checkin    { background:#3b82f6 !important; color:#fff !important; }
        .fp-checkout   { background:#2563eb !important; color:#fff !important; }
        .fp-inrange    { background:#93c5fd !important; color:#0f172a !important; }

        .swiper { height: 384px; }
        .swiper-button-next, .swiper-button-prev {
            color: white;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            width: 40px !important;
            height: 40px !important;
            margin-top: -20px !important;
        }
        .swiper-button-next::after, .swiper-button-prev::after { font-size: 16px !important; }
        .swiper-pagination { bottom: 10px !important; }
        .swiper-pagination-bullet-active { background: #8f5d54 !important; }

        /* Room Details Swiper */
        .room-details-swiper {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: #000;
        }

        .room-details-swiper .swiper-wrapper {
            align-items: stretch;
        }

        .room-details-swiper .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
        }

        .room-details-swiper .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1 !important;
            filter: none !important;
            mix-blend-mode: normal !important;
            transform: translateZ(0);
        }

        .room-details-swiper .swiper-button-next,
        .room-details-swiper .swiper-button-prev {
            width: 38px !important;
            height: 38px !important;
            border-radius: 9999px;
            background: rgba(0,0,0,0.45) !important;
            color: #fff !important;
        }

        .room-details-swiper .swiper-button-next::after,
        .room-details-swiper .swiper-button-prev::after {
            font-size: 13px !important;
            font-weight: 700 !important;
        }

        @media (min-width: 768px) {
            .room-details-swiper .swiper-button-next,
            .room-details-swiper .swiper-button-prev {
                width: 44px !important;
                height: 44px !important;
            }

            .room-details-swiper .swiper-button-next::after,
            .room-details-swiper .swiper-button-prev::after {
                font-size: 15px !important;
            }
        }

        .room-details-swiper-pagination {
            position: absolute !important;
            bottom: 12px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: auto !important;
            z-index: 20 !important;
            display: flex !important;
            justify-content: center !important;
        }

        .room-details-swiper-pagination .swiper-pagination-bullet {
            width: 7px !important;
            height: 7px !important;
            background: rgba(255,255,255,0.55) !important;
            opacity: 1 !important;
            margin: 0 4px !important;
        }

        .room-details-swiper-pagination .swiper-pagination-bullet-active {
            width: 18px !important;
            border-radius: 9999px !important;
            background: #fff !important;
        }

        .room-details-swiper .swiper-slide-shadow-left,
        .room-details-swiper .swiper-slide-shadow-right,
        .room-details-swiper .swiper-slide-shadow-top,
        .room-details-swiper .swiper-slide-shadow-bottom {
            display: none !important;
        }
        
        /* Wrapper for carousel */
        div[x-show*="roomDetailsImages"] {
            background: transparent !important;
        }
        
        /* Image Gallery Swiper */
        .image-gallery-swiper {
            width: 100% !important;
            height: auto !important;
        }
        .image-gallery-swiper .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        .image-gallery-swiper .swiper-button-next,
        .image-gallery-swiper .swiper-button-prev {
            width: 50px !important;
            height: 50px !important;
            background: rgba(255,255,255,0.2) !important;
        }
        .image-gallery-swiper .swiper-button-next:hover,
        .image-gallery-swiper .swiper-button-prev:hover {
            background: rgba(255,255,255,0.3) !important;
        }
        .image-gallery-swiper .swiper-pagination-bullet {
            background: rgba(255,255,255,0.5) !important;
        }
        .image-gallery-swiper .swiper-pagination-bullet-active {
            background: white !important;
        }
    </style>

    @php
        $mobileLayoutStep1 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step1_availability')->value('value') ?? 1);
    @endphp

    <div class="bg-[#f5efe7] min-h-screen mobile-layout-mode-{{ $mobileLayoutStep1 }}">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight">Select Room(s)</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-xs text-gray-500">
                    Select your preferred accommodation + guests + pet options
                </p>
            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <x-booking.progress-indicator :stepNumber="2" :totalSteps="5" :bookingType="'staycation'" />
            </div>

            {{-- Back + Guest mode --}}
            <div class="flex items-end justify-between mb-6">
                <a href="{{ route('booking.calendar') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Back to Dates
                </a>

                @if (session()->has('user.is_guest') && session('user.is_guest'))
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                          style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                        <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                    </span>
                @endif
            </div>

            <div
                x-data="staycationAvailabilityMerged({{ $roomsCount }}, {{ $adultsTotal }}, {{ $childrenTotal }}, {{ $vatRate }}, {{ $vatRatePercentage }})"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2 order-2 lg:order-1">
                    {{-- Cancellation Policy (Best Practice UI) --}}
                    @php
                      $defaultPolicy = old('cancellation_policy', session('booking.cancellation_policy', 'flexible'));
                    @endphp

                    <div
                      class="space-y-4 bg-white rounded-2xl shadow-lg p-6 mb-6"
                      x-data="{
                        policy: @js($defaultPolicy),
                        expanded: null,
                        policies: {
                          flexible: {
                            label: 'Flexible (Best value)',
                            badge: 'RECOMMENDED',
                            icon: 'bi-arrow-repeat',
                            highlight: 'border-amber-300 bg-amber-50/70',
                            ring: 'ring-amber-200',
                            bullets: [
                              '100% refundable - up to 7 days before stay',
                              '50% refundable - 3-6 days before stay',
                              'Non-refundable - 0-3 days before stay'
                            ],
                            fine: [
                              'Best for unsure plans—more flexible refund options.',
                              'Processing time: 3–5 business days after approval.',
                              'Rebooking: 20% charge if less than 7 days before stay, no charge if more than 7 days.'
                            ]
                          },
                          nonrefundable: {
                            label: 'Economy (Non-refundable)',
                            badge: 'LOWEST PRICE',
                            icon: 'bi-cash',
                            highlight: 'border-rose-200 bg-rose-50/70',
                            ring: 'ring-rose-200',
                            bullets: [
                              'No cash refund after booking is confirmed',
                              'You may rebook based on availability (date change rules may apply)'
                            ],
                            fine: [
                              'Best for confirmed travel dates.',
                              'Fees may apply when changing dates close to check-in.'
                            ]
                          }
                        }
                      }"
                    >
                      <div class="flex items-start justify-between gap-4">
                        <div>
                          <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="bi bi-calendar-check text-2xl text-[#8f5d54]"></i>
                            Cancellation Policy
                          </h3>
                          <p class="text-sm text-gray-600 mt-1">
                            Choose the cancellation terms that match your flexibility. Refund tiers are based on <span class="font-semibold">days before check-in</span>.
                          </p>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
                          <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1">
                            <i class="bi bi-shield-check text-green-600"></i>
                            Clear refund tiers
                          </span>
                          <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1">
                            <i class="bi bi-clock-history text-blue-600"></i>
                            3–5 business days
                          </span>
                        </div>
                      </div>

                      {{-- Server-side error for policy --}}
                      @error('cancellation_policy')
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                          {{ $message }}
                        </div>
                      @enderror

                      <fieldset class="space-y-3">
                        <legend class="sr-only">Select cancellation policy</legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          {{-- Flexible (Recommended) --}}
                          <label
                            class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
                            :class="policy === 'flexible' ? policies.flexible.highlight + ' ring-2 ' + policies.flexible.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
                          >
                            <input
                              type="radio"
                              id="cancellation_policy_flexible"
                              name="cancellation_policy"
                              value="flexible"
                              class="sr-only"
                              x-model="policy"
                              required
                            >

                            <div class="flex items-start justify-between gap-3">
                              <div class="flex items-start gap-3">
                                <i class="text-3xl bi" :class="policies.flexible.icon" aria-hidden="true"></i>
                                <div>
                                  <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">Flexible</p>
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                                      <span x-text="policies.flexible.badge"></span>
                                    </span>
                                  </div>
                                  <p class="text-xs text-gray-600 mt-1">Most flexible refund tiers as dates get closer.</p>
                                </div>
                              </div>

                              <div class="flex items-center gap-2">
                                <span
                                  class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
                                  :class="policy === 'flexible' ? 'border-amber-400 bg-amber-100 text-amber-700' : 'border-gray-200 bg-white text-gray-400'"
                                  aria-hidden="true"
                                >
                                  <i class="bi bi-check-lg"></i>
                                </span>
                              </div>
                            </div>

                            <div class="mt-4 space-y-2 text-xs text-gray-700">
                              <div class="rounded-xl border border-amber-200/60 bg-white/60 p-3">
                                <ul class="space-y-1.5">
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                      <i class="bi bi-check"></i>
                                    </span>
                                    <span><span class="font-semibold">Up to 7 days</span> — 100% refundable</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                      <i class="bi bi-dash"></i>
                                    </span>
                                    <span><span class="font-semibold">3–6 days</span> — 50% refundable</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                                      <i class="bi bi-x"></i>
                                    </span>
                                    <span><span class="font-semibold">0–3 days</span> — Non-refundable</span>
                                  </li>
                                </ul>
                              </div>

                              <button
                                type="button"
                                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
                                @click.stop="expanded = expanded === 'flexible' ? null : 'flexible'"
                                :aria-expanded="expanded === 'flexible'"
                              >
                                <i class="bi" :class="expanded === 'flexible' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                View full terms
                              </button>

                              <div x-show="expanded === 'flexible'" x-transition class="rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-600">
                                <ul class="list-disc pl-5 space-y-1">
                                  <li>Refund tier is based on days before check-in (server time).</li>
                                  <li>Processing time: 3–5 business days after approval (banks may take longer).</li>
                                  <li>No-show or early departure is non-refundable.</li>
                                  <li><strong>Rebooking Policy:</strong> 20% charge on all fees if rebooked less than 7 days before stay. No rebooking charge if rebooked more than 7 days before stay.</li>
                                </ul>
                              </div>
                            </div>
                          </label>

                          {{-- Non-refundable --}}
                          <label
                            class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
                            :class="policy === 'nonrefundable' ? policies.nonrefundable.highlight + ' ring-2 ' + policies.nonrefundable.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
                          >
                            <input
                              type="radio"
                              id="cancellation_policy_nonrefundable"
                              name="cancellation_policy"
                              value="nonrefundable"
                              class="sr-only"
                              x-model="policy"
                              required
                            >

                            <div class="flex items-start justify-between gap-3">
                              <div class="flex items-start gap-3">
                                <i class="text-3xl bi" :class="policies.nonrefundable.icon" aria-hidden="true"></i>
                                <div>
                                  <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">Non-Refundable</p>
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                      <span x-text="policies.nonrefundable.badge"></span>
                                    </span>
                                  </div>
                                  <p class="text-xs text-gray-600 mt-1">Lowest price for fixed travel plans.</p>
                                </div>
                              </div>

                              <div class="flex items-center gap-2">
                                <span
                                  class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
                                  :class="policy === 'nonrefundable' ? 'border-rose-400 bg-rose-100 text-rose-700' : 'border-gray-200 bg-white text-gray-400'"
                                  aria-hidden="true"
                                >
                                  <i class="bi bi-check-lg"></i>
                                </span>
                              </div>
                            </div>

                            <div class="mt-4 space-y-2 text-xs text-gray-700">
                              <div class="rounded-xl border border-rose-200/60 bg-white/60 p-3">
                                <ul class="space-y-1.5">
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                                      <i class="bi bi-x"></i>
                                    </span>
                                    <span class="font-semibold">No refund after booking confirmation</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                      <i class="bi bi-arrow-repeat"></i>
                                    </span>
                                    <span>Rebook options may be available (subject to rules/availability)</span>
                                  </li>
                                </ul>
                              </div>

                              <button
                                type="button"
                                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
                                @click.stop="expanded = expanded === 'nonrefundable' ? null : 'nonrefundable'"
                                :aria-expanded="expanded === 'nonrefundable'"
                              >
                                <i class="bi" :class="expanded === 'nonrefundable' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                View full terms
                              </button>

                              <div x-show="expanded === 'nonrefundable'" x-transition class="rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-600">
                                <ul class="list-disc pl-5 space-y-1">
                                  <li>No cash refund for cancellations, no-shows, or early departure.</li>
                                  <li>Date changes may be allowed depending on availability and lead time.</li>
                                  <li>Any fare/price difference will be charged upon rebooking.</li>
                                </ul>
                              </div>
                            </div>
                          </label>
                        </div>
                      </fieldset>

                      {{-- Quick summary (what you selected) --}}
                      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                          <div>
                            <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                              <i class="bi bi-check2-circle text-green-600"></i>
                              Selected Policy:
                              <span class="text-[#8f5d54]" x-text="policy ? (policy === 'standard' ? 'Standard' : (policy === 'flexible' ? 'Flexible' : 'Non-Refundable')) : '—'"></span>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                              Tip: Flexible is recommended if your plans might change.
                            </p>
                          </div>

                          <div class="text-xs text-gray-600">
                            <p class="font-semibold text-gray-800">Refund timeline</p>
                            <p>3–5 business days after approval</p>
                          </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                          <template x-for="(b, idx) in (policy ? policies[policy].bullets : [])" :key="idx">
                            <div class="flex items-start gap-2 rounded-xl border border-white bg-white px-3 py-2">
                              <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                <i class="bi bi-dot"></i>
                              </span>
                              <p class="text-xs text-gray-700" x-text="b"></p>
                            </div>
                          </template>
                        </div>

                        <div class="mt-3 text-xs text-gray-600">
                          <p class="font-semibold text-gray-800 mb-1">Important notes</p>
                          <ul class="list-disc pl-5 space-y-1">
                            <li>Peak season dates (e.g., Dec 15–Jan 5, Holy Week & Summer) may have stricter rules.</li>
                            <li>No-show or early departure is typically non-refundable.</li>
                            <li>Cancellations must be requested through the booking system.</li>
                            <li><strong>Rebooking:</strong> 20% charge on all fees if rebooked less than 7 days before stay. No charge if rebooked more than 7 days.</li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    {{-- Room Slots + Controls --}}
                    <div class="mb-6">
                        {{-- Horizontal Room Slots --}}
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <template x-for="slotNum in maxSlots" :key="slotNum">
                                <div class="flex-1 min-w-35 max-w-50">
                                    <div class="border-2 rounded-lg p-3 text-center transition-all cursor-pointer relative group"
                                         :class="getSlotClass(slotNum)"
                                         @click="slotNum <= selectedCount() ? removeRoomBySlot(slotNum) : null"
                                         :title="slotNum <= selectedCount() ? 'Click to remove this room' : 'Empty slot'">
                                        <div class="text-xs font-semibold uppercase tracking-wider mb-1"
                                             :class="getSlotNumberClass(slotNum)"
                                             x-text="'Room ' + slotNum">
                                        </div>
                                        <div class="text-sm font-medium truncate" x-text="getSlotRoomName(slotNum)"></div>
                                        {{-- Remove icon on hover --}}
                                        <div x-show="slotNum <= selectedCount()" 
                                             x-cloak
                                             class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer shadow-md"
                                             @click.stop="removeRoomBySlot(slotNum)">
                                            <i class="bi bi-x text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Controls Row --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <div>
                                <label for="roomType" class="block text-sm font-medium text-gray-700 mb-1">Filter:</label>
                                <select id="roomType" x-model="roomType"
                                        class="block w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54] text-sm">
                                    <option value="all">All</option>
                                    <option value="glamping">Glamping</option>
                                    <option value="camping">Camping</option>
                                </select>
                            </div>

                            {{-- Add Room Button (hidden when max 4 rooms reached) --}}
                            <button type="button"
                                    class="px-4 py-2 rounded-lg font-semibold bg-[#8f5d54] text-white hover:bg-[#7a4e48] transition flex items-center gap-2 mt-5"
                                    @click="addRoomSlot()"
                                    x-show="maxSlots < 4"
                                    x-cloak
                            >
                                <i class="bi bi-plus-lg"></i>
                                Add Room
                            </button>

                            <button type="button"
                                    class="px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition mt-5"
                                    @click="resetAll()"
                                    :disabled="selectedCount() === 0 && maxSlots === targetRooms"
                            >
                                Reset All
                            </button>

                            {{-- Rooms Counter --}}
                            <div class="ml-auto flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 mt-5">
                                <span class="text-sm text-gray-600">Slots:</span>
                                <span class="font-bold text-[#8f5d54]" x-text="maxSlots"></span>
                                <span class="text-gray-400">/</span>
                                <span class="font-bold text-gray-700">4</span>
                                <span class="text-gray-300 mx-1">|</span>
                                <span class="text-sm text-gray-600">Filled:</span>
                                <span class="font-bold text-green-600" x-text="selectedCount()"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Rooms Grid --}}
                    <div class="cards-grid-responsive">
                        @forelse($staycations as $staycation)
                            @php
                                $roomDetail = $roomDetails[$staycation->id];
                                $avail = $availability[$staycation->id];
                                $status = $roomDetail['status']; // available | limited | unavailable
                                $bgClass = match($status) {
                                    'available' => 'bg-green-500',
                                    'limited' => 'bg-yellow-500',
                                    default => 'bg-gray-500'
                                };
                            @endphp

                            <div
                                class="relative h-full flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-xl {{ $status === 'unavailable' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}
                                {{ $status === 'available' ? 'ring-2 ring-green-200 hover:ring-green-300' : ($status === 'limited' ? 'ring-2 ring-yellow-200 hover:ring-yellow-300' : 'ring-2 ring-gray-200') }}"
                                :class="{ 'opacity-50 cursor-not-allowed': getRemainingSlots({{ $staycation->id }}) === 0 && !isSelected({{ $staycation->id }}) }"
                                data-room-id="{{ $staycation->id }}"
                                data-type="{{ $roomDetail['type'] }}"
                                x-show="roomType === 'all' || roomType === '{{ $roomDetail['type'] }}'"
                                @click="handleCardClick($event, {{ $staycation->id }}, '{{ $roomDetail['type'] }}')"
                            >
                                {{-- Selected indicator --}}
                                <div class="absolute top-2 left-2 z-10" x-show="isSelected({{ $staycation->id }})" x-transition>
                                    <div class="w-6 h-6 bg-[#8f5d54] rounded-full flex items-center justify-center">
                                        <i class="bi bi-check text-white text-sm"></i>
                                    </div>
                                </div>

                                {{-- Image (First image only - carousel now in modal) --}}
                                <div class="relative">
                                    @if($staycation->image)
                                        <img src="{{ $staycation->image }}" alt="{{ $roomDetail['name'] }}" class="w-full h-44 sm:h-56 md:h-96 object-cover">
                                    @elseif($staycation->swiperImages && $staycation->swiperImages->count() > 0)
                                        <img src="{{ $staycation->swiperImages->first()->image_path }}" alt="{{ $roomDetail['name'] }}" class="w-full h-44 sm:h-56 md:h-96 object-cover">
                                    @else
                                        <div class="w-full h-44 sm:h-56 md:h-96 bg-linear-to-br from-blue-200 to-purple-200 flex items-center justify-center cursor-pointer hover:from-blue-300 hover:to-purple-300">
                                            <div class="text-center">
                                                <i class="bi bi-image text-4xl opacity-50 text-white block mb-2"></i>
                                                <p class="text-xs text-white opacity-70">No images</p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Status badge --}}
                                    <div class="absolute top-3 right-3 z-10">
                                        <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-bold text-white {{ $bgClass }}">
                                            @if($status === 'available')
                                                <i class="bi bi-check-circle-fill mr-1"></i>Available
                                            @elseif($status === 'limited')
                                                <i class="bi bi-exclamation-triangle-fill mr-1"></i>Limited
                                            @else
                                                <i class="bi bi-x-circle-fill mr-1"></i>Unavailable
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Type badge --}}
                                    <div class="absolute bottom-3 left-3 z-10">
                                        <span class="px-2.5 py-1 bg-[#8f5d54] text-white text-[10px] sm:text-xs md:text-sm font-semibold rounded-full">
                                            {{ ucfirst($roomDetail['type']) }}
                                        </span>
                                    </div>

                                    {{-- Qty badge --}}
                                    @if(($avail['min_quantity'] ?? 0) > 0)
                                        <div class="absolute bottom-3 right-3 z-10">
                                            <span class="availability-badge px-2 py-1 bg-black/70 text-white text-[10px] sm:text-xs font-semibold rounded-full"
                                                  x-text="getRemainingSlots({{ $staycation->id }}) + ' available'">
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-3 sm:p-4 md:p-6">
                                    <h3 class="text-sm sm:text-base md:text-xl font-bold mb-1.5 md:mb-2 leading-tight mobile-card-title">
                                        {{ $roomDetail['name'] }}
                                    </h3>

                                    {{-- Price Range Display --}}
                                    @php
                                        $priceRange = $roomDetail['price_range'] ?? ['min_price' => 0, 'max_price' => 0, 'min_label' => '—', 'max_label' => '—'];
                                        $roomNameLower = strtolower($roomDetail['name']);
                                        $isBYOT = str_contains($roomNameLower, 'byot') ||
                                                 str_contains($roomNameLower, 'bring your own tent') ||
                                                 str_contains($roomNameLower, 'bring your own');
                                        $isCarCamping = !$isBYOT && str_contains($roomNameLower, 'car camping');
                                    @endphp

                                    <div class="text-sm sm:text-base md:text-lg font-bold text-[#8f5d54] mb-1.5 md:mb-2">
                                        @if($priceRange['min_price'] > 0 && $priceRange['max_price'] > 0)
                                            @if($priceRange['min_price'] == $priceRange['max_price'])
                                                {{ $priceRange['min_label'] }}
                                            @else
                                                {{ $priceRange['min_label'] }} - {{ $priceRange['max_label'] }}
                                            @endif
                                        @else
                                            ₱{{ number_format($roomDetail['price'], 0) }}
                                        @endif

                                        @if($isBYOT || $isCarCamping)
                                            {{-- no / night suffix --}}
                                        @else
                                            / night
                                        @endif
                                    </div>

                                    <p class="text-gray-600 mb-3 md:mb-4 text-xs sm:text-sm mobile-clamp-3">
                                        {{ $roomDetail['description'] }}
                                    </p>

                                    {{-- Guest controls if selected --}}
                                    <div x-show="isSelected({{ $staycation->id }})" x-transition class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        {{-- BYOT/Car Camping Quantity --}}
                                        <template x-if="getSelectedRoom({{ $staycation->id }}) && (getSelectedRoom({{ $staycation->id }}).name.toLowerCase().includes('byot') || getSelectedRoom({{ $staycation->id }}).name.toLowerCase().includes('bring your own tent'))">
                                            <div class="mb-3">
                                                <label :for="'byot-qty-card-' + {{ $staycation->id }}" class="block text-sm font-semibold text-gray-700 mb-1">BYOT Quantity</label>
                                                <input type="number" min="1"
                                                       :id="'byot-qty-card-' + {{ $staycation->id }}" :name="'byot-qty-card-' + {{ $staycation->id }}"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                       x-model.number="getSelectedRoom({{ $staycation->id }}).extra_mattress"
                                                       @change="validateQuantity({{ $staycation->id }}, 'extra_mattress', getSelectedRoom({{ $staycation->id }}).extra_mattress, roomDetailsMap[{{ $staycation->id }}]?.available_quantity || 8)"
                                                       @click.stop
                                                >
                                                <span class="text-xs text-gray-500 mt-1 block" x-text="'Available: ' + (roomDetailsMap[{{ $staycation->id }}]?.available_quantity || 8)"></span>
                                            </div>
                                        </template>
                                        <template x-if="getSelectedRoom({{ $staycation->id }}) && getSelectedRoom({{ $staycation->id }}).name.toLowerCase().includes('car camping')">
                                            <div class="mb-3">
                                                <label :for="'car-qty-card-' + {{ $staycation->id }}" class="block text-sm font-semibold text-gray-700 mb-1">Number of Vehicles</label>
                                                <input type="number" min="1"
                                                       :id="'car-qty-card-' + {{ $staycation->id }}" :name="'car-qty-card-' + {{ $staycation->id }}"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                       x-model.number="getSelectedRoom({{ $staycation->id }}).extra_mattress"
                                                       @change="validateQuantity({{ $staycation->id }}, 'extra_mattress', getSelectedRoom({{ $staycation->id }}).extra_mattress, roomDetailsMap[{{ $staycation->id }}]?.available_quantity || 8)"
                                                       @click.stop
                                                >
                                                <span class="text-xs text-gray-500 mt-1 block" x-text="'Available: ' + (roomDetailsMap[{{ $staycation->id }}]?.available_quantity || 8)"></span>
                                            </div>
                                        </template>

                                        {{-- Regular Rooms: Adults/Children --}}
                                        <template x-if="getSelectedRoom({{ $staycation->id }}) && !getSelectedRoom({{ $staycation->id }}).name.toLowerCase().includes('byot') && !getSelectedRoom({{ $staycation->id }}).name.toLowerCase().includes('bring your own tent') && !getSelectedRoom({{ $staycation->id }}).name.toLowerCase().includes('car camping')">
                                            <div class="mb-3">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <label :for="'adults-card-' + {{ $staycation->id }}" class="block text-sm font-semibold text-gray-700 mb-1">Adults</label>
                                                        <input type="number" min="1" max="20"
                                                               :id="'adults-card-' + {{ $staycation->id }}" :name="'adults-card-' + {{ $staycation->id }}"
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                               x-model.number="getSelectedRoom({{ $staycation->id }}).adults"
                                                               @change="validateAndSetGuests({{ $staycation->id }}, 'adults', getSelectedRoom({{ $staycation->id }}).adults, getSelectedRoom({{ $staycation->id }}).name.toLowerCase())"
                                                               @click.stop
                                                        >
                                                    </div>
                                                    <div>
                                                        <label :for="'children-card-' + {{ $staycation->id }}" class="block text-sm font-semibold text-gray-700 mb-1">Children</label>
                                                        <input type="number" min="0" max="20"
                                                               :id="'children-card-' + {{ $staycation->id }}" :name="'children-card-' + {{ $staycation->id }}"
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                               x-model.number="getSelectedRoom({{ $staycation->id }}).children"
                                                               @change="validateAndSetGuests({{ $staycation->id }}, 'children', getSelectedRoom({{ $staycation->id }}).children, getSelectedRoom({{ $staycation->id }}).name.toLowerCase())"
                                                               @click.stop
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Pets (for all room types) --}}
                                        <div class="mb-3">
                                            <span class="block text-sm font-semibold text-gray-700 mb-2">Pets</span>
                                            <div class="flex gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" :name="'pets-card-' + {{ $staycation->id }}" value="no"
                                                           x-model="getSelectedRoom({{ $staycation->id }}).pets"
                                                           @change="setField({{ $staycation->id }}, 'pets', 'no')"
                                                           @click.stop
                                                           class="text-[#8f5d54] focus:ring-[#8f5d54]">
                                                    <span class="text-sm">No</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" :name="'pets-card-' + {{ $staycation->id }}" value="yes"
                                                           x-model="getSelectedRoom({{ $staycation->id }}).pets"
                                                           @change="setField({{ $staycation->id }}, 'pets', 'yes')"
                                                           @click.stop
                                                           class="text-[#8f5d54] focus:ring-[#8f5d54]">
                                                    <span class="text-sm">Yes</span>
                                                </label>
                                            </div>
                                            <div x-show="getSelectedRoom({{ $staycation->id }}).pets === 'yes'" class="mt-2">
                                                <label class="flex items-start gap-2 mb-2 cursor-pointer">
                                                    <input type="checkbox"
                                                           :id="'pet-agreement-card-' + {{ $staycation->id }}" :name="'pet_agreement_card_' + {{ $staycation->id }}"
                                                           x-model="getSelectedRoom({{ $staycation->id }}).pet_agreement"
                                                           @change="setField({{ $staycation->id }}, 'pet_agreement', getSelectedRoom({{ $staycation->id }}).pet_agreement)"
                                                           @click.stop
                                                           class="mt-0.5 text-[#8f5d54] focus:ring-[#8f5d54]">
                                                    <span class="text-xs text-gray-600">
                                                        I agree to the <button type="button" @click.stop="petPolicyModal = true" class="text-[#8f5d54] underline">Pet Policy</button>
                                                    </span>
                                                </label>
                                                <label :for="'pets-qty-card-' + {{ $staycation->id }}" class="sr-only">Number of pets</label>
                                                <input type="number" min="1" placeholder="Number of pets"
                                                       :id="'pets-qty-card-' + {{ $staycation->id }}" :name="'pets-qty-card-' + {{ $staycation->id }}"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:bg-gray-100 disabled:text-gray-400"
                                                       x-model.number="getSelectedRoom({{ $staycation->id }}).num_pets"
                                                       @change="setField({{ $staycation->id }}, 'num_pets', getSelectedRoom({{ $staycation->id }}).num_pets)"
                                                       @click.stop
                                                       :disabled="!getSelectedRoom({{ $staycation->id }}).pet_agreement">
                                                <div x-show="!getSelectedRoom({{ $staycation->id }}).pet_agreement" class="text-xs text-amber-600 mt-1">
                                                    <i class="bi bi-exclamation-circle mr-1"></i>Check agreement to enable pets count
                                                </div>
                                            </div>
                                        </div>                    
                                    </div>

                                    <div class="mt-auto">
                                        {{-- Get features for modal --}}
                                        @php
                                            $featuresText = $staycation->texts->firstWhere('section', 'features');
                                            $features = $featuresText ? explode("\n", $featuresText->content) : [];
                                        @endphp

                                        {{-- View Room Details Button --}}
                                        <button
                                            type="button"
                                            @click.stop="showRoomDetailsModal(
                                                {{ $staycation->id }},
                                                '{{ addslashes($roomDetail['name']) }}',
                                                '{{ addslashes($roomDetail['description']) }}',
                                                {{ json_encode(array_values(array_filter(array_map('trim', $features)))) }},
                                                {{ json_encode($staycation->swiperImages ? $staycation->swiperImages->map(function($img) { return $img->image_url; })->toArray() : []) }}
                                            )"
                                            class="w-full bg-gray-50 text-gray-700 py-2 px-3 sm:px-4 rounded-lg font-semibold hover:bg-gray-100 transition text-xs sm:text-sm leading-tight mb-3 md:mb-4 border border-gray-200"
                                        >
                                            <i class="bi bi-info-circle mr-1"></i> View Room Details
                                        </button>

                                        {{-- Availability info --}}
                                        @if($status === 'limited')
                                            <div class="text-xs sm:text-sm text-amber-700 mb-3 md:mb-4 bg-amber-50 p-2.5 sm:p-3 rounded-lg border border-amber-200">
                                                <i class="bi bi-exclamation-triangle mr-1"></i>
                                                Only {{ $avail['available_nights'] }}/{{ $avail['total_nights'] }} nights available
                                            </div>
                                        @elseif($status === 'available')
                                            <div class="text-xs sm:text-sm text-green-700 mb-3 md:mb-4 bg-green-50 p-2.5 sm:p-3 rounded-lg border border-green-200">
                                                <i class="bi bi-check-circle mr-1"></i>
                                                Fully available for your dates
                                            </div>
                                        @endif

                                        {{-- Buttons --}}
                                        <div class="room-actions">
                                            <button
                                                type="button"
                                                @click.stop="showCalendar('{{ $roomDetail['type'] }}', {{ $staycation->id }})"
                                                class="w-full bg-gray-100 text-gray-700 py-2 px-2 sm:px-4 rounded-lg font-semibold hover:bg-gray-200 transition text-xs sm:text-sm leading-tight"
                                            >
                                                <i class="bi bi-calendar mr-1"></i>Calendar
                                            </button>

                                        @php
                                            $isBYOTButton = str_contains(strtolower($roomDetail['name']), 'byot') ||
                                                     str_contains(strtolower($roomDetail['name']), 'bring your own tent');
                                            $isCarCampingButton = str_contains(strtolower($roomDetail['name']), 'car camping');
                                            $isSpecialRoomButton = $isBYOTButton || $isCarCampingButton;
                                        @endphp
                                            <button
                                                type="button"
                                                class="w-full py-2 px-2 sm:px-4 rounded-lg font-semibold transition text-xs sm:text-sm leading-tight"
                                                @click.stop="toggleRoom({{ $staycation->id }}, '{{ $roomDetail['type'] }}')"
                                                :disabled="{{ $status !== 'available' ? 'true' : 'false' }} || (!isSelected({{ $staycation->id }}) && getRemainingSlots({{ $staycation->id }}) === 0)"
                                                :class="isSelected({{ $staycation->id }})
                                                    ? 'bg-green-600 text-white hover:bg-green-700'
                                                    : (getRemainingSlots({{ $staycation->id }}) === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : '{{ $status === 'available' ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}')"
    >
                                                <template x-if="isSelected({{ $staycation->id }})">
                                                    <span>
                                                        <span class="sm:hidden"><i class="bi bi-check mr-1"></i>Added (<span x-text="getSelectedCount({{ $staycation->id }})"></span>)</span>
                                                        <span class="hidden sm:inline"><i class="bi bi-check mr-1"></i> Added (<span x-text="getSelectedCount({{ $staycation->id }})"></span>) - <span x-text="getRemainingSlots({{ $staycation->id }})"></span> left</span>
                                                    </span>
                                                </template>
                                                <template x-if="!isSelected({{ $staycation->id }})">
                                                    <span x-text="getRemainingSlots({{ $staycation->id }}) === 0 ? 'Fully Booked' : 'Select'"></span>
                                                </template>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Edit hint --}}
                                    <div x-show="isSelected({{ $staycation->id }})" class="mt-3 text-xs text-gray-500 text-center">
                                        <i class="bi bi-info-circle mr-1"></i>
                                        @if($isSpecialRoomButton)
                                            Click "Add More" to increase quantity
                                        @else
                                            Click room in summary to edit details
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12 bg-white rounded-2xl shadow border border-gray-200">
                                <div class="text-gray-500">
                                    <i class="bi bi-calendar-x text-6xl mb-4 block text-gray-300"></i>
                                    <h3 class="text-2xl font-semibold mb-2 text-gray-700">No Staycations Available</h3>
                                    <p class="text-gray-500 mb-4">No staycations are configured in the system.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="lg:col-span-1 order-1 lg:order-2">
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-xl font-bold mb-6">Booking Summary</h3>

                        {{-- Dates --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-calendar-check mr-2 text-[#8f5d54]"></i> Selected Dates
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                @php
                                    $selCheckin = request('checkin') ?? ($checkin ?? null);
                                    $selCheckout = request('checkout') ?? ($checkout ?? null);
                                @endphp
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Check-in</span>
                                    <span class="font-semibold">
                                        @if ($selCheckin)
                                            {{ \Carbon\Carbon::parse($selCheckin)->format('M j, Y') }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Check-out</span>
                                    <span class="font-semibold">
                                        @if ($selCheckout)
                                            {{ \Carbon\Carbon::parse($selCheckout)->format('M j, Y') }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-t pt-3">
                                    <span class="text-sm text-gray-600">Total Nights</span>
                                    <span class="font-semibold text-[#8f5d54]">
                                        @if ($selCheckin && $selCheckout)
                                            {{ \Carbon\Carbon::parse($selCheckin)->diffInDays(\Carbon\Carbon::parse($selCheckout)) }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Selected rooms list --}}
                        <div class="mb-6" x-effect="$watch('selectedRooms', () => {}, { deep: true })">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800 flex items-center">
                                    <i class="bi bi-house mr-2 text-[#8f5d54]"></i> Selected Rooms
                                </h4>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full"
                                      :class="selectedCount() >= maxSlots ? 'bg-green-100 text-green-700' : 'bg-[#8f5d54]/10 text-[#8f5d54]'"
                                      x-text="selectedCount() + '/' + maxSlots">
                                </span>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <template x-if="selectedCount() === 0">
                                    <div class="text-sm text-gray-600">
                                        <p>No rooms selected yet.</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Target: <span x-text="targetRooms"></span> rooms
                                        </p>
                                    </div>
                                </template>

                                <template x-for="item in Object.values(selectedRooms)" :key="item.id">
                                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                        {{-- Room Header --}}
                                         <div class="p-3 bg-gray-50">
                                             <div class="flex items-start justify-between gap-3">
                                                 <div class="flex-1">
                                                     <div class="flex items-start justify-between w-full gap-2">
                                                        <!-- Room Name -->
                                                        <div class="font-semibold text-gray-900 text-sm leading-tight flex-1"
                                                            x-text="item.name">
                                                        </div>

                                                        <!-- Trash Icon -->
                                                        <button type="button"
                                                                class="text-red-600 hover:text-red-800 text-xs shrink-0 mt-0.5"
                                                                @click.stop="removeRoom(item.id)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                     
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        <template x-if="item.name.toLowerCase().includes('byot') || item.name.toLowerCase().includes('bring your own tent')">
                                                            <span>BYOT Qty: <span class="font-semibold" x-text="item.extra_mattress || 1"></span></span>
                                                        </template>
                                                        <template x-if="item.name.toLowerCase().includes('car camping')">
                                                            <span>Vehicles: <span class="font-semibold" x-text="item.extra_mattress || 1"></span></span>
                                                        </template>
                                                        <template x-if="!item.name.toLowerCase().includes('byot') && !item.name.toLowerCase().includes('bring your own tent') && !item.name.toLowerCase().includes('car camping')">
                                                            <span>Adults: <span class="font-semibold" x-text="item.adults"></span>, Children: <span class="font-semibold" x-text="item.children"></span></span>
                                                        </template>
                                                    </div>
                                                     <div class="text-xs text-green-600 font-semibold mt-1" x-text="calculateRoomPrice(item)"></div>

                                                     {{-- Price Breakdown --}}
                                                     <div class="mt-2 text-xs bg-white rounded p-2 border border-gray-200">
                                                         <div class="flex justify-between mb-1">
                                                             <span class="text-gray-600">Accommodation:</span>
                                                             <span class="font-semibold" x-text="getPriceRange(item)"></span>
                                                         </div>
                                                         <div x-show="item.pets === 'yes' && item.num_pets > 0" class="flex justify-between mb-1">
                                                             <span class="text-gray-600">Pets:</span>
                                                             <span class="text-gray-600font-semibold" x-text="'₱' + calculatePetPrice(item).toLocaleString()"></span>
                                                         </div>
                                                          <div class="border-t border-gray-300 pt-1 flex justify-between font-semibold">
                                                             <span>Total:</span>
                                                             <span class="text-green-600" x-text="calculateRoomPrice(item)"></span>
                                                         </div>
                                                     </div>
                                                 </div>
                                        </div>
                                    </div>
                                </template>                  
                            </div>
                        </div>

                        {{-- Total Price with VAT --}}
                        <div class="mb-6" x-show="selectedCount() > 0">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-cash mr-2 text-[#8f5d54]"></i> Estimated Total
                            </h4>

                            <div class="bg-green-50 rounded-lg p-4 border border-green-200 space-y-2">
                                <!--<div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-semibold" x-text="calculateSubtotal()"></span>
                                </div>-->

                                <!--<div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>
                                        <i class="bi bi-info-circle text-gray-400 text-xs" title="Value Added Tax as per Philippine TRAIN Law"></i>
                                        <span x-text="`${vatRatePercentage}% VAT:`"></span>
                                    </span>
                                    <span x-text="calculateVAT()"></span>
                                </div>-->

                                <div class="pt-2 flex justify-between items-center">
                                    <span class="font-semibold text-gray-800">Grand Total:</span>
                                    <span class="text-2xl font-bold text-green-600" x-text="calculateTotalPrice()"></span>
                                </div>

                                <div class="text-xs text-gray-500 mt-1">Final price with VAT (calculated at checkout)</div>

                                <!--<button type="button"
                                        x-show="hasRegularRooms()"
                                        @click="showPriceBreakdownModal = true"
                                        class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                                    <i class="bi bi-eye text-base"></i>
                                    <span>View Price Breakdown</span>
                                </button>-->

                                <div x-show="!hasRegularRooms()" class="mt-2 text-sm text-gray-500">
                                    <i class="bi bi-info-circle mr-1"></i>
                                    <span>Per slot pricing - no nightly breakdown</span>
                                </div>
                            </div>
                         </div>


                        {{-- Buttons --}}
                        <div class="space-y-3">
                            <button
                                type="button"
                                @click="submitAndProceed('{{ route('booking.staycationFood') }}')"
                                class="w-full bg-[#8f5d54] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#7a4e48] transition disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="selectedCount() === 0"
                            >
                                Proceed to Food <i class="bi bi-arrow-right ml-2"></i>
                            </button>

                            <button type="button"
                                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 transition"
                                    @click="submitAndProceed('{{ route('booking.staycationPayment') }}')">
                                <i class="bi bi-credit-card mr-2"></i>
                                Proceed to Payment
                            </button>

                            <button
                                type="button"
                                class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-semibold hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="selectedCount() === 0"
                            >
                                Clear Selection
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Calendar Modal --}}
                <div
                    x-show="calendarModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="calendarModal = false"
                >
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold" x-text="`Availability Calendar - ${currentRoomName}`"></h3>
                            <button type="button" @click="calendarModal = false" class="text-gray-500 hover:text-gray-800">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="mx-auto" style="max-width:400px;">
                            <input x-ref="calendar" id="availability-calendar" type="text" class="w-full border rounded-lg px-3 py-2" readonly />
                        </div>
                    </div>
                </div>

                {{-- Pet Policy Modal --}}
                <div x-show="petPolicyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                     @click.self="petPolicyModal=false">
                    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl p-6 overflow-y-auto" style="max-height: 90vh;">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Pet Policy</h3>
                            <button type="button" class="text-gray-500 hover:text-gray-800" @click="petPolicyModal=false">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="text-sm text-gray-700 space-y-3">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold">Owner Responsibility</div>
                                <div>Pet owners are responsible at all times. Pets must not be left unattended.</div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold">Leash Policy</div>
                                <div>Pets may be off-leash only under close and careful supervision.</div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold">Overnight Stays & Pet Fee</div>
                                <div>₱2,000 per pet fee applies when staying inside. Any damages charged accordingly.</div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="font-semibold text-red-900">Damage & Liability</div>
                                <div class="text-red-800">Owners will be held liable for any damage/injury caused by pets.</div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold" @click="petPolicyModal=false">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Image Gallery Modal --}}
                <div 
                    x-show="imageGalleryModal" 
                    x-cloak 
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                    @click.self="imageGalleryModal = false"
                    @keydown.escape.window="imageGalleryModal = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="w-full max-w-3xl" @click.stop>
                        {{-- Close Button --}}
                        <button
                            type="button"
                            class="absolute top-4 right-4 w-12 h-12 backdrop-blur rounded-full flex items-center justify-center text-white transition z-10"
                            @click="imageGalleryModal = false"
                        >
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>

                        {{-- Image Carousel --}}
                        <div x-show="imageGalleryImages && imageGalleryImages.length > 0" class="relative">
                            <div class="swiper image-gallery-swiper" x-ref="imageGallerySwiper">
                                <div class="swiper-wrapper">
                                    <template x-for="(img, index) in imageGalleryImages" :key="index">
                                        <div class="swiper-slide">
                                            <img :src="img" :alt="imageGalleryTitle" class="w-full h-auto object-contain rounded-xl" style="max-height: 80vh;">
                                        </div>
                                    </template>
                                </div>
                                <div class="swiper-pagination image-gallery-swiper-pagination"></div>
                                <div class="swiper-button-next image-gallery-swiper-next"></div>
                                <div class="swiper-button-prev image-gallery-swiper-prev"></div>
                            </div>

                            {{-- Image Counter --}}
                            <div class="absolute bottom-6 left-6 bg-black/50 backdrop-blur text-white px-4 py-2 rounded-full text-sm font-semibold">
                                <span class="swiper-pagination-current"></span> / <span class="swiper-pagination-total"></span>
                            </div>

                            {{-- Room Title --}}
                            <div class="absolute top-6 left-6 z-10">
                                <h3 class="text-2xl font-bold text-white drop-shadow-lg" x-text="imageGalleryTitle"></h3>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-3 justify-center mt-6">
                            <button 
                                type="button" 
                                class="px-8 py-3 bg-white text-gray-900 hover:bg-gray-100 rounded-xl font-semibold transition flex items-center gap-2 shadow-lg"
                                @click="imageGalleryModal = false"
                            >
                                <i class="bi bi-x-lg"></i>
                                Close
                            </button>
                            <button 
                                type="button" 
                                class="px-8 py-3 bg-[#8f5d54] text-white hover:bg-[#7a4e48] rounded-xl font-semibold transition flex items-center gap-2 shadow-lg"
                                @click="closeGalleryAndShowDetails()"
                            >
                                <i class="bi bi-info-circle"></i>
                                View Full Details
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Room Details Modal --}}
                <div 
                    x-show="roomDetailsModal" 
                    x-cloak 
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                    @click.self="roomDetailsModal = false"
                    @keydown.escape.window="roomDetailsModal = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div
                        class="bg-white w-full max-w-sm sm:max-w-md md:max-w-lg lg:max-w-2xl xl:max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                        style="max-height: 92vh;"
                        @click.stop
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    >
                        {{-- Header with Image Carousel --}}
                        <div class="relative">
                            {{-- Image Carousel --}}
                            <div x-show="roomDetailsImages && roomDetailsImages.length > 0" class="relative">
                                <div class="swiper room-details-swiper" x-ref="roomDetailsSwiper">
                                    <div class="swiper-wrapper">
                                        <template x-for="(img, index) in roomDetailsImages" :key="index">
                                            <div class="swiper-slide">
                                                <img :src="img" alt="Room Image" class="w-full h-45 sm:h-55 md:h-65 object-cover">
                                            </div>
                                        </template>
                                    </div>
                                    <div class="swiper-pagination room-details-swiper-pagination"></div>
                                    <div class="swiper-button-next room-details-swiper-next"></div>
                                    <div class="swiper-button-prev room-details-swiper-prev"></div>
                                </div>
                            </div>

                            {{-- Close Button --}}
                            <button
                                type="button"
                                class="absolute top-4 right-4 w-10 h-10 backdrop-blur rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:text-gray-900 transition shadow-lg z-10"
                                @click="roomDetailsModal = false"
                            >
                                <i class="bi bi-x-lg text-lg"></i>
                            </button>

                            {{-- Room Type Badge --}}
                            <div class="absolute bottom-4 left-4 z-10">
                                <span class="px-4 py-1.5 bg-[#8f5d54] text-white text-sm font-semibold rounded-full shadow-lg" x-text="roomDetailsType"></span>
                            </div>

                            {{-- Status Badge --}}
                            <div class="absolute bottom-4 right-4 z-10">
                                <span 
                                    class="px-3 py-1 rounded-full text-xs font-bold text-white shadow-lg"
                                    :class="roomDetailsStatus === 'available' ? 'bg-green-500' : (roomDetailsStatus === 'limited' ? 'bg-yellow-500' : 'bg-gray-500')"
                                >
                                    <template x-if="roomDetailsStatus === 'available'">
                                        <span><i class="bi bi-check-circle-fill mr-1"></i>Available</span>
                                    </template>
                                    <template x-if="roomDetailsStatus === 'limited'">
                                        <span><i class="bi bi-exclamation-triangle-fill mr-1"></i>Limited</span>
                                    </template>
                                    <template x-if="roomDetailsStatus === 'unavailable'">
                                        <span><i class="bi bi-x-circle-fill mr-1"></i>Unavailable</span>
                                    </template>
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="px-4 sm:px-5 py-4 sm:py-5 overflow-y-auto flex-1">
                            {{-- Room Name & Price --}}
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                                <div class="min-w-0">
                                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight" x-text="roomDetailsName"></h3>

                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span x-show="roomDetailsSize" class="inline-flex items-center gap-1 text-xs sm:text-sm text-gray-600 bg-gray-100 rounded-full px-3 py-1">
                                            <i class="bi bi-arrows-angle-expand text-[#8f5d54]"></i>
                                            <span x-text="roomDetailsSize"></span>
                                        </span>

                                        <span x-show="roomDetailsCapacity" class="inline-flex items-center gap-1 text-xs sm:text-sm text-gray-600 bg-gray-100 rounded-full px-3 py-1">
                                            <i class="bi bi-people text-[#8f5d54]"></i>
                                            <span x-text="roomDetailsCapacity"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="sm:text-right shrink-0">
                                    <div class="text-xl sm:text-2xl font-bold text-[#8f5d54]" x-text="roomDetailsPrice"></div>
                                    <div class="text-xs text-gray-500" x-text="roomDetailsPriceLabel"></div>
                                </div>
                            </div>

                            {{-- Quick Stats --}}
                            <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-6">
                                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                                    <div class="text-base sm:text-lg font-bold text-gray-900" x-text="roomDetailsAvailable"></div>
                                    <div class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mt-1">Available</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                                    <div class="text-base sm:text-lg font-bold text-gray-900" x-text="roomDetailsNights"></div>
                                    <div class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mt-1">Nights</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                                    <div class="text-base sm:text-lg font-bold text-gray-900" x-text="roomDetailsGuests"></div>
                                    <div class="text-[9px] sm:text-xs text-gray-500 uppercase tracking-wide mt-1">Guests</div>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-5">
                                <h4 class="font-semibold text-sm text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="bi bi-card-text text-[#8f5d54]"></i>
                                    Description
                                </h4>
                                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed bg-gray-50 rounded-lg p-3 border border-gray-100" x-text="roomDetailsDescription"></p>
                            </div>

                            {{-- Amenities / Features --}}
                            <template x-if="roomDetailsFeatures && roomDetailsFeatures.length > 0">
                                <div>
                                    <h4 class="font-semibold text-sm text-gray-800 mb-3 flex items-center gap-2">
                                        <i class="bi bi-grid text-[#8f5d54]"></i>
                                        Room Amenities
                                    </h4>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                        <template x-for="feature in roomDetailsFeatures" :key="feature">
                                            <div class="flex items-start gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-100 hover:border-[#8f5d54]/30 transition">
                                                <div class="w-5 h-5 bg-[#8f5d54]/10 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                                                    <i class="bi bi-check-lg text-[#8f5d54] text-xs"></i>
                                                </div>
                                                <span class="text-xs text-gray-700 leading-snug" x-text="feature"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Additional Info --}}
                            <div class="mt-5 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-start gap-2">
                                    <i class="bi bi-info-circle text-blue-500 mt-0.5 text-sm shrink-0"></i>
                                    <div class="text-xs text-blue-700">
                                        <p class="font-semibold">Booking Info</p>
                                        <p class="mt-1">Prices vary by season and day. Select to see final pricing.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row gap-2 sm:gap-3">
                            <button 
                                type="button" 
                                class="flex-1 px-4 sm:px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-semibold transition flex items-center justify-center gap-2 text-sm sm:text-base min-h-12"
                                @click="roomDetailsModal = false"
                            >
                                <i class="bi bi-x-lg"></i>
                                <span>Close</span>
                            </button>
                            <button 
                                type="button" 
                                class="flex-1 px-4 sm:px-6 py-3 bg-[#8f5d54] text-white hover:bg-[#7a4e48] rounded-xl font-semibold transition flex items-center justify-center gap-2 text-sm sm:text-base min-h-12"
                                @click="selectRoomFromModal()"
                            >
                                <i class="bi bi-calendar-check"></i>
                                <span>Select This Room</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Quantity Selector Modal --}}
                <div x-show="showQuantityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                     @click.self="showQuantityModal=false">
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Select Quantity</h3>
                            <button type="button" class="text-gray-500 hover:text-gray-800" @click="showQuantityModal=false">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="mb-4">
                           
                            <p class="text-sm text-gray-500">Available: <span x-text="quantityModalAvailable" class="font-semibold text-[#8f5d54]"></span></p>
                        </div>
                        <div class="mb-6">
                            <label for="target-rooms-input" class="block text-sm font-medium text-gray-700 mb-2">How many rooms?</label>
                            <div class="flex items-center gap-3">
                                <button type="button" class="w-10 h-10 rounded-full border border-gray-300 hover:border-[#8f5d54] flex items-center justify-center"
                                        @click="quantityModalValue = Math.max(1, quantityModalValue - 1)"
                                        aria-label="Decrease room count">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number" id="target-rooms-input" name="target_rooms"
                                       class="text-xl font-bold w-12 text-center border border-gray-300 rounded-lg py-1"
                                       x-model.number="quantityModalValue" min="1" max="4" readonly>
                                <button type="button" class="w-10 h-10 rounded-full border border-gray-300 hover:border-[#8f5d54] flex items-center justify-center"
                                        @click="quantityModalValue = Math.min(quantityModalMax, quantityModalValue + 1)"
                                        aria-label="Increase room count">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold" @click="showQuantityModal=false">
                                Cancel
                            </button>
                            <button type="button" class="flex-1 px-4 py-2 bg-[#8f5d54] text-white hover:bg-[#7a4e48] rounded-lg font-semibold"
                                    @click="confirmQuantitySelection()">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Price Breakdown Modal --}}
                <div x-show="showPriceBreakdownModal"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4 overflow-y-auto"
                     @click.self="showPriceBreakdownModal = false">

                    <div class="w-full max-w-3xl bg-white rounded-xl shadow-2xl overflow-hidden mt-6">
                        <div class="px-6 py-4 border-b flex items-start justify-between">
                            <div>
                                <div class="text-xl font-bold text-gray-900">Calculation Result</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    <span x-text="currentBreakdownTitle()"></span>
                                </div>
                            </div>

                            <button type="button"
                                    class="text-gray-400 hover:text-gray-700 transition"
                                    @click="showPriceBreakdownModal = false">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-6">
                            <div class="rounded-lg border border-green-200 bg-green-50 p-6">
                                <div class="text-4xl font-extrabold text-green-600" x-text="calculateBreakdownTotal()"></div>
                                <div class="text-sm text-gray-700 mt-2">Total Amount</div>
                            </div>

                            <div class="text-lg font-bold text-gray-900">Daily Breakdown</div>

                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm min-w-150">
                                        <thead class="bg-gray-50 text-gray-700">
                                        <tr>
                                            <th class="text-left px-4 py-3 font-semibold">Room</th>
                                            <th class="text-left px-4 py-3 font-semibold">Date</th>
                                            <th class="text-left px-4 py-3 font-semibold">Day</th>
                                            <th class="text-left px-4 py-3 font-semibold">Season</th>
                                            <th class="text-left px-4 py-3 font-semibold">Day Type</th>
                                            <th class="text-right px-4 py-3 font-semibold">Rate</th>
                                        </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-100">
                                        <template x-for="(row, idx) in getAllRoomsBreakdown()" :key="idx">
                                            <tr class="bg-white">
                                                <td class="px-4 py-3 text-gray-800 font-medium" x-text="row.roomName || 'Room'"></td>
                                                <td class="px-4 py-3 text-gray-800" x-text="row.dateRaw"></td>
                                                <td class="px-4 py-3 text-gray-600" x-text="row.dayLong"></td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold"
                                                          :class="row.seasonType === 'Super-Peak'
                                                            ? 'bg-red-200 text-red-800'
                                                            : (row.seasonType === 'Peak'
                                                                ? 'bg-yellow-200 text-yellow-800'
                                                                : 'bg-green-200 text-green-800')"
                                                          x-text="row.season">
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold"
                                                          :class="row.dayType === 'Weekend'
                                                            ? 'bg-purple-200 text-purple-800'
                                                            : (row.dayType === 'Holiday'
                                                                ? 'bg-blue-200 text-blue-800'
                                                                : 'bg-gray-200 text-gray-800')"
                                                          x-text="row.dayType">
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right font-semibold text-green-600"
                                                    x-text="'₱' + row.rate.toLocaleString()"></td>
                                            </tr>
                                        </template>

                                        <tr class="bg-green-50 border-t border-green-200">
                                            <td colspan="5" class="px-4 py-4 text-right font-semibold text-gray-800">Total:</td>
                                            <td class="px-4 py-4 text-right font-bold text-green-600"
                                                x-text="calculateBreakdownTotal()">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="button"
                                        class="px-6 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 font-semibold transition"
                                        @click="showPriceBreakdownModal = false">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- end grid --}}
        </div>
    </div>

    <script>
        function staycationAvailabilityMerged(targetRooms = 1, targetAdults = 1, targetChildren = 0, vatRate = 0.0, vatRatePercentage = 0) {
            return {
                roomType: 'all',

                targetRooms: targetRooms,
                targetAdults: targetAdults,
                targetChildren: targetChildren,

                maxSlots: Math.min(Math.max(targetRooms, 1), 4),

                // Dynamic VAT rate from admin settings
                vatRate: vatRate,
                vatRatePercentage: vatRatePercentage,

                addRoomSlot() {
                    if (this.maxSlots >= 4) {
                        Swal.fire({
                            title: 'Maximum Limit Reached',
                            text: 'You cannot add more than 4 rooms.',
                            icon: 'warning',
                            confirmButtonColor: '#8f5d54'
                        });
                        return;
                    }
                    this.maxSlots++;
                },

                // ========== HELPER FUNCTIONS FOR MULTI-INSTANCE SUPPORT ==========
                getRoomAvailableQty(roomId) {
                    const detail = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    let qty = detail?.available_quantity;
                    if (qty === undefined || qty === null) qty = detail?.total_quantity;
                    qty = parseInt(qty || 0, 10);
                    return isFinite(qty) ? qty : 0;
                },

                countRoomInstances(roomId) {
                    return this.getSelectedRoomInstances(roomId).length;
                },

                getSelectedCount(roomId) {
                    // For BYOT / Car Camping rooms we sum the extra_mattress quantities,
                    // otherwise return the number of instances selected for this room.
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    const roomNameLower = (roomDetail?.name || '').toLowerCase();
                    const isBYOTorCar = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent') || roomNameLower.includes('car camping');

                    if (isBYOTorCar) {
                        const instances = this.getSelectedRoomInstances(roomId);
                        let total = 0;
                        for (const key of instances) {
                            total += parseInt(this.selectedRooms[key]?.extra_mattress || 1, 10);
                        }
                        return total;
                    }

                    return this.countRoomInstances(roomId);
                },

                getRemainingSlots(roomId) {
                    const availableQty = this.getRoomAvailableQty(roomId);
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    const roomNameLower = (roomDetail?.name || '').toLowerCase();
                    const isBYOTorCar = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent') || roomNameLower.includes('car camping');

                    if (isBYOTorCar) {
                        // For BYOT/Car Camping: count total quantity (extra_mattress) across all instances
                        const instances = this.getSelectedRoomInstances(roomId);
                        let totalQty = 0;
                        for (const key of instances) {
                            totalQty += parseInt(this.selectedRooms[key]?.extra_mattress || 1, 10);
                        }
                        return Math.max(0, availableQty - totalQty);
                    } else {
                        // Regular rooms: count instances
                        const selectedCount = this.countRoomInstances(roomId);
                        return Math.max(0, availableQty - selectedCount);
                    }
                },

                getNextInstanceNum(roomId) {
                    const keys = this.getSelectedRoomInstances(roomId);
                    if (keys.length === 0) return 1;

                    let max = 1;
                    for (const k of keys) {
                        const r = this.selectedRooms[k];
                        const n = parseInt(r?.instanceNum || 1, 10);
                        if (n > max) max = n;
                    }
                    return max + 1;
                },

                // Check if card should be disabled (unavailable status OR zero remaining slots)
                isCardDisabled(roomId) {
                    // Check PHP status from the data attribute or roomDetailsMap
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    const status = roomDetail ? roomDetail.status : 'available';
                    
                    // If status is unavailable, disable the card
                    if (status === 'unavailable') {
                        return true;
                    }
                    
                    // Check if remaining slots is 0 and not selected
                    const remainingSlots = this.getRemainingSlots(roomId);
                    const isSelected = this.isSelected(roomId);
                    
                    if (remainingSlots === 0 && !isSelected) {
                        return true;
                    }
                    return false;
                },

                // Handle card click - prevents clicks on disabled cards
                handleCardClick(event, roomId, type) {
                    if (this.isCardDisabled(roomId)) {
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }
                    this.toggleRoom(roomId, type);
                },
                // ========== END HELPER FUNCTIONS ==========

                checkin: '{{ $checkin }}',
                checkout: '{{ $checkout }}',

                roomDetailsMap: @json($roomDetails),

                bookingSettings: {
                    mattress_price_per_night: 800,
                    pet_price: 2000,
                },

                calendarModal: false,
                petPolicyModal: false,
                imageGalleryModal: false,
                imageGalleryImages: [],
                imageGalleryTitle: '',
                imageGalleryRoomId: null,
                imageGallerySwiperInstance: null,
                roomDetailsModal: false,
                roomDetailsName: '',
                roomDetailsId: null,
                roomDetailsDescription: '',
                roomDetailsTypeRaw: '',
                roomDetailsFeatures: [],
                roomDetailsType: '',
                roomDetailsStatus: 'available',
                roomDetailsCapacity: '',
                roomDetailsSize: '',
                roomDetailsPrice: '',
                roomDetailsPriceLabel: '/ night',
                roomDetailsAvailable: '',
                roomDetailsNights: '',
                roomDetailsGuests: '',
                roomDetailsImages: [],
                showPriceBreakdownModal: false,
                showQuantityModal: false,
                quantityModalRoomId: null,
                quantityModalRoomName: '',
                quantityModalAvailable: 0,
                quantityModalValue: 1,
                quantityModalMax: 1,
                editingRoom: null,
                currentRoomId: null,
                currentRoomName: '',
                calendarInstance: null,

                legendMaxQty: 0,
                legendLowThreshold: 0,

                selectedRooms: {},
                priceBreakdowns: {},

                // Double-click tracking (kept, pero single click now also adds instance)
                lastClickedRoom: null,
                lastClickTime: 0,

                // ✅ FIX: single click on already-selected should ADD another instance (not show "Room Already Selected")
                // For BYOT/Car Camping: increment quantity instead of creating new instance
                toggleRoom(roomId, type) {
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);
                    if (!room) return;

                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    const isSpecialRoom = isBYOT || isCarCamping;

                    // Check if room has 0 remaining slots and is not selected - prevent selection
                    const remainingSlots = this.getRemainingSlots(roomId);
                    const existingKeys = this.getSelectedRoomInstances(roomId);
                    const isAlreadySelected = existingKeys.length > 0;

                    if (remainingSlots === 0 && !isAlreadySelected) {
                        Swal.fire({
                            title: 'Fully Booked',
                            text: `${room.name} is currently fully booked and cannot be selected.`,
                            icon: 'error',
                            confirmButtonColor: '#8f5d54'
                        });
                        return;
                    }

                    // If already selected -> handle based on room type
                    if (isAlreadySelected) {
                        // For BYOT/Car Camping: increment quantity instead of adding new instance
                        if (isSpecialRoom) {
                            const firstKey = existingKeys[0];
                            const currentQty = parseInt(this.selectedRooms[firstKey]?.extra_mattress || 1, 10);
                            const availableQty = this.getRoomAvailableQty(roomId);

                            // Check if we can add more quantity
                            if (availableQty > 0 && currentQty >= availableQty) {
                                Swal.fire({
                                    title: 'Maximum Quantity Reached',
                                    text: `Only ${availableQty} ${isBYOT ? 'BYOT slots' : 'vehicle slots'} available for ${room.name}.`,
                                    icon: 'warning',
                                    confirmButtonColor: '#8f5d54'
                                });
                                return;
                            }

                            // Increment quantity
                            this.selectedRooms[firstKey].extra_mattress = currentQty + 1;

                            // Recalculate price with new quantity - use the existing room variable
                            if (room) {
                                this.selectedRooms[firstKey].accommodation_price = this.calculateBasePrice(room, this.selectedRooms[firstKey]);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: `${isBYOT ? 'BYOT Quantity' : 'Vehicle Count'} increased to ${currentQty + 1}`,
                                toast: true,
                                position: 'bottom-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                            return;
                        }

                        // For regular rooms: add another instance
                        // slot limit
                        if (this.selectedCount() >= this.maxSlots) {
                            Swal.fire({
                                title: 'Slots Full',
                                text: `All ${this.maxSlots} slot(s) are filled. Add more slots if needed.`,
                                icon: 'warning',
                                confirmButtonColor: '#8f5d54'
                            });
                            return;
                        }

                        // availability limit (only if backend provides qty)
                        const availableQty = this.getRoomAvailableQty(roomId);
                        const remainingSlots = this.getRemainingSlots(roomId);

                        if (availableQty > 0 && remainingSlots <= 0) {
                            Swal.fire({
                                title: 'No More Availability',
                                text: `Only ${availableQty} available for ${room.name} on your selected dates.`,
                                icon: 'warning',
                                confirmButtonColor: '#8f5d54'
                            });
                            return;
                        }

                        const nextInstance = this.getNextInstanceNum(roomId);
                        this.addRoomInstance(roomId, type, nextInstance);
                        return;
                    }

                    // first time select
                    if (this.selectedCount() >= this.maxSlots) {
                        Swal.fire({
                            title: 'Slots Full',
                            text: `You can only select up to ${this.maxSlots} room(s). Add more slots if needed.`,
                            icon: 'warning',
                            confirmButtonColor: '#8f5d54'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: `${room.name || 'Room'} selected`,
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });

                    const defaultAdults = 1;
                    // roomNameLower, isBYOT, isCarCamping already declared at the top of the function

                    const selectedRoomData = {
                        id: roomId,
                        name: room.name || 'Room',
                        type: (type || '').toLowerCase(),
                        adults: defaultAdults,
                        children: 0,
                        extra_mattress: (isBYOT || isCarCamping) ? 1 : 0,
                        pets: 'no',
                        pet_agreement: false,
                        num_pets: 0,
                    };

                    selectedRoomData.accommodation_price = this.calculateBasePrice(room, selectedRoomData);
                    this.selectedRooms[roomId] = selectedRoomData;
                    
                    // Auto-expand guest input when room is selected
                    this.editingRoom = roomId;
                },

                addRoomInstance(roomId, type, instanceNum) {
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);
                    if (!room) return;

                    const compositeKey = instanceNum === 1 ? String(roomId) : `${roomId}_${instanceNum}`;
                    if (this.selectedRooms[compositeKey]) {
                        return this.addRoomInstance(roomId, type, instanceNum + 1);
                    }

                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');

                    const selectedRoomData = {
                        id: compositeKey,
                        originalId: roomId,
                        instanceNum: instanceNum,
                        name: room.name || 'Room',
                        type: (type || '').toLowerCase(),
                        adults: 1,
                        children: 0,
                        extra_mattress: (isBYOT || isCarCamping) ? 1 : 0,
                        pets: 'no',
                        pet_agreement: false,
                        num_pets: 0,
                    };

                    selectedRoomData.accommodation_price = this.calculateBasePrice(room, selectedRoomData);
                    this.selectedRooms[compositeKey] = selectedRoomData;
                    
                    // Auto-expand guest input when room instance is added
                    this.editingRoom = compositeKey;

                    Swal.fire({
                        icon: 'success',
                        title: `${room.name} #${instanceNum} added`,
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                },

                removeRoom(roomId) {
                    const key = String(roomId);
                    if (!this.selectedRooms[key]) return;

                    const roomName = this.selectedRooms[key].name || 'Room';
                    const instanceNum = this.selectedRooms[key].instanceNum || 1;
                    const displayName = instanceNum > 1 ? `${roomName} #${instanceNum}` : roomName;

                    delete this.selectedRooms[key];

                    Swal.fire({
                        icon: 'info',
                        title: `${displayName} removed`,
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                },

                isSelected(roomId) {
                    const originalId = String(roomId);
                    return Object.keys(this.selectedRooms).some(key => {
                        const r = this.selectedRooms[key];
                        return (r.originalId && String(r.originalId) === originalId) || (key === originalId);
                    });
                },

                getSelectedRoomInstances(roomId) {
                    const originalId = String(roomId);
                    return Object.keys(this.selectedRooms).filter(key => {
                        const r = this.selectedRooms[key];
                        return (r.originalId && String(r.originalId) === originalId) || (key === originalId && !r.originalId);
                    });
                },

                getSelectedRoom(roomId) {
                    const instances = this.getSelectedRoomInstances(roomId);
                    if (instances.length === 0) return { adults: 1, children: 0, pets: 'no', pet_agreement: false, num_pets: 1, extra_mattress: 1, name: '' };
                    return this.selectedRooms[instances[0]];
                },

                selectedCount() {
                    return Object.keys(this.selectedRooms).length;
                },

                removeRoomBySlot(slotNum) {
                    const rooms = this.getSelectedRoomArray();
                    if (slotNum < 1 || slotNum > rooms.length) return;
                    
                    const roomToRemove = rooms[slotNum - 1];
                    if (roomToRemove) {
                        this.removeRoom(roomToRemove.id);
                    }
                },

                getSelectedRoomArray() {
                    return Object.values(this.selectedRooms);
                },

                getSlotClass(slotNum) {
                    const rooms = this.getSelectedRoomArray();
                    return slotNum <= rooms.length
                        ? 'border-[#8f5d54] bg-[#8f5d54]/5'
                        : 'border-dashed border-gray-300 bg-gray-50';
                },

                getSlotNumberClass(slotNum) {
                    const rooms = this.getSelectedRoomArray();
                    return slotNum <= rooms.length ? 'text-[#8f5d54]' : 'text-gray-400';
                },

                getSlotRoomName(slotNum) {
                    const rooms = this.getSelectedRoomArray();
                    if (slotNum <= rooms.length) {
                        const r = rooms[slotNum - 1];
                        if (!r) return 'Room ' + slotNum;

                        const roomNameLower = (r.name || '').toLowerCase();
                        const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                        const isCarCamping = roomNameLower.includes('car camping');

                        // For BYOT/Car Camping, show quantity if > 1
                        if ((isBYOT || isCarCamping) && (r.extra_mattress > 1)) {
                            return `${r.name} (${r.extra_mattress})`;
                        }

                        const instanceNum = r.instanceNum || 1;
                        return instanceNum > 1 ? `${r.name} #${instanceNum}` : r.name;
                    }
                    return '—';
                },

                hasRegularRooms() {
                    for (const k in this.selectedRooms) {
                        const r = this.selectedRooms[k];
                        const name = (r.name || '').toLowerCase();
                        const type = (r.type || '').toLowerCase();
                        const isSpecial = name.includes('byot') || name.includes('bring your own tent') || name.includes('car camping') || type === 'byot' || type === 'car camping';
                        if (!isSpecial) return true;
                    }
                    return false;
                },

                getRoom(roomId) {
                    const key = String(roomId);
                    if (!this.selectedRooms[key]) {
                        return { adults: 1, children: 0, extra_mattress: 0, pets: 'no', pet_agreement: false, num_pets: 0 };
                    }
                    return this.selectedRooms[key];
                },

                setField(roomId, field, value) {
                    const key = String(roomId);
                    if (!this.selectedRooms[key]) return;

                    if (['adults','children','extra_mattress','num_pets'].includes(field)) {
                        value = parseInt(value || 0, 10);
                        if (!isFinite(value)) value = 0;
                        if (field === 'adults' && value < 1) value = 1;
                        if (['children','extra_mattress','num_pets'].includes(field) && value < 0) value = 0;

                        const nameLower = (this.selectedRooms[key].name || '').toLowerCase();
                        const isBYOTorCar = nameLower.includes('byot') || nameLower.includes('bring your own tent') || nameLower.includes('car camping');

                        if (field === 'num_pets') {
                            if (isBYOTorCar) value = 0;
                            else if (value > {{ $maxPetsPerRoom }}) {
                                value = {{ $maxPetsPerRoom }};
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Pet Limit Exceeded',
                                    text: 'Maximum {{ $maxPetsPerRoom }} pets allowed for this room',
                                    confirmButtonColor: '#8f5d54',
                                });
                            }
                        }
                    }

                    this.selectedRooms[key][field] = value;

                    const staycations = @json($staycations);
                    const originalId = this.selectedRooms[key].originalId || key;
                    const room = staycations.find(s => s.id == originalId);
                    if (room) {
                        this.selectedRooms[key].accommodation_price = this.calculateBasePrice(room, this.selectedRooms[key]);
                    }

                    if (field === 'pets' && value === 'no') {
                        this.selectedRooms[key].pet_agreement = false;
                        this.selectedRooms[key].num_pets = 0;
                    }
                    if (field === 'pet_agreement' && value === false) {
                        this.selectedRooms[key].num_pets = 0;
                    }
                },

                validateAndSetGuests(roomId, field, value, roomName) {
                    const normalizedValue = parseInt(value || 0, 10);
                    if (!isFinite(normalizedValue)) {
                        this.setField(roomId, field, 0);
                        return;
                    }

                    const currentRoom = this.getRoom(roomId);
                    let adults = field === 'adults' ? normalizedValue : (currentRoom.adults || 1);
                    let children = field === 'children' ? normalizedValue : (currentRoom.children || 0);
                    const totalGuests = adults + children;

                    // For standard/premium rooms, enforce max 6 guests
                    if ((roomName.includes('standard') || roomName.includes('premium')) && totalGuests > 6) {
                        // Adjust the changed field to fit within limit
                        if (field === 'adults') {
                            const maxAdults = Math.max(1, 6 - children);
                            this.setField(roomId, field, maxAdults);
                        } else if (field === 'children') {
                            const maxChildren = Math.max(0, 6 - adults);
                            this.setField(roomId, field, maxChildren);
                        }
                        return;
                    }

                    this.setField(roomId, field, normalizedValue);
                },

                validateQuantity(roomId, fieldName, value, maxQuantity) {
                    const normalizedValue = parseInt(value || 1, 10);
                    if (!isFinite(normalizedValue)) {
                        this.setField(roomId, fieldName, 1);
                        return;
                    }

                    // For BYOT/Car Camping, calculate actual remaining considering other instances
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    const roomNameLower = (roomDetail?.name || '').toLowerCase();
                    const isBYOTorCar = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent') || roomNameLower.includes('car camping');

                    let actualMax = maxQuantity;
                    if (isBYOTorCar) {
                        // Calculate total quantity used by other instances of the same room
                        const instances = this.getSelectedRoomInstances(roomId);
                        let usedByOthers = 0;
                        for (const key of instances) {
                            if (key !== String(roomId)) {
                                usedByOthers += parseInt(this.selectedRooms[key]?.extra_mattress || 1, 10);
                            }
                        }
                        actualMax = Math.max(1, maxQuantity - usedByOthers);
                    }

                    if (normalizedValue > actualMax) {
                        Swal.fire({
                            title: 'Availability Limit Exceeded',
                            text: `Only ${actualMax} available for this accommodation during the selected dates.`,
                            icon: 'warning',
                            confirmButtonColor: '#8f5d54'
                        });
                        this.setField(roomId, fieldName, actualMax);
                        return;
                    }

                    if (normalizedValue < 1) {
                        this.setField(roomId, fieldName, 1);
                        return;
                    }

                    this.setField(roomId, fieldName, normalizedValue);
                },

                resetAll() {
                    this.selectedRooms = {};
                    this.maxSlots = Math.min(Math.max(this.targetRooms, 1), 4);
                },

                showCalendar(type, roomId) {
                    this.currentRoomId = roomId;
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);
                    this.currentRoomName = room ? room.name : 'Room';

                    this.calendarModal = true;
                    this.$nextTick(() => this.initCalendar());
                },

                showImageGalleryModal(roomId, name, images) {
                    if (!images || images.length === 0) {
                        this.showRoomDetailsModal(roomId, name, '', []);
                        return;
                    }
                    
                    this.imageGalleryRoomId = roomId;
                    this.imageGalleryTitle = name;
                    this.imageGalleryImages = images;
                    this.imageGalleryModal = true;
                    
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const swiperEl = this.$refs.imageGallerySwiper;
                            if (swiperEl) {
                                if (swiperEl.swiper) {
                                    swiperEl.swiper.destroy(true, true);
                                }
                                this.imageGallerySwiperInstance = new Swiper(swiperEl, {
                                    loop: this.imageGalleryImages.length > 1,
                                    pagination: { 
                                        el: '.image-gallery-swiper-pagination', 
                                        clickable: true,
                                        dynamicBullets: true
                                    },
                                    navigation: { 
                                        nextEl: '.image-gallery-swiper-next', 
                                        prevEl: '.image-gallery-swiper-prev' 
                                    },
                                    keyboard: true,
                                    speed: 500
                                });
                            }
                        }, 100);
                    });
                },
                
                closeGalleryAndShowDetails() {
                    this.imageGalleryModal = false;
                    const roomId = this.imageGalleryRoomId;
                    const room = this.roomDetailsMap && this.roomDetailsMap[roomId];
                    if (room) {
                        this.showRoomDetailsModal(roomId, room.name || this.imageGalleryTitle, room.description || '', []);
                    }
                },

                showRoomDetailsModal(roomId, name, description, features, images = []) {
                    this.roomDetailsId = roomId;
                    if (this.roomDetailsModal) {
                        console.warn('Modal already open, preventing recursive call.');
                        return;
                    }

                    this.roomDetailsName = name;
                    this.roomDetailsDescription = description;
                    
                    // Get room details from roomDetailsMap
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    
                    // Set room type
                    this.roomDetailsType = roomDetail ? (roomDetail.type ? roomDetail.type.charAt(0).toUpperCase() + roomDetail.type.slice(1) : 'Room') : 'Room';
                    this.roomDetailsTypeRaw = roomDetail?.type || '';
                    
                    // Set status
                    this.roomDetailsStatus = roomDetail ? roomDetail.status : 'available';
                    
                    // Set size if available
                    this.roomDetailsSize = roomDetail?.size || '';
                    
                    // Set price
                    const priceRange = roomDetail?.price_range || null;
                    if (priceRange && priceRange.min_price > 0 && priceRange.max_price > 0) {
                        if (priceRange.min_price === priceRange.max_price) {
                            this.roomDetailsPrice = priceRange.min_label || '₱' + priceRange.min_price.toLocaleString();
                        } else {
                            this.roomDetailsPrice = (priceRange.min_label || '₱' + priceRange.min_price.toLocaleString()) + ' - ' + (priceRange.max_label || '₱' + priceRange.max_price.toLocaleString());
                        }
                        this.roomDetailsPriceLabel = '/ night';
                    } else if (roomDetail?.price) {
                        this.roomDetailsPrice = '₱' + parseInt(roomDetail.price).toLocaleString();
                        const roomNameLower = (name || '').toLowerCase();
                        const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own');
                        const isCarCamping = roomNameLower.includes('car camping');
                        this.roomDetailsPriceLabel = (isBYOT || isCarCamping) ? '' : '/ night';
                    } else {
                        this.roomDetailsPrice = '₱0';
                        this.roomDetailsPriceLabel = '/ night';
                    }
                    
                    // Set available quantity
                    this.roomDetailsAvailable = roomDetail?.available_quantity || roomDetail?.total_quantity || '∞';
                    
                    // Calculate nights
                    if (this.checkin && this.checkout) {
                        const checkinDate = new Date(this.checkin);
                        const checkoutDate = new Date(this.checkout);
                        const nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));
                        this.roomDetailsNights = nights;
                    } else {
                        this.roomDetailsNights = '—';
                    }
                    
                    // Set total guests
                    this.roomDetailsGuests = this.targetAdults + this.targetChildren;
                    
                    // Parse features
                    if (typeof features === 'string') {
                        try {
                            this.roomDetailsFeatures = JSON.parse(features);
                        } catch (e) {
                            this.roomDetailsFeatures = [];
                        }
                    } else if (Array.isArray(features)) {
                        this.roomDetailsFeatures = features;
                    } else {
                        this.roomDetailsFeatures = [];
                    }
                    
                    // Set images from passed images parameter or imageGalleryImages if available
                    if (images && images.length > 0) {
                        this.roomDetailsImages = images;
                    } else if (this.imageGalleryImages && this.imageGalleryImages.length > 0) {
                        this.roomDetailsImages = this.imageGalleryImages;
                    } else {
                        this.roomDetailsImages = [];
                    }
                    
                    this.roomDetailsModal = true;

                    this.$nextTick(() => {
                        setTimeout(() => {
                            const swiperEl = this.$refs.roomDetailsSwiper;
                            if (swiperEl && this.roomDetailsImages.length > 1) {
                                if (swiperEl.swiper) {
                                    swiperEl.swiper.destroy(true, true);
                                }
                                new Swiper(swiperEl, {
                                    loop: this.roomDetailsImages.length > 1,
                                    pagination: { 
                                        el: '.room-details-swiper-pagination', 
                                        clickable: true,
                                        dynamicBullets: true
                                    },
                                    navigation: { 
                                        nextEl: '.room-details-swiper-next', 
                                        prevEl: '.room-details-swiper-prev' 
                                    },
                                    autoplay: this.roomDetailsImages.length > 1 ? { delay: 4000, disableOnInteraction: false } : false,
                                    speed: 500
                                });
                            }
                        }, 100);
                    });
                },

                selectRoomFromModal() {
                    const roomId = this.roomDetailsId;
                    if (!roomId) return;
                    const type = (this.roomDetailsTypeRaw || '').toLowerCase();

                    // Use existing toggleRoom to add/select the room
                    try {
                        this.toggleRoom(roomId, type);
                    } catch (e) {
                        console.error('Failed to select room from modal', e);
                    }

                    // Close modal after selection
                    this.roomDetailsModal = false;

                    // Clear temporary modal id
                    this.roomDetailsId = null;
                    this.roomDetailsTypeRaw = '';
                },

                initCalendar() {
                    if (this.calendarInstance) this.calendarInstance.destroy();

                    const checkin = '{{ request('checkin') ?? ($checkin ?? '') }}';
                    const checkout = '{{ request('checkout') ?? ($checkout ?? '') }}';

                    if (!this.$refs.calendar) return;

                    const roomId = this.currentRoomId;
                    const roomInfo = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    const baseQty = Math.max(0, parseInt((roomInfo && roomInfo.total_quantity) ? roomInfo.total_quantity : 0, 10));

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const fmt = (d) => {
                        const y = d.getFullYear();
                        const m = String(d.getMonth()+1).padStart(2,'0');
                        const da = String(d.getDate()).padStart(2,'0');
                        return `${y}-${m}-${da}`;
                    };

                    fetch(`/api/inventory/${roomId}`, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                    .then(async (r) => {
                        const text = await r.text();
                        try { return JSON.parse(text); } catch { return { items: [] }; }
                    })
                    .then((data) => {
                        const items = (data && data.items) ? data.items : [];
                        const invMap = {};
                        for (const it of items) {
                            if (it && it.date) invMap[it.date] = parseInt(it.min_quantity || 0, 10);
                        }

                        let legendMax = baseQty;
                        if (!legendMax) {
                            for (const q of Object.values(invMap)) legendMax = Math.max(legendMax, q || 0);
                        }
                        this.legendMaxQty = legendMax;
                        this.legendLowThreshold = legendMax > 0 ? Math.max(1, Math.floor(legendMax * 0.3)) : 1;

                        this.calendarInstance = flatpickr(this.$refs.calendar, {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            defaultDate: [checkin, checkout],
                            inline: true,
                            disable: [
                                (date) => {
                                    const checkDate = new Date(date);
                                    checkDate.setHours(0, 0, 0, 0);
                                    if (checkDate < today) return true;

                                    const ds = fmt(date);
                                    const qty = (ds in invMap) ? invMap[ds] : baseQty;
                                    return (qty || 0) === 0;
                                }
                            ],
                            onDayCreate: (selectedDates, dateStr, instance, dayElem) => {
                                const date = dayElem.dateObj;
                                const ds = instance.formatDate(date, 'Y-m-d');
                                dayElem.style.borderRadius = '6px';

                                const checkDate = new Date(date);
                                checkDate.setHours(0, 0, 0, 0);
                                const isPast = checkDate < today;

                                const qty = (ds in invMap) ? invMap[ds] : baseQty;

                                if (isPast || (qty || 0) === 0) {
                                    dayElem.style.background = '#d3d3d3';
                                    dayElem.style.color = '#6c757d';
                                    return;
                                }

                                if (qty <= this.legendLowThreshold) {
                                    dayElem.style.background = '#fff3cd';
                                    dayElem.style.color = '#856404';
                                } else {
                                    dayElem.style.background = '#d4edda';
                                    dayElem.style.color = '#155724';
                                }

                                const badge = document.createElement('div');
                                badge.textContent = qty;
                                badge.style.position = 'absolute';
                                badge.style.top = '2px';
                                badge.style.right = '2px';
                                badge.style.fontSize = '9px';
                                badge.style.fontWeight = '600';
                                badge.style.padding = '1px 3px';
                                badge.style.borderRadius = '8px';
                                badge.style.lineHeight = '1';
                                badge.style.minWidth = '14px';
                                badge.style.textAlign = 'center';

                                if (qty === 0) {
                                    badge.style.backgroundColor = '#fee2e2';
                                    badge.style.color = '#dc2626';
                                    badge.style.border = '1px solid #fecaca';
                                } else if (qty <= this.legendLowThreshold) {
                                    badge.style.backgroundColor = '#fef3c7';
                                    badge.style.color = '#d97706';
                                    badge.style.border = '1px solid #fde68a';
                                } else {
                                    badge.style.backgroundColor = '#d1fae5';
                                    badge.style.color = '#059669';
                                    badge.style.border = '1px solid #a7f3d0';
                                }

                                dayElem.style.position = 'relative';
                                dayElem.appendChild(badge);

                                if (ds >= checkin && ds < checkout) {
                                    dayElem.style.outline = '2px solid #3b82f6';
                                    dayElem.style.outlineOffset = '-2px';
                                }
                            }
                        });
                    })
                    .catch((err) => {
                        console.error('Failed to fetch inventory:', err);
                        this.legendMaxQty = baseQty;
                        this.legendLowThreshold = baseQty > 0 ? Math.max(1, Math.floor(baseQty * 0.3)) : 1;
                        this.calendarInstance = flatpickr(this.$refs.calendar, {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            defaultDate: [checkin, checkout],
                            inline: true
                        });
                    });
                },

                // ====== Pricing functions (same as your original; kept) ======
                calculateBasePrice(room, selectedRoomData = null) {
                    return this.fallbackCalculateBasePrice(room, selectedRoomData);
                },

                fallbackCalculateBasePrice(room, selectedRoomData = null) {
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    const isSpecialRoom = isBYOT || isCarCamping;

                    const quantity = selectedRoomData ? parseInt(selectedRoomData.extra_mattress || 1) : 1;

                    if (isSpecialRoom) {
                        // FIX: Use originalId for lookup since room.id might be a composite key
                        const lookupId = room.originalId || room.id;
                        const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                        const basePrice = (roomDetail && roomDetail.price) ? roomDetail.price : (room.price || 1500);
                        return basePrice * quantity;
                    }

                    let nights = 1;
                    if (this.checkin && this.checkout) {
                        const checkinDate = new Date(this.checkin);
                        const checkoutDate = new Date(this.checkout);
                        nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));
                    }

                    // FIX: Use originalId for lookup since room.id might be a composite key
                    const lookupId = room.originalId || room.id;
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                    const dayTypePricing = (roomDetail && roomDetail.day_type_pricing) ? roomDetail.day_type_pricing : {};
                    const peakPeriods = (roomDetail && roomDetail.peak_periods) ? roomDetail.peak_periods : [];
                    const roomBasePrice = (roomDetail && roomDetail.price) ? roomDetail.price : (room.type === 'glamping' ? 5500 : 3000);

                    let totalPrice = 0;
                    const checkinDate = new Date(this.checkin);
                    const checkoutDate = new Date(this.checkout);

                    for (let i = 0; i < nights; i++) {
                        const currentDate = new Date(checkinDate);
                        currentDate.setDate(checkinDate.getDate() + i);

                        const dayShort = currentDate.toLocaleDateString('en-US', { weekday: 'short' });

                        let season = 'Non-Peak';
                        for (const peak of peakPeriods) {
                            const peakStart = new Date(peak.start_date);
                            const peakEnd = new Date(peak.end_date);
                            if (currentDate >= peakStart && currentDate <= peakEnd) {
                                season = peak.peak_type_name || 'Peak';
                                break;
                            }
                        }

                        const isWeekend = dayShort.toLowerCase() === 'sat' || dayShort.toLowerCase() === 'sun';
                        const dateOnly = currentDate.toISOString().split('T')[0];

                        let rate = 0;
                        const datePricing = (roomDetail && roomDetail.date_pricing) ? roomDetail.date_pricing : {};

                        if (datePricing[dateOnly]) {
                            rate = parseFloat(datePricing[dateOnly]) || roomBasePrice;
                        } else if (dayTypePricing && Object.keys(dayTypePricing).length > 0) {
                            if (isWeekend) {
                                rate = season === 'Peak'
                                    ? (dayTypePricing.peak_weekend || dayTypePricing.peak || dayTypePricing.weekend || dayTypePricing.regular || roomBasePrice)
                                    : (dayTypePricing.regular_weekend || dayTypePricing.regular || roomBasePrice);
                            } else {
                                rate = season === 'Peak'
                                    ? (dayTypePricing.peak_weekday || dayTypePricing.peak || dayTypePricing.weekday || dayTypePricing.regular || roomBasePrice)
                                    : (dayTypePricing.regular_weekday || dayTypePricing.regular || roomBasePrice);
                            }
                        } else {
                            rate = isWeekend ? roomBasePrice + 1360 : roomBasePrice;
                        }

                        totalPrice += rate;
                    }

                    return totalPrice;
                },

                calculateMattressPrice(room) {
                    let nights = 1;
                    if (this.checkin && this.checkout) {
                        const checkinDate = new Date(this.checkin);
                        const checkoutDate = new Date(this.checkout);
                        nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));
                    }

                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    if (isBYOT || isCarCamping) return 0;

                    return (room.extra_mattress || 0) * this.bookingSettings.mattress_price_per_night * nights;
                },

                calculatePetPrice(room) {
                    if (room.pets !== 'yes' || !room.num_pets) return 0;

                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    if (isBYOT || isCarCamping) return 0;

                    return room.num_pets * this.bookingSettings.pet_price;
                },

                getPriceRange(room) {
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    const isSpecialRoom = isBYOT || isCarCamping;

                    if (isSpecialRoom) {
                        // FIX: Use originalId for lookup since room.id might be a composite key
                        const lookupId = room.originalId || room.id;
                        const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                        const basePrice = (roomDetail && roomDetail.price) ? roomDetail.price : 1500;
                        return '₱' + basePrice.toLocaleString();
                    }

                    // FIX: Use originalId for lookup since room.id might be a composite key
                    const lookupId = room.originalId || room.id;
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                    const dayTypePricing = (roomDetail && roomDetail.day_type_pricing) ? roomDetail.day_type_pricing : {};
                    const peakPeriods = (roomDetail && roomDetail.peak_periods) ? roomDetail.peak_periods : [];

                    let nights = 1;
                    if (this.checkin && this.checkout) {
                        const checkinDate = new Date(this.checkin);
                        const checkoutDate = new Date(this.checkout);
                        nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));
                    }

                    const rates = [];
                    const checkinDate = new Date(this.checkin);
                    const basePrice = (roomDetail && roomDetail.price) ? roomDetail.price : (room.type === 'glamping' ? 5500 : 3000);

                    for (let i = 0; i < nights; i++) {
                        const currentDate = new Date(checkinDate);
                        currentDate.setDate(checkinDate.getDate() + i);

                        const dayShort = currentDate.toLocaleDateString('en-US', { weekday: 'short' });

                        let season = 'Non-Peak';
                        for (const peak of peakPeriods) {
                            const peakStart = new Date(peak.start_date);
                            const peakEnd = new Date(peak.end_date);
                            if (currentDate >= peakStart && currentDate <= peakEnd) {
                                season = peak.peak_type_name || 'Peak';
                                break;
                            }
                        }

                        const isWeekend = dayShort.toLowerCase() === 'sat' || dayShort.toLowerCase() === 'sun';
                        const dateOnly = currentDate.toISOString().split('T')[0];

                        let rate = 0;
                        const datePricing = (roomDetail && roomDetail.date_pricing) ? roomDetail.date_pricing : {};

                        if (datePricing[dateOnly]) {
                            rate = parseFloat(datePricing[dateOnly]) || basePrice;
                        } else if (dayTypePricing && Object.keys(dayTypePricing).length > 0) {
                            if (isWeekend) {
                                rate = season === 'Peak'
                                    ? (dayTypePricing.peak_weekend || dayTypePricing.peak || dayTypePricing.weekend || dayTypePricing.regular || basePrice)
                                    : (dayTypePricing.regular_weekend || dayTypePricing.regular || basePrice);
                            } else {
                                rate = season === 'Peak'
                                    ? (dayTypePricing.peak_weekday || dayTypePricing.peak || dayTypePricing.weekday || dayTypePricing.regular || basePrice)
                                    : (dayTypePricing.regular_weekday || dayTypePricing.regular || basePrice);
                            }
                        } else {
                            rate = isWeekend ? basePrice + 1360 : basePrice;
                        }

                        rates.push(rate);
                    }

                    const uniqueRates = [...new Set(rates)];
                    if (uniqueRates.length === 1) return '₱' + uniqueRates[0].toLocaleString() + ' / night';
                    return '₱' + Math.min(...uniqueRates).toLocaleString() + ' - ₱' + Math.max(...uniqueRates).toLocaleString() + ' / night';
                },

                calculateRoomPrice(room) {
                    // Use stored accommodation_price which is already calculated
                    const base = room.accommodation_price || 0;
                    const pet = this.calculatePetPrice(room);
                    return '₱' + (base + pet).toLocaleString();
                },

                calculateSubtotal() {
                    let total = 0;
                    for (const room of Object.values(this.selectedRooms)) {
                        const base = room.accommodation_price || 0;
                        total += base + this.calculatePetPrice(room);
                    }
                    return '₱' + total.toLocaleString();
                },

                calculateVAT() {
                    let total = 0;
                    for (const room of Object.values(this.selectedRooms)) {
                        const base = room.accommodation_price || 0;
                        total += base + this.calculatePetPrice(room);
                    }
                    // Calculate dynamic VAT from subtotal (using dynamic rate from admin settings)
                    const vat = total * this.vatRate;
                    return '₱' + vat.toLocaleString();
                },

                calculateTotalPrice() {
                    let total = 0;
                    for (const room of Object.values(this.selectedRooms)) {
                        const base = room.accommodation_price || 0;
                        total += base + this.calculatePetPrice(room);
                    }
                    // Calculate Grand Total (subtotal + dynamic VAT)
                    const grandTotal = total * (1 + this.vatRate);
                    return '₱' + grandTotal.toLocaleString();
                },

                // For breakdown modal - shows base total without VAT and WITHOUT pet price (pet price only in subtotal)
                calculateBreakdownTotal() {
                    let total = 0;
                    for (const room of Object.values(this.selectedRooms)) {
                        const base = room.accommodation_price || 0;
                        total += base; // Only room price, not pet price
                    }
                    return '₱' + total.toLocaleString();
                },

                // breakdown helpers (unchanged behavior)
                currentBreakdownTitle() {
                    const roomCount = Object.keys(this.selectedRooms).length;
                    const nights = this.getNightsCount();
                    
                    // Format dates as MM-DD-YYYY
                    const formatDate = (dateStr) => {
                        if (!dateStr) return '';
                        const d = new Date(dateStr);
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const da = String(d.getDate()).padStart(2, '0');
                        const y = d.getFullYear();
                        return `${m}-${da}-${y}`;
                    };
                    
                    const ci = formatDate(this.checkin);
                    const co = formatDate(this.checkout);
                    if (roomCount === 0) return 'No rooms selected';
                    if (roomCount === 1) {
                        const room = Object.values(this.selectedRooms)[0];
                        return `${room.name} • ${nights} night(s) • ${ci} to ${co}`;
                    }
                    return `${roomCount} rooms • ${nights} night(s) • ${ci} to ${co}`;
                },

                getNightsCount() {
                    if (!this.checkin || !this.checkout) return 0;
                    const a = new Date(this.checkin);
                    const b = new Date(this.checkout);
                    return Math.max(0, Math.ceil((b - a) / (1000 * 60 * 60 * 24)));
                },

                getPriceBreakdown(room) {
                    const breakdown = [];

                    if (!this.checkin || !this.checkout) return breakdown;

                    const checkinDate = new Date(this.checkin);
                    const checkoutDate = new Date(this.checkout);
                    const nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));

                    // FIX: Use originalId for lookup since room.id might be a composite key (e.g., "5_2")
                    const lookupId = room.originalId || room.id;
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;

                    // Check if BYOT or Car Camping (per quantity pricing, NOT per night)
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    const isSpecialRoom = isBYOT || isCarCamping;

                    // Get dynamic pricing info from roomDetail
                    const dayTypePricing = (roomDetail && roomDetail.day_type_pricing) ? roomDetail.day_type_pricing : {};
                    const peakPeriods = (roomDetail && roomDetail.peak_periods) ? roomDetail.peak_periods : [];

                    for (let i = 0; i < nights; i++) {
                        const currentDate = new Date(checkinDate);
                        currentDate.setDate(checkinDate.getDate() + i);

                        const dateStr = currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const dayName = currentDate.toLocaleDateString('en-US', { weekday: 'long' });
                        const dayShort = currentDate.toLocaleDateString('en-US', { weekday: 'short' });

                        // Determine season based on peak periods
                        let season = 'Non-Peak';
                        const dateOnly = currentDate.toISOString().split('T')[0];

                        // Check if peak periods exist
                        if (peakPeriods && peakPeriods.length > 0) {
                            for (const peak of peakPeriods) {
                                const peakStart = peak.start_date ? String(peak.start_date).split('T')[0] : '';
                                const peakEnd = peak.end_date ? String(peak.end_date).split('T')[0] : '';

                                if (peakStart && peakEnd && dateOnly >= peakStart && dateOnly <= peakEnd) {
                                    season = peak.peak_type_name || 'Peak';
                                    break;
                                }
                            }
                        } else {
                            // Fallback to roomDetail.seasonType if available
                            if (roomDetail?.seasonType) {
                                season = roomDetail.seasonType;
                            }
                        }

                        // Determine day type (Weekday/Weekend) and get price
                        let dayType = 'Weekday';
                        let rate = 0;
                        const dayLower = (dayShort || '').toLowerCase();
                        const isWeekend = dayLower === 'sat' || dayLower === 'sun' || dayLower === 'saturday' || dayLower === 'sunday';
                        if (isWeekend) dayType = 'Weekend';

                        // Get price based on day type
                        // Get base price for fallback
                        const basePrice = (roomDetail && roomDetail.price) ? roomDetail.price : (room.type === 'glamping' ? 5500 : 3000);

                        // Check for specific date pricing first
                        const datePricing = (roomDetail && roomDetail.date_pricing) ? roomDetail.date_pricing : {};
                        const dateOnlyStr = dateOnly;

                        if (datePricing[dateOnlyStr]) {
                            // Use specific date price override
                            rate = parseFloat(datePricing[dateOnlyStr]) || basePrice;
                        } else if (dayTypePricing && Object.keys(dayTypePricing).length > 0) {
                            // Use dynamic pricing from roomDetail
                            if (isWeekend) {
                                dayType = 'Weekend';
                                rate = season === 'Peak'
                                    ? (dayTypePricing.peak_weekend || dayTypePricing.peak || dayTypePricing.weekend || dayTypePricing.regular || basePrice)
                                    : (dayTypePricing.regular_weekend || dayTypePricing.regular || basePrice);
                            } else {
                                dayType = 'Weekday';
                                rate = season === 'Peak'
                                    ? (dayTypePricing.peak_weekday || dayTypePricing.peak || dayTypePricing.weekday || dayTypePricing.regular || basePrice)
                                    : (dayTypePricing.regular_weekday || dayTypePricing.regular || basePrice);
                            }
                        } else {
                            // Fallback to default pricing
                            rate = isWeekend ? basePrice + 1360 : basePrice;
                        }

                        // For BYOT/Car Camping, show per quantity rate
                        const quantity = room.extra_mattress || 1;
                        const displayRate = isBYOT || isCarCamping ? rate * quantity : rate;

                        breakdown.push({
                            date: dateStr,
                            day: dayShort,
                            season: season,
                            dayType: dayType,
                            rate: displayRate
                        });
                    }

                    return breakdown;
                },

                getSingleRoomBreakdown() {
                    const room = Object.values(this.selectedRooms)[0];
                    if (!room) return [];

                    // Use the breakdown from the API if available
                    if (this.priceBreakdowns && this.priceBreakdowns[room.id]) {
                        return this.priceBreakdowns[room.id].map(r => {
                            // Determine if it's a peak type (for badge styling)
                            const isPeak = r.season && r.season.toLowerCase().includes('peak') && !r.season.toLowerCase().includes('non');
                            const isSuperPeak = r.season && r.season.toLowerCase().includes('super');

                            // Format date as MM-DD-YYYY
                            const d = new Date(r.date);
                            const m = String(d.getMonth() + 1).padStart(2, '0');
                            const da = String(d.getDate()).padStart(2, '0');
                            const y = d.getFullYear();
                            const formattedDate = `${m}-${da}-${y}`;

                            return {
                                dateRaw: formattedDate,
                                dayLong: r.day_of_week,
                                season: r.season,
                                seasonType: isSuperPeak ? 'Super-Peak' : (isPeak ? 'Peak' : 'Non-Peak'),
                                dayType: r.dayType,
                                rate: r.rate
                            };
                        });
                    }

                    // Fallback to frontend calculation if API data not available
                    // FIX: Use originalId for lookup since room.id might be a composite key
                    const lookupId = room.originalId || room.id;
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                    const peakPeriods = (roomDetail && roomDetail.peak_periods) ? roomDetail.peak_periods : [];

                    const peakTypeMap = {};
                    for (const peak of peakPeriods) {
                        if (peak.start_date && peak.end_date) {
                            const peakTypeName = peak.peak_type_name || peak.name || 'Peak';
                            const start = new Date(peak.start_date);
                            const end = new Date(peak.end_date);
                            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                                const dateStr = d.toISOString().split('T')[0];
                                peakTypeMap[dateStr] = peakTypeName;
                            }
                        }
                    }

                    const raw = this.getPriceBreakdown(room);

                    const mapDayLong = {
                        Sun: 'Sunday', Mon: 'Monday', Tue: 'Tuesday', Wed: 'Wednesday',
                        Thu: 'Thursday', Fri: 'Friday', Sat: 'Saturday'
                    };

                    const toISO = (dateStr) => {
                        const d = new Date(dateStr);
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const da = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${da}`;
                    };

                    const formatDateMMDDYYYY = (dateStr) => {
                        const d = new Date(dateStr);
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const da = String(d.getDate()).padStart(2, '0');
                        const y = d.getFullYear();
                        return `${m}-${da}-${y}`;
                    };

                    return raw.map(r => {
                        const isoDate = formatDateMMDDYYYY(r.date);
                        const configuredSeason = r.season || 'Non-Peak';

                        const isPeak = configuredSeason && configuredSeason.toLowerCase().includes('peak') && !configuredSeason.toLowerCase().includes('non');
                        const isSuperPeak = configuredSeason && configuredSeason.toLowerCase().includes('super');

                        return {
                            dateRaw: isoDate,
                            dayLong: mapDayLong[r.day] || r.day,
                            season: configuredSeason,
                            seasonType: isSuperPeak ? 'Super-Peak' : (isPeak ? 'Peak' : 'Non-Peak'),
                            dayType: r.dayType,
                            rate: r.rate
                        };
                    });
                },

                getAllRoomsBreakdown() {
                    const rooms = Object.values(this.selectedRooms);
                    if (rooms.length === 0) return [];

                    // If single room, use existing breakdown
                    if (rooms.length === 1) {
                        return this.getSingleRoomBreakdown();
                    }

                    // For multiple rooms, aggregate all breakdowns with room name
                    const allBreakdowns = [];

                    for (const room of rooms) {
                        const roomNameLower = (room.name || '').toLowerCase();
                        const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                        const isCarCamping = roomNameLower.includes('car camping');
                        const isSpecialRoom = isBYOT || isCarCamping;

                        // For BYOT/Car Camping, show just single per-slot entry
                        if (isSpecialRoom) {
                            // FIX: Use originalId for lookup since room.id might be a composite key
                            const lookupId = room.originalId || room.id;
                            const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                            const basePrice = (roomDetail && roomDetail.price) ? roomDetail.price : (room.price || 1500);
                            const quantity = room.extra_mattress || 1;
                            const totalPrice = basePrice * quantity;

                            // Format checkin date as MM-DD-YYYY
                            const formatCheckin = (dateStr) => {
                                if (!dateStr) return '-';
                                const d = new Date(dateStr);
                                const m = String(d.getMonth() + 1).padStart(2, '0');
                                const da = String(d.getDate()).padStart(2, '0');
                                const y = d.getFullYear();
                                return `${m}-${da}-${y}`;
                            };

                            allBreakdowns.push({
                                roomName: room.name,
                                dateRaw: formatCheckin(this.checkin),
                                dayLong: 'Per Slot',
                                season: '-',
                                seasonType: 'Non-Peak',
                                dayType: 'Per Slot',
                                rate: totalPrice
                            });
                            continue;
                        }

                        // For regular rooms, show nightly breakdown
                        // FIX: Use originalId for lookup since room.id might be a composite key (e.g., "5_2")
                        const lookupId = room.originalId || room.id;
                        const roomDetail = this.roomDetailsMap && this.roomDetailsMap[lookupId] ? this.roomDetailsMap[lookupId] : null;
                        const peakPeriods = (roomDetail && roomDetail.peak_periods) ? roomDetail.peak_periods : [];

                        const peakTypeMap = {};
                        for (const peak of peakPeriods) {
                            if (peak.start_date && peak.end_date) {
                                const peakTypeName = peak.peak_type_name || peak.name || 'Peak';
                                const start = new Date(peak.start_date);
                                const end = new Date(peak.end_date);
                                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                                    const dateStr = d.toISOString().split('T')[0];
                                    peakTypeMap[dateStr] = peakTypeName;
                                }
                            }
                        }

                        const raw = this.getPriceBreakdown(room);

                        const mapDayLong = {
                            Sun: 'Sunday', Mon: 'Monday', Tue: 'Tuesday', Wed: 'Wednesday',
                            Thu: 'Thursday', Fri: 'Friday', Sat: 'Saturday'
                        };

                        const toISO = (dateStr) => {
                            const d = new Date(dateStr);
                            const y = d.getFullYear();
                            const m = String(d.getMonth() + 1).padStart(2, '0');
                            const da = String(d.getDate()).padStart(2, '0');
                            return `${y}-${m}-${da}`;
                        };

                        const formatDateMMDDYYYY = (dateStr) => {
                            const d = new Date(dateStr);
                            const m = String(d.getMonth() + 1).padStart(2, '0');
                            const da = String(d.getDate()).padStart(2, '0');
                            const y = d.getFullYear();
                            return `${m}-${da}-${y}`;
                        };

                        for (const r of raw) {
                            const formattedDate = formatDateMMDDYYYY(r.date);
                            const configuredSeason = r.season || 'Non-Peak';

                            const isPeak = configuredSeason && configuredSeason.toLowerCase().includes('peak') && !configuredSeason.toLowerCase().includes('non');
                            const isSuperPeak = configuredSeason && configuredSeason.toLowerCase().includes('super');

                            allBreakdowns.push({
                                roomName: room.name,
                                dateRaw: formattedDate,
                                dayLong: mapDayLong[r.day] || r.day,
                                season: configuredSeason,
                                seasonType: isSuperPeak ? 'Super-Peak' : (isPeak ? 'Peak' : 'Non-Peak'),
                                dayType: r.dayType,
                                rate: r.rate
                            });
                        }
                    }

                    return allBreakdowns;
                },

                fetchBookingSettings() {
                    fetch('/api/booking/settings')
                        .then(response => response.json())
                        .then(data => {
                            if (data.mattress_price_per_night !== undefined) this.bookingSettings.mattress_price_per_night = data.mattress_price_per_night;
                            if (data.pet_price !== undefined) this.bookingSettings.pet_price = data.pet_price;
                        })
                        .catch(error => console.error('Failed to fetch booking settings:', error));
                },

                fetchVatRate() {
                    fetch('/api/vat-rate')
                        .then(response => response.json())
                        .then(data => {
                            if (data.rate !== undefined) this.vatRate = data.rate;
                            if (data.rate_percentage !== undefined) this.vatRatePercentage = data.rate_percentage;
                        })
                        .catch(error => console.error('Failed to fetch VAT rate:', error));
                },

                hydrateSelectedRoomsFromSession() {
                    const bookingAccommodations = @json(session('booking.accommodations', []));
                    const staycations = @json($staycations);
                    const sessionCheckin = @json(session('booking.checkin'));
                    const sessionCheckout = @json(session('booking.checkout'));

                    // Always start from empty to prevent stale carry-over
                    this.selectedRooms = {};
                    this.editingRoom = null;

                    // Only hydrate if the saved accommodations match the currently rendered date range
                    const sameDateRange = String(sessionCheckin || '') === String(this.checkin || '')
                        && String(sessionCheckout || '') === String(this.checkout || '');

                    if (!sameDateRange || !bookingAccommodations || typeof bookingAccommodations !== 'object' || Object.keys(bookingAccommodations).length === 0) {
                        return;
                    }

                    const hydrated = {};

                    for (const [roomKey, acc] of Object.entries(bookingAccommodations)) {
                        const originalId = parseInt(acc.room_id || acc.accommodation_id || (String(roomKey).split('_')[0]), 10);
                        const instanceNum = parseInt(acc.instance_num || (String(roomKey).includes('_') ? String(roomKey).split('_')[1] : 1), 10);
                        const compositeKey = instanceNum > 1 ? `${originalId}_${instanceNum}` : String(originalId);

                        const staycation = staycations.find(s => String(s.id) === String(originalId));
                        const roomName = (staycation && staycation.name) ? staycation.name : (acc.room_name || 'Room');

                        const hydratedRoom = {
                            id: compositeKey,
                            originalId: originalId,
                            instanceNum: instanceNum,
                            name: roomName,
                            type: (staycation && staycation.type) ? String(staycation.type).toLowerCase() : '',
                            adults: parseInt(acc.adults ?? 1, 10) || 1,
                            children: parseInt(acc.children ?? 0, 10) || 0,
                            extra_mattress: parseInt(acc.extra_mattress ?? 0, 10) || 0,
                            pets: (acc.pets === 'yes') ? 'yes' : 'no',
                            pet_agreement: !!acc.pet_agreement,
                            num_pets: parseInt(acc.num_pets ?? 0, 10) || 0,
                            accommodation_price: parseFloat(acc.accommodation_price ?? 0) || 0,
                        };

                        if (hydratedRoom.pets !== 'yes') {
                            hydratedRoom.pet_agreement = false;
                            hydratedRoom.num_pets = 0;
                        }
                        if (hydratedRoom.pets === 'yes' && !hydratedRoom.pet_agreement) {
                            hydratedRoom.num_pets = 0;
                        }

                        if (staycation) {
                            hydratedRoom.accommodation_price = this.calculateBasePrice(staycation, hydratedRoom);
                        }

                        hydrated[compositeKey] = hydratedRoom;
                    }

                    // Always assign fresh object to avoid stale reactive leftovers
                    this.selectedRooms = { ...hydrated };

                    const keys = Object.keys(this.selectedRooms);
                    this.editingRoom = keys.length > 0 ? keys[0] : null;
                },

                init() {
                    if (window.Echo && window.Echo.channel) {
                        window.Echo.channel('pricing-updates')
                            .listen('PricingUpdated', (e) => {
                                this.fetchUpdatedPricingData();
                            });
                    }

                    this.fetchBookingSettings();
                    this.fetchVatRate();
                    this.hydrateSelectedRoomsFromSession();
                },

                fetchUpdatedPricingData() {
                    fetch('/api/booking/pricing-data')
                        .then(response => response.json())
                        .then(data => {
                            if (data.roomDetails) {
                                this.roomDetailsMap = data.roomDetails;
                                for (const roomId of Object.keys(this.selectedRooms)) {
                                    const r = this.selectedRooms[roomId];
                                    const staycations = @json($staycations);
                                    const originalId = r.originalId || (''+roomId).split('_')[0];
                                    const room = staycations.find(s => String(s.id) === String(originalId));
                                    if (room) {
                                        this.selectedRooms[roomId].accommodation_price = this.calculateBasePrice(room, this.selectedRooms[roomId]);
                                    }
                                }
                            }
                        })
                        .catch(error => console.error('Failed to fetch updated pricing data:', error));
                },

                submitAndProceed(nextUrl = '{{ route("booking.staycationFood") }}') {
                    if (this.selectedCount() === 0) {
                        Swal.fire('Select at least one room', 'Please select a room before continuing.', 'warning');
                        return;
                    }

                    for (const r of Object.values(this.selectedRooms)) {
                        if (r.pets === 'yes') {
                            if (!r.pet_agreement) {
                                Swal.fire('Pet policy required', `Please agree to the pet policy for "${r.name}".`, 'warning');
                                return;
                            }
                            if (!r.num_pets || r.num_pets < 1) {
                                Swal.fire('Pet count required', `Please set number of pets (1-2) for "${r.name}".`, 'warning');
                                return;
                            }
                        }
                    }

                    fetch('{{ route("booking.staycation") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            selected_rooms: this.selectedRooms,
                            proceed_to_activities: true
                        })
                    })
                    .then(r => {
                        if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                        return r.json();
                    })
                    .then(data => {
                        if (data.success) window.location.href = nextUrl;
                        else Swal.fire('Error', data.message || 'Failed to save selection', 'error');
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Network error saving selection: ' + error.message, 'error');
                    });
                }
            }
        }

        function initMobileCardCarousels() {
            if (!window.matchMedia('(max-width: 767px)').matches) return;
            const root = document.querySelector('.mobile-layout-mode-3');
            if (!root) return;

            const getSlideWidth = (track) => {
                const firstCard = track.querySelector(':scope > *');
                if (!firstCard) return track.clientWidth || 1;
                const trackStyles = getComputedStyle(track);
                const gapValue = parseFloat(trackStyles.columnGap || trackStyles.gap || '0') || 0;
                return firstCard.getBoundingClientRect().width + gapValue;
            };

            const syncActiveDot = (track) => {
                const dots = track.nextElementSibling;
                if (!dots || !dots.classList.contains('mobile-carousel-dots')) return;
                const slideWidth = getSlideWidth(track);
                if (!slideWidth) return;

                const maxIndex = dots.children.length - 1;
                const activeIndex = Math.min(maxIndex, Math.max(0, Math.round(track.scrollLeft / slideWidth)));

                Array.from(dots.children).forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === activeIndex);
                });
            };

            root.querySelectorAll('.cards-grid-responsive').forEach((track) => {
                if (track.dataset.mobileCarouselReady === '1') return;
                const cards = Array.from(track.children).filter((node) => node.nodeType === 1);
                if (cards.length <= 1) return;

                const dots = document.createElement('div');
                dots.className = 'mobile-carousel-dots';
                dots.setAttribute('aria-label', 'Carousel pagination');

                cards.forEach((_, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `mobile-carousel-dot${index === 0 ? ' is-active' : ''}`;
                    button.setAttribute('aria-label', `Go to card ${index + 1}`);
                    button.addEventListener('click', () => {
                        track.scrollTo({
                            left: index * getSlideWidth(track),
                            behavior: 'smooth'
                        });
                    });
                    dots.appendChild(button);
                });

                track.insertAdjacentElement('afterend', dots);
                track.addEventListener('scroll', () => syncActiveDot(track), { passive: true });
                window.addEventListener('resize', () => syncActiveDot(track));
                syncActiveDot(track);
                track.dataset.mobileCarouselReady = '1';
            });
        }

        document.addEventListener('DOMContentLoaded', initMobileCardCarousels);

        // NOTE: your original realtime listeners used "this" outside Alpine scope.
        // I left them out to avoid runtime errors; if you want, paste them inside init() where "this" is valid.
    </script>

    @if(session('booking_prefill_saved'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: {!! json_encode(session('booking_prefill_saved')) !!},
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        });
    </script>
    @endif
</x-app-layout>

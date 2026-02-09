{{-- resources/views/booking/staycation-availability.blade.php --}}
<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @if(config('broadcasting.default') !== 'null')
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

        /* base rounding */
        .flatpickr-day { border-radius: 6px; }

        /* availability tiers */
        .fp-avail-high { background:#d4edda !important; color:#155724 !important; }
        .fp-avail-low  { background:#fff3cd !important; color:#856404 !important; }
        .fp-not-avail  { background:#f8d7da !important; color:#721c24 !important; }
        .fp-invalid    { background:#d3d3d3 !important; color:#6c757d !important; }

        /* range highlights (optional) */
        .fp-checkin    { background:#3b82f6 !important; color:#fff !important; }
        .fp-checkout   { background:#2563eb !important; color:#fff !important; }
        .fp-inrange    { background:#93c5fd !important; color:#0f172a !important; }

        /* Swiper styles */
        .swiper {
            height: 384px; /* h-96 = 24rem = 384px */
        }
        .swiper-button-next, .swiper-button-prev {
            color: white;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            width: 40px !important;
            height: 40px !important;
            margin-top: -20px !important;
        }
        .swiper-button-next::after, .swiper-button-prev::after {
            font-size: 16px !important;
        }
        .swiper-pagination {
            bottom: 10px !important;
        }
        .swiper-pagination-bullet-active {
            background: #8f5d54 !important;
        }
    </style>

    <div class="bg-[#f5efe7] min-h-screen">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight" style="font-family:'Gotham Book', sans-serif;">Select Room(s)</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-xs text-gray-500" style="font-family:'Gotham Book', sans-serif;">
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
                x-data="staycationAvailabilityMerged()"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2">
                    {{-- Filter --}}
                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="roomType" class="block text-sm font-medium text-gray-700 mb-2" style="font-family:'Gotham Book', sans-serif;">Filter by Type:</label>
                            <select id="roomType" x-model="roomType"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Types</option>
                                <option value="glamping">Glamping</option>
                                <option value="camping">Camping</option>
                            </select>
                        </div>

                        <button type="button"
                                class="ml-auto px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="selectedCount() === 0"
                        >
                            Reset All
                        </button>
                    </div>

                    {{-- Rooms Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                class="relative bg-white rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl {{ $status === 'unavailable' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}
                                {{ $status === 'available' ? 'ring-2 ring-green-200 hover:ring-green-300' : ($status === 'limited' ? 'ring-2 ring-yellow-200 hover:ring-yellow-300' : 'ring-2 ring-gray-200') }}"
                                data-room-id="{{ $staycation->id }}"
                                data-type="{{ $roomDetail['type'] }}"
                                x-show="roomType === 'all' || roomType === '{{ $roomDetail['type'] }}'"
                                @click="{{ $status === 'unavailable' ? 'null' : "toggleRoom(" . $staycation->id . ", '" . $roomDetail['type'] . "')" }}"
                                {{ $status === 'unavailable' ? 'style=pointer-events:none' : '' }}
                            >
                                {{-- Selected indicator --}}
                                <div class="absolute top-2 left-2 z-10" x-show="isSelected({{ $staycation->id }})" x-transition>
                                    <div class="w-6 h-6 bg-[#8f5d54] rounded-full flex items-center justify-center">
                                        <i class="bi bi-check text-white text-sm"></i>
                                    </div>
                                </div>

                                {{-- Image --}}
                                <div class="relative">
                                    @if($staycation->swiperImages && $staycation->swiperImages->count() > 1)
                                        <div class="swiper staycation-swiper-{{ $staycation->id }}">
                                            <div class="swiper-wrapper">
                                                @foreach($staycation->swiperImages as $image)
                                                    <div class="swiper-slide">
                                                        <img src="{{ $image->image_path }}" alt="{{ $image->title ?? $roomDetail['name'] }}" class="w-full h-96 object-cover">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-pagination swiper-pagination-{{ $staycation->id }}"></div>
                                            <div class="swiper-button-next swiper-button-next-{{ $staycation->id }}" @click.stop></div>
                                            <div class="swiper-button-prev swiper-button-prev-{{ $staycation->id }}" @click.stop></div>
                                        </div>
                                    @else
                                        <img src="{{ $staycation->swiperImages && $staycation->swiperImages->count() > 0 ? $staycation->swiperImages->first()->image_path : $roomDetail['image'] }}" alt="{{ $roomDetail['name'] }}" class="w-full h-96 object-cover">
                                    @endif

                                    {{-- Status badge --}}
                                    <div class="absolute top-4 right-4 z-10">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold text-white {{ $bgClass }}">
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
                                    <div class="absolute bottom-4 left-4 z-10">
                                        <span class="px-3 py-1 bg-[#8f5d54] text-white text-sm font-semibold rounded-full">
                                            {{ ucfirst($roomDetail['type']) }}
                                        </span>
                                    </div>

                                    {{-- Qty badge --}}
                                    @if(($avail['min_quantity'] ?? 0) > 0)
                                        <div class="absolute bottom-4 right-4 z-10">
                                            <span class="availability-badge px-2 py-1 bg-black/70 text-white text-xs font-semibold rounded-full">
                                                {{ $avail['min_quantity'] }} available
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-bold mb-2" style="font-family:'Gotham Book', sans-serif;">
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

                                    <div class="text-lg font-bold text-[#8f5d54] mb-2">
                                        @if($priceRange['min_price'] > 0 && $priceRange['max_price'] > 0)
                                            @if($priceRange['min_price'] == $priceRange['max_price'])
                                                {{-- All prices are the same --}}
                                                {{ $priceRange['min_label'] }} / 
                                            @else
                                                {{-- Price range with different min/max --}}
                                                {{ $priceRange['min_label'] }} - {{ $priceRange['max_label'] }} / 
                                            @endif
                                        @else
                                            ₱{{ number_format($roomDetail['price'], 0) }} / 
                                        @endif

                                        @if($isBYOT || $isCarCamping)
                                            slot
                                        @else
                                            night
                                        @endif
                                    </div>

                                    <p class="text-gray-600 mb-4 text-sm">
                                        {{ $roomDetail['description'] }}
                                    </p>

                                    {{-- Features --}}
                                    @php
                                        $featuresText = $staycation->texts->firstWhere('section', 'features');
                                        $features = $featuresText ? explode("\n", $featuresText->content) : [];
                                    @endphp
                                    <x-staycation-features :features="$features" />

                                    {{-- Availability info --}}
                                    @if($status === 'limited')
                                        <div class="text-sm text-amber-700 mb-4 bg-amber-50 p-3 rounded-lg border border-amber-200">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            Only {{ $avail['available_nights'] }}/{{ $avail['total_nights'] }} nights available
                                        </div>
                                    @elseif($status === 'available')
                                        <div class="text-sm text-green-700 mb-4 bg-green-50 p-3 rounded-lg border border-green-200">
                                            <i class="bi bi-check-circle mr-1"></i>
                                            Fully available for your dates
                                        </div>
                                    @endif

                                    {{-- Buttons --}}
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click.stop="showCalendar('{{ $roomDetail['type'] }}', {{ $staycation->id }})"
                                            class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-semibold hover:bg-gray-200 transition text-sm"
                                        >
                                            <i class="bi bi-calendar mr-1"></i>Calendar
                                        </button>

                                        <button
                                            type="button"
                                            class="flex-1 py-2 px-4 rounded-lg font-semibold transition text-sm"
                                            @click.stop="toggleRoom({{ $staycation->id }}, '{{ $roomDetail['type'] }}')"
                                            {{ $status !== 'available' ? 'disabled' : '' }}
                                            :class="isSelected({{ $staycation->id }})
                                                ? 'bg-green-600 text-white hover:bg-green-700'
                                                : '{{ $status === 'available' ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}'
                                            "
                                        >
                                            <template x-if="isSelected({{ $staycation->id }})">
                                                <span><i class="bi bi-check2-circle mr-1"></i>Selected</span>
                                            </template>
                                            <template x-if="!isSelected({{ $staycation->id }})">
                                                <span><i class="bi bi-plus-circle mr-1"></i>Select</span>
                                            </template>
                                        </button>
                                    </div>

                                    {{-- Inline edit panel (shows when selected) --}}
                                    <div x-show="isSelected({{ $staycation->id }})" x-transition class="mt-4 border-t pt-4" @click.stop @input.stop>
                                        <div class="text-sm font-semibold text-gray-800 mb-3">
                                            Guest details (edit anytime)
                                        </div>

                                        @php
                                            $roomNameLower = strtolower($roomDetail['name']);
                                            // Check for BYOT first (prioritize BYOT over car camping)
                                            $isBYOT = str_contains($roomNameLower, 'byot') ||
                                                     str_contains($roomNameLower, 'bring your own tent') ||
                                                     str_contains($roomNameLower, 'bring your own');
                                            // Check for car camping only if not BYOT
                                            $isCarCamping = !$isBYOT && str_contains($roomNameLower, 'car camping');
                                        @endphp

                                        @if($isBYOT)
                                            {{-- BYOT: Show "How many BYOT" only --}}
                                            <div class="grid grid-cols-1 gap-2">
                                                <div>
                                                    <label class="text-xs text-gray-600 font-semibold">How many BYOT?</label>
                                                    <input type="number" min="1" max="{{ $roomDetails[$staycation->id]['available_quantity'] ?? 8 }}"
                                                           class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                           :value="getRoom({{ $staycation->id }}).extra_mattress || 1"
                                                           @input="validateQuantity({{ $staycation->id }}, 'extra_mattress', $event.target.value, {{ $roomDetails[$staycation->id]['available_quantity'] ?? 8 }})"
                                                    >
                                                    <span class="text-xs text-gray-500 mt-1 block" x-text="'Remaining: ' + ({{ $roomDetails[$staycation->id]['available_quantity'] ?? 0 }} - (getRoom({{ $staycation->id }}).extra_mattress || 1))"></span>
                                                </div>
                                            </div>
                                        @elseif($isCarCamping)
                                            {{-- Car Camping: Show "Number of Vehicles" only --}}
                                            <div class="grid grid-cols-1 gap-2">
                                                <div>
                                                    <label class="text-xs text-gray-600 font-semibold">Number of Vehicles</label>
                                                    <input type="number" min="1" max="{{ $roomDetails[$staycation->id]['available_quantity'] ?? 8 }}"
                                                           class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                           :value="getRoom({{ $staycation->id }}).extra_mattress || 1"
                                                           @input="validateQuantity({{ $staycation->id }}, 'extra_mattress', $event.target.value, {{ $roomDetails[$staycation->id]['available_quantity'] ?? 8 }})"
                                                    >
                                                    <span class="text-xs text-gray-500 mt-1 block" x-text="'Remaining: ' + ({{ $roomDetails[$staycation->id]['available_quantity'] ?? 0 }} - (getRoom({{ $staycation->id }}).extra_mattress || 1))"></span>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Regular rooms: Adults, Children, Extra Mattress --}}
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="text-xs text-gray-600">Adults</label>
                                                    <input type="number" min="1" max="20"
                                                           class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                           :value="getRoom({{ $staycation->id }}).adults"
                                                           @input="validateAndSetGuests({{ $staycation->id }}, 'adults', $event.target.value, '{{ strtolower($roomDetail['name']) }}')"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-600">Children</label>
                                                    <input type="number" min="0" max="20"
                                                           class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                           :value="getRoom({{ $staycation->id }}).children"
                                                           @input="validateAndSetGuests({{ $staycation->id }}, 'children', $event.target.value, '{{ strtolower($roomDetail['name']) }}')"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-600">Extra Mattress</label>
                                                    <input type="number" min="0" max="8"
                                                           class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                           :value="getRoom({{ $staycation->id }}).extra_mattress"
                                                           @input="setField({{ $staycation->id }}, 'extra_mattress', $event.target.value)"
                                                    >
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Pet policy per room (Not applicable for BYOT and Car Camping) --}}
                                        @if(!$isBYOT && !$isCarCamping)
                                            <div class="mt-4 border-t pt-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-sm font-semibold text-gray-800">Pets</div>
                                                    <button type="button"
                                                            class="text-xs text-blue-700 underline hover:text-blue-900"
                                                            @click="petPolicyModal=true"
                                                    >
                                                        View Pet Policy
                                                    </button>
                                                </div>

                                                <div class="mt-2 flex gap-4 text-sm">
                                                    <label class="inline-flex items-center gap-2">
                                                        <input type="radio"
                                                               :name="'pets_'+{{ $staycation->id }}"
                                                               value="no"
                                                               :checked="getRoom({{ $staycation->id }}).pets === 'no'"
                                                               @change="setField({{ $staycation->id }}, 'pets', 'no')"
                                                        >
                                                        <span>No pets</span>
                                                    </label>
                                                    <label class="inline-flex items-center gap-2">
                                                        <input type="radio"
                                                               :name="'pets_'+{{ $staycation->id }}"
                                                               value="yes"
                                                               :checked="getRoom({{ $staycation->id }}).pets === 'yes'"
                                                               @change="setField({{ $staycation->id }}, 'pets', 'yes')"
                                                        >
                                                        <span>Bringing pets (+₱2,000/pet)</span>
                                                    </label>
                                                </div>

                                                <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3"
                                                     x-show="getRoom({{ $staycation->id }}).pets === 'yes'"
                                                     x-transition
                                                >
                                                    <label class="inline-flex items-start gap-2 text-sm text-blue-900">
                                                        <input type="checkbox"
                                                               class="mt-1"
                                                               :checked="getRoom({{ $staycation->id }}).pet_agreement"
                                                               @change="setField({{ $staycation->id }}, 'pet_agreement', $event.target.checked)"
                                                        >
                                                        <span>
                                                            I agree to the pet policy
                                                            <span class="block text-xs text-blue-800 mt-1">
                                                                Max 2 pets. Fee required to confirm.
                                                            </span>
                                                        </span>
                                                    </label>

                                                    <div class="mt-3">
                                                        <label class="text-xs text-gray-700">Number of pets</label>
                                                        <input type="number" min="0" max="2"
                                                               class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                               :disabled="!getRoom({{ $staycation->id }}).pet_agreement"
                                                               :value="getRoom({{ $staycation->id }}).num_pets"
                                                               @input="setField({{ $staycation->id }}, 'num_pets', $event.target.value)"
                                                        >
                                                        <div class="text-xs text-gray-600 mt-1" x-show="!getRoom({{ $staycation->id }}).pet_agreement">
                                                            Please check agreement to enable pets count.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Quick remove --}}
                                        <div class="mt-4">
                                            <button type="button"
                                                    class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-200 transition"
                                                    @click="toggleRoom({{ $staycation->id }}, '{{ $roomDetail['type'] }}')"
                                            >
                                                Remove this room
                                            </button>
                                        </div>
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
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-xl font-bold mb-6" style="font-family:'Gotham Book', sans-serif;">Booking Summary</h3>

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
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-house mr-2 text-[#8f5d54]"></i> Selected Rooms
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <template x-if="selectedCount() === 0">
                                    <div class="text-sm text-gray-600">No rooms selected yet.</div>
                                </template>

                                <template x-for="item in Object.values(selectedRooms)" :key="item.id">
                                    <div class="bg-white rounded-lg border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="font-semibold text-gray-900 text-sm" x-text="item.name"></div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    <template x-if="item.name.toLowerCase().includes('byot') || item.name.toLowerCase().includes('bring your own tent')">
                                                        <span>
                                                            BYOT Qty: <span class="font-semibold" x-text="item.extra_mattress || 1"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="item.name.toLowerCase().includes('car camping')">
                                                        <span>
                                                            Vehicles: <span class="font-semibold" x-text="item.extra_mattress || 1"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!item.name.toLowerCase().includes('byot') && !item.name.toLowerCase().includes('bring your own tent') && !item.name.toLowerCase().includes('car camping')">
                                                        <span>
                                                            Adults: <span class="font-semibold" x-text="item.adults"></span>,
                                                            Children: <span class="font-semibold" x-text="item.children"></span>,
                                                            Extra Mattress: <span class="font-semibold" x-text="item.extra_mattress"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                                <template x-if="!item.name.toLowerCase().includes('byot') && !item.name.toLowerCase().includes('bring your own tent') && !item.name.toLowerCase().includes('car camping')">
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        Pets: <span class="font-semibold" x-text="item.pets"></span>
                                                        <template x-if="item.pets === 'yes'">
                                                            <span> • # pets: <span class="font-semibold" x-text="item.num_pets"></span></span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <div class="text-xs text-gray-600 mt-2 space-y-1">
                                                    <div class="text-xs text-gray-500">Price Breakdown:</div>
                                                    <div class="pl-2 space-y-0.5">
                                                        <div>Accommodation: <span class="font-semibold text-green-600" x-text="'₱' + calculateBasePrice(item).toLocaleString()"></span></div>
                                                        <div x-show="item.extra_mattress > 0 && !item.name.toLowerCase().includes('byot') && !item.name.toLowerCase().includes('car camping') && !item.name.toLowerCase().includes('bring your own tent')">Extra Mattress: <span class="font-semibold text-green-600" x-text="'₱' + calculateMattressPrice(item).toLocaleString()"></span></div>
                                                        <div x-show="item.pets === 'yes' && item.num_pets > 0 && !item.name.toLowerCase().includes('byot') && !item.name.toLowerCase().includes('car camping') && !item.name.toLowerCase().includes('bring your own tent')">Pets: <span class="font-semibold text-green-600" x-text="'₱' + calculatePetPrice(item).toLocaleString()"></span></div>
                                                        <div class="border-t pt-1 font-semibold">Total: <span class="text-green-600" x-text="calculateRoomPrice(item)"></span></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button"
                                                    class="text-xs text-red-600 hover:text-red-800 font-semibold"
                                                    @click="removeRoom(item.id)"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Total Price --}}
                        <div class="mb-6" x-show="selectedCount() > 0">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-cash mr-2 text-[#8f5d54]"></i> Estimated Total
                            </h4>

                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="text-2xl font-bold text-green-600" x-text="calculateTotalPrice()"></div>
                                <div class="text-sm text-green-700 mt-1">Includes accommodation + extras (final price calculated at checkout)</div>
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
                            <input x-ref="calendar" type="text" class="w-full border rounded-lg px-3 py-2" readonly />
                        </div>

                        <div class="mt-4 text-sm text-gray-600">
                            <div x-show="legendLowThreshold === 0" class="text-center text-gray-500">
                                Loading availability...
                            </div>
                            <div x-show="legendLowThreshold > 0" class="grid grid-cols-2 gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded" style="background:#d4edda;"></span>
                                    <span x-text="'Available (' + (legendLowThreshold + 1) + '+ slots)'"></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded" style="background:#fff3cd;"></span>
                                    <span x-text="'Limited (1–' + legendLowThreshold + ' slots)'"></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded" style="background:#f8d7da;"></span>
                                    <span>Fully Booked (0)</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded bg-blue-500"></span>
                                    <span>Check-in Date</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded bg-blue-600"></span>
                                    <span>Check-out Date</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded bg-blue-400"></span>
                                    <span>Period of Stay</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded bg-gray-400"></span>
                                    <span>Invalid Date</span>
                                </div>
                            </div>
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
                </div>

            </div>
        </div>
    </div>

    <script>
        function staycationAvailabilityMerged() {
            return {
                roomType: 'all',
                
                // Store checkin/checkout for dynamic calculation
                checkin: '{{ $checkin }}',
                checkout: '{{ $checkout }}',

                // room details for thresholds and base capacity
                roomDetailsMap: @json($roomDetails),

                // Booking settings (pricing) from backend
                bookingSettings: {
                    mattress_price_per_night: 800,  // default
                    pet_price: 2000,                // default
                },

                // modals
                calendarModal: false,
                petPolicyModal: false,
                currentRoomId: null,
                currentRoomName: '',
                calendarInstance: null,

                // legend variables
                legendMaxQty: 0,
                legendLowThreshold: 0, // will be calculated dynamically

                // selected rooms map: { [id]: {id,name,type,adults,children,extra_mattress,pets,pet_agreement,num_pets} }
                selectedRooms: {},

                toggleRoom(roomId, type) {
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);

                    if (!room) return;

                    if (this.selectedRooms[roomId]) {
                        // deselect
                        delete this.selectedRooms[roomId];
                        return;
                    }

                    // check maximum selection limit
                    if (this.selectedCount() >= 4) {
                        Swal.fire('Maximum Selection Reached', 'You can select up to 4 accommodations only.', 'warning');
                        return;
                    }

                    // select with defaults
                    const defaultAdults = 2;
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    
                    // Create the selected room data first
                    const selectedRoomData = {
                        id: roomId,
                        name: room.name || 'Room',
                        type: (type || '').toLowerCase(),
                        adults: defaultAdults,
                        children: 0,
                        extra_mattress: (isBYOT || isCarCamping) ? 1 : 0, // Default to 1 for BYOT/Car Camping
                        pets: 'no',
                        pet_agreement: false,
                        num_pets: 0,
                    };

                    // Calculate the accommodation price using the selected room data
                    const accommodationPrice = this.calculateBasePrice(room, selectedRoomData);

                    selectedRoomData.accommodation_price = accommodationPrice; // Store the exact displayed price

                    this.selectedRooms[roomId] = selectedRoomData;
                },

                removeRoom(roomId) {
                    if (this.selectedRooms[roomId]) delete this.selectedRooms[roomId];
                },

                isSelected(roomId) {
                    return !!this.selectedRooms[roomId];
                },

                selectedCount() {
                    return Object.keys(this.selectedRooms).length;
                },

                getRoom(roomId) {
                    // for bindings
                    if (!this.selectedRooms[roomId]) {
                        return {
                            adults: 1, children: 0, extra_mattress: 0,
                            pets: 'no', pet_agreement: false, num_pets: 0
                        };
                    }
                    return this.selectedRooms[roomId];
                },

                setField(roomId, field, value) {
                    if (!this.selectedRooms[roomId]) return;

                    // normalize numbers
                    if (['adults','children','extra_mattress','num_pets'].includes(field)) {
                        value = parseInt(value || 0, 10);
                        if (!isFinite(value)) value = 0;
                        if (field === 'adults' && value < 1) value = 1;
                        if (['children','extra_mattress','num_pets'].includes(field) && value < 0) value = 0;

                        const roomNameLower = (this.selectedRooms[roomId].name || '').toLowerCase();
                        const isBYOTorCar = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent') || roomNameLower.includes('car camping');

                        // Cap pets to 2 for all non-BYOT/Car Camping; force 0 for BYOT/Car Camping
                        if (field === 'num_pets') {
                            if (isBYOTorCar) {
                                value = 0;
                            } else if (value > 2) {
                                value = 2;
                                // Modal warning for exceeding pet limit
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Pet Limit Exceeded',
                                    text: 'Maximum 2 pets allowed for this room',
                                    confirmButtonColor: '#8f5d54',
                                });
                            }
                        }
                        
                        // No hard cap for BYOT/Car Camping quantity - allow booking full available amount
                        if (field === 'extra_mattress') {
                            if (!isBYOTorCar) {
                                // Regular rooms: max 8 extra mattress
                                if (value > 8) value = 8;
                            }
                            // For BYOT/Car Camping, no cap here - validateQuantity handles the limit
                            if (value < 1) value = 1;
                        }
                    }

                    this.selectedRooms[roomId][field] = value;

                    // Update accommodation_price whenever a field changes that affects pricing
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);
                    if (room) {
                        this.selectedRooms[roomId].accommodation_price = this.calculateBasePrice(room, this.selectedRooms[roomId]);
                    }

                    // pet logic
                    if (field === 'pets' && value === 'no') {
                        this.selectedRooms[roomId].pet_agreement = false;
                        this.selectedRooms[roomId].num_pets = 0;
                    }
                    if (field === 'pet_agreement' && value === false) {
                        this.selectedRooms[roomId].num_pets = 0;
                    }
                },

                validateAndSetGuests(roomId, field, value, roomName) {
                    const normalizedValue = parseInt(value || 0, 10);
                    if (!isFinite(normalizedValue)) {
                        this.setField(roomId, field, 0);
                        return;
                    }

                    // Get total guests for this room
                    const currentRoom = this.getRoom(roomId);
                    let adults = field === 'adults' ? normalizedValue : (currentRoom.adults || 1);
                    let children = field === 'children' ? normalizedValue : (currentRoom.children || 0);
                    const totalGuests = adults + children;

                    // Check guest limit for standard and premium
                    if ((roomName.includes('standard') || roomName.includes('premium')) && totalGuests > 6) {
                        Swal.fire({
                            title: 'Guest Limit Exceeded',
                            text: 'Maximum 6 guests (adults + children) allowed for Standard and Premium rooms',
                            icon: 'warning',
                            confirmButtonColor: '#8f5d54'
                        });
                        // Revert to previous value
                        return;
                    }

                    // Set the field normally
                    this.setField(roomId, field, normalizedValue);
                },

                validateAdultLimit(roomId, value, maxAdults) {
                    const normalizedValue = parseInt(value || 0, 10);
                    if (!isFinite(normalizedValue)) {
                        this.setField(roomId, 'adults', 1);
                        return;
                    }

                    if (normalizedValue > maxAdults) {
                        Swal.fire({
                            title: 'Adult Limit Exceeded',
                            text: `Maximum ${maxAdults} adults allowed for this accommodation`,
                            icon: 'warning',
                            confirmButtonColor: '#8f5d54'
                        });
                        this.setField(roomId, 'adults', maxAdults);
                        return;
                    }

                    if (normalizedValue < 1) {
                        this.setField(roomId, 'adults', 1);
                        return;
                    }

                    this.setField(roomId, 'adults', normalizedValue);
                },

                validateQuantity(roomId, fieldName, value, maxQuantity) {
                    const normalizedValue = parseInt(value || 1, 10);
                    if (!isFinite(normalizedValue)) {
                        this.setField(roomId, fieldName, 1);
                        return;
                    }

                    if (normalizedValue > maxQuantity) {
                        Swal.fire({
                            title: 'Availability Limit Exceeded',
                            text: `Only ${maxQuantity} available for this accommodation during the selected dates.`,
                            icon: 'warning',
                            confirmButtonColor: '#8f5d54'
                        });
                        this.setField(roomId, fieldName, maxQuantity);
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
                },

                showCalendar(type, roomId) {
                    this.currentRoomId = roomId;
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);
                    this.currentRoomName = room ? room.name : 'Room';

                    this.calendarModal = true;
                    this.$nextTick(() => this.initCalendar());
                },

                initCalendar() {
                    if (this.calendarInstance) this.calendarInstance.destroy();

                    const checkin = '{{ request('checkin') ?? ($checkin ?? '') }}';
                    const checkout = '{{ request('checkout') ?? ($checkout ?? '') }}';

                    if (!this.$refs.calendar) {
                        console.error('Calendar element not found');
                        return;
                    }

                    const roomId = this.currentRoomId;
                    const roomInfo = this.roomDetailsMap && this.roomDetailsMap[roomId] ? this.roomDetailsMap[roomId] : null;
                    const baseQty = Math.max(0, parseInt((roomInfo && roomInfo.total_quantity) ? roomInfo.total_quantity : 0, 10));

                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // reset to start of today
                    
                    const fmt = (d) => {
                        const y = d.getFullYear();
                        const m = String(d.getMonth()+1).padStart(2,'0');
                        const da = String(d.getDate()).padStart(2,'0');
                        return `${y}-${m}-${da}`;
                    };

                    // Fetch per-room inventory (next 30 days). We'll fallback to baseQty for other dates.
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

                        // Threshold: 30% of base capacity (fallback to max from inventory if baseQty missing)
                        let legendMax = baseQty;
                        if (!legendMax) {
                            for (const q of Object.values(invMap)) legendMax = Math.max(legendMax, q || 0);
                        }
                        this.legendMaxQty = legendMax;
                        this.legendLowThreshold = legendMax > 0 ? Math.max(1, Math.floor(legendMax * 0.3)) : 1;

                        // Create calendar AFTER inventory data is loaded
                        this.calendarInstance = flatpickr(this.$refs.calendar, {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            defaultDate: [checkin, checkout],
                            inline: true,
                            // Disable past dates and zero-quantity dates
                            disable: [
                                (date) => {
                                    // Check if date is in the past
                                    const checkDate = new Date(date);
                                    checkDate.setHours(0, 0, 0, 0);
                                    if (checkDate < today) return true; // past date = disabled
                                    
                                    // Check inventory
                                    const ds = fmt(date);
                                    const qty = (ds in invMap) ? invMap[ds] : baseQty;
                                    return (qty || 0) === 0; // zero quantity = disabled
                                }
                            ],
                            onDayCreate: (selectedDates, dateStr, instance, dayElem) => {
                                const date = dayElem.dateObj;
                                const ds = instance.formatDate(date, 'Y-m-d');
                                dayElem.style.borderRadius = '6px';

                                // Check if past
                                const checkDate = new Date(date);
                                checkDate.setHours(0, 0, 0, 0);
                                const isPast = checkDate < today;

                                const qty = (ds in invMap) ? invMap[ds] : baseQty;

                                // Past or zero = invalid (gray)
                                if (isPast || (qty || 0) === 0) {
                                    dayElem.style.background = '#d3d3d3';
                                    dayElem.style.color = '#6c757d';
                                    return;
                                }

                                // Limited or available
                                if (qty <= this.legendLowThreshold) {
                                    dayElem.style.background = '#fff3cd';
                                    dayElem.style.color = '#856404';
                                } else {
                                    dayElem.style.background = '#d4edda';
                                    dayElem.style.color = '#155724';
                                }

                                // Create availability badge
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

                                // Color coding based on availability
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
                        // Fallback: no API data; render calendar with baseQty only
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

                calculateBasePrice(room, selectedRoomData = null) {
                    // Calculate nights dynamically from stored checkin/checkout
                    let nights = 1;
                    if (this.checkin && this.checkout) {
                        const checkinDate = new Date(this.checkin);
                        const checkoutDate = new Date(this.checkout);
                        nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));
                    }

                    // Get room details from roomDetailsMap which includes dynamic pricing from admin panel
                    const roomDetail = this.roomDetailsMap && this.roomDetailsMap[room.id] ? this.roomDetailsMap[room.id] : null;

                    // Use the exact base price from admin panel without modifications
                    let basePrice = (roomDetail && roomDetail.price) ? roomDetail.price : 0;

                    // If no price from admin, use type-based default
                    if (basePrice === 0) {
                        basePrice = room.type === 'glamping' ? 5500 : 3000;
                    }

                    // Check if BYOT or Car Camping (per quantity pricing)
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');

                    let totalPrice = 0;

                    if (isBYOT || isCarCamping) {
                        // Per quantity pricing: base price is per night per unit
                        const quantity = selectedRoomData ? parseInt(selectedRoomData.extra_mattress || 1) : 1;
                        const correctedBasePrice = basePrice;
                        totalPrice = correctedBasePrice * quantity * nights;
                    } else {
                        // Regular rooms: base price is per night
                        totalPrice = basePrice * nights;
                    }

                    return totalPrice;
                },

                calculateMattressPrice(room) {
                    // Calculate nights dynamically from stored checkin/checkout
                    let nights = 1;
                    if (this.checkin && this.checkout) {
                        const checkinDate = new Date(this.checkin);
                        const checkoutDate = new Date(this.checkout);
                        nights = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)));
                    }
                    
                    // Check if BYOT or Car Camping
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    
                    // For BYOT/Car Camping, extra_mattress field stores quantity (tents/vehicles)
                    // No additional charge for this
                    if (isBYOT || isCarCamping) {
                        return 0;
                    }
                    
                    // Regular rooms: mattress pricing
                    const price = (room.extra_mattress || 0) * this.bookingSettings.mattress_price_per_night * nights;
                    return price;
                },

                calculatePetPrice(room) {
                    if (room.pets !== 'yes' || !room.num_pets) return 0;
                    
                    // Check if BYOT or Car Camping - no pet policy for these
                    const roomNameLower = (room.name || '').toLowerCase();
                    const isBYOT = roomNameLower.includes('byot') || roomNameLower.includes('bring your own tent');
                    const isCarCamping = roomNameLower.includes('car camping');
                    
                    if (isBYOT || isCarCamping) {
                        return 0;
                    }
                    
                    // Pet fee is ONE-TIME, not per night - uses dynamic setting
                    const price = room.num_pets * this.bookingSettings.pet_price;
                    return price;
                },

                calculateRoomPrice(room) {
                    const selectedRoom = this.selectedRooms[room.id];
                    const base = this.calculateBasePrice(room, selectedRoom);
                    const mattress = this.calculateMattressPrice(selectedRoom || room);
                    const pet = this.calculatePetPrice(selectedRoom || room);

                    const total = base + mattress + pet;
                    return '₱' + total.toLocaleString();
                },

                calculateTotalPrice() {
                    let total = 0;
                    for (const room of Object.values(this.selectedRooms)) {
                        // Use the stored accommodation_price for accuracy
                        const base = room.accommodation_price || 0;
                        const mattress = this.calculateMattressPrice(room);
                        const pet = this.calculatePetPrice(room);
                        total += base + mattress + pet;
                    }
                    return '₱' + total.toLocaleString();
                },

                generateLegend() {
                    // Don't show legend until threshold is calculated
                    if (this.legendLowThreshold === 0) {
                        return '<div class="text-center text-gray-500">Loading availability...</div>';
                    }
                    
                    return `
                        <div class="grid grid-cols-2 gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded" style="background:#d4edda;"></span>
                                <span>Available (${this.legendLowThreshold + 1}+ slots)</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded" style="background:#fff3cd;"></span>
                                <span>Limited (1–${this.legendLowThreshold} slots)</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded" style="background:#f8d7da;"></span>
                                <span>Fully Booked (0)</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded bg-blue-500"></span>
                                <span>Check-in Date</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded bg-blue-600"></span>
                                <span>Check-out Date</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded bg-blue-400"></span>
                                <span>Period of Stay</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded bg-gray-400"></span>
                                <span>Invalid Date</span>
                            </div>
                        </div>
                    `;
                },

                fetchBookingSettings() {
                    fetch('/api/booking/settings')
                        .then(response => response.json())
                        .then(data => {
                            // Update booking settings from backend
                            if (data.mattress_price_per_night !== undefined) {
                                this.bookingSettings.mattress_price_per_night = data.mattress_price_per_night;
                            }
                            if (data.pet_price !== undefined) {
                                this.bookingSettings.pet_price = data.pet_price;
                            }
                            console.log('Booking settings loaded:', this.bookingSettings);
                        })
                        .catch(error => console.error('Failed to fetch booking settings:', error));
                },

                init() {
                    // Fetch booking settings from backend
                    this.fetchBookingSettings();
                    
                    this.$nextTick(() => {
                        @foreach($staycations as $staycation)
                            @if($staycation->swiperImages && $staycation->swiperImages->count() > 1)
                                new Swiper('.staycation-swiper-{{ $staycation->id }}', {
                                    loop: true,
                                    pagination: {
                                        el: '.swiper-pagination-{{ $staycation->id }}',
                                        clickable: true,
                                    },
                                    navigation: {
                                        nextEl: '.swiper-button-next-{{ $staycation->id }}',
                                        prevEl: '.swiper-button-prev-{{ $staycation->id }}',
                                    },
                                    autoplay: {
                                        delay: 3000,
                                        disableOnInteraction: false,
                                    },
                                });
                            @endif
                        @endforeach
                    });
                },

                submitAndProceed(nextUrl = '{{ route("booking.staycationFood") }}') {
                    if (this.selectedCount() === 0) {
                        Swal.fire('Select at least one room', 'Please select a room before continuing.', 'warning');
                        return;
                    }

                    const targetUrl = nextUrl || '{{ route("booking.staycationFood") }}';

                    // validation: pet agreement required if pets yes and num pets > 0
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
                        if (!r.ok) {
                            throw new Error(`HTTP error! status: ${r.status}`);
                        }
                        return r.json();
                    })
                    .then(data => {
                        if (data.success) {
                            window.location.href = targetUrl;
                        } else {
                            Swal.fire('Error', data.message || 'Failed to save selection', 'error');
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Network error saving selection: ' + error.message, 'error');
                    });
                }
            }
        }

        // Real-time inventory updates
        @if(config('broadcasting.default') !== 'null')
        window.Echo.channel('inventory')
            .listen('.inventory.updated', (e) => {
                console.log('Inventory updated:', e);

                // If calendar is open and matches the updated staycation
                if (this.calendarModal && this.currentRoomId === e.staycationId) {
                    this.initCalendar();
                }

                // Update room card availability (optional but recommended)
                updateRoomAvailability(e.staycationId);
            });
        @endif

        // Real-time pricing updates
        @if(config('broadcasting.default') !== 'null')
        window.Echo.channel('pricing')
            .listen('.pricing.updated', (e) => {
                console.log('Pricing updated:', e);
                // Fetch updated prices dynamically
                fetch('/api/booking/staycation/prices?checkin={{ $checkin }}&checkout={{ $checkout }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update roomDetailsMap with new prices
                            Object.keys(data.prices).forEach(staycationId => {
                                if (this.roomDetailsMap && this.roomDetailsMap[staycationId]) {
                                    this.roomDetailsMap[staycationId].price = data.prices[staycationId].price;
                                }
                            });

                            // Update selected rooms prices
                            Object.keys(this.selectedRooms).forEach(roomId => {
                                if (this.selectedRooms[roomId] && this.roomDetailsMap[roomId]) {
                                    this.selectedRooms[roomId].accommodation_price = this.calculateBasePrice(
                                        { id: roomId, name: this.selectedRooms[roomId].name, type: this.selectedRooms[roomId].type },
                                        this.selectedRooms[roomId]
                                    );
                                }
                            });

                            // Show notification
                            Swal.fire({
                                icon: 'success',
                                title: 'Prices Updated',
                                text: 'Room prices have been updated in real-time.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Failed to fetch updated prices:', error);
                        // Fallback to reload
                        window.location.reload();
                    });
            });
        @endif

        function updateRoomAvailability(staycationId) {
            fetch(`/api/inventory/${staycationId}`)
                .then(response => response.json())
                .then(data => {
                    // Find the room card
                    const roomCard = document.querySelector(`[data-room-id="${staycationId}"]`);
                    if (!roomCard) return;

                    // Extract min quantity from new data
                    const items = data.items || [];
                    const minQty = items.length > 0 
                        ? Math.min(...items.map(item => item.min_quantity)) 
                        : 0;
                    
                    // Update availability badge
                    const availabilityBadge = roomCard.querySelector('.availability-badge');
                    if (availabilityBadge) {
                        availabilityBadge.textContent = `${minQty} available`;
                        
                        // Update status class (available/limited/unavailable)
                        if (minQty === 0) {
                            availabilityBadge.className = 'availability-badge bg-gray-500 text-white';
                        } else if (minQty <= 2) {
                            availabilityBadge.className = 'availability-badge bg-yellow-500 text-white';
                        } else {
                            availabilityBadge.className = 'availability-badge bg-green-500 text-white';
                        }
                    }
                })
                .catch(error => console.error('Failed to fetch inventory:', error));
        }

    </script>
</x-app-layout>

{{-- resources/views/booking/staycation-food.blade.php --}}
<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <style>
        [x-cloak]{display:none!important;}

        /* base rounding */
        .flatpickr-day { border-radius: 6px; }

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

    @php
    // Calculate accommodation total from stored room prices
    $accommodationTotal = 0;
    foreach ($booking['accommodations'] ?? [] as $roomId => $acc) {
        if (!empty($acc)) {
            $accommodationTotal += $acc['total_price'] ?? 0;
        }
    }
    @endphp

    <div class="bg-[#f5efe7] min-h-screen">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight" style="font-family:'Gotham Book', sans-serif;">Select Food & Beverages</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-xs text-gray-500" style="font-family:'Gotham Book', sans-serif;">
                    Add food and beverages to your staycation (optional)
                </p>
            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <x-booking.progress-indicator :stepNumber="3" :totalSteps="5" :bookingType="'staycation'" />
            </div>

            {{-- Back + Guest mode --}}
            <div class="flex items-end justify-between mb-6">
                <a href="{{ route('booking.staycationAvailability') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Back to Rooms
                </a>

                @if (session()->has('user.is_guest') && session('user.is_guest'))
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                          style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                        <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                    </span>
                @endif
            </div>

            <div
                x-data="staycationFoodSelection({{ $accommodationTotal }})"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2">
                    {{-- Filter --}}
                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="foodCategory" class="block text-sm font-medium text-gray-700 mb-2" style="font-family:'Gotham Book', sans-serif;">Filter by:</label>
                            <select id="foodCategory" x-model="selectedCategory"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Items</option>
                                <option value="special">Special Options</option>
                                <option value="breakfast">Breakfast</option>
                                <option value="lunch">Lunch</option>
                                <option value="dinner">Dinner</option>
                                <option value="package">Packages</option>
                            </select>
                        </div>

                        <button type="button"
                                class="ml-auto px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="selectedFoods.length === 0"
                        >
                            Reset All
                        </button>
                    </div>

                    {{-- Food Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Special Options (like Cook Your Own) --}}
                        @forelse($foodMenus->where('is_special_option', true) as $special)
                            @php
                                $category = $special->category ?? 'special';
                                $specialColor = match($special->name) {
                                    'Cook Your Own' => ['bg' => 'from-green-50 to-emerald-50', 'ring' => 'ring-green-200 hover:ring-green-400', 'border' => 'border-green-300', 'btn' => 'bg-green-600 hover:bg-green-700', 'badge' => 'bg-green-600', 'icon' => 'bi-fire'],
                                    default => ['bg' => 'from-blue-50 to-cyan-50', 'ring' => 'ring-blue-200 hover:ring-blue-400', 'border' => 'border-blue-300', 'btn' => 'bg-blue-600 hover:bg-blue-700', 'badge' => 'bg-blue-600', 'icon' => 'bi-star']
                                };
                            @endphp
                            <div
                                class="relative bg-linear-to-br rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl ring-2 cursor-pointer"
                                :class="'{{ $specialColor['bg'] }} {{ $specialColor['ring'] }} {{ $specialColor['border'] }}'"
                                @click="toggleFood({{ $special->id }}, '{{ $special->name }}', '{{ $category }}', {{ $special->price }})"
                                x-show="selectedCategory === 'all' || selectedCategory === 'special'"
                            >
                                {{-- Selected indicator --}}
                                <div class="absolute top-2 left-2 z-10" x-show="isSelected({{ $special->id }})" x-transition>
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center" :style="'background: ' + ({{ $special->id }} === {{ $special->id }} ? '#16a34a' : '#0284c7')">
                                        <i class="bi bi-check text-white text-sm"></i>
                                    </div>
                                </div>

                                {{-- Image --}}
                                <div class="relative overflow-hidden h-96 flex items-center justify-center bg-gray-100">
                                    @php
                                        $hasMultipleSwiperImages = $special->swiperImages && $special->swiperImages->count() > 1;
                                        $hasSingleSwiperImage = $special->swiperImages && $special->swiperImages->count() == 1;
                                    @endphp

                                    @if($hasMultipleSwiperImages)
                                        {{-- Multiple swiper images - carousel --}}
                                        <div class="swiper special-swiper-{{ $special->id }}">
                                            <div class="swiper-wrapper">
                                                @foreach($special->swiperImages as $image)
                                                    <div class="swiper-slide">
                                                        <img src="{{ $image->image_path }}" alt="{{ $image->title ?? $special->name }}" class="w-full h-96 object-cover">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-pagination swiper-pagination-special-{{ $special->id }}"></div>
                                            <div class="swiper-button-next swiper-button-next-special-{{ $special->id }}"></div>
                                            <div class="swiper-button-prev swiper-button-prev-special-{{ $special->id }}"></div>
                                        </div>
                                    @elseif($hasSingleSwiperImage)
                                        {{-- Single swiper image --}}
                                        <img src="{{ $special->swiperImages->first()->image_path }}" alt="{{ $special->swiperImages->first()->title ?? $special->name }}" class="w-full h-96 object-cover">
                                    @elseif($special->image)
                                        {{-- Main food image --}}
                                        <img src="{{ $special->image }}" alt="{{ $special->name }}" class="w-full h-96 object-cover">
                                    @else
                                        {{-- Fallback to icon if no image --}}
                                        <div class="text-center" :class="'bg-linear-to-br {{ $specialColor['bg'] }}'">
                                            <i class="bi {{ $specialColor['icon'] }} text-6xl mb-4 block" :class="'{{ $specialColor['icon'] === 'bi-fire' ? 'text-green-600' : 'text-blue-600' }}'"></i>
                                            <p class="font-semibold" :class="'{{ $specialColor['icon'] === 'bi-fire' ? 'text-green-700' : 'text-blue-700' }}'">{{ $special->name }}</p>
                                        </div>
                                    @endif
                                    
                                    {{-- Category badge --}}
                                    <div class="absolute bottom-4 left-4 z-10">
                                        <span class="px-3 py-1 text-white text-sm font-semibold rounded-full" :class="'{{ $specialColor['badge'] }}'">
                                            Special
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-bold mb-2" style="font-family:'Gotham Book', sans-serif;">
                                        {{ $special->name }}
                                    </h3>

                                    <div class="mb-3">
                                        <span class="text-lg font-bold" :class="{{ $special->price }} === 0 ? 'text-green-600' : 'text-[#8f5d54]'">
                                            {{ $special->price === 0 ? 'FREE' : '₱' . number_format($special->price, 0) }}
                                        </span>
                                    </div>

                                    <p class="text-gray-600 mb-4 text-sm">
                                        {{ $special->description ?? 'Special option for your staycation' }}
                                    </p>

                                    <button type="button"
                                            class="w-full py-2 rounded-lg font-semibold transition text-white"
                                            :class="isSelected({{ $special->id }}) ? '{{ $specialColor['btn'] }}' : 'bg-gray-400 hover:bg-gray-500'"
                                            @click.stop="toggleFood({{ $special->id }}, '{{ $special->name }}', '{{ $category }}', {{ $special->price }})">
                                        <i :class="isSelected({{ $special->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
                                        <span x-text="isSelected({{ $special->id }}) ? 'Selected' : 'Select Option'"></span>
                                    </button>
                                </div>
                            </div>
                        @empty
                        @endforelse

                        @forelse($foodMenus->where('is_special_option', false) as $food)
                            @php
                                $category = $food->category ?? 'package';
                            @endphp

                            <div
                                class="relative bg-white rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl ring-2 ring-gray-200 hover:ring-[#8f5d54] cursor-pointer"
                                data-category="{{ $category }}"
                                x-show="selectedCategory === 'all' || selectedCategory === '{{ $category }}'"
                                @click="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})"
                            >
                                {{-- Selected indicator --}}
                                <div class="absolute top-2 left-2 z-10" x-show="isSelected({{ $food->id }})" x-transition>
                                    <div class="w-6 h-6 bg-[#8f5d54] rounded-full flex items-center justify-center">
                                        <i class="bi bi-check text-white text-sm"></i>
                                    </div>
                                </div>

                                {{-- Image --}}
                                <div class="relative bg-gray-200 overflow-hidden">
                                    @php
                                        $hasMultipleSwiperImages = $food->swiperImages && $food->swiperImages->count() > 1;
                                        $hasSingleSwiperImage = $food->swiperImages && $food->swiperImages->count() == 1;
                                    @endphp
                                    
                                    @if($hasMultipleSwiperImages)
                                        {{-- Multiple swiper images - carousel --}}
                                        <div class="swiper food-swiper-{{ $food->id }}">
                                            <div class="swiper-wrapper">
                                                @foreach($food->swiperImages as $image)
                                                    <div class="swiper-slide">
                                                        <img src="{{ $image->image_path }}" alt="{{ $image->title ?? $food->name }}" class="w-full h-96 object-cover">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-pagination swiper-pagination-{{ $food->id }}"></div>
                                            <div class="swiper-button-next swiper-button-next-{{ $food->id }}"></div>
                                            <div class="swiper-button-prev swiper-button-prev-{{ $food->id }}"></div>
                                        </div>
                                    @elseif($hasSingleSwiperImage)
                                        {{-- Single swiper image --}}
                                        <img src="{{ $food->swiperImages->first()->image_path }}" alt="{{ $food->swiperImages->first()->title ?? $food->name }}" class="w-full h-96 object-cover">
                                    @elseif($food->image)
                                        {{-- Main food image --}}
                                        <img src="{{ $food->image }}" alt="{{ $food->name }}" class="w-full h-96 object-cover">
                                    @else
                                        {{-- Placeholder --}}
                                        <div class="w-full h-96 bg-linear-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                                            <i class="bi bi-image text-6xl text-gray-300"></i>
                                        </div>
                                    @endif

                                    {{-- Category badge --}}
                                    <div class="absolute bottom-4 left-4 z-10">
                                        <span class="px-3 py-1 bg-[#8f5d54] text-white text-sm font-semibold rounded-full">
                                            {{ ucfirst($category) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-bold mb-2" style="font-family:'Gotham Book', sans-serif;">
                                        {{ $food->name }}
                                    </h3>

                                    <div class="mb-3">
                                        <span class="text-lg font-bold text-[#8f5d54]">
                                            ₱{{ number_format($food->price ?? 0, 0, ',', ',') }}
                                        </span>
                                    </div>

                                    <p class="text-gray-600 mb-4 text-sm">
                                        {{ $food->description ?? 'No description available' }}
                                    </p>

                                    <button type="button"
                                            class="w-full py-2 rounded-lg font-semibold transition"
                                            :class="isSelected({{ $food->id }}) ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                            @click.stop="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})">
                                        <i :class="isSelected({{ $food->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
                                        <span x-text="isSelected({{ $food->id }}) ? 'Added to Cart' : 'Add to Cart'"></span>
                                    </button>
                                </div>
                            </div>

                        @empty
                            <div class="col-span-full text-center py-12 bg-white rounded-2xl shadow border border-gray-200">
                                <div class="text-gray-500">
                                    <i class="bi bi-cup-straw text-6xl mb-4 block text-gray-300"></i>
                                    <h3 class="text-2xl font-semibold mb-2 text-gray-700">No Food Available</h3>
                                    <p class="text-gray-500 mb-4">No food items are currently available for selection.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- RIGHT SIDEBAR --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 lg:sticky lg:top-6 space-y-6">
                        {{-- Selected Dates Section --}}
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-calendar-check mr-2 text-[#8f5d54]"></i> Selected Dates
                            </h4>
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                                @php
                                    $checkin = $booking['checkin'] ?? null;
                                    $checkout = $booking['checkout'] ?? null;
                                @endphp
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Check-in:</span>
                                    <span class="font-semibold">{{ $checkin ? \Carbon\Carbon::parse($checkin)->format('M j, Y') : '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Check-out:</span>
                                    <span class="font-semibold">{{ $checkout ? \Carbon\Carbon::parse($checkout)->format('M j, Y') : '—' }}</span>
                                </div>
                                @if($checkin && $checkout)
                                    <div class="flex justify-between border-t border-gray-200 pt-2">
                                        <span class="text-gray-600">Total Nights:</span>
                                        <span class="font-semibold text-[#8f5d54]">{{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Selected Rooms Section --}}
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-house mr-2 text-[#8f5d54]"></i> Selected Rooms
                            </h4>
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                @php
                                    $accommodations = $booking['accommodations'] ?? [];
                                    $roomIds = array_keys($accommodations);
                                @endphp

                                @if(count($accommodations) > 0)
                                    @foreach($roomIds as $roomId)
                                        @php
                                            $room = $accommodations[$roomId];
                                            $staycation = $staycations->find($roomId);
                                            $roomNameLower = strtolower($staycation->name ?? '');
                                            $isBYOT = str_contains($roomNameLower, 'byot') || str_contains($roomNameLower, 'bring your own tent');
                                            $isCarCamping = str_contains($roomNameLower, 'car camping');
                                        @endphp
                                        <div class="bg-white rounded border border-gray-200 p-3 text-xs">
                                            <div class="font-semibold text-gray-900 mb-2">{{ $staycation->name ?? 'Room #' . $roomId }}</div>
                                            <div class="text-gray-600 space-y-1">
                                                @if($isBYOT)
                                                    <div>BYOT Qty: <span class="font-semibold">{{ $room['extra_mattress'] ?? 1 }}</span>, Adults: <span class="font-semibold">{{ $room['adults'] ?? 1 }}</span>, Children: <span class="font-semibold">{{ $room['children'] ?? 0 }}</span></div>
                                                @elseif($isCarCamping)
                                                    <div>Vehicles: <span class="font-semibold">{{ $room['extra_mattress'] ?? 1 }}</span>, Adults: <span class="font-semibold">{{ $room['adults'] ?? 1 }}</span>, Children: <span class="font-semibold">{{ $room['children'] ?? 0 }}</span></div>
                                                @else
                                                    <div>Adults: <span class="font-semibold">{{ $room['adults'] ?? 1 }}</span>, Children: <span class="font-semibold">{{ $room['children'] ?? 0 }}</span></div>
                                                    <div>Mattress: <span class="font-semibold">{{ $room['extra_mattress'] ?? 0 }}</span></div>
                                                @endif
                                                @if(!$isBYOT && !$isCarCamping)
                                                    <div>Pets: <span class="font-semibold">{{ $room['pets'] ?? 'no' }}</span>
                                                        @if(($room['pets'] ?? 'no') === 'yes')
                                                            <span>(#{{ $room['num_pets'] ?? 0 }})</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="border-t border-gray-200 mt-2 pt-2 space-y-1">
                                                @if($isBYOT || $isCarCamping)
                                                    {{-- For BYOT/Car Camping, show breakdown: unit price × quantity × nights --}}
                                                    @php
                                                        $quantity = $room['extra_mattress'] ?? 1;
                                                        $checkin = !empty($booking['checkin']) ? \Carbon\Carbon::parse($booking['checkin']) : null;
                                                        $checkout = !empty($booking['checkout']) ? \Carbon\Carbon::parse($booking['checkout']) : null;
                                                        $nights = $checkin && $checkout ? max(1, $checkout->diffInDays($checkin)) : 1;
                                                        $unitPrice = $room['base_price'] > 0 && $nights > 0 ? ($room['base_price'] / $quantity / $nights) : 0;
                                                    @endphp
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Unit Price/Night:</span>
                                                        <span>₱{{ number_format($unitPrice, 0) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Quantity:</span>
                                                        <span>{{ $quantity }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Nights:</span>
                                                        <span>{{ $nights }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-xs border-t pt-1 mt-1">
                                                        <span class="text-gray-600">Calculation:</span>
                                                        <span>₱{{ number_format($unitPrice, 0) }} × {{ $quantity }} × {{ $nights }}</span>
                                                    </div>
                                                @else
                                                    {{-- For regular rooms --}}
                                                    @php
                                                        $checkin = !empty($booking['checkin']) ? \Carbon\Carbon::parse($booking['checkin']) : null;
                                                        $checkout = !empty($booking['checkout']) ? \Carbon\Carbon::parse($booking['checkout']) : null;
                                                        $nights = $checkin && $checkout ? max(1, $checkout->diffInDays($checkin)) : 1;
                                                        $baseNightlyPrice = $room['base_price'] > 0 && $nights > 0 ? ($room['base_price'] / $nights) : 0;
                                                    @endphp
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Price/Night:</span>
                                                        <span>₱{{ number_format($baseNightlyPrice, 0) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600">Nights:</span>
                                                        <span>{{ $nights }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-xs border-t pt-1 mt-1">
                                                        <span class="text-gray-600">Room Subtotal:</span>
                                                        <span>₱{{ number_format($baseNightlyPrice * $nights, 0) }}</span>
                                                    </div>
                                                @endif
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Room:</span>
                                                    <span class="font-semibold text-gray-900">₱{{ number_format($room['base_price'] ?? 0, 0) }}</span>
                                                </div>
                                                @if(($room['mattress_price'] ?? 0) > 0 && !$isBYOT && !$isCarCamping)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Mattress:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($room['mattress_price'] ?? 0, 0) }}</span>
                                                    </div>
                                                @endif
                                                @if(($room['pet_price'] ?? 0) > 0 && !$isBYOT && !$isCarCamping)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Pets:</span>
                                                        <span class="font-semibold text-gray-900">₱{{ number_format($room['pet_price'] ?? 0, 0) }}</span>
                                                    </div>
                                                @endif
                                                <div class="flex justify-between border-t border-gray-200 pt-1">
                                                    <span class="font-semibold text-gray-800">Total:</span>
                                                    <span class="font-bold text-[#8f5d54]">₱{{ number_format($room['total_price'] ?? 0, 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-xs">No rooms selected</p>
                                @endif
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Order Summary --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800 flex items-center">
                                    <i class="bi bi-cart mr-2 text-[#8f5d54]"></i> Order Summary
                                </h4>
                                <button type="button" class="text-xs font-semibold text-gray-500 hover:text-gray-800" @click="resetAll()">Reset</button>
                            </div>

                            {{-- Accommodation Total --}}
                            <div class="bg-gray-50 rounded-lg p-3 mb-4 space-y-2 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-700">Accommodation:</span>
                                    <span class="font-bold text-[#8f5d54]">₱{{ number_format($accommodationTotal, 0) }}</span>
                                </div>
                            </div>

                            <template x-if="selectedFoods.length === 0">
                                <div class="text-center text-gray-500 py-6">
                                    <i class="bi bi-clipboard text-4xl mb-2 block text-gray-300"></i>
                                    <p class="text-sm">No items selected yet.</p>
                                </div>
                            </template>

                            <template x-if="selectedFoods.length > 0">
                                <div class="space-y-4">
                                    <template x-for="food in selectedFoods" :key="food.id">
                                <div class="border rounded-xl p-4 shadow-sm" :class="food.id === 'cook-your-own' ? 'border-green-200 bg-green-50' : 'border-gray-200'">
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold" x-text="food.name"></p>
                                            <p class="text-xs text-gray-500 capitalize" :class="food.category === 'special' ? 'text-green-600' : ''" x-text="food.category"></p>
                                            <p class="text-sm font-bold mt-1" :class="food.price === 0 ? 'text-green-600' : 'text-[#8f5d54]'" x-text="food.price === 0 ? 'FREE' : '₱' + Number(food.price).toLocaleString()"></p>
                                        </div>
                                        <button type="button" class="text-gray-400 hover:text-red-600 transition" @click="removeFood(food.id)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    {{-- Quantity controls only for paid items --}}
                                    <template x-if="food.id !== 'cook-your-own' && food.category !== 'special'">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" @click="decreaseQty(food.id)">-</button>
                                                <input type="number" min="1" max="20" class="w-16 text-center border border-gray-300 rounded-md py-1 text-sm" :value="food.quantity" @change="setField(food.id, 'quantity', $event.target.value)">
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" @click="increaseQty(food.id)">+</button>
                                            </div>
                                            <div class="text-right text-[#8f5d54] font-bold text-sm" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
                                        </div>

                                        <div class="mt-3">
                                            <textarea rows="2" class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54] placeholder-gray-400" placeholder="Notes or dietary preferences..." :value="food.notes || ''" @input="setField(food.id, 'notes', $event.target.value)"></textarea>
                                        </div>
                                    </template>

                                    {{-- Special message for special options --}}
                                    <template x-if="food.category === 'special' || food.id === 'cook-your-own'">
                                        <div class="mt-3 p-3 rounded-lg border" :class="food.name === 'Cook Your Own' ? 'bg-green-100 border-green-300' : 'bg-blue-100 border-blue-300'">
                                            <p class="text-xs" :class="food.name === 'Cook Your Own' ? 'text-green-700' : 'text-blue-700'"><i class="bi bi-info-circle mr-1"></i><span x-text="food.name === 'Cook Your Own' ? 'You\'ll have access to a fully equipped kitchen. No additional charge.' : 'Special option selected. No additional charge.'"></span></p>
                                        </div>
                                    </template>
                                    </template>

                                    <div class="border-t pt-4 flex items-center justify-between">
                                        <span class="text-sm font-semibold">Grand Total</span>
                                        <span class="text-lg font-bold text-[#8f5d54]" x-text="calculateTotal()"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <hr class="border-gray-200">

                        <div class="flex flex-col gap-3">
                            <button type="button"
                                    class="w-full py-3 rounded-xl font-semibold text-white bg-[#8f5d54] hover:bg-[#7a4e48] transition"
                                    @click="submitAndProceed('{{ route('booking.staycationActivities') }}')">
                                <i class="bi bi-arrow-right mr-2"></i>
                                Proceed to Activities
                            </button>
                            <button type="button"
                                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 transition"
                                    @click="submitAndProceed('{{ route('booking.staycationPayment') }}')">
                                <i class="bi bi-credit-card mr-2"></i>
                                Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function staycationFoodSelection(accommodationTotal = 0) {
            return {
                selectedFoods: [],
                selectedCategory: 'all',
                accommodationTotal: accommodationTotal,

                toggleFood(foodId, name, category, price) {
                    const index = this.selectedFoods.findIndex(f => f.id === foodId);
                    if (index > -1) {
                        this.selectedFoods.splice(index, 1);
                        return;
                    }
                    this.selectedFoods.push({
                        id: foodId,
                        name: name,
                        category: category,
                        price: Number(price) || 0,
                        quantity: 1,
                        notes: ''
                    });
                },

                removeFood(foodId) {
                    const index = this.selectedFoods.findIndex(f => f.id === foodId);
                    if (index > -1) this.selectedFoods.splice(index, 1);
                },

                isSelected(foodId) {
                    return this.selectedFoods.some(f => f.id === foodId);
                },

                setField(foodId, field, value) {
                    const food = this.selectedFoods.find(f => f.id === foodId);
                    if (!food) return;

                    if (field === 'quantity') {
                        let val = parseInt(value || 1, 10);
                        if (isNaN(val) || val < 1) val = 1;
                        if (val > 20) val = 20;
                        food.quantity = val;
                        return;
                    }
                    food[field] = value;
                },

                increaseQty(foodId) {
                    const food = this.selectedFoods.find(f => f.id === foodId);
                    if (food && food.quantity < 20) food.quantity++;
                },

                decreaseQty(foodId) {
                    const food = this.selectedFoods.find(f => f.id === foodId);
                    if (food && food.quantity > 1) food.quantity--;
                },

                resetAll() {
                    if (this.selectedFoods.length === 0) return;
                    Swal.fire({
                        title: 'Reset Selection?',
                        text: 'This will clear all selected items from your cart.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Reset',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#8f5d54'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.selectedFoods = [];
                        }
                    });
                },

                calculateTotal() {
                    const foodTotal = this.selectedFoods.reduce((sum, food) => sum + (food.price * food.quantity), 0);
                    const grandTotal = this.accommodationTotal + foodTotal;
                    return '₱' + grandTotal.toLocaleString();
                },

                submitAndProceed(nextUrl = '{{ route("booking.staycationActivities") }}') {
                    // Store selected foods in session then move to provided step
                    const targetUrl = nextUrl || '{{ route("booking.staycationActivities") }}';

                    if (this.selectedFoods.length === 0) {
                        window.location.href = targetUrl;
                        return;
                    }

                    fetch('{{ route("booking.saveFoodSelection") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            selected_foods: this.selectedFoods
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = targetUrl;
                        } else {
                            Swal.fire('Error', data.message || 'Failed to save food selection', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'An error occurred while saving your selection', 'error');
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize swipers for each food item
            const foodCards = document.querySelectorAll('[data-category]');
            foodCards.forEach(card => {
                const swiper = card.querySelector('.swiper');
                if (swiper) {
                    const swiperId = swiper.className.match(/food-swiper-(\d+)/)?.[1];
                    if (swiperId) {
                        const element = card.querySelector('.food-swiper-' + swiperId);
                        if (element && element.querySelector('.swiper-slide')) {
                            new Swiper('.food-swiper-' + swiperId, {
                                loop: true,
                                pagination: {
                                    el: '.swiper-pagination-' + swiperId,
                                    clickable: true
                                },
                                navigation: {
                                    nextEl: '.swiper-button-next-' + swiperId,
                                    prevEl: '.swiper-button-prev-' + swiperId
                                },
                                autoplay: {
                                    delay: 4000,
                                    disableOnInteraction: false
                                }
                            });
                        }
                    }
                }
            });

            // Initialize swipers for special food items
            const specialSwipers = document.querySelectorAll('[class*="special-swiper-"]');
            specialSwipers.forEach(swiperEl => {
                const specialSwiperId = swiperEl.className.match(/special-swiper-(\d+)/)?.[1];
                if (specialSwiperId) {
                    const element = document.querySelector('.special-swiper-' + specialSwiperId);
                    if (element && element.querySelector('.swiper-slide')) {
                        new Swiper('.special-swiper-' + specialSwiperId, {
                            loop: true,
                            pagination: {
                                el: '.swiper-pagination-special-' + specialSwiperId,
                                clickable: true
                            },
                            navigation: {
                                nextEl: '.swiper-button-next-special-' + specialSwiperId,
                                prevEl: '.swiper-button-prev-special-' + specialSwiperId
                            },
                            autoplay: {
                                delay: 4000,
                                disableOnInteraction: false
                            }
                        });
                    }
                }
            });
        });
    </script>
</x-app-layout>
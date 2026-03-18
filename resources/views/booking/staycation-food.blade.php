{{-- resources/views/booking/staycation-food.blade.php --}}
<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @if(config('broadcasting.default') !== 'null')
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
    <script>
        window.Pusher = Pusher;
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

    // Calculate activities total
    $activitiesTotal = 0;
    $selectedActivities = $booking['selected_activities'] ?? [];
    foreach ($selectedActivities as $activity) {
        $activitiesTotal += ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
    }

    // Calculate nights
    $nights = 1; // default
    if (!empty($booking['checkin']) && !empty($booking['checkout'])) {
        $start = new DateTime($booking['checkin']);
        $end = new DateTime($booking['checkout']);
        $diff = $start->diff($end);
        $nights = max(1, $diff->days);
    }

    // Get selected foods from booking data
    $selectedFoods = $booking['selected_foods'] ?? [];

    // Create foods collection keyed by ID for the component
    $foods = $foodMenus->keyBy('id');

    // Key staycations by ID for the component
    $staycations = $staycations->keyBy('id');
    @endphp

    <div class="bg-[#f5efe7] min-h-screen">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight">Select Food & Beverages</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-xs text-gray-500">
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
                x-data="staycationFoodSelection({{ $accommodationTotal }}, {{ $activitiesTotal }}, {{ $nights }})"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2">
                    {{-- Filter --}}
                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="foodCategory" class="block text-sm font-medium text-gray-700 mb-2">Filter by:</label>
                            <select id="foodCategory" x-model="selectedCategory"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Items</option>
                                <option value="cook_your_own">Cook Your Own</option>
                                <option value="messhall">Messhall</option>
                                <option value="special">Special Options</option>
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
                                    <h3 class="text-xl font-bold mb-2">
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

                        @forelse($foodMenus->where('is_special_option', false)->whereIn('category', ['messhall', 'cook_your_own']) as $food)
                            @php
                                $category = $food->category ?? 'package';
                            @endphp

                            <div
                                class="relative bg-white rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl ring-2 ring-gray-200 hover:ring-[#8f5d54] cursor-pointer"
                                data-category="{{ $category }}"
                                data-food-id="{{ $food->id }}"
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

                                    {{-- Stock badge --}}
                                    @if($food->is_inventory_managed)
                                        <div class="absolute bottom-4 right-4 z-10">
                                            @if($food->stock_quantity <= 0)
                                                <span class="availability-badge px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full">
                                                    Out of Stock
                                                </span>
                                            @elseif($food->stock_quantity <= 5)
                                                <span class="availability-badge px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full animate-pulse">
                                                    Only {{ $food->stock_quantity }} left
                                                </span>
                                            @elseif($food->stock_quantity <= 10)
                                                <span class="availability-badge px-2 py-1 bg-orange-600 text-white text-xs font-semibold rounded-full">
                                                    {{ $food->stock_quantity }} left
                                                </span>
                                            @else
                                                <span class="availability-badge px-2 py-1 bg-black/70 text-white text-xs font-semibold rounded-full">
                                                    {{ $food->stock_quantity }} available
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-bold mb-2">
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
                                            :class="isSelected({{ $food->id }}) ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : '{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}'"
                                            :disabled="{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'true' : 'false' }}"
                                            @click.stop="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})">
                                        <i :class="isSelected({{ $food->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
                                        <span x-text="isSelected({{ $food->id }}) ? 'Added to Cart' : '{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart' }}'"></span>
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
                    <x-booking-summary
                        :booking="$booking"
                        :staycations="$staycations"
                        :selectedActivities="$selectedActivities"
                        :selectedFoods="$selectedFoods"
                        :foods="$foods"
                        :showDynamicFood="true"
                        :showActionButtons="true"
                        :showActivities="false"
                        :accommodationTotal="$accommodationTotal"
                        :activitiesTotal="$activitiesTotal"
                    />
                </div>
            </div>
        </div>
    </div>

    <script>
        function staycationFoodSelection(accommodationTotal = 0, activitiesTotal = 0, nights = 1) {
            return {
                selectedFoods: [],
                selectedCategory: 'all',
                accommodationTotal: accommodationTotal,
                activitiesTotal: activitiesTotal,
                nights: nights,

                toggleFood(foodId, name, category, price) {
                    const index = this.selectedFoods.findIndex(f => f.id === foodId);
                    if (index > -1) {
                        this.selectedFoods.splice(index, 1);
                        // Show SweetAlert toast for removal
                        Swal.fire({
                            icon: 'info',
                            title: `${name} removed`,
                            toast: true,
                            position: 'bottom-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
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
                    // Show SweetAlert toast for addition
                    Swal.fire({
                        icon: 'success',
                        title: `${name} added`,
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                },

                removeFood(foodId) {
                    const index = this.selectedFoods.findIndex(f => f.id === foodId);
                    if (index > -1) {
                        const foodName = this.selectedFoods[index].name;
                        this.selectedFoods.splice(index, 1);
                        // Show SweetAlert toast for removal
                        Swal.fire({
                            icon: 'info',
                            title: `${foodName} removed`,
                            toast: true,
                            position: 'bottom-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
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
                    let foodTotal = 0;
                    this.selectedFoods.forEach(food => {
                        foodTotal += food.price * food.quantity; // No nights multiplication
                    });
                    const subtotal = this.accommodationTotal + this.activitiesTotal + foodTotal;
                    const vat = subtotal * 0.12;
                    const grandTotal = subtotal + vat;
                    return '₱' + grandTotal.toLocaleString();
                },

                calculateSubtotal() {
                    let foodTotal = 0;
                    this.selectedFoods.forEach(food => {
                        foodTotal += food.price * food.quantity;
                    });
                    const subtotal = this.accommodationTotal + this.activitiesTotal + foodTotal;
                    return '₱' + subtotal.toLocaleString();
                },

                calculateVAT() {
                    let foodTotal = 0;
                    this.selectedFoods.forEach(food => {
                        foodTotal += food.price * food.quantity;
                    });
                    const subtotal = this.accommodationTotal + this.activitiesTotal + foodTotal;
                    const vat = subtotal * 0.12;
                    return '₱' + vat.toLocaleString();
                },

                calculateFoodTotal() {
                    let total = 0;
                    this.selectedFoods.forEach(food => {
                        total += food.price * food.quantity;
                    });
                    return '₱' + total.toLocaleString();
                },

                calculateActivitiesTotal() {
                    return '₱' + this.activitiesTotal.toLocaleString();
                },

                formatCurrency(amount) {
                    return '₱' + Number(amount).toLocaleString();
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

            // Listen for real-time food stock updates
            @if(config('broadcasting.default') !== 'null')
            if (typeof Echo !== 'undefined') {
                Echo.channel('food-stock')
                    .listen('.food.updated', (e) => {
                        console.log('Food stock updated:', e);
                        updateFoodStockDisplay(e.id, e.stock_quantity, e.is_inventory_managed);
                    });
            }
            @endif
        });

        // Function to update food stock display in real-time
        function updateFoodStockDisplay(foodId, stockQuantity, isInventoryManaged) {
            const foodCard = document.querySelector(`[data-food-id="${foodId}"]`);
            if (!foodCard) return;

            const stockBadge = foodCard.querySelector('.availability-badge');
            const selectButton = foodCard.querySelector('button[type="button"]');

            if (!isInventoryManaged) {
                if (stockBadge) stockBadge.style.display = 'none';
                if (selectButton) selectButton.disabled = false;
                return;
            }

            if (stockBadge) {
                if (stockQuantity <= 0) {
                    stockBadge.textContent = 'Out of Stock';
                    stockBadge.className = 'availability-badge px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full';
                } else if (stockQuantity <= 5) {
                    stockBadge.textContent = `Only ${stockQuantity} left`;
                    stockBadge.className = 'availability-badge px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full animate-pulse';
                } else if (stockQuantity <= 10) {
                    stockBadge.textContent = `${stockQuantity} left`;
                    stockBadge.className = 'availability-badge px-2 py-1 bg-orange-600 text-white text-xs font-semibold rounded-full';
                } else {
                    stockBadge.textContent = `${stockQuantity} available`;
                    stockBadge.className = 'availability-badge px-2 py-1 bg-black/70 text-white text-xs font-semibold rounded-full';
                }
                stockBadge.style.display = 'inline-block';
            }

            if (selectButton) {
                selectButton.disabled = stockQuantity <= 0;
                const buttonText = selectButton.querySelector('span:last-child');
                if (buttonText) {
                    buttonText.textContent = stockQuantity <= 0 ? 'Out of Stock' : 'Add to Cart';
                }
            }
        }
    </script>
</x-app-layout>

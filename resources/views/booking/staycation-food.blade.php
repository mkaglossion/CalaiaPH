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
                x-data="staycationFoodSelection()"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2">
                    {{-- Filter --}}
                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="foodCategory" class="block text-sm font-medium text-gray-700 mb-2" style="font-family:'Gotham Book', sans-serif;">Filter by Category:</label>
                            <select id="foodCategory" x-model="selectedCategory"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Categories</option>
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
                        @forelse($foodMenus as $food)
                            @php
                                $category = $food->category ?? 'package';
                            @endphp

                            <div
                                class="relative bg-white rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl ring-2 ring-gray-200 hover:ring-[#8f5d54] cursor-pointer"
                                data-category="{{ $category }}"
                                x-show="selectedCategory === 'all' || selectedCategory === '{{ $category }}'"
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
                                            @click="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})">
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
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold" style="font-family:'Gotham Book', sans-serif;">Order Summary</h3>
                            <button type="button" class="text-xs font-semibold text-gray-500 hover:text-gray-800" @click="resetAll()">Reset</button>
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
                                    <div class="border border-gray-200 rounded-xl p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-2 mb-3">
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold" x-text="food.name"></p>
                                                <p class="text-xs text-gray-500 capitalize" x-text="food.category"></p>
                                                <p class="text-sm font-bold text-[#8f5d54] mt-1" x-text="'₱' + Number(food.price).toLocaleString()"></p>
                                            </div>
                                            <button type="button" class="text-gray-400 hover:text-red-600 transition" @click="removeFood(food.id)">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

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
                                    </div>
                                </template>

                                <div class="border-t pt-4 flex items-center justify-between">
                                    <span class="text-sm font-semibold">Subtotal</span>
                                    <span class="text-lg font-bold text-[#8f5d54]" x-text="calculateTotal()"></span>
                                </div>
                            </div>
                        </template>

                        <div class="flex flex-col gap-3">
                            <button type="button"
                                    class="w-full py-3 rounded-xl font-semibold text-white bg-[#8f5d54] hover:bg-[#7a4e48] transition"
                                    @click="submitAndProceed()">
                                <i class="bi bi-arrow-right mr-2"></i>
                                Proceed to Activities
                            </button>
                            <button type="button"
                                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 transition"
                                    @click="submitAndProceed()">
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
        function staycationFoodSelection() {
            return {
                selectedFoods: @json(session('selected_foods', [])),
                selectedCategory: 'all',

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
                    const total = this.selectedFoods.reduce((sum, food) => sum + (food.price * food.quantity), 0);
                    return '₱' + total.toLocaleString();
                },

                submitAndProceed() {
                    // Store selected foods in session via fetch
                    const endpoint = this.selectedFoods.length > 0 ? '{{ route("booking.saveFoodSelection") }}' : '{{ route("booking.staycationActivities") }}';

                    if (this.selectedFoods.length === 0) {
                        window.location.href = '{{ route("booking.staycationActivities") }}';
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
                            window.location.href = '{{ route("booking.staycationActivities") }}';
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
        });
    </script>
</x-app-layout>
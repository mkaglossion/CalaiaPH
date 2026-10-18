{{-- resources/views/booking/staycation-food.blade.php --}}
<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <x-booking.mobile-carousel-support />

    @php
        $enablePageRealtime = config('broadcasting.default') === 'pusher'
            && filled(config('broadcasting.connections.pusher.key'))
            && filled(config('broadcasting.connections.pusher.options.cluster'))
            && !in_array(request()->getHost(), ['127.0.0.1', 'localhost']);
    @endphp

    @if($enablePageRealtime)
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.5.0/dist/web/pusher.min.js"></script>
    <script>
        window.Pusher = Pusher;
        try {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                encrypted: true
            });
        } catch (e) {
            console.log('Pusher setup failed, continuing without real-time features');
        }
    </script>
    @endif

    <style>
        .mobile-card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8rem;
        }
        @media (min-width: 640px) {
            .cards-grid-responsive { gap: 1rem; }
            .mobile-card-title {
                display: block;
                -webkit-line-clamp: initial;
                min-height: 0;
                overflow: visible;
            }
        }
        @media (min-width: 768px) {
            .cards-grid-responsive { gap: 1.5rem; }
        }

        /* base rounding */
        .flatpickr-day { border-radius: 6px; }

        /* Swiper styles */
        .swiper {
            height: 176px;
        }
        @media (min-width: 640px) {
            .swiper { height: 224px; }
        }
        @media (min-width: 768px) {
            .swiper { height: 384px; } /* h-96 = 24rem = 384px */
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
    // Create a lightweight lookup for JS prefill (id => name, price, category)
    $foodLookup = $foodMenus->mapWithKeys(function($f) {
        return [$f->id => [
            'name' => $f->name,
            'price' => $f->price ?? 0,
            'category' => $f->category ?? 'messhall'
        ]];
    });

    // Key staycations by ID for the component
    $staycations = $staycations->keyBy('id');

    // Group food packages by category so each section can render its matching packages first
    $foodPackages = $foodPackages ?? collect();
    $messhallPackages = $foodPackages->filter(function ($package) {
        return strtolower($package->category ?? 'messhall') === 'messhall';
    })->values();
    $overnightStayMenuPackages = $foodPackages->filter(function ($package) {
        return strtolower($package->category ?? '') === 'overnight_stay_menu';
    })->values();
    $cookYourOwnPackages = $foodPackages->filter(function ($package) {
        return strtolower($package->category ?? '') === 'cook_your_own';
    })->values();
    $specialPackages = $foodPackages->filter(function ($package) {
        return strtolower($package->category ?? '') === 'special';
    })->values();
    $packageCategoryLabels = $packageCategoryLabels ?? [];
    $foodBookingSectionCopy = $foodBookingSectionCopy ?? [];
    $messhallLabel = $packageCategoryLabels['messhall'] ?? 'Messhall';
    $overnightStayMenuLabel = $packageCategoryLabels['overnight_stay_menu'] ?? 'Messhall';
    $cookYourOwnLabel = $packageCategoryLabels['cook_your_own'] ?? 'Cook Your Own';
    $specialLabel = $packageCategoryLabels['special'] ?? 'Special';
    $messhallSectionCopy = $foodBookingSectionCopy['messhall'] ?? [
        'heading' => $messhallLabel . ' Items',
        'description' => 'Choose from ' . strtolower($messhallLabel) . ' items below.',
    ];
    $overnightStayMenuSectionCopy = $foodBookingSectionCopy['overnight_stay_menu'] ?? [
        'heading' => $overnightStayMenuLabel . ' Packages',
        'description' => 'Choose from ' . strtolower($overnightStayMenuLabel) . ' packages below.',
    ];
    $cookYourOwnSectionCopy = $foodBookingSectionCopy['cook_your_own'] ?? [
        'heading' => $cookYourOwnLabel . ' Packages',
        'description' => 'Choose from ' . $cookYourOwnLabel . ' packages below.',
    ];
    $specialSectionCopy = $foodBookingSectionCopy['special'] ?? [
        'heading' => $specialLabel . ' Packages',
        'description' => 'Choose from ' . $specialLabel . ' packages below.',
    ];
    @endphp

    @php
        $mobileLayoutStep2 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step2_food')->value('value') ?? 3);
    @endphp

    <div class="bg-[#f5efe7] min-h-screen mobile-layout-mode-{{ $mobileLayoutStep2 }}">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight">Select Food & Beverages</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-sm text-gray-500">
                    Browse meal packages first, then choose ala carte items.
                </p>
            </div>

            {{-- Dining Intent Banner --}}
            @if(isset($booking['intent']) && $booking['intent'] === 'dining_included')
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-blue-800 text-center">
                        <strong>ðŸ½ï¸ Dining Included:</strong> You can now browse and select meals for your stay
                    </p>
                </div>
            @endif

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

            {{-- Pass prefill data into safe globals to avoid Alpine parsing errors --}}
            <script>
                window.__initialSelected = {!! json_encode($booking['selected_foods'] ?? []) !!};
                window.__foodLookup = {!! json_encode($foodLookup) !!};
            </script>

            <div
                x-data="staycationFoodSelection({{ $accommodationTotal }}, {{ $activitiesTotal }}, {{ $nights }}, window.__initialSelected, window.__foodLookup, {{ $vatRate }}, {{ $vatRatePercentage }})"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2 order-2 lg:order-1">
                    @php
                        $packagesSettings = $packagesSettings ?? [
                            'show' => true,
                            'title' => 'Meal Packages',
                            'description' => 'Curated meal bundles with special pricing',
                            'icon' => 'bi-bag-heart'
                        ];
                    @endphp


                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="foodCategory" class="block text-sm font-medium text-gray-700 mb-2">Filter by:</label>
                            <select id="foodCategory" name="foodCategory" x-model="selectedCategory"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Items</option>
                                <option value="messhall">{{ $messhallLabel }}</option>
                                <option value="overnight_stay_menu">{{ $overnightStayMenuLabel }} Packages</option>
                                <option value="cook_your_own">{{ $cookYourOwnLabel }}</option>
                                <option value="special">{{ $specialLabel }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="sortBy" class="block text-sm font-medium text-gray-700 mb-2">Sort by:</label>
                            <form method="GET" action="{{ route('booking.staycationFood') }}">
                                <select id="sortBy" name="sort_by"
                                        onchange="this.form.submit()"
                                        class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                    @foreach(($sortOptions ?? ['featured' => 'Featured']) as $key => $label)
                                        <option value="{{ $key }}" {{ ($sortBy ?? 'featured') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        <button type="button"
                                class="ml-auto px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="selectedFoods.length === 0"
                        >
                            Reset All
                        </button>
                    </div>

                    @php
                        // Group foods by meal type and category for display
                        $foodsByMealAndCategory = [];
                        $groupFoodsBySubcategory = function ($foods) {
                            return $foods->groupBy(function ($food) {
                                $subcategory = trim((string) ($food->subcategory ?? ''));
                                return $subcategory !== '' ? $subcategory : '__default';
                            });
                        };

                        foreach ($foodMenusByMealType as $mealType => $foods) {
                            $foodsByMealAndCategory[$mealType] = [
                                'messhall' => $foods->filter(function ($food) {
                                    return !($food->is_special_option ?? false) && strtolower($food->category ?? '') === 'messhall';
                                })->values(),
                                'cook_your_own' => $foods->filter(function ($food) {
                                    $category = strtolower($food->category ?? '');
                                    $name = strtolower($food->name ?? '');
                                    return $name === 'cook your own' || $category === 'cook_your_own';
                                })->values(),
                                'special' => $foods->filter(function ($food) {
                                    $name = strtolower($food->name ?? '');
                                    return ($food->is_special_option ?? false) && $name !== 'cook your own';
                                })->values(),
                            ];
                        }

                    @endphp

                    {{-- Keep packages sections outside the meal type loop --}}
                    @if($overnightStayMenuPackages->count() > 0)
                        <div class="mb-10 order-1 lg:order-1" x-show="selectedCategory === 'all' || selectedCategory === 'overnight_stay_menu'">
                            <div class="mb-8 text-center flex flex-col items-center justify-center">
                                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">{{ $overnightStayMenuSectionCopy['heading'] }}</h2>
                                <p class="mt-3 text-base md:text-lg text-gray-500 max-w-2xl">
                                    {{ $overnightStayMenuSectionCopy['description'] }}
                                </p>
                            </div>

                            <div class="cards-grid-responsive">
                                @foreach($overnightStayMenuPackages as $package)
                                    @include('booking.partials.food-package-card', ['package' => $package])
                                @endforeach
                            </div>
                        </div>
                    @endif


                    @if($messhallPackages->count() > 0)
                        <div class="mb-10" x-show="selectedCategory === 'all' || selectedCategory === 'messhall'">
                            <div class="mb-8 text-center flex flex-col items-center justify-center">
                                @if(filled($messhallSectionCopy['heading'] ?? ''))
                                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">{{ $messhallSectionCopy['heading'] }}</h2>
                                @endif
                                @if(filled($messhallSectionCopy['description'] ?? ''))
                                    <p class="mt-3 text-base md:text-lg text-gray-500 max-w-2xl">
                                        {{ $messhallSectionCopy['description'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="cards-grid-responsive">
                                @foreach($messhallPackages as $package)
                                    @include('booking.partials.food-package-card', ['package' => $package])
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($cookYourOwnPackages->count() > 0)
                        <div class="mb-10 mt-12" x-show="selectedCategory === 'all' || selectedCategory === 'cook_your_own'">
                            <div class="mb-6 text-center">
                                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $cookYourOwnSectionCopy['heading'] }}</h2>
                                <p class="mt-2 text-sm text-gray-500">{{ $cookYourOwnSectionCopy['description'] }}</p>
                            </div>

                            <div class="cards-grid-responsive">
                                @foreach($cookYourOwnPackages as $package)
                                    @include('booking.partials.food-package-card', ['package' => $package])
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($specialPackages->count() > 0)
                        <div class="mb-8 mt-12" x-show="selectedCategory === 'all' || selectedCategory === 'special'">
                            <div class="mb-6 text-center">
                                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $specialSectionCopy['heading'] }}</h2>
                                <p class="mt-2 text-sm text-gray-500">{{ $specialSectionCopy['description'] }}</p>
                            </div>

                            <div class="cards-grid-responsive">
                                @foreach($specialPackages as $package)
                                    @include('booking.partials.food-package-card', ['package' => $package])
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Loop through meal types --}}
                    @foreach($foodsByMealAndCategory as $mealType => $categories)
                        @php
                            $mealTypeKey = strtolower((string) $mealType);
                            $mealTypeDisplayName = ucwords(str_replace('_', ' ', (string) ($mealType ?: 'Other')));
                            $mealTypeSectionCopy = $foodBookingSectionCopy[$mealTypeKey] ?? [
                                'heading' => $mealTypeDisplayName,
                                'description' => 'Choose your ' . strtolower($mealTypeDisplayName) . ' options below.',
                            ];
                            $hasAnyFoodsInMealType = collect($categories)->flatten()->count() > 0;
                        @endphp

                        @if($hasAnyFoodsInMealType)
                            <div class="mb-12" x-show="selectedCategory === 'all' || selectedCategory === 'messhall' || selectedCategory === 'cook_your_own' || selectedCategory === 'special'">
                                <div class="mb-8 text-center flex flex-col items-center justify-center">
                                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">{{ $mealTypeSectionCopy['heading'] }}</h2>
                                    <p class="mt-3 text-base md:text-lg text-gray-500 max-w-2xl">
                                        {{ $mealTypeSectionCopy['description'] }}
                                    </p>
                                </div>

                                {{-- Display Messhall foods for this meal type --}}
                                @if($categories['messhall']->count() > 0 && (request()->get('debug') || true))
                                    @php
                                        $messhallSubcategoryGroups = $groupFoodsBySubcategory($categories['messhall']);
                                    @endphp
                                    <div class="mb-8" x-show="selectedCategory === 'all' || selectedCategory === 'messhall'">
                                        @foreach($messhallSubcategoryGroups as $messhallSubcategory => $subcategoryFoods)
                                            @php
                                                $messhallSubcategoryLabel = $messhallSubcategory === '__default'
                                                    ? null
                                                    : $messhallSubcategory;
                                            @endphp
                                            <div class="{{ !$loop->first ? 'mt-8' : '' }}">
                                                @if($messhallSubcategoryLabel)
                                                    <h4 class="text-lg md:text-xl font-semibold text-gray-800 mb-4">{{ $messhallSubcategoryLabel }}</h4>
                                                @endif
                                                <div class="cards-grid-responsive">
                                            @foreach($subcategoryFoods as $food)
                                                @php
                                                    $category = 'messhall';
                                                @endphp

                                                <div
                                                    class="relative h-full flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-xl ring-2 ring-gray-200 hover:ring-[#8f5d54] cursor-pointer"
                                                    data-category="{{ $category }}"
                                                    data-food-id="{{ $food->id }}"
                                                    @click="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})"
                                                >
                                                    @include('booking.partials.card-selection-actions', [
                                                        'itemType' => 'food',
                                                        'itemId' => $food->id,
                                                        'selectionExpression' => "selectedFoods.find(f => !f.is_package && String(f.id) === String({$food->id})) || { quantity: 1 }",
                                                        'selectedCondition' => 'isSelected(' . $food->id . ')',
                                                    ])

                                                    <div class="relative bg-gray-200 overflow-hidden">
                                                        @php
                                                            $hasMultipleSwiperImages = $food->swiperImages && $food->swiperImages->count() > 1;
                                                            $hasSingleSwiperImage = $food->swiperImages && $food->swiperImages->count() == 1;
                                                        @endphp

                                                        @if($hasMultipleSwiperImages)
                                                            <div class="swiper food-swiper-{{ $food->id }}">
                                                                <div class="swiper-wrapper">
                                                                    @foreach($food->swiperImages as $image)
                                                                        <div class="swiper-slide">
                                                                            <img src="{{ \App\Helpers\ImageHelper::src($image->image_path) }}" alt="{{ $image->title ?? $food->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                <div class="swiper-pagination swiper-pagination-{{ $food->id }}"></div>
                                                                <div class="swiper-button-next swiper-button-next-{{ $food->id }}"></div>
                                                                <div class="swiper-button-prev swiper-button-prev-{{ $food->id }}"></div>
                                                            </div>
                                                        @elseif($hasSingleSwiperImage)
                                                            <img src="{{ \App\Helpers\ImageHelper::src($food->swiperImages->first()->image_path) }}" alt="{{ $food->swiperImages->first()->title ?? $food->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                        @elseif($food->image)
                                                            <img src="{{ \App\Helpers\ImageHelper::src($food->image) }}" alt="{{ $food->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                        @else
                                                            <div class="w-full h-64 sm:h-64 md:h-96 bg-linear-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                                                                <i class="bi bi-image text-6xl text-gray-300"></i>
                                                            </div>
                                                        @endif

                                                         <div class="absolute bottom-4 left-4 z-10">
                                                             <span class="px-3 py-1 bg-[#8f5d54] text-white text-sm font-semibold rounded-full">
                                                                 {{ $messhallLabel }}
                                                             </span>
                                                         </div>

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

                                                     <div class="p-3 sm:p-4 md:p-6 flex flex-col flex-1">
                                                         <h3 class="text-sm sm:text-base md:text-xl font-bold mb-1.5 md:mb-2 leading-tight mobile-card-title">{{ $food->name }}</h3>

                                                         <div class="mb-3">
                                                             <span class="text-sm sm:text-base md:text-lg font-bold text-[#8f5d54]">
                                                                 ₱{{ number_format($food->price ?? 0, 0, ',', ',') }}
                                                             </span>
                                                         </div>

                                                         {{-- Quantity controls for selected food --}}
                                                         <template x-if="isSelected({{ $food->id }})">
                                                             <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                                                 <div class="flex items-center justify-between gap-4">
                                                                     <div class="flex items-center gap-2">
                                                                         <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="decreaseQty({{ $food->id }})">&minus;</button>
                                                                         <input type="number" min="1" max="20" :id="'food-qty-card-' + {{ $food->id }}" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="getSelectedQuantity({{ $food->id }})" @change.stop="setField({{ $food->id }}, 'quantity', $event.target.value)">
                                                                         <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="increaseQty({{ $food->id }})">+</button>
                                                                     </div>
                                                                     <div class="text-right">
                                                                         <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + ({{ $food->price ?? 0 }} * getSelectedQuantity({{ $food->id }})).toLocaleString()"></div>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </template>

                                                         @include('booking.partials.expandable-description', [
                                                             'description' => $food->description,
                                                             'fallback' => 'No description available',
                                                             'wrapperClass' => 'mb-3 md:mb-4',
                                                             'textClass' => 'text-gray-600',
                                                             'buttonClass' => 'text-[#8f5d54]',
                                                             'clampLines' => 3,
                                                         ])

                                                         <button type="button"
                                                                 class="w-full mt-auto py-2 rounded-lg font-semibold transition"
:class="{
    'bg-[#8f5d54] text-white hover:bg-[#7a4e48]': isSelected({{ $food->id }}),
    'bg-gray-300 text-gray-500 cursor-not-allowed': !isSelected({{ $food->id }}) && {{ $food->is_inventory_managed ? 'true' : 'false' }} && {{ $food->stock_quantity }} <= 0,
    'bg-gray-100 text-gray-700 hover:bg-gray-200': !isSelected({{ $food->id }}) && (!{{ $food->is_inventory_managed ? 'true' : 'false' }} || {{ $food->stock_quantity }} > 0)
}"
                                                                 :disabled="{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'true' : 'false' }}"
                                                                 @click.stop="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})">
                                                             <i :class="isSelected({{ $food->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
                                                             <span x-text="isSelected({{ $food->id }}) ? 'Added to Cart' : '{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart' }}'"></span>
                                                         </button>
                                                     </div>
                                                </div>
                                            @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            {{-- Display Cook Your Own foods for this meal type --}}
                                @if($categories['cook_your_own']->count() > 0)
                                    @php
                                        $cookYourOwnSubcategoryGroups = $groupFoodsBySubcategory($categories['cook_your_own']);
                                    @endphp
                                    <div class="mb-8" x-show="selectedCategory === 'all' || selectedCategory === 'cook_your_own'">
                                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ $cookYourOwnLabel }}</h3>
                                        @foreach($cookYourOwnSubcategoryGroups as $cookYourOwnSubcategory => $subcategoryFoods)
                                            @php
                                                $cookYourOwnSubcategoryLabel = $cookYourOwnSubcategory === '__default'
                                                    ? null
                                                    : $cookYourOwnSubcategory;
                                            @endphp
                                            <div class="{{ !$loop->first ? 'mt-8' : '' }}">
                                                @if($cookYourOwnSubcategoryLabel)
                                                    <h4 class="text-lg md:text-xl font-semibold text-gray-800 mb-4">{{ $cookYourOwnSubcategoryLabel }}</h4>
                                                @endif
                                                <div class="cards-grid-responsive">
                                            @foreach($subcategoryFoods as $food)
                                                @php
                                                    $category = 'cook_your_own';
                                                @endphp

                                                <div
                                                    class="relative h-full flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-xl ring-2 ring-gray-200 hover:ring-[#8f5d54] cursor-pointer"
                                                    data-category="{{ $category }}"
                                                    data-food-id="{{ $food->id }}"
                                                    @click="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})"
                                                >
                                                    @include('booking.partials.card-selection-actions', [
                                                        'itemType' => 'food',
                                                        'itemId' => $food->id,
                                                        'selectionExpression' => "selectedFoods.find(f => !f.is_package && String(f.id) === String({$food->id})) || { quantity: 1 }",
                                                        'selectedCondition' => 'isSelected(' . $food->id . ')',
                                                    ])

                                                    <div class="relative bg-gray-200 overflow-hidden">
                                                        @php
                                                            $hasMultipleSwiperImages = $food->swiperImages && $food->swiperImages->count() > 1;
                                                            $hasSingleSwiperImage = $food->swiperImages && $food->swiperImages->count() == 1;
                                                        @endphp

                                                        @if($hasMultipleSwiperImages)
                                                            <div class="swiper food-swiper-{{ $food->id }}">
                                                                <div class="swiper-wrapper">
                                                                    @foreach($food->swiperImages as $image)
                                                                        <div class="swiper-slide">
                                                                            <img src="{{ \App\Helpers\ImageHelper::src($image->image_path) }}" alt="{{ $image->title ?? $food->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                <div class="swiper-pagination swiper-pagination-{{ $food->id }}"></div>
                                                                <div class="swiper-button-next swiper-button-next-{{ $food->id }}"></div>
                                                                <div class="swiper-button-prev swiper-button-prev-{{ $food->id }}"></div>
                                                            </div>
                                                        @elseif($hasSingleSwiperImage)
                                                            <img src="{{ \App\Helpers\ImageHelper::src($food->swiperImages->first()->image_path) }}" alt="{{ $food->swiperImages->first()->title ?? $food->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                        @elseif($food->image)
                                                            <img src="{{ \App\Helpers\ImageHelper::src($food->image) }}" alt="{{ $food->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                        @else
                                                            <div class="w-full h-64 sm:h-64 md:h-96 bg-linear-to-br from-green-100 to-emerald-200 flex items-center justify-center">
                                                                <i class="bi bi-fire text-6xl text-green-600"></i>
                                                            </div>
                                                        @endif

                                                        <div class="absolute bottom-4 left-4 z-10">
                                                            <span class="px-3 py-1 bg-green-600 text-white text-sm font-semibold rounded-full">
                                                                {{ $cookYourOwnLabel }}
                                                            </span>
                                                        </div>

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

                                                     <div class="p-3 sm:p-4 md:p-6 flex flex-col flex-1">
                                                         <h3 class="text-sm sm:text-base md:text-xl font-bold mb-1.5 md:mb-2 leading-tight mobile-card-title">{{ $food->name }}</h3>

                                                         <div class="mb-3">
                                                             <span class="text-sm sm:text-base md:text-lg font-bold text-[#8f5d54]">
                                                                 ₱{{ number_format($food->price ?? 0, 0, ',', ',') }}
                                                             </span>
                                                         </div>

                                                         {{-- Quantity controls for selected food --}}
                                                         <template x-if="isSelected({{ $food->id }}) && '{{ $category }}' !== 'special'">
                                                             <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                                                 <div class="flex items-center justify-between gap-4">
                                                                     <div class="flex items-center gap-2">
                                                                         <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="decreaseQty({{ $food->id }})">&minus;</button>
                                                                         <input type="number" min="1" max="20" :id="'food-qty-card-' + {{ $food->id }}" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="getSelectedQuantity({{ $food->id }})" @change.stop="setField({{ $food->id }}, 'quantity', $event.target.value)">
                                                                         <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="increaseQty({{ $food->id }})">+</button>
                                                                     </div>
                                                                     <div class="text-right">
                                                                         <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + ({{ $food->price ?? 0 }} * getSelectedQuantity({{ $food->id }})).toLocaleString()"></div>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </template>

                                                         @include('booking.partials.expandable-description', [
                                                             'description' => $food->description,
                                                             'fallback' => 'No description available',
                                                             'wrapperClass' => 'mb-3 md:mb-4',
                                                             'textClass' => 'text-gray-600',
                                                             'buttonClass' => 'text-[#8f5d54]',
                                                             'clampLines' => 3,
                                                         ])

                                                         <button type="button"
                                                                 class="w-full mt-auto py-2 rounded-lg font-semibold transition"
:class="{
    'bg-[#8f5d54] text-white hover:bg-[#7a4e48]': isSelected({{ $food->id }}),
    'bg-gray-300 text-gray-500 cursor-not-allowed': !isSelected({{ $food->id }}) && {{ $food->is_inventory_managed ? 'true' : 'false' }} && {{ $food->stock_quantity }} <= 0,
    'bg-gray-100 text-gray-700 hover:bg-gray-200': !isSelected({{ $food->id }}) && (!{{ $food->is_inventory_managed ? 'true' : 'false' }} || {{ $food->stock_quantity }} > 0)
}"
                                                                 :disabled="{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'true' : 'false' }}"
                                                                 @click.stop="toggleFood({{ $food->id }}, '{{ $food->name }}', '{{ $category }}', {{ $food->price ?? 0 }})">
                                                             <i :class="isSelected({{ $food->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
                                                             <span x-text="isSelected({{ $food->id }}) ? 'Added to Cart' : '{{ $food->is_inventory_managed && $food->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart' }}'"></span>
                                                         </button>
                                                     </div>
                                                </div>
                                            @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif


                                  {{-- Display Special foods for this meal type --}}
                                @if($categories['special']->count() > 0)
                                    @php
                                        $specialSubcategoryGroups = $groupFoodsBySubcategory($categories['special']);
                                    @endphp
                                    <div class="mb-8" x-show="selectedCategory === 'all' || selectedCategory === 'special'">
                                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ $specialLabel }}</h3>
                                        @foreach($specialSubcategoryGroups as $specialSubcategory => $subcategoryFoods)
                                            @php
                                                $specialSubcategoryLabel = $specialSubcategory === '__default'
                                                    ? null
                                                    : $specialSubcategory;
                                            @endphp
                                            <div class="{{ !$loop->first ? 'mt-8' : '' }}">
                                                @if($specialSubcategoryLabel)
                                                    <h4 class="text-lg md:text-xl font-semibold text-gray-800 mb-4">{{ $specialSubcategoryLabel }}</h4>
                                                @endif
                                                <div class="cards-grid-responsive">
                                            @foreach($subcategoryFoods as $special)
                                                @php
                                                    $category = $special->category ?? 'special';
                                                    $specialColor = match($special->name) {
                                                        'Cook Your Own' => ['bg' => 'from-green-50 to-emerald-50', 'ring' => 'ring-green-200 hover:ring-green-400', 'border' => 'border-green-300', 'btn' => 'bg-green-600 hover:bg-green-700', 'badge' => 'bg-green-600', 'icon' => 'bi-fire'],
                                                        default => ['bg' => 'from-blue-50 to-cyan-50', 'ring' => 'ring-blue-200 hover:ring-blue-400', 'border' => 'border-blue-300', 'btn' => 'bg-blue-600 hover:bg-blue-700', 'badge' => 'bg-blue-600', 'icon' => 'bi-star']
                                                    };
                                                @endphp

                                                <div
                                                    class="relative h-full flex flex-col bg-linear-to-br rounded-2xl shadow-lg overflow-hidden border transition-all duration-300 hover:shadow-xl ring-2 cursor-pointer"
                                                    :class="'{{ $specialColor['bg'] }} {{ $specialColor['ring'] }} {{ $specialColor['border'] }}'"
                                                    @click="toggleFood({{ $special->id }}, '{{ $special->name }}', '{{ $category }}', {{ $special->price }})"
                                                >
                                                    @include('booking.partials.card-selection-actions', [
                                                        'itemType' => 'food',
                                                        'itemId' => $special->id,
                                                        'selectionExpression' => "selectedFoods.find(f => !f.is_package && String(f.id) === String({$special->id})) || { quantity: 1 }",
                                                        'selectedCondition' => 'isSelected(' . $special->id . ')',
                                                    ])

                                                    <div class="relative overflow-hidden h-64 sm:h-64 md:h-96 flex items-center justify-center bg-gray-100">
                                                        @php
                                                            $hasMultipleSwiperImages = $special->swiperImages && $special->swiperImages->count() > 1;
                                                            $hasSingleSwiperImage = $special->swiperImages && $special->swiperImages->count() == 1;
                                                        @endphp

                                                        @if($hasMultipleSwiperImages)
                                                            <div class="swiper special-swiper-{{ $special->id }}">
                                                                <div class="swiper-wrapper">
                                                                    @foreach($special->swiperImages as $image)
                                                                        <div class="swiper-slide">
                                                                            <img src="{{ \App\Helpers\ImageHelper::src($image->image_path) }}" alt="{{ $image->title ?? $special->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                <div class="swiper-pagination swiper-pagination-special-{{ $special->id }}"></div>
                                                                <div class="swiper-button-next swiper-button-next-special-{{ $special->id }}"></div>
                                                                <div class="swiper-button-prev swiper-button-prev-special-{{ $special->id }}"></div>
                                                            </div>
                                                        @elseif($hasSingleSwiperImage)
                                                            <img src="{{ \App\Helpers\ImageHelper::src($special->swiperImages->first()->image_path) }}" alt="{{ $special->swiperImages->first()->title ?? $special->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                        @elseif($special->image)
                                                            <img src="{{ \App\Helpers\ImageHelper::src($special->image) }}" alt="{{ $special->name }}" class="w-full h-64 sm:h-64 md:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                                        @else
                                                            <div class="text-center" :class="'bg-linear-to-br {{ $specialColor['bg'] }}'">
                                                                <i class="bi {{ $specialColor['icon'] }} text-6xl mb-4 block" :class="'{{ $specialColor['icon'] === 'bi-fire' ? 'text-green-600' : 'text-blue-600' }}'"></i>
                                                                <p class="font-semibold" :class="'{{ $specialColor['icon'] === 'bi-fire' ? 'text-green-700' : 'text-blue-700' }}'">{{ $special->name }}</p>
                                                            </div>
                                                        @endif

                                                        <div class="absolute bottom-4 left-4 z-10">
                                                            <span class="px-3 py-1 text-white text-sm font-semibold rounded-full" :class="'{{ $specialColor['badge'] }}'">
                                                                {{ $specialLabel }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 sm:p-4 md:p-6 flex flex-col flex-1">
                                                        <h3 class="text-sm sm:text-base md:text-xl font-bold mb-1.5 md:mb-2 leading-tight mobile-card-title">{{ $special->name }}</h3>

                                                        <div class="mb-3">
                                                            <span class="text-sm sm:text-base md:text-lg font-bold" :class="{{ $special->price }} === 0 ? 'text-green-600' : 'text-[#8f5d54]'">
                                                                {{ $special->price === 0 ? 'FREE' : '₱' . number_format($special->price, 0) }}
                                                            </span>
                                                        </div>

                                                        @include('booking.partials.expandable-description', [
                                                            'description' => $special->description,
                                                            'fallback' => 'Special option for your staycation',
                                                            'wrapperClass' => 'mb-3 md:mb-4',
                                                            'textClass' => 'text-gray-600',
                                                            'buttonClass' => 'text-[#8f5d54]',
                                                            'clampLines' => 3,
                                                        ])

                                                        <button type="button"
                                                                class="w-full mt-auto py-2 rounded-lg font-semibold transition text-white"
                                                                :class="isSelected({{ $special->id }}) ? '{{ $specialColor['btn'] }}' : 'bg-gray-400 hover:bg-gray-500'"
                                                                @click.stop="toggleFood({{ $special->id }}, '{{ $special->name }}', '{{ $category }}', {{ $special->price }})">
                                                            <i :class="isSelected({{ $special->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
                                                            <span x-text="isSelected({{ $special->id }}) ? 'Selected' : 'Select Option'"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach

                </div>

                {{-- RIGHT SIDEBAR --}}
                <div class="lg:col-span-1 order-1 lg:order-2">
                    @php
                        // Calculate food total for display in summary
                        $displayFoodTotal = 0;
                        foreach ($selectedFoods as $food) {
                            $displayFoodTotal += ($food['price'] ?? 0) * ($food['quantity'] ?? 1);
                        }
                    @endphp
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
                        :foodTotal="$displayFoodTotal"
                    />
                </div>
            </div>
        </div>
    </div>

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

    <script>
        function staycationFoodSelection(accommodationTotal = 0, activitiesTotal = 0, nights = 1, initialSelected = [], foodLookup = {}, vatRate = 0.0, vatRatePercentage = 0) {
            return {
                selectedFoods: initialSelected && initialSelected.length ? initialSelected.map(item => {
                    // Handle package items - preserve all package properties
                    if (item.is_package || item.package_id || (typeof item.id === 'string' && item.id.startsWith('package-'))) {
                        return {
                            id: item.id || `package-${item.package_id}`,
                            package_id: String(item.package_id || ''),
                            name: item.name || 'Package',
                            category: 'package',
                            price: Number(item.price || 0),
                            quantity: Number(item.quantity || 1),
                            is_package: true,
                            included_foods: item.included_foods || item.included_activities || [],
                            package_sections: item.package_sections || [],
                            selected_choices: item.selected_choices || [],
                            notes: item.notes || ''
                        };
                    }
                    // Normalize server-side prefills to client format
                    if (item.food_id) {
                        const id = parseInt(item.food_id, 10);
                        const meta = foodLookup[id] || {};
                        return {
                            id: id,
                            name: meta.name || (item.name || 'Selected Food'),
                            category: meta.category || (item.category || 'messhall'),
                            price: Number(meta.price ?? item.price ?? 0),
                            quantity: Number(item.quantity ?? item.adults ?? 1),
                            notes: item.notes ?? ''
                        };
                    }
                    // If already in expected shape, return as-is (ensure number types)
                    return {
                        id: item.id,
                        name: item.name || 'Food',
                        category: item.category || 'messhall',
                        price: Number(item.price || 0),
                        quantity: Number(item.quantity || 1),
                        notes: item.notes || ''
                    };
                }) : [],
                selectedCategory: 'all',
                accommodationTotal: accommodationTotal,
                activitiesTotal: activitiesTotal,
                nights: nights,
                vatRate: vatRate,
                vatRatePercentage: vatRatePercentage,

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

                getSelectedQuantity(foodId) {
                    const f = this.selectedFoods.find(s => parseInt(s.id || s.food_id) === parseInt(foodId));
                    return f ? (f.quantity || 1) : 0;
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
                buildStructuredPackageFoods(packageSections, selectedChoices = []) {
                    if (!Array.isArray(packageSections) || packageSections.length === 0) {
                        return [];
                    }

                    const selectedChoiceMap = new Map((selectedChoices || []).map(choice => [
                        String(choice.section_title || choice.section_index || ''),
                        choice,
                    ]));

                    return packageSections.flatMap((section, sectionIndex) => {
                        const mode = String(section.mode || 'included');

                        if (mode === 'choice') {
                            const lookupKey = String(section.title || sectionIndex);
                            const selectedChoice = selectedChoiceMap.get(lookupKey) || null;
                            if (!selectedChoice) {
                                return [];
                            }

                            return [{
                                id: selectedChoice.food_id,
                                name: selectedChoice.food_name,
                                price: Number(selectedChoice.price || 0),
                                quantity: 1,
                                choice_section: section.title || '',
                            }];
                        }

                        return (section.items || []).map(item => ({
                            id: item.id,
                            name: item.name,
                            price: Number(item.price || 0),
                            quantity: Number(item.quantity || 1),
                            section_title: section.title || '',
                        }));
                    }).filter(item => item && item.id);
                },

                async promptPackageChoices(packageName, packagePrice, packageSections) {
                    const choiceSections = (packageSections || []).filter(section => String(section.mode || 'included') === 'choice' && Array.isArray(section.items) && section.items.length > 0);
                    if (choiceSections.length === 0) {
                        return [];
                    }

                    const html = choiceSections.map((section, sectionIndex) => `
                        <div class="text-left mb-4 w-full max-w-full">
                            <label for="choice-section-${sectionIndex}" class="block text-sm font-semibold text-gray-800 mb-2">${section.title || 'Choice'}</label>
                            <select id="choice-section-${sectionIndex}" class="block w-full max-w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white">
                                <option value="">Select one</option>
                                ${(section.items || []).map((item) => `
                                    <option value="${String(item.id)}">${item.name} (${item.quantity || 1}x)</option>
                                `).join('')}
                            </select>
                            ${section.note ? `<p class="mt-2 text-xs text-gray-500">${section.note}</p>` : ''}
                        </div>
                    `).join('');

                    const result = await Swal.fire({
                        title: `${packageName} choices`,
                        html,
                        confirmButtonText: 'Add Package',
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        focusConfirm: false,
                        width: 560,
                        padding: '1.5rem',
                        customClass: {
                            popup: 'overflow-x-hidden',
                            htmlContainer: 'overflow-x-hidden',
                        },
                        preConfirm: () => {
                            const container = Swal.getHtmlContainer();
                            const selections = [];

                            for (let sectionIndex = 0; sectionIndex < choiceSections.length; sectionIndex++) {
                                const section = choiceSections[sectionIndex];
                                const select = container?.querySelector(`#choice-section-${sectionIndex}`);
                                const foodId = select?.value;
                                if (!foodId) {
                                    Swal.showValidationMessage(`Please choose one for ${section.title || 'this section'}.`);
                                    return false;
                                }

                                const food = (section.items || []).find(item => String(item.id) === String(foodId));
                                if (!food) {
                                    Swal.showValidationMessage(`Invalid choice for ${section.title || 'this section'}.`);
                                    return false;
                                }

                                selections.push({
                                    section_title: section.title || '',
                                    section_index: sectionIndex,
                                    food_id: String(food.id),
                                    food_name: food.name,
                                    price: Number(food.price || 0),
                                });
                            }

                            return selections;
                        }
                    });

                    if (!result.isConfirmed) {
                        return null;
                    }

                    return result.value || [];
                },

                addPackageToCart(packageId, packageName, packagePrice, packageFoods, packageSections = [], selectedChoices = []) {
                    const existingPackageIndex = this.selectedFoods.findIndex(f => f.is_package && String(f.package_id) === String(packageId));
                    const includedFoods = this.buildStructuredPackageFoods(packageSections, selectedChoices);
                    const finalIncludedFoods = includedFoods.length > 0 ? includedFoods : packageFoods;

                    if (existingPackageIndex > -1) {
                        this.selectedFoods[existingPackageIndex].quantity += 1;
                        return;
                    }

                    this.selectedFoods.push({
                        id: `package-${packageId}`,
                        package_id: String(packageId),
                        name: packageName,
                        category: 'package',
                        price: Number(packagePrice) || 0,
                        quantity: 1,
                        is_package: true,
                        included_foods: finalIncludedFoods,
                        package_sections: packageSections || [],
                        selected_choices: selectedChoices || [],
                        notes: ''
                    });

                    Swal.fire({
                        icon: 'success',
                        title: `${packageName} package added!`,
                        html: `<p class="text-sm">Total: <strong>₱${Number(packagePrice || 0).toLocaleString()}</strong></p>`,
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                },

                async togglePackage(packageId, packageName, packagePrice, packageFoods, packageSections = []) {
                    const existingPackageIndex = this.selectedFoods.findIndex(f => f.is_package && String(f.package_id) === String(packageId));

                    if (existingPackageIndex > -1) {
                        this.selectedFoods.splice(existingPackageIndex, 1);
                        Swal.fire({
                            icon: 'info',
                            title: `${packageName} removed`,
                            toast: true,
                            position: 'bottom-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        return;
                    }

                    const choiceSections = (packageSections || []).filter(section => String(section.mode || 'included') === 'choice' && Array.isArray(section.items) && section.items.length > 0);
                    if (choiceSections.length > 0) {
                        const selectedChoices = await this.promptPackageChoices(packageName, packagePrice, packageSections);
                        if (selectedChoices === null) {
                            return;
                        }
                        this.addPackageToCart(packageId, packageName, packagePrice, packageFoods, packageSections, selectedChoices);
                        return;
                    }

                    this.addPackageToCart(packageId, packageName, packagePrice, packageFoods, packageSections, []);
                },

                isSelected(foodId) {
                    return this.selectedFoods.some(f => f.id === foodId);
                },

isPackageSelected(packageId) {
                    return this.selectedFoods.some(f => f.is_package && String(f.package_id) === String(packageId));
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
                    const vat = subtotal * this.vatRate;
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
                    const vat = subtotal * this.vatRate;
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

                decreasePackageQty(packageId) {
                    const pkg = this.selectedFoods.find(f => f.is_package && String(f.package_id) === String(packageId));
                    if (pkg && pkg.quantity > 1) pkg.quantity--;
                },

                increasePackageQty(packageId) {
                    const pkg = this.selectedFoods.find(f => f.is_package && String(f.package_id) === String(packageId));
                    if (pkg && pkg.quantity < 20) pkg.quantity++;
                },

                getPackageQuantity(packageId) {
                    const pkg = this.selectedFoods.find(f => f.is_package && String(f.package_id) === String(packageId));
                    return pkg ? pkg.quantity || 1 : 1;
                },

                setPackageField(packageId, field, value) {
                    const pkg = this.selectedFoods.find(f => f.is_package && String(f.package_id) === String(packageId));
                    if (!pkg) return;

                    if (field === 'quantity') {
                        let val = parseInt(value || 1, 10);
                        if (isNaN(val) || val < 1) val = 1;
                        if (val > 20) val = 20;
                        pkg.quantity = val;
                        return;
                    }
                    pkg[field] = value;
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
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                const error = new Error(data.message || 'Request failed');
                                error.status = response.status;
                                error.data = data;
                                throw error;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            window.location.href = targetUrl;
                        } else {
                            let errorMsg = data.message || 'Failed to save food selection';
                            if (data.errors) {
                                const errors = Object.values(data.errors).flat();
                                errorMsg = errors.join('\n');
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        let msg = error.message || 'An error occurred while saving your selection';
                        if (error.data && error.data.errors) {
                            const errors = Object.values(error.data.errors).flat();
                            msg = errors.join('\n');
                        }
                        Swal.fire('Error', msg, 'error');
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
            @if($enablePageRealtime)
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

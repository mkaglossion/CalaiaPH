<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        .mobile-card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8rem;
        }
        .mobile-card-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 4.6rem;
        }
        @media (min-width: 640px) {
            .cards-grid-responsive { gap: 1rem; }
            .mobile-card-title,
            .mobile-card-description {
                display: block;
                -webkit-line-clamp: initial;
                min-height: 0;
                overflow: visible;
            }
        }
        @media (min-width: 768px) {
            .cards-grid-responsive { gap: 1.5rem; }
        }
    </style>

    @php
    // Calculate accommodation & activities totals from session
    $accommodationTotal = 0;
    foreach ($booking['accommodations'] ?? [] as $roomId => $acc) {
        if (!empty($acc)) {
            $accommodationTotal += $acc['total_price'] ?? 0;
        }
    }

    // Calculate food total
    $foodTotal = 0;
    $selectedFoods = $booking['selected_foods'] ?? [];
    foreach ($selectedFoods as $food) {
        $foodTotal += ($food['price'] ?? 0) * ($food['quantity'] ?? 1);
    }

    // Calculate nights
    $nights = 1;
    if (!empty($booking['checkin']) && !empty($booking['checkout'])) {
        $start = new DateTime($booking['checkin']);
        $end = new DateTime($booking['checkout']);
        $diff = $start->diff($end);
        $nights = max(1, $diff->days);
    }

    // Get selected data from booking
    $selectedActivities = $booking['selected_activities'] ?? [];
    $selectedActivityPackages = $booking['selected_activity_packages'] ?? [];
    $staycations = $staycations->keyBy('id');

    // Get unique categories from activities
    $categories = $activities->pluck('category')->unique()->filter()->sort()->values();
    @endphp

    @php
        $mobileLayoutStep3 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step3_activities')->value('value') ?? 3);
    @endphp

    <div class="bg-[#f5efe7] min-h-screen mobile-layout-mode-{{ $mobileLayoutStep3 }}">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight">Select Activities & Experiences</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-sm text-gray-500">
                    Browse activity packages first, then choose à la carte items.
                </p>
            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <x-booking.progress-indicator :stepNumber="4" :totalSteps="5" :bookingType="'staycation'" />
            </div>

            {{-- Back + Guest mode --}}
            <div class="flex items-end justify-between mb-6">
                <a href="{{ route('booking.staycationFood') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Back to Food & Beverages
                </a>

                @if (session()->has('user.is_guest') && session('user.is_guest'))
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                          style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                        <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                    </span>
                @endif
            </div>

            <div
                x-data="staycationActivitiesSelection({{ $accommodationTotal }}, {{ $foodTotal }}, {{ $nights }}, {{ $vatRate }}, {{ $vatRatePercentage }})"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2 order-2 lg:order-1">
                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="activityCategory" class="block text-sm font-medium text-gray-700 mb-2">Filter by:</label>
                            <select id="activityCategory" name="activityCategory" x-model="selectedCategory"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Items</option>
                                @foreach($categories as $category)
                                    <option value="{{ strtolower($category) }}">{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="sortBy" class="block text-sm font-medium text-gray-700 mb-2">Sort by:</label>
                            <form method="GET" action="{{ route('booking.staycationActivities') }}">
                                @foreach(request()->except('sort_by') as $queryKey => $queryValue)
                                    @if(is_array($queryValue))
                                        @foreach($queryValue as $nestedValue)
                                            <input type="hidden" name="{{ $queryKey }}[]" value="{{ $nestedValue }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                                    @endif
                                @endforeach

                                <select id="sortBy" name="sort_by"
                                        onchange="this.form.submit()"
                                        class="block w-full min-w-56 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                    @foreach(($sortOptions ?? ['featured' => 'Featured']) as $key => $label)
                                        <option value="{{ $key }}" {{ ($sortBy ?? 'featured') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        <button type="button"
                                class="ml-auto px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="(selectedActivities.length + selectedActivityPackages.length) === 0"
                        >
                            Reset All
                        </button>
                    </div>

                    {{-- ACTIVITY PACKAGES SECTION --}}
                    @if($activityPackages->count() > 0)
                        <div class="mb-10">
                            <div class="mb-8 text-center flex flex-col items-center justify-center">
                                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">Activity Packages</h2>
                                <p class="mt-3 text-base md:text-lg text-gray-500 max-w-2xl">
                                    Curated activity bundles with special pricing
                                </p>
                            </div>

                            <div class="cards-grid-responsive">
                                @foreach($activityPackages as $package)
                                    @include('booking.partials.activity-package-card', ['package' => $package])
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- CATEGORIZED ACTIVITIES SECTIONS --}}
                    @foreach($categories as $category)
                        @php
                            $categoryActivities = $activities->filter(function ($activity) use ($category) {
                                return strtolower($activity->category ?? '') === strtolower($category);
                            })->values();
                        @endphp

                        @if($categoryActivities->count() > 0)
                            <div class="mb-10 mt-12" x-show="selectedCategory === 'all' || selectedCategory === '{{ strtolower($category) }}'">
                                <div class="mb-8 text-center flex flex-col items-center justify-center">
                                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">{{ ucfirst($category) }}</h2>
                                    <p class="mt-3 text-base md:text-lg text-gray-500 max-w-2xl">
                                        Choose from activities à la carte below.
                                    </p>
                                </div>

                                <div class="cards-grid-responsive">
                                    @foreach($categoryActivities as $activity)
                                        <x-activity-card :activity="$activity" />
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach


                </div>

                {{-- RIGHT SIDEBAR --}}
                <div class="lg:col-span-1 order-1 lg:order-2">
                    @php
                        // Calculate current activities total from selected items
                        $currentActivitiesTotal = 0;
                        foreach ($selectedActivities as $activity) {
                            $currentActivitiesTotal += ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
                        }
                        foreach ($selectedActivityPackages as $package) {
                            $currentActivitiesTotal += ($package['price'] ?? 0) * ($package['quantity'] ?? 1);
                        }
                    @endphp
                    <x-booking-summary
                        :booking="$booking"
                        :staycations="$staycations"
                        :selectedActivities="$selectedActivities"
                        :selectedFoods="$selectedFoods"
                        :showDynamicFood="false"
                        :showActionButtons="true"
                        :showActivities="true"
                        :showDynamicActivities="true"
                        :accommodationTotal="$accommodationTotal"
                        :foodTotal="$foodTotal"
                        :activitiesTotal="$currentActivitiesTotal"
                        actionButtonType="activities"
                    />
                </div>
            </div>
        </div>
    </div>

    <script>
        function staycationActivitiesSelection(accommodationTotal = 0, foodTotal = 0, nights = 1, vatRate = 0.0, vatRatePercentage = 0) {
            return {
                selectedActivities: [],
                selectedActivityPackages: [],
                selectedCategory: 'all',
                accommodationTotal: accommodationTotal,
                foodTotal: foodTotal,
                nights: nights,
                vatRate: vatRate,
                vatRatePercentage: vatRatePercentage,

                toggleActivity(activityId, name, category, price) {
                    const id = String(activityId);
                    const index = this.selectedActivities.findIndex(a => a.id === id);
                    if (index > -1) {
                        this.selectedActivities.splice(index, 1);
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
                    this.selectedActivities.push({
                        id: id,
                        name: name,
                        category: category,
                        price: Number(price) || 0,
                        guests: 1
                    });
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

                toggleActivityPackage(packageId, packageName, packagePrice, packageActivities) {
                    const id = String(packageId);
                    const existingIndex = this.selectedActivityPackages.findIndex(p => p.package_id === id);
                    if (existingIndex > -1) {
                        this.selectedActivityPackages.splice(existingIndex, 1);
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
                    this.selectedActivityPackages.push({
                        id: `package-${id}`,
                        package_id: id,
                        name: packageName,
                        category: 'package',
                        price: Number(packagePrice) || 0,
                        quantity: 1,
                        is_package: true,
                        included_activities: packageActivities || []
                    });
                    Swal.fire({
                        icon: 'success',
                        title: `${packageName} added!`,
                        html: `<p class="text-sm">Total: <strong>₱${packagePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></p>`,
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                },

                isActivitySelected(activityId) {
                    return this.selectedActivities.some(a => a.id === String(activityId));
                },

                isActivityPackageSelected(packageId) {
                    return this.selectedActivityPackages.some(p => p.package_id === String(packageId));
                },

                resetAll() {
                    if ((this.selectedActivities.length + this.selectedActivityPackages.length) === 0) return;
                    Swal.fire({
                        title: 'Reset Selection?',
                        text: 'This will clear all selected activities from your cart.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Reset',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#8f5d54'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.selectedActivities = [];
                            this.selectedActivityPackages = [];
                        }
                    });
                },

                submitAndProceed(nextUrl = '{{ route("booking.staycationPayment") }}') {
                    const allSelected = [...this.selectedActivities, ...this.selectedActivityPackages];
                    const targetUrl = nextUrl || '{{ route("booking.staycationPayment") }}';

                    fetch('{{ route("booking.storeStaycationActivities") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            selected_activities: allSelected
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
                            let errorMsg = data.message || 'Failed to save activities';
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
                },

                calculateActivitiesTotal() {
                    let total = 0;
                    // Calculate from individual activities
                    this.selectedActivities.forEach(activity => {
                        total += (activity.price || 0) * (activity.guests || 1);
                    });
                    // Calculate from packages
                    this.selectedActivityPackages.forEach(pkg => {
                        total += (pkg.price || 0) * (pkg.quantity || 1);
                    });
                    return '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                calculateSubtotal() {
                    let total = this.accommodationTotal + this.foodTotal;
                    // Add activities total
                    this.selectedActivities.forEach(activity => {
                        total += (activity.price || 0) * (activity.guests || 1);
                    });
                    this.selectedActivityPackages.forEach(pkg => {
                        total += (pkg.price || 0) * (pkg.quantity || 1);
                    });
                    return '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                calculateVAT() {
                    let total = this.accommodationTotal + this.foodTotal;
                    // Add activities total
                    this.selectedActivities.forEach(activity => {
                        total += (activity.price || 0) * (activity.guests || 1);
                    });
                    this.selectedActivityPackages.forEach(pkg => {
                        total += (pkg.price || 0) * (pkg.quantity || 1);
                    });
                    // Calculate VAT on subtotal
                    const vat = total * this.vatRate;
                    return '₱' + vat.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                calculateTotal() {
                    let total = this.accommodationTotal + this.foodTotal;
                    // Add activities total
                    this.selectedActivities.forEach(activity => {
                        total += (activity.price || 0) * (activity.guests || 1);
                    });
                    this.selectedActivityPackages.forEach(pkg => {
                        total += (pkg.price || 0) * (pkg.quantity || 1);
                    });
                    const vat = total * this.vatRate;
                    const grandTotal = total + vat;
                    return '₱' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                decreaseActivityPackageQty(packageId) {
                    const pkg = this.selectedActivityPackages.find(p => p.package_id === String(packageId));
                    if (pkg && pkg.quantity > 1) pkg.quantity--;
                },

                increaseActivityPackageQty(packageId) {
                    const pkg = this.selectedActivityPackages.find(p => p.package_id === String(packageId));
                    if (pkg && pkg.quantity < 20) pkg.quantity++;
                },

                setActivityPackageField(packageId, field, value) {
                    const pkg = this.selectedActivityPackages.find(p => p.package_id === packageId);
                    if (!pkg) return;
                    if (field === 'quantity') {
                        let qty = parseInt(value);
                        if (isNaN(qty) || qty < 1) qty = 1;
                        if (qty > 20) qty = 20;
                        pkg[field] = qty;
                    } else {
                        pkg[field] = value;
                    }
                },

                decreaseActivityGuests(activityId) {
                    const activity = this.selectedActivities.find(a => a.id === String(activityId));
                    if (activity && activity.guests > 1) activity.guests--;
                },

                increaseActivityGuests(activityId) {
                    const activity = this.selectedActivities.find(a => a.id === String(activityId));
                    if (activity && activity.guests < 20) activity.guests++;
                },

                setActivityField(activityId, field, value) {
                    const activity = this.selectedActivities.find(a => a.id === activityId);
                    if (!activity) return;
                    if (field === 'guests') {
                        let guests = parseInt(value);
                        if (isNaN(guests) || guests < 1) guests = 1;
                        if (guests > 20) guests = 20;
                        activity[field] = guests;
                    } else {
                        activity[field] = value;
                    }
                },

                formatCurrency(amount) {
                    return '₱' + Number(amount).toLocaleString();
                },

                getActivityGuests(activityId) {
                    const activity = this.selectedActivities.find(a => a.id === String(activityId));
                    return activity ? activity.guests || 1 : 1;
                },

                getActivityPackageQuantity(packageId) {
                    const pkg = this.selectedActivityPackages.find(p => p.package_id === String(packageId));
                    return pkg ? pkg.quantity || 1 : 1;
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
    </script>
</x-app-layout>

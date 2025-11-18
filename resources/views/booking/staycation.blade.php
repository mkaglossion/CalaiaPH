{{-- resources/views/booking/staycation.blade.php --}}
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="CalaiaPH - Farm stays and outdoor adventures">
    <meta name="keywords" content="farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>Booking Staycation - Calaia Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  </head>
  <body class="bg-[#f5efe7]">
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
          <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
              {{ session('success') }}
          </div>
      @endif
    </div>
    <x-header active="booking" />
    <div class="min-h-screen">
@php
    // Initialize / read booking session
    $booking = session('booking', [
        'checkin'        => null,
        'checkout'       => null,
        'accommodations' => [],
        'booking_type'   => 'general',
        'timestamp'      => now()->timestamp,
        'completed_steps'=> [],
        'current_step'   => 1,
    ]);

    $bookingType  = $booking['booking_type'] ?? 'general';
    $stepNumber   = '1'; // landing page
    $totalSteps   = $bookingType === 'staycation' ? '3' : '4';
    $backLink     = url('/home');

    $completedSteps  = $booking['completed_steps'] ?? [];
    $step1Completed  = in_array(1, $completedSteps);

    $checkinDate  = $booking['checkin'] ?? null;
    $checkoutDate = $booking['checkout'] ?? null;
    $datesSelected = !empty($checkinDate) && !empty($checkoutDate);

    // Calculate nights
    $nights = 0;
    if ($datesSelected) {
        $start = new DateTime($checkinDate);
        $end = new DateTime($checkoutDate);
        $diff = $start->diff($end);
        $nights = $diff->days;
    }

    // ---- NEW: group staycations into glamping + camping & compute stats ----
    $staycations     = $staycations ?? [];
    $glampingStaycations = [];
    $campingStaycations  = [];

    foreach ($staycations as $s) {
        if (($s['type'] ?? null) === 'glamping') {
            $glampingStaycations[] = $s;
        } elseif (($s['type'] ?? null) === 'camping') {
            $campingStaycations[] = $s;
        }
    }

    $glampingMinPrice = !empty($glampingStaycations)
        ? min(array_column($glampingStaycations, 'price'))
        : 0;
    $glampingCount    = count($glampingStaycations);

    $campingMinPrice = !empty($campingStaycations)
        ? min(array_column($campingStaycations, 'price'))
        : 0;
    $campingCount    = count($campingStaycations);

    // Category hero images + descriptions
    $glampingImage = asset('assets/images/carousel/Glamping-1.jpg');
    $campingImage  = asset('assets/images/carousel/Car-camping3.jpg');

    $glampingDescription = 'Luxury camping experience with premium amenities, king-size bed, private bathroom, and panoramic views.';
    $campingDescription  = 'Traditional camping experiences with vehicle access and natural settings.';
@endphp

@if (!$step1Completed || !$datesSelected)
    <script>
        window.location.href = "{{ $backLink }}";
    </script>
@endif

<div class="pt-6 md:pt-12"
     data-accommodation-count="{{ count($accommodationData ?? []) }}"
     data-root-dir="{{ url('/') }}"
     data-is-logged-in="{{ session()->has('user') || session('user.is_guest') === true ? 'true' : 'false' }}"
     data-pet-policy-accepted="{{ session('user.pet_policy') == 1 ? 'true' : 'false' }}"
     data-accommodation-data='@json($accommodationData ?? [])'
     data-booking-completed-steps='@json($booking['completed_steps'] ?? [])'>

    {{-- Header --}}
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Book Staycation</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-gray-500">Plan your perfect getaway at Calaia Nature Park</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mx-0 md:mx-10">
        {{-- Main content --}}
        <div class="lg:col-span-2">
            <div class="flex flex-col">
                {{-- Step indicator --}}
                <small class="text-gray-500 text-sm mb-2">
                    Steps {{ $stepNumber }} of {{ $totalSteps }}
                </small>

                {{-- Navigation --}}
                <div class="flex items-end justify-between my-3">
                    <a href="{{ $backLink }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Back
                    </a>

                    <div class="flex items-center gap-2">
                        @if (session()->has('user.is_guest') && session('user.is_guest'))
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                                style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                                <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Booking section --}}
                <div class="booking-section">
                    {{-- Top nav: accommodation tabs --}}
                    <div class="mb-6">
                        <div
                            class="flex flex-row flex-nowrap justify-between gap-2 pr-2 mt-2 border-b border-gray-200 overflow-x-auto"
                            style="white-space: nowrap;">
                            <div class="flex flex-row flex-nowrap gap-2">
                                @foreach ($accommodationData as $i => $acc)
                                    <button type="button"
                                            class="accom-btn my-2 px-3 py-2 border rounded-md text-xs md:text-sm font-medium transition
                                                   {{ $i === 0 ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 text-gray-700 hover:bg-gray-100' }}"
                                            data-index="{{ $i }}"
                                            aria-label="Select accommodation {{ $i + 1 }}">
                                        Accommodation {{ $i + 1 }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- "Accommodation 1" label like the design --}}
                        <div class="mt-6">
                            <div class="flex flex-col">
                                <span class="text-xs tracking-[0.2em] uppercase text-gray-500">
                                    Accommodation 1
                                </span>
                                <div class="mt-1 h-[2px] w-16"
                                     style="background: linear-gradient(90deg, #b5654a 0%, #f0c27b 100%);"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 2 main category cards: Glamping + Camping --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Glamping card --}}
                        <div class="bg-white rounded-2xl shadow-[0_18px_40px_rgba(15,23,42,0.08)] border border-gray-100 overflow-hidden flex flex-col">
                            <img
                                src="{{ $glampingImage }}"
                                alt="Glamping Options"
                                class="w-full object-cover"
                                style="height: 280px;">

                            <div class="flex flex-col p-6 h-full">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h3 class="text-xl md:text-2xl font-extrabold text-gray-900">
                                        Glamping Options
                                    </h3>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold"
                                        style="background: #ffc44c; color: #2b1b00;">
                                        GLAMPING
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 leading-relaxed mb-3">
                                    {{ $glampingDescription }}
                                </p>

                                {{-- Subtypes line --}}
                                <p class="text-xs text-gray-500 mb-4">
                                    Includes:
                                    <span class="font-semibold text-gray-800">Standard</span> and
                                    <span class="font-semibold text-gray-800">Premium</span> glamping tents.
                                </p>

                                @if($glampingCount > 0)
                                    <p class="text-sm font-semibold mb-1"
                                       style="color: #0092b0;">
                                        Starting at ₱ {{ number_format($glampingMinPrice) }}/night
                                    </p>
                                    <p class="text-xs text-gray-500 mb-6">
                                        {{ $glampingCount }} {{ Str::plural('option', $glampingCount) }} available
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mb-6">No glamping options available.</p>
                                @endif

                                <button
                                    type="button"
                                    class="select-category-btn mt-auto w-full inline-flex items-center justify-center rounded-full text-sm font-semibold text-white px-6 py-3 transition"
                                    data-category="glamping"
                                    data-staycations='@json($glampingStaycations)'
                                    @if($glampingCount === 0) disabled @endif
                                    style="background-color:#8b5b3c; box-shadow:0 10px 20px rgba(139,91,60,0.35);">
                                    Select Glamping Options
                                </button>
                            </div>
                        </div>

                        {{-- Camping card --}}
                        <div class="bg-white rounded-2xl shadow-[0_18px_40px_rgba(15,23,42,0.08)] border border-gray-100 overflow-hidden flex flex-col">
                            <img
                                src="{{ $campingImage }}"
                                alt="Camping Options"
                                class="w-full object-cover"
                                style="height: 280px;">

                            <div class="flex flex-col p-6 h-full">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h3 class="text-xl md:text-2xl font-extrabold text-gray-900">
                                        Camping Options
                                    </h3>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold"
                                        style="background:#31b44b; color:#ffffff;">
                                        CAMPING
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 leading-relaxed mb-3">
                                    {{ $campingDescription }}
                                </p>

                                {{-- Subtypes line --}}
                                <p class="text-xs text-gray-500 mb-4">
                                    Includes:
                                    <span class="font-semibold text-gray-800">Bell Tent</span>,
                                    <span class="font-semibold text-gray-800">Dome Tent</span>,
                                    <span class="font-semibold text-gray-800">BYOT</span>, and
                                    <span class="font-semibold text-gray-800">Car Camping</span>.
                                </p>

                                @if($campingCount > 0)
                                    <p class="text-sm font-semibold mb-1"
                                       style="color: #0092b0;">
                                        Starting at ₱ {{ number_format($campingMinPrice) }}/night
                                    </p>
                                    <p class="text-xs text-gray-500 mb-6">
                                        {{ $campingCount }} {{ Str::plural('option', $campingCount) }} available
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mb-6">No camping options available.</p>
                                @endif

                                <button
                                    type="button"
                                    class="select-category-btn mt-auto w-full inline-flex items-center justify-center rounded-full text-sm font-semibold text-white px-6 py-3 transition"
                                    data-category="camping"
                                    data-staycations='@json($campingStaycations)'
                                    @if($campingCount === 0) disabled @endif
                                    style="background-color:#8b5b3c; box-shadow:0 10px 20px rgba(139,91,60,0.35);">
                                    Select Camping Options
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Add accommodation button --}}
                    @if (count($accommodationData) < 4)
                        <div class="my-6">
                            <button type="button"
                                    id="add-accommodation"
                                    class="w-full border border-transparent rounded-full text-sm md:text-base font-semibold text-gray-700 py-3 px-5 min-h-[48px] hover:bg-gray-50 transition inline-flex items-center justify-center"
                                    aria-label="Add another accommodation to your booking">
                                <span class="mr-2 text-lg">+</span>
                                Add another accommodation
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: booking summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Booking Summary</h3>

                @if($datesSelected)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Check-in:</span>
                            <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkinDate)->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Check-out:</span>
                            <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkoutDate)->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nights:</span>
                            <span class="text-sm font-medium">{{ $nights }}</span>
                        </div>
                        <hr class="my-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Accommodations:</span>
                            <span class="text-sm font-medium" id="selected-accommodations">None selected</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold">
                            <span>Total:</span>
                            <span id="total-price">₱ 0</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button class="w-full bg-emerald-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed" id="proceed-btn" disabled>
                            Proceed to Next Step
                        </button>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Please select your dates to see the booking summary.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
{{-- Glamping Modal --}}
<div id="glamping-modal" class="fixed inset-0 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full mx-4 max-h-[95vh] overflow-hidden border border-gray-200">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Select Glamping Option</h2>
                <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none" data-modal="glamping-modal" style="line-height: 1;">
                    ×
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="glamping-options">
                    <!-- Options will be populated by JavaScript -->
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition" data-modal="glamping-modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Camping Modal --}}
<div id="camping-modal" class="fixed inset-0 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full mx-4 max-h-[95vh] overflow-hidden border border-gray-200">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Select Camping Option</h2>
                <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none" data-modal="camping-modal" style="line-height: 1;">
                    ×
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="camping-options">
                    <!-- Options will be populated by JavaScript -->
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition" data-modal="camping-modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Toast --}}
    <div class="toast fixed bottom-4 right-4 flex items-center text-white bg-emerald-600 border-0 rounded-lg shadow-lg px-4 py-3 hidden"
         role="alert" aria-live="assertive" aria-atomic="true">
        <div class="flex items-center w-full">
            <div class="toast-body text-sm flex-1"></div>
            <button type="button"
                    class="ml-3 text-white text-xs font-semibold uppercase tracking-wide btn-close"
                    data-bs-dismiss="toast"
                    aria-label="Close">
                ✕
            </button>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
<script>
    const el = document.querySelector('.pt-6.md\\:pt-12');
    window.accommodationCount   = parseInt(el.dataset.accommodationCount);
    window.ROOT_DIR             = el.dataset.rootDir;
    window.isLoggedIn           = el.dataset.isLoggedIn === 'true';
    window.petPolicyAccepted    = el.dataset.petPolicyAccepted === 'true';
    window.accommodationData    = JSON.parse(el.dataset.accommodationData);
    window.bookingCompletedSteps= JSON.parse(el.dataset.bookingCompletedSteps);
    window.currentStep          = 'staycation';

    // Global booking functions
    window.FeeCalculator = {
        calculateTotal: function(accommodations, nights) {
            let total = 0;
            accommodations.forEach(acc => {
                if (acc.staycation && acc.staycation.price) {
                    total += acc.staycation.price * nights;
                }
            });
            return total;
        }
    };

    window.updateBookingSummary = function() {
        const selectedAccommodations = [];
        const accommodationElements = document.querySelectorAll('.accommodation-item');

        accommodationElements.forEach((accEl, index) => {
            const selectedStaycation = accEl.querySelector('.selected-staycation');
            if (selectedStaycation && selectedStaycation.dataset.staycation) {
                const staycation = JSON.parse(selectedStaycation.dataset.staycation);
                selectedAccommodations.push({
                    accommodation_index: index,
                    staycation: staycation
                });
            }
        });

        // Update selected accommodations text with details
        const selectedAccText = document.getElementById('selected-accommodations');
        if (selectedAccText) {
            if (selectedAccommodations.length === 0) {
                selectedAccText.textContent = 'None selected';
            } else {
                const selectedNames = selectedAccommodations.map(acc => acc.staycation.name).join(', ');
                selectedAccText.textContent = selectedNames;
            }
        }

        // Calculate total price
        const checkinDate = new Date('{{ $checkinDate }}');
        const checkoutDate = new Date('{{ $checkoutDate }}');
        const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));

        const totalPrice = window.FeeCalculator.calculateTotal(selectedAccommodations, nights);
        const totalPriceEl = document.getElementById('total-price');
        if (totalPriceEl) {
            totalPriceEl.textContent = '₱ ' + totalPrice.toLocaleString();
        }

        // Enable/disable proceed button
        const proceedBtn = document.getElementById('proceed-btn');
        if (proceedBtn) {
            proceedBtn.disabled = selectedAccommodations.length === 0;
        }
    };

    window.ensureBookingSummaryVisible = function() {
        const summaryEl = document.querySelector('.bg-white.rounded-xl.shadow-md.p-6.sticky');
        if (summaryEl) {
            summaryEl.style.display = 'block';
        }
    };

    window.initializeBookingSummary = function() {
        window.updateBookingSummary();
        window.ensureBookingSummaryVisible();
    };

    window.addAccommodationWithValidation = function() {
        const currentCount = window.accommodationCount || 0;
        if (window.BOOKING_CONFIG && currentCount >= window.BOOKING_CONFIG.maxAccommodations) {
            showToast(`Maximum of ${window.BOOKING_CONFIG.maxAccommodations} accommodations allowed`, 'warning');
            return;
        }

        // Add new accommodation tab
        const tabsContainer = document.querySelector('.flex.flex-row.flex-nowrap.justify-between.gap-2.pr-2.mt-2.border-b.border-gray-200');
        if (tabsContainer) {
            const newIndex = currentCount;
            const newTab = document.createElement('button');
            newTab.type = 'button';
            newTab.className = 'accom-btn my-2 px-3 py-2 border rounded-md text-xs md:text-sm font-medium transition border-gray-300 text-gray-700 hover:bg-gray-100';
            newTab.dataset.index = newIndex;
            newTab.setAttribute('aria-label', `Select accommodation ${newIndex + 1}`);
            newTab.textContent = `Accommodation ${newIndex + 1}`;
            tabsContainer.querySelector('.flex.flex-row.flex-nowrap.gap-2').appendChild(newTab);
        }

        // Add new accommodation container
        const container = document.querySelector('.staycation-container');
        if (container) {
            const newAccDiv = document.createElement('div');
            newAccDiv.className = 'accommodation-item';
            newAccDiv.dataset.index = currentCount;
            newAccDiv.innerHTML = `
                <div class="selected-staycation" style="display: none;"></div>
                <p class="text-center text-gray-500">Please select a staycation option above.</p>
            `;
            container.appendChild(newAccDiv);
        }

        window.accommodationCount++;
        window.updateBookingSummary();
    };

    // Toast function
    function showToast(message, type = 'info') {
        const toastEl = document.querySelector('.toast');
        if (toastEl) {
            const toastBody = toastEl.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = message;
            }
            toastEl.classList.remove('hidden');
            setTimeout(() => {
                toastEl.classList.add('hidden');
            }, 3000);
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Staycation page loaded - initializing booking summary');

        setTimeout(function () {
            if (typeof window.ensureBookingSummaryVisible === 'function') {
                window.ensureBookingSummaryVisible();
            }
            if (typeof window.initializeBookingSummary === 'function') {
                window.initializeBookingSummary();
            }
            initializeStaycationValidation();
        }, 100);
    });

    window.addEventListener('load', function () {
        setTimeout(function () {
            if (typeof window.ensureBookingSummaryVisible === 'function') {
                window.ensureBookingSummaryVisible();
            }
        }, 200);
    });

    function initializeStaycationValidation() {
        const addAccommodationBtn = document.getElementById('add-accommodation');
        if (addAccommodationBtn) {
            addAccommodationBtn.addEventListener('click', function () {
                const currentCount = window.accommodationCount || 0;
                if (window.BOOKING_CONFIG && currentCount >= window.BOOKING_CONFIG.maxAccommodations) {
                    showToast(`Maximum of ${window.BOOKING_CONFIG.maxAccommodations} accommodations allowed`, 'warning');
                    return;
                }

                if (typeof window.addAccommodationWithValidation === 'function') {
                    window.addAccommodationWithValidation();
                }
            });
        }

        // Modal functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('select-category-btn')) {
                const category = e.target.dataset.category;
                const staycations = JSON.parse(e.target.dataset.staycations || '[]');
                openCategoryModal(category, staycations);
            }

            if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
                const modalId = e.target.dataset.modal || e.target.closest('.close-modal').dataset.modal;
                closeModal(modalId);
            }

            if (e.target.classList.contains('select-staycation-modal')) {
                const staycationData = e.target.dataset.staycation;
                if (staycationData) {
                    const staycation = JSON.parse(staycationData);
                    selectStaycationFromModal(staycation);
                }
            }
        });

        // Modal close functionality is now only via the close button

        console.log('Staycation validation initialized');
    }

    function openCategoryModal(category, staycations) {
        const modalId = `${category}-modal`;
        const modal = document.getElementById(modalId);
        const optionsContainer = document.getElementById(`${category}-options`);

        if (!modal || !optionsContainer) return;

        // Clear previous options
        optionsContainer.innerHTML = '';

        // Populate options
        staycations.forEach(staycation => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'bg-white rounded-xl shadow-md overflow-hidden transition transform hover:-translate-y-1 hover:shadow-xl h-full flex flex-col border border-gray-100';

            optionDiv.innerHTML = `
                <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover" style="height: 220px;">
                <div class="flex flex-col p-4 h-full">
                    <div class="flex justify-between items-start mb-2 gap-2">
                        <h5 class="font-bold text-base md:text-lg mb-0" style="line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em;">
                            ${staycation.name}
                        </h5>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ml-2" style="background: linear-gradient(135deg, ${category === 'glamping' ? '#FFD700 0%, #FFA500 100%' : '#32CD32 0%, #228B22 100%'}); color: ${category === 'glamping' ? '#333' : '#fff'};">
                            ${category.charAt(0).toUpperCase() + category.slice(1)}
                        </span>
                    </div>
                    <p class="text-gray-500 text-xs md:text-sm mb-3" style="flex-grow: 1;">
                        Capacity: ${staycation.capacity} guests (${staycation.adults} adults, ${staycation.children} children)
                    </p>
                    <div class="mt-auto">
                        <span class="block font-bold text-primary-600 text-base md:text-lg mb-2">
                            ₱ ${staycation.price.toLocaleString()}/night
                        </span>
                        <button class="select-staycation-modal w-full inline-flex justify-center items-center rounded-lg text-xs md:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition px-4 py-3"
                                data-staycation='${JSON.stringify(staycation).replace(/'/g, "'")}'
                                style="font-size: 0.9rem;">
                            Select This Option
                        </button>
                    </div>
                </div>
            `;

            optionsContainer.appendChild(optionDiv);
        });

        // Show modal
        modal.classList.remove('hidden');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function selectStaycation(staycation) {
        // Find the active accommodation tab
        const activeTab = document.querySelector('.accom-btn.bg-gray-900');
        if (!activeTab) return;

        const accommodationIndex = parseInt(activeTab.dataset.index);

        // Find or create the accommodation item container
        let accommodationItem = document.querySelector(`.accommodation-item[data-index="${accommodationIndex}"]`);
        if (!accommodationItem) {
            // Create the container if it doesn't exist
            const container = document.querySelector('.booking-section .flex.flex-col');
            if (container) {
                accommodationItem = document.createElement('div');
                accommodationItem.className = 'accommodation-item';
                accommodationItem.dataset.index = accommodationIndex;
                accommodationItem.innerHTML = `
                    <div class="selected-staycation" style="display: none;"></div>
                    <p class="text-center text-gray-500">Please select a staycation option above.</p>
                `;
                container.appendChild(accommodationItem);
            }
        }

        if (accommodationItem) {
            // Update the selected staycation
            const selectedDiv = accommodationItem.querySelector('.selected-staycation');
            if (selectedDiv) {
                selectedDiv.dataset.staycation = JSON.stringify(staycation);
                selectedDiv.style.display = 'block';
            }

            // Update the display text
            const textP = accommodationItem.querySelector('p');
            if (textP) {
                textP.textContent = `Selected: ${staycation.name}`;
                textP.className = 'text-center text-green-600 font-semibold';
            }

            // Update booking summary
            window.updateBookingSummary();
        }
    }

    function selectStaycationFromModal(staycation) {
        // Close the modal
        const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50:not(.hidden)');
        if (modal) {
            modal.classList.add('hidden');
        }

        // Select the staycation
        selectStaycation(staycation);
    }
</script>


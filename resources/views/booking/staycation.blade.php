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

    // Check if accommodations are selected
    $accommodationsSelected = !empty($booking['accommodations']) && count(array_filter($booking['accommodations'])) > 0;

    // For staycation, adjust step display
    if ($bookingType === 'staycation') {
        if ($accommodationsSelected) {
            $stepNumber = '1'; // Show as step 1 (completed booking)
        }
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
     data-accommodation-data='@json($accommodationData ?? [])'>

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
                    @if($bookingType === 'staycation')
                        Step 1 of 3: Select Your Staycation
                    @else
                        Steps {{ $stepNumber }} of {{ $totalSteps }}
                    @endif
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
                        <div class="flex flex-row flex-nowrap justify-between gap-2 pr-2 p-2 mt-2 border-b border-gray-200 overflow-x-auto"
                            style="white-space: nowrap;">
                            <div class="flex flex-row flex-nowrap gap-2" id="accommodation-tabs">
                                <!-- Accommodation tabs will be populated by JavaScript -->
                                <div class="accom-tab-item relative" data-index="0">
                                    <button type="button"
                                            class="accom-btn my-2 px-4 py-2 border rounded-md text-xs md:text-sm font-medium transition bg-gray-900 text-white border-gray-900"
                                            data-index="0"
                                            aria-label="Select accommodation 1">
                                        ACCOMMODATION 1
                                    </button>
                                </div>
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
                                    <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                                        Glamping Options
                                    </h3>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-base font-semibold"
                                        style="background: #ffc44c; color: #2b1b00;">
                                        GLAMPING
                                    </span>
                                </div>

                                <p class="text-lg text-gray-600 leading-relaxed mb-3">
                                    {{ $glampingDescription }}
                                </p>

                                {{-- Subtypes line --}}
                                <p class="text-base text-gray-500 mb-4">
                                    Includes:
                                    <span class="font-semibold text-gray-800">Standard</span> and
                                    <span class="font-semibold text-gray-800">Premium</span> glamping tents.
                                </p>

                                @if($glampingCount > 0)
                                     <p class="text-lg font-semibold mb-1"
                                        style="color: #7a7d52;">
                                         Starts at ₱5,760/night (Weekdays: Sun.-Thu.)<br>• ₱7,200/night (Weekends: Fri.-Sat.)
                                     </p>
                                     <p class="text-base text-gray-500 mb-6">
                                         {{ $glampingCount }} {{ Str::plural('option', $glampingCount) }} available
                                     </p>
                                 @else
                                     <p class="text-base text-gray-400 mb-6">No glamping options available.</p>
                                 @endif

                                <button
                                    type="button"
                                    class="select-category-btn mt-auto w-full inline-flex items-center justify-center rounded-full text-lg font-semibold text-white px-6 py-3 transition"
                                    data-category="glamping"
                                    data-staycations='@json($glampingStaycations)'
                                    @if($glampingCount === 0) disabled @endif
                                    style="background-color:#8f5d54; box-shadow:0 10px 20px rgba(143,93,84,0.35);">
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
                                    <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                                        Camping Options
                                    </h3>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-base font-semibold"
                                        style="background:#31b44b; color:#ffffff;">
                                        CAMPING
                                    </span>
                                </div>

                                <p class="text-lg text-gray-600 leading-relaxed mb-3">
                                    {{ $campingDescription }}
                                </p>

                                {{-- Subtypes line --}}
                                <p class="text-base text-gray-500 mb-4">
                                    Includes:
                                    <span class="font-semibold text-gray-800">Bell Tent</span>,
                                    <span class="font-semibold text-gray-800">Dome Tent</span>,
                                    <span class="font-semibold text-gray-800">BYOT</span>, and
                                    <span class="font-semibold text-gray-800">Car Camping</span>.
                                </p>

                                @if($campingCount > 0)
                                     <p class="text-lg font-semibold mb-1"
                                        style="color: #7a7d52;">
                                         Starts at ₱300/night (BYOT/Car Camping)<br>• ₱3,000/night (Bell/Dome Tents)
                                     </p>
                                     <p class="text-base text-gray-500 mb-6">
                                         {{ $campingCount }} {{ Str::plural('option', $campingCount) }} available
                                     </p>
                                 @else
                                     <p class="text-base text-gray-400 mb-6">No camping options available.</p>
                                 @endif

                                <button
                                    type="button"
                                    class="select-category-btn mt-auto w-full inline-flex items-center justify-center rounded-full text-lg font-semibold text-white px-6 py-3 transition"
                                    data-category="camping"
                                    data-staycations='@json($campingStaycations)'
                                    @if($campingCount === 0) disabled @endif
                                    style="background-color:#8f5d54; box-shadow:0 10px_20px_rgba(143,93,84,0.35);">
                                    Select Camping Options
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Selected accommodations display --}}
                    <div id="selected-accommodations-container" class="mt-6 space-y-4">
                        <!-- Selected accommodations will be displayed here -->
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
                            <span class="text-sm font-medium" id="selected-accommodations-count">0 selected</span>
                        </div>
                        <div id="selected-accommodations-summary" class="mt-2 space-y-1">
                            <!-- Selected accommodations details will be populated here -->
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Guests:</span>
                            <span class="text-sm font-medium" id="total-guests">0</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold">
                            <span>Total:</span>
                            <span id="total-price">₱ 0</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button class="w-full bg-emerald-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed" id="proceed-btn" disabled onclick="proceedToActivities()">
                            Proceed to Activities
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
<div id="glamping-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="glamping-modal-title">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 transition-opacity duration-300" id="glamping-modal-backdrop" onclick="if (!event.target.closest('#glamping-modal-content')) closeModal('glamping-modal')"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="glamping-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="glamping-modal-title" class="text-2xl font-bold text-gray-900">Select Glamping Options</h2>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" data-modal="glamping-modal" aria-label="Close modal" style="line-height: 1;">
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="glamping-options">
                        @foreach($glampingStaycations as $staycation)
                            <div class="bg-white border rounded-xl p-4 flex items-center gap-4">
                                <input type="checkbox"
                                       class="staycation-checkbox"
                                       data-id="{{ $staycation['id'] }}"
                                       data-name="{{ $staycation['name'] }}"
                                       data-price="{{ $staycation['price'] }}"
                                       id="glamping-{{ $staycation['id'] }}">
                                <label for="glamping-{{ $staycation['id'] }}" class="flex-1 cursor-pointer">
                                    <div class="font-semibold">{{ $staycation['name'] }}</div>
                                    <div class="text-gray-500 text-sm">₱{{ number_format($staycation['price']) }}/night</div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-400" data-modal="glamping-modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Camping Modal --}}
<div id="camping-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="camping-modal-title">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 transition-opacity duration-300" id="camping-modal-backdrop" onclick="if (!event.target.closest('#camping-modal-content')) closeModal('camping-modal')"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="camping-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="camping-modal-title" class="text-2xl font-bold text-gray-900">Select Camping Options</h2>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" data-modal="camping-modal" aria-label="Close modal" style="line-height: 1;">
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="camping-options">
                        @foreach($campingStaycations as $staycation)
                            <div class="bg-white border rounded-xl p-4 flex items-center gap-4">
                                <input type="checkbox"
                                       class="staycation-checkbox"
                                       data-id="{{ $staycation['id'] }}"
                                       data-name="{{ $staycation['name'] }}"
                                       data-price="{{ $staycation['price'] }}"
                                       id="camping-{{ $staycation['id'] }}">
                                <label for="camping-{{ $staycation['id'] }}" class="flex-1 cursor-pointer">
                                    <div class="font-semibold">{{ $staycation['name'] }}</div>
                                    <div class="text-gray-500 text-sm">₱{{ number_format($staycation['price']) }}/night</div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-400" data-modal="camping-modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pet Policy Modal --}}
<div id="pet-policy-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="pet-policy-modal-title">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 transition-opacity duration-300" id="pet-policy-modal-backdrop" onclick="if (!event.target.closest('#pet-policy-modal-content')) closeModal('pet-policy-modal')"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="pet-policy-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="pet-policy-modal-title" class="text-2xl font-bold text-gray-900">Pet Policy</h2>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" data-modal="pet-policy-modal" aria-label="Close modal" style="line-height: 1;">
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto text-center">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center justify-center">
                            <span class="text-3xl mr-2">🐾</span>
                            Pets are Welcome at Calaia Nature Park!
                        </h3>
                        <p class="text-lg text-gray-700 leading-relaxed">We're happy to welcome well-socialized, non-aggressive pets to Calaia! To ensure a safe and enjoyable experience for everyone, including our animal residents and guests, we kindly ask pet owners to follow these guidelines:</p>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2 flex items-center justify-center">
                                <span class="text-2xl mr-2">🐶</span>
                                Owner Responsibility
                            </h4>
                            <p class="text-gray-700">Pet owners are fully responsible for the behavior and care of their pets at all times. Pets must never be left unattended.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2 flex items-center justify-center">
                                <span class="text-2xl mr-2">🐕</span>
                                Leash Policy
                            </h4>
                            <p class="text-gray-700">Pets may be off-leash only when under the close and careful supervision of their owners.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2 flex items-center justify-center">
                                <span class="text-2xl mr-2">🦙</span>
                                Respect for Farm Animals
                            </h4>
                            <p class="text-gray-700">Please ensure your pets do not disturb or chase the grazing animals on the property.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2 flex items-center justify-center">
                                <span class="text-2xl mr-2">🏕️</span>
                                Overnight Stays & Pet Fee
                            </h4>
                            <p class="text-gray-700 mb-2">Kindly inform us upon booking if you will be bringing a pet.</p>
                            <p class="text-gray-700 mb-2">If your pet will stay inside the glamping tent, a pet fee of ₱2,000 per pet applies and must be paid in full to confirm your booking.</p>
                            <p class="text-gray-700">Any damage to the tent, bedding, or furnishings caused by pets will be charged accordingly.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2 flex items-center justify-center">
                                <span class="text-2xl mr-2">📋</span>
                                Health & Cleanliness
                            </h4>
                            <p class="text-gray-700">All pets must be up to date on vaccinations (including rabies) and treated for external parasites. We will kindly ask for the date of your pet's last parasite treatment.</p>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="text-xl font-semibold text-red-900 mb-2 flex items-center justify-center">
                                <span class="text-2xl mr-2">⚠️</span>
                                Damage & Liability
                            </h4>
                            <p class="text-red-800">Owners will be held liable for any damage caused to property or injury inflicted on farm animals, other pets, fellow guests, or Calaia staff.</p>
                        </div>
                    </div>

                    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-lg font-semibold text-green-900 flex items-center justify-center">
                            <span class="text-2xl mr-2">🐾🌿</span>
                            We appreciate your cooperation and understanding. Let's work together to keep Calaia a safe, peaceful, and pet-friendly space for all!
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-400" data-modal="pet-policy-modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Toast --}}
    <div class="toast fixed bottom-4 right-4 flex items-center text-white bg-emerald-600 border-0 rounded-lg shadow-lg px-4 py-3 hidden z-60"
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
    window.accommodationCount = 1; // Start with 1 accommodation
    window.ROOT_DIR = el.dataset.rootDir;
    window.accommodationData = JSON.parse(el.dataset.accommodationData || '[]');
    window.selectedAccommodations = {}; // Store selected accommodations by index

    // Global booking functions
    window.FeeCalculator = {
        calculateTotal: function(accommodations, checkin, checkout) {
            if (!checkin || !checkout) return 0;

            const checkinDate = new Date(checkin);
            const checkoutDate = new Date(checkout);
            const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
            if (nights <= 0) return 0;

            let total = 0;

            Object.values(accommodations).forEach(acc => {
                if (!acc || !acc.staycation) return;

                const staycation = acc.staycation;
                const guests = acc.guests || 1;
                const name = staycation.name.toLowerCase();

                if (name.includes('standard') || name.includes('premium')) {
                    let accTotal = 0;
                    let current = new Date(checkinDate);
                    while (current < checkoutDate) {
                        const day = current.getDay();
                        const isWeekend = day === 5 || day === 6; // Fri & Sat
                        let basePrice = 0;
                        if (name.includes('standard')) basePrice = isWeekend ? 6000 : 4800;
                        if (name.includes('premium')) basePrice = isWeekend ? 7200 : 5760;

                        const extraGuests = Math.max(0, guests - 4);
                        accTotal += basePrice + (extraGuests * 400);
                        current.setDate(current.getDate() + 1);
                    }
                    total += accTotal;
                } else if (name.includes('bell') || name.includes('dome')) {
                    total += 3000 * nights;
                } else if (name.includes('byot') || name.includes('car')) {
                    total += 300 * guests * nights;
                }
            });

            return total;
        }
    };


    window.updateBookingSummary = function() {
        const summaryContainer = document.getElementById('selected-accommodations-summary');
        if (!summaryContainer) return;

        summaryContainer.innerHTML = ''; // Clear previous content
        let selectedCount = 0;
        let totalPrice = 0;
        let totalGuests = 0;

        // Loop over all selected accommodations
        Object.entries(window.selectedAccommodations).forEach(([key, accommodation]) => {
            if (!accommodation || !accommodation.staycation) return;

            selectedCount++;

            const guests = accommodation.guests;
            totalGuests += guests;
            const staycation = accommodation.staycation;
            const name = staycation.name;

            // Calculate price for this accommodation
            let accommodationPrice = 0;
            const checkin = new Date('{{ $checkinDate }}');
            const checkout = new Date('{{ $checkoutDate }}');
            const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));

            const lowerName = name.toLowerCase();
            if (lowerName.includes('standard') || lowerName.includes('premium')) {
                let current = new Date(checkin);
                while (current < checkout) {
                    const day = current.getDay();
                    const isWeekend = day === 5 || day === 6; // Fri/Sat
                    let basePrice = 0;
                    if (lowerName.includes('standard')) basePrice = isWeekend ? 6000 : 4800;
                    if (lowerName.includes('premium')) basePrice = isWeekend ? 7200 : 5760;
                    const extraGuests = Math.max(0, guests - 4);
                    accommodationPrice += basePrice + (extraGuests * 400);
                    current.setDate(current.getDate() + 1);
                }
            } else if (lowerName.includes('bell') || lowerName.includes('dome')) {
                accommodationPrice = 3000 * nights;
            } else if (lowerName.includes('byot') || lowerName.includes('car')) {
                accommodationPrice = 300 * guests * nights;
            }

            // Calculate pet fee
            const petFee = (accommodation.pet_agreement && accommodation.num_pets > 0) ? 2000 * accommodation.num_pets : 0;
            const totalAccPrice = accommodationPrice + petFee;

            totalPrice += totalAccPrice; // Add to total

            // Display individual accommodation with price breakdown
            const summaryItem = document.createElement('div');
            summaryItem.className = 'text-xs text-gray-600 space-y-1';

            const accommodationDiv = document.createElement('div');
            accommodationDiv.textContent = `ACCOMMODATION: ${name} - Guests: ${guests} - ₱ ${accommodationPrice.toLocaleString()}`;
            summaryItem.appendChild(accommodationDiv);

            if (petFee > 0) {
                const petDiv = document.createElement('div');
                petDiv.textContent = `Pet Fee (${accommodation.num_pets} pet${accommodation.num_pets > 1 ? 's' : ''}): ₱ ${petFee.toLocaleString()}`;
                summaryItem.appendChild(petDiv);
            }

            summaryContainer.appendChild(summaryItem);
        });

        // Update total price element
        const totalPriceEl = document.getElementById('total-price');
        if (totalPriceEl) totalPriceEl.textContent = `₱ ${totalPrice.toLocaleString()}`;

        // Update total guests element
        const totalGuestsEl = document.getElementById('total-guests');
        if (totalGuestsEl) totalGuestsEl.textContent = totalGuests;

        // Update selected count display
        const selectedAccText = document.getElementById('selected-accommodations-count');
        if (selectedAccText) selectedAccText.textContent = `${selectedCount} selected`;

        // Enable/disable proceed button
        const proceedBtn = document.getElementById('proceed-btn');
        if (proceedBtn) proceedBtn.disabled = selectedCount === 0;
    };

    window.initializeBookingSummary = function() {
        window.updateBookingSummary();
    };

    window.addAccommodationWithValidation = function() {
        if (window.accommodationCount >= 4) {
            showToast('Maximum of 4 accommodations allowed', 'error');
            return;
        }

        const newIndex = window.accommodationCount;
        window.accommodationCount++;

        // Create new tab
        const tabsContainer = document.getElementById('accommodation-tabs');
        if (tabsContainer) {
            const newTab = document.createElement('div');
            newTab.className = 'accom-tab-item relative';
            newTab.dataset.index = newIndex;
            newTab.innerHTML = `
                <button type="button"
                        class="accom-btn my-2 px-4 py-2 border rounded-md text-xs md:text-sm font-medium transition border-gray-300 text-gray-700 hover:bg-gray-100"
                        data-index="${newIndex}"
                        aria-label="Select accommodation ${newIndex + 1}">
                    ACCOMMODATION ${newIndex + 1}
                </button>
                <button type="button" 
                        class="remove-accom absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-600 transition"
                        data-index="${newIndex}"
                        aria-label="Remove accommodation ${newIndex + 1}">
                    ×
                </button>
            `;
            tabsContainer.appendChild(newTab);
        }

        // Update the "Add accommodation" button visibility
        const addButton = document.getElementById('add-accommodation');
        if (addButton && window.accommodationCount >= 4) {
            addButton.style.display = 'none';
        }

        window.updateBookingSummary();
        initializeTabEvents();
        
        // Show success toast
        showToast(`ACCOMMODATION ${newIndex + 1} added successfully`, 'success');
    };

    window.removeAccommodation = function(index) {
        // Remove from selected accommodations
        delete window.selectedAccommodations[index];
        
        // Remove the tab
        const tabToRemove = document.querySelector(`.accom-tab-item[data-index="${index}"]`);
        if (tabToRemove) {
            tabToRemove.remove();
        }

        // Remove the accommodation display
        const accDisplay = document.querySelector(`.accommodation-display[data-index="${index}"]`);
        if (accDisplay) {
            accDisplay.remove();
        }

        // Update accommodation count
        window.accommodationCount--;

        // If we removed the active tab, switch to first tab and update label
        const activeTab = document.querySelector('.accom-btn.bg-gray-900');
        if (!activeTab && window.accommodationCount > 0) {
            const firstTab = document.querySelector('.accom-tab-item:first-child .accom-btn');
            if (firstTab) {
                firstTab.click();
            }
        }

        // Show "Add accommodation" button if we're below max
        const addButton = document.getElementById('add-accommodation');
        if (addButton && window.accommodationCount < 4) {
            addButton.style.display = 'flex';
        }

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

    function initializeTabEvents() {
        // Tab click events
        document.querySelectorAll('.accom-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.dataset.index;

                // Update active tab styling
                document.querySelectorAll('.accom-btn').forEach(b => {
                    b.classList.remove('bg-gray-900', 'text-white', 'border-gray-900');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
                });
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
                this.classList.add('bg-gray-900', 'text-white', 'border-gray-900');
            });
        });

        // Remove accommodation events
        document.querySelectorAll('.remove-accom').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const index = this.dataset.index;
                if (window.accommodationCount > 1) {
                    window.removeAccommodation(index);
                    showToast('Accommodation removed', 'success');
                } else {
                    showToast('At least one accommodation is required', 'error');
                }
            });
        });
    }

    function initializeRemoveSelectedButtons() {
        document.querySelectorAll('.remove-selected-accom').forEach(btn => {
            btn.onclick = function() {
                const id = this.dataset.id;
                delete window.selectedAccommodations[id];
                displaySelectedAccommodation(id, null);
                window.updateBookingSummary();
            };
        });
    }

    // --- Display selected accommodation card ---
    function displaySelectedAccommodation(key, acc) {
        const container = document.getElementById('selected-accommodations-container');
        if (!container) return;

        const existing = container.querySelector(`.accommodation-display[data-key="${key}"]`);
        if (existing) existing.remove();
        if (!acc) return;

        const staycation = acc.staycation;
        const guests = acc.guests || 1;
        const div = document.createElement('div');
        div.className = 'accommodation-display bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-2';
        div.dataset.key = key;
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-12 h-12 rounded-lg object-cover">
                    <div>
                        <h4 class="font-semibold text-gray-900">${staycation.name}</h4>
                        <p class="text-xs text-gray-500">Guests: ${guests}</p>
                    </div>
                </div>
                <button class="remove-selected-accom text-red-500 hover:text-red-700 text-lg font-bold" data-key="${key}">×</button>
            </div>
        `;
        container.appendChild(div);

        const removeBtn = div.querySelector('.remove-selected-accom');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                delete window.selectedAccommodations[key];
                div.remove();
                window.updateBookingSummary();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            if (typeof window.initializeBookingSummary === 'function') {
                window.initializeBookingSummary();
            }
            initializeStaycationValidation();
            initializeTabEvents();
        }, 100);
    });

    function initializeStaycationValidation() {
        const addAccommodationBtn = document.getElementById('add-accommodation');
        if (addAccommodationBtn) {
            addAccommodationBtn.addEventListener('click', function () {
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
                    try {
                        const staycation = JSON.parse(staycationData);
                        selectStaycationFromModal(staycation, e.target);
                    } catch (error) {
                        console.error('Error parsing staycation data:', error);
                        showToast('Error selecting staycation option', 'error');
                    }
                }
            }
        });
    }

    // Global variables for modal management
    let activeModal = null;
    let previousFocusElement = null;
    let focusableElements = [];

    function getCapacityText(staycation) {
        const capacity = staycation.capacity || 0;
        const adults = staycation.adults || 0;
        const children = staycation.children || 0;
        const mattress = staycation.mattress || 0;
        const lowerName = staycation.name.toLowerCase();
        const mattressText = (lowerName.includes('bell') || lowerName.includes('dome')) ? '' : ` + extra mattress available ${mattress}`;
        const petsText = (lowerName.includes('glamping') || lowerName.includes('bell') || lowerName.includes('dome')) ? ', 2 pets' : '';
        return `Capacity: ${capacity} guests (${adults} adults, ${children} children${mattressText}${petsText})`;
    }

    function getPriceDisplay(staycation) {
        const lowerName = staycation.name.toLowerCase();
        let price;
        if (lowerName.includes('standard')) {
            price = '₱4,800/night (Weekdays: Sun.-Thu.)<br>• ₱6,000/night (Weekends: Fri.-Sat.)';
        } else if (lowerName.includes('premium')) {
            price = '₱5,760/night (Weekdays: Sun.-Thu.)<br>• ₱7,200/night (Weekends: Fri.-Sat.)';
        } else if (lowerName.includes('bell') || lowerName.includes('dome')) {
            price = 'Starts at ₱3,000/tent/night';
        } else if (lowerName.includes('byot') || lowerName.includes('car')) {
            price = 'Starts at ₱300/tent or camp space + ₱500/pax overnight fee';
        } else {
            price = 'Starts at ₱ ' + staycation.price.toLocaleString() + '/night';
        }
        console.log('getPriceDisplay for', staycation.name, ':', price);
        return price;
    }

    function getFeaturesList(name) {
        const lowerName = name.toLowerCase();
        if (lowerName.includes('standard')) {
            return `
                <li>• With Private Toilet & Bath</li>
                <li>• Tent good for 4 pax (max of 6; additional Php 400/pax/night for extra mattress)</li>
                <li>• Assigned heated shower with soap, shampoo & toilet with bidet (approx. 10 meters from tent)</li>
                <li>• Access to Wi-Fi</li>
                <li>• Private picnic table and fire pit/grill</li>
                <li>• Complimentary coffee/tea and drinking water in tent</li>
                <li>• Bathroom towels, dental & vanity kit, sleeping eye masks</li>
                <li>• Convenience outlet</li>
                <li>• Pet-friendly*</li>
            `;
        } else if (lowerName.includes('premium')) {
            return `
                <li>• With Private Toilet & Bath</li>
                <li>• Tent good for 4 pax (max of 6; additional Php 400/pax/night for extra mattress)</li>
                <li>• Exclusive heated shower with soap, shampoo & toilet with bidet, located right beside the tent</li>
                <li>• Access to Wi-Fi</li>
                <li>• Private picnic table and fire pit/grill</li>
                <li>• Complimentary coffee/tea and drinking water in tent</li>
                <li>• Bathroom towels, dental & vanity kit, sleeping eye masks</li>
                <li>• Convenience outlet</li>
                <li>• Pet-friendly*</li>
            `;
        } else if (lowerName.includes('bell')) {
            return `
                <li>• Fan-cooled, 25 sqm tent</li>
                <li>• Spacious enough for up to 8 mattress sets (+Php 400 per set/night) or 12 sleeping bags</li>
                <li>• Common toilet and shower</li>
                <li>• Convenience outlets</li>
                <li>• Access to Wi-Fi, camp amenities, and activities</li>
                <li>• Pet-friendly*</li>
            `;
        } else if (lowerName.includes('dome')) {
            return `
                <li>• Fan-cooled, 25 sqm tent</li>
                <li>• Spacious enough for up to 8 mattress sets (+Php 400 per set/night) or 12 sleeping bags</li>
                <li>• Common toilet and shower</li>
                <li>• Convenience outlets</li>
                <li>• Access to Wi-Fi, camp amenities, and activities</li>
                <li>• Pet-friendly*</li>
            `;
        } else if (lowerName.includes('byot') || lowerName.includes('car')) {
            return `
                <li>• Spacious area for your own tent or car camp setup</li>
                <li>• Common toilet and shower</li>
                <li>• Access to Wi-Fi, camp amenities, and activities</li>
            `;
        } else {
            return `
                <li>• Shared bathroom facilities</li>
                <li>• Double bed</li>
                <li>• Electricity and heating</li>
                <li>• Scenic views</li>
                <li>• Comfortable accommodations</li>
                <li>• 28 m² • 4 single beds</li>
            `;
        }
    }

    function openCategoryModal(category, staycations) {
        const modalId = `${category}-modal`;
        const modal = document.getElementById(modalId);
        const modalContent = document.getElementById(`${modalId}-content`);
        const optionsContainer = document.getElementById(`${category}-options`);

        if (!modal || !modalContent || !optionsContainer) return;

        // Store previous focus element
        previousFocusElement = document.activeElement;

        // Clear previous options
        optionsContainer.innerHTML = '';

        // Populate options
        staycations.forEach((staycation, index) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'bg-white rounded-xl shadow-md overflow-hidden transition transform hover:-translate-y-1 hover:shadow-xl h-full flex flex-col border border-gray-100';

            // Determine input label and type based on staycation name
            let inputLabel = 'Number of Guests';
            let inputPlaceholder = '1';
            let isBYOT = staycation.name.toLowerCase().includes('byot');
            let isCarCamping = staycation.name.toLowerCase().includes('car camping');

            if (isBYOT) {
                inputLabel = 'How many BYOT?';
                inputPlaceholder = '0';
            } else if (isCarCamping) {
                inputLabel = 'Number of Vehicles';
                inputPlaceholder = '0';
            }

            optionDiv.innerHTML = `
                <div class="relative overflow-hidden" style="height: 220px;">
                    <div class="carousel-container flex transition-transform duration-300 ease-in-out" style="width: 300%; height: 100%;">
                        <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover flex-shrink-0" style="width: 33.333%;">
                        <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover flex-shrink-0" style="width: 33.333%;">
                        <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover flex-shrink-0" style="width: 33.333%;">
                    </div>
                    <button class="carousel-prev absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75 transition" data-direction="prev">
                        ‹
                    </button>
                    <button class="carousel-next absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75 transition" data-direction="next">
                        ›
                    </button>
                    <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
                        <button class="carousel-indicator w-2 h-2 rounded-full bg-white opacity-50 hover:opacity-100 transition" data-slide="0"></button>
                        <button class="carousel-indicator w-2 h-2 rounded-full bg-white opacity-50 hover:opacity-100 transition" data-slide="1"></button>
                        <button class="carousel-indicator w-2 h-2 rounded-full bg-white opacity-50 hover:opacity-100 transition" data-slide="2"></button>
                    </div>
                </div>

                <div class="flex flex-col p-4 h-full">
                    <div class="flex justify-between items-start mb-2 gap-2">
                        <h5 class="font-bold text-xl md:text-2xl mb-0" style="line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em;">
                            ${staycation.name}
                        </h5>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-base font-semibold ml-2" style="background: linear-gradient(135deg, ${category === 'glamping' ? '#FFD700 0%, #FFA500 100%' : '#32CD32 0%, #228B22 100%'}); color: ${category === 'glamping' ? '#333' : '#fff'};">
                            ${category.charAt(0).toUpperCase() + category.slice(1)}
                        </span>
                    </div>

                    <!-- CHECKBOX TO SELECT STAYCATION -->
                    <div class="mb-3 flex items-center gap-2">
                        <input type="checkbox"
                            id="select-${index}"
                            class="staycation-checkbox w-5 h-5"
                            data-index="${index}"
                            data-staycation='${JSON.stringify(staycation).replace(/'/g, "'")}' />
                        <label for="select-${index}" class="text-gray-700 text-base md:text-lg cursor-pointer">
                            Select this staycation
                        </label>
                    </div>

                    <p class="text-gray-500 text-base md:text-lg mb-3" style="flex-grow: 1;">
                        ${isBYOT || isCarCamping ? 'Area capacity: Suitable for multiple setups and vehicles' : getCapacityText(staycation)}
                    </p>

                    <div class="mb-3">
                        <h6 class="text-sm font-semibold text-gray-800 mb-2">Features</h6>
                        <ul class="text-sm text-gray-600 space-y-1">
                            ${getFeaturesList(staycation.name)}
                        </ul>
                    </div>

                    <div class="mt-auto">
                        <div class="mb-3">
                            <label for="guests-${index}" class="block text-lg font-medium text-gray-700 mb-1">${inputLabel}</label>
                            ${isBYOT || isCarCamping ?
                                `<input type="number" id="guests-${index}" name="guests" required class="guest-input w-full px-4 py-2.5 border border-gray-300 rounded-lg text-lg sm:text-xl focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent" min="1" max="50" placeholder="${inputPlaceholder}">` :
                                `<select id="guests-${index}" name="guests" required class="guest-input w-full px-4 py-2.5 border border-gray-300 rounded-lg text-lg sm:text-xl focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
                                    ${Array.from({length: 7}, (_, i) => `<option value="${i}" ${i === 0 ? 'selected' : ''}>${i}</option>`).join('')}
                                </select>`
                            }
                        </div>
                        ${staycation.name.toLowerCase().includes('glamping') || staycation.name.toLowerCase().includes('bell') || staycation.name.toLowerCase().includes('dome') ? `
                        <div class="mb-3 pt-3 border-t border-gray-200">
                            <div class="mb-2">
                                <label class="flex items-start">
                                    <input type="checkbox" class="pet-agreement mt-1 mr-2 w-4 h-4" data-staycation-id="${staycation.id}" onchange="togglePetSelect(this)">
                                    <span class="text-sm text-gray-700">
                                        I agree to the <a href="#" onclick="openPetPolicyModal(); return false;" class="text-emerald-600 hover:text-emerald-800 underline">pet policy</a>
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-lg font-medium text-gray-700 mb-1">Number of pets</label>
                                <select class="pet-num-input w-full px-4 py-2.5 border border-gray-300 rounded-lg text-lg sm:text-xl focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent" data-staycation-id="${staycation.id}">
                                    ${Array.from({length: 3}, (_, i) => `<option value="${i}" ${i === 0 ? 'selected' : ''}>${i}</option>`).join('')}
                                </select>
                            </div>
                        </div>
                        ` : ''}
                        <span class="block font-bold text-primary-600 text-xl md:text-2xl mb-2">
                            ${getPriceDisplay(staycation)}
                        </span>

                        <div class="flex gap-2">
                            <button class="flex-1 inline-flex justify-center items-center rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition px-3 py-2"
                                onclick="alert('VR Preview coming soon!')">
                                <i class="fas fa-vr-cardboard mr-2"></i>
                                VR Preview
                            </button>
                        </div>
                    </div>
                </div>
            `;

            optionsContainer.appendChild(optionDiv);

            // Add event listeners for pet options
            const petAgreementInput = optionDiv.querySelector('.pet-agreement');
            const numPetsInput = optionDiv.querySelector('.pet-num-input');
            if (petAgreementInput) {
                petAgreementInput.addEventListener('change', updatePetDataForCurrentSelection);
            }
            if (numPetsInput) {
                numPetsInput.addEventListener('change', updatePetDataForCurrentSelection);
                numPetsInput.addEventListener('click', function() {
                    const checkbox = this.closest('.mb-3').querySelector('.pet-agreement');
                    if (!checkbox.checked) {
                        showToast('Please agree to the pet policy before selecting the number of pets.', 'error');
                    }
                });
                numPetsInput.addEventListener('focus', function() {
                    const checkbox = this.closest('.mb-3').querySelector('.pet-agreement');
                    if (!checkbox.checked) {
                        showToast('Please agree to the pet policy before selecting the number of pets.', 'error');
                    }
                });
            }

            // Carousel functionality
            let currentSlide = 0;
            const carouselContainer = optionDiv.querySelector('.carousel-container');
            const indicators = optionDiv.querySelectorAll('.carousel-indicator');
            const prevBtn = optionDiv.querySelector('.carousel-prev');
            const nextBtn = optionDiv.querySelector('.carousel-next');

            function updateCarousel() {
                if (carouselContainer) {
                    carouselContainer.style.transform = `translateX(-${currentSlide * 33.333}%)`;
                }
                indicators.forEach((ind, index) => {
                    ind.classList.toggle('opacity-100', index === currentSlide);
                    ind.classList.toggle('opacity-50', index !== currentSlide);
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentSlide = currentSlide > 0 ? currentSlide - 1 : 2;
                    updateCarousel();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentSlide = currentSlide < 2 ? currentSlide + 1 : 0;
                    updateCarousel();
                });
            }
            indicators.forEach((ind, index) => {
                ind.addEventListener('click', () => {
                    currentSlide = index;
                    updateCarousel();
                });
            });

            // (Optional) extra validation for guest-input enablement can stay if you add a select button later
        });

        // Set up focus trapping
        setupFocusTrap(modalId);

        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        // Focus first focusable element
        setTimeout(() => {
            const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }
        }, 100);

        activeModal = modalId;
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalContent = document.getElementById(`${modalId}-content`);

        if (modal && modalContent) {
            // Animate out
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');

            // Hide after animation
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);

            // Restore focus
            if (previousFocusElement && typeof previousFocusElement.focus === 'function') {
                setTimeout(() => {
                    previousFocusElement.focus();
                }, 310);
            }

            // Clear active modal
            activeModal = null;
        }
    }

    function setupFocusTrap(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Get all focusable elements within the modal
        focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        // Focus trap event listener
        const handleKeyDown = (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            } else if (e.key === 'Escape') {
                // ESC key
                e.preventDefault();
                closeModal(modalId);
            }
        };

        // Add event listener
        modal.addEventListener('keydown', handleKeyDown);

        // Store the listener for cleanup
        modal._focusTrapHandler = handleKeyDown;
    }

    function selectStaycationFromModal(staycation, button) {
        // Close the modal
        closeModal('glamping-modal');
        closeModal('camping-modal');

        // Get active tab index
        const activeTab = document.querySelector('.accom-btn.bg-gray-900');
        if (!activeTab) return;

        const accommodationIndex = parseInt(activeTab.dataset.index);

        // Get the guest number
        const card = button.closest('.bg-white.rounded-xl');
        const input = card.querySelector('.guest-input');
        const guests = input ? parseInt(input.value) || 1 : 1;

        // Store the selected accommodation
        window.selectedAccommodations[accommodationIndex] = {
            staycation: staycation,
            guests: guests
        };

        // Display the selected accommodation
        displaySelectedAccommodation(accommodationIndex, window.selectedAccommodations[accommodationIndex]);

        // Initialize remove buttons for selected accommodations
        initializeRemoveSelectedButtons();

        // Update booking summary
        window.updateBookingSummary();

        showToast(`ACCOMMODATION ${accommodationIndex + 1}: ${staycation.name} selected`, 'success');
    }

    function initializeMultiSelectionModal(category) {
        const modalId = `${category}-modal`;
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const saveBtn = modal.querySelector('.save-multi-selection');
        if (!saveBtn) return;

        saveBtn.addEventListener('click', () => {
            const checkboxes = modal.querySelectorAll('.staycation-checkbox:checked');
            if (checkboxes.length === 0) {
                showToast('Please select at least one option', 'error');
                return;
            }

            const activeTab = document.querySelector('.accom-btn.bg-gray-900');
            if (!activeTab) {
                showToast('No active accommodation tab', 'error');
                return;
            }

            const accommodationIndex = parseInt(activeTab.dataset.index);

            // Store selected accommodations
            window.selectedAccommodations[accommodationIndex] = [];

            checkboxes.forEach(cb => {
                const staycation = JSON.parse(cb.dataset.staycation || '{}');
                const guests = parseInt(cb.dataset.guests || 1);

                if (staycation.id) {
                    window.selectedAccommodations[accommodationIndex].push({
                        staycation: staycation,
                        guests: guests
                    });
                }
            });

            // Display all selected accommodations for this tab
            window.selectedAccommodations[accommodationIndex].forEach((acc, i) => {
                displaySelectedAccommodation(`${accommodationIndex}-${i}`, acc);
            });

            initializeRemoveSelectedButtons();
            window.updateBookingSummary();

            // Close modal
            closeModal(modalId);

            showToast(`${checkboxes.length} option(s) selected`, 'success');
        });
    }

    function openPetPolicyModal() {
        const modal = document.getElementById('pet-policy-modal');
        const modalContent = document.getElementById('pet-policy-modal-content');

        if (!modal || !modalContent) return;

        // Store previous focus element
        previousFocusElement = document.activeElement;

        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        // Focus first focusable element
        setTimeout(() => {
            const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }
        }, 100);

        activeModal = 'pet-policy-modal';
        setupFocusTrap('pet-policy-modal');
    }

    function togglePetSelect(checkbox) {
        const container = checkbox.closest('.mb-3');
        const select = container.querySelector('.pet-num-input');
        if (select) {
            select.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                select.value = '0'; // Reset to 0 when unchecked
            }
        }
    }

    function updatePetDataForCurrentSelection() {
        const activeTab = document.querySelector('.accom-btn.bg-gray-900');
        if (!activeTab) return;
        const accommodationIndex = parseInt(activeTab.dataset.index);
        const acc = window.selectedAccommodations[accommodationIndex];
        if (!acc) return;

        // Find the card in the currently open modal
        const modal = document.querySelector('#glamping-modal:not(.hidden), #camping-modal:not(.hidden)');
        if (!modal) return;
        const card = modal.querySelector('.bg-white.rounded-xl');
        if (!card) return;

        const petAgreementInput = card.querySelector('.pet-agreement');
        const numPetsInput = card.querySelector('.pet-num-input');
        if (petAgreementInput && numPetsInput) {
            acc.pet_agreement = petAgreementInput.checked;
            acc.num_pets = parseInt(numPetsInput.value) || 0;
            window.updateBookingSummary();
        }
    }

    // 👉 GLOBAL CHANGE HANDLER: checkbox + quantity + pets
    document.addEventListener('change', function(e) {
        const target = e.target;

        // Only handle relevant elements
        if (
            !target.matches('.staycation-checkbox') &&
            !target.matches('.guest-input') &&
            !target.matches('.pet-agreement') &&
            !target.matches('.pet-num-input')
        ) {
            return;
        }

        const card = target.closest('.bg-white.rounded-xl');
        if (!card) return;

        const checkbox = card.querySelector('.staycation-checkbox');
        if (!checkbox) return;

        const staycationData = checkbox.dataset.staycation;
        if (!staycationData) return;

        let staycation;
        try {
            staycation = JSON.parse(staycationData);
        } catch (err) {
            console.error('Invalid staycation data', err);
            return;
        }

        const guestsInput = card.querySelector('.guest-input');
        // Ignore old simple checkboxes (those cards without a guest dropdown)
        if (!guestsInput) return;

        const guests = parseInt(guestsInput.value) || 0;

        const petAgreementInput = card.querySelector('.pet-agreement');
        const numPetsInput = card.querySelector('.pet-num-input');
        const petAgreement = petAgreementInput ? petAgreementInput.checked : false;
        const numPets = numPetsInput ? parseInt(numPetsInput.value) || 0 : 0;

        if (target.matches('.pet-num-input') && !petAgreement) {
            numPetsInput.value = '0';
            showToast('Please agree to the pet policy before selecting the number of pets.', 'error');
            return;
        }

        const activeTab = document.querySelector('.accom-btn.bg-gray-900');
        if (!activeTab) return;

        const accommodationIndex = parseInt(activeTab.dataset.index);

        if (checkbox.checked) {
            // Save selection with latest values
            window.selectedAccommodations[accommodationIndex] = {
                staycation,
                guests,
                pet_agreement: petAgreement,
                num_pets: numPets
            };
            displaySelectedAccommodation(accommodationIndex, window.selectedAccommodations[accommodationIndex]);
        } else {
            // Unchecked: remove selection
            delete window.selectedAccommodations[accommodationIndex];
            displaySelectedAccommodation(accommodationIndex, null);
        }

        // Recompute summary and total every change
        window.updateBookingSummary();
    });

    function proceedToActivities() {
        // Send POST request to save accommodations and proceed
        fetch('{{ route("booking.staycation") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                accommodations: window.selectedAccommodations,
                proceed_to_activities: true,
                pet_agreement: document.getElementById('pet-agreement') ? document.getElementById('pet-agreement').checked : false,
                num_pets: document.getElementById('num-pets') ? (parseInt(document.getElementById('num-pets').value) || 0) : 0
            })
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.success) {
                window.location.href = '{{ route("booking.staycationActivities") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving accommodations. Please try again.');
        });
    }
</script>


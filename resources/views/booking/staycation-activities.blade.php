<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Select Staycation Activities</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

@php
$backUrl = route('booking.staycation');

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
$totalSteps   = $bookingType === 'staycation' ? '5' : '5';
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
    $nights = max(1, $diff->days);
}

// Calculate total guests
$totalGuests = collect($booking['accommodations'])->sum('guests');

// Check if accommodations are selected
$accommodationsSelected = !empty($booking['accommodations']) && count(array_filter($booking['accommodations'])) > 0;

// For staycation, adjust step display
if ($bookingType === 'staycation') {
    if ($accommodationsSelected) {
        $stepNumber = '1'; // Show as step 1 (completed booking)
    }
}

// Calculate accommodation total from stored room prices
$accommodationTotal = 0;
$breakfastTotal = 0;
foreach ($booking['accommodations'] ?? [] as $roomId => $acc) {
    if (!empty($acc)) {
        $accommodationTotal += ($acc['accommodation_price'] ?? 0) + ($acc['mattress_price'] ?? 0) + ($acc['pet_price'] ?? 0);
    }
}

// Get selected foods from booking data
$selectedFoods = $booking['selected_foods'] ?? [];

// Get selected activities from booking data
$selectedActivities = $booking['selected_activities'] ?? [];

// Key staycations by ID for the component
$staycations = $staycations->keyBy('id');
@endphp

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

<div class="pt-6 md:pt-12">
    {{-- Header --}}
    <div class="text-center mb-10">
        <h1 class="text-2xl font-bold tracking-tight" style="">Select Staycation Activities</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500" style="">Enhance your staycation experience with optional activities</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mx-0 md:mx-10 py-6">
        {{-- Main content --}}
        <div class="lg:col-span-2">
            <div class="flex flex-col">
                {{-- Progress indicator --}}
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <small class="text-gray-500 text-sm">
                            Step 4 of 5: Select Activities
                        </small>
                        <small class="text-gray-500 text-sm">80% Complete</small>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-[#8f5d54] h-2 rounded-full transition-all duration-300" style="width: 80%"></div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="flex items-end justify-between my-3">
                    <a href="{{ $backUrl }}"
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

                {{-- Activities Selection --}}
                <x-activities-page-no-sidebar
                    pretitle="Staycation Activities"
                    title="Select Staycation Activities"
                    subtitle="Enhance your staycation experience with optional activities"
                    :back-route="route('booking.staycation')"
                    :payment-route="route('booking.staycationPayment')"
                    :store-route="route('booking.storeStaycationActivities')"
                    activities-endpoint="/api/activities"
                    :total-guests="$totalGuests"
                    :auto-guests="true"
                    :is-staycation="true"
                    :accommodation-total="$accommodationTotal + $breakfastTotal"
                />
            </div>
        </div>

        {{-- Sidebar: booking summary --}}
        <div class="lg:col-span-1">
            <x-booking-summary
                :booking="$booking"
                :staycations="$staycations"
                :selectedActivities="$selectedActivities"
                :selectedFoods="$selectedFoods"
                :foods="$foods"
                :showActivities="true"
                :showDynamicActivities="true"
                :showActionButtons="true"
                :actionButtonType="'activities'"
            />
        </div>
    </div>
</div>

  {{-- Footer --}}
  <x-footer />

  {{-- Alpine.js --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
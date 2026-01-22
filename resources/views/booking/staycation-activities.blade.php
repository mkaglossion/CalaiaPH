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
        $accommodationTotal += $acc['total_price'] ?? 0;
    }
}
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
        <h1 class="text-2xl font-bold tracking-tight" style="font-family: 'Gotham Book', sans-serif;">Select Staycation Activities</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">Enhance your staycation experience with optional activities</p>
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
            <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-6 space-y-6">
                {{-- Selected Dates Section --}}
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="bi bi-calendar-check mr-2 text-[#8f5d54]"></i> Selected Dates
                    </h4>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                        @if($datesSelected)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Check-in:</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($checkinDate)->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Check-out:</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($checkoutDate)->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2">
                                <span class="text-gray-600">Total Nights:</span>
                                <span class="font-semibold text-[#8f5d54]">{{ $nights }}</span>
                            </div>
                        @else
                            <p class="text-gray-500 text-xs">No dates selected</p>
                        @endif
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Selected Rooms Section --}}
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="bi bi-house mr-2 text-[#8f5d54]"></i> Selected Rooms
                    </h4>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
                        @if(!empty($booking['accommodations']))
                            @foreach($booking['accommodations'] as $roomId => $room)
                                @if(!empty($room))
                                    @php
                                        $staycation = $staycations->get((int)$roomId);
                                        $roomNameLower = strtolower($staycation->name ?? '');
                                        $isBYOT = str_contains($roomNameLower, 'byot') || str_contains($roomNameLower, 'bring your own tent');
                                        $isCarCamping = str_contains($roomNameLower, 'car camping');
                                        $isSpecialRoom = $isBYOT || $isCarCamping;
                                    @endphp
                                    <div class="bg-white rounded border border-gray-200 p-3">
                                        <div class="font-semibold text-gray-900 mb-2">{{ $staycation->name ?? 'Room #' . $roomId }}</div>
                                        <div class="text-gray-600 space-y-1 mb-2">
                                            @if($isSpecialRoom)
                                                <div>Quantity: <span class="font-semibold">{{ $room['extra_mattress'] ?? 1 }}</span></div>
                                                <div>Adults: <span class="font-semibold">{{ $room['adults'] ?? 1 }}</span>, Children: <span class="font-semibold">{{ $room['children'] ?? 0 }}</span></div>
                                            @else
                                                <div>Adults: <span class="font-semibold">{{ $room['adults'] ?? 1 }}</span>, Children: <span class="font-semibold">{{ $room['children'] ?? 0 }}</span></div>
                                                <div>Mattress: <span class="font-semibold">{{ $room['extra_mattress'] ?? 0 }}</span></div>
                                                <div>Pets: <span class="font-semibold">{{ $room['pets'] ?? 'no' }}</span>
                                                    @if(($room['pets'] ?? 'no') === 'yes')
                                                        <span>(#{{ $room['num_pets'] ?? 0 }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 space-y-1">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Room:</span>
                                                <span class="font-semibold">₱{{ number_format($room['base_price'] ?? 0, 0) }}</span>
                                            </div>
                                            @if(!$isSpecialRoom && ($room['mattress_price'] ?? 0) > 0)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Mattress:</span>
                                                    <span class="font-semibold">₱{{ number_format($room['mattress_price'] ?? 0, 0) }}</span>
                                                </div>
                                            @endif
                                            @if(!$isSpecialRoom && ($room['pet_price'] ?? 0) > 0)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Pets:</span>
                                                    <span class="font-semibold">₱{{ number_format($room['pet_price'] ?? 0, 0) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between border-t border-gray-200 pt-1">
                                                <span class="font-semibold text-gray-800">Total:</span>
                                                <span class="font-bold text-[#8f5d54]">₱{{ number_format($room['total_price'] ?? 0, 0) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <p class="text-gray-500 text-xs">No rooms selected</p>
                        @endif
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Selected Food Section --}}
                @if(!empty($selectedFoods) && count($selectedFoods) > 0)
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="bi bi-cup-straw mr-2 text-[#8f5d54]"></i> Selected Food & Beverages
                    </h4>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
                        @php $foodTotal = 0; @endphp
                        @foreach($selectedFoods as $selectedFood)
                            @php
                                $food = $foods->get($selectedFood['id']);
                                if ($food) {
                                    $quantity = $selectedFood['quantity'] ?? 1;
                                    $itemTotal = $food->price * $quantity;
                                    $foodTotal += $itemTotal;
                                }
                            @endphp
                            @if($food)
                            <div class="bg-white rounded border border-gray-200 p-2">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-semibold text-gray-900">{{ $food->name }}</span>
                                    <span class="font-bold text-[#8f5d54]">₱{{ number_format($itemTotal, 0) }}</span>
                                </div>
                                <div class="text-gray-600">
                                    <div>Qty: <span class="font-semibold">{{ $quantity }}</span> × ₱{{ number_format($food->price, 0) }}</div>
                                    @if(!empty($selectedFood['notes']))
                                        <div class="text-xs text-gray-500 italic mt-1">{{ $selectedFood['notes'] }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                        <div class="border-t border-gray-200 pt-2 flex justify-between">
                            <span class="font-semibold text-gray-800">Food Subtotal:</span>
                            <span class="font-bold text-[#8f5d54]">₱{{ number_format($foodTotal, 0) }}</span>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">
                @endif

                {{-- Selected Activities Section --}}
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="bi bi-ticket-detailed mr-2 text-[#8f5d54]"></i> Selected Activities
                    </h4>
                    <div id="selected-activities" class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
                        <p class="text-gray-500">No activities selected</p>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Pricing Summary --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Accommodation:</span>
                        <span class="font-semibold">₱{{ number_format($accommodationTotal + $breakfastTotal, 0) }}</span>
                    </div>
                    @if(!empty($selectedFoods) && count($selectedFoods) > 0)
                    <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2">
                        <span class="text-gray-600">Food & Beverages:</span>
                        <span class="font-semibold">₱{{ number_format($foodTotal ?? 0, 0) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2">
                        <span class="text-gray-600">Activities:</span>
                        <span class="font-semibold" id="activities-total">₱0</span>
                    </div>
                    <div class="flex justify-between items-center text-lg font-bold border-t border-gray-200 pt-2">
                        <span>Grand Total:</span>
                        <span class="text-[#8f5d54]" id="grand-total" data-food-total="{{ $foodTotal ?? 0 }}">₱{{ number_format($accommodationTotal + $breakfastTotal + ($foodTotal ?? 0), 0) }}</span>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Action Buttons --}}
                <div class="flex flex-col gap-3">
                    <button
                        type="button"
                        id="skip-btn"
                        class="w-full py-3 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition"
                    >
                        <i class="bi bi-credit-card mr-2"></i>
                        Proceed to Payment Without Activities
                    </button>

                    <button
                        type="button"
                        id="confirm-btn"
                        class="w-full py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled
                    >
                        <i class="bi bi-check-circle mr-2"></i>
                        Confirm and Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers --}}
  <style>
    .font-gotham {
      font-family: 'Gotham Book', sans-serif;
    }
  </style>

  {{-- Alpine.js --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
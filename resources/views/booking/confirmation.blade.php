<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Booking Confirmed</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

@php
// For staycation confirmation
$totalGuests = 0;
$nights = 0;
$accommodationTotal = 0;
if (isset($isStaycation) && $isStaycation && !empty($bookingData['accommodations'])) {
    $totalGuests = collect($bookingData['accommodations'])->sum('guests');

    $nights = 0;

  if (!empty($bookingData['checkin']) && !empty($bookingData['checkout'])) {
      $checkin = \Carbon\Carbon::parse($bookingData['checkin']);
      $checkout = \Carbon\Carbon::parse($bookingData['checkout']);

      // Make sure checkout >= checkin
      if ($checkout->lt($checkin)) {
          $temp = $checkin;
          $checkin = $checkout;
          $checkout = $temp;
      }

      // diffInDays always positive
      $nights = $checkout->diffInDays($checkin);

      // Edge case: if someone books same day, count as 1 night
      if ($nights === 0) {
          $nights = 1;
      }
  }

  // Recalculate accommodation total including pet fees
  foreach ($bookingData['accommodations'] as $accommodation) {
      if (!empty($accommodation['staycation'])) {
          $quantity = $accommodation['guests'] ?? 1;
          $name = strtolower($accommodation['staycation']['name']);
          $accPrice = 0;

          if ($checkin && $checkout && (str_contains($name, 'standard') || str_contains($name, 'premium'))) {
              $current = clone $checkin;
              while ($current < $checkout) {
                  $day = (int)$current->format('w');
                  $isWeekend = $day === 5 || $day === 6;
                  $basePrice = 0;
                  if (str_contains($name, 'standard')) {
                      $basePrice = $isWeekend ? 6000 : 4800;
                  } elseif (str_contains($name, 'premium')) {
                      $basePrice = $isWeekend ? 7200 : 5760;
                  }
                  $extraGuests = max(0, $quantity - 4);
                  $accPrice += $basePrice + ($extraGuests * 400);
                  $current->modify('+1 day');
              }
          } elseif ($checkin && $checkout && (str_contains($name, 'bell') || str_contains($name, 'dome'))) {
              $accPrice = 3000 * $nights;
          } elseif ($checkin && $checkout && (str_contains($name, 'byot') || str_contains($name, 'car camping'))) {
              $accPrice = 300 * $quantity * $nights;
          } else {
              $accPrice = $accommodation['staycation']['price'] * $quantity * $nights;
          }

          // Add pet fee
          if (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) {
              $accPrice += 2000 * $accommodation['num_pets'];
          }

          // Add breakfast fee
          if (!empty($accommodation['has_breakfast'])) {
              $accPrice += 500 * $accommodation['guests'];
          }

          $accommodationTotal += $accPrice;
      }
  }
}
@endphp

@php
// For dayvisit confirmation
if (isset($isDayvisit) && $isDayvisit) {
    $selectedActivities = $selectedActivities ?? [];
    $activitiesTotal = collect($selectedActivities)->sum(function($activity) {
        return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
    });
    $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
}

// For regular bookings (daytour, etc.)
if (!isset($isStaycation) && !isset($isDayvisit)) {
    $selectedActivities = $selectedActivities ?? [];
    $activitiesTotal = collect($selectedActivities)->sum(function($activity) {
        return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
    });
    $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
}
@endphp

<body class="bg-gray-50 font-sans antialiased min-h-screen">
  {{-- Header --}}
  <x-header active="confirmation"/>

  {{-- Progress Indicator --}}
  <div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
        {{-- Step 1: Details --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Details</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Activities --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Payment --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Payment</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 4: Confirmation (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Confirmation</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Confirmation --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8 text-center">
        {{-- Success Icon --}}
        <div class="mb-6">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-check-circle-fill text-4xl text-green-600"></i>
          </div>
          <h1 class="text-2xl font-bold text-[#8f5d54] mb-2" style="">
            Booking Confirmed!
          </h1>
          <p class="text-xs text-gray-600" style="">
            Your booking has been successfully processed and confirmed.
          </p>
        </div>

        {{-- Booking Details --}}
        <div class="bg-gray-50 rounded-xl p-6 mb-6 text-left">
          <h2 class="text-2xl font-semibold text-gray-900 mb-4" style="">Booking Details</h2>

          @if(isset($isDayvisit) && $isDayvisit)
            {{-- Day Visit Booking Details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-500">Booking Type:</span>
                <span class="ml-2 text-gray-900">Day Visit</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Package:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['package'] ?? 'Day Visit Package' }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Date:</span>
                <span class="ml-2 text-gray-900">
                  @if (!empty($bookingData['booking_date']))
                    {{ \Carbon\Carbon::parse($bookingData['booking_date'])->format('M d, Y') }}
                  @endif
                </span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Guests:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['guests'] ?? '' }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Name:</span>
                <span class="ml-2 text-gray-900">{{ ($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '') }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Email:</span>
                <span class="ml-2 text-gray-900">{{ $customerInfo['email'] ?? '' }}</span>
              </div>
            </div>

            @if (!empty($selectedActivities))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2" style="">Selected Activities:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivities as $activity)
                    <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="space-y-2">
                <div class="flex justify-between items-center text-sm">
                  <span>Day Visit Package:</span>
                  <span>₱{{ number_format($bookingData['price'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span>Activities Total:</span>
                  <span>₱{{ number_format(collect($selectedActivities ?? [])->sum(function($activity) { return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1); }), 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                  <span>Total Amount Paid:</span>
                  <span class="text-[#8f5d54]">₱{{ number_format($grandTotal ?? 0, 2) }}</span>
                </div>
              </div>
            </div>
          @elseif(isset($isStaycation) && $isStaycation)
            {{-- Staycation Booking Details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-500">Booking Type:</span>
                <span class="ml-2 text-gray-900">Staycation</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Check-in:</span>
                <span class="ml-2 text-gray-900">
                  @if (!empty($bookingData['checkin']))
                    {{ \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') }}
                  @endif
                </span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Check-out:</span>
                <span class="ml-2 text-gray-900">
                  @if (!empty($bookingData['checkout']))
                    {{ \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') }}
                  @endif
                </span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Nights:</span>
                  <span class="ml-2 text-gray-900">{{ $nights ?? 0 }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Guests:</span>
                <span class="ml-2 text-gray-900">{{ $totalGuests }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Name:</span>
                <span class="ml-2 text-gray-900">{{ ($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '') }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Email:</span>
                <span class="ml-2 text-gray-900">{{ $customerInfo['email'] ?? '' }}</span>
              </div>
            </div>

            @if (!empty($bookingData['accommodations']))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2" style="">Selected Accommodations:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @php
                  $checkin = !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin']) : null;
                  $checkout = !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout']) : null;
                  $nights = $checkin && $checkout ? $checkout->diffInDays($checkin) : 0;
                  @endphp
                  @foreach ($bookingData['accommodations'] as $accommodation)
                    @if (!empty($accommodation['staycation']))
                      @php
                      $quantity = $accommodation['guests'] ?? 1;
                      $name = strtolower($accommodation['staycation']['name']);
                      $isBYOT = str_contains($name, 'byot');
                      $isCarCamping = str_contains($name, 'car camping');
                      $accommodationPrice = 0;
                      $basePriceTotal = 0;
                      $extraPriceTotal = 0;
                      $extraGuests = 0;

                      if ($checkin && $checkout && (str_contains($name, 'standard') || str_contains($name, 'premium'))) {
                          $current = clone $checkin;
                          while ($current < $checkout) {
                              $day = (int)$current->format('w');
                              $isWeekend = $day === 5 || $day === 6;
                              $basePrice = 0;
                              if (str_contains($name, 'standard')) {
                                  $basePrice = $isWeekend ? 6000 : 4800;
                              } elseif (str_contains($name, 'premium')) {
                                  $basePrice = $isWeekend ? 7200 : 5760;
                              }
                              $extraGuests = max(0, $quantity - 4);
                              $accommodationPrice += $basePrice + ($extraGuests * 400);
                              $basePriceTotal += $basePrice;
                              $extraPriceTotal += $extraGuests * 400;
                              $current->modify('+1 day');
                          }
                      } elseif ($checkin && $checkout && (str_contains($name, 'bell') || str_contains($name, 'dome'))) {
                          $accommodationPrice = 3000 * $nights;
                      } elseif ($checkin && $checkout && ($isBYOT || $isCarCamping)) {
                          $accommodationPrice = (300 * $nights) + (500 * $quantity * $nights);
                      } else {
                          $accommodationPrice = $accommodation['staycation']['price'] * $quantity * $nights;
                      }

                      $petFee = (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) ? 2000 * $accommodation['num_pets'] : 0;
                      $breakfastFee = (!empty($accommodation['has_breakfast'])) ? 500 * $accommodation['guests'] : 0;
                      $totalPrice = $accommodationPrice + $petFee + $breakfastFee;

                      // Calculate base rate per night (weekday rate for standard/premium)
                      $baseRate = 0;
                      if (str_contains($name, 'standard')) {
                          $baseRate = 4800; // weekday rate
                      } elseif (str_contains($name, 'premium')) {
                          $baseRate = 5760; // weekday rate
                      } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                          $baseRate = 3000;
                      } elseif ($isBYOT || $isCarCamping) {
                          $baseRate = 300 + (500 * $quantity);
                      }

                      $extraRate = $extraGuests * 400; // ₱400 per extra guest per night
                      @endphp
                      <li>• {{ $accommodation['staycation']['name'] }}
                        @if($isBYOT || $isCarCamping)
                          ({{ $accommodation['guests'] }} {{ $isBYOT ? 'BYOT' : 'Vehicles' }})
                        @endif
                        - ₱{{ number_format($baseRate, 2) }}/night
                        @if($extraGuests > 0)
                          <br>&nbsp;&nbsp;Extra Mattress ({{ $extraGuests }} guest{{ $extraGuests > 1 ? 's' : '' }}): ₱{{ number_format($extraRate, 2) }}/night
                        @endif
                        @if($petFee > 0)
                          <br>&nbsp;&nbsp;Pet Fee ({{ $accommodation['num_pets'] }} pet{{ $accommodation['num_pets'] > 1 ? 's' : '' }}): ₱{{ number_format($petFee, 2) }}
                        @endif
                        @if($breakfastFee > 0)
                          <br>&nbsp;&nbsp;Breakfast ({{ $accommodation['guests'] }} guest{{ $accommodation['guests'] > 1 ? 's' : '' }}): ₱{{ number_format($breakfastFee, 2) }}
                        @endif
                      </li>
                    @endif
                  @endforeach
                </ul>
              </div>
            @endif

            @if (!empty($selectedActivities))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2" style="">Selected Activities:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivities as $activity)
                    <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="space-y-2">
                <div class="flex justify-between items-center text-sm">
                  <span>Accommodation Total:</span>
                  <span>₱{{ number_format($bookingData['final_totals']['accommodation_total'] ?? $accommodationTotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span>Activities Total:</span>
                  <span>₱{{ number_format($bookingData['final_totals']['activities_total'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                  <span>Total Amount Paid:</span>
                  <span class="text-[#8f5d54]">₱{{ number_format(($bookingData['final_totals']['accommodation_total'] ?? $accommodationTotal) + ($bookingData['final_totals']['activities_total'] ?? 0), 2) }}</span>
                </div>
              </div>
            </div>
          @else
            {{-- Regular Booking Details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-500">Booking Type:</span>
                <span class="ml-2 text-gray-900">{{ ucfirst($bookingData['type'] ?? '') }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Package:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['package'] ?? '' }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Date:</span>
                <span class="ml-2 text-gray-900">
                  @if (!empty($bookingData['booking_date']))
                    {{ \Carbon\Carbon::parse($bookingData['booking_date'])->format('M d, Y') }}
                  @endif
                </span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Guests:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['guests'] ?? '' }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Name:</span>
                <span class="ml-2 text-gray-900">{{ ($bookingData['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? '') }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-500">Email:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['email'] ?? '' }}</span>
              </div>
            </div>

            @if (!empty($selectedActivities))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2" style="">Selected Activities:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivities as $activity)
                    <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] ?? 1 }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="space-y-2">
                <div class="flex justify-between items-center text-sm">
                  <span>Accommodation Total:</span>
                  <span>₱{{ number_format($bookingData['price'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span>Activities Total:</span>
                  <span>₱{{ number_format(collect($selectedActivities ?? [])->sum(function($activity) { return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1); }), 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                  <span>Total Amount Paid:</span>
                  <span class="text-[#8f5d54]">₱{{ number_format($grandTotal, 2) }}</span>
                </div>
              </div>
            </div>
          @endif
        </div>

        {{-- Next Steps --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
          <h3 class="text-2xl font-semibold text-blue-900 mb-3" style="">What's Next?</h3>
          <ul class="text-xs text-blue-800 space-y-2 text-left" style="">
            <li class="flex items-start">
              <i class="bi bi-envelope mr-2 mt-0.5"></i>
              <span>Check your email for a detailed confirmation and booking instructions.</span>
            </li>
            <li class="flex items-start">
              <i class="bi bi-calendar-event mr-2 mt-0.5"></i>
              <span>Save the date in your calendar. We'll send reminders as your booking date approaches.</span>
            </li>
            <li class="flex items-start">
              <i class="bi bi-telephone mr-2 mt-0.5"></i>
              <span>Contact us if you need to make changes or have any questions.</span>
            </li>
          </ul>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('home') }}"
             class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-6 py-3 text-sm font-semibold text-white hover:bg-[#a0675a] transition-colors">
            <i class="bi bi-house"></i>
            Return to Home
          </a>
          <button onclick="window.print()"
                  class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="bi bi-printer"></i>
            Print Confirmation
          </button>
        </div>

        {{-- Contact Info --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
          <p class="text-xs text-gray-500 mb-2" style="">Need assistance?</p>
          <p class="text-xs text-gray-600" style="">
            Contact us at <a href="mailto:calaia.ph@gmail.com" class="text-[#8f5d54] hover:underline">calaia.ph@gmail.com</a>
            or call <span class="font-medium">+63 905 129 4741</span>
          </p>
        </div>
      </div>
    </div>
  </main>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers --}}
  <style>
    .font-vila-zilka {
      font-family: 'Vila Zilka Serif', ui-serif, Georgia, 'Times New Roman', serif;
    }

    @media print {
      @page {
        size: legal;
        margin: 0.5in;
      }

      /* Hide header and footer */
      x-header, x-footer, header, footer {
        display: none !important;
      }

      /* Hide progress indicator */
      .bg-white.shadow-sm.border-b.border-gray-100 {
        display: none !important;
      }

      body {
        background: white !important;
      }

      main {
        page-break-inside: avoid;
        max-height: 13in; /* Legal height minus margins */
        overflow: hidden;
      }

      .bg-gray-50 {
        background: white !important;
      }

      /* Hide actions section */
      .flex.flex-col.sm\\:flex-row.gap-4.justify-center {
        display: none !important;
      }
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
    // Clear any saved selections after booking confirmation
    sessionStorage.removeItem('staycation_selections');
  </script>
</body>
</html>
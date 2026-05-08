<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Booking Confirmed</title>

  {{-- Stylesheets --}}
  @vite(['resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

@php
// For staycation confirmation
$totalGuests = 0;
$nights = 0;
$accommodationTotal = 0;
$foodTotal = 0;
$activitiesTotal = 0;

if (isset($isStaycation) && $isStaycation && !empty($bookingData['accommodations'])) {
    // Use total_guests from PaymentService if available, otherwise calculate
    $totalGuests = $bookingData['total_guests'] ?? collect($bookingData['accommodations'])->sum(function($acc) { return ($acc['adults'] ?? 0) + ($acc['children'] ?? 0); });

    // Use nights from PaymentService if available, otherwise calculate
    if (isset($bookingData['nights']) && $bookingData['nights'] > 0) {
        $nights = $bookingData['nights'];
    } elseif (!empty($bookingData['checkin']) && !empty($bookingData['checkout'])) {
        $checkin = \Carbon\Carbon::parse($bookingData['checkin']);
        $checkout = \Carbon\Carbon::parse($bookingData['checkout']);

        // Make sure checkout >= checkin
        if ($checkout->lt($checkin)) {
            $temp = $checkin;
            $checkin = $checkout;
            $checkout = $temp;
        }

        // diffInDays always positive, use max to ensure at least 1 night
        $nights = max(1, $checkout->diffInDays($checkin));
    } else {
        $nights = 1; // default to 1 night if no dates
    }

  // Always recalculate accommodation total from stored total_price (more reliable than final_totals)
  foreach ($bookingData['accommodations'] as $roomId => $accommodation) {
      if (!empty($accommodation)) {
          $accommodationTotal += $accommodation['total_price'] ?? 0;
      }
  }
  
  // Calculate activities total
  $activitiesTotal = collect($selectedActivities ?? [])->sum(function($activity) {
      return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
  });
  
  // Always calculate food total from selectedFoods array data (most accurate)
  if (!empty($selectedFoods)) {
      foreach ($selectedFoods as $foodData) {
          $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1); // No nights multiplication
      }
  }
  
  // Calculate grand total for staycation
  $grandTotal = $accommodationTotal + $activitiesTotal + $foodTotal;
}

// Calculate VAT breakdown using VatService (12% Philippine VAT)
$vatService = app(\App\Services\VatService::class);
$vatBreakdown = $vatService->generateBreakdown((float) ($grandTotal ?? 0), true); // true = VAT inclusive
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
          <h1 class="text-2xl font-bold text-[#8f5d54] mb-2">
            Booking Confirmed!
          </h1>
          <p class="text-xs text-gray-600">
            Your booking has been successfully processed and confirmed.
          </p>
        </div>

        {{-- Booking Details --}}
        <div class="bg-gray-50 rounded-xl p-6 mb-6 text-left">
          <h2 class="text-2xl font-semibold text-gray-900 mb-4">Booking Details</h2>

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
              @php
                $hasSpecialRoom = collect($bookingData['accommodations'] ?? [])->some(function($acc) {
                  $name = $acc['accommodation_name'] ?? '';
                  return stripos($name, 'byot') !== false || stripos($name, 'car camping') !== false || stripos($name, 'bring your own') !== false;
                });
              @endphp
              @if (!$hasSpecialRoom)
              <div>
                <span class="font-medium text-gray-500">Guests:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['total_guests'] ?? $totalGuests }}</span>
              </div>
              @endif
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
                <h3 class="text-2xl font-medium text-gray-900 mb-2">Selected Activities:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivities as $activity)
                    @php
                      $isByotOrCamping = stripos($activity['name'] ?? '', 'byot') !== false || 
                                        stripos($activity['name'] ?? '', 'car camping') !== false || 
                                        stripos($activity['name'] ?? '', 'bring your own') !== false;
                    @endphp
                    <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }}
                      @if(!$isByotOrCamping)
                        x {{ $activity['guests'] ?? 1 }}
                      @endif
                    </li>
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
                {{-- Subtotal (VAT-inclusive) --}}
                <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2">
                  <span>Subtotal:</span>
                  <span class="font-medium">₱{{ number_format($vatBreakdown['gross_amount'] ?? $grandTotal ?? 0, 2) }}</span>
                </div>
                
                {{-- VAT Breakdown --}}
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>
                    <i class="bi bi-info-circle text-gray-400 text-xs"></i>
<x-vat-label />
                  </span>
                  <span>₱{{ number_format($vatBreakdown['vat_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                  <span>Total Amount Paid:</span>
                  <span class="text-[#8f5d54]">₱{{ number_format($vatBreakdown['gross_amount'] ?? $grandTotal ?? 0, 2) }}</span>
                </div>
                <!--<p class="text-xs text-gray-500 italic text-center mt-2">
                  * All prices are VAT-inclusive as required by Philippine tax regulations.
                </p>-->
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
                  <span class="ml-2 text-gray-900">{{ max(1, abs($nights ?? 0)) }}</span>
              </div>
              @php
                $hasSpecialRoom = false;
                foreach ($bookingData['accommodations'] ?? [] as $roomId => $acc) {
                  if (!empty($acc)) {
                    $staycation = $staycations->get($acc['accommodation_id'] ?? $roomId);
                    $name = strtolower($staycation->name ?? '');
                    if (str_contains($name, 'byot') || str_contains($name, 'car camping') || str_contains($name, 'bring your own')) {
                      $hasSpecialRoom = true;
                      break;
                    }
                  }
                }
              @endphp
              @if (!$hasSpecialRoom)
              <div>
                <span class="font-medium text-gray-500">Guests:</span>
                <span class="ml-2 text-gray-900">{{ $totalGuests }}</span>
              </div>
              @endif
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
                <h3 class="text-2xl font-medium text-gray-900 mb-2">Selected Accommodations:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @php
                  $checkin = !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin']) : null;
                  $checkout = !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout']) : null;
                  $nights = $checkin && $checkout ? max(1, $checkout->diffInDays($checkin)) : 0;
                  @endphp
                  @foreach ($bookingData['accommodations'] as $roomId => $accommodation)
                    @if (!empty($accommodation))
                      @php
                      $staycation = $staycations->get($accommodation['accommodation_id'] ?? $roomId);
                      $name = strtolower($staycation->name ?? '');
                      $isBYOT = stripos($name, 'byot') !== false || stripos($name, 'bring your own tent') !== false;
                      $isCarCamping = stripos($name, 'car camping') !== false;
                      $quantity = $accommodation['quantity'] ?? 1;
                      $basePrice = $accommodation['base_price'] ?? 0;
                      $mattressPrice = $accommodation['mattress_price'] ?? 0;
                      $petPrice = $accommodation['pet_price'] ?? 0;
                      $totalPrice = $accommodation['total_price'] ?? 0;
                      @endphp
                      @if($staycation)
                        <li>• {{ $staycation->name }}
                          @if($isBYOT || $isCarCamping)
                            {{-- BYOT/Car Camping: Show quantity only --}}
                            - {{ $quantity }} unit{{ $quantity !== 1 ? 's' : '' }}
                            - ₱{{ number_format($totalPrice, 2) }}
                          @else
                            {{-- Regular rooms: Show guests --}}
                            @if(!empty($accommodation['adults']) || !empty($accommodation['children']))
                              ({{ $accommodation['adults'] ?? 0 }} adult{{ ($accommodation['adults'] ?? 0) !== 1 ? 's' : '' }}, {{ $accommodation['children'] ?? 0 }} child{{ ($accommodation['children'] ?? 0) !== 1 ? 'ren' : '' }})
                            @endif
                            - ₱{{ number_format($totalPrice, 2) }}
                            @if($mattressPrice > 0)
                              + Mattress: ₱{{ number_format($mattressPrice, 2) }}
                            @endif
                            @if($petPrice > 0)
                              + Pet: ₱{{ number_format($petPrice, 2) }}
                            @endif
                          @endif
                        </li>
                      @endif
                    @endif
                  @endforeach
                </ul>
              </div>
            @endif

            @if (!empty($selectedFoods))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2">Selected Foods:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @forelse ($selectedFoods as $foodData)
                    @php
                    $foodName = $foodData['name'] ?? 'Unknown Food';
                    $foodPrice = $foodData['price'] ?? 0;
                    $foodQuantity = $foodData['quantity'] ?? 1;
                    $foodItemTotal = $foodPrice * $foodQuantity;
                    @endphp
                    <li>• {{ $foodName }} - ₱{{ number_format($foodPrice, 2) }} × {{ $foodQuantity }} = ₱{{ number_format($foodItemTotal, 2) }}</li>
                  @empty
                    <li>No foods selected</li>
                  @endforelse
                </ul>
              </div>
            @endif

            @if (!empty($selectedActivities))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2">Selected Activities:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivities as $activity)
                    @php
                      $isByotOrCamping = stripos($activity['name'] ?? '', 'byot') !== false || 
                                        stripos($activity['name'] ?? '', 'car camping') !== false || 
                                        stripos($activity['name'] ?? '', 'bring your own') !== false;
                    @endphp
                    <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }}
                      @if(!$isByotOrCamping)
                        x {{ $activity['guests'] }}
                      @endif
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="space-y-2">
                @if($accommodationTotal > 0)
                <div class="flex justify-between items-center text-sm">
                  <span>Accommodation Total:</span>
                  <span>₱{{ number_format($accommodationTotal, 2) }}</span>
                </div>
                @endif
                @if($foodTotal > 0)
                <div class="flex justify-between items-center text-sm">
                  <span>Food & Beverages Total:</span>
                  <span>₱{{ number_format($foodTotal, 2) }}</span>
                </div>
                @endif
                @if($activitiesTotal > 0)
                <div class="flex justify-between items-center text-sm">
                  <span>Activities Total:</span>
                  <span>₱{{ number_format($activitiesTotal, 2) }}</span>
                </div>
                @endif
                {{-- Subtotal (VAT-inclusive) --}}
                <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2">
                  <span>Subtotal:</span>
                  <span class="font-medium">₱{{ number_format($vatBreakdown['gross_amount'] ?? ($accommodationTotal + $foodTotal + $activitiesTotal), 2) }}</span>
                </div>
                {{-- VAT Included --}}
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>Includes 12% VAT:</span>
                  <span>₱{{ number_format($vatBreakdown['vat_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                  <span>Total Amount Paid:</span>
                  <span class="text-[#8f5d54]">₱{{ number_format($vatBreakdown['gross_amount'] ?? ($accommodationTotal + $foodTotal + $activitiesTotal), 2) }}</span>
                </div>
                <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
                <p class="text-xs text-gray-500 italic text-center mt-2">
                  * All prices are VAT-inclusive as required by Philippine tax regulations.
                </p> -->
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
              @php
                $hasSpecialRoom = collect($bookingData['accommodations'] ?? [])->some(function($acc) {
                  $name = $acc['accommodation_name'] ?? '';
                  return stripos($name, 'byot') !== false || stripos($name, 'car camping') !== false || stripos($name, 'bring your own') !== false;
                });
              @endphp
              @if (!$hasSpecialRoom)
              <div>
                <span class="font-medium text-gray-500">Guests:</span>
                <span class="ml-2 text-gray-900">{{ $bookingData['guests'] ?? '' }}</span>
              </div>
              @endif
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
                <h3 class="text-2xl font-medium text-gray-900 mb-2">Selected Activities:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivities as $activity)
                    @php
                      $isByotOrCamping = stripos($activity['name'] ?? '', 'byot') !== false || 
                                        stripos($activity['name'] ?? '', 'car camping') !== false || 
                                        stripos($activity['name'] ?? '', 'bring your own') !== false;
                    @endphp
                    <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }}
                      @if(!$isByotOrCamping)
                        x {{ $activity['guests'] ?? 1 }}
                      @endif
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="space-y-2">
                @if(($bookingData['price'] ?? 0) > 0)
                <div class="flex justify-between items-center text-sm">
                  <span>Accommodation Total:</span>
                  <span>₱{{ number_format($bookingData['price'] ?? 0, 2) }}</span>
                </div>
                @endif
                @php
                $activitiesTotal = collect($selectedActivities ?? [])->sum(function($activity) { return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1); });
                @endphp
                @if($activitiesTotal > 0)
                <div class="flex justify-between items-center text-sm">
                  <span>Activities Total:</span>
                  <span>₱{{ number_format($activitiesTotal, 2) }}</span>
                </div>
                @endif
                {{-- Subtotal (VAT-inclusive) --}}
                <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2">
                  <span>Subtotal:</span>
                  <span class="font-medium">₱{{ number_format($vatBreakdown['gross_amount'] ?? $grandTotal, 2) }}</span>
                </div>
                {{-- VAT Included --}}
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>Includes 12% VAT:</span>
                  <span>₱{{ number_format($vatBreakdown['vat_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                  <span>Total Amount Paid:</span>
                  <span class="text-[#8f5d54]">₱{{ number_format($vatBreakdown['gross_amount'] ?? $grandTotal, 2) }}</span>
                </div>
                <!-- <p class="text-xs text-gray-500 italic text-center mt-2">
                  * All prices are VAT-inclusive as required by Philippine tax regulations.
                </p> -->


                
              </div>
            </div>
          @endif
        </div>

        {{-- Next Steps --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
          <h3 class="text-2xl font-semibold text-blue-900 mb-3">What's Next?</h3>
          <ul class="text-xs text-blue-800 space-y-2 text-left">
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
          <p class="text-xs text-gray-500 mb-2">Need assistance?</p>
          <p class="text-xs text-gray-600">
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
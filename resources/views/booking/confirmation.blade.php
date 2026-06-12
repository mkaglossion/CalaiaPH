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
$paymentSummary = [];
$grandTotal = 0;
$selectedActivityPackages = $selectedActivityPackages ?? ($bookingData['selected_activity_packages'] ?? []);
$bookingFlowType = strtolower((string) ($bookingData['booking_type'] ?? $bookingData['type'] ?? ''));
$confirmationContent = $confirmationContent ?? app(\App\Services\BookingConfirmationContentService::class)->forBookingData($bookingData, [
    'reference' => $bookingData['transaction_id'] ?? '',
    'package' => $bookingData['package'] ?? '',
]);
$pageContent = $confirmationContent['page'] ?? [];
$nextStepsContent = $confirmationContent['next_steps'] ?? [];
$supportContent = $confirmationContent['support'] ?? [];
$buttonContent = $confirmationContent['buttons'] ?? [];

if (isset($isStaycation) && $isStaycation) {
    $confirmationSteps = [
        ['label' => 'Rooms', 'status' => 'completed'],
        ['label' => 'Food', 'status' => 'completed'],
        ['label' => 'Activities', 'status' => 'completed'],
        ['label' => 'Payment', 'status' => 'completed'],
        ['label' => 'Confirmation', 'status' => 'current'],
    ];
} elseif ($bookingFlowType === 'package') {
    $confirmationSteps = [
        ['label' => 'Details', 'status' => 'completed'],
        ['label' => 'Payment', 'status' => 'completed'],
        ['label' => 'Confirmation', 'status' => 'current'],
    ];
} else {
    $confirmationSteps = [
        ['label' => 'Details', 'status' => 'completed'],
        ['label' => 'Activities', 'status' => 'completed'],
        ['label' => 'Payment', 'status' => 'completed'],
        ['label' => 'Confirmation', 'status' => 'current'],
    ];
}

if (isset($isStaycation) && $isStaycation && !empty($bookingData['accommodations'])) {
    // Use canonical payment summary when available.
    $paymentSummary = (array) ($bookingData['payment_summary'] ?? []);

    // Use total_guests from PaymentService if available, otherwise calculate.
    $totalGuests = $bookingData['total_guests'] ?? collect($bookingData['accommodations'])->sum(function($acc) { return ($acc['adults'] ?? 0) + ($acc['children'] ?? 0); });

    // Use nights from PaymentService if available, otherwise calculate.
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

    if (!empty($paymentSummary)) {
        $accommodationTotal = (float) ($bookingData['final_totals']['accommodation_total'] ?? 0);
        $foodTotal = (float) ($bookingData['final_totals']['food_total'] ?? 0);
        $activitiesTotal = (float) ($bookingData['final_totals']['activities_total'] ?? 0);
        $grandTotal = (float) ($paymentSummary['grand_total_paid'] ?? ($bookingData['final_totals']['grand_total'] ?? 0));
    } else {
        foreach ($bookingData['accommodations'] as $roomId => $accommodation) {
            if (!empty($accommodation)) {
                $accommodationTotal += (float) ($accommodation['total_price'] ?? 0);
            }
        }

        $activitiesTotal = collect($selectedActivities ?? [])->sum(function($activity) {
            return (float) ($activity['price'] ?? 0) * (int) ($activity['guests'] ?? 1);
        });

        $activitiesTotal += collect($selectedActivityPackages ?? [])->sum(function($package) {
            return (float) ($package['price'] ?? 0) * (int) ($package['quantity'] ?? 1);
        });

        if (!empty($selectedFoods)) {
            foreach ($selectedFoods as $foodData) {
                $foodTotal += (float) ($foodData['price'] ?? 0) * (int) ($foodData['quantity'] ?? 1);
            }
        }

        $grandTotal = $accommodationTotal + $activitiesTotal + $foodTotal;
    }
}

// Calculate VAT breakdown using VatService (12% Philippine VAT)
$vatService = app(\App\Services\VatService::class);
if ((isset($isStaycation) && $isStaycation) && !empty($paymentSummary)) {
    $fallbackVat = $vatService->generateBreakdown((float) ($grandTotal ?? 0), true);
    $vatBreakdown = [
        'gross_amount' => (float) ($paymentSummary['grand_total_paid'] ?? ($grandTotal ?? 0)),
        'vat_amount' => (float) ($paymentSummary['vat_amount'] ?? ($fallbackVat['vat_amount'] ?? 0)),
    ];
} else {
    $vatBreakdown = $vatService->generateBreakdown((float) ($grandTotal ?? 0), true); // true = VAT inclusive
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
  <x-booking-progress :steps="$confirmationSteps" />

  {{-- Confirmation --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8 text-center">
        {{-- Success Icon --}}
        <div class="mb-6">
          @if(!empty($pageContent['eyebrow']))
            <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">{{ $pageContent['eyebrow'] }}</p>
          @endif
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-check-circle-fill text-4xl text-green-600"></i>
          </div>
          <h1 class="text-2xl font-bold text-[#8f5d54] mb-2">
            {{ $pageContent['title'] ?? 'Booking Confirmed!' }}
          </h1>
          <p class="text-xs text-gray-600">
            {{ $pageContent['description'] ?? 'Your booking has been successfully processed and confirmed.' }}
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

            @if(!empty($bookingData['acceptances'] ?? []))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Accepted Agreements</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach($bookingData['acceptances'] as $agreement)
                    @php
                      $agreementLabel = $agreement['label'] ?? 'Agreement';
                      $acceptedAt = !empty($agreement['accepted_at']) ? \Carbon\Carbon::parse($agreement['accepted_at'])->format('M d, Y h:i A') : null;
                      $agreementDetails = is_array($agreement['details'] ?? null) ? $agreement['details'] : [];
                      $detailLabels = [
                          'participant_name' => 'Participant / Guest Name',
                          'birth_date' => 'Date of Birth',
                          'gender' => 'Gender',
                          'address' => 'Address',
                          'parent_guardian_name' => 'Parent / Guardian Name',
                          'emergency_contact_name' => 'Emergency Contact Name',
                          'emergency_contact_phone' => 'Emergency Contact Number',
                          'relationship_to_participant' => 'Relationship to Participant',
                          'signature_name' => 'Typed Name / Signature',
                          'signature_date' => 'Date',
                          'parent_signature_name' => 'Parent / Guardian Signature',
                          'child_ward_name' => 'Child / Ward Name',
                          'parent_signature_date' => 'Parent / Guardian Date',
                          'medical_condition' => 'Medical or Physical Condition',
                      ];
                    @endphp
                    <li>
                      {{ $agreementLabel }} - {{ !empty($agreement['accepted']) ? 'Accepted' : 'Not accepted' }}
                      @if($acceptedAt)
                        ({{ $acceptedAt }})
                      @endif
                      @if(!empty($agreementDetails))
                        <ul class="mt-1 ml-4 space-y-0.5 text-xs text-gray-500 list-disc">
                          @foreach($agreementDetails as $field => $value)
                            @if($value !== null && $value !== '')
                              <li>
                                <span class="font-medium text-gray-600">{{ $detailLabels[$field] ?? str_replace('_', ' ', ucfirst($field)) }}:</span>
                                {{ $value }}
                              </li>
                            @endif
                          @endforeach
                        </ul>
                      @endif
                    </li>
                  @endforeach
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
                        x {{ $activity['guests'] ?? 1 }}
                      @endif
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

            @if (!empty($selectedActivityPackages))
              <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-2xl font-medium text-gray-900 mb-2">Selected Activity Packages:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                  @foreach ($selectedActivityPackages as $package)
                    @php
                      $packageName = $package['name'] ?? 'Unknown Package';
                      $packagePrice = (float) ($package['price'] ?? 0);
                      $packageQuantity = (int) ($package['quantity'] ?? 1);
                      $packageTotal = $packagePrice * $packageQuantity;
                    @endphp
                    <li>â€¢ {{ $packageName }} - â‚±{{ number_format($packagePrice, 2) }} x {{ $packageQuantity }} = â‚±{{ number_format($packageTotal, 2) }}</li>
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
                      $roomType = strtolower((string)(($staycation ? $staycation->type : null) ?? ($accommodation['type'] ?? '')));
                      $supportsExtraMattress = $roomType === 'glamping'
                        || str_contains($name, 'dome')
                        || str_contains($name, 'bell');
                      $isBYOT = stripos($name, 'byot') !== false || stripos($name, 'bring your own tent') !== false;
                      $isCarCamping = stripos($name, 'car camping') !== false;
                      $quantity = $accommodation['quantity'] ?? 1;
                      $extraMattressCount = (int)($accommodation['extra_mattress'] ?? 0);
                      $basePrice = $accommodation['base_price'] ?? 0;
                      $additionalAdults = (int)($accommodation['additional_adults'] ?? $extraMattressCount);
                      $mattressPrice = $accommodation['additional_adult_price'] ?? ($accommodation['mattress_price'] ?? 0);
                      $petPrice = $accommodation['pet_price'] ?? 0;
                      $totalPrice = $accommodation['total_price'] ?? 0;
                      @endphp
                      @if($staycation)
                        <li>&bull; {{ $staycation->name }}
                          @if($isBYOT || $isCarCamping)
                            {{-- BYOT/Car Camping: Show quantity only --}}
                            - {{ $quantity }} unit{{ $quantity !== 1 ? 's' : '' }}
                            - &#8369;{{ number_format($totalPrice, 2) }}
                          @else
                            {{-- Regular rooms: Show guests --}}
                            @if(!empty($accommodation['adults']) || !empty($accommodation['children']))
                              ({{ $accommodation['adults'] ?? 0 }} adult{{ ($accommodation['adults'] ?? 0) !== 1 ? 's' : '' }}, {{ $accommodation['children'] ?? 0 }} child{{ ($accommodation['children'] ?? 0) !== 1 ? 'ren' : '' }}@if($supportsExtraMattress && $additionalAdults > 0), additional {{ $additionalAdults }} adult{{ $additionalAdults !== 1 ? 's' : '' }}@endif)
                            @endif
                            - &#8369;{{ number_format($totalPrice, 2) }}
                            @if($supportsExtraMattress && $mattressPrice > 0)
                              + Additional Adult{{ $additionalAdults !== 1 ? 's' : '' }}: &#8369;{{ number_format($mattressPrice, 2) }}
                            @endif
                            @if($petPrice > 0)
                              + Pet: &#8369;{{ number_format($petPrice, 2) }}
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
                @if((float)($paymentSummary['promo_discount'] ?? 0) > 0)
                <div class="flex justify-between items-center text-sm text-green-700">
                  <span>Promo Discount:</span>
                  <span>-&#8369;{{ number_format((float)($paymentSummary['promo_discount'] ?? 0), 2) }}</span>
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
          <h3 class="text-2xl font-semibold text-blue-900 mb-3">{{ $nextStepsContent['title'] ?? "What's Next?" }}</h3>
          <ul class="text-xs text-blue-800 space-y-2 text-left">
            @php
              $nextStepIcons = ['bi-envelope', 'bi-calendar-event', 'bi-telephone'];
            @endphp
            @forelse(($nextStepsContent['items'] ?? []) as $index => $step)
              <li class="flex items-start">
                <i class="bi {{ $nextStepIcons[$index] ?? 'bi-dot' }} mr-2 mt-0.5"></i>
                <span>{{ $step }}</span>
              </li>
            @empty
              <li class="flex items-start">
                <i class="bi bi-envelope mr-2 mt-0.5"></i>
                <span>Check your email for a detailed confirmation and booking instructions.</span>
              </li>
            @endforelse
          </ul>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('home') }}"
             class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-6 py-3 text-sm font-semibold text-white hover:bg-[#a0675a] transition-colors">
            <i class="bi bi-house"></i>
            {{ $buttonContent['home'] ?? 'Return to Home' }}
          </a>
          @if(!empty($buttonContent['print']))
            <button onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
              <i class="bi bi-printer"></i>
              {{ $buttonContent['print'] }}
            </button>
          @endif
        </div>

        {{-- Contact Info --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
          <p class="text-xs text-gray-500 mb-2">{{ $supportContent['title'] ?? 'Need assistance?' }}</p>
          <p class="text-xs text-gray-600">
            {{ $supportContent['description'] ?? 'Reach us anytime if you need help with your booking.' }}
            <a href="mailto:{{ $supportContent['email'] ?? 'calaia.ph@gmail.com' }}" class="text-[#8f5d54] hover:underline">{{ $supportContent['email'] ?? 'calaia.ph@gmail.com' }}</a>
            @if(!empty($supportContent['phone']))
              or call <span class="font-medium">{{ $supportContent['phone'] }}</span>
            @endif
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

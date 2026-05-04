{{-- Booking Summary Component --}}
{{-- Usage: <x-booking-summary :booking="$booking" :staycations="$staycations" :selectedFoods="$selectedFoods" :foods="$foods" /> --}}

@props([
    'booking' => [],
    'staycations' => collect(),
    'selectedFoods' => [],
    'foods' => collect(),
    'selectedActivities' => null,
    'selectedActivityPackages' => [],
    'showActivities' => true,
    'showPricing' => true,
    'showDynamicFood' => false,
    'showDynamicActivities' => false,
    'showActionButtons' => false,
    'actionButtonType' => 'food', // 'food', 'activities', 'payment'
    'showTrustElements' => false,
    'showEmptyActivities' => false, // Show activities section even when empty
    // Additional props for dynamic integration
    'accommodationTotal' => 0,
    'activitiesTotal' => 0,
    'foodTotal' => 0,
])

@php
// Ensure selectedActivities is always an array
if (is_string($selectedActivities)) {
    $selectedActivities = json_decode($selectedActivities, true) ?? [];
} elseif ($selectedActivities === null) {
    $selectedActivities = [];
}

// Calculate totals - use provided props if available (for dynamic updates)
$accommodationTotal = $accommodationTotal > 0 ? $accommodationTotal : 0;
$activitiesTotalProp = $activitiesTotal;
$foodTotalProp = $foodTotal;

// If dynamic props are 0, calculate from booking data
if ($accommodationTotal === 0) {
    foreach ($booking['accommodations'] ?? [] as $roomId => $acc) {
        if (!empty($acc)) {
            $accommodationTotal += $acc['total_price'] ?? 0;
        }
    }
}

// Date calculations
$checkinDate = $booking['checkin'] ?? null;
$checkoutDate = $booking['checkout'] ?? null;
$datesSelected = !empty($checkinDate) && !empty($checkoutDate);
$nights = 0;
if ($datesSelected) {
    $start = new DateTime($checkinDate);
    $end = new DateTime($checkoutDate);
    $diff = $start->diff($end);
    $nights = max(1, $diff->days);
}

// Food total
if (!empty($selectedFoods)) {
    foreach ($selectedFoods as $foodData) {
        $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1); // No nights multiplication
    }
}

// Calculate grand total - ALWAYS use cumulative totals from all previous steps
// Get totals from final_totals if available, otherwise calculate from current items
$previousAccommodationTotal = $booking['final_totals']['accommodation_total'] ?? 0;
$previousFoodTotal = $booking['final_totals']['food_total'] ?? 0;
$previousActivitiesTotal = $booking['final_totals']['activities_total'] ?? 0;

// Calculate current step totals
$currentAccommodationTotal = 0;
foreach ($booking['accommodations'] ?? [] as $roomId => $acc) {
    if (!empty($acc)) {
        $currentAccommodationTotal += $acc['total_price'] ?? 0;
    }
}

// Use final_totals if available (they have cumulative data from all steps)
// Otherwise use current calculated totals
$accommodationTotal = ($previousAccommodationTotal > 0) ? $previousAccommodationTotal : $currentAccommodationTotal;
$foodTotal = ($previousFoodTotal > 0) ? $previousFoodTotal : ($calculatedFoodTotal ?? $foodTotal);
$activitiesTotal = ($previousActivitiesTotal > 0) ? $previousActivitiesTotal : $activitiesTotal;

// Calculate cumulative grand total
$grandTotal = $accommodationTotal + $foodTotal + $activitiesTotal;

// If grandTotal is still 0, try to get from final_totals
if ($grandTotal === 0 && isset($booking['final_totals']['grand_total'])) {
    $grandTotal = (float) $booking['final_totals']['grand_total'];
}

// Debug: Log the values for troubleshooting
// file_put_contents(storage_path('logs/debug.log'), "grandTotal: $grandTotal, accommodation: $accommodationTotal, food: $foodTotal, activities: $activitiesTotal\n", FILE_APPEND);

// If grandTotal is 0, try to get from final_totals
if ($grandTotal === 0 && isset($booking['final_totals']['grand_total'])) {
    $grandTotal = (float) $booking['final_totals']['grand_total'];
}

// If grandTotal is still 0, fallback to direct calculation from individual totals
if ($grandTotal === 0) {
    // Get from final_totals if available
    if (isset($booking['final_totals'])) {
        $grandTotal = (float) ($booking['final_totals']['accommodation_total'] ?? 0) + 
                      (float) ($booking['final_totals']['food_total'] ?? 0) + 
                      (float) ($booking['final_totals']['activities_total'] ?? 0);
    } else {
        $grandTotal = ($accommodationTotal ?? 0) + ($activitiesTotal ?? 0) + ($calculatedFoodTotal ?? $foodTotal ?? 0);
    }
}

// Calculate VAT breakdown using VatService (12% Philippine VAT)
$vatService = app(\App\Services\VatService::class);
$vatBreakdown = $vatService->generateBreakdown((float) $grandTotal, false); // false = VAT exclusive (we add VAT to base price)
@endphp

<div class="bg-white rounded-2xl shadow-lg p-6 space-y-6">
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
                <div class="flex justify-between border-t border-gray-100 pt-2">
                    <span class="text-gray-600">Total Nights:</span>
                    <span class="font-semibold text-[#8f5d54]">{{ $nights }}</span>
                </div>
            @else
                <p class="text-gray-500 text-xs">No dates selected</p>
            @endif
        </div>
    </div>

    <hr class="border-gray-300">

    {{-- Selected Rooms Section --}}
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-house mr-2 text-[#8f5d54]"></i> Selected Rooms
        </h4>
        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
            @if(!empty($booking['accommodations']))
                @foreach($booking['accommodations'] as $roomId => $room)
                    @if(!empty($room))
                        @php
                            $actualRoomId = $room['accommodation_id'] ?? $roomId;
                            $staycation = $staycations->get((int)$actualRoomId);
                            $roomNameLower = strtolower($staycation->name ?? '');

                            // Check for BYOT first (prioritize BYOT over car camping)
                            $isBYOT = str_contains($roomNameLower, 'byot') ||
                                     str_contains($roomNameLower, 'bring your own tent') ||
                                     str_contains($roomNameLower, 'bring your own');

                            // Check for car camping only if not BYOT
                            $isCarCamping = !$isBYOT && str_contains($roomNameLower, 'car camping');

                            $isSpecialRoom = $isBYOT || $isCarCamping;
                        @endphp
                        <div class="bg-white rounded border border-gray-100 p-3 text-xs">
                            <div class="font-semibold text-gray-900 mb-2">
                                {{ $staycation->name ?? 'Room #' . $actualRoomId }}
                                @if(($room['instance_num'] ?? 1) > 1)
                                    <span class="text-[#8f5d54]">#{{ $room['instance_num'] }}</span>
                                @endif
                            </div>
                            <div class="text-gray-600 space-y-1 mb-3">
                                @if($isBYOT)
                                    <div>BYOT Qty: <span class="font-semibold">{{ $room['extra_mattress'] ?? 1 }}</span></div>
                                @elseif($isCarCamping)
                                    <div>Vehicles: <span class="font-semibold">{{ $room['extra_mattress'] ?? 1 }}</span></div>
                                @else
                                    <div>Adults: <span class="font-semibold">{{ $room['adults'] ?? 1 }}</span>, Children: <span class="font-semibold">{{ $room['children'] ?? 0 }}</span></div>
                                    <div>Pets: <span class="font-semibold">{{ $room['pets'] ?? 'no' }}</span>
                                        @if(($room['pets'] ?? 'no') === 'yes')
                                            <span>(#{{ $room['num_pets'] ?? 0 }})</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Price Breakdown (only for regular rooms, not BYOT or Car Camping) --}}
                            @if(!$isSpecialRoom)
                            <div class="bg-gray-50 rounded p-2 mb-2 border border-gray-100">
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div class="flex justify-between">
                                        <span>Accommodation:</span>
                                        <span class="font-semibold">₱{{ number_format($room['accommodation_price'] ?? 0, 0) }}</span>
                                    </div>
                                    @if(($room['pets'] ?? 'no') === 'yes' && (($room['num_pets'] ?? 0) > 0))
                                        <div class="flex justify-between">
                                            <span>Pets:</span>
                                            <span class="font-semibold">₱{{ number_format($room['pet_price'] ?? 0, 0) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @php
                                $roomAccommodationPrice = (float)($room['accommodation_price'] ?? 0);
                                $roomPetPrice = (($room['pets'] ?? 'no') === 'yes' && (($room['num_pets'] ?? 0) > 0))
                                    ? (float)($room['pet_price'] ?? 0)
                                    : 0;
                                $roomDisplayTotal = $roomAccommodationPrice + $roomPetPrice;
                            @endphp
                            <div class="border-t border-gray-100 pt-2">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-800">Total:</span>
                                    <span class="font-bold text-[#8f5d54]">₱{{ number_format($roomDisplayTotal, 0) }}</span>
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

    <hr class="border-gray-100">

    @if($showDynamicFood)
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-cup-straw mr-2 text-[#8f5d54]"></i> Selected Food & Beverages
        </h4>

        @include('components.partials._selected-foods-dynamic')
    </div>

    <hr class="border-gray-100">
    @else
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-cup-straw mr-2 text-[#8f5d54]"></i> Selected Food & Beverages
        </h4>

        @include('components.partials._selected-foods-static')
    </div>

    <hr class="border-gray-100">
    @endif

    {{-- Selected Activities Section --}}
    @if($showDynamicActivities)
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-ticket-detailed mr-2 text-[#8f5d54]"></i> Selected Activities
        </h4>
        
        @include('components.partials._selected-activities-dynamic')
    </div>

    <hr class="border-gray-100">
    @elseif($showActivities && (!empty($selectedActivities) || !empty($selectedActivityPackages) || $showEmptyActivities))
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-ticket-detailed mr-2 text-[#8f5d54]"></i> Selected Activities
        </h4>

        @include('components.partials._selected-activities-static', ['selectedActivityPackages' => $selectedActivityPackages])
    </div>

    <hr class="border-gray-300">
    @endif



    {{-- Pricing Summary --}}
    @if($showPricing)
    <div class="space-y-3">
        @if($showDynamicFood || $showDynamicActivities)
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Accommodation:</span>
            <span class="font-semibold" x-text="formatCurrency(accommodationTotal)"></span>
        </div>
        @else
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Accommodation:</span>
            <span class="font-semibold">₱{{ number_format($accommodationTotal, 0) }}</span>
        </div>
        @endif
        @if($showDynamicFood)
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Food & Beverages:</span>
            <span class="font-semibold" x-text="calculateFoodTotal()"></span>
        </div>
        @elseif(!empty($selectedFoods) && count($selectedFoods) > 0)
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Food & Beverages:</span>
            <span class="font-semibold">₱{{ number_format($calculatedFoodTotal ?? $foodTotal, 0) }}</span>
        </div>
        @endif
        @if(($showActivities && !empty($selectedActivities)) || $showDynamicActivities)
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Activities:</span>
            <span id="activities-total" class="font-semibold" x-text="calculateActivitiesTotal()"></span>
        </div>
        @endif
        
        {{-- Subtotal (before VAT) --}}
        <div class="flex justify-between items-center text-sm border-t border-gray-100 pt-2">
            <span class="text-gray-600">Subtotal:</span>
            @if($showDynamicFood || $showDynamicActivities)
                <span id="subtotal" class="font-semibold" x-text="calculateSubtotal()"></span>
            @else
                <span id="subtotal" class="font-semibold">₱{{ number_format($grandTotal, 2) }}</span>
            @endif
        </div>
        
        {{-- VAT Included Breakdown --}}
        <div class="flex justify-between items-center text-sm text-gray-500">
            <span>
                <i class="bi bi-info-circle text-gray-400 text-xs cursor-help" title="Value Added Tax as per Philippine TRAIN Law"></i>
                Includes 12% VAT:
            </span>
            @if($showDynamicFood || $showDynamicActivities)
            <span id="vat-amount" x-text="calculateVAT()"></span>
            @else
            <span id="vat-amount">₱{{ number_format($vatBreakdown['vat_amount'], 2) }}</span>
            @endif
        </div>
        
        {{-- Grand Total --}}
        <div class="flex justify-between items-center text-lg font-bold border-t border-gray-200 pt-2">
            <span>Grand Total:</span>
            @if($showDynamicFood || $showDynamicActivities)
                <span id="grand-total" class="text-[#8f5d54]" x-text="calculateTotal()" data-food-total="{{ $foodTotalProp }}"></span>
            @else
                <span id="grand-total" class="text-[#8f5d54]" data-food-total="{{ $foodTotal }}">₱{{ number_format($vatBreakdown['gross_amount'], 2) }}</span>
            @endif
        </div>
        
        {{-- VAT Note --}}
        <p class="text-xs text-gray-500 italic text-center mt-2">
            * All prices are VAT-inclusive as required by Philippine tax regulations.
        </p>
    </div>
    @endif

    @if($showTrustElements)
    {{-- Trust Elements --}}
    <div class="mt-6">
      <h4 class="text-sm font-semibold text-gray-900 mb-3 text-center">Secure Payment</h4>
      <div class="grid grid-cols-3 gap-2 text-center">
        <div class="flex flex-col items-center">
          <i class="fas fa-lock text-green-600 text-xl mb-1"></i>
          <span class="text-xs text-gray-600">SSL Secured</span>
        </div>
        <div class="flex flex-col items-center">
          <i class="fas fa-shield-alt text-blue-600 text-xl mb-1"></i>
          <span class="text-xs text-gray-600">Protected</span>
        </div>
        <div class="flex flex-col items-center">
          <i class="fas fa-credit-card text-purple-600 text-xl mb-1"></i>
          <span class="text-xs text-gray-600">Encrypted</span>
        </div>
      </div>
      <p class="text-xs text-gray-500 text-center mt-3">Your payment information is encrypted and secure</p>
    </div>
    @endif

    {{-- Action Buttons --}}
    @if($showActionButtons)
    <hr class="border-gray-100 my-6">

    <div class="flex flex-col gap-3">
        @if($actionButtonType === 'food')
            <button type="button"
                    class="w-full py-3 rounded-xl font-semibold text-white bg-[#8f5d54] hover:bg-[#7a4e48] transition flex items-center justify-center shadow-md"
                    @click="submitAndProceed('{{ route('booking.staycationActivities') }}')">
                <i class="bi bi-arrow-right mr-2"></i>
                Proceed to Activities
            </button>
            <button type="button"
                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-700 hover:bg-green-800 transition flex items-center justify-center shadow-md"
                    @click="submitAndProceed('{{ route('booking.staycationPayment') }}')">
                <i class="bi bi-check2 mr-2 text-lg"></i>
                Proceed to Payment
            </button>
        @elseif($actionButtonType === 'activities')
            <button type="button"
                    class="w-full py-3 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 flex items-center justify-center gap-3 border border-gray-300 transition"
                    @click="submitAndProceed('{{ route('booking.staycationPayment') }}')">
                <i class="bi bi-credit-card text-lg text-gray-500"></i>
                <span>Proceed to Payment Without Activities</span>
            </button>
            <button type="button"
                    id="confirm-btn"
                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-700 hover:bg-green-800 flex items-center justify-center shadow-md transition"
                    :class="(selectedActivities.length + selectedActivityPackages.length) === 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    :disabled="(selectedActivities.length + selectedActivityPackages.length) === 0"
                    @click="submitAndProceed('{{ route('booking.staycationPayment') }}')">
                <i class="bi bi-check2 mr-2 text-lg"></i>
                Confirm and Proceed
            </button>
        @endif
    </div>
    @endif
</div>
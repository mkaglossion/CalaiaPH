{{-- Booking Summary Component --}}
{{-- Usage: <x-booking-summary :booking="$booking" :staycations="$staycations" :selectedFoods="$selectedFoods" :foods="$foods" /> --}}

@props([
    'booking' => [],
    'staycations' => collect(),
    'selectedFoods' => [],
    'foods' => collect(),
    'selectedActivities' => null,
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
                                    @if($room['pet_price'] ?? 0 > 0)
                                        <div class="flex justify-between">
                                            <span>Pets:</span>
                                            <span class="font-semibold">₱{{ number_format($room['pet_price'] ?? 0, 0) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <div class="border-t border-gray-100 pt-2">
                                <div class="flex justify-between">
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

    <hr class="border-gray-100">

    {{-- Selected Food Section --}}
    @if($showDynamicFood)
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-cup-straw mr-2 text-[#8f5d54]"></i> Selected Food & Beverages
        </h4>

        <template x-if="selectedFoods.length === 0">
            <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
                <p class="text-gray-500">No food selected yet</p>
            </div>
        </template>

        <template x-if="selectedFoods.length > 0">
            <div class="space-y-4">
                <template x-for="food in selectedFoods" :key="food.id">
                <div class="border rounded-xl p-4 shadow-sm" :class="food.id === 'cook-your-own' ? 'border-green-200 bg-green-50' : 'border-gray-100'">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex-1">
                            <p class="text-sm font-semibold" x-text="food.name"></p>
                            <p class="text-xs text-gray-500 capitalize" :class="food.category === 'special' ? 'text-green-600' : ''" x-text="food.category"></p>
                            <p class="text-sm font-bold mt-1" :class="food.price === 0 ? 'text-green-600' : 'text-[#8f5d54]'" x-text="food.price === 0 ? 'FREE' : '₱' + Number(food.price).toLocaleString()"></p>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-red-600 transition" @click="removeFood(food.id)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    {{-- Quantity controls only for paid items --}}
                    <template x-if="food.id !== 'cook-your-own' && food.category !== 'special'">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" @click="decreaseQty(food.id)">-</button>
                                <input type="number" id="quantity" name="quantity" min="1" max="20" class="w-16 text-center border border-gray-300 rounded-md py-1 text-sm" :value="food.quantity" @change="setField(food.id, 'quantity', $event.target.value)">
                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" @click="increaseQty(food.id)">+</button>
                            </div>
                            <div class="text-right text-[#8f5d54] font-bold text-sm" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
                        </div>

                        <div class="mt-3">
                            <textarea rows="2" class="w-full border border-gray-100 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54] placeholder-gray-400" placeholder="Notes or dietary preferences..." :value="food.notes || ''" @input="setField(food.id, 'notes', $event.target.value)"></textarea>
                        </div>
                    </template>

                    {{-- Special message for special options --}}
                    <template x-if="food.category === 'special' || food.id === 'cook-your-own'">
                        <div class="mt-3 p-3 rounded-lg border" :class="food.name === 'Cook Your Own' ? 'border-green-300' : 'border-blue-300'">
                            <p class="text-xs" :class="food.name === 'Cook Your Own' ? 'text-green-700' : 'text-blue-700'"><i class="bi bi-info-circle mr-1"></i><span x-text="food.name === 'Cook Your Own' ? 'You\'ll have access to a fully equipped kitchen. No additional charge.' : 'Special option selected. No additional charge.'"></span></p>
                        </div>
                    </template>
                </div>
                </template>

            </div>
        </template>
    </div>

    <hr class="border-gray-100">
    @else
    @if(!empty($selectedFoods) && count($selectedFoods) > 0)
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-cup-straw mr-2 text-[#8f5d54]"></i> Selected Food & Beverages
        </h4>
        <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
            @php $calculatedFoodTotal = 0; @endphp
            @foreach($selectedFoods as $selectedFood)
                @php
                    $food = $foods->get($selectedFood['id']);
                    if ($food) {
                        $quantity = $selectedFood['quantity'] ?? 1;
                        $itemTotal = $food->price * $quantity;
                        $calculatedFoodTotal += $itemTotal;
                    }
                @endphp
                @if($food)
                <div class="bg-white rounded border border-gray-100 p-2">
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
        </div>
    </div>

    <hr class="border-gray-100">
    @endif
    @endif

    {{-- Selected Activities Section --}}
    @if($showDynamicActivities && !empty($selectedActivities))
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-ticket-detailed mr-2 text-[#8f5d54]"></i> Selected Activities
        </h4>
        <div id="selected-activities" class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
            @foreach($selectedActivities as $activity)
            <div class="bg-white rounded border border-gray-100 p-2">
                <div class="flex justify-between items-start">
                    <span class="font-semibold text-gray-900">{{ $activity['name'] }}</span>
                    <span class="font-bold text-[#8f5d54]">₱{{ number_format(($activity['price'] ?? 0) * ($activity['guests'] ?? 1), 0) }}</span>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <button type="button" class="w-6 h-6 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">-</button>
                    <span class="text-xs font-semibold">{{ $activity['guests'] ?? 1 }} guest{{ ($activity['guests'] ?? 1) > 1 ? 's' : '' }}</span>
                    <button type="button" class="w-6 h-6 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">+</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <hr class="border-gray-100">
    @elseif($showActivities && (!empty($selectedActivities) || $showEmptyActivities))
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-ticket-detailed mr-2 text-[#8f5d54]"></i> Selected Activities
        </h4>
        <div id="selected-activities" class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
            @if(!empty($selectedActivities))
                @foreach($selectedActivities as $activity)
                <div class="bg-white rounded border border-gray-100 p-2">
                    <div class="flex justify-between items-start">
                        <span class="font-semibold text-gray-900">{{ $activity['name'] }}</span>
                        <span class="font-bold text-[#8f5d54]">₱{{ number_format(($activity['price'] ?? 0) * ($activity['guests'] ?? 1), 0) }}</span>
                    </div>
                    <div class="text-gray-600 mt-1">
                        {{ $activity['guests'] }} guest{{ ($activity['guests'] ?? 1) > 1 ? 's' : '' }}
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-gray-500">No activities selected</p>
            @endif
        </div>
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
                    class="w-full py-3 rounded-xl font-semibold text-white bg-[#8f5d54] hover:bg-[#7a4e48] transition"
                    @click="submitAndProceed('{{ route('booking.staycationActivities') }}')">
                <i class="bi bi-arrow-right mr-2"></i>
                Proceed to Activities
            </button>
            <button type="button"
                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 transition"
                    @click="submitAndProceed('{{ route('booking.staycationPayment') }}')">
                <i class="bi bi-credit-card mr-2"></i>
                Proceed to Payment
            </button>
        @elseif($actionButtonType === 'activities')
            <button type="button"
                    id="skip-btn"
                    class="w-full py-3 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                <i class="bi bi-credit-card mr-2"></i>
                Proceed to Payment Without Activities
            </button>
            <button type="button"
                    id="confirm-btn"
                    class="w-full py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                <i class="bi bi-check-circle mr-2"></i>
                Confirm and Proceed
            </button>
        @endif
    </div>
    @endif
</div>
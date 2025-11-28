@props([
    'bookingData' => null,
    'selectedActivities' => [],
    'accommodationTotal' => 0,
    'activitiesTotal' => 0,
    'grandTotal' => 0,
    'showAccommodation' => false,
    'showActivities' => true,
    'checkinDate' => null,
    'checkoutDate' => null,
    'totalGuests' => 1,
    'nights' => 0,
    'accommodations' => [],
    'bookingType' => 'general',
    'title' => 'Booking Summary'
])

<section aria-labelledby="summary-heading" class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4">
    <h2 id="summary-heading" class="text-2xl font-semibold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">
        {{ $title }}
    </h2>

    {{-- Date and Guest Information --}}
    @if($checkinDate && $checkoutDate)
        <div class="space-y-3 mb-6">
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
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Guests:</span>
                <span class="text-sm font-medium">{{ $totalGuests }}</span>
            </div>
            <hr class="my-3">
        </div>
    @endif

    {{-- Accommodation Details --}}
    @if($showAccommodation && !empty($accommodations))
        <div class="space-y-3 mb-6">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Accommodations:</span>
                <span class="text-sm font-medium">{{ count(array_filter($accommodations)) }} selected</span>
            </div>

            @if($bookingType === 'staycation')
                <div class="mt-2 space-y-1">
                    @foreach($accommodations as $index => $accommodation)
                        @if(!empty($accommodation) && !empty($accommodation['staycation']))
                            @php
                                $quantity = $accommodation['guests'] ?? 1;
                                $name = strtolower($accommodation['staycation']['name']);
                                $isBYOT = str_contains($name, 'byot');
                                $isCarCamping = str_contains($name, 'car camping');
                                $accommodationPrice = 0;

                                if (str_contains($name, 'standard') || str_contains($name, 'premium')) {
                                    $current = new DateTime($checkinDate);
                                    $end = new DateTime($checkoutDate);
                                    while ($current < $end) {
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
                                        $current->modify('+1 day');
                                    }
                                } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                                    $accommodationPrice = 3000 * $nights;
                                } elseif ($isBYOT || $isCarCamping) {
                                    $accommodationPrice = 300 * $quantity * $nights;
                                }

                                $petFee = (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) ? 2000 * $accommodation['num_pets'] : 0;
                            @endphp
                            <div class="text-xs text-gray-600 space-y-1">
                                <div>ACCOMMODATION {{ $index + 1 }}: {{ $accommodation['staycation']['name'] }} - ₱{{ number_format($accommodationPrice + $petFee) }}</div>
                                @if($petFee > 0)
                                    <div>Pet Fee ({{ $accommodation['num_pets'] }} pet{{ $accommodation['num_pets'] > 1 ? 's' : '' }}): ₱{{ number_format($petFee) }}</div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="flex justify-between items-center text-lg font-bold pt-2 border-t border-gray-200">
                <span>Accommodation Total:</span>
                <span>₱ {{ number_format($accommodationTotal) }}</span>
            </div>
        </div>
    @else
        @if($bookingData)
            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Package:</span>
                    <span class="text-sm font-medium">{{ $bookingData['package'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Date:</span>
                    <span class="text-sm font-medium">{{ $bookingData['booking_date'] ? \Carbon\Carbon::parse($bookingData['booking_date'])->format('M j, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Guests:</span>
                    <span class="text-sm font-medium">{{ $bookingData['guests'] ?? 1 }}</span>
                </div>
                <hr class="my-3">
                <div class="flex justify-between items-center text-lg font-bold">
                    <span>Base Price:</span>
                    <span>₱ {{ number_format(($bookingData['price'] ?? 0) / ($bookingData['guests'] ?? 1)) }}/person</span>
                </div>
            </div>
        @endif
    @endif

    {{-- Selected Activities --}}
    @if($showActivities)
        <h3 class="text-xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Selected Activities</h3>
        <div id="selected-activities" class="space-y-3 mb-4">
            @if(empty($selectedActivities))
                <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">No activities selected</p>
            @else
                @foreach($selectedActivities as $activity)
                    <div class="flex justify-between items-start p-2 bg-white rounded border">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-900" style="font-family: 'Gotham Book', sans-serif;">{{ $activity['name'] }}</p>
                            <div class="mt-2">
                                <label class="block text-xs text-gray-600 mb-1" style="font-family: 'Gotham Book', sans-serif;">Number of Guests</label>
                                <input type="number" class="activity-guests w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent"
                                       value="{{ $activity['guests'] }}" min="1" max="{{ $totalGuests }}" data-activity-id="{{ $activity['id'] }}">
                            </div>
                            @if(isset($activity['type']) && $activity['type'] === 'custom')
                                <p class="text-xs text-gray-500 mt-1" style="font-family: 'Gotham Book', sans-serif;">{{ $activity['description'] ?? '' }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-[#8f5d54]">₱{{ number_format(($activity['price'] ?? 0) * ($activity['guests'] ?? 1)) }}</span>
                            <button type="button" class="text-red-500 hover:text-red-700 remove-activity-btn" data-activity-id="{{ $activity['id'] ?? '' }}">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center text-lg font-semibold">
                <span>Activities Total:</span>
                <span id="total-cost" class="text-[#8f5d54]">₱{{ number_format($activitiesTotal) }}</span>
            </div>
        </div>
    @endif

    {{-- Grand Total --}}
    @if($grandTotal > 0)
        <div class="border-t border-gray-200 pt-4 mt-4">
            <div class="flex justify-between items-center text-xl font-bold text-[#8f5d54]">
                <span>Grand Total:</span>
                <span id="grand-total">₱{{ number_format($grandTotal) }}</span>
            </div>
        </div>
    @endif
</section>
{{-- Day Visit Booking Summary Component --}}
@props([
    'booking' => [],
    'selectedActivities' => [],
    'showActivities' => true,
    'showTrustElements' => true
])

@php
// Prices from admin are VAT-exclusive (before tax)
// We need to add 12% VAT to calculate the final amount

// Handle both new (multiple packages) and legacy (single package) data structures
$isMultiPackage = isset($booking['packages']) && is_array($booking['packages']);

if ($isMultiPackage) {
    // New structure: multiple packages
    $packagePrice = $booking['total_price'] ?? 0;
    $bookingDate = $booking['visit_date'] ?? null;
    $totalGuests = $booking['total_guests'] ?? 1;
    $packageCount = $booking['package_count'] ?? 1;
    
    // Get first package name or show count
    $firstPackage = $booking['packages'][0] ?? null;
    if ($packageCount > 1) {
        $packageName = ($firstPackage['name'] ?? 'Day Visit Package') . ' + ' . ($packageCount - 1) . ' more';
    } else {
        $packageName = $firstPackage['name'] ?? 'Day Visit Package';
    }
} else {
    // Legacy structure: single package
    $packagePrice = $booking['price'] ?? 0;
    $bookingDate = $booking['booking_date'] ?? null;
    $totalGuests = $booking['guests'] ?? 1;
    $packageName = $booking['package'] ?? $booking['package_name'] ?? 'Day Visit Package';
}

$datesSelected = !empty($bookingDate);

// Activities total - use price as-is (VAT-exclusive)
$activitiesTotal = 0;
foreach ($selectedActivities as $activity) {
    // Use price as-is (VAT-exclusive from admin)
    $activitiesTotal += ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
}

// Calculate VAT breakdown - prices from admin are VAT-exclusive, need to add VAT
$vatService = app(\App\Services\VatService::class);
$subtotal = $packagePrice + $activitiesTotal;
$vatBreakdown = $vatService->generateBreakdown($subtotal, false); // false = prices are VAT-exclusive
$grandTotal = $vatBreakdown['gross_amount']; // This includes VAT
$vatAmount = $vatBreakdown['vat_amount'];
@endphp

<div class="bg-white rounded-2xl shadow-lg p-6 space-y-6">
    {{-- Selected Date Section --}}
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-calendar-check mr-2 text-[#8f5d54]"></i> Selected Date
        </h4>
        <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
            @if($datesSelected)
                <div class="flex justify-between">
                    <span class="text-gray-600">Visit Date:</span>
                    <span class="font-semibold">{{ \Carbon\Carbon::parse($bookingDate)->format('M j, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Number of Guests:</span>
                    <span class="font-semibold">{{ $totalGuests }}</span>
                </div>
            @else
                <p class="text-gray-500 text-xs">No date selected</p>
            @endif
        </div>
    </div>

    <hr class="border-gray-300">

    {{-- Selected Package Section --}}
    <div>
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
            <i class="bi bi-box mr-2 text-[#8f5d54]"></i> Selected Package
        </h4>
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-900">{{ $packageName }}</span>
                <span class="font-bold text-[#8f5d54]">₱{{ number_format($packagePrice, 0) }}</span>
            </div>
        </div>
    </div>

    <hr class="border-gray-300">

    {{-- Selected Activities Section --}}
    @if($showActivities)
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
                        @php
                        // Use price as-is (VAT-inclusive from admin)
                        $activityTotal = ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
                        @endphp
                        <span class="font-bold text-[#8f5d54]">₱{{ number_format($activityTotal, 0) }}</span>
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
    <div class="space-y-3">
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600 uppercase">{{ $packageName }}:</span>
            <span class="font-semibold">₱{{ number_format($packagePrice, 0) }}</span>
        </div>
        @if($showActivities)
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Activities:</span>
            <span id="activities-total" class="font-semibold">₱{{ number_format($activitiesTotal, 0) }}</span>
        </div>
        @endif
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">VAT (12%):</span>
            <span id="vat-amount" class="font-semibold">₱{{ number_format($vatAmount, 0) }}</span>
        </div>
        <div class="flex justify-between items-center text-lg font-bold border-t border-gray-100 pt-2">
            <span>Grand Total:</span>
            <span id="grand-total" class="text-[#8f5d54]">₱{{ number_format($grandTotal, 0) }}</span>
        </div>
    </div>


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
</div>

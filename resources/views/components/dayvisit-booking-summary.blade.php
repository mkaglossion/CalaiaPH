{{-- Day Visit Booking Summary Component --}}
@props([
    'booking' => [],
    'selectedActivities' => [],
    'showActivities' => true,
    'showTrustElements' => true,
    'vatBreakdown' => null
])

@php
// Calculate totals
$activitiesTotal = 0;
$baseTotal = $booking['price'] ?? 0;

// Activities total
foreach ($selectedActivities as $activity) {
    $activitiesTotal += ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
}

$baseTotal += $activitiesTotal;

// VAT calculation
if ($vatBreakdown) {
    $subtotal = $vatBreakdown['net_amount'] ?? $baseTotal;
    $vatAmount = $vatBreakdown['vat_amount'] ?? 0;
    $grandTotal = $vatBreakdown['gross_amount'] ?? $baseTotal;
} else {
    // Default 12% VAT calculation
    $vatRate = 0.12;
    $subtotal = $baseTotal / (1 + $vatRate);
    $vatAmount = $baseTotal - $subtotal;
    $grandTotal = $baseTotal;
}

// Date calculations
$bookingDate = $booking['booking_date'] ?? null;
$datesSelected = !empty($bookingDate);

// Get package name - check multiple possible keys
$packageName = $booking['package'] ?? $booking['package_name'] ?? 'Day Visit Package';
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
                    <span class="font-semibold">{{ $booking['guests'] ?? 1 }}</span>
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
                <span class="font-bold text-[#8f5d54]">₱{{ number_format($booking['price'] ?? 0, 0) }}</span>
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

    {{-- Pricing Summary with VAT Breakdown --}}
    <div class="space-y-3">
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600 uppercase">{{ $packageName }}:</span>
            <span class="font-semibold">₱{{ number_format($booking['price'] ?? 0, 0) }}</span>
        </div>
        @if($showActivities)
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Activities:</span>
            <span id="activities-total" class="font-semibold">₱{{ !empty($selectedActivities) ? number_format($activitiesTotal, 0) : '0' }}</span>
        </div>
        @endif
        
        {{-- VAT Breakdown --}}
        <div class="border-t border-gray-200 pt-3 mt-3 space-y-2">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-semibold">₱{{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>Includes 12% VAT:</span>
                <span>₱{{ number_format($vatAmount, 2) }}</span>
            </div>
        </div>
        
        <div class="flex justify-between items-center text-lg font-bold border-t border-gray-100 pt-2">
            <span>Grand Total:</span>
            <span id="grand-total" class="text-[#8f5d54]">₱{{ number_format($grandTotal, 2) }}</span>
        </div>
        
        <p class="text-xs text-gray-500 mt-2">
            * All prices are VAT-inclusive as required by Philippine tax regulations.
        </p>
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

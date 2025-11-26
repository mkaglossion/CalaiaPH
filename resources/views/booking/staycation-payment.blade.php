<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Staycation Payment</title>

  {{-- Styles --}}
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

@php
$backUrl = route('booking.staycationActivities');

// Initialize booking session data
$booking = session('booking', [
    'checkin' => null,
    'checkout' => null,
    'accommodations' => [],
    'booking_type' => 'general',
    'timestamp' => now()->timestamp,
    'completed_steps' => [],
    'current_step' => 1,
]);

$checkinDate = $booking['checkin'] ?? null;
$checkoutDate = $booking['checkout'] ?? null;
$datesSelected = !empty($checkinDate) && !empty($checkoutDate);

// Calculate nights for display purposes
$nights = 0;
if ($datesSelected) {
    $start = new DateTime($checkinDate);
    $end = new DateTime($checkoutDate);
    $diff = $start->diff($end);
    $nights = $diff->days;
}

// Calculate total guests
$totalGuests = collect($booking['accommodations'])->sum('guests');

// Calculate accommodation total dynamically
$accommodationTotal = 0;
if ($datesSelected) {
    $start = new DateTime($checkinDate);
    $end = new DateTime($checkoutDate);

    foreach ($booking['accommodations'] ?? [] as $acc) {
        if (!empty($acc) && !empty($acc['staycation'])) {
            $quantity = $acc['guests'] ?? 1;
            $name = strtolower($acc['staycation']['name']);
            $isBYOT = str_contains($name, 'byot');
            $isCarCamping = str_contains($name, 'car camping');

            if (str_contains($name, 'standard') || str_contains($name, 'premium')) {
                $current = clone $start;
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
                    $accommodationTotal += $basePrice + ($extraGuests * 400);
                    $current->modify('+1 day');
                }
            } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                $accommodationTotal += 3000 * $nights;
            } elseif ($isBYOT || $isCarCamping) {
                $accommodationTotal += 300 * $quantity * $nights;
            }
        }
    }

    // Add pet fees
    foreach ($booking['accommodations'] ?? [] as $acc) {
        if (!empty($acc['pet_agreement']) && !empty($acc['num_pets'])) {
            $accommodationTotal += 2000 * $acc['num_pets'];
        }
    }
}

// Get activities total from session
$selectedActivities = $booking['selected_activities'] ?? [];
$activitiesTotal = collect($selectedActivities)->sum(function($activity) {
    return ($activity['price'] ?? 0) * ($activity['guests'] ?? 0);
});
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
        <h1 class="text-2xl font-bold tracking-tight" style="font-family: 'Gotham Book', sans-serif;">Complete Staycation Payment</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">Review your staycation details and complete your payment</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mx-0 md:mx-10 pb-12">
        {{-- Main content --}}
        <div class="lg:col-span-2">
            <div class="flex flex-col">
                {{-- Step indicator --}}
                <small class="text-gray-500 text-sm mb-2">
                    Step 3 of 3: Payment
                </small>

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

                {{-- Payment Form --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
                    <section aria-labelledby="payment-method-heading">
                        <form action="{{ route('booking.processPayment') }}" method="POST" class="space-y-8">
                            @csrf

                            {{-- Validation Errors --}}
                            @if ($errors->any())
                                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    <p class="font-semibold mb-1">There were some problems with your submission:</p>
                                    <ul class="list-disc list-inside space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Customer Information --}}
                            <div class="bg-gray-50 rounded-xl p-4 sm:py-6">
                                <h3 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
                                    <i class="bi bi-person-circle mr-2 text-[#8f5d54]"></i>
                                    Personal Information
                                </h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    {{-- First Name --}}
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            First Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="first_name"
                                            name="first_name"
                                            value=""
                                            autocomplete="given-name"
                                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                                            placeholder="Juan"
                                        >
                                    </div>

                                    {{-- Last Name --}}
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            Last Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="last_name"
                                            name="last_name"
                                            value=""
                                            autocomplete="family-name"
                                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                                            placeholder="Dela Cruz"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
                                    {{-- Email --}}
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            value=""
                                            autocomplete="email"
                                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                                            placeholder="you@example.com"
                                        >
                                    </div>

                                    {{-- Phone --}}
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                            Phone Number <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            value=""
                                            autocomplete="tel"
                                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                                            placeholder="+63 9XX XXX XXXX"
                                        >
                                    </div>
                                </div>

                                {{-- Special Requests --}}
                                <div class="mt-4 sm:mt-6">
                                    <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-2">
                                        Special Requests (Optional)
                                    </label>
                                    <textarea
                                        id="special_requests"
                                        name="special_requests"
                                        rows="4"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                                        placeholder="Any special requirements, celebrations, or notes..."
                                    ></textarea>
                                    <p class="mt-1 text-xs text-gray-400" style="font-family: 'Gotham Book', sans-serif;">
                                        Example: "Vegan meal options", "Birthday surprise setup", or accessibility needs.
                                    </p>
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <fieldset class="space-y-3">
                                <legend
                                    id="payment-method-heading"
                                    class="block text-sm sm:text-base font-medium text-gray-800 mb-1"
                                >
                                    Payment Method
                                </legend>
                                <p class="text-xs text-gray-500 mb-2" style="font-family: 'Gotham Book', sans-serif;">
                                    Secure and encrypted payment via your chosen provider.
                                </p>

                                <div class="space-y-3">
                                    {{-- GCash --}}
                                    <label
                                        for="payment_gcash"
                                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-[#8f5d54]/60 hover:bg-gray-50 transition-colors"
                                    >
                                        <span class="flex items-center gap-3">
                                            <input
                                                id="payment_gcash"
                                                type="radio"
                                                name="payment_method"
                                                value="gcash"
                                                class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                                                checked
                                                required
                                            >
                                            <span class="flex flex-col">
                                                <span class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                                    <i class="bi bi-wallet2 text-base"></i>
                                                    GCash
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    Pay instantly using your GCash wallet.
                                                </span>
                                            </span>
                                        </span>
                                        <i class="fa-solid fa-shield-halved text-gray-300 text-sm" aria-hidden="true"></i>
                                    </label>

                                    {{-- PayMaya --}}
                                    <label
                                        for="payment_paymaya"
                                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-[#8f5d54]/60 hover:bg-gray-50 transition-colors"
                                    >
                                        <span class="flex items-center gap-3">
                                            <input
                                                id="payment_paymaya"
                                                type="radio"
                                                name="payment_method"
                                                value="paymaya"
                                                class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                                            >
                                            <span class="flex flex-col">
                                                <span class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                                    <i class="bi bi-credit-card text-base"></i>
                                                    PayMaya
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    Use your Maya account or linked cards.
                                                </span>
                                            </span>
                                        </span>
                                        <i class="fa-solid fa-shield-halved text-gray-300 text-sm" aria-hidden="true"></i>
                                    </label>

                                    {{-- Bank Transfer --}}
                                    <label
                                        for="payment_bank"
                                        class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-[#8f5d54]/60 hover:bg-gray-50 transition-colors"
                                    >
                                        <span class="flex items-center gap-3">
                                            <input
                                                id="payment_bank"
                                                type="radio"
                                                name="payment_method"
                                                value="bank"
                                                class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                                            >
                                            <span class="flex flex-col">
                                                <span class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                                    <i class="bi bi-bank2 text-base"></i>
                                                    Bank Transfer
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    Transfer via your preferred local bank.
                                                </span>
                                            </span>
                                        </span>
                                        <i class="fa-solid fa-shield-halved text-gray-300 text-sm" aria-hidden="true"></i>
                                    </label>
                                </div>
                            </fieldset>

                            {{-- Terms and Conditions --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6">
                                <div class="flex items-start">
                                    <input
                                        type="checkbox"
                                        id="terms"
                                        name="terms"
                                        class="mt-1 mr-3 h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                                        required
                                    >
                                    <label for="terms" class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                                        I agree to the
                                        <a href="#" class="text-[#8f5d54] hover:underline font-medium">Terms and Conditions</a> and
                                        <a href="#" class="text-[#8f5d54] hover:underline font-medium">Cancellation Policy</a>.
                                        I understand that this booking is subject to availability and confirmation. I also acknowledge that payment is non-refundable except in cases specified in the policy.
                                    </label>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="pt-4 space-y-3">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
                                >
                                    <i class="bi bi-lock-fill mr-2"></i>
                                    <span>Complete Staycation Payment</span>
                                    <span class="font-bold ml-2">
                                        ₱{{ number_format($accommodationTotal + $activitiesTotal, 2, '.', '') }}
                                    </span>
                                </button>


                                <p class="mt-3 text-xs text-center text-gray-500" style="font-family: 'Gotham Book', sans-serif;">
                                    Your payment is secured with 256-bit SSL encryption. By proceeding, you agree to our terms of service.
                                </p>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        {{-- Sidebar: booking summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Booking Summary</h3>

                @if($datesSelected)
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
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Accommodations:</span>
                            <span class="text-sm font-medium">{{ count(array_filter($booking['accommodations'] ?? [])) }} selected</span>
                        </div>
                        <div class="mt-2 space-y-1">
                            @if(!empty($booking['accommodations']))
                                @php
                                $start = new DateTime($checkinDate);
                                $end = new DateTime($checkoutDate);
                                @endphp
                                @foreach($booking['accommodations'] as $index => $accommodation)
                                    @if(!empty($accommodation) && !empty($accommodation['staycation']))
                                        @php
                                        $quantity = $accommodation['guests'] ?? 1;
                                        $name = strtolower($accommodation['staycation']['name']);
                                        $isBYOT = str_contains($name, 'byot');
                                        $isCarCamping = str_contains($name, 'car camping');
                                        $accommodationPrice = 0;
                                        $basePriceTotal = 0;
                                        $extraPriceTotal = 0;
                                        $extraGuests = 0;

                                        if (str_contains($name, 'standard') || str_contains($name, 'premium')) {
                                            $current = clone $start;
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
                                                $basePriceTotal += $basePrice;
                                                $extraPriceTotal += $extraGuests * 400;
                                                $current->modify('+1 day');
                                            }
                                        } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                                            $accommodationPrice = 3000 * $nights;
                                        } elseif ($isBYOT || $isCarCamping) {
                                            $accommodationPrice = 300 * $quantity * $nights;
                                        }

                                        $petFee = (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) ? 2000 * $accommodation['num_pets'] : 0;
                                        $totalPrice = $accommodationPrice + $petFee;
                                        $baseAvg = $nights > 0 ? round($basePriceTotal / $nights) : 0;
                                        $extraAvg = $nights > 0 ? round($extraPriceTotal / $nights) : 0;
                                        @endphp
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <div>ACCOMMODATION {{ $index + 1 }}: {{ $accommodation['staycation']['name'] }} - ₱{{ number_format($baseAvg) }}/night ({{ $accommodation['guests'] }} guests)</div>
                                            @if($extraPriceTotal > 0)
                                                <div>Extra Mattress ({{ $extraGuests }} guest{{ $extraGuests > 1 ? 's' : '' }}): ₱{{ number_format($extraAvg) }}/night</div>
                                            @endif
                                            @if($petFee > 0)
                                                <div>Pet Fee ({{ $accommodation['num_pets'] }} pet{{ $accommodation['num_pets'] > 1 ? 's' : '' }}): ₱{{ number_format($petFee) }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold pt-2 border-t border-gray-200">
                            <span>Accommodation Total:</span>
                            <span>₱ {{ number_format($accommodationTotal, 2, '.', '') }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-500 mb-6" style="font-family: 'Gotham Book', sans-serif;">Please select your dates to see the booking summary.</p>
                @endif

                <hr class="my-4">

                <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Selected Activities</h3>

                <div class="space-y-3 mb-4">
                    @if(!empty($selectedActivities))
                        @foreach($selectedActivities as $activity)
                            <div class="flex justify-between items-start p-2 bg-white rounded border">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-[#8f5d54]">₱{{ number_format($activity['price'] * $activity['guests'], 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">No activities selected</p>
                    @endif
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>Activities Total:</span>
                        <span>₱{{ number_format($activitiesTotal, 2, '.', '') }}</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center text-xl font-bold text-[#8f5d54]">
                        <span>Grand Total:</span>
                        <span>₱{{ number_format($accommodationTotal + $activitiesTotal, 2, '.', '') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers --}}
  <style>
    .font-vila-zilka {
      font-family: 'Vila Zilka Serif', ui-serif, Georgia, 'Times New Roman', serif;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
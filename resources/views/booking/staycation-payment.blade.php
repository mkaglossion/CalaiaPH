
{{-- resources/views/staycation-payment.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Staycation Payment</title>

{{-- Styles --}}
  @vite(['resources/js/app.js'])
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Discount type radio buttons
      const discountTypeRadios = document.querySelectorAll('input[name="discount_type"]');
      const discountIdField = document.getElementById('discount-id-field');
      const discountIdInput = document.getElementById('discount_id_number');
      const discountIdLabel = discountIdField.querySelector('label');

      // Function to update discount ID field
      function updateDiscountIdField(discountType) {
        if (discountType === 'none') {
          discountIdField.classList.add('hidden');
          discountIdInput.disabled = true;
          discountIdInput.removeAttribute('required');
        } else {
          discountIdField.classList.remove('hidden');
          discountIdInput.disabled = false;
          discountIdInput.setAttribute('required', 'required');

          // Update label text based on discount type
          if (discountType === 'senior') {
            discountIdLabel.innerHTML = 'Senior Citizen ID Number <span class="text-red-500">*</span>';
            discountIdInput.placeholder = 'e.g., SC-123456';
          } else if (discountType === 'pwd') {
            discountIdLabel.innerHTML = 'PWD ID Number <span class="text-red-500">*</span>';
            discountIdInput.placeholder = 'e.g., PWD-123456';
          }
        }
      }

      // Add event listeners to radio buttons
      discountTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
          updateDiscountIdField(this.value);
        });
      });

      // Initial update based on default value
      const defaultDiscountType = document.querySelector('input[name="discount_type"]:checked').value;
      updateDiscountIdField(defaultDiscountType);
    });
  </script>
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
    $nights = max(1, $diff->days);
}

// Calculate total guests - sum adults and children from each accommodation
$totalGuests = collect($booking['accommodations'])->sum(function($acc) {
    return ($acc['adults'] ?? 0) + ($acc['children'] ?? 0);
});

// Use the stored totals from the controller
$accommodationTotal = $booking['final_totals']['accommodation_total'] ?? 0;
$activitiesTotal = $booking['final_totals']['activities_total'] ?? 0;
$foodTotal = $booking['final_totals']['food_total'] ?? 0;
$grandTotal = $booking['final_totals']['grand_total'] ?? 0;

// Calculate VAT breakdown using VatService (12% Philippine VAT)
$vatService = app(\App\Services\VatService::class);
$vatBreakdown = $vatService->generateBreakdown((float) $grandTotal, true); // true = VAT inclusive

// Get selected activities from session
$selectedActivities = $booking['selected_activities'] ?? [];
@endphp

<body class="bg-[#f5efe7]">
    @if(isset($_GET['activities']))
    <script>
        window.history.replaceState(null, null, window.location.pathname);
    </script>
    @endif

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
            <h1 class="text-2xl font-bold tracking-tight">Complete Staycation Payment</h1>
            <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
            <p class="text-xs text-gray-500">Review your staycation details and complete your payment</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mx-0 md:mx-10 pb-12">
          {{-- Main content --}}
          <div class="lg:col-span-2">
            <div class="flex flex-col">

              {{-- Progress indicator --}}
              <div class="mb-6">
                  <div class="flex justify-between items-center mb-2">
                      <small class="text-gray-500 text-sm">Step 5 of 5: Payment & Checkout</small>
                      <small class="text-gray-500 text-sm">100% Complete</small>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-[#8f5d54] h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
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
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                            <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                        </span>
                    @endif
                </div>
              </div>

              {{-- Payment Form --}}
              <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
                <section aria-labelledby="payment-method-heading">
                  <form action="{{ route('booking.initiatePayment') }}" method="POST" class="space-y-8">
                    @csrf

                    {{-- Hidden Booking Data --}}
                    <input type="hidden" name="booking_data" value="{{ json_encode($booking) }}">
                    <input type="hidden" name="checkin" value="{{ $booking['checkin'] ?? '' }}">
                    <input type="hidden" name="checkout" value="{{ $booking['checkout'] ?? '' }}">
                    <input type="hidden" name="accommodations" value="{{ json_encode($booking['accommodations'] ?? []) }}">
                    <input type="hidden" name="selected_foods" value="{{ json_encode($booking['selected_foods'] ?? []) }}">
                    <input type="hidden" name="selected_activities" value="{{ json_encode($booking['selected_activities'] ?? []) }}">
                    <input type="hidden" name="booking_type" value="{{ $booking['booking_type'] ?? 'general' }}">
                    <input type="hidden" name="total_guests" value="{{ $totalGuests }}">
                    
                    {{-- Discount Fields --}}
                    <input type="hidden" name="discount_type" value="{{ old('discount_type', $booking['discount_type'] ?? 'none') }}">

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
                      <h3 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
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
                            value="{{ old('first_name') }}"
                            autocomplete="given-name"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                            placeholder="Juan"
                            required
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
                            value="{{ old('last_name') }}"
                            autocomplete="family-name"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                            placeholder="Dela Cruz"
                            required
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
                            value="{{ old('email') }}"
                            autocomplete="email"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                            placeholder="you@example.com"
                            required
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
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                            placeholder="+63 9XX XXX XXXX"
                            required
                          >
                        </div>
                      </div>

                      {{-- Discount Selection --}}
                      <div class="mt-4 sm:mt-6">
                        <p class="block text-sm font-medium text-gray-700 mb-3">
                          Discount Type (Optional)
                        </p>
                        <div class="space-y-3">
                          <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="none"
                                   class="w-4 h-4 text-[#8f5d54] focus:ring-[#8f5d54]"
                                   {{ old('discount_type', $booking['discount_type'] ?? 'none') === 'none' ? 'checked' : '' }}
                                   onchange="toggleIdCapture()">
                            <span class="text-sm text-gray-700">No Discount</span>
                          </label>
                          <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="senior"
                                   class="w-4 h-4 text-[#8f5d54] focus:ring-[#8f5d54]"
                                   {{ old('discount_type', $booking['discount_type'] ?? 'none') === 'senior' ? 'checked' : '' }}
                                   onchange="toggleIdCapture()">
                            <span class="text-sm text-gray-700">Senior Citizen Discount (20%)</span>
                          </label>
                          <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="pwd"
                                   class="w-4 h-4 text-[#8f5d54] focus:ring-[#8f5d54]"
                                   {{ old('discount_type', $booking['discount_type'] ?? 'none') === 'pwd' ? 'checked' : '' }}
                                   onchange="toggleIdCapture()">
                            <span class="text-sm text-gray-700">PWD Discount (20%)</span>
                          </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                          * Discounts are subject to validation upon arrival with valid ID.
                        </p>
                      </div>

                      {{-- ID Format Notice --}}
                      <div id="id-format-notice" class="{{ (old('discount_type', $booking['discount_type'] ?? 'none') === 'none') ? 'hidden' : '' }} mt-4 sm:mt-6 bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                          <i class="bi bi-info-circle text-amber-600 mt-0.5"></i>
                          <div class="text-sm text-amber-800">
                            <p class="font-medium mb-1">ID Format Requirements:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                              <li>Senior Citizen: <strong>SC-XXXXXX</strong> (e.g., SC-123456)</li>
                              <li>PWD: <strong>PWD-XXXXXX</strong> (e.g., PWD-123456)</li>
                            </ul>
                            <p class="mt-2 text-xs font-semibold text-amber-900">
                              <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                              Important: Your 20% discount is subject to ID verification. Please present your valid ID at check-in. Invalid or missing IDs will require payment of the discount difference.
                            </p>
                          </div>
                        </div>
                      </div>

                      {{-- Discount ID Number (only visible if discount is selected) --}}
                      <div id="discount-id-field" class="{{ (old('discount_type', $booking['discount_type'] ?? 'none') === 'none') ? 'hidden' : '' }} mt-4 sm:mt-6">
                        <label for="discount_id_number" class="block text-sm font-medium text-gray-700 mb-2">
                          {{ old('discount_type', $booking['discount_type'] ?? 'none') === 'senior' ? 'Senior Citizen ID Number' : 'PWD ID Number' }} <span class="text-red-500">*</span>
                        </label>
                        <input
                          type="text"
                          id="discount_id_number"
                          name="discount_id_number"
                          value="{{ old('discount_id_number', $booking['discount_id_number'] ?? '') }}"
                          class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                          placeholder="{{ old('discount_type', $booking['discount_type'] ?? 'none') === 'senior' ? 'SC-123456' : 'PWD-123456' }}"
                          pattern="(SC|PWD)-[0-9]{6,}"
                          oninput="validateIdFormat(this)"
                          {{ (old('discount_type', $booking['discount_type'] ?? 'none') === 'none') ? 'disabled' : 'required' }}
                        >
                        <p id="id-format-error" class="text-xs text-red-600 mt-1 hidden">
                          <i class="bi bi-exclamation-circle mr-1"></i>
                          Invalid format. Use SC-XXXXXX or PWD-XXXXXX
                        </p>
                      </div>

                      {{-- ID Capture Section (shown when discount is selected) --}}
                      <div id="id-capture-section" class="{{ (old('discount_type', $booking['discount_type'] ?? 'none') === 'none') ? 'hidden' : '' }} mt-6">
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                          <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-[#8f5d54] bg-opacity-10 rounded-full flex items-center justify-center">
                              <i class="bi bi-person-badge text-[#8f5d54] text-xl"></i>
                            </div>
                            <div>
                              <h3 class="text-lg font-semibold text-gray-800">ID Verification</h3>
                              <p class="text-sm text-gray-500">Upload or capture your ID to claim discount</p>
                            </div>
                          </div>

                          {{-- Capture/Upload Options --}}
                          <div class="grid grid-cols-2 gap-3 mb-4">
                            {{-- Camera Capture Button --}}
                            <button 
                              type="button"
                              id="btn-capture"
                              class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-[#8f5d54] rounded-lg hover:bg-[#8f5d54] hover:bg-opacity-5 transition-colors group"
                            >
                              <i class="bi bi-camera text-2xl text-[#8f5d54] group-hover:scale-110 transition-transform mb-2"></i>
                              <span class="text-sm font-medium text-[#8f5d54]">Capture with Camera</span>
                              <span class="text-xs text-gray-500 mt-1">Take a photo</span>
                            </button>

                            {{-- File Upload Button --}}
                            <button 
                              type="button"
                              id="btn-upload"
                              class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-[#8f5d54] hover:bg-[#8f5d54] hover:bg-opacity-5 transition-colors group"
                            >
                              <i class="bi bi-upload text-2xl text-gray-400 group-hover:text-[#8f5d54] transition-colors mb-2"></i>
                              <span class="text-sm font-medium text-gray-600 group-hover:text-[#8f5d54]">Upload File</span>
                              <span class="text-xs text-gray-500 mt-1">Select from device</span>
                            </button>
                          </div>

                          {{-- Hidden File Inputs --}}
                          <input 
                            type="file" 
                            id="id-file-input" 
                            accept="image/jpeg,image/png,image/jpg"
                            class="hidden"
                          >
                          <input 
                            type="file" 
                            id="id-camera-input" 
                            accept="image/*" 
                            capture="environment"
                            class="hidden"
                          >

                          {{-- Image Preview --}}
                          <div id="preview-container" class="hidden mb-4">
                            <p class="block text-sm font-medium text-gray-700 mb-2">Preview</p>
                            <div class="relative">
                              <img 
                                id="id-preview" 
                                src="" 
                                alt="ID Preview" 
                                class="w-full max-h-64 object-contain rounded-lg border border-gray-200"
                              >
                              <button 
                                type="button"
                                id="btn-remove-image"
                                class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors shadow-md"
                                title="Remove image"
                              >
                                <i class="bi bi-trash text-sm"></i>
                              </button>
                            </div>
                          </div>

                          {{-- Instructions --}}
                          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <div class="flex items-start gap-2">
                              <i class="bi bi-info-circle text-blue-500 mt-0.5"></i>
                              <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">Tips for best results:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                  <li>Ensure good lighting</li>
                                  <li>Keep the ID flat and clearly visible</li>
                                  <li>All corners of the ID should be visible</li>
                                  <li>Text should be readable</li>
                                </ul>
                              </div>
                            </div>
                          </div>

                          {{-- Status Message --}}
                          <div id="id-status-message" class="hidden"></div>
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
                        >{{ old('special_requests') }}</textarea>
                        <p class="mt-1 text-xs text-gray-400">
                          Example: "Vegan meal options", "Birthday surprise setup", or accessibility needs.
                        </p>
                      </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="space-y-3">
                      <h3 class="block text-sm sm:text-base font-medium text-gray-800 mb-1">
                        Payment Method
                      </h3>
                      <p class="text-xs text-gray-500 mb-2">
                        Secure payment via PayMongo. Choose your preferred payment method including card, GCash, PayMaya, or QR PH.
                      </p>

                      {{-- Credit/Debit Card --}}
                      <div class="space-y-3">
                        <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:bg-gray-50 transition-colors cursor-pointer">
                          <input
                            id="payment_paymongo"
                            type="radio"
                            name="payment_method"
                            value="paymongo"
                            class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                            checked
                          >
                          <div class="flex items-center gap-3">
                            <i class="bi bi-credit-card text-base text-gray-600"></i>
                            <div>
                              <span class="text-sm font-semibold text-gray-900">Credit/Debit Card</span>
                              <span class="text-xs text-gray-500 block">Secure payment via PayMongo</span>
                            </div>
                          </div>
                          <i class="fa-solid fa-shield-halved text-gray-300 text-sm ml-auto"></i>
                        </label>

                        {{-- Scan QR Code --}}
                        <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl bg-white hover:bg-gray-50 transition-colors cursor-pointer">
                          <input
                            id="payment_qr"
                            type="radio"
                            name="payment_method"
                            value="qr"
                            class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                          >
                          <div class="flex items-center gap-3">
                            <i class="bi bi-qr-code text-base text-gray-600"></i>
                            <div>
                              <span class="text-sm font-semibold text-gray-900">Scan QR Code</span>
                              <span class="text-xs text-gray-500 block">Pay using GCash, PayMaya, QR PH, or other QR-enabled wallets</span>
                            </div>
                          </div>
                          <i class="fa-solid fa-mobile-screen-button text-gray-300 text-sm ml-auto"></i>
                        </label>
                      </div>
                    </div>

                    {{-- Cancellation Policy (Best Practice UI) --}}
                    @php
                      $defaultPolicy = old('cancellation_policy', $booking['cancellation_policy'] ?? 'flexible');
                    @endphp

                    <div
                      class="space-y-4"
                      x-data="{
                        policy: @js($defaultPolicy),
                        expanded: null,
                        policies: {
                          standard: {
                            label: 'Standard (Free cancellation window)',
                            badge: 'STANDARD',
                            icon: '📅',
                            highlight: 'border-emerald-200 bg-emerald-50/60',
                            ring: 'ring-emerald-200',
                            bullets: [
                              '≥ 7 days before check-in: 100% refund',
                              '3–6 days before check-in: 50% refund',
                              '< 3 days before check-in: No refund'
                            ],
                            fine: [
                              'Refunds are returned to the original payment method when possible.',
                              'Processing time: 3–5 business days after approval.'
                            ]
                          },
                          flexible: {
                            label: 'Flexible (Best value)',
                            badge: 'RECOMMENDED',
                            icon: '🔄',
                            highlight: 'border-amber-300 bg-amber-50/70',
                            ring: 'ring-amber-200',
                            bullets: [
                              '30+ days: 100% refund',
                              '15–29 days: 75% refund',
                              '7–14 days: 50% refund',
                              '3–6 days: 25% refund',
                              '< 3 days: No refund'
                            ],
                            fine: [
                              'Best for unsure plans—more refund tiers as your date gets closer.',
                              'Processing time: 3–5 business days after approval.'
                            ]
                          },
                          nonrefundable: {
                            label: 'Economy (Non-refundable)',
                            badge: 'LOWEST PRICE',
                            icon: '💰',
                            highlight: 'border-rose-200 bg-rose-50/70',
                            ring: 'ring-rose-200',
                            bullets: [
                              'No cash refund after booking is confirmed',
                              'You may rebook based on availability (date change rules may apply)'
                            ],
                            fine: [
                              'Best for confirmed travel dates.',
                              'Fees may apply when changing dates close to check-in.'
                            ]
                          }
                        }
                      }"
                    >
                      <div class="flex items-start justify-between gap-4">
                        <div>
                          <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="bi bi-calendar-check text-2xl text-[#8f5d54]"></i>
                            Cancellation Policy
                          </h3>
                          <p class="text-sm text-gray-600 mt-1">
                            Choose the cancellation terms that match your flexibility. Refund tiers are based on <span class="font-semibold">days before check-in</span>.
                          </p>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
                          <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1">
                            <i class="bi bi-shield-check text-green-600"></i>
                            Clear refund tiers
                          </span>
                          <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1">
                            <i class="bi bi-clock-history text-blue-600"></i>
                            3–5 business days
                          </span>
                        </div>
                      </div>

                      {{-- Server-side error for policy --}}
                      @error('cancellation_policy')
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                          {{ $message }}
                        </div>
                      @enderror

                      <fieldset class="space-y-3">
                        <legend class="sr-only">Select cancellation policy</legend>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                          {{-- Standard --}}
                          <label
                            class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
                            :class="policy === 'standard' ? policies.standard.highlight + ' ring-2 ' + policies.standard.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
                          >
                            <input
                              type="radio"
                              name="cancellation_policy"
                              value="standard"
                              class="sr-only"
                              x-model="policy"
                              required
                            >

                            <div class="flex items-start justify-between gap-3">
                              <div class="flex items-start gap-3">
                                <div class="text-3xl leading-none" aria-hidden="true" x-text="policies.standard.icon"></div>
                                <div>
                                  <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">Standard</p>
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                      <span x-text="policies.standard.badge"></span>
                                    </span>
                                  </div>
                                  <p class="text-xs text-gray-600 mt-1">Good balance of savings + protection.</p>
                                </div>
                              </div>

                              <div class="flex items-center gap-2">
                                <span
                                  class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
                                  :class="policy === 'standard' ? 'border-emerald-400 bg-emerald-100 text-emerald-700' : 'border-gray-200 bg-white text-gray-400'"
                                  aria-hidden="true"
                                >
                                  <i class="bi bi-check-lg"></i>
                                </span>
                              </div>
                            </div>

                            <div class="mt-4 space-y-2 text-xs text-gray-700">
                              <div class="rounded-xl border border-emerald-200/60 bg-white/60 p-3">
                                <ul class="space-y-1.5">
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                      <i class="bi bi-check"></i>
                                    </span>
                                    <span><span class="font-semibold">≥ 7 days</span> — 100% refund</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                      <i class="bi bi-dash"></i>
                                    </span>
                                    <span><span class="font-semibold">3–6 days</span> — 50% refund</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                                      <i class="bi bi-x"></i>
                                    </span>
                                    <span><span class="font-semibold">< 3 days</span> — No refund</span>
                                  </li>
                                </ul>
                              </div>

                              <button
                                type="button"
                                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
                                @click.stop="expanded = expanded === 'standard' ? null : 'standard'"
                                :aria-expanded="expanded === 'standard'"
                              >
                                <i class="bi" :class="expanded === 'standard' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                View full terms
                              </button>

                              <div x-show="expanded === 'standard'" x-transition class="rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-600">
                                <ul class="list-disc pl-5 space-y-1">
                                  <li>Refunds are computed based on the time the cancellation request is submitted.</li>
                                  <li>Processing time: 3–5 business days after approval (banks may take longer).</li>
                                  <li>No-show or early departure is non-refundable.</li>
                                </ul>
                              </div>
                            </div>
                          </label>

                          {{-- Flexible (Recommended) --}}
                          <label
                            class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
                            :class="policy === 'flexible' ? policies.flexible.highlight + ' ring-2 ' + policies.flexible.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
                          >
                            <input
                              type="radio"
                              name="cancellation_policy"
                              value="flexible"
                              class="sr-only"
                              x-model="policy"
                              required
                            >

                            <div class="flex items-start justify-between gap-3">
                              <div class="flex items-start gap-3">
                                <div class="text-3xl leading-none" aria-hidden="true" x-text="policies.flexible.icon"></div>
                                <div>
                                  <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">Flexible</p>
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                                      <span x-text="policies.flexible.badge"></span>
                                    </span>
                                  </div>
                                  <p class="text-xs text-gray-600 mt-1">Most flexible refund tiers as dates get closer.</p>
                                </div>
                              </div>

                              <div class="flex items-center gap-2">
                                <span
                                  class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
                                  :class="policy === 'flexible' ? 'border-amber-400 bg-amber-100 text-amber-700' : 'border-gray-200 bg-white text-gray-400'"
                                  aria-hidden="true"
                                >
                                  <i class="bi bi-check-lg"></i>
                                </span>
                              </div>
                            </div>

                            <div class="mt-4 space-y-2 text-xs text-gray-700">
                              <div class="rounded-xl border border-amber-200/60 bg-white/60 p-3">
                                <ul class="space-y-1.5">
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                      <i class="bi bi-check"></i>
                                    </span>
                                    <span><span class="font-semibold">30+ days</span> — 100%</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                      <i class="bi bi-check"></i>
                                    </span>
                                    <span><span class="font-semibold">15–29 days</span> — 75%</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                      <i class="bi bi-dash"></i>
                                    </span>
                                    <span><span class="font-semibold">7–14 days</span> — 50%</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-orange-100 text-orange-700">
                                      <i class="bi bi-dash"></i>
                                    </span>
                                    <span><span class="font-semibold">3–6 days</span> — 25%</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                                      <i class="bi bi-x"></i>
                                    </span>
                                    <span><span class="font-semibold">< 3 days</span> — No refund</span>
                                  </li>
                                </ul>
                              </div>

                              <button
                                type="button"
                                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
                                @click.stop="expanded = expanded === 'flexible' ? null : 'flexible'"
                                :aria-expanded="expanded === 'flexible'"
                              >
                                <i class="bi" :class="expanded === 'flexible' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                View full terms
                              </button>

                              <div x-show="expanded === 'flexible'" x-transition class="rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-600">
                                <ul class="list-disc pl-5 space-y-1">
                                  <li>Refund tier is based on days before check-in (server time).</li>
                                  <li>Processing time: 3–5 business days after approval (banks may take longer).</li>
                                  <li>No-show or early departure is non-refundable.</li>
                                </ul>
                              </div>
                            </div>
                          </label>

                          {{-- Non-refundable --}}
                          <label
                            class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
                            :class="policy === 'nonrefundable' ? policies.nonrefundable.highlight + ' ring-2 ' + policies.nonrefundable.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
                          >
                            <input
                              type="radio"
                              name="cancellation_policy"
                              value="nonrefundable"
                              class="sr-only"
                              x-model="policy"
                              required
                            >

                            <div class="flex items-start justify-between gap-3">
                              <div class="flex items-start gap-3">
                                <div class="text-3xl leading-none" aria-hidden="true" x-text="policies.nonrefundable.icon"></div>
                                <div>
                                  <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">Non-Refundable</p>
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                      <span x-text="policies.nonrefundable.badge"></span>
                                    </span>
                                  </div>
                                  <p class="text-xs text-gray-600 mt-1">Lowest price for fixed travel plans.</p>
                                </div>
                              </div>

                              <div class="flex items-center gap-2">
                                <span
                                  class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
                                  :class="policy === 'nonrefundable' ? 'border-rose-400 bg-rose-100 text-rose-700' : 'border-gray-200 bg-white text-gray-400'"
                                  aria-hidden="true"
                                >
                                  <i class="bi bi-check-lg"></i>
                                </span>
                              </div>
                            </div>

                            <div class="mt-4 space-y-2 text-xs text-gray-700">
                              <div class="rounded-xl border border-rose-200/60 bg-white/60 p-3">
                                <ul class="space-y-1.5">
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                                      <i class="bi bi-x"></i>
                                    </span>
                                    <span class="font-semibold">No refund after booking confirmation</span>
                                  </li>
                                  <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                      <i class="bi bi-arrow-repeat"></i>
                                    </span>
                                    <span>Rebook options may be available (subject to rules/availability)</span>
                                  </li>
                                </ul>
                              </div>

                              <button
                                type="button"
                                class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
                                @click.stop="expanded = expanded === 'nonrefundable' ? null : 'nonrefundable'"
                                :aria-expanded="expanded === 'nonrefundable'"
                              >
                                <i class="bi" :class="expanded === 'nonrefundable' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                View full terms
                              </button>

                              <div x-show="expanded === 'nonrefundable'" x-transition class="rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-600">
                                <ul class="list-disc pl-5 space-y-1">
                                  <li>No cash refund for cancellations, no-shows, or early departure.</li>
                                  <li>Date changes may be allowed depending on availability and lead time.</li>
                                  <li>Any fare/price difference will be charged upon rebooking.</li>
                                </ul>
                              </div>
                            </div>
                          </label>
                        </div>
                      </fieldset>

                      {{-- Quick summary (what you selected) --}}
                      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                          <div>
                            <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                              <i class="bi bi-check2-circle text-green-600"></i>
                              Selected Policy:
                              <span class="text-[#8f5d54]" x-text="policy ? (policy === 'standard' ? 'Standard' : (policy === 'flexible' ? 'Flexible' : 'Non-Refundable')) : '—'"></span>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                              Tip: Flexible is recommended if your plans might change.
                            </p>
                          </div>

                          <div class="text-xs text-gray-600">
                            <p class="font-semibold text-gray-800">Refund timeline</p>
                            <p>3–5 business days after approval</p>
                          </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                          <template x-for="(b, idx) in (policy ? policies[policy].bullets : [])" :key="idx">
                            <div class="flex items-start gap-2 rounded-xl border border-white bg-white px-3 py-2">
                              <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                <i class="bi bi-dot"></i>
                              </span>
                              <p class="text-xs text-gray-700" x-text="b"></p>
                            </div>
                          </template>
                        </div>

                        <div class="mt-3 text-xs text-gray-600">
                          <p class="font-semibold text-gray-800 mb-1">Important notes</p>
                          <ul class="list-disc pl-5 space-y-1">
                            <li>Peak season dates (e.g., Dec 15–Jan 5, Holy Week & Summer) may have stricter rules.</li>
                            <li>No-show or early departure is typically non-refundable.</li>
                            <li>Cancellations must be requested through the booking system.</li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    {{-- Cancellation Policy Acknowledgment --}}
                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 sm:p-6">
                      <div class="flex items-start">
                        <input
                          type="checkbox"
                          id="cancellation_acknowledge"
                          name="cancellation_acknowledge"
                          class="mt-1 mr-3 h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-600"
                          {{ old('cancellation_acknowledge') ? 'checked' : '' }}
                          required
                        >
                        <label for="cancellation_acknowledge" class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                          <strong>I acknowledge and accept the selected Cancellation Policy</strong><br>
                          I understand the refund terms, timelines, and conditions for the cancellation policy I selected above.
                          I agree that cancellations must be made through the booking system, and refunds will be processed according to the chosen policy terms.
                          During peak seasons, stricter policies may apply.
                        </label>
                      </div>
                      @error('cancellation_acknowledge')
                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                      @enderror
                    </div>

                    {{-- Terms and Conditions --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6">
                      <div class="flex items-start">
                        <input
                          type="checkbox"
                          id="terms"
                          name="terms_accepted"
                          class="mt-1 mr-3 h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                          {{ old('terms_accepted') ? 'checked' : '' }}
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

                    {{-- PayMongo platform fee notice removed: PayMongo does not add a 19.4% platform fee by default. --}}

                    {{-- Action Buttons --}}
                    <div class="pt-4 space-y-3">
                      <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
                      >
                        <i class="bi bi-lock-fill mr-2"></i>
                        <span>Complete Staycation Payment</span>
                        <span class="font-bold ml-2">₱{{ number_format($grandTotal, 2, '.', '') }}</span>
                      </button>

                      <p class="mt-3 text-xs text-center text-gray-500">
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
            <x-booking-summary
                :booking="$booking"
                :staycations="$staycations"
                :selectedFoods="$selectedFoods"
                :foods="$foods"
                :selectedActivities="$selectedActivities"
                :showActivities="true"
                :showTrustElements="true"
            />
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <x-footer />

      <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // ID Capture state - declare first before any functions use it
          let capturedImage = null;

          // Discount type radio buttons
          const discountTypeRadios = document.querySelectorAll('input[name="discount_type"]');
          const discountIdField = document.getElementById('discount-id-field');
          const discountIdInput = document.getElementById('discount_id_number');
          const discountIdLabel = discountIdField.querySelector('label');
          const idCaptureSection = document.getElementById('id-capture-section');

          // Function to update discount ID field
          function updateDiscountIdField(discountType) {
            if (discountType === 'none') {
              discountIdField.classList.add('hidden');
              discountIdInput.disabled = true;
              discountIdInput.removeAttribute('required');
              idCaptureSection.classList.add('hidden');
              resetIdCapture();
            } else {
              discountIdField.classList.remove('hidden');
              discountIdInput.disabled = false;
              discountIdInput.setAttribute('required', 'required');
              idCaptureSection.classList.remove('hidden');

              // Update label text based on discount type
              if (discountType === 'senior') {
                discountIdLabel.innerHTML = 'Senior Citizen ID Number <span class="text-red-500">*</span>';
                discountIdInput.placeholder = 'e.g., SC-123456';
              } else if (discountType === 'pwd') {
                discountIdLabel.innerHTML = 'PWD ID Number <span class="text-red-500">*</span>';
                discountIdInput.placeholder = 'e.g., PWD-123456';
              }
            }
          }

          // Add event listeners to radio buttons
          discountTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
              updateDiscountIdField(this.value);
            });
          });

          // Initial update based on default value
          const defaultDiscountType = document.querySelector('input[name="discount_type"]:checked').value;
          updateDiscountIdField(defaultDiscountType);

          function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
          }

          function openCamera() {
            if (isMobileDevice()) {
              document.getElementById('id-camera-input').click();
            } else {
              if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                startCameraStream();
              } else {
                document.getElementById('id-file-input').click();
              }
            }
          }

          async function startCameraStream() {
            try {
              const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
              });
              showCameraModal(stream);
            } catch (err) {
              console.error('Camera access denied:', err);
              document.getElementById('id-file-input').click();
            }
          }

          function showCameraModal(stream) {
            let modal = document.getElementById('camera-modal');
            if (!modal) {
              modal = document.createElement('div');
              modal.id = 'camera-modal';
              modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
              modal.innerHTML = `
                <div class="bg-white rounded-lg p-4 max-w-lg w-full mx-4">
                  <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Capture ID</h3>
                    <button id="close-camera" class="text-gray-500 hover:text-gray-700">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <div class="relative">
                    <video id="camera-video" class="w-full rounded-lg" autoplay playsinline></video>
                    <canvas id="camera-canvas" class="hidden"></canvas>
                  </div>
                  <div class="mt-4 flex justify-center">
                    <button id="btn-snap" class="bg-[#8f5d54] text-white px-6 py-2 rounded-lg hover:bg-[#7a4a42]">
                      <i class="bi bi-camera"></i> Take Photo
                    </button>
                  </div>
                </div>
              `;
              document.body.appendChild(modal);
            }

            const video = document.getElementById('camera-video');
            const canvas = document.getElementById('camera-canvas');
            
            video.srcObject = stream;
            video.play();

            document.getElementById('btn-snap').onclick = () => {
              canvas.width = video.videoWidth;
              canvas.height = video.videoHeight;
              canvas.getContext('2d').drawImage(video, 0, 0);
              
              capturedImage = canvas.toDataURL('image/jpeg', 0.9);
              showPreview(capturedImage);
              
              stream.getTracks().forEach(track => track.stop());
              modal.remove();
            };

            document.getElementById('close-camera').onclick = () => {
              stream.getTracks().forEach(track => track.stop());
              modal.remove();
            };
          }

          function showPreview(imageSrc) {
            document.getElementById('id-preview').src = imageSrc;
            document.getElementById('preview-container').classList.remove('hidden');
            // Show success toast for ID upload
            Swal.fire({
              icon: 'success',
              title: 'ID image uploaded',
              toast: true,
              position: 'bottom-end',
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true
            });
          }

          function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
              showIdStatus('error', 'Please select an image file.');
              return;
            }

            if (file.size > 2 * 1024 * 1024) {
              showIdStatus('error', 'File size must be less than 2MB.');
              return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
              capturedImage = e.target.result;
              showPreview(capturedImage);
            };
            reader.readAsDataURL(file);
          }

          function showIdStatus(type, message) {
            const statusEl = document.getElementById('id-status-message');
            statusEl.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
            
            if (type === 'success') {
              statusEl.classList.add('bg-green-100', 'text-green-700', 'p-3', 'rounded-lg', 'text-sm');
            } else {
              statusEl.classList.add('bg-red-100', 'text-red-700', 'p-3', 'rounded-lg', 'text-sm');
            }
            
            statusEl.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message;
          }

          function resetIdCapture() {
            const hasImage = document.getElementById('id-preview').src && 
                           document.getElementById('id-preview').src !== window.location.href &&
                           !document.getElementById('preview-container').classList.contains('hidden');
            
            if (hasImage) {
              // Show confirmation before removing
              Swal.fire({
                title: 'Remove ID Image?',
                text: 'Are you sure you want to remove the uploaded ID image?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280'
              }).then((result) => {
                if (result.isConfirmed) {
                  performReset();
                  Swal.fire({
                    icon: 'info',
                    title: 'ID image removed',
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                  });
                }
              });
            } else {
              performReset();
            }
          }

          function performReset() {
            document.getElementById('id-preview').src = '';
            document.getElementById('preview-container').classList.add('hidden');
            document.getElementById('id-file-input').value = '';
            document.getElementById('id-camera-input').value = '';
            document.getElementById('discount_id_number').value = '';
            const idError = document.getElementById('id-format-error');
            if (idError) idError.classList.add('hidden');
            const idInput = document.getElementById('discount_id_number');
            if (idInput) {
              idInput.classList.remove('border-red-500');
              idInput.classList.add('border-gray-300');
            }
            if (typeof capturedImage !== 'undefined') {
              capturedImage = null;
            }
          }

          function validateIdFormat(input) {
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            const value = input.value.toUpperCase();
            const errorEl = document.getElementById('id-format-error');
            
            // Auto-format: add prefix if missing
            if (value && !value.startsWith('SC-') && !value.startsWith('PWD-')) {
              if (discountType === 'senior' && /^\d/.test(value)) {
                input.value = 'SC-' + value;
              } else if (discountType === 'pwd' && /^\d/.test(value)) {
                input.value = 'PWD-' + value;
              }
            }
            
            // Validate format
            const seniorPattern = /^SC-\d{6,}$/;
            const pwdPattern = /^PWD-\d{6,}$/;
            
            let isValid = false;
            if (discountType === 'senior') {
              isValid = seniorPattern.test(input.value);
            } else if (discountType === 'pwd') {
              isValid = pwdPattern.test(input.value);
            }
            
            if (input.value && !isValid) {
              errorEl.classList.remove('hidden');
              input.classList.add('border-red-500');
              input.classList.remove('border-gray-300', 'focus:ring-[#8f5d54]');
            } else {
              errorEl.classList.add('hidden');
              input.classList.remove('border-red-500');
              input.classList.add('border-gray-300', 'focus:ring-[#8f5d54]');
            }
            
            return isValid;
          }

          // Bind ID capture events
          document.getElementById('btn-capture').addEventListener('click', openCamera);
          document.getElementById('btn-upload').addEventListener('click', () => {
            document.getElementById('id-file-input').click();
          });
          document.getElementById('id-file-input').addEventListener('change', handleFileSelect);
          document.getElementById('id-camera-input').addEventListener('change', handleFileSelect);
          document.getElementById('btn-remove-image').addEventListener('click', resetIdCapture);
        });

        // Global function for onchange attribute
        function toggleIdCapture() {
          const discountType = document.querySelector('input[name="discount_type"]:checked').value;
          const idCaptureSection = document.getElementById('id-capture-section');
          const discountIdField = document.getElementById('discount-id-field');
          const discountIdInput = document.getElementById('discount_id_number');
          const idFormatNotice = document.getElementById('id-format-notice');
          
          if (discountType === 'senior' || discountType === 'pwd') {
            idCaptureSection.classList.remove('hidden');
            discountIdField.classList.remove('hidden');
            if (idFormatNotice) idFormatNotice.classList.remove('hidden');
            discountIdInput.disabled = false;
            discountIdInput.setAttribute('required', 'required');
            // Update placeholder based on discount type
            if (discountType === 'senior') {
              discountIdInput.placeholder = 'SC-123456';
            } else {
              discountIdInput.placeholder = 'PWD-123456';
            }
          } else {
            idCaptureSection.classList.add('hidden');
            discountIdField.classList.add('hidden');
            if (idFormatNotice) idFormatNotice.classList.add('hidden');
            discountIdInput.disabled = true;
            discountIdInput.removeAttribute('required');
            // Reset values
            discountIdInput.value = '';
            const idError = document.getElementById('id-format-error');
            if (idError) idError.classList.add('hidden');
          }
        }

        // Form validation before submission
        document.querySelector('form[action="{{ route('booking.initiatePayment') }}"]').addEventListener('submit', function(e) {
          const discountType = document.querySelector('input[name="discount_type"]:checked').value;
          
          if (discountType === 'senior' || discountType === 'pwd') {
            const idNumber = document.getElementById('discount_id_number').value;
            const idImage = document.getElementById('id-preview').src;
            
            // Validate ID format
            const seniorPattern = /^SC-\d{6,}$/;
            const pwdPattern = /^PWD-\d{6,}$/;
            let isValidFormat = false;
            
            if (discountType === 'senior') {
              isValidFormat = seniorPattern.test(idNumber);
            } else {
              isValidFormat = pwdPattern.test(idNumber);
            }
            
            if (!isValidFormat) {
              e.preventDefault();
              Swal.fire({
                icon: 'error',
                title: 'Invalid ID Format',
                html: 'Please enter a valid ID number format:<br><br>' +
                      '<strong>Senior:</strong> SC-123456<br>' +
                      '<strong>PWD:</strong> PWD-123456',
                confirmButtonColor: '#8f5d54'
              });
              document.getElementById('discount_id_number').focus();
              return false;
            }
            
            // Validate ID image is uploaded
            if (!idImage || idImage === '' || idImage === window.location.href) {
              e.preventDefault();
              Swal.fire({
                icon: 'warning',
                title: 'ID Image Required',
                text: 'Please upload or capture your ID image to claim the discount.',
                confirmButtonColor: '#8f5d54'
              });
              return false;
            }
            
            // Show confirmation - prevent default and handle async
            e.preventDefault();
            
            const discountLabel = discountType === 'senior' ? 'Senior Citizen' : 'PWD';
            
            Swal.fire({
              icon: 'info',
              title: 'Discount Verification Notice',
              html: `<div class="text-left">
                <p class="mb-2">You have selected <strong>${discountLabel}</strong> discount.</p>
                <p class="mb-3">ID Number: <strong>${idNumber}</strong></p>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm">
                  <p class="font-semibold text-amber-800 mb-2">⚠️ Important:</p>
                  <ul class="list-disc list-inside text-amber-700 space-y-1">
                    <li>Your ID will be verified at check-in/arrival</li>
                    <li>Invalid or fake IDs will require full payment</li>
                    <li>Discount is subject to admin approval</li>
                  </ul>
                </div>
              </div>`,
              showCancelButton: true,
              confirmButtonText: 'Yes, proceed',
              cancelButtonText: 'Cancel',
              confirmButtonColor: '#8f5d54',
              cancelButtonColor: '#6b7280',
              reverseButtons: true
            }).then((result) => {
              if (result.isConfirmed) {
                e.target.submit();
              }
            });
          }
        });
      </script>

    </div>
</body>
</html>

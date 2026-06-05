{{-- Payment Form Component --}}

@props([
    'action' => '',
    'bookingData' => [],
    'grandTotal' => 0,
    'errors' => null,
    'bookingType' => 'staycation'
])

<section aria-labelledby="payment-method-heading">
  <form action="{{ $action ?: ($bookingType === 'staycation' ? route('booking.initiatePayment') : route('booking.processPayment')) }}" method="POST" class="space-y-8">
    @csrf

    {{-- Hidden Booking Data --}}
    <input type="hidden" name="booking_data" value="{{ json_encode($bookingData) }}">
    
    {{-- Validation Errors --}}
    @if ($errors && $errors->any())
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
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
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
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
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
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
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
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
            placeholder="+63 9XX XXX XXXX"
            required
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
          class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
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
            {{ old('payment_method', 'paymongo') === 'paymongo' ? 'checked' : '' }}
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
            {{ old('payment_method') === 'qr' ? 'checked' : '' }}
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

    {{-- Select Rate Type --}}
    @php
      $defaultPolicy = old('cancellation_policy', $bookingData['cancellation_policy'] ?? 'flexible');
    @endphp

    <x-rate-type-selector :default-policy="$defaultPolicy" />

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

    {{-- Action Buttons --}}
    <div class="pt-4 space-y-3">
      <button
        type="submit"
        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
      >
        <i class="bi bi-lock-fill mr-2"></i>
        <span>Complete Payment</span>
        <span class="font-bold ml-2">₱{{ number_format($grandTotal, 2, '.', '') }}</span>
      </button>

      <p class="mt-3 text-xs text-center text-gray-500">
        Your payment is secured with 256-bit SSL encryption. By proceeding, you agree to our terms of service.
      </p>
    </div>
  </form>
</section>

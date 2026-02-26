{{-- Payment Form Component for Day Visit --}}

@props([
    'action' => '',
    'bookingData' => [],
    'grandTotal' => 0,
    'errors' => null
])

<section aria-labelledby="payment-method-heading">
  <form action="{{ $action }}" method="POST" class="space-y-8">
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

      {{-- Discount Selection --}}
      <div class="mt-4 sm:mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-3">
          Discount Type (Optional)
        </label>
        <div class="space-y-3">
          <label class="flex items-center space-x-3 cursor-pointer">
            <input type="radio" name="discount_type" value="none"
                   class="w-4 h-4 text-[#8f5d54] focus:ring-[#8f5d54]"
                   {{ old('discount_type', 'none') === 'none' ? 'checked' : '' }}>
            <span class="text-sm text-gray-700">No Discount</span>
          </label>
          <label class="flex items-center space-x-3 cursor-pointer">
            <input type="radio" name="discount_type" value="senior"
                   class="w-4 h-4 text-[#8f5d54] focus:ring-[#8f5d54]"
                   {{ old('discount_type', 'none') === 'senior' ? 'checked' : '' }}>
            <span class="text-sm text-gray-700">Senior Citizen Discount (20%)</span>
          </label>
          <label class="flex items-center space-x-3 cursor-pointer">
            <input type="radio" name="discount_type" value="pwd"
                   class="w-4 h-4 text-[#8f5d54] focus:ring-[#8f5d54]"
                   {{ old('discount_type', 'none') === 'pwd' ? 'checked' : '' }}>
            <span class="text-sm text-gray-700">PWD Discount (20%)</span>
          </label>
        </div>
        <p class="mt-2 text-xs text-gray-500">
          * Discounts are subject to validation upon arrival with valid ID.
        </p>
      </div>

      {{-- Discount ID Number (only visible if discount is selected) --}}
      <div id="discount-id-field" class="{{ old('discount_type', 'none') === 'none' ? 'hidden' : '' }} mt-4 sm:mt-6">
        <label for="discount_id_number" class="block text-sm font-medium text-gray-700 mb-2">
          {{ old('discount_type', 'none') === 'senior' ? 'Senior Citizen ID Number' : 'PWD ID Number' }} <span class="text-red-500">*</span>
        </label>
        <input
          type="text"
          id="discount_id_number"
          name="discount_id_number"
          value="{{ old('discount_id_number') }}"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
          placeholder="{{ old('discount_type', 'none') === 'senior' ? 'e.g., SC-123456' : 'e.g., PWD-123456' }}"
          {{ old('discount_type', 'none') === 'none' ? 'disabled' : 'required' }}
        >
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

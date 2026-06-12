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

// Calculate VAT breakdown using VatService (dynamic VAT rate)
$vatService = app(\App\Services\VatService::class);
$vatBreakdown = $vatService->generateBreakdown((float) $grandTotal, false); // false = VAT exclusive
$vatRate = $vatService->getCurrentVatRate();

// Get selected activities from session
$selectedActivities = $booking['selected_activities'] ?? [];
$selectedActivityPackages = $booking['selected_activity_packages'] ?? [];

// Dynamic confirmation CMS content, including the camp waiver copy
$confirmationContent = $confirmationContent ?? app(\App\Services\BookingConfirmationContentService::class)->forType('staycation', [
    'reference' => $booking['transaction_id'] ?? '',
    'package' => $booking['package'] ?? 'Staycation',
]);
$campWaiverContent = $campWaiverContent ?? app(\App\Services\BookingConfirmationContentService::class)->forType('campwaiver', [
    'reference' => $booking['transaction_id'] ?? '',
    'package' => $booking['package'] ?? 'Staycation',
]);
$campWaiverService = app(\App\Services\BookingConfirmationContentService::class);
$campWaiverTitle = trim((string) ($campWaiverContent['camp_waiver']['title'] ?? '')) !== '' ? $campWaiverContent['camp_waiver']['title'] : 'Camp Waiver and Release from Liability';
$campWaiverBody = trim((string) ($campWaiverContent['camp_waiver']['body'] ?? '')) !== '' ? $campWaiverContent['camp_waiver']['body'] : $campWaiverService->defaultCampWaiverBody();
$campWaiverInitial = [
    'participant_name' => old('camp_waiver_participant_name', ''),
    'birth_date' => old('camp_waiver_birth_date', ''),
    'gender' => old('camp_waiver_gender', ''),
    'address' => old('camp_waiver_address', ''),
    'parent_guardian_name' => old('camp_waiver_parent_guardian_name', ''),
    'emergency_name' => old('camp_waiver_emergency_name', ''),
    'emergency_phone' => old('camp_waiver_emergency_phone', ''),
    'relationship' => old('camp_waiver_relationship', ''),
    'medical_condition' => old('camp_waiver_medical_condition', ''),
    'signature_name' => old('camp_waiver_signature_name', ''),
    'signature_date' => old('camp_waiver_signature_date', ''),
    'parent_signature_name' => old('camp_waiver_parent_signature_name', ''),
    'child_ward_name' => old('camp_waiver_child_ward_name', ''),
    'parent_signature_date' => old('camp_waiver_parent_signature_date', ''),
];
$campWaiverHasErrors = collect([
    'camp_waiver_accepted',
    'camp_waiver_participant_name',
    'camp_waiver_birth_date',
    'camp_waiver_gender',
    'camp_waiver_address',
    'camp_waiver_emergency_name',
    'camp_waiver_emergency_phone',
    'camp_waiver_relationship',
    'camp_waiver_signature_name',
    'camp_waiver_signature_date',
    'camp_waiver_parent_signature_name',
    'camp_waiver_child_ward_name',
    'camp_waiver_parent_signature_date',
])->contains(fn ($field) => $errors->has($field));
@endphp

<script>
let appliedPromo = null;
let originalTotal = {{ $vatBreakdown['gross_amount'] }};
let vatRate = {{ $vatRate }};
</script>

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
          <div class="lg:col-span-2 order-2 lg:order-1">
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
                  <form
                    id="staycation-payment-form"
                    action="{{ route('booking.initiatePayment') }}"
                    method="POST"
                    class="space-y-8"
                    @submit="if (!validateCampWaiverBeforePayment()) { $event.preventDefault(); }"
                  >
                    @csrf

                    {{-- Hidden Booking Data --}}
                    <input type="hidden" name="booking_data" value="{{ json_encode($booking) }}">
                    <input type="hidden" name="checkin" value="{{ $booking['checkin'] ?? '' }}">
                    <input type="hidden" name="checkout" value="{{ $booking['checkout'] ?? '' }}">
                    <input type="hidden" name="accommodations" value="{{ json_encode($booking['accommodations'] ?? []) }}">
                    <input type="hidden" name="selected_foods" value="{{ json_encode($booking['selected_foods'] ?? []) }}">
                    <input type="hidden" name="selected_activities" value="{{ json_encode($booking['selected_activities'] ?? []) }}">
                    <input type="hidden" name="selected_activity_packages" value="{{ json_encode($booking['selected_activity_packages'] ?? []) }}">
                    <input type="hidden" name="booking_type" value="{{ $booking['booking_type'] ?? 'general' }}">
                    <input type="hidden" name="total_guests" value="{{ $totalGuests }}">
                    
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

                     {{-- Promo Code --}}
                     <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6">
                       <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                         <i class="bi bi-ticket-perforated mr-2 text-blue-600"></i>
                         Promo Code (Optional)
                       </h3>

                       <div class="space-y-4">
                         <div class="flex gap-3">
                           <div class="flex-1">
                             <label for="promo_code" class="block text-sm font-medium text-gray-700 mb-2">
                               Enter Promo Code
                             </label>
                              <input
                                type="text"
                                id="promo_code"
                                name="promo_code"
                                autocomplete="off"
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Enter your promo code to get discounts"
                              >
                           </div>
                           <div class="flex items-end">
                             <button
                               type="button"
                               id="apply_promo_btn"
                               onclick="applyPromoCode()"
                               class="px-4 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                             >
                               <i class="bi bi-check-circle"></i>
                               Apply
                             </button>
                           </div>
                         </div>

                         <div id="promo_status" class="hidden text-sm"></div>

                         <div id="promo_details" class="hidden bg-white border border-gray-200 rounded-lg p-3">
                           <div class="flex items-center justify-between">
                             <div>
                               <p class="font-semibold text-gray-900" id="promo_name"></p>
                               <p class="text-sm text-gray-600" id="promo_description"></p>
                             </div>
                             <div class="text-right">
                               <p class="text-sm text-gray-500">Discount</p>
                               <p class="font-semibold text-green-600" id="promo_discount"></p>
                             </div>
                           </div>
                         </div>

                         {{-- Hidden inputs for promo data --}}
                         <input type="hidden" id="applied_promo_id" name="applied_promo_id">
                         <input type="hidden" id="applied_promo_code" name="applied_promo_code">
                         <input type="hidden" id="applied_discount_amount" name="applied_discount_amount">
                       </div>
                     </div>

                     {{-- Payment Method --}}
                     <!--<div class="space-y-3">
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
                    </div>-->

                    {{-- Booking Terms & Policy Agreement --}}
                    <div
                      class="bg-yellow-50 border border-blue-200 rounded-xl p-4 sm:p-6"
                      x-data="{
                        bookingPolicyModal: false,
                        campWaiverModal: @js($campWaiverHasErrors),
                        campWaiverStep: @js($campWaiverHasErrors ? 'fill' : 'read'),
                        bookingPolicyAccepted: @js((bool) old('terms_and_policy_accepted')),
                        campWaiverAccepted: @js((bool) old('camp_waiver_accepted')),
                        campWaiver: @js($campWaiverInitial),
                        requiredCampWaiverFields: [
                          'participant_name',
                          'birth_date',
                          'gender',
                          'relationship',
                          'address',
                          'emergency_name',
                          'emergency_phone',
                          'signature_name',
                          'signature_date',
                        ],
                        displayValue(value) { return value && String(value).trim() !== '' ? value : '________________'; },
                        genderLabel(value) {
                          return ({
                            male: 'Male',
                            female: 'Female',
                            other: 'Other',
                            prefer_not_to_say: 'Prefer not to disclose',
                          })[value] || '________________';
                        },
                        isCampWaiverFieldsComplete() {
                          return this.requiredCampWaiverFields.every((field) => {
                            return String(this.campWaiver[field] || '').trim() !== '';
                          });
                        },
                        canProceedToPayment() {
                          return this.bookingPolicyAccepted && this.campWaiverAccepted && this.isCampWaiverFieldsComplete();
                        },
                        getFirstMissingCampWaiverField() {
                          if (!this.bookingPolicyAccepted) {
                            return 'terms_and_policy';
                          }

                          if (!this.campWaiverAccepted) {
                            return 'camp_waiver';
                          }

                          return this.requiredCampWaiverFields.find((field) => {
                            return String(this.campWaiver[field] || '').trim() === '';
                          }) || null;
                        },
                        showPaymentToast(message) {
                          Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: message,
                            showConfirmButton: false,
                            timer: 4500,
                            timerProgressBar: true,
                            customClass: {
                              popup: 'rounded-xl shadow-lg',
                            }
                          });
                        },
                        validateCampWaiverBeforePayment() {
                          const missingField = this.getFirstMissingCampWaiverField();

                          if (!missingField) {
                            return true;
                          }

                          if (missingField === 'terms_and_policy') {
                            this.showPaymentToast('Please accept the terms and conditions before proceeding.');
                            return false;
                          }

                          if (missingField === 'camp_waiver') {
                            this.showPaymentToast('Please accept the camp waiver before proceeding.');
                            return false;
                          }
                          this.campWaiverModal = true;
                          this.campWaiverStep = 'fill';

                          const missingElement = this.$el.querySelector(`#camp_waiver_${missingField}`);
                          if (missingElement) {
                            window.setTimeout(() => missingElement.focus(), 150);
                          }

                          this.showPaymentToast('Please complete the required camp waiver details before proceeding to payment.');
                          return false;
                        }
                      }"
                      @keydown.escape.window="bookingPolicyModal = false; campWaiverModal = false"
                    >
                      <p class="mb-4 text-sm font-medium text-gray-700">
                        Please read our terms and conditions, and camp waiver before proceeding.
                      </p>

                      <div class="flex items-start">
                        <input
                          type="checkbox"
                          id="terms_and_policy"
                          name="terms_and_policy_accepted"
                          x-model="bookingPolicyAccepted"
                          class="mt-1 mr-3 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-600"
                          {{ old('terms_and_policy_accepted') ? 'checked' : '' }}
                          required
                        >
                        <label for="terms_and_policy" class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                          <strong>I agree to the Terms & Conditions and acknowledge the selected Cancellation Policy</strong><br>
                          I understand and accept the refund terms, timelines, and conditions for the cancellation policy I selected above.
                          This booking is subject to availability and confirmation. Cancellations must be made through the booking system, and refunds will be processed according to the chosen policy terms.
                          During peak seasons, stricter policies may apply. Payment is non-refundable except in cases specified in the policy.
                        </label>
                      </div>
                      <div class="mt-3 pl-7">
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-[#8f5d54] hover:underline"
                          @click="bookingPolicyModal = true"
                        >
                          <i class="bi bi-file-earmark-text"></i>
                          Read full booking policy
                        </button>
                      </div>
                      @error('terms_and_policy_accepted')
                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                      @enderror

                      <div class="mt-5 rounded-xl border border-rose-200 bg-white/70 p-4 sm:p-6">
                        <div class="flex items-start">
                          <input
                            type="checkbox"
                            id="camp_waiver"
                            name="camp_waiver_accepted"
                            x-model="campWaiverAccepted"
                            class="mt-1 mr-3 h-4 w-4 text-rose-600 border-gray-300 focus:ring-rose-600"
                            {{ old('camp_waiver_accepted') ? 'checked' : '' }}
                            required
                          >
                          <label for="camp_waiver" class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                            <strong>I agree to the {{ $campWaiverTitle }}</strong><br>
                            I confirm that the details I entered in the camp waiver form are true and complete.
                          </label>
                        </div>

                        <div class="mt-3 pl-7">
                          <button
                            type="button"
                            class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-[#8f5d54] hover:underline"
                            @click="campWaiverStep = 'read'; campWaiverModal = true"
                          >
                            <i class="bi bi-pencil-square"></i>
                            Review or edit camp waiver
                          </button>
                        </div>

                        @error('camp_waiver_accepted')
                          <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                      </div>

                      <div
                        x-show="campWaiverModal"
                        x-cloak
                        x-transition.opacity
                        class="fixed inset-0 z-9999 bg-black/50 p-4 sm:p-6 overflow-y-auto"
                        @click.self="campWaiverModal = false"
                      >
                        <div class="mx-auto mt-4 sm:mt-10 mb-4 w-full max-w-6xl rounded-2xl bg-white shadow-2xl border border-gray-200 max-h-[calc(100vh-2rem)] flex flex-col overflow-hidden">
                          <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                            <div class="space-y-3">
                              <div>
                                <p class="text-xs font-semibold tracking-wide text-[#8f5d54]">CAMP WAIVER</p>
                                <h3 class="mt-1 text-base sm:text-lg font-semibold text-gray-900">{{ $campWaiverTitle }}</h3>
                              </div>

                              <div class="flex flex-wrap gap-2">
                                <button
                                  type="button"
                                  class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors"
                                  :class="campWaiverStep === 'read' ? 'border-[#8f5d54] bg-[#8f5d54] text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                  @click="campWaiverStep = 'read'"
                                >
                                  <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[11px]">1</span>
                                  Review waiver
                                </button>
                                <button
                                  type="button"
                                  class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors"
                                  :class="campWaiverStep === 'fill' ? 'border-[#8f5d54] bg-[#8f5d54] text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                  @click="campWaiverStep = 'fill'"
                                >
                                  <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[11px]">2</span>
                                  Complete form
                                </button>
                              </div>
                            </div>

                            <button
                              type="button"
                              class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                              @click="campWaiverModal = false"
                              aria-label="Close camp waiver modal"
                            >
                              <i class="bi bi-x-lg"></i>
                            </button>
                          </div>

                          <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
                            <div class="space-y-6">
                              <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                                <p class="text-sm font-semibold text-gray-900">How to complete this waiver</p>
                                <p class="mt-1 text-xs sm:text-sm text-gray-600">
                                  Best practice: read the waiver first, then complete the required details. Optional and guardian-related fields are grouped separately to keep the form cleaner on mobile.
                                </p>
                              </div>

                              <div
                                x-show="campWaiverStep === 'read'"
                                x-cloak
                                x-transition.opacity
                                class="space-y-6"
                              >
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
                                  <p class="text-sm font-semibold text-gray-900">Camp Waiver Text</p>
                                  <div class="mt-3 max-h-[42vh] overflow-y-auto pr-1 space-y-4 text-sm leading-6 text-gray-700 whitespace-pre-line">
                                    {!! nl2br(e($campWaiverBody)) !!}
                                  </div>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-3">
                                  <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 lg:col-span-2">
                                    <p class="text-sm font-semibold text-gray-900">Before you proceed</p>
                                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                                      <li>&bull; Make sure the guest information is complete and accurate.</li>
                                      <li>&bull; For minors, the parent or guardian section will need to be filled out too.</li>
                                      <li>&bull; After completing the form, review the live preview before checking the acceptance box.</li>
                                    </ul>
                                  </div>

                                  <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 sm:p-5">
                                    <p class="text-sm font-semibold text-rose-900">Need to make changes?</p>
                                    <p class="mt-1 text-xs sm:text-sm text-rose-800">
                                      Switch to the form step anytime without closing the modal.
                                    </p>
                                  </div>
                                </div>
                              </div>

                              <div
                                x-show="campWaiverStep === 'fill'"
                                x-cloak
                                x-transition.opacity
                                class="space-y-6"
                              >
                                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                                  <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                      <div>
                                        <p class="text-sm font-semibold text-gray-900">Guest Information</p>
                                        <p class="mt-1 text-xs sm:text-sm text-gray-600">Required details first, optional details below.</p>
                                      </div>
                                      <span class="rounded-full bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700">Required before checkout</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                      <div class="sm:col-span-2">
                                        <label for="camp_waiver_participant_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Guest Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="text"
                                          id="camp_waiver_participant_name"
                                          name="camp_waiver_participant_name"
                                          x-model="campWaiver.participant_name"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          placeholder="Juan Dela Cruz"
                                          required
                                        >
                                      </div>

                                      <div>
                                        <label for="camp_waiver_birth_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Date of Birth <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="date"
                                          id="camp_waiver_birth_date"
                                          name="camp_waiver_birth_date"
                                          x-model="campWaiver.birth_date"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          required
                                        >
                                      </div>

                                      <div>
                                        <label for="camp_waiver_gender" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Gender <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                          id="camp_waiver_gender"
                                          name="camp_waiver_gender"
                                          x-model="campWaiver.gender"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base bg-white focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          required
                                        >
                                          <option value="">Choose gender</option>
                                          <option value="male">Male</option>
                                          <option value="female">Female</option>
                                          <option value="other">Other</option>
                                          <option value="prefer_not_to_say">Prefer not to disclose</option>
                                        </select>
                                      </div>

                                      <div class="sm:col-span-2">
                                        <label for="camp_waiver_relationship" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Relationship to Guest <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="text"
                                          id="camp_waiver_relationship"
                                          name="camp_waiver_relationship"
                                          x-model="campWaiver.relationship"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          placeholder="Self, parent, or guardian"
                                          required
                                        >
                                      </div>

                                      <div class="sm:col-span-2">
                                        <label for="camp_waiver_address" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Residential Address <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                          id="camp_waiver_address"
                                          name="camp_waiver_address"
                                          x-model="campWaiver.address"
                                          rows="3"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          placeholder="Complete residential address"
                                          required
                                        ></textarea>
                                      </div>

                                      <div>
                                        <label for="camp_waiver_emergency_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Emergency Contact Person <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="text"
                                          id="camp_waiver_emergency_name"
                                          name="camp_waiver_emergency_name"
                                          x-model="campWaiver.emergency_name"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          placeholder="Full name of contact person"
                                          required
                                        >
                                      </div>

                                      <div>
                                        <label for="camp_waiver_emergency_phone" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Emergency Contact Number <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="tel"
                                          id="camp_waiver_emergency_phone"
                                          name="camp_waiver_emergency_phone"
                                          x-model="campWaiver.emergency_phone"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          placeholder="+63 9XX XXX XXXX"
                                          required
                                        >
                                      </div>

                                      <div class="sm:col-span-2">
                                        <details class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                          <summary class="cursor-pointer text-sm font-semibold text-gray-900">
                                            Optional details and guardian section
                                          </summary>
                                          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                              <label for="camp_waiver_parent_guardian_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                                Parent / Guardian Full Name <span class="text-gray-400">(if applicable)</span>
                                              </label>
                                              <input
                                                type="text"
                                                id="camp_waiver_parent_guardian_name"
                                                name="camp_waiver_parent_guardian_name"
                                                x-model="campWaiver.parent_guardian_name"
                                                class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                                placeholder="Required if the guest is a minor"
                                              >
                                            </div>

                                            <div>
                                              <label for="camp_waiver_parent_signature_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                                Parent / Guardian Name and Signature <span class="text-gray-400">(if under 18)</span>
                                              </label>
                                              <input
                                                type="text"
                                                id="camp_waiver_parent_signature_name"
                                                name="camp_waiver_parent_signature_name"
                                                x-model="campWaiver.parent_signature_name"
                                                class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                                placeholder="Enter the full name of the parent or guardian"
                                              >
                                            </div>

                                            <div>
                                              <label for="camp_waiver_child_ward_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                                Name of Child or Ward <span class="text-gray-400">(if under 18)</span>
                                              </label>
                                              <input
                                                type="text"
                                                id="camp_waiver_child_ward_name"
                                                name="camp_waiver_child_ward_name"
                                                x-model="campWaiver.child_ward_name"
                                                class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                                placeholder="Enter the full name of the child or ward"
                                              >
                                            </div>

                                            <div>
                                              <label for="camp_waiver_parent_signature_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                                Parent / Guardian Date <span class="text-gray-400">(if under 18)</span>
                                              </label>
                                              <input
                                                type="date"
                                                id="camp_waiver_parent_signature_date"
                                                name="camp_waiver_parent_signature_date"
                                                x-model="campWaiver.parent_signature_date"
                                                class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                              >
                                            </div>

                                            <div class="sm:col-span-2">
                                              <label for="camp_waiver_medical_condition" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                                Medical or Physical Condition <span class="text-gray-400">(optional)</span>
                                              </label>
                                              <textarea
                                                id="camp_waiver_medical_condition"
                                                name="camp_waiver_medical_condition"
                                                x-model="campWaiver.medical_condition"
                                                rows="3"
                                                class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                                placeholder="List allergies, conditions, or restrictions, if any"
                                              ></textarea>
                                            </div>
                                          </div>
                                        </details>
                                      </div>

                                      <div>
                                        <label for="camp_waiver_signature_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Signature Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="text"
                                          id="camp_waiver_signature_name"
                                          name="camp_waiver_signature_name"
                                          x-model="campWaiver.signature_name"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          placeholder="Enter your full name"
                                          required
                                        >
                                      </div>

                                      <div>
                                        <label for="camp_waiver_signature_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                          Signature Date <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                          type="date"
                                          id="camp_waiver_signature_date"
                                          name="camp_waiver_signature_date"
                                          x-model="campWaiver.signature_date"
                                          class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm sm:text-base focus:border-[#8f5d54] focus:ring-[#8f5d54]"
                                          required
                                        >
                                      </div>
                                    </div>
                                  </div>

                                  <div class="space-y-4">
                                    <div class="rounded-2xl border border-gray-200 bg-[#faf7f4] p-4 sm:p-5">
                                      <div class="flex items-start justify-between gap-3">
                                        <div>
                                          <p class="text-sm font-semibold text-gray-900">Live Preview</p>
                                          <p class="mt-1 text-xs sm:text-sm text-gray-600">
                                            This updates as the guest fills out the form.
                                          </p>
                                        </div>
                                        <button
                                          type="button"
                                          class="text-xs font-semibold text-[#8f5d54] hover:underline"
                                          @click="campWaiverStep = 'read'"
                                        >
                                          Review waiver text
                                        </button>
                                      </div>

                                      <div class="mt-4 space-y-2 text-sm text-gray-800">
                                        <div><span class="font-medium">Guest Full Name:</span> <span x-text="displayValue(campWaiver.participant_name)"></span></div>
                                        <div class="grid gap-2 sm:grid-cols-2">
                                          <div><span class="font-medium">Date of Birth:</span> <span x-text="displayValue(campWaiver.birth_date)"></span></div>
                                          <div><span class="font-medium">Gender:</span> <span x-text="displayValue(genderLabel(campWaiver.gender))"></span></div>
                                        </div>
                                        <div><span class="font-medium">Residential Address:</span> <span x-text="displayValue(campWaiver.address)"></span></div>
                                        <div><span class="font-medium">Parent/Guardian Full Name:</span> <span x-text="displayValue(campWaiver.parent_guardian_name)"></span></div>
                                        <div><span class="font-medium">Emergency Contact Person:</span> <span x-text="displayValue(campWaiver.emergency_name)"></span></div>
                                        <div><span class="font-medium">Emergency Contact Phone Number:</span> <span x-text="displayValue(campWaiver.emergency_phone)"></span></div>
                                        <div><span class="font-medium">Relationship to Guest:</span> <span x-text="displayValue(campWaiver.relationship)"></span></div>
                                        <div><span class="font-medium">Medical / Physical Condition:</span> <span x-text="displayValue(campWaiver.medical_condition)"></span></div>
                                        <div class="pt-2 border-t border-gray-200">
                                          <div><span class="font-medium">Guest Signature:</span> <span x-text="displayValue(campWaiver.signature_name)"></span></div>
                                          <div><span class="font-medium">Signature Date:</span> <span x-text="displayValue(campWaiver.signature_date)"></span></div>
                                        </div>
                                        <div class="pt-2 border-t border-gray-200">
                                          <div><span class="font-medium">Parent/Guardian Signature:</span> <span x-text="displayValue(campWaiver.parent_signature_name)"></span></div>
                                          <div><span class="font-medium">Name of Child or Ward:</span> <span x-text="displayValue(campWaiver.child_ward_name)"></span></div>
                                          <div><span class="font-medium">Date:</span> <span x-text="displayValue(campWaiver.parent_signature_date)"></span></div>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
                                      <p class="text-sm font-semibold text-gray-900">Camp Waiver Text</p>
                                      <div class="mt-3 max-h-[28vh] overflow-y-auto pr-1 space-y-4 text-sm leading-6 text-gray-700 whitespace-pre-line">
                                        {!! nl2br(e($campWaiverBody)) !!}
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="border-t border-gray-100 bg-white px-5 py-4 sm:px-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                              <p class="text-xs sm:text-sm text-gray-500">
                                Read first, then complete the form. Keep the waiver open if you need to review it while filling out the details.
                              </p>
                              <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                                <button
                                  type="button"
                                  class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                  @click="campWaiverModal = false"
                                >
                                  Close
                                </button>
                                <button
                                  type="button"
                                  class="inline-flex items-center justify-center rounded-lg bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a0675a]"
                                  x-show="campWaiverStep === 'read'"
                                  x-cloak
                                  @click="campWaiverStep = 'fill'"
                                >
                                  Continue to form
                                </button>
                                <button
                                  type="button"
                                  class="inline-flex items-center justify-center rounded-lg bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a0675a]"
                                  x-show="campWaiverStep === 'fill'"
                                  x-cloak
                                  @click="campWaiverStep = 'read'"
                                >
                                  Back to review
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div
                        x-show="bookingPolicyModal"
                        x-cloak
                        x-transition.opacity
                        class="fixed inset-0 z-9999 bg-black/50 p-4 sm:p-6"
                        @click.self="bookingPolicyModal = false"
                      >
                        <div class="mx-auto mt-4 sm:mt-10 w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-gray-200">
                          <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                            <div>
                              <p class="text-xs font-semibold tracking-wide text-[#8f5d54]">BOOKING POLICY</p>
                              <h3 class="mt-1 text-base sm:text-lg font-semibold text-gray-900">Booking Confirmation Terms</h3>
                            </div>
                            <button
                              type="button"
                              class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                              @click="bookingPolicyModal = false"
                              aria-label="Close booking policy modal"
                            >
                              <i class="bi bi-x-lg"></i>
                            </button>
                          </div>

                          <div class="max-h-[70vh] overflow-y-auto px-5 py-4 sm:px-6 sm:py-5">
                            <div class="space-y-5 text-sm text-gray-700">
                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">Can I modify my booking after confirmation?</p>
                                <p>Yes. Modifications such as guest details, add-ons, or activity preferences can be made up to the night before your date of stay, subject to availability.</p>
                              </div>

                              <hr class="border-gray-200">

                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">Can I rebook my stay?</p>
                                <ul class="space-y-1">
                                  <li>&bull; You are entitled to one (1) free rebooking if you inform us more than 6 days before your original booking date.</li>
                                  <li>&bull; Rebooking requests made less than 6 days before the original booking will incur a 20% charge based on the tent rental fees and any prepaid meals.</li>
                                  <li>&bull; Second rebooking requests, regardless of when they are made, will be charged 50% of the tent rental fees and prepaid meals.</li>
                                  <li>&bull; A third rebooking is no longer allowed, and the booking will be considered void.</li>
                                  <li>&bull; The new date must be within 30 days from your original booking date.</li>
                                </ul>
                              </div>

                              <hr class="border-gray-200">

                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">What is your cancellation policy?</p>
                                <ul class="space-y-1">
                                  <li>&bull; 7 days or more before the booking date &ndash; Full refund.</li>
                                  <li>&bull; 3&ndash;6 days before the booking date &ndash; 50% refund.</li>
                                  <li>&bull; Less than 3 days before the booking date &ndash; Non-refundable.</li>
                                </ul>
                              </div>

                              <hr class="border-gray-200">

                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">Are there refunds for bad weather or emergencies?</p>
                                <p>If Calaia Nature Park cancels your booking due to bad weather or unforeseen circumstances, you will receive a full refund or the option to reschedule.</p>
                              </div>

                              <hr class="border-gray-200">

                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">What happens if I don't show up for my booking?</p>
                                <p>No-shows are non-refundable.</p>
                              </div>

                              <hr class="border-gray-200">

                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">How do I request a refund?</p>
                                <p>Please contact us via Instagram or Facebook Messenger with your booking details and proof of payment for verification.</p>
                              </div>

                              <hr class="border-gray-200">

                              <div class="space-y-1">
                                <p class="font-semibold text-gray-900">Can I transfer my reservation to someone else?</p>
                                <p>No. All reservations and bookings are non-transferable.</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    {{-- PayMongo platform fee notice removed: PayMongo does not add a 19.4% platform fee by default. --}}

                    {{-- Action Buttons --}}
                    <div class="pt-4 space-y-3">
                      <div :class="canProceedToPayment() ? '' : 'cursor-not-allowed'">
                      <button
                        type="button"
                        @click="if (validateCampWaiverBeforePayment()) { $el.closest('form').requestSubmit(); }"
                        :disabled="!canProceedToPayment()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600 disabled:shadow-none disabled:hover:bg-gray-300 disabled:hover:translate-y-0 disabled:focus:ring-gray-300"
                      >
                        <i class="bi bi-lock-fill mr-2"></i>
                        <span>Complete Staycation Payment</span>
                        <span id="button-total" class="font-bold ml-2">₱{{ number_format($vatBreakdown['gross_amount'], 2) }}</span>
                      </button>
                      </div>

                      <div class="rounded-xl border border-gray-200 bg-white/70 px-4 py-3 text-center">
                        <p x-show="canProceedToPayment()" x-cloak class="text-xs sm:text-sm font-medium text-green-700">
                          Ready for payment.
                        </p>
                        <p x-show="!canProceedToPayment()" x-cloak class="text-xs sm:text-sm font-medium text-gray-600">
                          Complete and accept the waiver form to enable payment.
                        </p>
                      </div>

                      <p class="mt-2 text-xs text-center text-gray-500">
                        Your payment is secured with 256-bit SSL encryption. By proceeding, you agree to our terms and conditions and camp waiver.
                      </p>
                    </div>
                  </form>
                </section>
              </div>
            </div>
          </div>

          {{-- Sidebar: booking summary --}}
          <div class="lg:col-span-1 order-1 lg:order-2">
            <x-booking-summary
                :booking="$booking"
                :staycations="$staycations"
                :selectedFoods="$selectedFoods"
                :foods="$foods"
                :selectedActivities="$selectedActivities"
                :selectedActivityPackages="$selectedActivityPackages"
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
function applyPromoCode() {
    const code = document.getElementById('promo_code').value.trim().toUpperCase();
    const btn = document.getElementById('apply_promo_btn');
    const statusEl = document.getElementById('promo_status');

    if (!code) {
        showPromoStatus('error', 'Please enter a promo code.');
        return;
    }

    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Applying...';

    // Clear previous status
    statusEl.classList.add('hidden');

    fetch('{{ route("api.promo-codes.apply") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            code: code,
            booking_total: originalTotal
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Promo code response:', data);
        if (data.valid) {
            appliedPromo = data.promo_code;

            // Check if required data is available
            if (!data.promo_code || data.promo_code.discount_amount === undefined || data.new_total === undefined) {
                let missing = [];
                if (!data.promo_code) missing.push('promo_code');
                if (data.promo_code && data.promo_code.discount_amount === undefined) missing.push('discount_amount');
                if (data.new_total === undefined) missing.push('new_total');
                showPromoStatus('error', 'Invalid promo code response: missing ' + missing.join(', ') + '. Please try again.');
                return;
            }

            // Update hidden inputs
            document.getElementById('applied_promo_id').value = data.promo_code.id;
            document.getElementById('applied_promo_code').value = data.promo_code.code;
            document.getElementById('applied_discount_amount').value = data.promo_code.discount_amount;

            // Show promo details
            document.getElementById('promo_name').textContent = data.promo_code.name;
            document.getElementById('promo_description').textContent = data.promo_code.code;
            document.getElementById('promo_discount').textContent = formatPhpCurrency(parseFloat(data.promo_code.discount_amount));
            document.getElementById('promo_details').classList.remove('hidden');

            // Update totals display
            updateTotalsDisplay(parseFloat(data.new_total));

            showPromoStatus('success', data.message + ' You saved ' + formatPhpCurrency(parseFloat(data.promo_code.discount_amount)) + '!');

            // Change button to remove
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Remove';
            btn.onclick = removePromoCode;
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-red-600', 'hover:bg-red-700');
        } else {
            showPromoStatus('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error applying promo code:', error);
        showPromoStatus('error', 'Error applying promo code. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

function removePromoCode() {
    appliedPromo = null;

    // Clear hidden inputs
    document.getElementById('applied_promo_id').value = '';
    document.getElementById('applied_promo_code').value = '';
    document.getElementById('applied_discount_amount').value = '';

    // Hide promo details
    document.getElementById('promo_details').classList.add('hidden');

    // Reset totals
    updateTotalsDisplay(originalTotal);

    // Clear input and status
    document.getElementById('promo_code').value = '';
    document.getElementById('promo_status').classList.add('hidden');

    // Reset button
    const btn = document.getElementById('apply_promo_btn');
    btn.innerHTML = '<i class="bi bi-check-circle"></i> Apply';
    btn.onclick = applyPromoCode;
    btn.classList.remove('bg-red-600', 'hover:bg-red-700');
    btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
}

function updateTotalsDisplay(newTotal) {
    if (typeof newTotal !== 'number' || isNaN(newTotal)) {
        console.error('Invalid newTotal value:', newTotal);
        return;
    }

    // Update the button total
    const buttonTotal = document.getElementById('button-total');
    if (buttonTotal) {
        buttonTotal.textContent = formatPhpCurrency(newTotal);
    }

    // Update sidebar grand total
    const sidebarTotal = document.getElementById('grand-total');
    if (sidebarTotal) {
        sidebarTotal.textContent = formatPhpCurrency(newTotal);
    }
}

function formatPhpCurrency(amount) {
    const value = Number(amount);
    if (!Number.isFinite(value)) return '₱0.00';
    return '₱' + value.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function showPromoStatus(type, message) {
    const statusEl = document.getElementById('promo_status');
    statusEl.classList.remove('hidden', 'text-green-600', 'text-red-600');

    if (type === 'success') {
        statusEl.classList.add('text-green-600');
    } else {
        statusEl.classList.add('text-red-600');
    }

    statusEl.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message;
}
      </script>



    </div>
</body>
</html>

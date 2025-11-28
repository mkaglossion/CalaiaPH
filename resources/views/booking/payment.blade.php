<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Payment</title>

  {{-- Styles --}}
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-gray-50 font-sans antialiased min-h-screen">
  {{-- Header --}}
  <x-header active="booking" />

  {{-- Progress Indicator --}}
  <div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-3">
      <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
        {{-- Step 1: Details --}}
        <div class="flex items-center text-green-600">
          <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-2">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-sm font-medium">Details</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Activities --}}
        <div class="flex items-center text-green-600">
          <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-2">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-sm font-medium">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Payment --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-8 h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-sm font-bold mb-1">
            3
          </div>
          <span class="text-sm font-medium">Payment</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 4: Confirmation --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold mb-1">
            4
          </div>
          <span class="text-sm font-medium">Confirmation</span>
        </div>
      </div>
    </div>
  </div>

  <main class="py-8 sm:py-12 md:py-16 min-h-[80vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
        {{-- Page Title --}}
        <header class="mb-6 sm:mb-8 text-center">
          <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
            CalaiaPH Booking
          </p>
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#8f5d54] font-gotham">
            Complete Your Payment
          </h1>
          <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
            Review your booking details and choose your preferred payment method.
          </p>
        </header>

        {{-- Layout: Summary + Payment --}}
        <div class="grid gap-6 lg:gap-8 grid-cols-1 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
          {{-- Back Button --}}
          <div class="lg:col-span-2 mb-4">
            <a
              href="{{ route('booking.activities') }}"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
            >
              <i class="bi bi-arrow-left"></i>
              Back to Activities
            </a>
          </div>

          {{-- Booking Summary --}}
          <section
            aria-labelledby="booking-summary-heading"
            class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4"
          >
            <div class="flex items-center justify-between mb-4">
              <h2 id="booking-summary-heading" class="text-lg font-semibold text-gray-900">
                Booking Summary
              </h2>
              <span class="inline-flex items-center rounded-full bg-[#f4ebe9] px-3 py-1 text-xs font-semibold text-[#8f5d54]">
                <i class="fa-solid fa-ticket-simple mr-1.5"></i>
                {{ ucfirst($bookingData['type'] ?? '') }}
              </span>
            </div>

            <div class="space-y-2 text-sm text-gray-700">
              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Package</span>
                <span class="text-right">{{ $bookingData['package'] ?? '' }}</span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Price</span>
                <span class="text-right font-semibold text-[#8f5d54]">
                  ₱{{ number_format((float) ($bookingData['price'] ?? 0), 2) }}
                </span>
              </div>

              @php
                $selectedActivities = session('selected_activities', []);
                $activitiesTotal = 0;
                foreach ($selectedActivities as $activity) {
                    $activitiesTotal += $activity['price'] * ($activity['guests'] ?? 1);
                }
                $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
              @endphp

              @if (!empty($selectedActivities))
                <hr class="my-3 border-dashed border-gray-200">

                <div class="space-y-2">
                  <p class="font-medium text-gray-700">Selected Activities:</p>
                  @foreach ($selectedActivities as $activity)
                    <div class="flex justify-between gap-4 pl-4">
                      <span class="text-gray-600">{{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] ?? 1 }}</span>
                      <span class="text-right font-semibold text-[#8f5d54]">
                        ₱{{ number_format($activity['price'] * ($activity['guests'] ?? 1), 2) }}
                      </span>
                    </div>
                  @endforeach
                </div>

                <hr class="my-3 border-dashed border-gray-200">

                <div class="flex justify-between gap-4 font-semibold">
                  <span class="text-gray-900">Total</span>
                  <span class="text-right text-[#8f5d54]">
                    ₱{{ number_format($grandTotal, 2) }}
                  </span>
                </div>
              @else
                <hr class="my-3 border-dashed border-gray-200">
              @endif

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Guest Name</span>
                <span class="text-right">
                  {{ ($bookingData['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? '') }}
                </span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Email</span>
                <span class="text-right break-words">
                  {{ $bookingData['email'] ?? '' }}
                </span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Phone</span>
                <span class="text-right">
                  {{ $bookingData['phone'] ?? '' }}
                </span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Date</span>
                <span class="text-right">
                  @if (!empty($bookingData['booking_date']))
                    {{ \Carbon\Carbon::parse($bookingData['booking_date'])->format('M d, Y') }}
                  @endif
                </span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Guests</span>
                <span class="text-right">
                  {{ $bookingData['guests'] ?? '' }}
                </span>
              </div>

              @php
                $specialRequests = $bookingData['special_requests'] ?? null;
              @endphp

              @if (!empty($specialRequests))
                <div class="pt-3 mt-2 border-t border-dashed border-gray-200">
                  <p class="font-medium text-gray-500 mb-1">Special Requests</p>
                  <p class="text-sm text-gray-700 whitespace-pre-line">
                    {{ $specialRequests }}
                  </p>
                </div>
              @endif
            </div>

            <div class="mt-4 text-xs text-gray-400">
              Please review your details carefully before proceeding with payment.
            </div>
          </section>

          {{-- Payment Form --}}
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
              <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 flex items-center">
                  <i class="bi bi-person-circle mr-2 text-[#8f5d54]"></i>
                  Personal Information
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900">
                      {{ ($bookingData['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? '') }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900">
                      {{ $bookingData['email'] ?? '' }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900">
                      {{ $bookingData['phone'] ?? '' }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900 min-h-[40px]">
                      {{ $specialRequests ?: 'None' }}
                    </div>
                  </div>
                </div>
                {{-- Hidden inputs for form submission --}}
                <input type="hidden" name="first_name" value="{{ $bookingData['first_name'] ?? '' }}">
                <input type="hidden" name="last_name" value="{{ $bookingData['last_name'] ?? '' }}">
                <input type="hidden" name="email" value="{{ $bookingData['email'] ?? '' }}">
                <input type="hidden" name="phone" value="{{ $bookingData['phone'] ?? '' }}">
                <input type="hidden" name="special_requests" value="{{ $bookingData['special_requests'] ?? '' }}">
              </div>

              {{-- Payment Method --}}
              <fieldset class="space-y-3">
                <legend
                  id="payment-method-heading"
                  class="block text-sm sm:text-base font-medium text-gray-800 mb-1"
                >
                  Payment Method
                </legend>
                <p class="text-xs sm:text-sm text-gray-500 mb-2">
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
                        {{ old('payment_method', 'gcash') === 'gcash' ? 'checked' : '' }}
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
                        {{ old('payment_method') === 'paymaya' ? 'checked' : '' }}
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
                        {{ old('payment_method') === 'bank' ? 'checked' : '' }}
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
                    {{ old('terms') ? 'checked' : '' }}
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

              {{-- Pay Now Button --}}
              <div class="pt-4">
                <button
                  type="submit"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
                >
                  <i class="bi bi-lock-fill mr-2"></i>
                  <span>Complete Payment</span>
                  <span class="font-bold ml-2">
                    ₱{{ number_format($grandTotal, 2) }}
                  </span>
                </button>
                <p class="mt-3 text-xs sm:text-sm text-center text-gray-500">
                  Your payment is secured with 256-bit SSL encryption. By proceeding, you agree to our terms of service.
                </p>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </main>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers (move to CSS if possible) --}}
  <style>
    .font-gotham {
      font-family: 'Gotham', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    .font-gotham {
      font-family: 'Gotham Book', sans-serif;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>

{{-- Payment Form Component --}}

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

    {{-- Cancellation Policy --}}
    @php
      $defaultPolicy = old('cancellation_policy', $bookingData['cancellation_policy'] ?? 'flexible');
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
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

@props(['defaultPolicy' => 'flexible'])

@php
  $normalizedPolicy = in_array($defaultPolicy, ['flexible', 'nonrefundable'], true) ? $defaultPolicy : 'flexible';
@endphp

<div
  class="space-y-4"
  x-data="{
    policy: @js($normalizedPolicy),
    showPolicy: false,
    policies: {
      flexible: {
        badge: 'Recommended',
        description: 'Most flexible refund options.',
        highlight: 'border-amber-300 bg-amber-50/70',
        ring: 'ring-amber-200',
        bullets: [
          'Up to 7 days - 100% refundable',
          '3-6 days - 50% refundable',
          '0-3 days - Non-refundable'
        ]
      },
      nonrefundable: {
        badge: 'Lowest Price',
        save: 'Save 10%',
        description: 'Best price for fixed travel plans.',
        highlight: 'border-rose-200 bg-rose-50/70',
        ring: 'ring-rose-200',
        bullets: [
          'No refund after confirmation',
          'Rebooking may be available'
        ]
      }
    }
  }"
>
  <div>
    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
      <i class="bi bi-calendar-check text-2xl text-[#8f5d54]"></i>
      Select Rate Type
    </h3>
    <p class="text-sm text-gray-600 mt-1">
      Choose the rate that best matches your stay plans.
    </p>
  </div>

  @error('cancellation_policy')
    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
      {{ $message }}
    </div>
  @enderror

  <fieldset class="space-y-3">
    <legend class="sr-only">Select rate type</legend>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
      <label
        class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
        :class="policy === 'flexible' ? policies.flexible.highlight + ' ring-2 ' + policies.flexible.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
      >
        <input
          type="radio"
          id="cancellation_policy_flexible"
          name="cancellation_policy"
          value="flexible"
          class="sr-only"
          x-model="policy"
          required
        >

        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <p class="text-sm font-semibold text-gray-900">Flexible</p>
              <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                <span x-text="policies.flexible.badge"></span>
              </span>
            </div>
            <p class="text-xs text-gray-600 mt-1" x-text="policies.flexible.description"></p>
          </div>

          <span
            class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
            :class="policy === 'flexible' ? 'border-amber-400 bg-amber-100 text-amber-700' : 'border-gray-200 bg-white text-gray-400'"
            aria-hidden="true"
          >
            <i class="bi bi-check-lg"></i>
          </span>
        </div>

        <ul class="mt-4 space-y-1.5 text-xs text-gray-700">
          <template x-for="(bullet, index) in policies.flexible.bullets" :key="'flex-' + index">
            <li class="flex items-start gap-2">
              <i class="bi bi-dot text-base leading-4 text-[#8f5d54]"></i>
              <span x-text="bullet"></span>
            </li>
          </template>
        </ul>

        <button
          type="button"
          class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
          @click.stop="showPolicy = true"
        >
          <i class="bi bi-file-earmark-text"></i>
          View full booking policy
        </button>
      </label>

      <label
        class="group relative cursor-pointer rounded-2xl border-2 p-5 transition-all hover:shadow-lg"
        :class="policy === 'nonrefundable' ? policies.nonrefundable.highlight + ' ring-2 ' + policies.nonrefundable.ring : 'border-gray-200 bg-white hover:border-[#8f5d54]/30'"
      >
        <input
          type="radio"
          id="cancellation_policy_nonrefundable"
          name="cancellation_policy"
          value="nonrefundable"
          class="sr-only"
          x-model="policy"
          required
        >

        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <p class="text-sm font-semibold text-gray-900">Non-Refundable</p>
              <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                <span x-text="policies.nonrefundable.badge"></span>
              </span>
              <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                <span x-text="policies.nonrefundable.save"></span>
              </span>
            </div>
            <p class="text-xs text-gray-600 mt-1" x-text="policies.nonrefundable.description"></p>
          </div>

          <span
            class="inline-flex h-6 w-6 items-center justify-center rounded-full border transition"
            :class="policy === 'nonrefundable' ? 'border-rose-400 bg-rose-100 text-rose-700' : 'border-gray-200 bg-white text-gray-400'"
            aria-hidden="true"
          >
            <i class="bi bi-check-lg"></i>
          </span>
        </div>

        <ul class="mt-4 space-y-1.5 text-xs text-gray-700">
          <template x-for="(bullet, index) in policies.nonrefundable.bullets" :key="'nonref-' + index">
            <li class="flex items-start gap-2">
              <i class="bi bi-dot text-base leading-4 text-[#8f5d54]"></i>
              <span x-text="bullet"></span>
            </li>
          </template>
        </ul>

        <button
          type="button"
          class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-gray-900"
          @click.stop="showPolicy = true"
        >
          <i class="bi bi-file-earmark-text"></i>
          View full booking policy
        </button>
      </label>
    </div>
  </fieldset>

  <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
    <div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
      <i class="bi bi-check2-circle text-green-600"></i>
      <span>Selected Rate:</span>
      <span class="text-[#8f5d54]" x-text="policy === 'flexible' ? 'Flexible' : 'Non-Refundable'"></span>
    </div>
  </div>

  <div
    x-show="showPolicy"
    x-cloak
    x-transition
    class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 space-y-5"
  >
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs font-semibold tracking-wide text-[#8f5d54]">BOOKING POLICY</p>
        <p class="text-sm text-gray-600 mt-1">Detailed terms for both rate types.</p>
      </div>
      <button
        type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
        @click="showPolicy = false"
        aria-label="Close booking policy"
      >
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="space-y-4 text-sm text-gray-700">
      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-pencil-square text-[#8f5d54]"></i>Can I modify my booking after confirmation?</p>
        <p>Yes. Modifications such as guest details, add-ons, or activity preferences can be made up to the night before your date of stay, subject to availability.</p>
      </div>

      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-arrow-repeat text-[#8f5d54]"></i>Can I rebook my stay?</p>
        <ul class="space-y-1">
          <li>&bull; One (1) free rebooking if requested more than 6 days before stay.</li>
          <li>&bull; Less than 6 days before stay = 20% rebooking fee.</li>
          <li>&bull; Second rebooking request = 50% fee.</li>
          <li>&bull; Third rebooking is no longer allowed.</li>
          <li>&bull; New date must be within 30 days from original booking date.</li>
        </ul>
      </div>

      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-calendar-x text-[#8f5d54]"></i>What is your cancellation policy?</p>
        <ul class="space-y-1">
          <li>&bull; 7 days or more before booking = Full refund.</li>
          <li>&bull; 3&ndash;6 days before booking = 50% refund.</li>
          <li>&bull; Less than 3 days before booking = Non-refundable.</li>
        </ul>
      </div>

      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-cloud-rain-heavy text-[#8f5d54]"></i>Are there refunds for bad weather or emergencies?</p>
        <p>If Calaia Nature Park cancels the booking due to bad weather or unforeseen circumstances, the guest may choose a full refund or reschedule.</p>
      </div>

      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-person-x text-[#8f5d54]"></i>What happens for no-shows?</p>
        <p>No-shows are non-refundable.</p>
      </div>

      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-chat-dots text-[#8f5d54]"></i>How do guests request a refund?</p>
        <p>Guests must contact via Instagram or Facebook Messenger with booking details and proof of payment.</p>
      </div>

      <div class="space-y-1">
        <p class="font-semibold text-gray-900 flex items-center gap-2"><i class="bi bi-shield-x text-[#8f5d54]"></i>Can reservations be transferred?</p>
        <p>Reservations are strictly non-transferable.</p>
      </div>
    </div>
  </div>
</div>


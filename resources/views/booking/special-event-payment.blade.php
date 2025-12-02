<x-page-layout title="CalaiaPH - Special Event Payment" active-header="groups">
  {{-- Progress Indicator --}}
  <x-booking-progress
      :steps="[
          ['label' => 'Event', 'status' => 'completed'],
          ['label' => 'Details', 'status' => 'completed'],
          ['label' => 'Activities', 'status' => 'completed'],
          ['label' => 'Payment', 'status' => 'current'],
          ['label' => 'Confirmation', 'status' => 'pending']
      ]"
  />

  <main class="py-8 sm:py-12 md:py-16 min-h-[80vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
        {{-- Page Title --}}
        <header class="mb-6 sm:mb-8 text-center">
          <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
            CalaiaPH Special Event
          </p>
          <h1 class="text-2xl font-bold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">
            Complete Your Payment
          </h1>
          <p class="mt-2 text-xs text-gray-500 max-w-2xl mx-auto" style="font-family: 'Gotham Book', sans-serif;">
            Review your special event booking details and choose your preferred payment method.
          </p>
        </header>

        {{-- Layout: Summary + Payment --}}
        <div class="grid gap-6 lg:gap-8 grid-cols-1 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
          {{-- Back Button --}}
          <div class="lg:col-span-2 mb-4">
            <a
              href="{{ route('booking.specialEventActivities') }}"
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
              <h2 id="booking-summary-heading" class="text-2xl font-semibold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">
                Special Event Summary
              </h2>
              <span class="inline-flex items-center rounded-full bg-[#f4ebe9] px-3 py-1 text-xs font-semibold text-[#8f5d54]">
                <i class="fa-solid fa-calendar-star mr-1.5"></i>
                Special Event
              </span>
            </div>

            <div class="space-y-2 text-sm text-gray-700">
              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Event</span>
                <span class="text-right">{{ $bookingData['event_name'] ?? 'Special Event' }}</span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Base Price</span>
                <span class="text-right font-semibold text-[#8f5d54]">
                  ₱{{ number_format((float) ($bookingData['price'] ?? 0), 2) }}
                </span>
              </div>

              @php
                $selectedActivities = session('special_event_selected_activities', []);
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
                <span class="text-right wrap-break-word">
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
                <span class="font-medium text-gray-500">Check-in</span>
                <span class="text-right">
                  @if (!empty($bookingData['checkin']))
                    {{ \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') }}
                  @endif
                </span>
              </div>

              <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Check-out</span>
                <span class="text-right">
                  @if (!empty($bookingData['checkout']))
                    {{ \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') }}
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

            <div class="mt-4 text-xs text-gray-400" style="font-family: 'Gotham Book', sans-serif;">
              Please review your details carefully before proceeding with payment.
            </div>
          </section>

          {{-- Payment Form --}}
          <x-payment-form
              action="{{ route('booking.processPayment') }}"
              :booking-data="$bookingData"
              :grand-total="$grandTotal ?? 0"
              :errors="$errors ?? null"
          />
        </div>
      </div>
    </div>
  </main>

  {{-- Footer --}}
  <x-footer />
  </x-page-layout>

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
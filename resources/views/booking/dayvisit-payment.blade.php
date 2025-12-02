<x-page-layout title="CalaiaPH - Day Visit Payment" active-header="daytour">
  {{-- Progress Indicator --}}
  <x-booking-progress
      :steps="[
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
            CalaiaPH Day Visit
          </p>
          <h1 class="text-2xl font-bold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">
            Complete Your Payment
          </h1>
          <p class="mt-2 text-xs text-gray-500 max-w-2xl mx-auto" style="font-family: 'Gotham Book', sans-serif;">
            Review your day visit booking details and choose your preferred payment method.
          </p>
        </header>

        {{-- Layout: Summary + Payment --}}
        <div class="grid gap-6 lg:gap-8 grid-cols-1 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
          {{-- Back Button --}}
          <div class="lg:col-span-2 mb-4">
            <a
              href="{{ route('booking.dayvisitActivities') }}"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
            >
              <i class="bi bi-arrow-left"></i>
              Back to Activities
            </a>
          </div>

          {{-- Booking Summary --}}
          <x-booking-summary
              :booking-data="$bookingData"
              title="Day Visit Summary"
              badge-text="Day Visit"
              badge-icon="sun"
              session-key="dayvisit_selected_activities"
          />

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

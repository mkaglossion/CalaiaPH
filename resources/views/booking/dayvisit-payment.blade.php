<x-page-layout title="CalaiaPH - Day Visit Payment" active-header="daytour">
  @php
  $selectedActivities = session('dayvisit_selected_activities', []);
  $activitiesTotal = collect($selectedActivities)->sum(function($activity) {
      return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
  });
  $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
  @endphp
  {{-- Progress Indicator --}}
  <div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
        {{-- Step 1: Availability (completed) --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Availability</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Activities (completed) --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Payment (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            3
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Payment</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 4: Confirmation --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            4
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Confirmation</span>
        </div>
      </div>
    </div>
  </div>

  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh] bg-[#f5efe7]">
    <div class="rounded-xl max-w- mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-8 sm:py-12 lg:py-16">
      {{-- Page Title --}}
      <header class="mb-6 sm:mb-8 text-center">
        <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
          CalaiaPH Booking
        </p>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#8f5d54]" style="">
          Complete Your Payment
        </h1>
        <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
          Review your booking details and choose your preferred payment method.
        </p>
      </header>

      {{-- Layout: Payment + Summary --}}
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
        {{-- LEFT: Payment Form (2/3 width) --}}
        <div class="lg:col-span-2">
          {{-- Back Button --}}
          <div class="mb-4">
            <a
              href="{{ route('dayvisit.activities') }}"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
            >
              <i class="bi bi-arrow-left"></i>
              Back to Activities
            </a>
          </div>

          <div class="bg-white rounded-xl border border-gray-100 p-6 md:p-8 shadow-sm">
            <x-payment-form
                action="{{ route('dayvisit.initiatePayment') }}"
                :booking-data="$bookingData"
                :grand-total="$grandTotal ?? 0"
                :errors="$errors ?? null"
            />
          </div>
        </div>

        {{-- RIGHT: Booking Summary (1/3 width) --}}
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl border border-gray-100 p-6 md:p-8 shadow-sm lg:sticky lg:top-4">
            <x-dayvisit-booking-summary
                :booking="$bookingData"
                :selectedActivities="$selectedActivities"
                :showActivities="true"
                :showTrustElements="true"
            />
          </div>
        </div>
      </div>
    </div>
  </main>

</x-page-layout>

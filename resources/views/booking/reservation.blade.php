<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Booking</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-gray-50 font-sans antialiased min-h-screen">
  {{-- Header --}}
  <x-header active="reservation"/>

  {{-- Progress Indicator --}}
  <div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
        {{-- Step 1: Details (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            1
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Details</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Activities --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            2
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Payment --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            3
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Payment</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 4: Confirmation --}}
        <div class="flex flex-col items-center text-gray-300">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            4
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Confirmation</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Booking Form --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
        {{-- Title --}}
        <header class="mb-6 sm:mb-8 text-center">
          <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
            CalaiaPH Booking
          </p>
          <h1 class="text-2xl font-bold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">
            Book Your Experience
          </h1>
          <p class="mt-2 text-xs text-gray-500 max-w-2xl mx-auto px-2" style="font-family: 'Gotham Book', sans-serif;">
            Fill in your details below. You’ll review and complete your payment in the next step.
          </p>
        </header>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
          {{-- Booking Summary --}}
          <section
            aria-labelledby="selected-package-heading"
            class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4 order-2 lg:order-1"
          >
            <div class="flex items-center justify-between mb-4">
              <h2 id="selected-package-heading" class="text-2xl font-semibold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">
                Selected Package
              </h2>
              <span class="inline-flex items-center rounded-full bg-[#f4ebe9] px-2 sm:px-3 py-1 text-xs font-semibold text-[#8f5d54]">
                <i class="fa-solid fa-leaf mr-1 sm:mr-1.5"></i>
                <span class="hidden sm:inline">{{ ucfirst($type ?? '') }}</span>
                <span class="sm:hidden">{{ substr(ucfirst($type ?? ''), 0, 3) }}</span>
              </span>
            </div>

            <div class="space-y-3 text-sm text-gray-700">
              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Experience Type</span>
                <span class="text-right text-xs sm:text-sm">{{ ucfirst($type ?? '') }}</span>
              </div>

              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Package</span>
                <span class="text-right text-xs sm:text-sm">{{ $package ?? '' }}</span>
              </div>

              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Price</span>
                <span class="text-right font-semibold text-[#8f5d54] text-sm sm:text-base">
                  @if(($max_guests ?? 50) == 6)
                    ₱{{ number_format((float) ($price ?? 0), 2) }} for 6
                  @else
                    ₱{{ number_format((float) ($price ?? 0), 2) }}
                  @endif
                </span>
              </div>
            </div>

            <div class="mt-4 p-3 bg-white/50 rounded-lg">
              <p class="text-xs text-gray-500 leading-relaxed" style="font-family: 'Gotham Book', sans-serif;">
                Package details will be confirmed via email after your booking is completed.
              </p>
            </div>
          </section>

          {{-- Booking Form --}}
          <section aria-labelledby="booking-form-heading">
            <h2 id="booking-form-heading" class="sr-only">Booking Details</h2>

            <form action="{{ route('booking.store') }}" method="POST" class="space-y-8">
              @csrf

              {{-- Validation Errors --}}
              @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 mb-2">
                  <p class="font-semibold mb-1">Please fix the following issues:</p>
                  <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              {{-- Personal Information --}}
              <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
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
                      required
                      autocomplete="given-name"
                      class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      placeholder="Juan"
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
                      required
                      autocomplete="family-name"
                      class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      placeholder="Dela Cruz"
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
                      required
                      autocomplete="email"
                      class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      placeholder="you@example.com"
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
                      required
                      autocomplete="tel"
                      class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      placeholder="+63 9XX XXX XXXX"
                    >
                  </div>
                </div>
              </div>

              {{-- Booking Details --}}
              <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
                  <i class="bi bi-calendar-event mr-2 text-[#8f5d54]"></i>
                  Booking Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                  {{-- Preferred Date --}}
                  <div>
                    <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">
                      Preferred Date
                    </label>
                    <div class="relative">
                      <input
                        type="text"
                        id="booking_date"
                        name="booking_date"
                        value="{{ old('booking_date') }}"
                        required
                        readonly
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent hover:border-[#8f5d54]/70 hover:bg-gray-50"
                        placeholder="Select date"
                      >
                      <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                        <i class="bi bi-calendar3"></i>
                      </div>
                    </div>
                    <!-- Availability text -->
                    <p class="text-xs text-gray-500 mt-1" id="availability-text" style="font-family: 'Gotham Book', sans-serif;"></p>
                  </div>

                  {{-- Number of Guests --}}
                  <div>
                    <label for="guests" class="block text-sm font-medium text-gray-700 mb-1">
                      Number of Guests
                    </label>
                    <input
                      type="number"
                      id="guests"
                      name="guests"
                      value="{{ old('guests', $default_guests ?? 1) }}"
                      min="1"
                      max="{{ $max_guests ?? 50 }}"
                      required
                      @if(($max_guests ?? 50) == 6) readonly @endif
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                      placeholder="1"
                    >
                  </div>
                </div>

                {{-- Special Requests --}}
                <div class="mt-4">
                  <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-1">
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
                    Example: &quot;Vegan meal options&quot;, &quot;Birthday surprise setup&quot;, or accessibility needs.
                  </p>
                </div>
              </div>

              {{-- Hidden Fields --}}
              <input type="hidden" name="type" value="{{ $type }}">
              <input type="hidden" name="package" value="{{ $package }}">
              <input type="hidden" name="price" value="{{ $price }}">

              {{-- Submit Button --}}
              <div class="pt-4">
                <button
                  type="submit"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-sm sm:text-base font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
                >
                  <i class="bi bi-arrow-right-circle mr-2"></i>
                  <span>Proceed to Activities</span>
                </button>
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <p class="text-xs text-blue-700 text-center" style="font-family: 'Gotham Book', sans-serif;">
                    <i class="bi bi-info-circle mr-1"></i>
                    Optional activities can enhance your experience. You can skip them if preferred.
                  </p>
                </div>
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

  {{-- Flatpickr CSS --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <style>
      .flatpickr-day.flatpickr-disabled {
          opacity: 0.35;
          cursor: not-allowed;
          text-decoration: line-through;
      }

      .flatpickr-day.today {
          border-color: #8f5d54;
      }

      .flatpickr-day.selected,
      .flatpickr-day.startRange,
      .flatpickr-day.endRange {
          background: #8f5d54;
          border-color: #8f5d54;
          color: #ffffff;
      }

      .flatpickr-day.inRange {
          background: rgba(143, 93, 84, 0.12);
      }

      .flatpickr-day.weekend {
          background-color: #8f5d54;
          border-color: #8f5d54;
          color: #ffffff;
          border-radius: 9999px;
      }
  </style>

  <script type="application/json" id="capacity-data">
      {!! json_encode([
          'day-visit' => $settings->day_visit_capacity ?? 10,
          'room' => $settings->room_capacity ?? 5
      ]) !!}
  </script>
  <script type="application/json" id="inventory-data">
      {!! json_encode($inventory ?? []) !!}
  </script>
  <script type="application/json" id="booking-type-data">
      {!! json_encode($type) !!}
  </script>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          const type = JSON.parse(document.getElementById('booking-type-data').textContent);
          const capacityMap = JSON.parse(document.getElementById('capacity-data').textContent);
          const inventory = JSON.parse(document.getElementById('inventory-data').textContent);

          const fp = flatpickr('#booking_date', {
              dateFormat: 'Y-m-d',
              minDate: new Date(),
              disableMobile: true,
              mode: 'single',
              onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
                  markWeekends(dayElem);
              },
              onChange: function(selectedDates, dateStr, instance) {
                  updateAvailabilityText(dateStr);
              }
          });

          function markWeekends(dayElem) {
              if (!dayElem || !dayElem.dateObj) return;
              const date = dayElem.dateObj;
              const day = date.getDay();
              if (day === 0 || day === 6) {
                  dayElem.classList.add('weekend');
              }
          }

          function updateAvailabilityText(date) {
              const availabilityText = document.getElementById('availability-text');
              if (!date) {
                  availabilityText.textContent = '';
                  return;
              }

              const typeInventory = inventory[type] || {};
              const remaining = typeInventory[date];
              const capacity = capacityMap[type];

              if (remaining === 0) {
                  availabilityText.textContent = 'Fully booked na po for this date.';
              } else if (typeof remaining === 'number') {
                  if (capacity !== null) {
                      const used = capacity - remaining;
                      availabilityText.textContent = remaining + ' slot(s) remaining out of ' + capacity + ' (booked ' + used + ').';
                  } else {
                      availabilityText.textContent = remaining + ' slot(s) remaining for this date.';
                  }
              } else if (capacity !== null) {
                  availabilityText.textContent = 'Up to ' + capacity + ' slot(s) available per day.';
              } else {
                  availabilityText.textContent = '';
              }
          }
      });
   </script>
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
 </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Special Event Details</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-gray-50 font-sans antialiased min-h-screen">
  {{-- Header --}}
  <x-header active="groups"/>

  {{-- Progress Indicator --}}
  <div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
        {{-- Step 1: Event --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Event</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Details (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            2
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Details</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Activities --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            3
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 4: Payment --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            4
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Payment</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 5: Confirmation --}}
        <div class="flex flex-col items-center text-gray-300">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            5
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Confirmation</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Details Form --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
        {{-- Title --}}
        <header class="mb-6 sm:mb-8 text-center">
          <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
            CalaiaPH Special Event - {{ $bookingData['event_name'] ?? 'Event' }}
          </p>
          <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-[#8f5d54] font-gotham">
            Enter Your Details
          </h1>
          <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto px-2">
            Please provide your personal information and event preferences.
          </p>
        </header>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
          {{-- Back Button --}}
          <div class="lg:col-span-2 mb-4">
            <a
              href="{{ route('booking.specialEvent') }}"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
            >
              <i class="bi bi-arrow-left"></i>
              Back to Event Selection
            </a>
          </div>

          {{-- Event Summary --}}
          <section
            aria-labelledby="event-summary-heading"
            class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4 order-2 lg:order-1"
          >
            <div class="flex items-center justify-between mb-4">
              <h2 id="event-summary-heading" class="text-base sm:text-lg font-semibold text-gray-900">
                Event Summary
              </h2>
              <span class="inline-flex items-center rounded-full bg-[#f4ebe9] px-2 sm:px-3 py-1 text-xs font-semibold text-[#8f5d54]">
                <i class="fa-solid fa-calendar-star mr-1 sm:mr-1.5"></i>
                <span class="hidden sm:inline">Special Event</span>
                <span class="sm:hidden">Event</span>
              </span>
            </div>

            <div class="space-y-3 text-sm text-gray-700">
              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Selected Event</span>
                <span class="text-right text-xs sm:text-sm">{{ $bookingData['event_name'] ?? 'Special Event' }}</span>
              </div>

              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Check-in Date</span>
                <span class="text-right text-xs sm:text-sm">{{ $bookingData['checkin'] ? \Carbon\Carbon::parse($bookingData['checkin'])->format('M j, Y') : 'Not set' }}</span>
              </div>

              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Check-out Date</span>
                <span class="text-right text-xs sm:text-sm">{{ $bookingData['checkout'] ? \Carbon\Carbon::parse($bookingData['checkout'])->format('M j, Y') : 'Not set' }}</span>
              </div>

              @if($bookingData['checkin'] && $bookingData['checkout'])
              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Duration</span>
                <span class="text-right font-semibold text-[#8f5d54] text-sm sm:text-base">
                  {{ \Carbon\Carbon::parse($bookingData['checkin'])->diffInDays(\Carbon\Carbon::parse($bookingData['checkout'])) + 1 }} days
                </span>
              </div>
              @endif

              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Base Price</span>
                <span class="text-right font-semibold text-[#8f5d54] text-sm sm:text-base">
                  ₱{{ number_format((float) ($bookingData['price'] ?? 5000), 2) }}
                </span>
              </div>
            </div>

            <div class="mt-4 p-3 bg-white/50 rounded-lg">
              <p class="text-xs text-gray-500 leading-relaxed">
                Complete your details to proceed to activity selection and payment.
              </p>
            </div>
          </section>

          {{-- Details Form --}}
          <section aria-labelledby="details-form-heading">
            <h2 id="details-form-heading" class="sr-only">Enter Your Details</h2>

            <form action="{{ route('booking.storeSpecialEvent') }}" method="POST" class="space-y-6">
              @csrf

              {{-- Validation Errors --}}
              @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                  <p class="font-semibold mb-1">There were some problems with your submission:</p>
                  <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach>
                  </ul>
                </div>
              @endif

              {{-- Personal Information --}}
              <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
                  <i class="bi bi-person-circle mr-2 text-[#8f5d54]"></i>
                  Personal Information
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input
                      type="text"
                      id="first_name"
                      name="first_name"
                      value="{{ old('first_name', $bookingData['first_name'] ?? '') }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      required
                    >
                  </div>
                  <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input
                      type="text"
                      id="last_name"
                      name="last_name"
                      value="{{ old('last_name', $bookingData['last_name'] ?? '') }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      required
                    >
                  </div>
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      value="{{ old('email', $bookingData['email'] ?? '') }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      required
                    >
                  </div>
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input
                      type="tel"
                      id="phone"
                      name="phone"
                      value="{{ old('phone', $bookingData['phone'] ?? '') }}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      required
                    >
                  </div>
                </div>
              </div>

              {{-- Event Details --}}
              <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
                  <i class="bi bi-calendar-event mr-2 text-[#8f5d54]"></i>
                  Event Details
                </h3>
                <div class="space-y-4">
                  <div>
                    <label for="guests" class="block text-sm font-medium text-gray-700 mb-1">Number of Guests *</label>
                    <input
                      type="number"
                      id="guests"
                      name="guests"
                      value="{{ old('guests', $bookingData['guests'] ?? 0) }}"
                      min="0"
                      max="100"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                      required
                    >
                    <p class="text-xs text-gray-500 mt-1">Maximum 100 guests for special events</p>
                  </div>

                  <div>
                    <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
                    <textarea
                      id="special_requests"
                      name="special_requests"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors resize-vertical"
                      placeholder="Any special requirements, dietary restrictions, or additional notes..."
                    >{{ old('special_requests', $bookingData['special_requests'] ?? '') }}</textarea>
                  </div>
                </div>
              </div>

              {{-- Hidden Fields --}}
              <input type="hidden" name="event_id" value="{{ $bookingData['event_id'] ?? '' }}">
              <input type="hidden" name="checkin" value="{{ $bookingData['checkin'] ?? '' }}">
              <input type="hidden" name="checkout" value="{{ $bookingData['checkout'] ?? '' }}">

              {{-- Submit Button --}}
              <div class="pt-4">
                <button
                  type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
                >
                  <span>Continue to Activities</span>
                  <i class="bi bi-arrow-right"></i>
                </button>
                <p class="mt-3 text-xs text-center text-gray-500" style="font-family: 'Gotham Book', sans-serif;">
                  Your information will be securely stored for the booking process.
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
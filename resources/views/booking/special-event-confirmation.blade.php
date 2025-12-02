<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Special Event Booking Confirmed</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-gray-50 font-sans antialiased min-h-screen">
  {{-- Header --}}
  <x-header active="confirmation"/>

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

        {{-- Step 2: Details --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Details</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Activities --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 4: Payment --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Payment</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 5: Confirmation (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Confirmation</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Confirmation --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8 text-center">
        {{-- Success Icon --}}
        <div class="mb-6">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-check-circle-fill text-4xl text-green-600"></i>
          </div>
          <h1 class="text-2xl font-bold text-[#8f5d54] mb-2" style="font-family: 'Gotham Book', sans-serif;">
            Special Event Booking Confirmed!
          </h1>
          <p class="text-xs text-gray-600" style="font-family: 'Gotham Book', sans-serif;">
            Your special event booking has been successfully processed and confirmed.
          </p>
        </div>

        {{-- Booking Details --}}
        <div class="bg-gray-50 rounded-xl p-6 mb-6 text-left">
          <h2 class="text-2xl font-semibold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Special Event Booking Details</h2>

          {{-- Special Event Booking Details --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <span class="font-medium text-gray-500">Booking Type:</span>
              <span class="ml-2 text-gray-900">Special Event</span>
            </div>
            <div>
              <span class="font-medium text-gray-500">Event:</span>
              <span class="ml-2 text-gray-900">{{ $bookingData['event_name'] ?? 'Special Event' }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-500">Event Dates:</span>
              <span class="ml-2 text-gray-900">
                @if (!empty($bookingData['checkin']))
                  {{ \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') }}
                @endif
                @if (!empty($bookingData['checkout']))
                  - {{ \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') }}
                @endif
              </span>
            </div>
            <div>
              <span class="font-medium text-gray-500">Guests:</span>
              <span class="ml-2 text-gray-900">{{ $bookingData['guests'] ?? 1 }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-500">Name:</span>
              <span class="ml-2 text-gray-900">{{ ($bookingData['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? '') }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-500">Email:</span>
              <span class="ml-2 text-gray-900">{{ $bookingData['email'] ?? '' }}</span>
            </div>
          </div>

          @php
          $activitiesTotal = 0;
          foreach ($selectedActivities as $activity) {
              $activitiesTotal += $activity['price'] * ($activity['guests'] ?? 1);
          }
          $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
          @endphp

          @if (!empty($selectedActivities))
            <div class="mt-4 pt-4 border-t border-gray-200">
              <h3 class="text-2xl font-medium text-gray-900 mb-2" style="font-family: 'Gotham Book', sans-serif;">Selected Activities:</h3>
              <ul class="text-sm text-gray-600 space-y-1">
                @foreach ($selectedActivities as $activity)
                  <li>• {{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] ?? 1 }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="space-y-2">
              <div class="flex justify-between items-center text-sm">
                <span>Event Package:</span>
                <span>₱{{ number_format($bookingData['price'] ?? 0, 2) }}</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span>Activities Total:</span>
                <span>₱{{ number_format($activitiesTotal, 2) }}</span>
              </div>
              <div class="flex justify-between items-center text-lg font-semibold pt-2 border-t border-gray-300">
                <span>Total Amount Paid:</span>
                <span class="text-[#8f5d54]">₱{{ number_format($grandTotal, 2) }}</span>
              </div>
            </div>
          </div>
        </div>

        {{-- Next Steps --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
          <h3 class="text-2xl font-semibold text-blue-900 mb-3" style="font-family: 'Gotham Book', sans-serif;">What's Next?</h3>
          <ul class="text-xs text-blue-800 space-y-2 text-left" style="font-family: 'Gotham Book', sans-serif;">
            <li class="flex items-start">
              <i class="bi bi-envelope mr-2 mt-0.5"></i>
              <span>Check your email for a detailed confirmation and booking instructions.</span>
            </li>
            <li class="flex items-start">
              <i class="bi bi-calendar-event mr-2 mt-0.5"></i>
              <span>Save the event date in your calendar. We'll send reminders as your event date approaches.</span>
            </li>
            <li class="flex items-start">
              <i class="bi bi-telephone mr-2 mt-0.5"></i>
              <span>Contact us if you need to make changes or have any questions about your special event.</span>
            </li>
            <li class="flex items-start">
              <i class="bi bi-people mr-2 mt-0.5"></i>
              <span>Our event coordinator will contact you within 24-48 hours to discuss event details and customization options.</span>
            </li>
          </ul>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('home') }}"
             class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-6 py-3 text-sm font-semibold text-white hover:bg-[#a0675a] transition-colors">
            <i class="bi bi-house"></i>
            Return to Home
          </a>
          <button onclick="window.print()"
                  class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="bi bi-printer"></i>
            Print Confirmation
          </button>
        </div>

        {{-- Contact Info --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
          <p class="text-xs text-gray-500 mb-2" style="font-family: 'Gotham Book', sans-serif;">Need assistance with your special event?</p>
          <p class="text-xs text-gray-600" style="font-family: 'Gotham Book', sans-serif;">
            Contact us at <a href="mailto:calaia.ph@gmail.com" class="text-[#8f5d54] hover:underline">calaia.ph@gmail.com</a>
            or call <span class="font-medium">+63 905 129 4741</span>
          </p>
        </div>
      </div>
    </div>
  </main>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers --}}
  <style>
    .font-vila-zilka {
      font-family: 'Vila Zilka Serif', ui-serif, Georgia, 'Times New Roman', serif;
    }

    @media print {
      @page {
        size: legal;
        margin: 0.5in;
      }

      /* Hide header and footer */
      x-header, x-footer, header, footer {
        display: none !important;
      }

      /* Hide progress indicator */
      .bg-white.shadow-sm.border-b.border-gray-100 {
        display: none !important;
      }

      body {
        background: white !important;
      }

      main {
        page-break-inside: avoid;
        max-height: 13in; /* Legal height minus margins */
        overflow: hidden;
      }

      .bg-gray-50 {
        background: white !important;
      }

      /* Hide actions section */
      .flex.flex-col.sm\\:flex-row.gap-4.justify-center {
        display: none !important;
      }
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
    // Clear any saved selections after booking confirmation
    sessionStorage.removeItem('special_event_selections');
  </script>
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Special Event Booking</title>

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
        {{-- Step 1: Event Selection (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            1
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Event</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Details --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
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

  {{-- Event Selection --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
        {{-- Title --}}
        <header class="mb-6 sm:mb-8 text-center">
          <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
            CalaiaPH Special Events
          </p>
          <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-[#8f5d54] font-vila-zilka flex items-center justify-center gap-2 sm:gap-3">
            <i class="fa-solid fa-calendar-star text-2xl sm:text-3xl md:text-4xl text-[#8f5d54]"></i>
            <span>Choose Your Special Event</span>
          </h1>
          <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto px-2">
            Select the special event that best fits your celebration or gathering.
          </p>
        </header>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
          {{-- Back Button --}}
          <div class="lg:col-span-2 mb-4">
            <button
              type="button"
              onclick="window.location.href='{{ url('/') }}'"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
            >
              <i class="bi bi-arrow-left"></i>
              Back to Home
            </button>
          </div>

          {{-- Date Summary --}}
          <section
            aria-labelledby="selected-dates-heading"
            class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4 order-2 lg:order-1"
          >
            <div class="flex items-center justify-between mb-4">
              <h2 id="selected-dates-heading" class="text-base sm:text-lg font-semibold text-gray-900">
                Selected Dates
              </h2>
              <span class="inline-flex items-center rounded-full bg-[#f4ebe9] px-2 sm:px-3 py-1 text-xs font-semibold text-[#8f5d54]">
                <i class="fa-solid fa-calendar mr-1 sm:mr-1.5"></i>
                <span class="hidden sm:inline">Special Event</span>
                <span class="sm:hidden">Event</span>
              </span>
            </div>

            <div class="space-y-3 text-sm text-gray-700">
              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Check-in Date</span>
                <span class="text-right text-xs sm:text-sm">{{ $checkin ? \Carbon\Carbon::parse($checkin)->format('M j, Y') : 'Not selected' }}</span>
              </div>

              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Check-out Date</span>
                <span class="text-right text-xs sm:text-sm">{{ $checkout ? \Carbon\Carbon::parse($checkout)->format('M j, Y') : 'Not selected' }}</span>
              </div>

              @if($checkin && $checkout)
              <div class="flex justify-between gap-2 sm:gap-4">
                <span class="font-medium text-gray-500 text-xs sm:text-sm">Duration</span>
                <span class="text-right font-semibold text-[#8f5d54] text-sm sm:text-base">
                  {{ max(1, abs(\Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)))) + 1 }} days
                </span>
              </div>
              @endif
            </div>

            <div class="mt-4 p-3 bg-white/50 rounded-lg">
              <p class="text-xs text-gray-500 leading-relaxed">
                Event details and pricing will be confirmed after selection.
              </p>
            </div>
          </section>

          {{-- Event Selection --}}
          <section aria-labelledby="event-selection-heading">
            <h2 id="event-selection-heading" class="sr-only">Select Special Event</h2>

            @if($events->count() > 0)
              <div class="space-y-4">
                @foreach($events as $event)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-[#8f5d54] transition-colors">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-4">
                        @if(str_contains(strtolower($event->name), 'birthday'))
                          <img src="{{ asset('assets/images/carousel/groups.jpg') }}" alt="Birthday Event" class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg border border-gray-200">
                        @elseif(str_contains(strtolower($event->name), 'corporate'))
                          <img src="{{ asset('assets/images/carousel/menu1.jpg') }}" alt="Corporate Event" class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg border border-gray-200">
                        @else
                          <img src="{{ asset('assets/images/carousel/activities-1.jpg') }}" alt="Special Event" class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg border border-gray-200">
                        @endif
                        <span>{{ $event->name }}</span>
                      </h3>
                      @if($event->texts->count() > 0)
                        @foreach($event->texts as $text)
                          @if($text->section === 'description' || $text->title === 'Description')
                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($text->content, 150) }}</p>
                          @endif
                        @endforeach
                      @endif

                      {{-- Event Images --}}
                      @if($event->swiperImages->count() > 0)
                        <div class="flex gap-2 mt-3">
                          @foreach($event->swiperImages->take(3) as $image)
                            <img src="{{ $image->image_path }}" alt="{{ $image->title }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                          @endforeach
                        </div>
                      @endif
                    </div>

                    <div class="ml-4">
                      <button
                        type="button"
                        data-event-id="{{ $event->id }}"
                        data-event-name="{{ addslashes($event->name) }}"
                        onclick="selectEvent(this)"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                      >
                        Select Event
                      </button>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            @else
              <div class="text-center py-8">
                <i class="bi bi-calendar-x text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Special Events Available</h3>
                <p class="text-gray-600">Please check back later or contact us for custom event arrangements.</p>
              </div>
            @endif
          </section>
        </div>
      </div>
    </div>
  </main>

  {{-- Hidden Form for Event Selection --}}
  <form id="eventForm" action="{{ route('booking.specialEvent') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="event_id" id="selectedEventId">
  </form>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers (move to CSS if possible) --}}
  <style>
    .font-gotham {
      font-family: 'Gotham', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    .font-vila-zilka {
      font-family: 'Vila Zilka Serif', ui-serif, Georgia, 'Times New Roman', serif;
    }
  </style>

  <script>
    function selectEvent(button) {
      const eventId = button.getAttribute('data-event-id');
      const eventName = button.getAttribute('data-event-name');
      if (confirm(`Are you sure you want to select "${eventName}"?`)) {
        document.getElementById('selectedEventId').value = eventId;
        document.getElementById('eventForm').submit();
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
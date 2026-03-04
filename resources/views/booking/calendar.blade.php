{{-- calendar.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="CalaiaPH - Farm stays and outdoor adventures">
  <meta name="keywords" content="farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience">
  <meta name="author" content="CalaiaPH">
  @vite(['resources/js/app.js'])

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // SweetAlert always above modals/panels
    window.fireSwalOverModal = function (opts) {
      const openModal = document.querySelector('.modal.show,[role="dialog"][aria-modal="true"],.fixed.inset-0.z-\\[9999\\],.z-9999');
      let baseZ = 1055;
      if (openModal) {
        const z = Number(getComputedStyle(openModal).zIndex);
        if (!Number.isNaN(z)) baseZ = z;
      }
      return Swal.fire({
        ...opts,
        didOpen: () => {
          const c = Swal.getContainer();
          if (c) c.style.zIndex = String(baseZ + 50);
        }
      });
    };
  </script>

  <style>
    [x-cloak]{display:none!important;}
    .swal2-container { z-index: 200000 !important; }

    /* ===== availability tier colors ===== */
    .fp-avail-high { background:#d4edda !important; color:#155724 !important; }
    .fp-avail-low  { background:#fff3cd !important; color:#856404 !important; }
    .fp-not-avail  { background:#f8d7da !important; color:#721c24 !important; }
    .fp-invalid    { background:#e5e7eb !important; color:#6b7280 !important; }

    /* range highlights (staycation) */
    .fp-checkin    { background:#3b82f6 !important; color:#fff !important; }
    .fp-checkout   { background:#2563eb !important; color:#fff !important; }
    .fp-inrange    { background:#93c5fd !important; color:#0f172a !important; }

    /* ===== Responsive flatpickr centered ===== */
    .booking-calendar{
      display:flex;
      justify-content:center;
      width:100%;
      margin:0;
      padding:0;
    }

    .booking-calendar .flatpickr-calendar{
      position:relative !important;
      margin:0 auto !important;
      padding:0 !important;
      width:100% !important;
      max-width:100% !important;
      box-shadow:none !important;
      border:none !important;
      left:auto !important;
      top:auto !important;
      transform:none !important;
      background:transparent !important;
      font-family:inherit !important;
    }

    .booking-calendar .flatpickr-calendar.inline{
      display:flex !important;
      justify-content:center !important;
    }

    .booking-calendar .flatpickr-rContainer{
      display:flex !important;
      justify-content:center !important;
      align-items:flex-start !important;
      width:100% !important;
      max-width:100% !important;
      margin:0 !important;
    }

    .booking-calendar .flatpickr-innerContainer{
      display:flex !important;
      justify-content:center !important;
      align-items:flex-start !important;
      gap:20px !important;
      width:100% !important;
      max-width:100% !important;
      overflow:visible !important;
      margin:0 !important;
      padding:0 !important;
      flex-wrap:nowrap !important;
    }

    .booking-calendar .flatpickr-months{
      width:auto !important;
      flex:1 !important;
      max-width:420px !important;
      margin:0 !important;
    }

    .booking-calendar .flatpickr-month{ width:100% !important; margin:0 !important; }

    .booking-calendar .flatpickr-prev-month,
    .booking-calendar .flatpickr-next-month{
      fill:#1a1a1a !important;
      padding:10px !important;
    }

     /* day cells */
      .booking-calendar .flatpickr-day{
       border:1px solid #e5e7eb !important;
       margin:1px !important;
       width:14.2857% !important;
       max-width:none !important;
       min-height:75px !important;
       display:flex !important;
       flex-direction: column !important;
       align-items:center !important;
       justify-content:flex-start !important;
       padding-top:8px !important;
       font-size:24px !important; /* Increased date size */
       font-weight:700 !important;
       position:relative !important;
       text-align:center !important;
       transition:all .15s ease !important;
       border-radius:10px !important;
       overflow: visible !important; /* IMPORTANT: badge visibility */
       z-index: 1 !important;       /* IMPORTANT */
     }

    .booking-calendar .flatpickr-day:hover:not(.flatpickr-disabled):not(.fp-invalid){
      transform: translateY(-1px) !important;
      box-shadow:0 6px 18px rgba(0,0,0,.12) !important;
      z-index:10 !important;
    }

     /* ===== Quantity badge: VERY visible ===== */
      /* Make sure each day is relative so we can position overlays */
.booking-calendar .flatpickr-day{
  position: relative !important;
  overflow: visible !important;
}

/* GREEN circle background (like screenshot) */
.booking-calendar .flatpickr-day.is-available::before{
  content:"";
  position:absolute;
  inset: 6px;                 /* controls circle size */
  border-radius: 999px;
  background: rgba(34,197,94,.18); /* green tint */
  z-index: 1;
}

/* Day number should stay above the circle */
.booking-calendar .flatpickr-day.is-available{
  color: #111827 !important;
}
.booking-calendar .flatpickr-day.is-available .flatpickr-day-number,
.booking-calendar .flatpickr-day.is-available{
  z-index: 2;
}

/* Blue ring on selected day (like screenshot) */
.booking-calendar .flatpickr-day.selected,
.booking-calendar .flatpickr-day.startRange,
.booking-calendar .flatpickr-day.endRange{
  background: transparent !important;
  border: 2px solid #2563eb !important; /* blue ring */
  border-radius: 999px !important;
  color: #111827 !important;
  z-index: 3;
}

/* Optional: keep range days with subtle fill */
.booking-calendar .flatpickr-day.inRange::before{
  content:"";
  position:absolute;
  inset: 6px;
  border-radius: 999px;
  background: rgba(37,99,235,.10);
  z-index: 1;
}

/* --- Badge: top-right number (your style, tuned to screenshot) --- */
    /* make day container reference for absolute badge */
    .booking-calendar .flatpickr-day{
        position: relative !important;
    }

    /* badge position - top right, above date number */
    .booking-calendar .flatpickr-day .avail-badge{
        position: absolute !important;
        top: -4px !important;
        right: -4px !important;

        display: flex !important;
        align-items: center !important;
        justify-content: center !important;

        font-size: 11px !important;
        font-weight: 600 !important;

        width: 20px !important;
        height: 20px !important;

        border-radius: 999px !important;
        background: #16a34a !important;
        color: #fff !important;

        line-height: 1 !important;
        z-index: 10000 !important;
        pointer-events: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
    }
    /* badge colors per tier - matching legend colors */
    .booking-calendar .fp-avail-high .avail-badge{ background:#10b981 !important; color:#fff !important; border-color:#059669 !important; }
    .booking-calendar .fp-avail-low  .avail-badge{ background:#f59e0b !important; color:#fff !important; border-color:#d97706 !important; }
    .booking-calendar .fp-not-avail  .avail-badge{ background:#ef4444 !important; color:#fff !important; border-color:#dc2626 !important; }
    .booking-calendar .fp-invalid    .avail-badge{ background:#9ca3af !important; color:#fff !important; border-color:#6b7280 !important; }

    .booking-calendar .flatpickr-days{ width:100% !important; margin:0 !important; }
    .booking-calendar .dayContainer{
      width:100% !important;
      min-width:100% !important;
      max-width:100% !important;
      margin:0 !important;
      display:flex !important;
      flex-wrap:wrap !important;
      overflow: visible !important; /* IMPORTANT */
    }

    .booking-calendar .flatpickr-monthDropdown-months,
    .booking-calendar .flatpickr-current-month{
      background:transparent !important;
      color:#111827 !important;
      font-weight:800 !important;
      font-size:16px !important;
      padding:8px 0 !important;
    }

    .booking-calendar .flatpickr-weekdays{
      background:transparent !important;
      color:#6b7280 !important;
      font-weight:600 !important;
      padding:8px 0 !important;
      width:100% !important;
    }

    .booking-calendar .flatpickr-weekday{
      color:#6b7280 !important;
      font-weight:700 !important;
      width:14.2857% !important;
      display:flex !important;
      align-items:center !important;
      justify-content:center !important;
      padding:8px 0 !important;
      font-size:12px !important;
    }

     /* MOBILE: 1 month */
     @media (max-width: 767px){
       .booking-calendar .flatpickr-innerContainer{ flex-direction:column !important; gap:18px !important; width:100% !important; }
       .booking-calendar .flatpickr-months{ width:100% !important; max-width:100% !important; flex:none !important; }
       .booking-calendar .flatpickr-day{ min-height:65px !important; font-size:18px !important; border-radius:12px !important; padding-top:6px !important; }
       .booking-calendar .flatpickr-day .avail-badge{ font-size:10px !important; padding:2px 4px !important; min-width:18px !important; height:18px !important; top:-3px !important; right:-3px !important; z-index:10000 !important; }
     }

    /* ===== Legend cards ===== */
    .legend-card{
      border:1px solid #e5e7eb;
      background:#f9fafb;
      border-radius:14px;
      padding:12px 14px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      transition: all .15s ease;
    }
    .legend-card:hover{ transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0,0,0,.06); }
    .legend-left{ display:flex; align-items:center; gap:10px; }
    .legend-dot{ width:10px; height:10px; border-radius:999px; }
    .legend-pill{
      font-size:12px;
      font-weight:800;
      padding:4px 10px;
      border-radius:999px;
      border:1px solid rgba(0,0,0,.08);
      white-space:nowrap;
    }

    /* ===== Mobile sticky CTA ===== */
    .mobile-cta-wrap{
      position: fixed;
      left: 0; right: 0;
      bottom: 0;
      padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
      background: rgba(245,239,231,.92);
      backdrop-filter: blur(8px);
      border-top: 1px solid rgba(0,0,0,.08);
      z-index: 99999;
    }
  </style>

  <title>{{ $bookingType === 'daytour' ? 'Day Tour Calendar' : 'Booking Calendar' }} - Calaia Farm</title>
</head>

<body class="bg-[#f5efe7]">
  <x-header active="booking" />

  {{-- extra bottom padding so sticky CTA won't cover content on mobile --}}
  <div class="pt-6 px-4 sm:px-6 lg:px-12 pb-28 md:pb-10">
    {{-- Header --}}
    <div class="text-center mb-10">
      @if($bookingType === 'daytour')
        <h1 class="text-2xl font-bold tracking-tight">Select Your Day Tour Date</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500">Choose your preferred date for the day tour</p>
      @else
        <h1 class="text-2xl font-bold tracking-tight">Select Your Staycation to Check-in & Check-out Date</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500">Choose your check-in and check-out dates</p>
      @endif
    </div>

    {{-- Progress --}}
    <div class="mb-6">
      @php $totalSteps = $bookingType === 'daytour' ? 3 : 4; @endphp
      <x-booking.progress-indicator :stepNumber="1" :totalSteps="$totalSteps" :bookingType="$bookingType" />
    </div>

    {{-- Back + Guest mode --}}
    <div class="flex items-end justify-between mb-6">
      <a href="{{ route('home') }}"
         class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
        <i class="bi bi-arrow-left mr-2"></i> Back to Home
      </a>

      @if (session()->has('user.is_guest') && session('user.is_guest'))
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
              style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
          <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
        </span>
      @endif
    </div>

    @if($bookingType === 'daytour')
      <div class="max-w-7xl mx-auto"
           x-data="dayTourPicker({
              availability: @js($availability),
              initialDate: @js($selectedDate),
              lowThreshold: @js($totalQuantity - 1),
              totalQuantity: @js($totalQuantity),
              maxQty: @js($maxQty),
              dayTours: @js($dayToursList),
              bookingStartDate: @js($bookingStartDate),
              bookingEndDate: @js($bookingEndDate)
           })"
           x-init="init()">
    @else
      <div class="max-w-7xl mx-auto"
           x-data="dateRangePicker({
              availability: @js($availability),
              initialCheckin: @js($checkin),
              initialCheckout: @js($checkout),
              lowThreshold: @js($totalQuantity - 1),
              totalQuantity: @js($totalQuantity),
              maxQty: @js($maxQty),
              bookingStartDate: @js($bookingStartDate),
              bookingEndDate: @js($bookingEndDate)
           })"
           x-init="init()">
    @endif

      {{-- GUESTS & PROMO --}}
      <section class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

          {{-- DATE FIELDS --}}
          @if($bookingType === 'daytour')
            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="selected-date" name="selected_date" readonly
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-3 text-sm"
                       x-model="selectedDateDisplay" placeholder="Select date">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Selected Date</p></div>
            </div>
          @else
            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="checkin-date" name="checkin_date" readonly
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-3 text-sm"
                       x-model="checkinDisplay" placeholder="Select check-in date">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Check-in Date</p></div>
            </div>

            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="checkout-date" name="checkout_date" readonly
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-3 text-sm"
                       x-model="checkoutDisplay" placeholder="Select check-out date">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Check-out Date</p></div>
            </div>

            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-clock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" readonly
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-3 text-sm"
                       x-model="nightsText" placeholder="0 nights">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Number of Nights</p></div>
            </div>
          @endif

          {{-- ROOMS & GUESTS --}}
          <div class="flex flex-col relative" @click.away="closeGuestsPanel()">
            <button type="button" @click.stop="toggleGuestsPanel()"
                    class="w-full h-12 border border-gray-300 rounded-lg text-left px-4 hover:bg-gray-50 transition-colors flex items-center justify-between"
                    :aria-expanded="isGuestsPanelOpen.toString()" aria-controls="guests-panel">
              <div class="flex items-center gap-2 text-gray-700">
                <i class="bi bi-people"></i>
                <span class="text-sm" x-text="guestsSummary"></span>
              </div>
              <i class="bi" :class="isGuestsPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
            <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Add rooms and guests (Adults/Children)</p></div>

            <div id="guests-panel" x-show="isGuestsPanelOpen" x-cloak x-transition.origin.top
                 class="absolute top-full mt-2 z-9999 left-1/2 -translate-x-1/2 w-[min(600px,calc(100vw-2rem))] bg-white border border-gray-200 rounded-2xl shadow-xl p-4">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="text-base font-semibold text-gray-800">Rooms & Guests</h3>
                  <p class="text-xs text-gray-500">Add up to 4 rooms. Set adults/children per room.</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0" @click="closeGuestsPanel()">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <div class="mt-3 flex items-center justify-between border border-gray-200 rounded-xl p-3">
                <div>
                  <p class="text-sm font-medium text-gray-800">Rooms</p>
                  <p class="text-xs text-gray-500">Choose 1 to 4 rooms</p>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" class="w-9 h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40"
                          @click.stop="setRoomsCount(roomsCount - 1)">
                    <i class="bi bi-dash-lg"></i>
                  </button>
                  <span class="min-w-8 text-center font-semibold" x-text="roomsCount"></span>
                  <button type="button" class="w-9 h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40"
                          @click.stop="setRoomsCount(roomsCount + 1)">
                    <i class="bi bi-plus-lg"></i>
                  </button>
                </div>
              </div>

              <div class="mt-3 space-y-3 max-h-80 md:max-h-96 overflow-auto pr-1">
                <template x-for="(room, idx) in rooms" :key="idx">
                  <div class="border border-gray-200 rounded-xl p-3">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-semibold text-gray-800" x-text="`Room ${idx+1}`"></p>
                      <p class="text-xs text-gray-500">
                        <span x-text="room.adults"></span> adults • <span x-text="room.children"></span> children
                      </p>
                    </div>

                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3">
                        <div class="pr-2">
                          <p class="text-sm font-medium text-gray-800">Adults</p>
                          <p class="text-xs text-gray-500">Age 13+</p>
                        </div>
                        <div class="flex items-center gap-1">
                          <button type="button" class="w-9 h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40"
                                  @click.stop="decAdults(idx)" :disabled="room.adults <= 1">
                            <i class="bi bi-dash-lg text-sm"></i>
                          </button>
                          <span class="min-w-7 text-center font-semibold text-sm" x-text="room.adults"></span>
                          <button type="button" class="w-9 h-9 rounded-lg border border-gray-300 hover:border-brand/70"
                                  @click.stop="incAdults(idx)">
                            <i class="bi bi-plus-lg text-sm"></i>
                          </button>
                        </div>
                      </div>

                      <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3">
                        <div class="pr-2">
                          <p class="text-sm font-medium text-gray-800">Children</p>
                          <p class="text-xs text-gray-500">Age 0–12</p>
                        </div>
                        <div class="flex items-center gap-1">
                          <button type="button" class="w-9 h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40"
                                  @click.stop="decChildren(idx)" :disabled="room.children <= 0">
                            <i class="bi bi-dash-lg text-sm"></i>
                          </button>
                          <span class="min-w-7 text-center font-semibold text-sm" x-text="room.children"></span>
                          <button type="button" class="w-9 h-9 rounded-lg border border-gray-300 hover:border-brand/70"
                                  @click.stop="incChildren(idx)">
                            <i class="bi bi-plus-lg text-sm"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <div class="mt-4 flex gap-2 justify-end">
                <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-sm"
                        @click="resetGuests()">Reset</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-[#8f5d54] text-white hover:bg-[#7a4e48] text-sm"
                        @click="closeGuestsPanel()">Done</button>
              </div>
            </div>
          </div>

          {{-- PROMO CODE --}}
          <div class="flex flex-col relative" @click.away="closePromoPanel()">
            <button type="button" @click.stop="togglePromoPanel()"
                    class="w-full h-12 border border-gray-300 rounded-lg text-left px-4 hover:bg-gray-50 transition-colors flex items-center justify-between"
                    :aria-expanded="isPromoPanelOpen.toString()" aria-controls="promo-panel">
              <div class="flex items-center gap-2 text-gray-700">
                <i class="bi bi-ticket-perforated"></i>
                <span class="text-sm" x-text="promoCode ? 'Promo Applied' : 'Add Promo Code'"></span>
              </div>
              <i class="bi" :class="isPromoPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
            <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Enter promo code for discounts</p></div>

            <div id="promo-panel" x-show="isPromoPanelOpen" x-cloak x-transition.origin.top
                 class="absolute top-full mt-2 z-9999 left-1/2 -translate-x-1/2 w-[min(400px,calc(100vw-2rem))] bg-white border border-gray-200 rounded-2xl shadow-xl p-4">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="text-base font-semibold text-gray-800">Promo Code</h3>
                  <p class="text-xs text-gray-500">Enter your promo code to get discounts</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0" @click="closePromoPanel()">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <div class="mt-3">
                <input type="text" x-model.trim="promoCode" placeholder="Add promo code"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none text-sm">
              </div>

              <div class="mt-4 flex gap-2 justify-end">
                <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-sm"
                        @click="cancelPromo()">Cancel</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-[#8f5d54] text-white hover:bg-[#7a4e48] text-sm"
                        @click="applyPromo()">Apply</button>
              </div>
            </div>
          </div>

          {{-- CONTINUE BUTTON (desktop/tablet) --}}
          <div class="flex flex-col">
            <button
              class="hidden md:flex w-full h-12 bg-[#8f5d54] text-white py-3 px-6 rounded-lg font-semibold hover:bg-[#7a4e48] transition items-center justify-center gap-2"
              @click="continueBooking()"
            >
              @if($bookingType === 'daytour')
                Continue to Packages <i class="bi bi-arrow-right"></i>
              @else
                Continue to Rooms <i class="bi bi-arrow-right"></i>
              @endif
            </button>

            {{-- hint under button --}}
            <div class="mt-1 text-xs text-gray-500 hidden md:block">
              Select dates first to continue.
            </div>
          </div>

        </div>
      </section>

      {{-- CALENDAR WRAP --}}
      <section class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
        <div class="p-6">
          <div x-show="!bookingStartDate || !bookingEndDate" class="text-center py-12">
            <i class="bi bi-calendar-x text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Booking Not Available</h3>
            <p class="text-gray-500">No booking dates are currently configured. Please check back later or contact us for assistance.</p>
          </div>

          <div x-show="bookingStartDate && bookingEndDate" x-ref="calendar" class="booking-calendar"></div>
        </div>

        @if($bookingType === 'daytour')
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 px-6 py-4 text-white" style="background:#91b474;">
            <div class="text-sm font-semibold">Operating Hours: <span class="font-normal">8:00 AM - 5:00 PM</span></div>
            <div class="text-sm italic">* Select a date to see available packages and group slots.</div>
          </div>
        @else
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 px-6 py-4 text-white" style="background:#91b474;">
            <div class="text-sm font-semibold">
              Check-in: <span class="font-normal">01:00 PM</span> &nbsp;|&nbsp; Check-out: <span class="font-normal">11:00 PM</span>
            </div>
            <div class="text-sm italic">* Rates are subject to change without prior notice and indicative only.</div>
          </div>
        @endif
      </section>

      {{-- LEGEND --}}
      <section class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
          <i class="bi bi-info-circle mr-2 text-[#8f5d54]"></i> Legend
        </h4>

        <div x-show="!availability || Object.keys(availability).length === 0" class="text-center text-gray-500 py-4">
          Loading availability data...
        </div>

        <div x-show="availability && Object.keys(availability).length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div class="legend-card">
            <div class="legend-left">
              <span class="legend-dot" style="background:#10b981;"></span>
              <div>
                <p class="text-sm font-semibold text-gray-800">Available</p>
                <p class="text-xs text-gray-500">Good slots</p>
              </div>
            </div>
            <span class="legend-pill" style="background:#d1fae5;color:#047857;border-color:#a7f3d0;">
              <span x-text="availableThresholdText"></span>
            </span>
          </div>

          <div class="legend-card">
            <div class="legend-left">
              <span class="legend-dot" style="background:#f59e0b;"></span>
              <div>
                <p class="text-sm font-semibold text-gray-800">Limited</p>
                <p class="text-xs text-gray-500">Few slots left</p>
              </div>
            </div>
            <span class="legend-pill" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
              <span x-text="limitedThresholdText"></span>
            </span>
          </div>

          <div class="legend-card">
            <div class="legend-left">
              <span class="legend-dot" style="background:#ef4444;"></span>
              <div>
                <p class="text-sm font-semibold text-gray-800">Fully Booked</p>
                <p class="text-xs text-gray-500">No slots</p>
              </div>
            </div>
            <span class="legend-pill" style="background:#fee2e2;color:#b91c1c;border-color:#fecaca;">0</span>
          </div>

          @if($bookingType !== 'daytour')
            <div class="legend-card">
              <div class="legend-left">
                <span class="legend-dot" style="background:#3b82f6;"></span>
                <div>
                  <p class="text-sm font-semibold text-gray-800">Check-in</p>
                  <p class="text-xs text-gray-500">Start</p>
                </div>
              </div>
              <span class="legend-pill" style="background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe;">Start</span>
            </div>

            <div class="legend-card">
              <div class="legend-left">
                <span class="legend-dot" style="background:#2563eb;"></span>
                <div>
                  <p class="text-sm font-semibold text-gray-800">Check-out</p>
                  <p class="text-xs text-gray-500">End</p>
                </div>
              </div>
              <span class="legend-pill" style="background:#dbeafe;color:#1e40af;border-color:#bfdbfe;">End</span>
            </div>

            <div class="legend-card">
              <div class="legend-left">
                <span class="legend-dot" style="background:#93c5fd;"></span>
                <div>
                  <p class="text-sm font-semibold text-gray-800">Stay Range</p>
                  <p class="text-xs text-gray-500">Between</p>
                </div>
              </div>
              <span class="legend-pill" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">Range</span>
            </div>
          @endif

          <div class="legend-card">
            <div class="legend-left">
              <span class="legend-dot" style="background:#9ca3af;"></span>
              <div>
                <p class="text-sm font-semibold text-gray-800">Invalid</p>
                <p class="text-xs text-gray-500">No inventory / disabled</p>
              </div>
            </div>
            <span class="legend-pill" style="background:#e5e7eb;color:#6b7280;border-color:#d1d5db;">—</span>
          </div>
        </div>
      </section>

      {{-- MOBILE STICKY CTA (always visible) --}}
      <div class="mobile-cta-wrap md:hidden">
        <button
          class="w-full h-12 bg-[#8f5d54] text-white rounded-xl font-extrabold shadow-lg hover:bg-[#7a4e48] transition flex items-center justify-center gap-2"
          @click="continueBooking()"
        >
          @if($bookingType === 'daytour')
            Continue to Packages <i class="bi bi-arrow-right"></i>
          @else
            Continue to Rooms <i class="bi bi-arrow-right"></i>
          @endif
        </button>
      </div>

    </div> {{-- end x-data wrapper --}}
  </div> {{-- end page wrapper --}}

  {{-- ======================= SCRIPTS ======================= --}}

  @if($bookingType === 'daytour')
  <script>
    function dayTourPicker({ availability, initialDate, lowThreshold, totalQuantity, maxQty, dayTours, bookingStartDate, bookingEndDate }) {
      return {
        availability: availability || {},
        selectedDate: initialDate || null,
        calendarInstance: null,
        dayTours: dayTours || [],
        bookingStartDate: bookingStartDate || null,
        bookingEndDate: bookingEndDate || null,

        // Guests/Rooms
        isGuestsPanelOpen: false,
        toggleGuestsPanel() { this.isGuestsPanelOpen = !this.isGuestsPanelOpen; },
        closeGuestsPanel() { this.isGuestsPanelOpen = false; },

        roomsCount: 1,
        rooms: [{ adults: 2, children: 0 }],

        get totalAdults() { return this.rooms.reduce((sum, r) => sum + Number(r.adults || 0), 0); },
        get totalChildren() { return this.rooms.reduce((sum, r) => sum + Number(r.children || 0), 0); },
        get guestsSummary() {
          const rooms = this.roomsCount;
          const a = this.totalAdults;
          const c = this.totalChildren;
          const roomLabel = rooms === 1 ? 'Room' : 'Rooms';
          const adultLabel = a === 1 ? 'Adult' : 'Adults';
          const childLabel = c === 1 ? 'Child' : 'Children';
          return `${rooms} ${roomLabel}, ${a} ${adultLabel}${c > 0 ? `, ${c} ${childLabel}` : ''}`;
        },

        setRoomsCount(val) {
          const next = Number(val || 1);
          if (next > 4) {
            fireSwalOverModal({ title:'Maximum Rooms Reached', text:'You can only add up to 4 rooms.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          if (next < 1) {
            fireSwalOverModal({ title:'Minimum Rooms Required', text:'You need at least 1 room.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          this.roomsCount = next;
          if (this.rooms.length < next) { while (this.rooms.length < next) this.rooms.push({ adults: 2, children: 0 }); }
          else if (this.rooms.length > next) { this.rooms = this.rooms.slice(0, next); }
        },

        incAdults(idx) { this.rooms[idx].adults = Number(this.rooms[idx].adults || 0) + 1; },
        decAdults(idx) { this.rooms[idx].adults = Math.max(1, Number(this.rooms[idx].adults || 1) - 1); },
        incChildren(idx) { this.rooms[idx].children = Number(this.rooms[idx].children || 0) + 1; },
        decChildren(idx) { this.rooms[idx].children = Math.max(0, Number(this.rooms[idx].children || 0) - 1); },

        resetGuests() { this.roomsCount = 1; this.rooms = [{ adults: 2, children: 0 }]; },

        // Promo code
        isPromoPanelOpen: false,
        promoCode: '',
        togglePromoPanel() { this.isPromoPanelOpen = !this.isPromoPanelOpen; },
        closePromoPanel() { this.isPromoPanelOpen = false; },
        cancelPromo() { this.promoCode = ''; this.isPromoPanelOpen = false; },
        applyPromo() { this.isPromoPanelOpen = false; },

        legendMaxQty: maxQty || 0,
        legendLowThreshold: 2,
        totalQuantity: totalQuantity || 0,

        _months: 2,
        _resizeTimer: null,

        init() {
          if (initialDate) this.selectedDate = initialDate;
          this.calculateSmartThresholds();
          this._months = this.getResponsiveMonths();
          this.$nextTick(() => this.initCalendar());

          window.addEventListener('resize', () => {
            clearTimeout(this._resizeTimer);
            this._resizeTimer = setTimeout(() => {
              const next = this.getResponsiveMonths();
              if (next !== this._months) { this._months = next; this.initCalendar(true); }
            }, 200);
          });
        },

        getResponsiveMonths() { return window.matchMedia('(min-width: 768px)').matches ? 2 : 1; },
        get selectedDateDisplay() { return this.selectedDate ? this.formatDisplay(this.selectedDate) : ''; },

        availableThresholdText: 'Loading...',
        limitedThresholdText: 'Loading...',

        calculateSmartThresholds() {
          this.legendLowThreshold = Math.max(1, Math.floor(this.totalQuantity * 0.3));
          this.availableThresholdText = (this.legendLowThreshold + 1) + '+ slots';
          this.limitedThresholdText = '1-' + this.legendLowThreshold + ' slots';
        },

        // safer qty getter (handles different field names)
        getQty(details){
          if (!details) return null;
          const raw =
            details.min_quantity ??
            details.quantity ??
            details.slots ??
            details.available ??
            details.remaining ??
            details.qty ??
            null;
          const n = Number(raw);
          return Number.isFinite(n) ? n : null;
        },

        initCalendar(keepSelected = false) {
          if (!this.bookingStartDate || !this.bookingEndDate) return;

          const selected = keepSelected ? this.selectedDate : null;

          if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }

          this.calculateSmartThresholds();

          const disabledDates = [];
          for (const [dateString, details] of Object.entries(this.availability)) {
            if (details && details.is_available === false) disabledDates.push(dateString);
          }

          if (!this.$refs.calendar) return;

          const today = new Date();
          today.setHours(0,0,0,0);

          const minDate = this.parseDate(this.bookingStartDate) || today;
          const maxDate = this.parseDate(this.bookingEndDate);
          const months = this._months;

          this.calendarInstance = flatpickr(this.$refs.calendar, {
            mode: 'single',
            dateFormat: 'Y-m-d',
            inline: true,
            disableMobile: true,
            monthSelectorType: 'static',
            showMonths: months,
            minDate,
            maxDate,
            defaultDate: selected || this.selectedDate,

            disable: [
              (date) => {
                const checkDate = new Date(date);
                checkDate.setHours(0,0,0,0);
                if (checkDate < today) return true;

                const dateStr = this.formatDateForCompare(date);
                const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateStr);
                if (!hasInventory) return true;

                return disabledDates.includes(dateStr);
              }
            ],

            onDayCreate: (_, __, fp, dayElem) => {
              const dateString = fp.formatDate(dayElem.dateObj, 'Y-m-d');

              // Remove old badge if exists
              const existingBadge = dayElem.querySelector('.avail-badge');
              if (existingBadge) {
                existingBadge.remove();
              }

              // Ensure dayElem has proper positioning
              dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');
              dayElem.style.position = 'relative';
              dayElem.style.overflow = 'visible';

              const checkDate = new Date(dayElem.dateObj);
              checkDate.setHours(0,0,0,0);
              const isPast = checkDate < today;

              const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateString);
              const details = hasInventory ? this.availability[dateString] : null;

               // ALWAYS create badge - ensure it's visible
              const badge = document.createElement('span');
              badge.className = 'avail-badge';
              badge.style.cssText = 'position:absolute !important; top:-4px !important; right:-4px !important; display:flex !important; align-items:center !important; justify-content:center !important; font-size:11px !important; font-weight:600 !important; padding:0 !important; border-radius:999px !important; line-height:1 !important; width:20px !important; height:20px !important; text-align:center !important; z-index:10000 !important; pointer-events:none !important; visibility:visible !important; opacity:1 !important; box-shadow:0 2px 4px rgba(0,0,0,0.2) !important;';

              // invalid / no inventory / disabled / past
              if (!hasInventory || disabledDates.includes(dateString) || isPast) {
                dayElem.classList.add('fp-invalid');
                badge.textContent = '-';
                dayElem.appendChild(badge);
                return;
              }

              const qty = this.getQty(details);
              const safeQty = (qty === null ? 0 : qty);

              if (safeQty === 0) dayElem.classList.add('fp-not-avail');
              else if (safeQty <= this.legendLowThreshold) dayElem.classList.add('fp-avail-low');
              else dayElem.classList.add('fp-avail-high');

              badge.textContent = String(safeQty);
              
              // Force append badge and ensure it's the last child
              dayElem.appendChild(badge);
              badge.style.display = 'flex';

              if (this.selectedDate === dateString) {
                dayElem.style.outline = '3px solid #2563eb';
                dayElem.style.outlineOffset = '-3px';
              } else {
                dayElem.style.outline = 'none';
              }
            },

            onChange: (selectedDates, _, fp) => {
              this.selectedDate = (selectedDates.length === 1) ? fp.formatDate(selectedDates[0], 'Y-m-d') : null;
              this.initCalendar(true);
            }
          });
        },

        continueBooking() {
          if (!this.selectedDate) {
            fireSwalOverModal({ title:'Please Select a Date', text:'You need to select a date for your day tour.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          const url = new URL(window.location.origin + '/booking/dayvisit/availability');
          url.searchParams.set('date', this.selectedDate);
          window.location.href = url.toString();
        },

        parseDate(dateStr) {
          const [y,m,d] = (dateStr || '').split('-').map(Number);
          if (!y || !m || !d) return null;
          return new Date(y, m - 1, d);
        },

        formatDisplay(dateStr) {
          const d = this.parseDate(dateStr);
          if (!d) return '';
          return d.toLocaleDateString('en-US', { month:'short', day:'2-digit', year:'numeric' });
        },

        formatDateForCompare(date) {
          const y = date.getFullYear();
          const m = String(date.getMonth() + 1).padStart(2, '0');
          const d = String(date.getDate()).padStart(2, '0');
          return `${y}-${m}-${d}`;
        }
      }
    }
  </script>
  @else
  <script>
    function dateRangePicker({ availability, initialCheckin, initialCheckout, lowThreshold, totalQuantity, maxQty, bookingStartDate, bookingEndDate }) {
      return {
        availability: availability || {},
        checkin: null,
        checkout: null,
        calendarInstance: null,
        bookingStartDate: bookingStartDate || null,
        bookingEndDate: bookingEndDate || null,

        // Guests/Rooms
        isGuestsPanelOpen: false,
        toggleGuestsPanel() { this.isGuestsPanelOpen = !this.isGuestsPanelOpen; },
        closeGuestsPanel() { this.isGuestsPanelOpen = false; },

        roomsCount: 1,
        rooms: [{ adults: 2, children: 0 }],

        get totalAdults() { return this.rooms.reduce((sum, r) => sum + Number(r.adults || 0), 0); },
        get totalChildren() { return this.rooms.reduce((sum, r) => sum + Number(r.children || 0), 0); },
        get guestsSummary() {
          const rooms = this.roomsCount;
          const a = this.totalAdults;
          const c = this.totalChildren;
          const roomLabel = rooms === 1 ? 'Room' : 'Rooms';
          const adultLabel = a === 1 ? 'Adult' : 'Adults';
          const childLabel = c === 1 ? 'Child' : 'Children';
          return `${rooms} ${roomLabel}, ${a} ${adultLabel}${c > 0 ? `, ${c} ${childLabel}` : ''}`;
        },

        setRoomsCount(val) {
          const next = Number(val || 1);
          if (next > 4) {
            fireSwalOverModal({ title:'Maximum Rooms Reached', text:'You can only add up to 4 rooms.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          if (next < 1) {
            fireSwalOverModal({ title:'Minimum Rooms Required', text:'You need at least 1 room.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          this.roomsCount = next;
          if (this.rooms.length < next) { while (this.rooms.length < next) this.rooms.push({ adults: 2, children: 0 }); }
          else if (this.rooms.length > next) { this.rooms = this.rooms.slice(0, next); }
        },

        incAdults(idx) { this.rooms[idx].adults = Number(this.rooms[idx].adults || 0) + 1; },
        decAdults(idx) { this.rooms[idx].adults = Math.max(1, Number(this.rooms[idx].adults || 1) - 1); },
        incChildren(idx) { this.rooms[idx].children = Number(this.rooms[idx].children || 0) + 1; },
        decChildren(idx) { this.rooms[idx].children = Math.max(0, Number(this.rooms[idx].children || 0) - 1); },

        resetGuests() { this.roomsCount = 1; this.rooms = [{ adults: 2, children: 0 }]; },

        // Promo code
        isPromoPanelOpen: false,
        promoCode: '',
        togglePromoPanel() { this.isPromoPanelOpen = !this.isPromoPanelOpen; },
        closePromoPanel() { this.isPromoPanelOpen = false; },
        cancelPromo() { this.promoCode = ''; this.isPromoPanelOpen = false; },
        applyPromo() { this.isPromoPanelOpen = false; },

        legendMaxQty: maxQty || 0,
        legendLowThreshold: 2,
        totalQuantity: totalQuantity || 0,

        _months: 2,
        _resizeTimer: null,

        init() {
          if (initialCheckin) this.checkin = initialCheckin;
          if (initialCheckout) this.checkout = initialCheckout;

          this.calculateSmartThresholds();
          this._months = this.getResponsiveMonths();
          this.$nextTick(() => this.initCalendar());

          window.addEventListener('resize', () => {
            clearTimeout(this._resizeTimer);
            this._resizeTimer = setTimeout(() => {
              const next = this.getResponsiveMonths();
              if (next !== this._months) { this._months = next; this.initCalendar(true); }
            }, 200);
          });
        },

        getResponsiveMonths() { return window.matchMedia('(min-width: 768px)').matches ? 2 : 1; },

        get checkinDisplay() { return this.checkin ? this.formatDisplay(this.checkin) : ''; },
        get checkoutDisplay() { return this.checkout ? this.formatDisplay(this.checkout) : ''; },

        get nights() {
          if (!this.checkin || !this.checkout) return 0;
          const a = this.parseDate(this.checkin);
          const b = this.parseDate(this.checkout);
          const diff = Math.round((b - a) / (1000*60*60*24));
          return diff > 0 ? diff : 0;
        },
        get nightsText() { return this.nights ? `${this.nights} night(s)` : '—'; },

        availableThresholdText: 'Loading...',
        limitedThresholdText: 'Loading...',

        calculateSmartThresholds() {
          this.legendLowThreshold = Math.max(1, Math.floor(this.totalQuantity * 0.3));
          this.availableThresholdText = (this.legendLowThreshold + 1) + '+ slots';
          this.limitedThresholdText = '1-' + this.legendLowThreshold + ' slots';
        },

        // safer qty getter
        getQty(details){
          if (!details) return null;
          const raw =
            details.min_quantity ??
            details.quantity ??
            details.slots ??
            details.available ??
            details.remaining ??
            details.qty ??
            null;
          const n = Number(raw);
          return Number.isFinite(n) ? n : null;
        },

        initCalendar(keepSelected = false) {
          if (!this.bookingStartDate || !this.bookingEndDate) return;

          const selected = keepSelected ? [this.checkin, this.checkout] : null;

          if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }

          this.calculateSmartThresholds();

          const disabledDates = [];
          for (const [dateString, details] of Object.entries(this.availability)) {
            if (details && details.is_available === false) disabledDates.push(dateString);
          }

          if (!this.$refs.calendar) return;

          const today = new Date();
          today.setHours(0,0,0,0);

          const minDate = this.parseDate(this.bookingStartDate) || today;
          const maxDate = this.parseDate(this.bookingEndDate);
          const months = this._months;

          this.calendarInstance = flatpickr(this.$refs.calendar, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            inline: true,
            disableMobile: true,
            monthSelectorType: 'static',
            showMonths: months,
            minDate,
            maxDate,

            defaultDate: selected ? selected.filter(Boolean) : [this.checkin, this.checkout].filter(Boolean),

            disable: [
              (date) => {
                const checkDate = new Date(date);
                checkDate.setHours(0,0,0,0);
                if (checkDate < today) return true;

                const dateStr = this.formatDateForCompare(date);
                const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateStr);
                if (!hasInventory) return true;

                return disabledDates.includes(dateStr);
              }
            ],

            onDayCreate: (_, __, fp, dayElem) => {
              const dateString = fp.formatDate(dayElem.dateObj, 'Y-m-d');

              // Remove old badge if exists
              const existingBadge = dayElem.querySelector('.avail-badge');
              if (existingBadge) {
                existingBadge.remove();
              }

              // Ensure dayElem has proper positioning
              dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');
              dayElem.style.position = 'relative';
              dayElem.style.overflow = 'visible';

              const checkDate = new Date(dayElem.dateObj);
              checkDate.setHours(0,0,0,0);
              const isPast = checkDate < today;

              const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateString);
              const details = hasInventory ? this.availability[dateString] : null;

               // ALWAYS create badge - ensure it's visible
              const badge = document.createElement('span');
              badge.className = 'avail-badge';
              badge.style.cssText = 'position:absolute !important; top:-4px !important; right:-4px !important; display:flex !important; align-items:center !important; justify-content:center !important; font-size:11px !important; font-weight:600 !important; padding:0 !important; border-radius:999px !important; line-height:1 !important; width:20px !important; height:20px !important; text-align:center !important; z-index:10000 !important; pointer-events:none !important; visibility:visible !important; opacity:1 !important; box-shadow:0 2px 4px rgba(0,0,0,0.2) !important;';

              // invalid / no inventory / disabled / past
              if (!hasInventory || disabledDates.includes(dateString) || isPast) {
                dayElem.classList.add('fp-invalid');
                badge.textContent = '-';
                dayElem.appendChild(badge);
                return;
              }

              const qty = this.getQty(details);
              const safeQty = (qty === null ? 0 : qty);

              if (safeQty === 0) dayElem.classList.add('fp-not-avail');
              else if (safeQty <= this.legendLowThreshold) dayElem.classList.add('fp-avail-low');
              else dayElem.classList.add('fp-avail-high');

              badge.textContent = String(safeQty);
              
              // Force append badge and ensure it's the last child
              dayElem.appendChild(badge);
              badge.style.display = 'flex';

              // Range outline overlay (keep original behavior)
              const hasRange = !!this.checkin && !!this.checkout;
              if (hasRange) {
                if (dateString === this.checkin || dateString === this.checkout) {
                  dayElem.style.outline = '3px solid #2563eb';
                  dayElem.style.outlineOffset = '-3px';
                } else if (dateString > this.checkin && dateString < this.checkout) {
                  dayElem.style.outline = '3px solid #93c5fd';
                  dayElem.style.outlineOffset = '-3px';
                } else {
                  dayElem.style.outline = 'none';
                }
              } else {
                dayElem.style.outline = 'none';
              }
            },

            onChange: (selectedDates, _, fp) => {
              if (selectedDates.length === 2) {
                this.checkin = fp.formatDate(selectedDates[0], 'Y-m-d');
                this.checkout = fp.formatDate(selectedDates[1], 'Y-m-d');
              } else if (selectedDates.length === 1) {
                this.checkin = fp.formatDate(selectedDates[0], 'Y-m-d');
                this.checkout = null;
              } else {
                this.checkin = null;
                this.checkout = null;
              }
              this.initCalendar(true);
            }
          });
        },

        continueBooking() {
          if (!this.checkin || !this.checkout) {
            fireSwalOverModal({ title:'Please Select Dates', text:'You need to select both check-in and check-out dates.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          const url = new URL(window.location.origin + '/booking/staycation/availability');
          url.searchParams.set('checkin', this.checkin);
          url.searchParams.set('checkout', this.checkout);
          window.location.href = url.toString();
        },

        parseDate(dateStr) {
          const [y,m,d] = (dateStr || '').split('-').map(Number);
          if (!y || !m || !d) return null;
          return new Date(y, m - 1, d);
        },

        formatDisplay(dateStr) {
          const d = this.parseDate(dateStr);
          if (!d) return '';
          return d.toLocaleDateString('en-US', { month:'short', day:'2-digit', year:'numeric' });
        },

        formatDateForCompare(date) {
          const y = date.getFullYear();
          const m = String(date.getMonth() + 1).padStart(2, '0');
          const d = String(date.getDate()).padStart(2, '0');
          return `${y}-${m}-${d}`;
        }
      }
    }
  </script>
  @endif

  <x-floating-buttons />
  <x-footer />
</body>
</html>
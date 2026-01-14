 <!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="CalaiaPH - Farm stays and outdoor adventures">
    <meta name="keywords" content="farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        [x-cloak]{display:none!important;}

        /* base rounding */
        .flatpickr-day { border-radius: 6px; }

        /* availability tiers */
        .fp-avail-high { background:#d4edda !important; color:#155724 !important; }
        .fp-avail-low  { background:#fff3cd !important; color:#856404 !important; }
        .fp-not-avail  { background:#f8d7da !important; color:#721c24 !important; }
        .fp-invalid    { background:#d3d3d3 !important; color:#6c757d !important; }

        /* range highlights (optional) */
        .fp-checkin    { background:#3b82f6 !important; color:#fff !important; }
        .fp-checkout   { background:#2563eb !important; color:#fff !important; }
        .fp-inrange    { background:#93c5fd !important; color:#0f172a !important; }

        /* Swiper styles */
        .swiper {
            height: 384px; /* h-96 = 24rem = 384px */
        }
        .swiper-button-next, .swiper-button-prev {
            color: white;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            width: 40px !important;
            height: 40px !important;
            margin-top: -20px !important;
        }
        .swiper-button-next::after, .swiper-button-prev::after {
            font-size: 16px !important;
        }
        .swiper-pagination {
            bottom: 10px !important;
        }
        .swiper-pagination-bullet-active {
            background: #8f5d54 !important;
        }

        /* ===== RESPONSIVE FLATPICKR (CENTERED) ===== */
        .booking-calendar{
          display:flex;
          justify-content:center;
          width:100%;
        }

        /* make inline calendar not fixed width */
        .booking-calendar .flatpickr-calendar.inline{
          margin: 0 auto !important;
          width: auto !important;
          max-width: 100% !important;
          box-shadow: none !important;
          border: 0 !important;
        }

        /* allow 2-month layout to shrink properly */
        .booking-calendar .flatpickr-rContainer,
        .booking-calendar .flatpickr-days,
        .booking-calendar .dayContainer{
          width: 100% !important;
          min-width: 0 !important;
        }

        /* prevent overflow on mobile */
        .booking-calendar .flatpickr-innerContainer{
          max-width: 100% !important;
          overflow-x: auto;
          -webkit-overflow-scrolling: touch;
        }

        /* nicer spacing between 2 months */
        .booking-calendar .flatpickr-innerContainer{
          gap: 16px !important;
          justify-content: center !important;
        }

        /* day cell grid look & consistent sizing */
        .booking-calendar .flatpickr-day{
          border: 1px solid #e5e7eb !important;
          margin: 0 !important;
          max-width: none !important;
        }

        /* MOBILE: force 1 month and prevent squeezing */
        @media (max-width: 767px){
          .booking-calendar .flatpickr-calendar.inline{
            width: 100% !important;
          }
          .booking-calendar .flatpickr-innerContainer{
            overflow-x: hidden; /* no horizontal scroll in 1 month mode */
          }
        }

        /* ===== SCREENSHOT STYLING ===== */
        /* Month headers green background */
        .booking-calendar .flatpickr-monthDropdown-months,
        .booking-calendar .flatpickr-current-month{
          background: #91b474 !important;
          color: white !important;
        }

        /* Weekdays row green with white text */
        .booking-calendar .flatpickr-weekdays{
          background: #91b474 !important;
        }
        .booking-calendar .flatpickr-weekday{
          color: white !important;
          font-weight: bold !important;
        }

        /* Continue button align right on desktop */
        @media (min-width: 768px){
          .booking-calendar + .booking-calendar + .booking-calendar + .booking-calendar + section + section + div{
            text-align: right !important;
          }
        }
    </style>

    <title>Booking Calendar - Calaia Farm</title>
  </head>

  <body class="bg-[#f5efe7]">
    <x-header active="booking" />

    <div class="pt-6 px-4 sm:px-6 lg:px-12">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-2xl font-bold tracking-tight" style="font-family:'Gotham Book', sans-serif;">Select Your Staycation to Check-in & Check-out Date</h1>
            <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
            <p class="text-xs text-gray-500" style="font-family:'Gotham Book', sans-serif;">
                Choose your check-in and check-out dates
            </p>
        </div>

        {{-- Progress --}}
        <div class="mb-6">
            <x-booking.progress-indicator :stepNumber="1" :totalSteps="4" :bookingType="'staycation'" />
        </div>

        {{-- Back + Guest mode --}}
        <div class="flex items-end justify-between mb-6">
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <i class="bi bi-arrow-left mr-2"></i>
                Back to Home
            </a>

            @if (session()->has('user.is_guest') && session('user.is_guest'))
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                      style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                    <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                </span>
            @endif
        </div>

        <div
          class="max-w-4xl mx-auto pb-10"
          x-data="dateRangePicker({
            availability: @js($availability),
            initialCheckin: @js($checkin),
            initialCheckout: @js($checkout),
            lowThreshold: @js($totalQuantity - 1),
            totalQuantity: @js($totalQuantity)
          })"
          x-init="init()"
        >
            {{-- TOP INPUTS --}}
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-6">
                <div>
                  <label class="block text-sm font-bold text-gray-900 mb-1">Check-in</label>
                  <div class="relative">
                    <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input
                      type="text"
                      readonly
                      class="w-full border border-gray-300 bg-gray-100 rounded-none pl-10 pr-3 py-2 text-sm"
                      x-model="checkinDisplay"
                      placeholder="Select date"
                    >
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-bold text-gray-900 mb-1">Check-out</label>
                  <div class="relative">
                    <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input
                      type="text"
                      readonly
                      class="w-full border border-gray-300 bg-gray-100 rounded-none pl-10 pr-3 py-2 text-sm"
                      x-model="checkoutDisplay"
                      placeholder="Select date"
                    >
                  </div>
                </div>

                <div class="md:text-left">
                  <div class="text-sm font-bold text-gray-900">Number of night(s)</div>
                  <div class="text-sm text-gray-800" x-text="nightsText"></div>
                </div>
            </section>

            {{-- CALENDAR WRAP --}}
            <section class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
                <div class="p-6">
                  <!-- flatpickr mounts here -->
                  <div x-ref="calendar" class="booking-calendar"></div>
                </div>

                <!-- GREEN BAR (check-in/out time + note) -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 px-6 py-4 text-white"
                     style="background:#91b474;">
                  <div class="text-sm font-semibold">
                    Check-in: <span class="font-normal">02:00 PM</span> &nbsp;|&nbsp;
                    Check-out: <span class="font-normal">12:00 PM</span>
                  </div>
                  <div class="text-sm italic">
                    * Rates are subject to change without prior notice and indicative only.
                  </div>
                </div>
            </section>

            {{-- LEGEND --}}
            <section class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="bi bi-info-circle mr-2 text-[#8f5d54]"></i> Legend
                </h4>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-avail-high"></span>
                      <span class="text-sm" x-text="`Available (up to ${totalQuantity} slots)`"></span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-avail-low"></span>
                      <span class="text-sm" x-text="`Limited (1–${legendLowThreshold} slots)`"></span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-not-avail"></span>
                      <span class="text-sm">Not Available (0)</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-checkin"></span>
                      <span class="text-sm">Check-in Date</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-checkout"></span>
                      <span class="text-sm">Check-out Date</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-inrange"></span>
                      <span class="text-sm">Period of Stay</span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-invalid"></span>
                      <span class="text-sm">Invalid Date</span>
                    </div>
                </div>
            </section>

            {{-- CONTINUE BUTTON --}}
            <div class="flex justify-center">
                <button
                  class="bg-[#8f5d54] text-white py-3 px-8 rounded-lg font-semibold hover:bg-[#7a4e48] transition disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="!checkin || !checkout"
                  @click="continueBooking()"
                >
                  Continue to Rooms <i class="bi bi-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>

  <script>
    function dateRangePicker({ availability, initialCheckin, initialCheckout, lowThreshold, totalQuantity }) {
      return {
        availability: availability || {},
        checkin: null,
        checkout: null,
        calendarInstance: null,

        legendMaxQty: 0,
        legendLowThreshold: lowThreshold || 2,
        totalQuantity: totalQuantity || 0,

        _months: 2,
        _resizeTimer: null,

        init() {
          if (initialCheckin) this.checkin = initialCheckin;
          if (initialCheckout) this.checkout = initialCheckout;

          this._months = this.getResponsiveMonths();
          this.$nextTick(() => this.initCalendar());

          // ✅ re-init on resize (responsive switch 1<->2 months)
          window.addEventListener('resize', () => {
            clearTimeout(this._resizeTimer);
            this._resizeTimer = setTimeout(() => {
              const next = this.getResponsiveMonths();
              if (next !== this._months) {
                this._months = next;
                this.initCalendar(true);
              }
            }, 200);
          });
        },

        getResponsiveMonths() {
          return window.matchMedia('(min-width: 768px)').matches ? 2 : 1;
        },

        get checkinDisplay() {
          return this.checkin ? this.formatDisplay(this.checkin) : '';
        },
        get checkoutDisplay() {
          return this.checkout ? this.formatDisplay(this.checkout) : '';
        },
        get nights() {
          if (!this.checkin || !this.checkout) return 0;
          const a = this.parseDate(this.checkin);
          const b = this.parseDate(this.checkout);
          const diff = Math.round((b - a) / (1000*60*60*24));
          return diff > 0 ? diff : 0;
        },
        get nightsText() {
          return this.nights ? `${this.nights} night(s)` : '—';
        },

        initCalendar(keepSelected = false) {
          const selected = keepSelected ? [this.checkin, this.checkout] : null;

          if (this.calendarInstance) {
            this.calendarInstance.destroy();
            this.calendarInstance = null;
          }

          // max quantity calc (optional)
          this.legendMaxQty = 0;
          for (const [dateString, details] of Object.entries(this.availability)) {
            const qty = details.min_quantity || 0;
            if (qty > this.legendMaxQty) this.legendMaxQty = qty;
          }

          // disabled dates
          const disabledDates = [];
          for (const [dateString, details] of Object.entries(this.availability)) {
            if (!details.is_available) disabledDates.push(dateString);
          }

          if (!this.$refs.calendar) return;

          const months = this._months;

          this.calendarInstance = flatpickr(this.$refs.calendar, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            inline: true,
            disableMobile: true,
            monthSelectorType: 'static',
            showMonths: months,

            // ✅ keep selection when re-init
            defaultDate: selected ? selected.filter(Boolean) : [this.checkin, this.checkout].filter(Boolean),

            disable: disabledDates,

            onDayCreate: (_, __, fp, dayElem) => {
              const dateString = fp.formatDate(dayElem.dateObj, 'Y-m-d');

              // reset
              dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');

              // disabled
              if (disabledDates.includes(dateString)) {
                dayElem.classList.add('fp-invalid');
                return;
              }

              const details = this.availability[dateString];
              const qty = Number(details?.min_quantity || 0);

              if (!details?.is_available || qty <= 0) {
                dayElem.classList.add('fp-not-avail');
                return;
              }

              if (qty <= this.legendLowThreshold) {
                dayElem.classList.add('fp-avail-low');
              } else {
                dayElem.classList.add('fp-avail-high');
              }

              // qty badge
              const badge = document.createElement('small');
              badge.textContent = qty;
              badge.style.display = 'block';
              badge.style.fontSize = '10px';
              badge.style.fontWeight = 'bold';
              badge.style.marginTop = '2px';
              dayElem.appendChild(badge);
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
            }
          });
        },

        continueBooking() {
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
          return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        }
      }
    }
  </script>
    <x-footer />
  </body>
</html>

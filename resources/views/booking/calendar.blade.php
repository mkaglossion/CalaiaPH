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
          display: flex;
          justify-content: center;
          width: 100%;
          margin: 0;
          padding: 0;
        }

        /* make inline calendar centered */
        .booking-calendar .flatpickr-calendar{
          position: relative !important;
          margin: 0 auto !important;
          padding: 0 !important;
          width: 100% !important;
          max-width: 100% !important;
          box-shadow: none !important;
          border: none !important;
          left: auto !important;
          top: auto !important;
          transform: none !important;
          background: transparent !important;
          font-family: inherit !important;
        }

        .booking-calendar .flatpickr-calendar.inline{
          display: flex !important;
          justify-content: center !important;
        }

        /* center the container */
        .booking-calendar .flatpickr-rContainer{
          display: flex !important;
          justify-content: center !important;
          align-items: flex-start !important;
          width: 100% !important;
          max-width: 100% !important;
          margin: 0 !important;
        }

        /* inner container - holds months */
        .booking-calendar .flatpickr-innerContainer{
          display: flex !important;
          justify-content: center !important;
          align-items: flex-start !important;
          gap: 20px !important;
          width: 100% !important;
          max-width: 100% !important;
          overflow: visible !important;
          margin: 0 !important;
          padding: 0 !important;
          flex-wrap: nowrap !important;
        }

        /* month wrapper */
        .booking-calendar .flatpickr-months{
          width: auto !important;
          flex: 1 !important;
          max-width: 400px !important;
          margin: 0 !important;
        }

        /* individual month */
        .booking-calendar .flatpickr-month{
          width: 100% !important;
          margin: 0 !important;
        }

        /* navigation arrows */
        .booking-calendar .flatpickr-prev-month,
        .booking-calendar .flatpickr-next-month{
          fill: #1a1a1a !important;
          padding: 10px !important;
        }

        /* day cell sizing */
        .booking-calendar .flatpickr-day{
          border: 1px solid #e5e7eb !important;
          margin: 1px !important;
          padding: 10px 4px !important;
          width: 14.2857% !important;
          max-width: none !important;
          min-height: 60px !important;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
          font-size: 20px !important;
          font-weight: 600 !important;
          position: relative !important;
          text-align: center !important;
          transition: all 0.2s ease !important;
        }
        
        /* Availability badge styling - always visible, positioned top-right */
        .booking-calendar .flatpickr-day .avail-badge{
          position: absolute !important;
          top: 2px !important;
          right: 2px !important;
          display: block !important;
          font-size: 9px !important;
          font-weight: 600 !important;
          padding: 1px 3px !important;
          border-radius: 8px !important;
          line-height: 1 !important;
          min-width: 14px !important;
          text-align: center !important;
          box-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
        }

        /* Hover effect for interactive feedback */
        .booking-calendar .flatpickr-day:hover:not(.flatpickr-disabled):not(.fp-invalid){
          transform: scale(1.05) !important;
          box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
          z-index: 10 !important;
        }

        /* High availability - green background with border */
        .booking-calendar .fp-avail-high .avail-badge{
          background: #d1fae5 !important;
          color: #059669 !important;
          border: 1px solid #a7f3d0 !important;
        }

        /* Low availability - yellow background with border */
        .booking-calendar .fp-avail-low .avail-badge{
          background: #fef3c7 !important;
          color: #d97706 !important;
          border: 1px solid #fde68a !important;
        }

        /* Not available - red background with border */
        .booking-calendar .fp-not-avail .avail-badge{
          background: #fee2e2 !important;
          color: #dc2626 !important;
          border: 1px solid #fecaca !important;
        }

        /* Invalid/disabled - gray background with border */
        .booking-calendar .fp-invalid .avail-badge{
          background: #f3f4f6 !important;
          color: #6b7280 !important;
          border: 1px solid #d1d5db !important;
        }

        /* days container */
        .booking-calendar .flatpickr-days{
          width: 100% !important;
          margin: 0 !important;
        }

        /* week container */
        .booking-calendar .dayContainer{
          width: 100% !important;
          min-width: 100% !important;
          max-width: 100% !important;
          margin: 0 !important;
          display: flex !important;
          flex-wrap: wrap !important;
        }

        /* Month headers */
        .booking-calendar .flatpickr-monthDropdown-months,
        .booking-calendar .flatpickr-current-month{
          background: transparent !important;
          color: #1a1a1a !important;
          font-weight: 600 !important;
          font-size: 16px !important;
          padding: 8px 0 !important;
        }

        /* Weekdays row */
        .booking-calendar .flatpickr-weekdays{
          background: transparent !important;
          color: #666 !important;
          font-weight: 500 !important;
          padding: 8px 0 !important;
          width: 100% !important;
        }
        
        .booking-calendar .flatpickr-weekday{
          color: #666 !important;
          font-weight: 500 !important;
          width: 14.2857% !important;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
          padding: 8px 0 !important;
          font-size: 12px !important;
        }

        /* MOBILE RESPONSIVE - Stack months vertically */
        @media (max-width: 767px){
          .booking-calendar .flatpickr-innerContainer{
            flex-direction: column !important;
            gap: 20px !important;
            width: 100% !important;
          }
          
          .booking-calendar .flatpickr-months{
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
          }
          
          .booking-calendar .flatpickr-month{
            width: 100% !important;
          }
          
          .booking-calendar .flatpickr-day{
            min-height: 54px !important;
            padding: 8px 3px !important;
            font-size: 16px !important;
          }
          
          .booking-calendar .flatpickr-day .avail-badge{
            font-size: 6px !important;
            padding: 1px 3px !important;
            min-width: 14px !important;
            bottom: 2px !important;
            right: 2px !important;
          }
          
          /* On mobile, show badge on tap/touch */
          .booking-calendar .flatpickr-day:active .avail-badge{
            display: block !important;
          }
          
          .booking-calendar .flatpickr-weekday{
            font-size: 11px !important;
          }
        }

        /* TABLET RESPONSIVE */
        @media (min-width: 768px) and (max-width: 1023px){
          .booking-calendar .flatpickr-innerContainer{
            gap: 16px !important;
          }
          58px !important;
            padding: 9px 3px !important;
            font-size: 18px !important;
          }
          
          .booking-calendar .flatpickr-day .avail-badge{
            font-size: 8px !important;
            padding: 2px 6px !important;
            min-width: 21ortant;
          }
          
          .booking-calendar .flatpickr-day .avail-badge{
            font-size: 6px !important;
            padding: 1px 3px !important;
            min-width: 14px !important;
          }
        }

        /* DESKTOP - Ensure proper spacing */
        @media (min-width: 1024px){
          .booking-calendar .flatpickr-innerContainer{
            gap: 24px !important;
          }
          
          .booking-calendar .flatpickr-months{
            max-width: 420px !important;
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
            totalQuantity: @js($totalQuantity),
            maxQty: @js($maxQty)
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
                    Check-in: <span class="font-normal">01:00 PM</span> &nbsp;|&nbsp;
                    Check-out: <span class="font-normal">11:00 PM</span>
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

                <div x-show="!availability || Object.keys(availability).length === 0" class="text-center text-gray-500 py-4">
                    Loading availability data...
                </div>

                <div x-show="availability && Object.keys(availability).length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-avail-high"></span>
                      <span class="text-sm">
                        Available (<span x-text="availableThresholdText"></span>)
                      </span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-avail-low"></span>
                      <span class="text-sm">
                        Limited (<span x-text="limitedThresholdText"></span>)
                      </span>
                    </div>

                    <div class="flex items-center gap-2">
                      <span class="w-4 h-4 inline-block rounded fp-not-avail"></span>
                      <span class="text-sm">Fully Booked (0)</span>
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
    function dateRangePicker({ availability, initialCheckin, initialCheckout, lowThreshold, totalQuantity, maxQty }) {
      return {
        availability: availability || {},
        checkin: null,
        checkout: null,
        calendarInstance: null,

        legendMaxQty: maxQty || 0,
        legendLowThreshold: 2,
        totalQuantity: totalQuantity || 0,

        _months: 2,
        _resizeTimer: null,

        init() {
           if (initialCheckin) this.checkin = initialCheckin;
           if (initialCheckout) this.checkout = initialCheckout;

           // ✅ Smart threshold calculation based on current availability patterns
           this.calculateSmartThresholds();

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

        availableThresholdText: 'Loading...',
        limitedThresholdText: 'Loading...',

        calculateSmartThresholds() {
           try {
             // Get today's date and availability
             const today = new Date().toISOString().split('T')[0];
             const todayAvailability = Math.max(0, this.availability[today]?.min_quantity || 0);

             // Get all available quantities (exclude zero availability)
             const quantities = Object.values(this.availability || {})
               .map(d => Math.max(0, d.min_quantity || 0))
               .filter(q => q > 0);

             // Calculate max quantity for reference
             this.legendMaxQty = quantities.length > 0 ? Math.max(...quantities) : 0;

             // Fallback for no data
             if (quantities.length === 0) {
               this.legendLowThreshold = 1;
               this.availableThresholdText = '2+ slots';
               this.limitedThresholdText = '1–1 slots';
               return;
             }

             // Safe statistical analysis
             const sum = quantities.reduce((a, b) => a + b, 0);
             const avg = quantities.length > 0 ? sum / quantities.length : 0;

             // Calculate variance safely
             const variance = quantities.length > 1
               ? quantities.reduce((a, b) => a + Math.pow(b - avg, 2), 0) / (quantities.length - 1)
               : 0;
             const std = variance > 0 ? Math.sqrt(variance) : 0;

             // Smart threshold calculation with safe fallbacks
             const todayWeight = 0.4;
             const statWeight = 0.6;

             // Today's threshold: 50% of today's availability
             const todayThreshold = Math.max(1, Math.floor(todayAvailability * 0.5));

             // Statistical threshold: average minus half standard deviation
             const statThreshold = isNaN(avg) || isNaN(std)
               ? Math.max(1, Math.floor(avg || 1))
               : Math.max(1, Math.floor(avg - std * 0.5));

             // Dynamic percentage-based thresholds that scale with today's availability
             // This provides contextual availability information

             let dynamicThreshold;
             if (todayAvailability <= 3) {
               // Small operations: be more conservative
               dynamicThreshold = Math.max(1, Math.floor(todayAvailability * 0.4));
             } else if (todayAvailability <= 10) {
               // Medium operations: moderate thresholds
               dynamicThreshold = Math.max(1, Math.floor(todayAvailability * 0.3));
             } else {
               // Large operations: higher thresholds but capped for UX
               dynamicThreshold = Math.min(Math.floor(todayAvailability * 0.2), 5);
             }

             // Ensure minimum useful threshold
             this.legendLowThreshold = Math.max(1, dynamicThreshold);

             // Update text properties with safe values
             const safeThreshold = Math.max(1, this.legendLowThreshold);
             this.availableThresholdText = `${safeThreshold + 1}+ slots`;
             this.limitedThresholdText = `1–${safeThreshold} slots`;

             console.log('Smart threshold calculation:', {
               todayAvailability,
               quantitiesCount: quantities.length,
               avg: isNaN(avg) ? 'NaN' : avg.toFixed(1),
               std: isNaN(std) ? 'NaN' : std.toFixed(1),
               maxQty: this.legendMaxQty,
               todayThreshold,
               statThreshold,
               finalThreshold: this.legendLowThreshold,
               availableText: this.availableThresholdText,
               limitedText: this.limitedThresholdText
             });

           } catch (error) {
             console.error('Error in calculateSmartThresholds:', error);
             // Fallback values
             this.legendLowThreshold = 1;
             this.availableThresholdText = '2+ slots';
             this.limitedThresholdText = '1–1 slots';
           }
        },

        initCalendar(keepSelected = false) {
           const selected = keepSelected ? [this.checkin, this.checkout] : null;

           if (this.calendarInstance) {
             this.calendarInstance.destroy();
             this.calendarInstance = null;
           }

           // ✅ Recalculate smart thresholds
           this.calculateSmartThresholds();

          // disabled dates (only is_available=false)
          const disabledDates = [];
          for (const [dateString, details] of Object.entries(this.availability)) {
            if (!details.is_available) disabledDates.push(dateString);
          }

          if (!this.$refs.calendar) return;

          const today = new Date();
          today.setHours(0, 0, 0, 0);

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

            // Disable past dates and unavailable dates
            disable: [
              (date) => {
                // past dates are disabled
                const checkDate = new Date(date);
                checkDate.setHours(0, 0, 0, 0);
                if (checkDate < today) return true;
                
                // unavailable dates are disabled
                const dateStr = this.formatDateForCompare(date);
                return disabledDates.includes(dateStr);
              }
            ],

            onDayCreate: (_, __, fp, dayElem) => {
              const dateString = fp.formatDate(dayElem.dateObj, 'Y-m-d');

              // reset
              dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');
              dayElem.style.borderRadius = '6px';
              dayElem.style.position = 'relative';

              // Check if past date
              const checkDate = new Date(dayElem.dateObj);
              checkDate.setHours(0, 0, 0, 0);
              const isPast = checkDate < today;

              // disabled or past = invalid (gray)
              if (disabledDates.includes(dateString) || isPast) {
                dayElem.classList.add('fp-invalid');
                // Add badge for disabled dates too
                const badge = document.createElement('div');
                badge.className = 'avail-badge';
                badge.textContent = '-';
                badge.style.position = 'absolute';
                badge.style.top = '2px';
                badge.style.right = '2px';
                badge.style.fontSize = '9px';
                badge.style.fontWeight = '600';
                badge.style.padding = '1px 3px';
                badge.style.borderRadius = '8px';
                badge.style.lineHeight = '1';
                badge.style.minWidth = '14px';
                badge.style.textAlign = 'center';
                badge.style.backgroundColor = '#f3f4f6';
                badge.style.color = '#6b7280';
                badge.style.border = '1px solid #d1d5db';
                dayElem.appendChild(badge);
                return;
              }

              const details = this.availability && this.availability[dateString];
              const qty = details ? Number(details.min_quantity || 0) : 10; // Default to 10 if no data

              // ✅ No inventory = not available (fully booked)
              if (qty === 0) {
                dayElem.classList.add('fp-not-avail');
              } else if (qty <= this.legendLowThreshold) {
                // ✅ Limited availability (1–threshold)
                dayElem.classList.add('fp-avail-low');
              } else {
                // ✅ Available (threshold+1+)
                dayElem.classList.add('fp-avail-high');
              }

              // Create availability badge - always show quantity
              const badge = document.createElement('div');
              badge.className = 'avail-badge';
              badge.textContent = qty > 0 ? qty : '0';
              badge.style.position = 'absolute';
              badge.style.top = '2px';
              badge.style.right = '2px';
              badge.style.fontSize = '9px';
              badge.style.fontWeight = '600';
              badge.style.padding = '1px 3px';
              badge.style.borderRadius = '8px';
              badge.style.lineHeight = '1';
              badge.style.minWidth = '14px';
              badge.style.textAlign = 'center';
              
              // Apply colors based on quantity
              if (qty === 0) {
                badge.style.backgroundColor = '#fee2e2';
                badge.style.color = '#dc2626';
                badge.style.border = '1px solid #fecaca';
              } else if (qty <= this.legendLowThreshold) {
                badge.style.backgroundColor = '#fef3c7';
                badge.style.color = '#d97706';
                badge.style.border = '1px solid #fde68a';
              } else {
                badge.style.backgroundColor = '#d1fae5';
                badge.style.color = '#059669';
                badge.style.border = '1px solid #a7f3d0';
              }
              dayElem.appendChild(badge);

              // range overlay (keep availability background, add outline)
              const hasRange = !!this.checkin && !!this.checkout;
              if (hasRange) {
                if (dateString === this.checkin || dateString === this.checkout) {
                  dayElem.style.outline = '2px solid #2563eb';
                  dayElem.style.outlineOffset = '-2px';
                } else if (dateString > this.checkin && dateString < this.checkout) {
                  dayElem.style.outline = '2px solid #93c5fd';
                  dayElem.style.outlineOffset = '-2px';
                }
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

              // redraw to apply range outlines consistently
              this.initCalendar(true);
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
    {{-- Floating Buttons --}}
    <x-floating-buttons />
    <x-footer />
  </body>
</html>

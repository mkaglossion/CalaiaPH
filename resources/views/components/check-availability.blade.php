@props([
    'active' => 'room',
    // Inventory data from StaycationInventory (per date availability)
    // [
    //   '2025-11-20' => ['is_available' => true, 'min_quantity' => 5],
    //   '2025-11-21' => ['is_available' => false, 'min_quantity' => 0],
    // ]
    'inventory' => null,
    // Total quantity for fallback when no inventory data exists
    'totalQuantity' => null,
])

<div class="max-w-7xl mx-auto p-4 relative z-10 -mt-20">
    <div
        class="bg-white rounded-xl shadow-lg p-6"
        x-data='bookingWidget({
            inventory: @json($inventory ?? (object)[]),
            totalQuantity: @json($totalQuantity ?? 0),
        })'
        x-init="initDatepickers()"
        x-cloak
    >

        <!-- Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 mb-4">
            <button
                @click="setTab('room')"
                :class="activeTab === 'room' ? 'bg-[#b0775c] text-white shadow-sm' : 'text-gray-600 hover:text-[#b0775c]'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-house-door"></i>
                <span>Check Room Availability</span>
            </button>
            <button
                @click="setTab('day-visit')"
                :class="activeTab === 'day-visit' ? 'bg-[#b0775c] text-white shadow-sm' : 'text-gray-600 hover:text-[#b0775c]'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-sun"></i>
                <span>Day Visit</span>
            </button>
            <button
                @click="setTab('special-event')"
                :class="activeTab === 'special-event' ? 'bg-[#b0775c] text-white shadow-sm' : 'text-gray-600 hover:text-[#b0775c]'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-moon"></i>
                <span>Special Events</span>
            </button>
        </div>

        <!-- Divider -->
        <hr class="mb-6 border-gray-300">

        <!-- Booking Form for Room and Day Visit -->
        <form id="booking-form" method="POST" :action="formAction" novalidate x-show="activeTab !== 'special-event'">
        @csrf
        <input type="hidden" name="type" x-ref="type" value="room">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

              {{-- COL 1: CHECK-IN / VISIT DATE --}}
              <div :class="activeTab === 'day-visit' ? 'md:col-span-2' : ''" class="flex flex-col">
                <div class="relative">
                  <input
                    type="text"
                    name="check_in"
                    id="check_in"
                    x-ref="checkIn"
                    class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b0775c] focus:border-[#b0775c] outline-none transition-colors text-sm hover:border-[#b0775c]/70 hover:bg-gray-50"
                    placeholder="Select check-in date"
                    required
                    readonly
                  >
                  <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <i class="bi bi-calendar3"></i>
                  </div>
                </div>

                {{-- reserved helper space para hindi umaangat yung button --}}
                <div class="min-h-10px mt-1">
                  <p class="text-xs text-gray-500"
                     x-text="activeTab === 'day-visit' ? 'Visit Time (1:00 PM)' : 'Check-in Time (1:00 PM)'"
                     style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                  </p>

                  <p class="text-xs text-gray-500"
                     x-show="availabilityText"
                     x-text="availabilityText"
                     style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                  </p>
                </div>
              </div>

              {{-- COL 2: CHECK-OUT (ROOM ONLY) --}}
              <div x-show="activeTab === 'room'" x-cloak class="flex flex-col">
                {{-- ROOM: real check-out input --}}
                <div class="relative" x-show="activeTab === 'room'" x-cloak>
                  <input
                    type="text"
                    name="check_out"
                    id="check_out"
                    x-ref="checkOut"
                    class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b0775c] focus:border-[#b0775c] outline-none transition-colors text-sm hover:border-[#b0775c]/70 hover:bg-gray-50"
                    placeholder="Select check-out date"
                    required
                    readonly
                  >
                  <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <i class="bi bi-calendar3"></i>
                  </div>
                </div>


                <div class="min-h-10px mt-1">
                  <p class="text-xs text-gray-500"
                     x-show="activeTab === 'room'"
                     style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                    Check-out Time (11:00 PM)
                  </p>
                </div>
              </div>

              {{-- COL 3: BUTTON (ALIGNED WITH INPUTS) --}}
              <div class="flex flex-col">
                <button
                  type="submit"
                  name="start_booking"
                  class="w-full h-12 bg-[#b0775c] text-white hover:bg-[#b0775c]/90 transition-colors font-medium rounded-lg text-sm flex items-center justify-center"
                  id="check-availability-btn"
                  style="font-family: 'Gotham Book', sans-serif;"
                >
                  Check Availability
                </button>

                {{-- reserved helper space para pantay pa rin --}}
                <div class="min-h-10px mt-1"></div>
              </div>

            </div>

            {{-- Stay summary row: full width, outside grid --}}
            <div
                class="mt-2 text-xs md:text-sm text-gray-700"
                x-show="staySummary || activeTab === 'room' || activeTab === 'day-visit' || activeTab === 'special-event'"
            >
                <div class="flex flex-col md:flex-row md:items-baseline gap-1 md:gap-3">
                    <!-- e.g. "2 days and 1 night" -->
                    <span x-show="staySummary" x-text="staySummary"></span>

                    <!-- Room helper text -->
                    <span
                        x-show="activeTab === 'room'"
                        class="text-[11px] md:text-xs text-gray-500"
                        style="font-size: 13px; font-family: 'Gotham Book', sans-serif;"
                    >
                        Select the date range for your room booking.
                    </span>

                    <!-- Day Visit helper text -->
                    <span
                        x-show="activeTab === 'day-visit'"
                        class="text-[11px] md:text-xs text-gray-500"
                        style="font-size: 13px; font-family: 'Gotham Book', sans-serif;"
                    >
                        Select the date for your day visit.
                    </span>

                    <!-- Special Event helper text -->
                    <span
                        x-show="activeTab === 'special-event'"
                        class="text-[11px] md:text-xs text-gray-500"
                        style="font-size: 13px; font-family: 'Gotham Book', sans-serif;"
                    >
                        Contact us for special event arrangements.
                    </span>

                </div>
            </div>
        </form>

        <!-- Special Event Contact Section -->
        <div x-show="activeTab === 'special-event'" class="space-y-4">
            <div class="text-center">
                <p class="text-sm text-gray-600 mb-4" style="font-family: 'Gotham Book', sans-serif;">
                    Message us here for special event arrangements
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a
                        href="https://m.me/410774518796277"
                        target="_blank"
                        class="inline-flex items-center gap-2 bg-[#0084ff] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#0084ff]/90 transition-colors"
                    >
                        <i class="bi bi-messenger"></i>
                        Message on Messenger
                    </a>
                    <a
                        href="mailto:calaia.ph@gmail.com?subject=Special Event Inquiry"
                        class="inline-flex items-center gap-2 bg-[#b0775c] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#b0775c]/90 transition-colors"
                    >
                        <i class="bi bi-envelope"></i>
                        Send Email
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

{{-- Flatpickr CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .flatpickr-day.flatpickr-disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .flatpickr-day.today {
        border-color: #b0775c;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #b0775c;
        border-color: #b0775c;
        color: #ffffff;
    }

    .flatpickr-day.inRange {
        background: rgba(177, 119, 92, 0.12);
    }

    .flatpickr-day.weekend {
        background-color: #b0775c;
        border-color: #b0775c;
        color: #ffffff;
        border-radius: 9999px;
    }



    [x-cloak] { display: none !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    function bookingWidget(config = {}) {
        return {
            activeTab: 'room',
            formAction: '{{ url("/booking/staycation") }}',
            fpRange: null,
            availabilityText: '',
            staySummary: '',

            // inventory data from StaycationInventory (per date availability)
            inventory: config.inventory ?? {},

            // total quantity for fallback
            totalQuantity: config.totalQuantity ?? 0,

            setTab(type) {
                this.activeTab = type;
                this.$refs.type.value = type;

                // Update form action based on tab
                if (type === 'room') {
                    this.formAction = '{{ url("/booking/staycation") }}';
                } else if (type === 'day-visit') {
                    this.formAction = '{{ url("/booking/dayvisit") }}';
                } else {
                    this.formAction = '{{ url("/booking") }}';
                }

                if (type === 'day-visit') {
                    if (this.$refs.checkOut) this.$refs.checkOut.value = '';
                }

                if (this.fpRange) {
                    if (type === 'day-visit') {
                        this.fpRange.set('mode', 'single');
                    } else {
                        this.fpRange.set('mode', 'range');
                    }
                }

                this.updateAvailabilityText();
                this.updateStaySummary();
            },

            initDatepickers() {
                const today = new Date();
                const self = this;

                this.fpRange = flatpickr(this.$refs.checkIn, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    minDate: today,
                    disableMobile: true,
                    onDayCreate(selectedDates, dateStr, instance, dayElem) {
                        self.markWeekends(dayElem);
                    },
                    onOpen(selectedDates, dateStr, instance) {
                        if (self.activeTab === 'day-visit') {
                            instance.set('mode', 'single');
                        } else {
                            instance.set('mode', 'range');
                        }
                    },
                    onChange(selectedDates, dateStr, instance) {
                        // DAY VISIT
                        if (self.activeTab === 'day-visit') {
                            if (selectedDates.length > 0) {
                                const inStr = instance.formatDate(selectedDates[0], 'Y-m-d');
                                self.$refs.checkIn.value = inStr;
                            }
                            if (self.$refs.checkOut) self.$refs.checkOut.value = '';
                            self.updateAvailabilityText();
                            self.updateStaySummary();
                            return;
                        }

                        // ROOM = range
                        if (selectedDates.length > 0) {
                            const inStr = instance.formatDate(selectedDates[0], 'Y-m-d');
                            self.$refs.checkIn.value = inStr;
                        }

                        if (selectedDates.length > 1 && self.$refs.checkOut) {
                            const outStr = instance.formatDate(selectedDates[1], 'Y-m-d');
                            self.$refs.checkOut.value = outStr;
                        } else if (self.$refs.checkOut) {
                            self.$refs.checkOut.value = '';
                        }

                        self.updateAvailabilityText();
                        self.updateStaySummary();
                    }
                });

                if (this.activeTab === 'day-visit') {
                    this.fpRange.set('mode', 'single');
                } else {
                    this.fpRange.set('mode', 'range');
                }
            },

            markWeekends(dayElem) {
                if (!dayElem || !dayElem.dateObj) return;
                const date = dayElem.dateObj;
                const day = date.getDay();
                if (day === 0 || day === 6) {
                    dayElem.classList.add('weekend');
                }
            },

            applyAvailabilityColor(dayElem, instance) {
                if (!dayElem || !dayElem.dateObj) return;
                const dateString = instance.formatDate(dayElem.dateObj, 'Y-m-d');

                // reset
                dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');

                const details = this.inventory[dateString];
                const qty = Number(details?.min_quantity || 0);

                if (!details?.is_available || qty <= 0) {
                    dayElem.classList.add('fp-not-avail');
                    return;
                }

                const lowThreshold = Math.floor(this.totalQuantity * 0.3); // 30% threshold
                if (qty <= lowThreshold) {
                    dayElem.classList.add('fp-avail-low');
                } else {
                    dayElem.classList.add('fp-avail-high');
                }
            },

            updateAvailabilityText() {
                const date = this.$refs.checkIn.value;
                if (!date) {
                    this.availabilityText = '';
                    return;
                }

                const dateData = this.inventory[date];
                if (!dateData) {
                    // No inventory data for this date, use total quantity as fallback
                    this.availabilityText = `Up to ${this.totalQuantity} slot(s) available per day.`;
                    return;
                }

                const { is_available, min_quantity } = dateData;

                if (!is_available || min_quantity <= 0) {
                    this.availabilityText = 'Fully booked na po for this date.';
                } else {
                    this.availabilityText = `${min_quantity} slot(s) available for this date.`;
                }
            },


            updateStaySummary() {
                const checkInVal = this.$refs.checkIn.value;
                const checkOutVal = this.$refs.checkOut ? this.$refs.checkOut.value : '';

                if (!checkInVal) {
                    this.staySummary = '';
                    return;
                }

                if (this.activeTab === 'day-visit') {
                    this.staySummary = 'Day visit only';
                    return;
                }

                if (!checkOutVal) {
                    this.staySummary = '';
                    return;
                }

                const start = new Date(checkInVal + 'T00:00:00');
                const end   = new Date(checkOutVal + 'T00:00:00');
                const diffMs = end - start;

                if (diffMs <= 0) {
                    this.staySummary = '';
                    return;
                }

                const nights = diffMs / (1000 * 60 * 60 * 24);
                const days   = nights + 1;

                const dayLabel   = days === 1 ? 'day' : 'days';
                const nightLabel = nights === 1 ? 'night' : 'nights';

                this.staySummary = `${days} ${dayLabel} and ${nights} ${nightLabel}`;
            }
        }
    }
</script>

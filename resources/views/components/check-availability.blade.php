@props([
    'active' => 'room',
    // galing Admin Panel (per day capacity)
    'dayVisitCapacity' => null,
    'roomCapacity' => null,
    'specialEventCapacity' => null,
    // optional: per-date remaining slots (array from backend)
    // [
    //   'day-visit' => ['2025-11-20' => 5, ...],
    //   'room'      => ['2025-11-20' => 2, ...],
    //   'special-event' => ['2025-11-20' => 1, ...],
    // ]
    'inventory' => null,
])

<div class="max-w-7xl mx-auto p-4 relative z-10 -mt-20">
    <div
        class="bg-white rounded-xl shadow-lg p-6"
        x-data='bookingWidget({
            dayVisitCapacity: @json($dayVisitCapacity),
            roomCapacity: @json($roomCapacity),
            specialEventCapacity: @json($specialEventCapacity),
            inventory: @json($inventory ?? ["day-visit" => (object)[], "room" => (object)[], "special-event" => (object)[]]),
        })'
        x-init="initDatepickers()"
        x-cloak
    >

        <!-- Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 mb-4">
            <button
                @click="setTab('room')"
                :class="activeTab === 'room' ? 'bg-[#8f5d54] text-white shadow-sm' : 'text-gray-600 hover:text-[#8f5d54]'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-house-door"></i>
                <span>Check Room Availability</span>
            </button>
            <button
                @click="setTab('day-visit')"
                :class="activeTab === 'day-visit' ? 'bg-[#8f5d54] text-white shadow-sm' : 'text-gray-600 hover:text-[#8f5d54]'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-sun"></i>
                <span>Day Visit</span>
            </button>
            <button
                @click="setTab('special-event')"
                :class="activeTab === 'special-event' ? 'bg-[#8f5d54] text-white shadow-sm' : 'text-gray-600 hover:text-[#8f5d54]'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-moon"></i>
                <span>Special Events</span>
            </button>
        </div>

        <!-- Divider -->
        <hr class="mb-6 border-gray-300">

        <form id="booking-form" method="POST" :action="formAction" novalidate>
        @csrf
        <input type="hidden" name="type" x-ref="type" value="room">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Check-in / Visit Date -->
                <div :class="activeTab === 'day-visit' ? 'md:col-span-2' : 'md:col-span-1'">
                    <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2" x-text="activeTab === 'day-visit' ? 'Visit Date' : 'Check-in'" style="font-family: 'Gotham Book', sans-serif;">
                    </label>

                    <div class="relative">
                        <input
                            type="text"
                            name="check_in"
                            id="check_in"
                            x-ref="checkIn"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none transition-colors text-sm hover:border-[#8f5d54]/70 hover:bg-gray-50"
                            placeholder="Select date"
                            required
                            readonly
                        >
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>

                    <!-- Availability text -->
                    <p class="text-xs text-gray-500 mt-1" x-show="availabilityText" x-text="availabilityText" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;"></p>
                </div>

                <!-- Check-out (ROOM + SPECIAL EVENT, pero iba behavior) -->
                <div x-show="activeTab !== 'day-visit'" class="md:col-span-1">
                    <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2" style="font-family: 'Gotham Book', sans-serif;">
                        Check-out
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            name="check_out"
                            id="check_out"
                            x-ref="checkOut"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none transition-colors text-sm hover:border-[#8f5d54]/70 hover:bg-gray-50"
                            placeholder="Select date"
                            :required="activeTab === 'room'"
                            readonly
                        >
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>
                </div>

                <!-- Button -->
                <div class="md:col-span-1 flex items-end h-full">
                    <button
                        type="submit"
                        name="start_booking"
                        class="w-full bg-[#8f5d54] text-white hover:bg-[#8f5d54]/90 transition-colors font-medium py-3 px-6 rounded-lg text-sm h-[46px] flex items-center justify-center"
                        id="check-availability-btn"
                    >
                        Search
                    </button>
                </div>

                <!-- Stay summary row: full width, aligned with grid -->
                <div
                    class="mt-2 text-xs md:text-sm text-gray-700 col-span-1 md:col-span-3"
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
                            Select the date range for your special event.
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

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

            // capacities galing Admin Panel (per day)
            dayVisitCapacity: config.dayVisitCapacity ?? null,
            roomCapacity: config.roomCapacity ?? null,
            specialEventCapacity: config.specialEventCapacity ?? null,

            // remaining slots per date per type (puwedeng i-override ng backend)
            inventory: config.inventory ?? {
                'day-visit': {},
                'room': {},
                'special-event': {}
            },

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

                        // Auto-submit if room tab and dates selected
                        if (self.activeTab === 'room' && self.$refs.checkOut && self.$refs.checkOut.value) {
                            document.getElementById('booking-form').submit();
                        }
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

            updateAvailabilityText() {
                const date = this.$refs.checkIn.value;
                if (!date) {
                    this.availabilityText = '';
                    return;
                }

                const type = this.activeTab;
                const typeInventory = this.inventory[type] || {};
                const remaining = typeInventory[date];

                const capacityMap = {
                    'day-visit': this.dayVisitCapacity,
                    'room': this.roomCapacity,
                    'special-event': this.specialEventCapacity,
                };
                const capacity = capacityMap[type] ?? null;

                if (remaining === 0) {
                    this.availabilityText = 'Fully booked na po for this date.';
                } else if (typeof remaining === 'number') {
                    if (capacity !== null) {
                        const used = capacity - remaining;
                        this.availabilityText = `${remaining} slot(s) remaining out of ${capacity} (booked ${used}).`;
                    } else {
                        this.availabilityText = `${remaining} slot(s) remaining for this date.`;
                    }
                } else if (capacity !== null) {
                    // may base capacity pero wala pang per-date data
                    this.availabilityText = `Up to ${capacity} slot(s) available per day.`;
                } else {
                    this.availabilityText = '';
                }
            },

            updateSpecialEventStay() {
                if (this.activeTab !== 'special-event') return;

                const checkInVal = this.$refs.checkIn.value;
                if (!checkInVal) {
                    if (this.$refs.checkOut) this.$refs.checkOut.value = '';
                    return;
                }

                const start = new Date(checkInVal + 'T00:00:00');
                const end = new Date(start.getTime() + 1 * 24 * 60 * 60 * 1000);

                const yyyy = end.getFullYear();
                const mm = String(end.getMonth() + 1).padStart(2, '0');
                const dd = String(end.getDate()).padStart(2, '0');
                const outStr = `${yyyy}-${mm}-${dd}`;

                if (this.$refs.checkOut) {
                    this.$refs.checkOut.value = outStr;
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

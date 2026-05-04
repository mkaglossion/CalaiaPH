@props([
    'active' => 'room',
    'inventory' => null,
    'totalQuantity' => null,
    'dayTourInventory' => null,
    'totalDayTourQuantity' => null,
])

<div class="bg-white rounded-2xl shadow-2xl p-4 md:p-6 border border-gray-100 mx-4 md:mx-auto max-w-7xl relative z-10 -mt-12 md:-mt-16"
        x-data='bookingWidget({
            inventory: @json($inventory ?? (object)[]),
            totalQuantity: @json($totalQuantity ?? 0),
            dayTourInventory: @json($dayTourInventory ?? (object)[]),
            totalDayTourQuantity: @json($totalDayTourQuantity ?? 0),
        })'
        x-init="initDatepickers()"
        x-cloak
    >
        <!-- Spacing -->
        <div class="mb-4 md:mb-6"></div>

        <!-- Booking Form for Room -->
        <form id="booking-form" method="POST" :action="formAction" novalidate>
            @csrf
            <input type="hidden" name="type" x-ref="type" value="room">

            <!-- Hidden fields for guests/rooms -->
            <input type="hidden" name="rooms_count" :value="roomsCount">
            <input type="hidden" name="adults_total" :value="totalAdults">
            <input type="hidden" name="children_total" :value="totalChildren">
            <input type="hidden" name="pets_total" :value="totalPets">

            <input type="hidden" name="guests" :value="totalAdults + totalChildren">



            <!-- Per-room hidden fields -->
            <template x-for="(room, idx) in rooms" :key="idx">
                <div class="hidden">
                    <input type="hidden" :name="`rooms[${idx+1}][adults]`" :value="room.adults">
                    <input type="hidden" :name="`rooms[${idx+1}][children]`" :value="room.children">
                    <input type="hidden" :name="`rooms[${idx+1}][pets]`" :value="room.pets || 0">
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-4">
                {{-- COL 1: CHECK-IN --}}
                <div class="flex flex-col">
                    <div class="relative group">
                        <input
                            type="text"
                            name="check_in"
                            id="check_in"
                            autocomplete="off"
                            x-ref="checkIn"
                            class="w-full h-12 md:h-12 pl-10 pr-4 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand outline-none transition-all text-sm hover:border-brand/70 hover:bg-gray-50"
                            placeholder="Check-in date"
                            required
                            readonly
                        >
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 group-hover:text-brand transition">
                            <i class="bi bi-calendar-event text-base md:text-lg"></i>
                        </div>
                    </div>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500 font-medium">
                            Check-in Time
                        </p>

                        <p class="text-xs font-medium"
                           x-show="availabilityText"
                           x-text="availabilityText"
                           :class="hasAvailability() ? 'text-green-600' : 'text-red-600'">
                        </p>
                    </div>
                </div>

                {{-- COL 2: CHECK-OUT --}}
                <div class="flex flex-col">
                    <div class="relative group">
                        <input
                            type="text"
                            name="check_out"
                            id="check_out"
                            autocomplete="off"
                            x-ref="checkOut"
                            class="w-full h-12 md:h-12 pl-10 pr-4 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand outline-none transition-all text-sm hover:border-brand/70 hover:bg-gray-50"
                            placeholder="Check-out date"
                            required
                            readonly
                        >
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 group-hover:text-brand transition">
                            <i class="bi bi-calendar-check text-base md:text-lg"></i>
                        </div>
                    </div>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500 font-medium">
                            Check-out Time
                        </p>
                    </div>
                </div>

                {{-- COL 3: GUESTS + ROOMS (DROPDOWN PANEL) --}}
                <div class="flex flex-col relative" @click.outside="closeGuestsPanel()">
                    <button
                        type="button"
                        @click.stop="toggleGuestsPanel()"
                        class="w-full h-12 md:h-12 border-2 border-gray-300 rounded-lg text-left px-3 md:px-4 hover:border-brand/70 hover:bg-brand/5 transition-all flex items-center justify-between group"
                        :aria-expanded="isGuestsPanelOpen.toString()"
                        aria-controls="guests-panel"
                    >
                        <div class="flex items-center gap-2 text-gray-700 group-hover:text-brand transition">
                            <i class="bi bi-people text-base md:text-lg"></i>
                            <span class="text-xs sm:text-sm font-medium" x-text="guestsSummary"></span>
                        </div>
                        <i class="bi transition-transform text-base md:text-lg" :class="isGuestsPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500 font-medium">
                            Rooms, Adults, Children & Pets
                        </p>
                    </div>

                    <!-- Wide / Responsive dropdown panel -->
                    <div
                        id="guests-panel"
                        x-show="isGuestsPanelOpen"
                        @click.outside="closeGuestsPanel()"
                        x-cloak
                        x-transition.origin.top
                        class="absolute top-full mt-2 z-9999
                               left-1/2 -translate-x-1/2
                               w-[min(680px,calc(100vw-1rem))]
                               max-h-[90vh]
                               overflow-y-auto
                               bg-white border border-gray-200 rounded-lg sm:rounded-xl shadow-xl p-3 sm:p-4"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-4">
                            <div class="flex-1">
                            <h3 class="text-sm md:text-base font-semibold text-gray-800">Rooms & Guests</h3>
                                <p class="text-xs text-gray-500 leading-snug">Add up to 4 rooms. Set adults/children/pets per room.</p>
                            </div>
                            <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0 self-start mt-0.5" @click="closeGuestsPanel()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <!-- Rooms count -->
                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between border border-gray-200 rounded-lg sm:rounded-xl p-2.5 sm:p-3 gap-2 sm:gap-0">
                            <div>
                                <p class="text-xs md:text-sm font-medium text-gray-800">Rooms</p>
                                <p class="text-xs text-gray-500">Choose 1 to 4 rooms</p>
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <button type="button"
                                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-sm"
                                        @click.stop="setRoomsCount(roomsCount - 1)">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <span class="min-w-6 sm:min-w-8 text-center font-semibold text-sm" x-text="roomsCount"></span>
                                <button type="button"
                                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-sm"
                                        @click.stop="setRoomsCount(roomsCount + 1)">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Per-room controls -->
                        <div class="mt-3 space-y-2 sm:space-y-3 max-h-80 md:max-h-96 overflow-y-auto pr-1 scrollbar-thin">
                            <template x-for="(room, idx) in rooms" :key="idx">
                                <div class="border border-gray-200 rounded-lg sm:rounded-xl p-2.5 sm:p-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-2">
                                        <p class="text-sm font-semibold text-gray-800" x-text="`Room ${idx+1}`"></p>
                                        <p class="text-xs text-gray-500 leading-snug">
                                            <span x-text="room.adults"></span> adults • <span x-text="room.children"></span> children<span x-show="room.pets > 0"> • <span x-text="room.pets"></span> pet<span x-show="room.pets !== 1">s</span></span>
                                        </p>
                                    </div>

                                    <div class="mt-2 sm:mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                                        <!-- Adults -->
                                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-2 sm:p-2.5">
                                            <div class="pr-1 sm:pr-2 flex-1">
                                                <p class="text-xs sm:text-sm font-medium text-gray-800">Adults</p>
                                            </div>
                                            <div class="flex items-center gap-0.5 sm:gap-1 shrink-0">
                                                <button type="button"
                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                                                        @click="decAdults(idx)"
                                                        :disabled="room.adults <= 1">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <span class="w-6 sm:w-7 text-center font-semibold text-xs sm:text-sm" x-text="room.adults"></span>
                                                <button type="button"
                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                                                        @click="incAdults(idx)">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Children -->
                                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-2 sm:p-2.5">
                                            <div class="pr-1 sm:pr-2 flex-1">
                                                <p class="text-xs sm:text-sm font-medium text-gray-800">Children</p>
                                                <p class="text-xs text-gray-500">0–12</p>
                                            </div>
                                            <div class="flex items-center gap-0.5 sm:gap-1 shrink-0">
                                                <button type="button"
                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                                                        @click="decChildren(idx)"
                                                        :disabled="room.children <= 0">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <span class="w-6 sm:w-7 text-center font-semibold text-xs sm:text-sm" x-text="room.children"></span>
                                                <button type="button"
                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                                                        @click="incChildren(idx)">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Pets -->
                                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-2 sm:p-2.5">
                                            <div class="pr-1 sm:pr-2 flex-1">
                                                <p class="text-xs sm:text-sm font-medium text-gray-800">Pets</p>
                                            </div>
                                            <div class="flex items-center gap-0.5 sm:gap-1 shrink-0">
                                                <button type="button"
                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                                                        @click="decPets(idx)"
                                                        :disabled="room.pets <= 0">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <span class="w-6 sm:w-7 text-center font-semibold text-xs sm:text-sm" x-text="room.pets || 0"></span>
                                                <button type="button"
                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                                                        @click="incPets(idx)">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 sm:mt-5 flex flex-col-reverse sm:flex-row gap-2 justify-end sticky bottom-0 bg-white pt-3 -mx-3 sm:-mx-4 px-3 sm:px-4">
                            <button type="button"
                                    class="px-3 sm:px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-xs sm:text-sm"
                                    @click="resetGuests()">
                                Reset
                            </button>
                            <button type="button"
                                    class="px-3 sm:px-4 py-2 rounded-lg border border-red-300 hover:border-red-500 text-xs sm:text-sm text-red-600 hover:bg-red-50"
                                    @click="clearDates()"
                                    x-show="hasDateSelection">
                                <i class="bi bi-trash mr-1"></i> Clear Dates
                            </button>
                            <button type="button"
                                    class="px-3 sm:px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand/90 text-xs sm:text-sm"
                                    @click="closeGuestsPanel()">
                                Done
                            </button>
                        </div>
                    </div>
                </div>



                {{-- COL 5: SUBMIT --}}
                <div class="flex flex-col">
                    <button
                        type="button"
                        @click="checkAvailability()"
                        :disabled="isLoading || !hasAvailability()"
                        class="w-full h-12 md:h-12 bg-brand text-white hover:bg-brand/90 active:scale-95 transition-all font-semibold rounded-lg text-xs md:text-sm flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                        id="check-availability-btn"
                    >
                        <span x-show="isLoading" class="inline-block">
                            <i class="bi bi-hourglass-split animate-spin"></i>
                        </span>
                        <span x-text="isLoading ? 'Checking...' : 'Check Availability'"></span>
                    </button>
                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-red-600 font-medium transition-opacity" 
                           x-show="availabilityText && !hasAvailability()"
                           x-cloak>
                            <i class="bi bi-exclamation-circle mr-1"></i>Please select different dates
                        </p>
                    </div>
                </div>
            </div>


        </form>

    </div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    <style>
        .flatpickr-day.flatpickr-disabled { opacity: 0.35; cursor: not-allowed; }
        .flatpickr-day.today { border-color: #a75e3e; font-weight: bold; }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange { 
            background: #a75e3e; 
            border-color: #a75e3e; 
            color: #ffffff;
            font-weight: 600;
        }
        .flatpickr-day.inRange { 
            background: rgba(167, 94, 62, 0.15); 
            color: #a75e3e;
        }
        .flatpickr-day.weekend {
            background-color: #a75e3e;
            border-color: #a75e3e;
            color: #ffffff;
            border-radius: 6px;
            font-weight: 600;
        }
        /* Availability indicators */
        .flatpickr-day.fp-avail-high {
            background-color: #22c55e !important;
            border-color: #22c55e !important;
            color: #ffffff !important;
            border-radius: 6px;
            font-weight: 600;
        }
        .flatpickr-day.fp-avail-low {
            background-color: #eab308 !important;
            border-color: #eab308 !important;
            color: #ffffff !important;
            border-radius: 6px;
            font-weight: 600;
        }
        .flatpickr-day.fp-not-avail {
            background-color: #d1d5db !important;
            border-color: #d1d5db !important;
            color: #6b7280 !important;
            border-radius: 6px;
            font-weight: 600;
            opacity: 0.6;
        }
        [x-cloak] { display: none !important; }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        .swal-booking-confirm .swal2-icon { 
            width: 4rem !important; 
            height: 4rem !important;
            margin-bottom: 1rem !important;
        }
        .swal-booking-confirm .swal2-confirm {
            box-shadow: 0 4px 12px rgba(167, 94, 62, 0.2) !important;
        }
        .swal-booking-confirm .swal2-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(167, 94, 62, 0.3) !important;
        }
        /* Ensure solid white background */
        #booking-form {
            background: #ffffff;
        }
    </style>
</style>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function bookingWidget(config = {}) {
        return {
            activeTab: 'room',
            formAction: '{{ url("/booking/staycation") }}',
            fpRange: null,
            availabilityText: '',
            staySummary: '',
            isLoading: false,

            // inventory
            inventory: config.inventory ?? {},
            totalQuantity: config.totalQuantity ?? 0,

            // day tour
            dayTourInventory: config.dayTourInventory ?? {},
            totalDayTourQuantity: config.totalDayTourQuantity ?? 0,

            // Guests/Rooms dropdown panel
            isGuestsPanelOpen: false,
            toggleGuestsPanel() {
                this.isGuestsPanelOpen = !this.isGuestsPanelOpen;
            },
            closeGuestsPanel() { this.isGuestsPanelOpen = false; },

            // Track date selection for Clear button visibility
            hasDateSelection: false,

            roomsCount: 1,
            rooms: [{ adults: 1, children: 0, pets: 0 }],

            get totalAdults() {
                return this.rooms.reduce((sum, r) => sum + Number(r.adults || 0), 0);
            },
            get totalChildren() {
                return this.rooms.reduce((sum, r) => sum + Number(r.children || 0), 0);
            },
            get totalPets() {
                return this.rooms.reduce((sum, r) => sum + Number(r.pets || 0), 0);
            },
            get guestsSummary() {
                const rooms = this.roomsCount;
                const a = this.totalAdults;
                const c = this.totalChildren;
                const p = this.totalPets;
                const adultLabel = a === 1 ? 'Adult' : 'Adults';
                const childLabel = c === 1 ? 'Child' : 'Children';
                const petLabel = p === 1 ? 'Pet' : 'Pets';

                // For room booking, show rooms and guests including pets
                const roomLabel = rooms === 1 ? 'Room' : 'Rooms';
                let summary = `${rooms} ${roomLabel}, ${a} ${adultLabel}`;
                if (c > 0) summary += `, ${c} ${childLabel}`;
                if (p > 0) summary += `, ${p} ${petLabel}`;
                return summary;
            },

            hasAvailability() {
                if (!this.availabilityText) return true;
                return !this.availabilityText.toLowerCase().includes('no available');
            },

            setRoomsCount(val) {
                const next = Number(val || 1);
                if (next > 4) {
                    Swal.fire({
                        title: 'Maximum Rooms Reached',
                        text: 'You can only add up to 4 rooms.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#a75e3e'
                    });
                    return;
                }
                if (next < 1) {
                    Swal.fire({
                        title: 'Minimum Rooms Required',
                        text: 'You need at least 1 room.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#a75e3e'
                    });
                    return;
                }
                this.roomsCount = next;

                if (this.rooms.length < next) {
                    while (this.rooms.length < next) this.rooms.push({ adults: 1, children: 0, pets: 0 });
                } else if (this.rooms.length > next) {
                    this.rooms = this.rooms.slice(0, next);
                }
            },

            incAdults(idx) { this.rooms[idx].adults = Number(this.rooms[idx].adults || 0) + 1; },
            decAdults(idx) { this.rooms[idx].adults = Math.max(1, Number(this.rooms[idx].adults || 1) - 1); },
            incChildren(idx) { this.rooms[idx].children = Number(this.rooms[idx].children || 0) + 1; },
            decChildren(idx) { this.rooms[idx].children = Math.max(0, Number(this.rooms[idx].children || 0) - 1); },
            incPets(idx) {
                const currentPets = Number(this.rooms[idx].pets || 0);
                if (currentPets >= 2) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pet Limit',
                        text: 'Maximum 2 pets per room',
                        confirmButtonColor: '#a75e3e',
                        timer: 1500
                    });
                    return;
                }
                this.rooms[idx].pets = currentPets + 1;
            },
            decPets(idx) { this.rooms[idx].pets = Math.max(0, Number(this.rooms[idx].pets || 0) - 1); },

            resetGuests() {
                this.roomsCount = 1;
                this.rooms = [{ adults: 1, children: 0, pets: 0 }];
            },

            clearDates() {
                // Clear date inputs
                if (this.$refs.checkIn) this.$refs.checkIn.value = '';
                if (this.$refs.checkOut) this.$refs.checkOut.value = '';
                this.hasDateSelection = false;
                // Close the guests panel after clearing
                this.isGuestsPanelOpen = false;
            },



            checkAvailability() {
                if (!this.$refs.checkIn?.value || !this.$refs.checkOut?.value) {
                    Swal.fire({
                        title: 'Please Select Dates',
                        text: 'You need to select both check-in and check-out dates.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#a75e3e'
                    });
                    return;
                }

                // Show confirmation modal with booking summary
                const checkIn = this.$refs.checkIn.value;
                const checkOut = this.$refs.checkOut.value;
                const roomsLabel = this.roomsCount === 1 ? 'Room' : 'Rooms';
                const adultsLabel = this.totalAdults === 1 ? 'Adult' : 'Adults';
                const childrenLabel = this.totalChildren === 1 ? 'Child' : 'Children';
                const petsLabel = this.totalPets === 1 ? 'Pet' : 'Pets';

                let guestDetails = `${this.totalAdults} ${adultsLabel}`;
                if (this.totalChildren > 0) guestDetails += `, ${this.totalChildren} ${childrenLabel}`;
                if (this.totalPets > 0) guestDetails += `, ${this.totalPets} ${petsLabel}`;

                const summaryHtml = `
                    <div class="text-left space-y-0">
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-200">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-house text-gray-600 text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold mb-0.5">ROOM DETAILS</p>
                                <p class="text-base font-bold text-gray-900">${this.roomsCount} ${roomsLabel}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 py-4 border-b border-gray-200">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-people text-gray-600 text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold mb-0.5">GUESTS</p>
                                <p class="text-base font-bold text-gray-900">${guestDetails}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 py-4 border-b border-gray-200">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-calendar-range text-gray-600 text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 font-semibold mb-2">CHECK-IN & CHECK-OUT</p>
                                <div class="flex gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 border border-gray-300 rounded-lg">
                                        <i class="bi bi-arrow-right text-gray-600 text-sm"></i>
                                        <span class="text-sm font-medium text-gray-800">${checkIn}</span>
                                    </span>
                                    <span class="inline-flex items-center px-2 text-gray-400">→</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 border border-gray-300 rounded-lg">
                                        <i class="bi bi-arrow-left text-gray-600 text-sm"></i>
                                        <span class="text-sm font-medium text-gray-800">${checkOut}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <div class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg w-full justify-center">
                                <i class="bi bi-moon-stars text-gray-600 text-base"></i>
                                <span class="font-semibold text-gray-900">${this.staySummary || 'Duration pending'}</span>
                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    title: 'Confirm Your Booking',
                    html: summaryHtml,
                    icon: 'info',
                    iconColor: '#3b82f6',
                    showCancelButton: true,
                    confirmButtonText: '✓ Proceed to Booking',
                    confirmButtonColor: '#a75e3e',
                    cancelButtonText: 'Back',
                    cancelButtonColor: '#6b7280',
                    customClass: {
                        container: 'swal-booking-confirm',
                        popup: 'rounded-xl shadow-2xl',
                        title: 'font-bold text-xl text-gray-900',
                        htmlContainer: 'text-left',
                        confirmButton: 'font-semibold px-6 py-2.5 rounded-lg transition-all hover:shadow-lg',
                        cancelButton: 'font-semibold px-6 py-2.5 rounded-lg transition-all'
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submitBooking();
                    }
                });
            },

            submitBooking() {
                this.isLoading = true;
                // Small delay to show loading state visually
                setTimeout(() => {
                    document.getElementById('booking-form').submit();
                }, 500);
            },

            setTab(type) {
                // Since there's only room availability now, this is simplified
                this.activeTab = 'room';
                this.$refs.type.value = 'room';
                this.formAction = '{{ url("/booking/staycation") }}';

                if (this.fpRange) {
                    this.fpRange.set('mode', 'range');
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
                        self.markAvailability(dayElem);
                    },
                    onOpen(selectedDates, dateStr, instance) {
                        instance.set('mode', 'range');
                    },
                    onChange(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            const inStr = instance.formatDate(selectedDates[0], 'Y-m-d');
                            self.$refs.checkIn.value = inStr;
                        }

                        if (selectedDates.length > 1 && self.$refs.checkOut) {
                            const outStr = instance.formatDate(selectedDates[1], 'Y-m-d');
                            self.$refs.checkOut.value = outStr;
                            self.hasDateSelection = true;
                        } else if (self.$refs.checkOut) {
                            self.$refs.checkOut.value = '';
                        }

                        self.updateAvailabilityText();
                        self.updateStaySummary();
                    },
                    onReady(selectedDates, dateStr, instance) {
                        const ensureFlatpickrAttrs = () => {
                            document.querySelectorAll('input.numInput.cur-year:not([name])').forEach((input, idx) => {
                                if (!input.id) input.id = `fp-year-input-${Date.now()}-${idx}`;
                                if (!input.name) input.name = 'flatpickr_year';
                            });
                            document.querySelectorAll('input.numInput.cur-month:not([name])').forEach((input, idx) => {
                                if (!input.id) input.id = `fp-month-input-${Date.now()}-${idx}`;
                                if (!input.name) input.name = 'flatpickr_month';
                            });
                            document.querySelectorAll('select.flatpickr-monthDropdown-months:not([name])').forEach((select, idx) => {
                                if (!select.id) select.id = `fp-month-select-${Date.now()}-${idx}`;
                                if (!select.name) select.name = 'flatpickr_month_select';
                            });
                        };
                        ensureFlatpickrAttrs();
                        setTimeout(ensureFlatpickrAttrs, 50);
                        setTimeout(ensureFlatpickrAttrs, 200);
                        const observer = new MutationObserver(() => ensureFlatpickrAttrs());
                        const calendarContainer = instance.calendarContainer;
                        if (calendarContainer) observer.observe(calendarContainer, { childList: true, subtree: true });
                    }
                });

                this.fpRange.set('mode', 'range');
            },

            markWeekends(dayElem) {
                if (!dayElem || !dayElem.dateObj) return;
                const day = dayElem.dateObj.getDay();
                if (day === 0 || day === 6) dayElem.classList.add('weekend');
            },

            markAvailability(dayElem) {
                if (!dayElem || !dayElem.dateObj) return;
                
                const fmtLocal = (d) => {
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${day}`;
                };
                
                const dateString = fmtLocal(dayElem.dateObj);
                const dateData = this.inventory[dateString];
                const quantity = dateData?.available_quantity ?? dateData?.min_quantity ?? 0;
                const isAvailable = dateData?.is_available ?? (quantity > 0);
                
                // Don't mark past dates or outside month dates
                if (dayElem.classList.contains('prevMonthDay') || dayElem.classList.contains('nextMonthDay')) {
                    return;
                }
                
                if (!isAvailable || quantity <= 0) {
                    dayElem.classList.add('fp-not-avail');
                } else if (quantity <= 2) {
                    dayElem.classList.add('fp-avail-low');
                } else {
                    dayElem.classList.add('fp-avail-high');
                }
            },

            updateAvailabilityText() {
                const checkInVal = this.$refs.checkIn.value;
                const checkOutVal = this.$refs.checkOut ? this.$refs.checkOut.value : '';

                if (!checkInVal) { this.availabilityText = ''; return; }

                if (!checkOutVal) {
                    const dateData = this.inventory[checkInVal];
                    if (!dateData) {
                        this.availabilityText = `Up to ${this.totalQuantity} slot(s) available per day (total across all staycations).`;
                        return;
                    }
                    const quantity = dateData.available_quantity ?? dateData.min_quantity ?? 0;
                    const isAvailable = dateData.is_available ?? (quantity > 0);

                    this.availabilityText = (!isAvailable || quantity <= 0)
                        ? 'No available for this date.'
                        : `${quantity} slot(s) available for this date (total across all staycations).`;
                    return;
                }

                const start = new Date(checkInVal + 'T00:00:00');
                const end = new Date(checkOutVal + 'T00:00:00');

                const fmtLocal = (d) => {
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${day}`;
                };

                let minQty = Infinity;
                let currentDate = new Date(start);

                while (currentDate < end) {
                    const dateString = fmtLocal(currentDate);
                    const dateData = this.inventory[dateString];
                    const qty = (dateData?.available_quantity ?? dateData?.min_quantity ?? null);
                    const effectiveQty = (qty === null) ? this.totalQuantity : qty;
                    const isAvailable = (dateData?.is_available ?? (effectiveQty > 0));

                    if (!isAvailable || effectiveQty <= 0) {
                        this.availabilityText = `No available for ${dateString}.`;
                        return;
                    }

                    minQty = Math.min(minQty, effectiveQty);
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                if (!isFinite(minQty)) { this.availabilityText = '0 slot(s) available across all nights.'; return; }
                this.availabilityText = `${minQty} slot(s) available across all nights.`;
            },

            updateStaySummary() {
                const checkInVal = this.$refs.checkIn.value;
                const checkOutVal = this.$refs.checkOut ? this.$refs.checkOut.value : '';

                if (!checkInVal) { this.staySummary = ''; return; }
                if (!checkOutVal) { this.staySummary = ''; return; }

                const start = new Date(checkInVal + 'T00:00:00');
                const end   = new Date(checkOutVal + 'T00:00:00');
                const diffMs = end - start;

                if (diffMs <= 0) { this.staySummary = ''; return; }

                const nights = diffMs / (1000 * 60 * 60 * 24);
                const days   = nights + 1;

                const dayLabel   = days === 1 ? 'day' : 'days';
                const nightLabel = nights === 1 ? 'night' : 'nights';

                this.staySummary = `${days} ${dayLabel} and ${nights} ${nightLabel}`;
            }
        }
    }
</script>
@props([
    'active' => 'room',
    'inventory' => null,
    'totalQuantity' => null,
    'dayTourInventory' => null,
    'totalDayTourQuantity' => null,
])

<div class="max-w-7xl mx-auto p-4 relative z-10 -mt-20">
    <div
        class="bg-white rounded-xl shadow-lg p-6"
        x-data='bookingWidget({
            inventory: @json($inventory ?? (object)[]),
            totalQuantity: @json($totalQuantity ?? 0),
            dayTourInventory: @json($dayTourInventory ?? (object)[]),
            totalDayTourQuantity: @json($totalDayTourQuantity ?? 0),
        })'
        x-init="initDatepickers()"
        x-cloak
    >
        <!-- Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 mb-4">
            <button
                type="button"
                @click="setTab('room')"
                :class="activeTab === 'room' ? 'bg-brand text-white shadow-sm' : 'text-gray-600 hover:text-brand'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-house-door"></i>
                <span>Check Room Availability</span>
            </button>
            <button
                type="button"
                @click="setTab('day-visit')"
                :class="activeTab === 'day-visit' ? 'bg-brand text-white shadow-sm' : 'text-gray-600 hover:text-brand'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-sun"></i>
                <span>Day Visit</span>
            </button>
            <button
                type="button"
                @click="setTab('special-event')"
                :class="activeTab === 'special-event' ? 'bg-brand text-white shadow-sm' : 'text-gray-600 hover:text-brand'"
                class="flex-1 px-3 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="bi bi-moon"></i>
                <span>Special Events</span>
            </button>
        </div>

        <hr class="mb-6 border-gray-300">

        <!-- Booking Form for Room and Day Visit -->
        <form id="booking-form" method="POST" :action="formAction" novalidate x-show="activeTab !== 'special-event'">
            @csrf
            <input type="hidden" name="type" x-ref="type" value="room">

            <!-- Hidden fields for guests/rooms -->
            <input type="hidden" name="rooms_count" :value="roomsCount">
            <input type="hidden" name="adults_total" :value="totalAdults">
            <input type="hidden" name="children_total" :value="totalChildren">
            <input type="hidden" name="pets_total" :value="totalPets">
            <!-- Total guests for day-visit -->
            <input type="hidden" name="guests" :value="totalAdults + totalChildren">

            <!-- Promo code (hidden, still submitted) -->
            <input type="hidden" name="promo_code" :value="promoCode">

            <!-- Per-room hidden fields -->
            <template x-for="(room, idx) in rooms" :key="idx">
                <div class="hidden">
                    <input type="hidden" :name="`rooms[${idx+1}][adults]`" :value="room.adults">
                    <input type="hidden" :name="`rooms[${idx+1}][children]`" :value="room.children">
                    <input type="hidden" :name="`rooms[${idx+1}][pets]`" :value="room.pets || 0">
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                {{-- COL 1: CHECK-IN / VISIT DATE --}}
                <div :class="activeTab === 'day-visit' ? 'md:col-span-2' : ''" class="flex flex-col">
                    <div class="relative">
                        <input
                            type="text"
                            name="check_in"
                            id="check_in"
                            autocomplete="off"
                            x-ref="checkIn"
                            class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand outline-none transition-colors text-sm hover:border-brand/70 hover:bg-gray-50"
                            placeholder="Select dates"
                            required
                            readonly
                        >
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500"
                           x-text="activeTab === 'day-visit' ? 'Visit Time (1:00 PM)' : 'Check-in Time (1:00 PM)'"
                           style="font-size: 13px;">
                        </p>

                        <p class="text-xs text-gray-500"
                           x-show="availabilityText"
                           x-text="availabilityText"
                           style="font-size: 13px;">
                        </p>
                    </div>
                </div>

                {{-- COL 2: CHECK-OUT (ROOM ONLY) --}}
                <div x-show="activeTab === 'room'" x-cloak class="flex flex-col">
                    <div class="relative">
                        <input
                            type="text"
                            name="check_out"
                            id="check_out"
                            autocomplete="off"
                            x-ref="checkOut"
                            class="w-full h-12 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand outline-none transition-colors text-sm hover:border-brand/70 hover:bg-gray-50"
                            placeholder="Select dates"
                            required
                            readonly
                        >
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500" style="font-size: 13px;">
                            Check-out Time (11:00 PM)
                        </p>
                    </div>
                </div>

                {{-- COL 3: GUESTS + ROOMS (DROPDOWN PANEL) --}}
                <div class="flex flex-col relative" @click.outside="closeGuestsPanel()">
                    <button
                        type="button"
                        @click.stop="toggleGuestsPanel()"
                        class="w-full h-12 border border-gray-300 rounded-lg text-left px-4 hover:border-brand/70 hover:bg-gray-50 transition-colors flex items-center justify-between"
                        :aria-expanded="isGuestsPanelOpen.toString()"
                        aria-controls="guests-panel"
                    >
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="bi bi-people"></i>
                            <span class="text-sm" x-text="guestsSummary"></span>
                        </div>
                        <i class="bi" :class="isGuestsPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500" style="font-size: 13px;" x-text="activeTab === 'day-visit' ? 'Add guests (Adults/Children)' : 'Add rooms and guests (Adults/Children/Pets)'">
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
                                <h3 class="text-base font-semibold text-gray-800" x-text="activeTab === 'day-visit' ? 'Guests' : 'Rooms & Guests'"></h3>
                                <p class="text-xs text-gray-500 leading-snug" x-text="activeTab === 'day-visit' ? 'Set adults and children' : 'Add up to 4 rooms. Set adults/children/pets per room.'"></p>
                            </div>
                            <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0 self-start mt-0.5" @click="closeGuestsPanel()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <!-- Rooms count - HIDDEN for day-visit -->
                        <div x-show="activeTab !== 'day-visit'" class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between border border-gray-200 rounded-lg sm:rounded-xl p-2.5 sm:p-3 gap-2 sm:gap-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Rooms</p>
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

                        <!-- Simplified Guests Controls for Day Visit -->
                        <div x-show="activeTab === 'day-visit'" class="mt-3 space-y-2 sm:space-y-3">
                            <!-- Adults (Day Visit) -->
                            <div class="flex items-center justify-between border border-gray-200 rounded-lg sm:rounded-xl p-2.5 sm:p-3.5">
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-gray-800">Adults</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-sm"
                                            @click="decAdults(0)"
                                            :disabled="rooms[0].adults <= 1">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <span class="min-w-6 sm:min-w-8 text-center font-semibold text-sm" x-text="rooms[0].adults"></span>
                                    <button type="button"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg border border-gray-300 hover:border-brand/70 text-sm"
                                            @click="incAdults(0)">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Children (Day Visit) -->
                            <div class="flex items-center justify-between border border-gray-200 rounded-lg sm:rounded-xl p-2.5 sm:p-3.5">
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-gray-800">Children</p>
                                    <p class="text-xs text-gray-500">Age 0–12</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-sm"
                                            @click="decChildren(0)"
                                            :disabled="rooms[0].children <= 0">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <span class="min-w-6 sm:min-w-8 text-center font-semibold text-sm" x-text="rooms[0].children"></span>
                                    <button type="button"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg border border-gray-300 hover:border-brand/70 text-sm"
                                            @click="incChildren(0)">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Per-room controls - Room tab only -->
                        <div x-show="activeTab !== 'day-visit'" class="mt-3 space-y-2 sm:space-y-3 max-h-80 md:max-h-96 overflow-y-auto pr-1 scrollbar-thin">
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

                {{-- COL 4: PROMO CODE (DROPDOWN PANEL) --}}
                <div class="flex flex-col relative" @click.outside="closePromoPanel()">
                    <button
                        type="button"
                        @click="togglePromoPanel()"
                        class="w-full h-12 border border-gray-300 rounded-lg text-left px-4 hover:border-brand/70 hover:bg-gray-50 transition-colors flex items-center justify-between"
                        :aria-expanded="isPromoPanelOpen.toString()"
                        aria-controls="promo-panel"
                    >
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="bi bi-ticket-perforated"></i>
                            <span class="text-sm" x-text="promoCode ? 'Promo Applied' : 'Add Promo Code'"></span>
                        </div>
                        <i class="bi" :class="isPromoPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>

                    <div class="min-h-10px mt-1">
                        <p class="text-xs text-gray-500" style="font-size: 13px;">
                            Enter promo code for discounts
                        </p>
                    </div>

                    <!-- Promo code dropdown panel -->
                    <div
                        id="promo-panel"
                        x-show="isPromoPanelOpen"
                        x-cloak
                        x-transition.origin.top
                        class="absolute top-full mt-2 z-9999
                               left-1/2 -translate-x-1/2
                               w-[min(400px,calc(100vw-2rem))]
                               bg-white border border-gray-200 rounded-2xl shadow-xl p-4"
                    >
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
                            <input
                                type="text" id="promo_code" name="promo_code" autocomplete="off"
                                x-model.trim="promoCode"
                                placeholder="Add promo code (Optional)"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand focus:border-brand outline-none text-sm"
                            >
                        </div>

                        <div class="mt-4 flex gap-2 justify-end">
                            <button type="button"
                                    class="px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-sm"
                                    @click="cancelPromo()">
                                Cancel
                            </button>
                            <button type="button"
                                    class="px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand/90 text-sm"
                                    @click="applyPromo()">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>

                {{-- COL 5: SUBMIT --}}
                <div class="flex flex-col">
                    <button
                        type="button"
                        @click="checkAvailability()"
                        class="w-full h-12 bg-brand text-white hover:bg-brand/90 transition-colors font-medium rounded-lg text-sm flex items-center justify-center"
                        id="check-availability-btn"
                    >
                        Check Availability
                    </button>
                    <div class="min-h-10px mt-1"></div>
                </div>
            </div>

            {{-- Stay summary row --}}
            <div class="mt-2 text-xs md:text-sm text-gray-700"
                 x-show="staySummary || activeTab === 'room' || activeTab === 'day-visit' || activeTab === 'special-event'">
                <div class="flex flex-col md:flex-row md:items-baseline gap-1 md:gap-3">
                    <span x-show="staySummary" x-text="staySummary"></span>

                    <span x-show="activeTab === 'room'" class="text-[11px] md:text-xs text-gray-500" style="font-size: 13px;">
                        Select the date range for your room booking.
                    </span>

                    <span x-show="activeTab === 'day-visit'" class="text-[11px] md:text-xs text-gray-500" style="font-size: 13px;">
                        Select the date for your day visit.
                    </span>

                    <span x-show="activeTab === 'special-event'" class="text-[11px] md:text-xs text-gray-500" style="font-size: 13px;">
                        Contact us for special event arrangements.
                    </span>
                </div>
            </div>
        </form>

        <!-- Special Event Contact Section -->
        <div x-show="activeTab === 'special-event'" class="space-y-4">
            <div class="text-center">
                <p class="text-sm text-gray-600 mb-4">
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
                        class="inline-flex items-center gap-2 bg-brand text-white px-6 py-3 rounded-lg font-medium hover:bg-brand/90 transition-colors"
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    .flatpickr-day.flatpickr-disabled { opacity: 0.35; cursor: not-allowed; }
    .flatpickr-day.today { border-color: #a75e3e; }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange { background: #a75e3e; border-color: #a75e3e; color: #ffffff; }
    .flatpickr-day.inRange { background: rgba(167, 94, 62, 0.12); }
    .flatpickr-day.weekend {
        background-color: #a75e3e;
        border-color: #a75e3e;
        color: #ffffff;
        border-radius: 9999px;
    }
    [x-cloak] { display: none !important; }
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

            // inventory
            inventory: config.inventory ?? {},
            totalQuantity: config.totalQuantity ?? 0,

            // day tour
            dayTourInventory: config.dayTourInventory ?? {},
            totalDayTourQuantity: config.totalDayTourQuantity ?? 0,

            // Guests/Rooms dropdown panel
            isGuestsPanelOpen: false,
            toggleGuestsPanel() { this.isGuestsPanelOpen = !this.isGuestsPanelOpen; },
            closeGuestsPanel() { this.isGuestsPanelOpen = false; },

            // Track date selection for Clear button visibility
            hasDateSelection: false,

            roomsCount: 1,
            rooms: [{ adults: 2, children: 0, pets: 0 }],

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

                // For day-visit, show only guests (no pets)
                if (this.activeTab === 'day-visit') {
                    let summary = `${a} ${adultLabel}`;
                    if (c > 0) summary += `, ${c} ${childLabel}`;
                    return summary;
                }

                // For room booking, show rooms and guests including pets
                const roomLabel = rooms === 1 ? 'Room' : 'Rooms';
                let summary = `${rooms} ${roomLabel}, ${a} ${adultLabel}`;
                if (c > 0) summary += `, ${c} ${childLabel}`;
                if (p > 0) summary += `, ${p} ${petLabel}`;
                return summary;
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
                    while (this.rooms.length < next) this.rooms.push({ adults: 2, children: 0, pets: 0 });
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
                this.rooms = [{ adults: 2, children: 0, pets: 0 }];
            },

            clearDates() {
                // Clear date inputs
                if (this.$refs.checkIn) this.$refs.checkIn.value = '';
                if (this.$refs.checkOut) this.$refs.checkOut.value = '';
                this.hasDateSelection = false;
                // Close the guests panel after clearing
                this.isGuestsPanelOpen = false;
            },

            // Promo code
            isPromoPanelOpen: false,
            promoCode: '',

            togglePromoPanel() { this.isPromoPanelOpen = !this.isPromoPanelOpen; },
            closePromoPanel() { this.isPromoPanelOpen = false; },
            cancelPromo() {
                this.promoCode = '';
                this.isPromoPanelOpen = false;
            },
            applyPromo() {
                // keep promoCode value for form submit (hidden input)
                this.isPromoPanelOpen = false;
            },

            checkAvailability() {
                if (this.activeTab === 'room') {
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
                } else if (this.activeTab === 'day-visit') {
                    if (!this.$refs.checkIn?.value) {
                        Swal.fire({
                            title: 'Please Select Date',
                            text: 'You need to select a visit date.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#a75e3e'
                        });
                        return;
                    }
                }
                document.getElementById('booking-form').submit();
            },

            setTab(type) {
                this.activeTab = type;
                this.$refs.type.value = type;

                // close dropdown/promo when switching tabs
                this.isGuestsPanelOpen = false;
                this.isPromoPanelOpen = false;

                if (type === 'room') {
                    this.formAction = '{{ url("/booking/staycation") }}';
                } else if (type === 'day-visit') {
                    this.formAction = '{{ url("/booking/dayvisit/availability") }}';
                } else {
                    this.formAction = '{{ url("/booking") }}';
                }

                if (type === 'day-visit') {
                    if (this.$refs.checkOut) this.$refs.checkOut.value = '';
                }

                if (this.fpRange) {
                    this.fpRange.set('mode', type === 'day-visit' ? 'single' : 'range');
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
                        instance.set('mode', self.activeTab === 'day-visit' ? 'single' : 'range');
                    },
                    onChange(selectedDates, dateStr, instance) {
                        if (self.activeTab === 'day-visit') {
                            if (selectedDates.length > 0) {
                                const inStr = instance.formatDate(selectedDates[0], 'Y-m-d');
                                self.$refs.checkIn.value = inStr;
                                self.hasDateSelection = true;
                            }
                            if (self.$refs.checkOut) self.$refs.checkOut.value = '';
                            self.updateAvailabilityText();
                            self.updateStaySummary();
                            return;
                        }

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

                this.fpRange.set('mode', this.activeTab === 'day-visit' ? 'single' : 'range');
            },

            markWeekends(dayElem) {
                if (!dayElem || !dayElem.dateObj) return;
                const day = dayElem.dateObj.getDay();
                if (day === 0 || day === 6) dayElem.classList.add('weekend');
            },

            updateAvailabilityText() {
                const checkInVal = this.$refs.checkIn.value;
                const checkOutVal = this.$refs.checkOut ? this.$refs.checkOut.value : '';

                if (!checkInVal) { this.availabilityText = ''; return; }

                if (this.activeTab === 'day-visit') {
                    const dateData = this.dayTourInventory[checkInVal];
                    if (!dateData) {
                        this.availabilityText = `Up to ${this.totalDayTourQuantity} slot(s) available for day visits.`;
                        return;
                    }
                    const quantity = dateData.available_quantity ?? dateData.min_quantity ?? 0;
                    const isAvailable = dateData.is_available ?? (quantity > 0);

                    this.availabilityText = (!isAvailable || quantity <= 0)
                        ? 'Fully booked for this date.'
                        : `${quantity} slot(s) available for day visits.`;
                    return;
                }

                if (!checkOutVal) {
                    const dateData = this.inventory[checkInVal];
                    if (!dateData) {
                        this.availabilityText = `Up to ${this.totalQuantity} slot(s) available per day (total across all staycations).`;
                        return;
                    }
                    const quantity = dateData.available_quantity ?? dateData.min_quantity ?? 0;
                    const isAvailable = dateData.is_available ?? (quantity > 0);

                    this.availabilityText = (!isAvailable || quantity <= 0)
                        ? 'Fully booked for this date.'
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
                        this.availabilityText = `Fully booked for ${dateString}.`;
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
                if (this.activeTab === 'day-visit') { this.staySummary = 'Day visit only'; return; }
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
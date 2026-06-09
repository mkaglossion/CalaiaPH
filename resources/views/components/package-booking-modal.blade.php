@props([
    'package'
])

<div 
    x-show="showBookingModal"
    x-cloak
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-end sm:items-center justify-center z-50 p-0 sm:p-4"
    x-transition.opacity
    @click.away="showBookingModal = false"
>
    <div class="bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-y-auto border border-gray-200">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-white px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-start gap-3 shadow-sm">
            <div class="flex-1 min-w-0">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 line-clamp-1">Book: <span class="text-brand" x-text="package.name"></span></h2>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Fill in your details to proceed</p>
            </div>
            <button @click="showBookingModal = false" class="text-gray-400 hover:text-gray-600 text-2xl sm:text-3xl leading-none shrink-0 mt-0.5">&times;</button>
        </div>

        <x-booking-progress
            :steps="[
                ['label' => 'Details', 'status' => 'current'],
                ['label' => 'Payment', 'status' => 'pending'],
                ['label' => 'Confirmation', 'status' => 'pending'],
            ]"
        />

        <!-- Error Messages -->
        <template x-if="errors.length > 0">
            <div class="px-4 sm:px-6 pt-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                    <h4 class="font-semibold text-red-800 mb-2 flex items-center gap-2 text-sm">
                        <i class="bi bi-exclamation-circle shrink-0"></i>
                        <span>Please fix errors below</span>
                    </h4>
                    <ul class="space-y-1 text-red-700 text-xs sm:text-sm">
                        <template x-for="error in errors" :key="error">
                            <li class="flex gap-2">
                                <span class="shrink-0">•</span>
                                <span x-text="error"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </template>

        <!-- Form -->
        <form @submit.prevent="processBooking()" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            @csrf

            <!-- Booking Details Section -->
            <div class="bg-gray-50 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Booking Details</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Date -->
                    <div>
                        <label for="booking_date" class="block font-semibold text-gray-700 mb-1.5 text-sm">Date *</label>
                        <input
                            type="date"
                            id="booking_date"
                            name="booking_date"
                            x-model="form.date"
                            :min="getMinDate()"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Number of Guests -->
                    <div>
                        <label for="total_guests" class="block font-semibold text-gray-700 mb-1.5 text-sm" x-text="package.pricing_type === 'per_group' ? 'Group Size *' : 'Guests *'"></label>
                        <div class="flex gap-2 items-end">
                            <input
                                type="number"
                                id="total_guests"
                                name="total_guests"
                                x-model.number="form.totalGuests"
                                min="1"
                                :max="package.max_guests"
                                required
                                class="flex-1 border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                            <span class="text-xs sm:text-sm text-gray-500 font-medium whitespace-nowrap" x-text="package.pricing_type === 'per_group' ? ('Max per group: ' + package.max_guests) : ('Max: ' + package.max_guests)"></span>
                        </div>
                    </div>

                    <!-- Session/Time (if available) -->
                    <template x-if="package.sessions && package.sessions.length > 0">
                        <div class="sm:col-span-2">
                            <label for="session" class="block font-semibold text-gray-700 mb-1.5 text-sm">Preferred Time *</label>
                            <select
                                id="session"
                                name="session"
                                x-model="form.session"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                                <option value="">-- Select Time --</option>
                                <template x-for="session in package.sessions" :key="session.name">
                                    <option :value="session.name" x-text="session.name + ': ' + formatSessionTimeRange(session)"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="bg-gray-50 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Contact Information</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block font-semibold text-gray-700 mb-1.5 text-sm">First Name *</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            x-model="form.firstName"
                            autocomplete="given-name"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block font-semibold text-gray-700 mb-1.5 text-sm">Last Name *</label>
                        <input 
                            type="text" 
                            id="last_name"
                            x-model="form.lastName"
                            autocomplete="family-name"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Phone -->
                    <div class="sm:col-span-2">
                        <label for="phone" class="block font-semibold text-gray-700 mb-1.5 text-sm">Phone *</label>
                        <div class="flex gap-2 items-center">
                            <span class="hidden sm:block px-3 py-2 sm:py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 font-medium text-sm whitespace-nowrap">+63</span>
                            <span class="sm:hidden px-2 py-2 bg-gray-100 border border-gray-300 rounded text-gray-600 font-medium text-xs whitespace-nowrap">+63</span>
                            <input 
                                type="tel" 
                                id="phone"
                                x-model="form.phone"
                                autocomplete="tel"
                                placeholder="9XXXXXXXXX"
                                maxlength="10"
                                inputmode="numeric"
                                pattern="[0-9]{10}"
                                required
                                class="flex-1 border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="sm:col-span-2">
                        <label for="email" class="block font-semibold text-gray-700 mb-1.5 text-sm">Email *</label>
                        <input 
                            type="email" 
                            id="email"
                            x-model="form.email"
                            autocomplete="email"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>
                </div>
            </div>

            <!-- Special Requests -->
            <div>
                <label for="special_requests" class="block font-semibold text-gray-700 mb-1.5 text-sm">Special Requests</label>
                <textarea 
                    id="special_requests"
                    x-model="form.specialRequests"
                    placeholder="Dietary requirements, allergies, etc."
                    rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition resize-none"></textarea>
            </div>

            <!-- Price Summary -->
            <div class="bg-linear-to-r from-brand/10 to-brand/5 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-brand/20">
                <div class="space-y-2">
                    <div class="flex justify-between text-xs sm:text-sm text-gray-600">
                        <span x-text="package.pricing_type === 'per_group' ? 'Price per group:' : 'Price per head:'"></span>
                        <span x-text="'₱' + formatPrice(package.price)" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between text-xs sm:text-sm text-gray-600" x-show="package.pricing_type !== 'per_group' && form.totalGuests > 1">
                        <span x-text="'For ' + form.totalGuests + ' guests:'"></span>
                        <span x-text="'₱' + formatPrice(totalPrice)" class="font-medium"></span>
                    </div>
                    <div class="border-t border-brand/30 pt-2 flex justify-between text-base sm:text-lg font-bold text-brand">
                        <span>Total:</span>
                        <span x-text="'₱' + formatPrice(totalPrice)"></span>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="grid gap-2 sm:gap-3 pt-2 grid-cols-2">
                <button
                    type="button"
                    @click="showBookingModal = false"
                    class="px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-semibold text-gray-700 text-sm sm:text-base">
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="px-4 py-2.5 sm:py-3 rounded-lg bg-brand hover:bg-[#a0675a] text-white font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base flex items-center justify-center gap-2">
                    <span x-show="!isLoading"><i class="bi bi-credit-card"></i> Pay Now</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <i class="bi bi-hourglass-split animate-spin"></i>
                        <span class="hidden sm:inline">Processing...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

@props(['active' => 'room'])

<div class="max-w-7xl mx-auto p-4 relative z-10 -mt-20">
    <div class="bg-white rounded-xl shadow-lg p-6" x-data="{ activeTab: 'room' }">
        <!-- Tabs and Special Arrangements -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button
                    @click="activeTab = 'room'; $refs.bookingType.value = 'room'"
                    :class="activeTab === 'room' ? 'bg-[#8f5d54] text-white shadow-sm' : 'text-gray-600 hover:text-[#8f5d54]'"
                    class="px-6 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <i class="bi bi-house-door"></i>
                    Room
                </button>
                <button
                    @click="activeTab = 'day-visit'; $refs.bookingType.value = 'day-visit'"
                    :class="activeTab === 'day-visit' ? 'bg-[#8f5d54] text-white shadow-sm' : 'text-gray-600 hover:text-[#8f5d54]'"
                    class="px-6 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <i class="bi bi-sun"></i>
                    Day Visit
                </button>
                <button
                    @click="activeTab = 'overnight'; $refs.bookingType.value = 'overnight'"
                    :class="activeTab === 'overnight' ? 'bg-[#8f5d54] text-white shadow-sm' : 'text-gray-600 hover:text-[#8f5d54]'"
                    class="px-6 py-3 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <i class="bi bi-moon"></i>
                    Overnight
                </button>
            </div>

            <!-- Special Arrangements Text -->
            <div class="text-sm text-gray-600">
                Special Arrangements, Group Bookings – Please message us
                <a href="https://m.me/410774518796277" class="text-blue-500 hover:underline font-medium" target="_blank" rel="noopener noreferrer">here</a>
            </div>
        </div>

        <!-- Divider -->
        <hr class="mb-6 border-gray-300">

        <form method="POST" action="{{ url('/booking') }}" novalidate>
            <input type="hidden" name="booking_type" x-ref="bookingType" value="room">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Check-in / Visit Date -->
                <div :class="activeTab === 'day-visit' ? 'md:col-span-2' : 'md:col-span-1'">
                    <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2" x-text="activeTab === 'day-visit' ? 'Visit Date' : 'Check-in'">
                    </label>
                    <input
                        type="text"
                        name="check_in"
                        id="check_in"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none transition-colors text-sm"
                        placeholder="Select date"
                        required
                        readonly
                    >
                </div>

                <!-- Check-out -->
                <div x-show="activeTab !== 'day-visit'" class="md:col-span-1">
                    <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2">
                        Check-out
                    </label>
                    <input
                        type="text"
                        name="check_out"
                        id="check_out"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none transition-colors text-sm"
                        placeholder="Select date"
                        :required="activeTab !== 'day-visit'"
                        readonly
                    >
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
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

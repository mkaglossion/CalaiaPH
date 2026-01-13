{{-- resources/views/booking/staycation-availability.blade.php --}}
<x-app-layout active-header="booking">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-[#f5efe7] min-h-screen">
        <div class="pt-6 px-4 sm:px-6 lg:px-12">
            {{-- Header --}}
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold tracking-tight" style="font-family:'Gotham Book', sans-serif;">Select Room(s)</h1>
                <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
                <p class="text-xs text-gray-500" style="font-family:'Gotham Book', sans-serif;">
                    Select your preferred accommodation + guests + pet options
                </p>
            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <x-booking.progress-indicator :stepNumber="1" :totalSteps="3" :bookingType="'staycation'" />
            </div>

            {{-- Back + Guest mode --}}
            <div class="flex items-end justify-between mb-6">
                <a href="{{ route('booking.staycation') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <i class="bi bi-arrow-left mr-2"></i>
                    Back to Dates
                </a>

                @if (session()->has('user.is_guest') && session('user.is_guest'))
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                          style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                        <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                    </span>
                @endif
            </div>

            <div
                x-data="staycationAvailabilityMerged()"
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10"
            >
                {{-- LEFT --}}
                <div class="lg:col-span-2">
                    {{-- Filter --}}
                    <div class="mb-6 flex items-end gap-3 flex-wrap">
                        <div>
                            <label for="roomType" class="block text-sm font-medium text-gray-700 mb-2" style="font-family:'Gotham Book', sans-serif;">Filter by Type:</label>
                            <select id="roomType" x-model="roomType"
                                    class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                <option value="all">All Types</option>
                                <option value="glamping">Glamping</option>
                                <option value="camping">Camping</option>
                            </select>
                        </div>

                        <button type="button"
                                class="ml-auto px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="selectedCount() === 0"
                        >
                            Reset All
                        </button>
                    </div>

                    {{-- Rooms Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($staycations as $staycation)
                            @php
                                $roomDetail = $roomDetails[$staycation->id];
                                $avail = $availability[$staycation->id];
                                $status = $roomDetail['status']; // available | limited | unavailable
                                $bgClass = match($status) {
                                    'available' => 'bg-green-500',
                                    'limited' => 'bg-yellow-500',
                                    default => 'bg-gray-500'
                                };
                            @endphp

                            <div
                                class="relative bg-white rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl
                                {{ $status === 'available' ? 'ring-2 ring-green-200 hover:ring-green-300' : ($status === 'limited' ? 'ring-2 ring-yellow-200 hover:ring-yellow-300' : 'ring-2 ring-gray-200') }}"
                                data-type="{{ $roomDetail['type'] }}"
                                x-show="roomType === 'all' || roomType === '{{ $roomDetail['type'] }}'"
                            >
                                {{-- Selected indicator --}}
                                <div class="absolute top-2 left-2 z-10" x-show="isSelected({{ $staycation->id }})" x-transition>
                                    <div class="w-6 h-6 bg-[#8f5d54] rounded-full flex items-center justify-center">
                                        <i class="bi bi-check text-white text-sm"></i>
                                    </div>
                                </div>

                                {{-- Image --}}
                                <div class="relative">
                                    <img src="{{ $roomDetail['image'] }}" alt="{{ $roomDetail['name'] }}" class="w-full h-96 object-cover">

                                    {{-- Status badge --}}
                                    <div class="absolute top-4 right-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold text-white {{ $bgClass }}">
                                            @if($status === 'available')
                                                <i class="bi bi-check-circle-fill mr-1"></i>Available
                                            @elseif($status === 'limited')
                                                <i class="bi bi-exclamation-triangle-fill mr-1"></i>Limited
                                            @else
                                                <i class="bi bi-x-circle-fill mr-1"></i>Unavailable
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Type badge --}}
                                    <div class="absolute bottom-4 left-4">
                                        <span class="px-3 py-1 bg-[#8f5d54] text-white text-sm font-semibold rounded-full">
                                            {{ ucfirst($roomDetail['type']) }}
                                        </span>
                                    </div>

                                    {{-- Qty badge --}}
                                    @if(($avail['min_quantity'] ?? 0) > 0)
                                        <div class="absolute bottom-4 right-4">
                                            <span class="px-2 py-1 bg-black/70 text-white text-xs font-semibold rounded-full">
                                                {{ $avail['min_quantity'] }} available
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-bold mb-2" style="font-family:'Gotham Book', sans-serif;">
                                        {{ $roomDetail['name'] }}
                                    </h3>

                                        <span class="text-lg font-bold text-[#8f5d54]">
                                            ₱{{ number_format($roomDetail['price'] ?? ($roomDetail['type'] === 'glamping' ? 5500 : 3000), 0, ',', ',') }}
                                        </span>

                                    <p class="text-gray-600 mb-4 text-sm">
                                        {{ $roomDetail['description'] }}
                                    </p>

                                    {{-- Availability info --}}
                                    @if($status === 'limited')
                                        <div class="text-sm text-amber-700 mb-4 bg-amber-50 p-3 rounded-lg border border-amber-200">
                                            <i class="bi bi-exclamation-triangle mr-1"></i>
                                            Only {{ $avail['available_nights'] }}/{{ $avail['total_nights'] }} nights available
                                        </div>
                                    @elseif($status === 'available')
                                        <div class="text-sm text-green-700 mb-4 bg-green-50 p-3 rounded-lg border border-green-200">
                                            <i class="bi bi-check-circle mr-1"></i>
                                            Fully available for your dates
                                        </div>
                                    @endif

                                    {{-- Buttons --}}
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click.stop="showCalendar('{{ $roomDetail['type'] }}', {{ $staycation->id }})"
                                            class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-semibold hover:bg-gray-200 transition text-sm"
                                        >
                                            <i class="bi bi-calendar mr-1"></i>Calendar
                                        </button>

                                        <button
                                            type="button"
                                            class="flex-1 py-2 px-4 rounded-lg font-semibold transition text-sm"
                                            @click.stop="toggleRoom({{ $staycation->id }}, '{{ $roomDetail['type'] }}')"
                                            {{ $status !== 'available' ? 'disabled' : '' }}
                                            :class="isSelected({{ $staycation->id }})
                                                ? 'bg-green-600 text-white hover:bg-green-700'
                                                : '{{ $status === 'available' ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}'
                                            "
                                        >
                                            <template x-if="isSelected({{ $staycation->id }})">
                                                <span><i class="bi bi-check2-circle mr-1"></i>Selected</span>
                                            </template>
                                            <template x-if="!isSelected({{ $staycation->id }})">
                                                <span><i class="bi bi-plus-circle mr-1"></i>Select</span>
                                            </template>
                                        </button>
                                    </div>

                                    {{-- Inline edit panel (shows when selected) --}}
                                    <div x-show="isSelected({{ $staycation->id }})" x-transition class="mt-4 border-t pt-4">
                                        <div class="text-sm font-semibold text-gray-800 mb-3">
                                            Guest details (edit anytime)
                                        </div>

                                        <div class="grid grid-cols-3 gap-2">
                                            <div>
                                                <label class="text-xs text-gray-600">Adults</label>
                                                <input type="number" min="1" max="20"
                                                       class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                       :value="getRoom({{ $staycation->id }}).adults"
                                                       @input="setField({{ $staycation->id }}, 'adults', $event.target.value)"
                                                >
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-600">Children</label>
                                                <input type="number" min="0" max="20"
                                                       class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                       :value="getRoom({{ $staycation->id }}).children"
                                                       @input="setField({{ $staycation->id }}, 'children', $event.target.value)"
                                                >
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-600">Extra Mattress</label>
                                                <input type="number" min="0" max="8"
                                                       class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54]"
                                                       :value="getRoom({{ $staycation->id }}).extra_mattress"
                                                       @input="setField({{ $staycation->id }}, 'extra_mattress', $event.target.value)"
                                                >
                                            </div>
                                        </div>

                                        {{-- Pet policy per room --}}
                                        <div class="mt-4 border-t pt-4">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm font-semibold text-gray-800">Pets</div>
                                                <button type="button"
                                                        class="text-xs text-blue-700 underline hover:text-blue-900"
                                                        @click="petPolicyModal=true"
                                                >
                                                    View Pet Policy
                                                </button>
                                            </div>

                                            <div class="mt-2 flex gap-4 text-sm">
                                                <label class="inline-flex items-center gap-2">
                                                    <input type="radio"
                                                           :name="'pets_'+{{ $staycation->id }}"
                                                           value="no"
                                                           :checked="getRoom({{ $staycation->id }}).pets === 'no'"
                                                           @change="setField({{ $staycation->id }}, 'pets', 'no')"
                                                    >
                                                    <span>No pets</span>
                                                </label>
                                                <label class="inline-flex items-center gap-2">
                                                    <input type="radio"
                                                           :name="'pets_'+{{ $staycation->id }}"
                                                           value="yes"
                                                           :checked="getRoom({{ $staycation->id }}).pets === 'yes'"
                                                           @change="setField({{ $staycation->id }}, 'pets', 'yes')"
                                                    >
                                                    <span>Bringing pets (+₱2,000/pet)</span>
                                                </label>
                                            </div>

                                            <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3"
                                                 x-show="getRoom({{ $staycation->id }}).pets === 'yes'"
                                                 x-transition
                                            >
                                                <label class="inline-flex items-start gap-2 text-sm text-blue-900">
                                                    <input type="checkbox"
                                                           class="mt-1"
                                                           :checked="getRoom({{ $staycation->id }}).pet_agreement"
                                                           @change="setField({{ $staycation->id }}, 'pet_agreement', $event.target.checked)"
                                                    >
                                                    <span>
                                                        I agree to the pet policy
                                                        <span class="block text-xs text-blue-800 mt-1">
                                                            Max 2 pets. Fee required to confirm.
                                                        </span>
                                                    </span>
                                                </label>

                                                <div class="mt-3">
                                                    <label class="text-xs text-gray-700">Number of pets</label>
                                                    <input type="number" min="0" max="2"
                                                           class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                           :disabled="!getRoom({{ $staycation->id }}).pet_agreement"
                                                           :value="getRoom({{ $staycation->id }}).num_pets"
                                                           @input="setField({{ $staycation->id }}, 'num_pets', $event.target.value)"
                                                    >
                                                    <div class="text-xs text-gray-600 mt-1" x-show="!getRoom({{ $staycation->id }}).pet_agreement">
                                                        Please check agreement to enable pets count.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Quick remove --}}
                                        <div class="mt-4">
                                            <button type="button"
                                                    class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-200 transition"
                                                    @click="toggleRoom({{ $staycation->id }}, '{{ $roomDetail['type'] }}')"
                                            >
                                                Remove this room
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12 bg-white rounded-2xl shadow border border-gray-200">
                                <div class="text-gray-500">
                                    <i class="bi bi-calendar-x text-6xl mb-4 block text-gray-300"></i>
                                    <h3 class="text-2xl font-semibold mb-2 text-gray-700">No Staycations Available</h3>
                                    <p class="text-gray-500 mb-4">No staycations are configured in the system.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 lg:sticky lg:top-6">
                        <h3 class="text-xl font-bold mb-6" style="font-family:'Gotham Book', sans-serif;">Booking Summary</h3>

                        {{-- Dates --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-calendar-check mr-2 text-[#8f5d54]"></i> Selected Dates
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Check-in</span>
                                    <span class="font-semibold">{{ \Carbon\Carbon::parse($checkin)->format('M j, Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Check-out</span>
                                    <span class="font-semibold">{{ \Carbon\Carbon::parse($checkout)->format('M j, Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center border-t pt-3">
                                    <span class="text-sm text-gray-600">Total Nights</span>
                                    <span class="font-semibold text-[#8f5d54]">
                                        {{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Selected rooms list --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-house mr-2 text-[#8f5d54]"></i> Selected Rooms
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <template x-if="selectedCount() === 0">
                                    <div class="text-sm text-gray-600">No rooms selected yet.</div>
                                </template>

                                <template x-for="item in Object.values(selectedRooms)" :key="item.id">
                                    <div class="bg-white rounded-lg border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="font-semibold text-gray-900 text-sm" x-text="item.name"></div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    Adults: <span class="font-semibold" x-text="item.adults"></span>,
                                                    Children: <span class="font-semibold" x-text="item.children"></span>,
                                                    Extra Mattress: <span class="font-semibold" x-text="item.extra_mattress"></span>
                                                </div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    Pets: <span class="font-semibold" x-text="item.pets"></span>
                                                    <template x-if="item.pets === 'yes'">
                                                        <span> • # pets: <span class="font-semibold" x-text="item.num_pets"></span></span>
                                                    </template>
                                                </div>
                                                <div class="text-xs text-gray-600 mt-2 space-y-1">
                                                    <div class="text-xs text-gray-500">Price Breakdown:</div>
                                                    <div class="pl-2 space-y-0.5">
                                                        <div>Accommodation: <span class="font-semibold text-green-600" x-text="calculateBasePrice(item)"></span></div>
                                                        <div x-show="item.extra_mattress > 0">Extra Mattress: <span class="font-semibold text-green-600" x-text="calculateMattressPrice(item)"></span></div>
                                                        <div x-show="item.pets === 'yes' && item.num_pets > 0">Pets: <span class="font-semibold text-green-600" x-text="calculatePetPrice(item)"></span></div>
                                                        <div class="border-t pt-1 font-semibold">Total: <span class="text-green-600" x-text="calculateRoomPrice(item)"></span></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button"
                                                    class="text-xs text-red-600 hover:text-red-800 font-semibold"
                                                    @click="removeRoom(item.id)"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Total Price --}}
                        <div class="mb-6" x-show="selectedCount() > 0">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-cash mr-2 text-[#8f5d54]"></i> Estimated Total
                            </h4>

                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="text-2xl font-bold text-green-600" x-text="calculateTotalPrice()"></div>
                                <div class="text-sm text-green-700 mt-1">Includes accommodation + extras (final price calculated at checkout)</div>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="space-y-3">
                            <button
                                type="button"
                                @click="submitAndProceed()"
                                class="w-full bg-[#8f5d54] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#7a4e48] transition disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="selectedCount() === 0"
                            >
                                Continue to Activities <i class="bi bi-arrow-right ml-2"></i>
                            </button>

                            <button
                                type="button"
                                class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-semibold hover:bg-gray-200 transition"
                                @click="resetAll()"
                                :disabled="selectedCount() === 0"
                            >
                                Clear Selection
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Calendar Modal --}}
                <div
                    x-show="calendarModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="calendarModal = false"
                >
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold" x-text="`Availability Calendar - ${currentRoomName}`"></h3>
                            <button type="button" @click="calendarModal = false" class="text-gray-500 hover:text-gray-800">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="mx-auto" style="max-width:400px;">
                            <div x-ref="calendar"></div>
                        </div>

                        <div class="mt-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-block w-4 h-4 rounded" style="background:#d4edda;"></span>
                                <span>Available</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-4 rounded" style="background:#f8d7da;"></span>
                                <span>Not Available</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pet Policy Modal --}}
                <div x-show="petPolicyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                     @click.self="petPolicyModal=false">
                    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl p-6 overflow-y-auto" style="max-height: 90vh;">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Pet Policy</h3>
                            <button type="button" class="text-gray-500 hover:text-gray-800" @click="petPolicyModal=false">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="text-sm text-gray-700 space-y-3">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold">Owner Responsibility</div>
                                <div>Pet owners are responsible at all times. Pets must not be left unattended.</div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold">Leash Policy</div>
                                <div>Pets may be off-leash only under close and careful supervision.</div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="font-semibold">Overnight Stays & Pet Fee</div>
                                <div>₱2,000 per pet fee applies when staying inside. Any damages charged accordingly.</div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="font-semibold text-red-900">Damage & Liability</div>
                                <div class="text-red-800">Owners will be held liable for any damage/injury caused by pets.</div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold" @click="petPolicyModal=false">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function staycationAvailabilityMerged() {
            return {
                roomType: 'all',

                // modals
                calendarModal: false,
                petPolicyModal: false,
                currentRoomId: null,
                currentRoomName: '',
                calendarInstance: null,

                // selected rooms map: { [id]: {id,name,type,adults,children,extra_mattress,pets,pet_agreement,num_pets} }
                selectedRooms: {},

                toggleRoom(roomId, type) {
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);

                    if (!room) return;

                    if (this.selectedRooms[roomId]) {
                        // deselect
                        delete this.selectedRooms[roomId];
                        return;
                    }

                    // check maximum selection limit
                    if (this.selectedCount() >= 4) {
                        Swal.fire('Maximum Selection Reached', 'You can select up to 4 accommodations only.', 'warning');
                        return;
                    }

                    // select with defaults
                    const defaultAdults = (type || '').toLowerCase() === 'glamping' ? 2 : 1;
                    this.selectedRooms[roomId] = {
                        id: roomId,
                        name: room.name || 'Room',
                        type: (type || '').toLowerCase(),
                        adults: defaultAdults,
                        children: 0,
                        extra_mattress: 0,
                        pets: 'no',
                        pet_agreement: false,
                        num_pets: 0,
                    };
                },

                removeRoom(roomId) {
                    if (this.selectedRooms[roomId]) delete this.selectedRooms[roomId];
                },

                isSelected(roomId) {
                    return !!this.selectedRooms[roomId];
                },

                selectedCount() {
                    return Object.keys(this.selectedRooms).length;
                },

                getRoom(roomId) {
                    // for bindings
                    if (!this.selectedRooms[roomId]) {
                        return {
                            adults: 1, children: 0, extra_mattress: 0,
                            pets: 'no', pet_agreement: false, num_pets: 0
                        };
                    }
                    return this.selectedRooms[roomId];
                },

                setField(roomId, field, value) {
                    if (!this.selectedRooms[roomId]) return;

                    // normalize numbers
                    if (['adults','children','extra_mattress','num_pets'].includes(field)) {
                        value = parseInt(value || 0, 10);
                        if (!isFinite(value)) value = 0;
                        if (field === 'adults' && value < 1) value = 1;
                        if (['children','extra_mattress','num_pets'].includes(field) && value < 0) value = 0;
                        if (field === 'num_pets' && value > 2) value = 2;
                    }

                    this.selectedRooms[roomId][field] = value;

                    // pet logic
                    if (field === 'pets' && value === 'no') {
                        this.selectedRooms[roomId].pet_agreement = false;
                        this.selectedRooms[roomId].num_pets = 0;
                    }
                    if (field === 'pet_agreement' && value === false) {
                        this.selectedRooms[roomId].num_pets = 0;
                    }
                },

                resetAll() {
                    this.selectedRooms = {};
                },

                showCalendar(type, roomId) {
                    this.currentRoomId = roomId;
                    const staycations = @json($staycations);
                    const room = staycations.find(s => s.id === roomId);
                    this.currentRoomName = room ? room.name : 'Room';

                    this.calendarModal = true;
                    this.$nextTick(() => this.initCalendar());
                },

                initCalendar() {
                    if (this.calendarInstance) this.calendarInstance.destroy();

                    const availabilityData = @json($availability);
                    const checkin = '{{ $checkin }}';
                    const checkout = '{{ $checkout }}';

                    this.calendarInstance = flatpickr(this.$refs.calendar, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        defaultDate: [checkin, checkout],
                        minDate: new Date(),

                        onReady: (selectedDates, dateStr, instance) => {
                            instance.calendarContainer.style.background = 'white';
                            instance.calendarContainer.style.borderRadius = '12px';
                            instance.calendarContainer.style.boxShadow = '0 10px 25px rgba(0,0,0,0.10)';
                            instance.calendarContainer.style.padding = '8px';
                        },

                        onDayCreate: (selectedDates, dateStr, instance, dayElem) => {
                            const date = dayElem.dateObj;
                            const dateString = instance.formatDate(date, 'Y-m-d');
                            dayElem.style.borderRadius = '6px';

                            // color only in selected range (same as your old logic)
                            if (dateString >= checkin && dateString < checkout) {
                                const stayAvail = availabilityData[this.currentRoomId];
                                let isAvailable = false;

                                if (stayAvail && stayAvail.inventory_details && stayAvail.inventory_details[dateString]) {
                                    isAvailable = !!stayAvail.inventory_details[dateString].is_available;
                                }

                                if (isAvailable) {
                                    dayElem.style.background = '#d4edda';
                                    dayElem.style.color = '#155724';
                                } else {
                                    dayElem.style.background = '#f8d7da';
                                    dayElem.style.color = '#721c24';
                                }
                            }
                        }
                    });
                },

                async calculateBasePrice(room) {
                    const nights = {{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }};
                    const totalGuests = (room.adults || 0) + (room.children || 0);

                    try {
                        // Get dynamic price from backend
                        const response = await fetch('{{ route("booking.calculatePrice") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                unit_type: 'staycation-' + room.id,
                                check_in: '{{ $checkin }}',
                                check_out: '{{ $checkout }}',
                                guests: totalGuests
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            return '₱' + (data.total_price * nights).toLocaleString();
                        }
                    } catch (e) {
                        console.error('Error calculating price:', e);
                    }

                    // Fallback to hardcoded pricing
                    const basePrice = room.type === 'glamping' ? 5500 : 3000;
                    // Add guest surcharge: base covers 2 guests, ₱500 per additional guest
                    const baseGuests = 2;
                    const extraGuestRate = 500;
                    const extraGuests = Math.max(0, totalGuests - baseGuests);
                    const adjustedPrice = basePrice + (extraGuests * extraGuestRate);

                    return '₱' + (adjustedPrice * nights).toLocaleString();
                },

                calculateMattressPrice(room) {
                    const nights = {{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }};
                    const price = (room.extra_mattress || 0) * 500 * nights;
                    return price > 0 ? '₱' + price.toLocaleString() : '';
                },

                calculatePetPrice(room) {
                    if (room.pets !== 'yes' || !room.num_pets) return '';
                    const price = room.num_pets * 2000;
                    return '₱' + price.toLocaleString();
                },

                calculateRoomPrice(room) {
                    const base = this.calculateBasePrice(room).replace(/[₱,]/g, '') * 1;
                    const mattress = this.calculateMattressPrice(room).replace(/[₱,]/g, '') * 1 || 0;
                    const pet = this.calculatePetPrice(room).replace(/[₱,]/g, '') * 1 || 0;
                    return '₱' + (base + mattress + pet).toLocaleString();
                },

                calculateTotalPrice() {
                    let total = 0;
                    for (const room of Object.values(this.selectedRooms)) {
                        const price = this.calculateRoomPrice(room).replace(/[₱,]/g, '') * 1;
                        total += price;
                    }
                    return '₱' + total.toLocaleString();
                },

                submitAndProceed() {
                    if (this.selectedCount() === 0) {
                        Swal.fire('Select at least one room', 'Please select a room before continuing.', 'warning');
                        return;
                    }

                    // validation: pet agreement required if pets yes and num pets > 0
                    for (const r of Object.values(this.selectedRooms)) {
                        if (r.pets === 'yes') {
                            if (!r.pet_agreement) {
                                Swal.fire('Pet policy required', `Please agree to the pet policy for "${r.name}".`, 'warning');
                                return;
                            }
                            if (!r.num_pets || r.num_pets < 1) {
                                Swal.fire('Pet count required', `Please set number of pets (1-2) for "${r.name}".`, 'warning');
                                return;
                            }
                        }
                    }

                    fetch('{{ route("booking.staycation") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            selected_rooms: this.selectedRooms,
                            proceed_to_activities: true
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '{{ route("booking.staycationActivities") }}';
                        } else {
                            Swal.fire('Error', data.message || 'Failed to save selection', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Network error saving selection', 'error'));
                }
            }
        }
    </script>
</x-app-layout>

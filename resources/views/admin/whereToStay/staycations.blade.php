@extends('layouts.admin')

@section('title', 'Staycation Rooms CMS')

@section('content')

<div
    x-data="staycationManager()"
    x-init="initialize()"
    class="p-4 sm:p-6 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-3xl sm:text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Staycation Room Management
    </h1>
    <p class="text-gray-600 mb-8 sm:mb-10 text-sm sm:text-base">Manage all your room options, capacity, pricing, and inventory.</p>
    @php
        $mobileLayoutStep1 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step1_availability')->value('value') ?? 3);
    @endphp

    <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-indigo-900">Booking Step 1 Mobile Layout</h3>
                <p class="text-xs text-indigo-700 mt-1">Controls mobile card style for `/booking/staycation/availability` only. Desktop layout stays unchanged.</p>
            </div>
            <div class="flex gap-2 items-center">
                <select id="mobileLayoutStep1"
                        class="px-3 py-2 border border-indigo-300 rounded-lg text-sm bg-white">
                    <option value="1" {{ $mobileLayoutStep1 === 1 ? 'selected' : '' }}>2 Columns</option>
                    <option value="2" {{ $mobileLayoutStep1 === 2 ? 'selected' : '' }}>1 Card Per Row</option>
                    <option value="3" {{ $mobileLayoutStep1 === 3 ? 'selected' : '' }}>Carousel (1 Card Swipe)</option>
                </select>
                <button type="button"
                        onclick="saveMobileLayoutSetting('mobile_layout_step1_availability', document.getElementById('mobileLayoutStep1').value)"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                    Save
                </button>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Staycation Rooms</h2>
            <p class="text-gray-500 text-sm">View and edit all room options.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <form method="GET" action="{{ route('admin.stay.staycations') }}">
                @foreach(request()->except('sort_by') as $queryKey => $queryValue)
                    @if(is_array($queryValue))
                        @foreach($queryValue as $nestedValue)
                            <input type="hidden" name="{{ $queryKey }}[]" value="{{ $nestedValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                    @endif
                @endforeach
                <label for="sort_by" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Sort by</label>
                <select id="sort_by" name="sort_by"
                        onchange="this.form.submit()"
                        class="w-full min-w-64 px-3 py-2 rounded-xl border border-gray-300 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach(($sortOptions ?? ['featured' => 'Featured']) as $key => $label)
                        <option value="{{ $key }}" {{ ($sortBy ?? 'featured') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>

            <a
                href="{{ route('admin.stay.settings') }}"
                class="px-3 py-2 sm:px-4 rounded-xl bg-orange-600 text-white font-medium shadow hover:bg-orange-700 hover:shadow-lg transition text-center text-sm sm:text-base"
            >
                Pricing Overview
            </a>
            <a
                href="{{ route('admin.booking-settings.index') }}"
                class="px-3 py-2 sm:px-4 rounded-xl bg-green-600 text-white font-medium shadow hover:bg-green-700 hover:shadow-lg transition text-center text-sm sm:text-base"
            >
                Additional Fees
            </a>
            <button
                type="button"
                @click="showStaycationModal = true; resetForm()"
                class="px-3 py-2 sm:px-4 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition text-center text-sm sm:text-base"
            >
                + Add Room
            </button>
        </div>
    </div>

    <div
        class="mb-6 rounded-xl border px-4 py-3 text-sm"
        :class="canReorder ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-slate-200 bg-white/80 text-slate-600'"
    >
        <template x-if="canReorder">
                <p>Featured mode uses the saved frontend order. Drag the cards below to update the order used on room booking pages.</p>
        </template>
        <template x-if="!canReorder">
            <p>Card dragging is available only in `Featured (Saved Order)` mode so temporary name/price sorts do not overwrite your saved frontend order.</p>
        </template>
    </div>

    {{-- Card Grid Layout --}}
    <div
        x-ref="staycationGrid"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10"
    >
        <template x-for="staycation in staycations" :key="staycation.id">
            <div
                data-staycation-card
                :data-staycation-id="staycation.id"
                class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative"
                :class="canReorder ? 'cursor-grab active:cursor-grabbing' : ''"
            >
                <div class="absolute right-4 top-4 z-10 flex items-center gap-2">
                    <span
                        class="inline-flex min-w-8 items-center justify-center rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-gray-500 shadow-sm"
                        x-text="`#${staycation.sort_order || 0}`"
                    ></span>
                    <button
                        x-show="canReorder"
                        type="button"
                        data-drag-handle
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white/95 text-gray-500 shadow-sm transition hover:border-gray-300 hover:text-gray-700"
                        aria-label="Drag to reorder room"
                        title="Drag to reorder room"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <circle cx="6" cy="5" r="1.4" />
                            <circle cx="6" cy="10" r="1.4" />
                            <circle cx="6" cy="15" r="1.4" />
                            <circle cx="14" cy="5" r="1.4" />
                            <circle cx="14" cy="10" r="1.4" />
                            <circle cx="14" cy="15" r="1.4" />
                        </svg>
                    </button>
                </div>

                <div class="h-48 overflow-hidden rounded-xl mb-4 relative bg-gray-100">
                    <template x-if="staycation.image && staycation.image.trim() !== ''">
                        <img :src="staycation.image" 
                             class="w-full h-full object-cover transition hover:scale-105" 
                             alt="room"
                             onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                    </template>
                    <template x-if="!staycation.image || staycation.image.trim() === ''">
                        <div class="w-full h-full bg-linear-to-br from-blue-200 to-purple-200 flex items-center justify-center cursor-pointer hover:from-blue-300 hover:to-purple-300">
                            <div class="text-center">
                                <i class="bi bi-image text-4xl opacity-50 text-white block mb-2"></i>
                                <p class="text-xs text-white opacity-70">No image</p>
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <h3 class="font-semibold text-lg text-gray-900" x-text="staycation.name || 'Untitled'"></h3>
                </div>

                <div class="mt-3 text-sm text-gray-600 space-y-1">
                    @php
                        // This will be evaluated at server-time for initial display, but Alpine will handle dynamic updates
                    @endphp
                    
                    <template x-if="!staycation.name || (!staycation.name.toLowerCase().includes('byot') && !staycation.name.toLowerCase().includes('car camping') && !staycation.name.toLowerCase().includes('bring your own'))">
                        <div class="space-y-1">
                        <p><span x-text="`Base included guests: ${staycation.included_guest_capacity || staycation.capacity || ((staycation.adults || 0) + (staycation.children || 0)) || 0}`"></span></p>
                            <p><span x-text="`Max guests: ${staycation.max_guest_capacity || staycation.capacity || 'N/A'}`"></span></p>
                        </div>
                    </template>
                    
                    <template x-if="staycation.name && (staycation.name.toLowerCase().includes('byot') || staycation.name.toLowerCase().includes('car camping') || staycation.name.toLowerCase().includes('bring your own'))">
                        <p class="text-blue-600 font-medium">Special Room: BYOT/Car Camping</p>
                    </template>

                    <div class="text-xs bg-green-50 border border-green-200 rounded p-2 mt-2">
                        <p class="font-semibold text-green-700">Today's Rate:
                            <span x-text="(() => { const rate = getAdminPreviewRate(staycation); return rate !== null ? '\u20B1' + new Intl.NumberFormat('en-US').format(rate) : 'No active rate today'; })()"></span>
                        </p>
                        <p class="text-gray-600 text-xs mt-1">
                            Active season: <span>{{ $currentSeason }}</span>
                        </p>
                        <p class="text-gray-600 text-xs mt-1">
                            Room override:
                            <span x-text="staycation.price_configs && staycation.price_configs.length > 0 ? (staycation.price_configs[0]?.name || 'Seasonal rate') : 'Global pricing fallback'"></span>
                        </p>
                        <template x-if="!getAdminPreviewRate(staycation)">
                            <p class="text-amber-700 text-xs mt-1">
                                This room has pricing saved, but no matching pricing rule was found for today's date.
                            </p>
                        </template>
                        <template x-if="getConfiguredRateSummary(staycation)">
                            <p class="text-gray-600 text-xs mt-1">
                                Configured rates:
                                <span x-text="(() => {
                                    const rates = getConfiguredRateSummary(staycation);
                                    if (!rates) return 'N/A';
                                    const fmt = (n) => '\u20B1' + new Intl.NumberFormat('en-US').format(n);
                                    return [rates.nonPeak ? fmt(rates.nonPeak) : null, rates.peak ? fmt(rates.peak) : null, rates.superPeak ? fmt(rates.superPeak) : null].filter(Boolean).join(' / ');
                                })()"></span>
                            </p>
                        </template>
                    </div>
                </div>

                <p class="text-xs text-gray-400 mt-3" x-text="`Uploaded: ${new Date(staycation.created_at || new Date()).toLocaleDateString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'})}`"></p>

                <div class="mt-5 flex justify-end space-x-3">
                    <button class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                        @click="editStaycation(staycation.id)">Edit</button>
                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                        @click="deleteStaycation(staycation.id, `{{ url('/admin/staycation-list') }}/${staycation.id}`)">Delete</button>
                </div>
            </div>
        </template>
        
        <template x-if="staycations.length === 0">
            <div class="col-span-full text-center py-12 bg-white/70 backdrop-blur-lg rounded-2xl border border-gray-200">
                <i class="bi bi-inbox text-5xl text-gray-300 mb-4 block"></i>
                <p class="text-lg text-gray-600 font-semibold">No Rooms Yet</p>
                <p class="text-gray-500 text-sm mt-1">Start by adding your first room.</p>
            </div>
        </template>
    </div>

    {{-- SWIPER IMAGES MANAGEMENT --}}
    <div class="flex justify-between items-end mb-6 mt-12">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Room Images Management</h2>
            <p class="text-gray-500 text-sm">Photos that appear in the room carousel.</p>
        </div>

        <button 
            @click="showModal = true" 
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition"
        >
            + Add Room Image
        </button>
    </div>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: @json(session('success')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2200
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2600
            });
        </script>
    @endif

    {{-- Images Table --}}
    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <table class="w-full text-left min-w-200">
            <thead class="bg-gray-100/70 backdrop-blur">
                <tr>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Preview</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Room Name</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Title</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Uploaded</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($images as $index => $img)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" alt="{{ $img->title }}" class="w-20 h-12 object-cover rounded" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $img->staycation->name ?? 'Untitled' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-right space-x-4">
                            <button class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition" @click="editImage({{ $img->id }}, '{{ $img->staycation_id }}', '{{ $img->title }}')">
                                Edit
                            </button>
                            <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition" @click="deleteImage({{ $img->id }}, '{{ route('admin.staycation-image.destroy', $img->id) }}')">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500">No images uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-14 border-gray-300">

    {{-- STAYCATION MODAL --}}
    <div 
        x-show="showStaycationModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-4xl p-5 sm:p-7 relative border border-gray-200 max-h-[95vh] overflow-y-auto">
            <button 
                @click="showStaycationModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editModeStaycation ? 'Edit Room' : 'Add Room'"></h3>

            <form id="staycationForm" @submit.prevent="saveStaycation()" class="space-y-5">
                @csrf

                <!-- Room Name, Days, Adults & Children -->
                <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6" :class="isSpecialStaycation ? 'lg:grid-cols-3' : ''">
                    <div>
                        <label for="staycation_name" class="block text-sm font-medium mb-1">Room Name</label>
                        <input type="text" id="staycation_name" name="name" autocomplete="off" x-model="form.name" @input="updateSpecialRoomStatus()" class="w-full border rounded px-3 py-2 h-10" placeholder="Premium Glamping Tent" required>
                        <p x-show="isSpecialStaycation" class="text-xs text-blue-600 mt-1">BYOT/Car Camping - No adults/children required</p>
                    </div>
                    <div>
                        <label for="staycation_days" class="block text-sm font-medium mb-1">Duration (Days)</label>
                        <input type="number" id="staycation_days" name="days" x-model.number="form.days" min="1" class="w-full border rounded px-3 py-2 h-10" placeholder="1" required>
                        <p class="text-xs text-gray-500 mt-1">Specify the number of days for the staycation.</p>
                    </div>

                    <div x-show="!isSpecialStaycation">
                        <label for="staycation_adults" class="block text-sm font-medium mb-1">Max Adults Allowed</label>
                        <input type="number" id="staycation_adults" name="adults" x-model.number="form.adults" min="1" class="w-full border rounded px-3 py-2 h-10" placeholder="1" required>
                        <p class="text-xs text-gray-500 mt-1">For glamping rooms, minimum is 1 adult.</p>
                    </div>
                    <div x-show="!isSpecialStaycation">
                        <label for="staycation_children" class="block text-sm font-medium mb-1">Max Children Allowed</label>
                        <input type="number" id="staycation_children" name="children" x-model.number="form.children" min="0" class="w-full border rounded px-3 py-2 h-10" placeholder="0" required>
                    </div>
                    <div x-show="!isSpecialStaycation">
                        <label for="staycation_max_capacity" class="block text-sm font-medium mb-1">Maximum Guests Allowed</label>
                        <input type="number" id="staycation_max_capacity" name="max_guest_capacity" x-model.number="form.max_guest_capacity" :min="Math.max(1, form.included_guest_capacity || 1)" class="w-full border rounded px-3 py-2 h-10" placeholder="6" required>
                        <p class="text-xs text-gray-500 mt-1">Can be higher than base setup to allow extra beds.</p>
                    </div>
                    <div x-show="!isSpecialStaycation">
                        <label for="staycation_included_capacity" class="block text-sm font-medium mb-1">Base number of guests</label>
                        <input type="number" id="staycation_included_capacity" name="included_guest_capacity" x-model.number="form.included_guest_capacity" min="1" class="w-full border rounded px-3 py-2 h-10" placeholder="4" required>
                        <p class="text-xs text-gray-500 mt-1">Guests covered by base price before extra mattress.</p>
                    </div>
                </div>
                <div x-show="!isSpecialStaycation" class="text-xs text-gray-600 -mt-2">
                    <span x-text="`Base included guests = ${Math.max(1, form.included_guest_capacity || 1)} - Extra bed starts from guest #${Math.max(1, form.included_guest_capacity || 1) + 1}`"></span>
                </div>

                <!-- Dynamic Price Configs -->
                <div class="space-y-3">
                    <span class="block text-sm font-medium">Room Pricing Overrides</span>
                    <p class="text-xs text-gray-500">Use room-specific rates here. Global seasonal rules are managed in Pricing Configuration.</p>
                    <div x-ref="priceConfigsList" class="space-y-3">
                        <template x-for="(config, index) in form.priceConfigs" :key="config._uid || index">
                        <div class="border p-3 rounded space-y-2 bg-white/70" :data-price-config-id="config._uid || index">

                            <!-- Config Name and Type -->
                            <div class="flex space-x-2 items-center">
                                <button
                                    type="button"
                                    data-price-config-handle
                                    class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded border border-gray-200 bg-gray-50 text-gray-500 hover:text-gray-700 hover:bg-gray-100 cursor-grab active:cursor-grabbing"
                                    title="Drag to reorder pricing rule"
                                    aria-label="Drag to reorder pricing rule"
                                >
                                    <i class="bi bi-grip-vertical"></i>
                                </button>
                                <input type="text" :id="'price_config_name_' + index" :name="'price_config_name_' + index" x-model="config.name" placeholder="Example: Standard Glamping - Peak Jan-Jun 2026" class="flex-1 border rounded px-2 py-1" required>

                                <select :id="'price_config_type_' + index" :name="'price_config_type_' + index" x-model="config.type" class="border rounded px-2 py-1">
                                    <option value="day">Day Pattern</option>
                                    <option value="date">Specific Dates</option>
                                    <option value="range">Date Range</option>
                                </select>

                                <button type="button" @click="removePriceConfig(index)" class="text-red-500 font-bold">&times;</button>
                            </div>

                            <p class="text-[11px] text-gray-500">
                                Use `Day Pattern` for repeating weekday or weekend pricing, `Specific Dates` for holidays, and `Date Range` for seasonal blocks.
                            </p>

                            <!-- Day pattern controls -->
                            <div x-show="config.type === 'day'" class="space-y-2">
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:items-end">
                                    <div>
                                        <label :for="'day_preset_' + index" class="block text-xs font-medium text-gray-600 mb-1">Quick Select</label>
                                        <select :id="'day_preset_' + index" :name="'day_preset_' + index" x-model="config.dayPreset" @change="applyDayPreset(config, index)" class="w-full border rounded px-2 py-1">
                                            <option value="custom">Custom days</option>
                                            <option value="weekdays">Weekdays (Sun-Thu)</option>
                                            <option value="weekends">Weekends (Fri-Sat)</option>
                                            <option value="all">All days</option>
                                        </select>
                                    </div>
                                    <p class="text-[11px] text-gray-500">
                                        Pick a preset first, then fine-tune with the checkboxes below.
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-1 sm:gap-2">
                                    <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                                        <label class="flex items-center space-x-1 rounded border border-gray-200 px-2 py-1">
                                            <input type="checkbox" :id="'day_' + day + '_' + index" :name="'day_' + day + '_' + index" :value="day" x-model="config.days" @change="config.dayPreset = 'custom'">
                                            <span class="text-sm" x-text="day"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>


                            <!-- Dates input -->
                            <div x-show="config.type === 'date'" class="space-y-2">
                                <label :for="'config_dates_' + index" class="block text-sm font-medium">Select Date(s)</label>
                                <p class="text-xs text-gray-500">Use this for holidays, promos, or specific calendar dates.</p>

                                <!-- Single date -->
                                <template x-if="!config.multipleDates">
                                    <input type="date" :id="'config_dates_' + index" :name="'config_dates_' + index" x-model="config.dates" class="w-full border rounded px-2 py-1">
                                </template>

                                <!-- Multiple dates (comma-separated) -->
                                <template x-if="config.multipleDates">
                                    <input type="text" :id="'config_dates_' + index" :name="'config_dates_' + index" x-model="config.dates" placeholder="YYYY-MM-DD, YYYY-MM-DD" class="w-full border rounded px-2 py-1">
                                </template>

                                <!-- Toggle for single/multiple dates -->
                                <label class="flex items-center space-x-2 mt-1">
                                    <input type="checkbox" :id="'multiple_dates_' + index" :name="'multiple_dates_' + index" x-model="config.multipleDates">
                                    <span class="text-sm text-gray-600">Allow multiple dates (comma-separated)</span>
                                </label>
                            </div>

                            <div x-show="config.type === 'range'" class="space-y-2">
                                <label class="block text-sm font-medium">Date Range</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label :for="'config_start_date_' + index" class="block text-xs text-gray-500 mb-1">Start Date</label>
                                        <input type="date" :id="'config_start_date_' + index" :name="'config_start_date_' + index" x-model="config.startDate" class="w-full border rounded px-2 py-1">
                                    </div>
                                    <div>
                                        <label :for="'config_end_date_' + index" class="block text-xs text-gray-500 mb-1">End Date</label>
                                        <input type="date" :id="'config_end_date_' + index" :name="'config_end_date_' + index" x-model="config.endDate" class="w-full border rounded px-2 py-1">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Covers every date from start to end, inclusive.</p>
                                <div class="space-y-2 pt-1">
                                    <label class="block text-sm font-medium">Applies To</label>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:items-end">
                                        <div>
                                            <label :for="'range_day_preset_' + index" class="block text-xs font-medium text-gray-600 mb-1">Quick Select</label>
                                            <select :id="'range_day_preset_' + index" :name="'range_day_preset_' + index" x-model="config.dayPreset" @change="applyDayPreset(config, index)" class="w-full border rounded px-2 py-1">
                                                <option value="custom">Custom days</option>
                                                <option value="weekdays">Weekdays (Sun-Thu)</option>
                                                <option value="weekends">Weekends (Fri-Sat)</option>
                                                <option value="all">All days</option>
                                            </select>
                                        </div>
                                        <p class="text-[11px] text-gray-500">
                                            Use `Weekdays` and `Weekends` on separate rules when prices differ inside the same season range.
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-1 sm:gap-2">
                                        <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                                            <label class="flex items-center space-x-1 rounded border border-gray-200 px-2 py-1">
                                                <input type="checkbox" :id="'range_day_' + day + '_' + index" :name="'range_day_' + day + '_' + index" :value="day" x-model="config.days" @change="config.dayPreset = 'custom'">
                                                <span class="text-sm" x-text="day"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>


                            <!-- Rates per season -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="flex flex-col">
                                    <label :for="'rate_nonpeak_' + index" class="text-xs sm:text-sm font-semibold mb-1">Non-Peak</label>
                                    <input type="number" :id="'rate_nonpeak_' + index" :name="'rate_nonpeak_' + index" x-model.number="config.rates.nonPeak" x-bind:value="config.rates.nonPeak" class="border rounded px-2 py-1 text-sm" min="0">
                                </div>
                                <div class="flex flex-col">
                                    <label :for="'rate_peak_' + index" class="text-xs sm:text-sm font-semibold mb-1">Peak</label>
                                    <input type="number" :id="'rate_peak_' + index" :name="'rate_peak_' + index" x-model.number="config.rates.peak" x-bind:value="config.rates.peak" class="border rounded px-2 py-1 text-sm" min="0">
                                </div>
                                <div class="flex flex-col">
                                    <label :for="'rate_superpeak_' + index" class="text-xs sm:text-sm font-semibold mb-1">Super Peak</label>
                                    <input type="number" :id="'rate_superpeak_' + index" :name="'rate_superpeak_' + index" x-model.number="config.rates.superPeak" x-bind:value="config.rates.superPeak" class="border rounded px-2 py-1 text-sm" min="0">
                                </div>
                            </div>

                        </div>
                        </template>
                    </div>

                    <button type="button" @click="addPriceConfig()" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">+ Add Pricing Rule</button>
                </div>

                <!-- Image Upload Section -->
                <div class="space-y-3">
                    <label for="staycation_image" class="block text-sm font-medium">Upload Image (optional)</label>
                    <input type="file" id="staycation_image" name="image" accept="image/*" x-ref="imageInput" @change="handleImageUpload($event)" class="w-full border rounded px-3 py-2 text-sm">
                    <template x-if="form.imagePreview">
                        <div class="mt-2 flex items-center gap-2">
                            <img :src="form.imagePreview" class="w-24 sm:w-32 h-16 sm:h-20 object-cover rounded border">
                            <button type="button" @click="removeImage()" class="text-red-500 hover:underline text-sm">Remove</button>
                        </div>
                    </template>
                </div>


                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" @click="showStaycationModal = false; resetForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition text-sm w-full sm:w-auto">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition text-sm w-full sm:w-auto" x-text="editModeStaycation ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- LOADING OVERLAY --}}
    <div 
        x-show="isLoading"
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-9999"
    >
        <div class="flex flex-col items-center space-y-3">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-30" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                <path class="opacity-90" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white text-lg font-medium">Processing...</span>
        </div>
    </div>

    {{-- Image Upload Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-5 sm:p-6 relative border border-gray-200">
            <button 
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Room Image' : 'Upload New Room Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                :action="editImageMode ? '/admin/staycation-image/' + currentImageId : '{{ route('admin.staycation-image.store') }}'"
                method="POST"
                @submit.prevent="
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const fileInput = $refs.image.files[0];
                    const title = $refs.title.value;
                    const staycationId = $refs.staycation.value;

                    if (!editImageMode && !fileInput) {
                        Swal.fire({
                            title: 'Missing Image',
                            text: 'Please select an image before uploading.',
                            icon: 'warning',
                            confirmButtonColor: '#3b82f6'
                        });
                        return;
                    }

                    if (!staycationId) {
                        Swal.fire({
                            title: 'Missing Room',
                            text: 'Please select a room.',
                            icon: 'warning',
                            confirmButtonColor: '#3b82f6'
                        });
                        return;
                    }

                    const formData = new FormData();
                    if (fileInput) formData.append('image', fileInput);
                    formData.append('title', title);
                    formData.append('staycation_id', staycationId);
                    if (editImageMode) formData.append('_method', 'PUT');

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = editImageMode ? 'Updating...' : 'Uploading...';
                    btn.disabled = true;

                    const url = editImageMode ? '/admin/staycation-image/' + currentImageId : '{{ route('admin.staycation-image.store') }}';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(async res => {
                        let data;
                        try { data = await res.json(); } catch { throw new Error('Invalid JSON'); }

                        if (res.ok) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || (editImageMode ? 'Image updated successfully!' : 'Image uploaded successfully!'),
                                icon: 'success',
                                confirmButtonColor: '#10b981',
                                timer: 2000,
                                timerProgressBar: true
                            });
                            $refs.image.value = '';
                            $refs.title.value = '';
                            $refs.staycation.value = '';
                            showModal = false;
                            resetImageForm();
                            window.location.reload();
                        } else if (data.errors) {
                            Swal.fire({
                                title: 'Validation Error',
                                text: Object.values(data.errors).flat().join('\n'),
                                icon: 'error',
                                confirmButtonColor: '#ef4444'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Operation failed.',
                                icon: 'error',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error ' + (editImageMode ? 'updating' : 'uploading') + ' image.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    })
                    .finally(() => {
                        btn.innerText = editImageMode ? 'Update' : 'Upload';
                        btn.disabled = false;
                    });
                "
            >
                @csrf
                <input type="hidden" name="_method" value="POST" x-bind:value="editImageMode ? 'PUT' : 'POST'">
                <div class="mb-3">
                    <label for="swiper_staycation_id" class="block font-semibold mb-1">Room</label>
                    <select id="swiper_staycation_id" name="staycation_id" x-ref="staycation" class="border rounded p-2 w-full" x-bind:value="editImageMode ? currentStaycationId : ''" required>
                        <option value="">Select Room</option>
                        @foreach($staycations as $staycation)
                            <option value="{{ $staycation->id }}">{{ $staycation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="swiper_image_title" class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" id="swiper_image_title" name="title" x-ref="title" class="border rounded p-2 w-full" x-bind:value="editImageMode ? currentImageTitle : ''">
                </div>

                <div class="mb-3">
                    <label for="swiper_image_file" class="block font-semibold mb-1">Upload Room Image/Video</label>
                    <input type="file" id="swiper_image_file" name="image" x-ref="image" accept="image/*,video/*" class="border rounded p-2 w-full" x-bind:required="!editImageMode">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded text-sm w-full sm:w-auto">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm w-full sm:w-auto" x-text="editImageMode ? 'Update' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- FEATURES MANAGEMENT --}}
    <div class="flex justify-between items-end mb-6 mt-12">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Room Features Management</h2>
            <p class="text-gray-500 text-sm">Amenities and features for each room.</p>
        </div>

        <button
            @click="resetTextForm(); showTextModal = true"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition"
        >
            + Add Room Features
        </button>
    </div>

    {{-- Features Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <table class="w-full text-left">
            <thead class="bg-gray-100/70 backdrop-blur">
                <tr>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Room Name</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Features List</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($texts as $index => $text)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $text->staycation->name ?? 'Untitled' }}</td>
                        <td class="px-6 py-4 text-gray-600 text-sm">
                            @php
                                $features = explode("\n", $text->content ?? '');
                                $featureCount = collect($features)->filter(fn($f) => trim($f))->count();
                            @endphp
                            @if($featureCount > 0)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">{{ $featureCount }} features</span>
                            @else
                                <span class="text-gray-400 italic">No features</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-4">
                            <button
                                type="button"
                                x-on:click="editText($el, {{ $text->id }}, '{{ $text->staycation_id }}', '{{ $text->section }}', '{{ $text->title }}')"
                                data-content="{!! htmlspecialchars($text->content ?? '', ENT_QUOTES) !!}"
                                class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                @click="deleteText({{ $text->id }}, '{{ route('admin.staycation-text.destroy', $text->id) }}')"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-gray-500">No features added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Features Modal -->
    <div
        x-show="showTextModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
    >
        <div
            @click.away="showTextModal = false"
            class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-5 sm:p-6 relative border border-gray-200"
        >
            <button 
                @click="showTextModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4 text-gray-900" x-text="editMode ? 'Edit Room Features' : 'Add Room Features'"></h3>

            <form
                id="textUploadForm"
                class="space-y-5"
                @submit.prevent="saveText()"
            >
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-3">
                    <label for="features_staycation_id" class="block font-semibold mb-1">Room</label>
                    <select
                        id="features_staycation_id"
                        name="staycation_id"
                        x-model="form.staycation_id"
                        class="border rounded p-2 w-full"
                        required
                    >
                        <option value="">Select Room</option>
                        @foreach($staycations as $staycation)
                            <option value="{{ $staycation->id }}">{{ $staycation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="features_text" class="block text-sm font-medium mb-1">Features List</label>
                    <textarea
                        id="features_text"
                        name="text"
                        x-model="form.text"
                        rows="8"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter each feature on a new line:&#10;LUXURY TENT SETUP&#10;GOURMET LUNCH SERVICE&#10;PRIVATE ATV EXPERIENCE&#10;GUIDED NATURE TOUR&#10;CUSTOM TEAM BUILDING"
                        required
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Enter each feature on a separate line. These will be displayed as bullet points on the room pages.</p>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-2">
                    <button
                        type="button"
                        @click="showTextModal = false"
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 text-sm w-full sm:w-auto"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm w-full sm:w-auto"
                    >
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
function staycationManager() {
    return {
        isLoading: false,
        showModal: false,
        showStaycationModal: false,
        editModeStaycation: false,
        showTextModal: false,
        editMode: false,
        editImageMode: false,
        currentImageId: null,
        currentStaycationId: '',
        currentImageTitle: '',
        canReorder: @js(($sortBy ?? 'featured') === 'featured'),
        reorderEndpoint: @js(route('admin.staycation-list.reorder')),
        sortableInstance: null,
        priceConfigSortable: null,
        isReordering: false,
        currentSeasonKey: @js($currentSeasonKey),
        staycations: @js($staycations),
        form: {
            id: null,
            name: '',
            days: 1,
            quantity: 1,
            adults: 1,
            children: 0,
            included_guest_capacity: 1,
            max_guest_capacity: 1,
            section: '',
            label: '',
            text: '',
            staycation_id: '',
            image: null,
            imagePreview: null,
            existingImage: null,
            removeImage: false,
            priceConfigs: [],
            images: []
        },

        computedThreshold: 0,
        isSpecialStaycation: false,
        updatedPrices: {}, // Track updated prices by staycation ID

        getUpdatedPrice(staycationId) {
            return this.updatedPrices[staycationId];
        },

        formatLocalDateKey(dateObj) {
            const y = dateObj.getFullYear();
            const m = String(dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dateObj.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },

                getAdminPreviewRate(staycation) {
                    const directRate = Number(staycation?.current_rate_preview);
                    if (Number.isFinite(directRate) && directRate > 0) {
                        return directRate;
                    }

            const configs = Array.isArray(staycation?.price_configs) ? staycation.price_configs : [];
            if (configs.length === 0) {
                return null;
            }

            const today = new Date();
            const dateKey = this.formatLocalDateKey(today);
            const dayMap = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const dayOfWeek = dayMap[today.getDay()];

            const normalizeType = (config) => {
                const type = String(config?.type || '').toLowerCase();
                if (type === 'range' || config?.startDate || config?.start_date || config?.endDate || config?.end_date) {
                    return 'range';
                }
                if (type === 'date' || config?.dates || config?.multipleDates) {
                    return 'date';
                }
                return 'day';
            };

            const parseDates = (config) => {
                const rawDates = config?.dates ?? [];
                const list = Array.isArray(rawDates)
                    ? rawDates
                    : String(rawDates).split(/[,\n]+/);

                return list
                    .map((item) => String(item).trim())
                    .filter(Boolean)
                    .map((item) => {
                        const parsed = new Date(item);
                        return Number.isNaN(parsed.getTime()) ? item : this.formatLocalDateKey(parsed);
                    });
            };

            const matchesConfig = (config) => {
                const type = normalizeType(config);

                if (type === 'date') {
                    return parseDates(config).includes(dateKey);
                }

                if (type === 'range') {
                    const start = config?.startDate || config?.start_date;
                    const end = config?.endDate || config?.end_date;
                    if (!start || !end) {
                        return false;
                    }

                    const startKey = this.formatLocalDateKey(new Date(start));
                    const endKey = this.formatLocalDateKey(new Date(end));
                    if (dateKey < startKey || dateKey > endKey) {
                        return false;
                    }

                    const days = Array.isArray(config?.days) ? config.days : [];
                    return days.length === 0 || days.includes(dayOfWeek);
                }

                const days = Array.isArray(config?.days) ? config.days : [];
                return days.length === 0 || days.includes(dayOfWeek);
            };

            for (const type of ['date', 'range', 'day']) {
                for (const config of configs) {
                    if (normalizeType(config) !== type) {
                        continue;
                    }

                    if (matchesConfig(config)) {
                        const rate = Number(config?.rates?.[this.currentSeasonKey] ?? 0);
                        if (rate > 0) {
                            return rate;
                        }
                    }
                }
            }

            return null;
        },

        getConfiguredRateSummary(staycation) {
            const configs = Array.isArray(staycation?.price_configs) ? staycation.price_configs : [];
            if (configs.length === 0) {
                return null;
            }

            const summary = {
                nonPeak: null,
                peak: null,
                superPeak: null
            };

            for (const config of configs) {
                const rates = config?.rates || {};
                if (summary.nonPeak === null && Number(rates.nonPeak) > 0) summary.nonPeak = Number(rates.nonPeak);
                if (summary.peak === null && Number(rates.peak) > 0) summary.peak = Number(rates.peak);
                if (summary.superPeak === null && Number(rates.superPeak) > 0) summary.superPeak = Number(rates.superPeak);
            }

            if (summary.nonPeak === null && summary.peak === null && summary.superPeak === null) {
                return null;
            }

            return summary;
        },

        initialize() {
            this.initPrices();
            this.$nextTick(() => this.initSortable());
        },

        initPrices() {
            // Initialize updatedPrices from staycations data
            this.staycations.forEach(stay => {
                if (stay.price_configs && stay.price_configs.length > 0) {
                    this.updatedPrices[stay.id] = {
                        nonPeak: stay.price_configs[0].rates.nonPeak,
                        peak: stay.price_configs[0].rates.peak,
                        superPeak: stay.price_configs[0].rates.superPeak
                    };
                }
            });
        },

        initSortable() {
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }

            if (!this.canReorder || typeof Sortable === 'undefined' || !this.$refs.staycationGrid) {
                return;
            }

            this.sortableInstance = Sortable.create(this.$refs.staycationGrid, {
                animation: 180,
                handle: '[data-drag-handle]',
                draggable: '[data-staycation-card]',
                ghostClass: 'staycation-card-ghost',
                chosenClass: 'staycation-card-chosen',
                dragClass: 'staycation-card-drag',
                onEnd: async () => {
                    const orderedIds = Array.from(
                        this.$refs.staycationGrid.querySelectorAll('[data-staycation-id]')
                    )
                        .map((card) => Number(card.dataset.staycationId))
                        .filter((id) => Number.isInteger(id) && id > 0);

                    if (orderedIds.length !== this.staycations.length) {
                        return;
                    }

                    this.applyStaycationOrder(orderedIds);
                    await this.persistStaycationOrder(orderedIds);
                },
            });
        },

        applyStaycationOrder(orderedIds) {
            const staycationMap = new Map(this.staycations.map((staycation) => [Number(staycation.id), staycation]));
            this.staycations = orderedIds
                .map((id, index) => {
                    const staycation = staycationMap.get(id);
                    if (!staycation) {
                        return null;
                    }

                    return {
                        ...staycation,
                        sort_order: index + 1,
                    };
                })
                .filter(Boolean);
        },

        async persistStaycationOrder(orderedIds) {
            if (this.isReordering) {
                return;
            }

            this.isReordering = true;
            this.sortableInstance?.option('disabled', true);

            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                const response = await fetch(this.reorderEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        ordered_ids: orderedIds,
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok || payload.success === false) {
                    throw new Error(payload.message || 'Failed to save room order.');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Order Not Saved',
                    text: error.message || 'Failed to save room order.',
                    confirmButtonColor: '#ef4444',
                }).then(() => {
                    window.location.reload();
                });
            } finally {
                this.isReordering = false;
                this.sortableInstance?.option('disabled', false);
            }
        },

        updateSpecialRoomStatus() {
            const name = (this.form.name || '').toLowerCase();
            this.isSpecialStaycation = name.includes('byot') || name.includes('car camping') || name.includes('bring your own');
        },

        isSpecialRoom(name) {
            const lowerName = (name || '').toLowerCase();
            return lowerName.includes('byot') || lowerName.includes('car camping') || lowerName.includes('bring your own');
        },

        resetForm() {
            this.destroyPriceConfigSortable();
            this.form = { id: null, name: '', days: 1, quantity: 1, adults: 1, children: 0, included_guest_capacity: 1, max_guest_capacity: 1, priceConfigs: [], image: null, imagePreview: null, existingImage: null, removeImage: false };
            this.editModeStaycation = false;
            this.isSpecialStaycation = false;
            this.removeImage(); // Explicitly clear image and input
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            this.currentStaycationId = '';
            this.currentImageTitle = '';
        },

        resetTextForm() {
            this.form.id = null;
            this.form.staycation_id = '';
            this.form.section = '';
            this.form.label = '';
            this.form.text = '';
            this.editMode = false;
        },

        editImage(id, staycationId, title) {
            this.currentImageId = id;
            this.currentStaycationId = staycationId;
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
        },

        editText(el, id, staycationId, section, title) {
            this.resetTextForm();
            this.form.id = id;
            this.form.staycation_id = staycationId;
            this.form.section = section || '';
            this.form.label = title || '';
            this.form.text = el.getAttribute('data-content') || '';
            this.editMode = true;
            this.showTextModal = true;
        },


        editStaycation(id) {
            this.resetForm();
            const staycation = this.staycations.find(s => s.id === id);
            if (staycation) {
                this.form.id = staycation.id;
                this.form.name = staycation.name;
                this.form.days = staycation.days || 1;
                this.updateSpecialRoomStatus();
                this.form.quantity = staycation.capacity;
                this.form.adults = this.isSpecialStaycation ? Number(staycation.adults || 0) : Math.max(1, Number(staycation.adults || 0));
                this.form.children = staycation.children;
                this.form.included_guest_capacity = Number(staycation.included_guest_capacity || staycation.capacity || ((staycation.adults || 0) + (staycation.children || 0)) || 1);
                this.form.max_guest_capacity = Number(staycation.max_guest_capacity || staycation.capacity || this.form.included_guest_capacity || 1);
                // Align inventory inputs with base capacity
                this.inventoryQty = Number(staycation.base_quantity || 1);
                this.inventoryQtyRange = Number(staycation.base_quantity || 1);
                this.inventoryTab = 'quick';
                // Compute initial threshold and optionally auto capacity
                this.updateThresholdFromQuantity();
                this.form.priceConfigs = (staycation.price_configs || []).map(pc => {
                    const datesStr = Array.isArray(pc.dates) ? pc.dates.join(', ') : (pc.dates || '');
                    const parsedDates = datesStr
                        .split(',')
                        .map(date => date.trim())
                        .filter(Boolean);
                    const isRange = (pc.type || '').toLowerCase() === 'range' || !!pc.startDate || !!pc.endDate || !!pc.start_date || !!pc.end_date;
                    const isMultiple = datesStr.includes(',');
                    const dayPreset = pc.dayPreset || this.inferDayPresetFromDays(pc.days || []);
                    return {
                        ...pc,
                        _uid: pc._uid || this.generatePriceConfigId(),
                        name: pc.name || '',
                        type: isRange ? 'range' : (pc.type || 'day'),
                        days: pc.days || [],
                        dayPreset,
                        startDate: pc.startDate || pc.start_date || parsedDates[0] || '',
                        endDate: pc.endDate || pc.end_date || parsedDates[parsedDates.length - 1] || '',
                        dates: datesStr,
                        multipleDates: isMultiple,
                        rates: pc.rates || { nonPeak: 0, peak: 0, superPeak: 0 }
                    };
                });
                // Handle existing image
                if (staycation.image) {
                    this.form.imagePreview = staycation.image;
                    this.form.existingImage = staycation.image; // Store original
                    this.form.removeImage = false; // Track if user wants to remove
                }
                this.editModeStaycation = true;
                this.showStaycationModal = true;
                // Load preview for this staycation
                this.$nextTick(() => {
                    this.loadInventoryPreview();
                    this.initPriceConfigSortable();
                });
            }
        },

        async computeAutoCapacity() {
            if (!this.form.id) { this.updateThresholdFromQuantity(); return; }
            try {
                const r = await fetch(`/api/inventory/${this.form.id}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                const data = await r.json();
                const items = Array.isArray(data.items) ? data.items : [];
                const maxQty = items.length ? Math.max(...items.map(i => Number(i.min_quantity || 0))) : Number(this.form.quantity || 1);
                this.form.quantity = isFinite(maxQty) && maxQty > 0 ? maxQty : 1;
                this.inventoryQty = this.form.quantity;
                this.inventoryQtyRange = this.form.quantity;
                this.updateThresholdFromQuantity();
            } catch (e) {
                this.updateThresholdFromQuantity();
            }
        },

        updateThresholdFromQuantity() {
            const base = Number(this.form.quantity || 0);
            const thr = Math.max(1, Math.floor(base * 0.3));
            this.computedThreshold = base > 0 ? thr : 0;
        },

        addPriceConfig() {
            this.form.priceConfigs.push({
                _uid: this.generatePriceConfigId(),
                name: '',
                type: 'day',
                days: [],
                dayPreset: 'custom',
                startDate: '',
                endDate: '',
                dates: '',
                multipleDates: false,
                rates: { nonPeak: 0, peak: 0, superPeak: 0 }
            });
            this.$nextTick(() => this.initPriceConfigSortable());
        },

        generatePriceConfigId() {
            return `price-config-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
        },

        inferDayPresetFromDays(days) {
            const normalizedDays = Array.from(new Set((days || []).map(day => String(day).trim()).filter(Boolean)));
            const presets = {
                weekdays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu'],
                weekends: ['Fri', 'Sat'],
                all: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
            };

            for (const [preset, expectedDays] of Object.entries(presets)) {
                if (expectedDays.length !== normalizedDays.length) {
                    continue;
                }

                const matches = expectedDays.every(day => normalizedDays.includes(day));
                if (matches) {
                    return preset;
                }
            }

            return 'custom';
        },

        getDaysForPreset(preset) {
            const presets = {
                weekdays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu'],
                weekends: ['Fri', 'Sat'],
                all: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                custom: []
            };

            return [...(presets[preset] || [])];
        },

        formatConfigCoverageKey(dateObj) {
            const y = dateObj.getFullYear();
            const m = String(dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dateObj.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },

        getConfigCoverage(config) {
            const type = String(config?.type || 'day').toLowerCase();

            if (type === 'date') {
                const rawDates = config?.dates ?? [];
                const list = Array.isArray(rawDates)
                    ? rawDates
                    : String(rawDates).split(/[,\n]+/);

                const coverage = {};
                list.forEach((item) => {
                    const value = String(item).trim();
                    if (!value) return;
                    const parsed = new Date(value);
                    if (!Number.isNaN(parsed.getTime())) {
                        coverage[this.formatConfigCoverageKey(parsed)] = true;
                    }
                });

                return Object.keys(coverage).length ? coverage : null;
            }

            if (type === 'range') {
                const start = config?.startDate || config?.start_date;
                const end = config?.endDate || config?.end_date;
                if (!start || !end) {
                    return null;
                }

                const days = Array.isArray(config?.days) ? config.days : [];
                const selectedDays = days.length ? days : this.getDaysForPreset(config?.dayPreset || 'custom');
                const allowedDays = selectedDays.length ? new Set(selectedDays) : null;

                const coverage = {};
                const startDate = new Date(start);
                const endDate = new Date(end);
                if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
                    return null;
                }

                const cursor = new Date(startDate);
                while (cursor <= endDate) {
                    const dayMap = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    const day = dayMap[cursor.getDay()];
                    if (!allowedDays || allowedDays.has(day)) {
                        coverage[this.formatConfigCoverageKey(cursor)] = true;
                    }
                    cursor.setDate(cursor.getDate() + 1);
                }

                return Object.keys(coverage).length ? coverage : null;
            }

            return null;
        },

        coverageHasOverlap(currentCoverage, existingCoverage) {
            return Object.keys(currentCoverage || {}).some((dateKey) => existingCoverage && existingCoverage[dateKey]);
        },

        findQuickSelectOverlap(config, currentIndex) {
            const currentCoverage = this.getConfigCoverage(config);
            if (!currentCoverage) {
                return null;
            }

            for (const [index, otherConfig] of (this.form.priceConfigs || []).entries()) {
                if (index === currentIndex) {
                    continue;
                }

                const otherType = String(otherConfig?.type || 'day').toLowerCase();
                if (!['date', 'range'].includes(otherType)) {
                    continue;
                }

                const existingCoverage = this.getConfigCoverage(otherConfig);
                if (existingCoverage && this.coverageHasOverlap(currentCoverage, existingCoverage)) {
                    return {
                        index,
                        name: otherConfig?.name || `pricing rule #${index + 1}`
                    };
                }
            }

            return null;
        },

        applyDayPreset(config, index) {
            const preset = config.dayPreset || 'custom';
            config.days = this.getDaysForPreset(preset);

            if (String(config.type || 'day').toLowerCase() === 'range') {
                const overlap = this.findQuickSelectOverlap(config, index);
                if (overlap) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Quick Select overlap',
                        text: `This quick select overlaps with "${overlap.name}". Choose a different day pattern.`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3200,
                        timerProgressBar: true
                    });
                    config.dayPreset = 'custom';
                    config.days = [];
                }
            }
        },

        removePriceConfig(index) {
            this.form.priceConfigs.splice(index, 1);
            this.$nextTick(() => this.initPriceConfigSortable());
        },

        destroyPriceConfigSortable() {
            if (this.priceConfigSortable) {
                this.priceConfigSortable.destroy();
                this.priceConfigSortable = null;
            }
        },

        initPriceConfigSortable() {
            this.$nextTick(() => {
                if (this.priceConfigSortable) {
                    this.priceConfigSortable.destroy();
                    this.priceConfigSortable = null;
                }

                if (
                    typeof Sortable === 'undefined' ||
                    !this.$refs.priceConfigsList ||
                    !this.form.priceConfigs ||
                    this.form.priceConfigs.length < 2
                ) {
                    return;
                }

                this.priceConfigSortable = Sortable.create(this.$refs.priceConfigsList, {
                    animation: 180,
                    handle: '[data-price-config-handle]',
                    draggable: '[data-price-config-id]',
                    ghostClass: 'price-config-ghost',
                    chosenClass: 'price-config-chosen',
                    dragClass: 'price-config-drag',
                    onEnd: () => {
                        const orderedIds = Array.from(
                            this.$refs.priceConfigsList.querySelectorAll('[data-price-config-id]')
                        )
                            .map((node) => node.dataset.priceConfigId)
                            .filter(Boolean);

                        if (orderedIds.length !== this.form.priceConfigs.length) {
                            return;
                        }

                        const configMap = new Map(
                            this.form.priceConfigs.map((config, index) => [
                                config._uid || `fallback-${index}`,
                                config
                            ])
                        );

                        this.form.priceConfigs = orderedIds
                            .map((id) => configMap.get(id))
                            .filter(Boolean);
                    }
                });
            });
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.image = file;
                    this.form.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeImage() {
            this.form.image = null;
            this.form.imagePreview = null;
            this.form.removeImage = true; // Mark for deletion
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
        },



        saveStaycation: async function() {
            // Validate form data
            if (!this.form.name.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Field',
                    text: 'Please enter a room name.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (!this.form.days || this.form.days < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Please enter a valid number of days.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (!this.form.quantity || this.form.quantity < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Please enter a valid quantity.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (this.form.adults < 0 || this.form.children < 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Please enter valid numbers for adults and children.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            if (!this.isSpecialRoom(this.form.name) && Number(this.form.adults || 0) < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Glamping rooms must have at least 1 adult.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            if (!this.isSpecialRoom(this.form.name)) {
                const includedCapacity = Number(this.form.included_guest_capacity || 0);
                const maxCapacity = Number(this.form.max_guest_capacity || 0);
                if (includedCapacity < 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Capacity',
                        text: 'Base included guests must be at least 1.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }
                if (maxCapacity < Math.max(1, includedCapacity)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Capacity',
                        text: `Max guests must be at least ${Math.max(1, includedCapacity)} based on your base included guests.`,
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }
            }
            
            // For non-special rooms, ensure at least one guest in total.
            if (!this.isSpecialRoom(this.form.name) && (Number(this.form.adults || 0) + Number(this.form.children || 0)) < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Field',
                    text: 'Please set at least 1 guest.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            
            if (!this.form.priceConfigs || this.form.priceConfigs.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Configuration',
                    text: 'Please add at least one pricing rule.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            for (const [index, config] of (this.form.priceConfigs || []).entries()) {
                const configType = (config.type || 'day').toLowerCase();
                const dayPreset = (config.dayPreset || 'custom').toLowerCase();

                if (configType === 'day' && (!Array.isArray(config.days) || config.days.length === 0) && dayPreset !== 'custom') {
                    config.days = this.getDaysForPreset(dayPreset);
                }

                if (configType === 'day' && (!Array.isArray(config.days) || config.days.length === 0)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Pricing Rule',
                        text: `Please select at least one day for pricing rule #${index + 1}.`,
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }

                if (configType === 'range') {
                    if (!config.startDate || !config.endDate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Incomplete Pricing Rule',
                            text: `Please set both start and end dates for pricing rule #${index + 1}.`,
                            confirmButtonColor: '#3b82f6'
                        });
                        return;
                    }

                    if (config.startDate > config.endDate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Date Range',
                            text: `Start date must be before or equal to end date for pricing rule #${index + 1}.`,
                            confirmButtonColor: '#3b82f6'
                        });
                        return;
                    }

                    const overlap = this.findQuickSelectOverlap(config, index);
                    if (overlap) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Quick Select overlap',
                            text: `This quick select overlaps with "${overlap.name}". Choose a different day pattern.`,
                            confirmButtonColor: '#3b82f6'
                        });
                        return;
                    }
                }

                if (configType === 'date' && !String(config.dates || '').trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Pricing Rule',
                        text: `Please set a date for pricing rule #${index + 1}.`,
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }
            }

            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('name', this.form.name);
            formData.append('days', this.form.days);
            formData.append('quantity', this.form.quantity);
            formData.append('adults', this.form.adults);
            formData.append('children', this.form.children);
            formData.append('included_guest_capacity', this.isSpecialRoom(this.form.name) ? '' : Number(this.form.included_guest_capacity || 1));
            formData.append('max_guest_capacity', this.isSpecialRoom(this.form.name) ? '' : Number(this.form.max_guest_capacity || 0));
            formData.append('priceConfigs', JSON.stringify(this.form.priceConfigs));
            
            if (this.form.image) {
                formData.append('image', this.form.image);
            }
            
            if (this.form.removeImage) {
                formData.append('remove_image', '1');
            }

            let url = '{{ route("admin.staycation-list.store") }}';
            let method = 'POST';
            if (this.editModeStaycation && this.form.id) {
                url = `{{ url('/admin/staycation-list') }}/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#staycationForm button[type=submit]');
            btn.innerText = this.editModeStaycation ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                
                if (res.ok) {
                    // Success - reload the page to show updated data
                    Swal.fire({
                        icon: 'success',
                        title: this.editModeStaycation ? 'Updated!' : 'Added!',
                        text: data.message || (this.editModeStaycation ? 'Room updated successfully!' : 'Room added successfully!'),
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        // Simply reload the page to show the updated data
                        window.location.reload();
                    });
                } else if (data.errors) {
                    let errorHtml = '<div style="text-align: left;">';
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        errorHtml += `<strong>${field}:</strong><br>`;
                        messages.forEach(msg => {
                            errorHtml += `• ${msg}<br>`;
                        });
                    });
                    errorHtml += '</div>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorHtml,
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Operation failed.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error submitting room: ' + (err.message || 'Unknown error'),
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                btn.innerText = this.editModeStaycation ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        async saveText() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            formData.append('staycation_id', this.form.staycation_id);
            formData.append('section', 'features');
            formData.append('title', 'Features');
            formData.append('content', this.form.text);

            let url = '{{ route("admin.staycation-text.store") }}';
            let method = 'POST';
            if (this.editMode && this.form.id) {
                url = `{{ url('/admin/staycation-text') }}/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    Swal.fire({
                        icon: "success",
                        title: this.editMode ? "Updated!" : "Added!",
                        text: data.message || (this.editMode ? "Text updated successfully!" : "Text added successfully!"),
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.form.staycation_id = '';
                        this.form.section = '';
                        this.form.label = '';
                        this.form.text = '';
                        this.editMode = false;
                        this.showTextModal = false;
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: "warning",
                        title: "Validation Error",
                        html: Object.values(data.errors).flat().join("<br>")
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: data.message || "Operation failed."
                    });
                }
            } catch(err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error submitting text.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2600
                });
            } finally {
                this.isLoading = false;
            }
        },

        // ===== INVENTORY FUNCTIONS =====
        inventoryTab: 'quick',
        inventoryQty: 10,
        inventoryStartDate: '',
        inventoryEndDate: '',
        inventoryQtyRange: 10,
        inventoryAvailable: true,

        quickSetInventory() {
            if (!this.form.id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Save the room first.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2200
                });
                return;
            }
            const qty = this.inventoryQty;
            if (!qty || qty < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please enter a valid quantity.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2200
                });
                return;
            }
            this.sendInventoryRequest({
                room_id: this.form.id,
                quantity: qty,
                type: 'quickset'
            });
        },

        addDateRangeInventory() {
            if (!this.form.id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Save the room first.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2200
                });
                return;
            }
            if (!this.inventoryStartDate || !this.inventoryEndDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please select start and end dates.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2200
                });
                return;
            }
            if (this.inventoryStartDate > this.inventoryEndDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Start date must be before end date.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2200
                });
                return;
            }
            this.sendInventoryRequest({
                room_id: this.form.id,
                start_date: this.inventoryStartDate,
                end_date: this.inventoryEndDate,
                quantity: this.inventoryQtyRange,
                is_available: this.inventoryAvailable,
                type: 'range'
            });
        },

        sendInventoryRequest(data) {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            let endpoint = '/admin/inventory/store';
            
            if (data.type === 'quickset') {
                endpoint = '/admin/inventory/quickset';
            }

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(async (r) => {
                const raw = await r.text();
                let res;
                try {
                    res = raw ? JSON.parse(raw) : {};
                } catch (e) {
                    throw new Error(`${r.status} ${r.statusText} - response not JSON`);
                }

                if (!r.ok || res.success === false) {
                    const msg = res.message
                        || (res.errors ? Object.values(res.errors).flat().join('\n') : null)
                        || `Request failed (${r.status})`;
                    throw new Error(msg);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Saved',
                    text: res.message || 'Saved',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1800
                });
                this.inventoryStartDate = '';
                this.inventoryEndDate = '';
                this.inventoryQtyRange = 10;
                this.loadInventoryPreview();
                // Refresh the page so the listing table reflects new availability
                setTimeout(() => window.location.reload(), 300);
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2600
                });
            });
        },

        loadInventoryPreview() {
            if (!this.form.id) return;
            const previewDiv = document.getElementById('inventoryPreview');
            if (!previewDiv) return;

            previewDiv.innerHTML = '<span class="text-gray-600">Loading...</span>';

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

            fetch(`/api/inventory/${this.form.id}`, { signal: controller.signal })
                .then(response => {
                    clearTimeout(timeoutId);
                    if (!response.ok) {
                        throw new Error(`API returned ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.items || data.items.length === 0) {
                        previewDiv.innerHTML = '<span class="text-gray-500">No inventory set yet. Use Quick Set or Custom Date Range above.</span>';
                        return;
                    }
                    let html = '<table class="w-full text-sm"><tr class="border-b"><th class="text-left py-1">Date</th><th class="text-center py-1">Qty</th><th class="text-center py-1">Status</th></tr>';
                    data.items.slice(0, 30).forEach(inv => {
                        const dateObj = new Date(inv.date);
                        const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const status = inv.is_available ? 'Ã¢Å“â€¦ Available' : 'Ã°Å¸Å¡Â« Blocked';
                        html += `<tr class="border-b"><td class="py-1">${dateStr}</td><td class="text-center">${inv.min_quantity}</td><td class="text-center text-xs">${status}</td></tr>`;
                    });
                    html += '</table>';
                    previewDiv.innerHTML = html;
                })
                .catch(err => {
                    clearTimeout(timeoutId);
                    console.error('Inventory preview error:', err);
                    
                    // Show helpful message
                    let errorMsg = 'No inventory data available yet.';
                    if (err.name === 'AbortError') {
                        errorMsg = 'Request timed out. The API endpoint may not be responding.';
                    } else if (err.message.includes('404')) {
                        errorMsg = 'Inventory API endpoint not found. Use Quick Set or Custom Date Range to add inventory.';
                    }
                    
                    previewDiv.innerHTML = `<span class="text-amber-600 text-sm">${errorMsg}</span>`;
                });
        },

        getCurrentSeasonPrice(rates) {
            const currentSeason = @js($currentSeason);
            const seasonKeyMap = {
                'Non-Peak': 'nonPeak',
                'Peak': 'peak',
                'Super-Peak': 'superPeak'
            };
            const key = seasonKeyMap[currentSeason] || 'nonPeak';
            return rates[key] || 0;
        },



        deleteStaycation(id, route) {
            Swal.fire({
                title: 'Delete Room?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    fetch(route, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        body: formData
                    }).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Room deleted successfully.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            window.location.reload();
                        });
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete room.'
                        });
                    });
                }
            });
        },

        deleteImage(id, route) {
            Swal.fire({
                title: 'Delete Image?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    fetch(route, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        body: formData
                    }).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Image deleted successfully.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            window.location.reload();
                        });
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete image.'
                        });
                    });
                }
            });
        },

        deleteText(id, route) {
            Swal.fire({
                title: 'Delete Features?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    fetch(route, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        body: formData
                    }).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Features deleted successfully.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            window.location.reload();
                        });
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete features.'
                        });
                    });
                }
            });
        },

        editImage(id, staycationId, title) {
            this.currentImageId = id;
            this.currentStaycationId = staycationId;
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            this.currentStaycationId = '';
            this.currentImageTitle = '';
            if (this.$refs.title) this.$refs.title.value = '';
            if (this.$refs.image) this.$refs.image.value = '';
        },

        editText(el, id, staycationId, section, title) {
            this.form.id = id;
            this.form.staycation_id = staycationId;
            this.form.section = section || '';
            this.form.label = title || '';
            this.form.text = el.getAttribute('data-content') || '';
            this.editMode = true;
            this.showTextModal = true;
        },

        resetTextForm() {
            this.form.id = null;
            this.form.staycation_id = '';
            this.form.section = '';
            this.form.label = '';
            this.form.text = '';
            this.editMode = false;
        }

    };
}

// Real-time inventory updates for admin panel
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize Echo if it's available (Laravel Echo is loaded)
    if (typeof window.Echo !== 'undefined') {
        // Listen for inventory updates
        window.Echo.channel('inventory')
            .listen('.inventory.updated', (e) => {
                console.log('Admin: Inventory updated:', e);

                // Update the "Today Avail" column for the affected staycation
                const availElement = document.querySelector(`.today-avail-${e.staycationId}`);
                if (availElement) {
                    // Fetch updated availability for today
                    fetch(`/api/inventory/${e.staycationId}`)
                        .then(response => response.json())
                        .then(data => {
                            const items = data.items || [];
                            const today = new Date().toISOString().split('T')[0];
                            const todayItem = items.find(item => item.date === today);
                            const todayAvail = todayItem ? todayItem.min_quantity : 0;

                            availElement.textContent = todayAvail;

                            // Optional: Add visual feedback
                            availElement.style.transition = 'background-color 0.3s';
                            availElement.style.backgroundColor = '#ffff99';
                            setTimeout(() => {
                                availElement.style.backgroundColor = '';
                            }, 1000);
                        })
                        .catch(error => console.error('Failed to fetch updated inventory:', error));
                }
            });
    }
});

</script>


<style>
.tilt-card {
    transform-style: preserve-3d;
    perspective: 1200px;
    transition: transform .25s ease, box-shadow .25s ease;
}
.tilt-card:hover {
    transform: rotateX(6deg) rotateY(-6deg) translateY(-6px) scale(1.02);
    box-shadow: 0 16px 35px rgba(0,0,0,0.18);
}
.staycation-card-ghost {
    opacity: 0.45;
}
.staycation-card-chosen,
.staycation-card-drag {
    cursor: grabbing !important;
}
.staycation-card-chosen {
    transform: rotate(1deg) scale(1.01);
}
.price-config-ghost {
    opacity: 0.45;
}
.price-config-chosen,
.price-config-drag {
    cursor: grabbing !important;
}
.price-config-chosen {
    transform: rotate(0.5deg) scale(1.01);
}
</style>

<script>
if (typeof window.saveMobileLayoutSetting !== 'function') {
    window.saveMobileLayoutSetting = function(settingKey, value) {
        const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
        fetch('{{ route("admin.booking-settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ setting_key: settingKey, value: Number(value) })
        })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok || data.success === false) {
                throw new Error(data.message || 'Failed to update mobile layout setting.');
            }
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Mobile layout updated.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1800
            });
        })
        .catch((err) => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Failed to update mobile layout setting.'
            });
        });
    }
}
</script>

@endsection

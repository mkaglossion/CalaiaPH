@extends('layouts.admin')

@section('content')
<style>
    .undo-banner {
        position: fixed;
        bottom: 20px;
        left: 20px;
        padding: 16px 20px;
        background: #374151;
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 9998;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .undo-banner button {
        background: #fbbf24;
        color: #1f2937;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }
    .undo-banner button:hover { background: #fcd34d; }
    .swal2-container {
        z-index: 12000 !important;
    }
</style>

<div class="p-4 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl space-y-6">

    <h1 class="text-3xl md:text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Pricing Configuration
    </h1>
    <p class="text-gray-600">Manage your pricing rules, durations, holidays, and rate matrix.</p>

    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        This is the global rate matrix used as the default pricing source. Room-specific overrides are managed inside <strong>Staycation Rooms</strong>.
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Success!',
                    text: @json(session('success')),
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Warning!',
                    text: @json(session('warning')),
                    icon: 'warning',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: @json(session('error')),
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    {{-- ========================= --}}
    {{-- Section 1: Weekend Rules --}}
    {{-- ========================= --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold mb-4">Weekend Rules</h2>
        
        <p class="text-sm text-gray-500 mb-3">
            Select which days should be considered <strong>Weekend</strong>. 
            All unselected days will be treated as <strong>Weekday</strong>.
        </p>

        <form action="{{ route('admin.dayTypes.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div
                class="grid grid-cols-7 gap-2 text-center sm:grid-cols-4"
                x-data="{
                    weekendDays: {{ json_encode(array_map('strval', $weekendDays ?? [])) }}
                }"
            >
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $abbr)
                    @php $isWeekend = in_array($i, $weekendDays ?? []); @endphp

                    <label for="weekend-day-{{ $i }}" class="cursor-pointer select-none">
                        <input
                            type="checkbox"
                            id="weekend-day-{{ $i }}"
                            name="weekend_days[]"
                            value="{{ $i }}"
                            class="sr-only peer"
                            x-model="weekendDays"
                        />

                        <div
                            class="flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all duration-200"
                            :class="weekendDays.includes(String({{ $i }})) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                        >
                            <svg class="w-5 h-5 mb-1" :class="weekendDays.includes(String({{ $i }})) ? 'text-blue-600' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20" x-show="weekendDays.includes(String({{ $i }}))">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <svg class="w-5 h-5 mb-1" :class="weekendDays.includes(String({{ $i }})) ? 'text-blue-600' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!weekendDays.includes(String({{ $i }}))">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>

                            <span
                                class="text-sm font-semibold"
                                :class="weekendDays.includes(String({{ $i }})) ? 'text-blue-700' : 'text-gray-600'"
                            >
                                {{ $abbr }}
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 flex items-center gap-2 text-sm">
                <span class="flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Selected = Weekend
                </span>
                <span class="flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-800 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Unselected = Weekday
                </span>
            </div>

            <button class="mt-4 w-full md:w-auto px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                Save Weekend Rules
            </button>
        </form>
    </div>

    {{-- ========================= --}}
    {{-- Section 2: Durations (Peak Periods) --}}
    {{-- ========================= --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6" x-data="durationManager()">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-semibold">Durations (Season Types)</h2>
                <p class="text-sm text-gray-500">Configure Non-Peak, Peak, and Super-Peak periods</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                + Add Duration
            </button>
        </div>

        {{-- Duration Legend --}}
        <div class="flex gap-4 mb-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-green-100 border border-green-300 rounded"></span>
                <span>Non-Peak (Priority: 1)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></span>
                <span>Peak (Priority: 2)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-red-100 border border-red-300 rounded"></span>
                <span>Super-Peak (Priority: 3 - Highest)</span>
            </div>
        </div>

        {{-- Duration Table --}}
        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <table class="w-full text-left min-w-160">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Type</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Start Date</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">End Date</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($peakTypes as $peakType)
                        @foreach($peakType->periods as $period)
                            <tr class="{{ $peakType->name === 'Super-Peak' ? 'bg-red-50' : ($peakType->name === 'Peak' ? 'bg-yellow-50' : 'bg-green-50') }} hover:bg-gray-50/70 transition">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $peakType->name === 'Super-Peak' ? 'bg-red-200 text-red-800' : ($peakType->name === 'Peak' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800') }}">
                                        {{ $peakType->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $period->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-4">
                                        <button 
                                            @click="editPeriod({{ $period->id }}, {{ $peakType->id }}, '{{ $period->start_date }}', '{{ $period->end_date }}', {{ $period->name ? "'$period->name'" : 'null' }})"
                                            class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.peak-periods.destroy', $period->id) }}" method="POST" class="inline" onsubmit="return queuePricingDelete(this, 'duration period', @js($period->name ?? 'Unnamed Duration'))">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit"
                                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                                >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No duration periods configured. Click "Add Duration" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cross-Year Note --}}
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
            <strong>Note:</strong> For periods that cross year boundaries (e.g., Dec 20 - Jan 4), 
            create two separate entries:
            <ul class="list-disc list-inside mt-1">
                <li>Dec 20, 2026 - Dec 31, 2026</li>
                <li>Jan 1, 2027 - Jan 4, 2027</li>
            </ul>
        </div>

        <!-- Duration Modal -->
        <template x-teleport="body">
            <div
                x-show="modalOpen"
                x-transition.opacity
                x-cloak
                @keydown.escape.window="closeModal()"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            >
                <div
                    @click.away="closeModal()"
                    class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-5 sm:p-7 relative border border-gray-200"
                >
                    <button @click="closeModal()" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>

                    <h3 class="text-xl font-bold mb-1 text-gray-900" x-text="editMode ? 'Edit Duration' : 'Add Duration'"></h3>
                    <p class="text-sm text-gray-600 mb-4">Choose season type and set the date range.</p>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        @csrf
                        <template x-if="editMode">@method('PUT')</template>

                        <div
                            x-show="formError"
                            x-cloak
                            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            x-text="formError"
                        ></div>

                        <div class="mb-4">
                            <span class="block font-semibold mb-2">Duration Type</span>
                            <div class="grid gap-2 md:grid-cols-3 sm:grid-cols-2 grid-cols-1">
                                <template x-for="type in predefinedTypes" :key="type.id">
                                    <button
                                        type="button"
                                        @click="selectType(type.id)"
                                        class="px-3 py-2 text-sm rounded border transition"
                                        :class="selectedType === type.id
                                            ? type.colorClass + ' ' + type.borderClass
                                            : 'bg-white hover:bg-gray-100 border-gray-300'">
                                        <span x-text="type.label"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="peak_type_id" x-model="form.peak_type_id">
                            @error('peak_type_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="duration-name" class="block font-semibold mb-1">Period Name (Optional)</label>
                            <input 
                                type="text" 
                                id="duration-name"
                                name="name"
                                x-model="form.name"
                                placeholder="e.g., Holy Week, Halloween, Holiday Season"
                                class="border rounded w-full p-2"
                                autocomplete="off">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="duration-start-date" class="block font-semibold mb-1">Start Date</label>
                                <input 
                                    type="date" 
                                    id="duration-start-date"
                                    name="start_date"
                                    x-model="form.start_date"
                                    class="border rounded w-full p-2"
                                    required
                                    autocomplete="off">
                            </div>

                            <div class="mb-4">
                                <label for="duration-end-date" class="block font-semibold mb-1">End Date</label>
                                <input 
                                    type="date" 
                                    id="duration-end-date"
                                    name="end_date"
                                    x-model="form.end_date"
                                    class="border rounded w-full p-2"
                                    required
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border rounded">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                                <span x-text="editMode ? 'Update' : 'Save'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    {{-- ========================= --}}
    {{-- Section 3: Declared Holidays --}}
    {{-- ========================= --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6" x-data="holidayManager()">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-semibold">Declared Holidays</h2>
                <p class="text-sm text-gray-500">Holidays take priority over weekend/weekday rules</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                + Add Holiday
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <table class="w-full text-left min-w-160">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($holidays as $holiday)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4">{{ $holiday->name }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex justify-end gap-4">
                                    <button 
                                        @click="editHoliday({{ $holiday->id }}, '{{ addslashes($holiday->name) }}', '{{ $holiday->date }}')"
                                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" class="inline" onsubmit="return queuePricingDelete(this, 'holiday', @js($holiday->name))">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit"
                                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                            >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                No holidays configured. Add holidays for special dates.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Holiday Modal -->
        <template x-teleport="body">
            <div x-show="modalOpen" x-transition x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div @click.away="closeModal()" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-7 border border-gray-200 max-h-[95vh] overflow-y-auto relative">
                    <button 
                        @click="closeModal()"
                        class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
                    >&times;</button>

                    <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Holiday' : 'Add Holiday'"></h3>

                    <form @submit.prevent="submitForm">
                        @csrf
                        <template x-if="editMode">
                            @method('PUT')
                        </template>

                        <div class="mb-4">
                            <label for="holiday-name" class="block font-semibold mb-1">Holiday Name</label>
                            <input 
                                type="text" 
                                id="holiday-name"
                                name="name"
                                x-model="form.name"
                                placeholder="e.g., Christmas Day, New Year's Day"
                                class="border rounded w-full p-2"
                                required
                                autocomplete="off">
                        </div>

                        <div class="mb-4">
                            <label for="holiday-date" class="block font-semibold mb-1">Holiday Date</label>
                            <input 
                                type="date" 
                                id="holiday-date"
                                name="date"
                                x-model="form.date"
                                class="border rounded w-full p-2"
                                required
                                autocomplete="off">
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border rounded">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                                <span x-text="editMode ? 'Update' : 'Save'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>



    {{-- ========================= --}}
    {{-- Section 4: Rate Matrix --}}
    {{-- ========================= --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6" x-data="rateMatrixManager()">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-semibold">Rate Matrix</h2>
                <p class="text-sm text-gray-500">Set prices per unit type, season type, and day type</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                + Add Rate
            </button>
        </div>

        @if($pricingConfigs->isEmpty())
            <div class="text-center py-8 text-gray-500">
                No rates configured. Add your first rate configuration below.
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100/70 backdrop-blur">
                            <tr>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Unit Type</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Season Type</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Day Type</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Price</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pricingConfigs as $unitType => $configs)
                                @foreach($configs as $config)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-6 py-4">{{ $config->unit_type }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $config->peakType->name === 'Super-Peak' ? 'bg-red-200 text-red-800' : ($config->peakType->name === 'Peak' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800') }}">
                                                {{ $config->peakType->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $config->dayType->name === 'Weekend' ? 'bg-purple-200 text-purple-800' : ($config->dayType->name === 'Holiday' ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-800') }}">
                                                {{ $config->dayType->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold">
                                            ₱{{ number_format($config->price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="flex justify-end gap-4">
                                                <button 
                                                    @click="editRate({{ $config->id }}, '{{ $config->unit_type }}', {{ $config->peak_type_id }}, {{ $config->day_type_id }}, {{ $config->price }})"
                                                    class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition">
                                                    Edit
                                                </button>
                                                <form action="{{ route('admin.pricing-configs.destroy', $config->id) }}" method="POST" class="inline" onsubmit="return queuePricingDelete(this, 'rate', @js($config->unit_type . ' - ' . $config->peakType->name . ' / ' . $config->dayType->name))">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit"
                                                        class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                                        >
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Rate Matrix Modal -->
        <template x-teleport="body">
            <div x-show="modalOpen" x-transition x-cloak class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div @click.away="closeModal()" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-7 border border-gray-200 max-h-[95vh] overflow-y-auto relative">
                    <button 
                        @click="closeModal()"
                        class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
                    >&times;</button>

                    <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Rate' : 'Add Rate'"></h3>

                    <form @submit.prevent="submitForm">
                        @csrf
                        <template x-if="editMode">
                            @method('PUT')
                        </template>

                        <div class="mb-4">
                            <label for="rate-unit-type" class="block font-semibold mb-1">Unit Type</label>
                            <input 
                                type="text" 
                                id="rate-unit-type"
                                name="unit_type"
                                x-model="form.unit_type"
                                placeholder="e.g., Glamping Tent, Cabin, Villa"
                                class="border rounded w-full p-2"
                                required
                                autocomplete="off">
                        </div>

                        <div class="mb-4">
                            <label for="rate-season-type" class="block font-semibold mb-2">Season Type</label>
                            <select id="rate-season-type" name="peak_type_id" x-model="form.peak_type_id" class="border rounded w-full p-2" required>
                                <option value="">Select Season Type</option>
                                @foreach($peakTypes as $peakType)
                                    <option value="{{ $peakType->id }}">{{ $peakType->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="rate-day-type" class="block font-semibold mb-2">Day Type</label>
                            <select id="rate-day-type" name="day_type_id" x-model="form.day_type_id" class="border rounded w-full p-2" required>
                                <option value="">Select Day Type</option>
                                @foreach($dayTypes as $dayType)
                                    <option value="{{ $dayType->id }}">{{ $dayType->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="rate-price" class="block font-semibold mb-1">Price (₱)</label>
                            <input 
                                type="number" 
                                id="rate-price"
                                name="price"
                                x-model="form.price"
                                step="0.01"
                                min="0"
                                class="border rounded w-full p-2"
                                required
                                autocomplete="off">
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border rounded">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                                <span x-text="editMode ? 'Update' : 'Save'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>



    {{-- ========================= --}}
    {{-- Section 5: Price Calculator --}}
    {{-- ========================= --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6" x-data="priceCalculator()">
        <h2 class="text-xl font-semibold mb-4">Price Calculator</h2>
        <p class="text-sm text-gray-500 mb-4">Test your pricing configuration by calculating a booking price</p>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label for="calculator-unit-type" class="block font-semibold mb-1">Unit Type</label>
                <input 
                    type="text" 
                    id="calculator-unit-type"
                    name="unitType"
                    x-model="unitType"
                    placeholder="e.g., Glamping Tent"
                    class="border rounded w-full p-2"
                    autocomplete="off">
            </div>
            <div>
                <label for="calculator-check-in" class="block font-semibold mb-1">Check-in Date</label>
                <input 
                    type="date" 
                    id="calculator-check-in"
                    name="checkIn"
                    x-model="checkIn"
                    class="border rounded w-full p-2"
                    autocomplete="off">
            </div>
            <div>
                <label for="calculator-check-out" class="block font-semibold mb-1">Check-out Date</label>
                <input 
                    type="date" 
                    id="calculator-check-out"
                    name="checkOut"
                    x-model="checkOut"
                    class="border rounded w-full p-2"
                    autocomplete="off">
            </div>
            <div class="flex items-end">
                <button 
                    @click="calculatePrice()"
                    :disabled="!unitType || !checkIn || !checkOut"
                    class="px-4 py-2 rounded-xl bg-green-600 text-white font-medium shadow hover:bg-green-700 hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Calculate Price
                </button>
            </div>
        </div>

        <div x-show="loading" class="text-center py-4 text-gray-500">
            Calculating...
        </div>

        <div x-show="result && result.total_price" x-transition class="mt-4">
            <div class="bg-gray-50 border rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Calculation Result</h3>
                    <span class="text-2xl font-bold text-green-600">₱<span x-text="result && result.total_price ? result.total_price.toLocaleString() : '0'"></span></span>
                </div>
                
                <p class="text-sm text-gray-500 mb-2" x-show="result && result.unit_type">
                    <span x-text="result && result.unit_type ? result.unit_type : ''"></span> • 
                    <span x-text="result && result.nights ? result.nights : ''"></span> night(s) • 
                    <span x-text="result && result.check_in ? result.check_in : ''"></span> to <span x-text="result && result.check_out ? result.check_out : ''"></span>
                </p>

                <table x-show="result && result.breakdown" class="w-full border text-sm mt-4">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1 text-left">Date</th>
                            <th class="border px-2 py-1 text-left">Day</th>
                            <th class="border px-2 py-1 text-left">Season</th>
                            <th class="border px-2 py-1 text-left">Day Type</th>
                            <th class="border px-2 py-1 text-right">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="night in (result && result.breakdown ? result.breakdown : [])" :key="night.date">
                            <tr>
                                <td class="border px-2 py-1" x-text="night.date"></td>
                                <td class="border px-2 py-1" x-text="night.day_of_week"></td>
                                <td class="border px-2 py-1">
                                    <span class="px-2 py-0.5 rounded text-xs" 
                                        :class="night.season_type === 'Super-Peak' ? 'bg-red-100' : (night.season_type === 'Peak' ? 'bg-yellow-100' : 'bg-green-100')"
                                        x-text="night.season_type"></span>
                                </td>
                                <td class="border px-2 py-1" x-text="night.day_type"></td>
                                <td class="border px-2 py-1 text-right font-semibold" x-text="'₱' + (night.rate ? night.rate.toLocaleString() : '0')"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="error" x-transition class="mt-4 p-3 bg-red-100 border border-red-300 rounded text-red-700">
            <span x-text="error"></span>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- Section 6: Pricing Rules Summary --}}
    {{-- ========================= --}}
    <div class="bg-blue-50 border border-blue-200 rounded p-5">
        <h3 class="text-lg font-semibold mb-3">Pricing Rules Summary</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <h4 class="font-semibold mb-2">Season Priority (Highest to Lowest)</h4>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Super-Peak</li>
                    <li>Peak</li>
                    <li>Non-Peak</li>
                </ol>
            </div>
            
            <div>
                <h4 class="font-semibold mb-2">Day Type Priority (Highest to Lowest)</h4>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Holiday (takes priority over all)</li>
                    <li>Weekend</li>
                    <li>Weekday</li>
                </ol>
            </div>
            
            <div>
                <h4 class="font-semibold mb-2">Calculation Flow</h4>
                <ol class="list-decimal list-inside space-y-1">
                    <li>For each night of stay</li>
                    <li>Determine Season Type</li>
                    <li>Determine Day Type</li>
                    <li>Get rate from matrix</li>
                    <li>Sum all nightly rates</li>
                </ol>
            </div>
        </div>
    </div>

</div>

<script>
    function queuePricingDelete(form, itemType, itemName) {
        const existing = document.querySelector('.undo-banner');
        if (existing) existing.remove();

        const banner = document.createElement('div');
        banner.className = 'undo-banner';

        let secondsLeft = 4;
        const safeItemType = String(itemType || 'item');
        const safeItemName = String(itemName || '').trim() || 'Untitled';

        banner.innerHTML = `
            <span>${safeItemType.charAt(0).toUpperCase() + safeItemType.slice(1)} "${safeItemName}" marked for deletion</span>
            <button type="button">Undo (${secondsLeft}s)</button>
        `;

        document.body.appendChild(banner);

        const undoButton = banner.querySelector('button');
        const timer = setInterval(() => {
            secondsLeft--;
            if (undoButton) {
                undoButton.textContent = `Undo (${secondsLeft}s)`;
            }

            if (secondsLeft <= 0) {
                clearInterval(timer);
                banner.remove();
                form.submit();
            }
        }, 1000);

        undoButton?.addEventListener('click', () => {
            clearInterval(timer);
            banner.remove();
        });

        return false;
    }

    function escapePricingToastText(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showPricingToast(title, message, icon = 'success') {
        Swal.fire({
            title,
            html: escapePricingToastText(message).replace(/\n/g, '<br>'),
            icon,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    // Duration Manager
    function durationManager() {
        return {
            modalOpen: false,
            editMode: false,
            selectedType: '',
            formError: '',
            submitting: false,
            predefinedTypes: @js($peakTypes->map(function($type) {
                $colorClass = $type->name === 'Super-Peak' ? 'bg-red-600 text-white' : ($type->name === 'Peak' ? 'bg-yellow-500 text-white' : 'bg-green-600 text-white');
                $borderClass = $type->name === 'Super-Peak' ? 'border-red-600' : ($type->name === 'Peak' ? 'border-yellow-500' : 'border-green-600');
                return [
                    'id' => (string) $type->id,
                    'label' => $type->name,
                    'colorClass' => $colorClass,
                    'borderClass' => $borderClass
                ];
            })),
            form: {
                id: null,
                peak_type_id: '',
                start_date: '',
                end_date: '',
                name: ''
            },
            
            openModal() {
                this.editMode = false;
                this.selectedType = '';
                this.formError = '';
                this.submitting = false;
                this.form = { id: null, peak_type_id: '', start_date: '', end_date: '', name: '' };
                this.modalOpen = true;
            },
            
            selectType(typeId) {
                this.selectedType = typeId;
                this.form.peak_type_id = typeId;
            },
            
            editPeriod(id, peakTypeId, startDate, endDate, name) {
                this.editMode = true;
                this.selectedType = String(peakTypeId);
                this.formError = '';
                this.submitting = false;
                this.form = {
                    id: id,
                    peak_type_id: String(peakTypeId),
                    start_date: startDate,
                    end_date: endDate,
                    name: name || ''
                };
                this.modalOpen = true;
            },
            
            closeModal() {
                this.modalOpen = false;
                this.editMode = false;
                this.selectedType = '';
                this.formError = '';
                this.submitting = false;
                this.form = { id: null, peak_type_id: '', start_date: '', end_date: '', name: '' };
            },
            
            async submitForm() {
                if (this.submitting) return;

                const isUpdate = this.editMode && this.form.id;
                const url = isUpdate
                    ? '{{ route("admin.peakPeriods.update", ":id") }}'.replace(':id', this.form.id)
                    : '{{ route("admin.peakPeriods.store") }}';
                const method = isUpdate ? 'PUT' : 'POST';
                this.formError = '';
                this.submitting = true;
                
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form),
                        credentials: 'same-origin'
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok) {
                        this.closeModal();
                        showPricingToast('Success!', data.message || (isUpdate ? 'Duration updated successfully!' : 'Duration added successfully!'), 'success');
                        window.location.reload();
                    } else if (data.errors) {
                        const errors = Object.values(data.errors).flat().join('\n');
                        this.formError = errors || 'Operation failed.';
                        showPricingToast('Error!', errors, 'error');
                    } else {
                        this.formError = data.message || 'Operation failed.';
                        showPricingToast('Error!', this.formError, 'error');
                    }
                } catch (err) {
                    console.error(err);
                    this.formError = 'Error submitting duration.';
                    showPricingToast('Error!', this.formError, 'error');
                } finally {
                    this.submitting = false;
                }
            }
        }
    }

    // Holiday Manager
    function holidayManager() {
        return {
            modalOpen: false,
            editMode: false,
            form: {
                id: null,
                name: '',
                date: ''
            },
            
            openModal() {
                this.editMode = false;
                this.form = { id: null, name: '', date: '' };
                this.modalOpen = true;
            },
            
            editHoliday(id, name, date) {
                this.editMode = true;
                this.form = { id, name, date };
                this.modalOpen = true;
            },
            
            closeModal() {
                this.modalOpen = false;
                this.editMode = false;
                this.form = { id: null, name: '', date: '' };
            },
            
            async submitForm() {
                const isUpdate = this.editMode && this.form.id;
                const url = isUpdate
                    ? '{{ route("admin.holidays.update", ":id") }}'.replace(':id', this.form.id)
                    : '{{ route("admin.holidays.store") }}';
                const method = isUpdate ? 'PUT' : 'POST';
                
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form),
                        credentials: 'same-origin'
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok) {
                        showPricingToast('Success!', data.message || (isUpdate ? 'Holiday updated successfully!' : 'Holiday added successfully!'), 'success');
                        this.closeModal();
                        window.location.reload();
                    } else if (data.errors) {
                        const errors = Object.values(data.errors).flat().join('\n');
                        showPricingToast('Error!', errors, 'error');
                    } else {
                        showPricingToast('Error!', data.message || 'Operation failed.', 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showPricingToast('Error!', 'Error submitting holiday.', 'error');
                }
            }
        }
    }

    // Rate Matrix Manager
    function rateMatrixManager() {
        return {
            modalOpen: false,
            editMode: false,
            form: {
                id: null,
                unit_type: '',
                peak_type_id: '',
                day_type_id: '',
                price: ''
            },
            
            openModal() {
                this.editMode = false;
                this.form = { id: null, unit_type: '', peak_type_id: '', day_type_id: '', price: '' };
                this.modalOpen = true;
            },
            
            editRate(id, unitType, peakTypeId, dayTypeId, price) {
                this.editMode = true;
                this.form = { id, unit_type: unitType, peak_type_id: peakTypeId, day_type_id: dayTypeId, price: price };
                this.modalOpen = true;
            },
            
            closeModal() {
                this.modalOpen = false;
                this.editMode = false;
                this.form = { id: null, unit_type: '', peak_type_id: '', day_type_id: '', price: '' };
            },
            
            async submitForm() {
                const isUpdate = this.editMode && this.form.id;
                const url = isUpdate
                    ? '{{ route("admin.pricing-configs.update", ":id") }}'.replace(':id', this.form.id)
                    : '{{ route("admin.pricing-configs.store") }}';
                const method = isUpdate ? 'PUT' : 'POST';
                
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form),
                        credentials: 'same-origin'
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok) {
                        showPricingToast('Success!', data.message || (isUpdate ? 'Rate updated successfully!' : 'Rate added successfully!'), 'success');
                        this.closeModal();
                        window.location.reload();
                    } else if (data.errors) {
                        const errors = Object.values(data.errors).flat().join('\n');
                        showPricingToast('Error!', errors, 'error');
                    } else {
                        showPricingToast('Error!', data.message || 'Operation failed.', 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showPricingToast('Error!', 'Error submitting rate.', 'error');
                }
            }
        }
    }

    // Price Calculator
    function priceCalculator() {
        return {
            unitType: '',
            checkIn: '',
            checkOut: '',
            loading: false,
            result: null,
            error: null,
            
            async calculatePrice() {
                this.loading = true;
                this.result = null;
                this.error = null;
                
                try {
                    const res = await fetch('/admin/pricing/calculate-price', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            unit_type: this.unitType,
                            check_in: this.checkIn,
                            check_out: this.checkOut
                        }),
                        credentials: 'same-origin'
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok) {
                        this.result = data;
                    } else {
                        this.error = data.message || 'Calculation failed.';
                    }
                } catch (err) {
                    console.error(err);
                    this.error = 'Error calculating price.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection

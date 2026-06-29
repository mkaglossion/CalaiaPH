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
    @php
        $seasonDurationTypes = $peakTypes->map(function($type) {
            $colorClass = $type->name === 'Super-Peak' ? 'bg-red-600 text-white' : ($type->name === 'Peak' ? 'bg-yellow-500 text-white' : 'bg-green-600 text-white');
            $borderClass = $type->name === 'Super-Peak' ? 'border-red-600' : ($type->name === 'Peak' ? 'border-yellow-500' : 'border-green-600');
            return [
                'id' => (string) $type->id,
                'label' => $type->name,
                'colorClass' => $colorClass,
                'borderClass' => $borderClass
            ];
        })->values();
        $seasonDurationRoutes = [
            'store' => route('admin.seasonDurations.periods.store'),
            'update' => route('admin.seasonDurations.periods.update', ['peakPeriod' => '__ID__']),
        ];
    @endphp

    <h1 class="text-3xl md:text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Season Durations
    </h1>
    <p class="text-gray-600">Manage season date ranges used by staycation bookings.</p>

    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        This page only manages season durations. Room rates are configured inside <strong>Staycation Rooms</strong>.
    </div>

    @if(session('success'))
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Success!"
            data-admin-flash-type="success"
            data-admin-flash-message="{{ e(session('success')) }}"
        ></div>
    @endif
    @if(session('warning'))
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Warning!"
            data-admin-flash-type="warning"
            data-admin-flash-message="{{ e(session('warning')) }}"
        ></div>
    @endif
    @if(session('error'))
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Error!"
            data-admin-flash-type="error"
            data-admin-flash-message="{{ e(session('error')) }}"
        ></div>
    @endif

    {{-- ========================= --}}
    {{-- Section 2: Season Durations --}}
    {{-- ========================= --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6" x-data="durationManager(@js($seasonDurationTypes), @js($seasonDurationRoutes))">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-semibold">Season Durations</h2>
                <p class="text-sm text-gray-500">Define the active date ranges for Non-Peak, Peak, and Super-Peak.</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                + Add Duration
            </button>
        </div>

        {{-- Duration Legend --}}
        <div class="flex gap-4 mb-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-green-100 border border-green-300 rounded"></span>
                <span>Non-Peak</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></span>
                <span>Peak</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 bg-red-100 border border-red-300 rounded"></span>
                <span>Super-Peak</span>
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
                                        <form action="{{ route('admin.seasonDurations.periods.destroy', $period->id) }}" method="POST" class="inline" onsubmit="return queuePricingDelete(this, 'season duration', @js($period->name ?? 'Unnamed Duration'))">
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
                                No season durations configured. Click "Add Duration" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cross-Year Note --}}
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
            <strong>Note:</strong> If a duration crosses year-end, split it into two entries:
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

                    <h3 class="text-xl font-bold mb-1 text-gray-900" x-text="editMode ? 'Edit Season Duration' : 'Add Season Duration'"></h3>
                    <p class="text-sm text-gray-600 mb-4">Choose a season type and set the date range.</p>

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
                            <span class="block font-semibold mb-2">Season Type</span>
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
                            <label for="duration-name" class="block font-semibold mb-1">Duration Name (Optional)</label>
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
</div>

@endsection

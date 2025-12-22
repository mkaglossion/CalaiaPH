@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6 space-y-8">

    <h1 class="text-2xl font-bold">Pricing Config</h1>

    @if(session('success'))
        <div class="bg-green-200 p-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- ========================= --}}
    {{-- Section 1: Weekday / Weekend --}}
    {{-- ========================= --}}
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Define Weekend Days</h2>

        <form action="{{ route('admin.dayTypes.update') }}" method="POST">
            @csrf
            @method('PUT')

            <p class="text-sm text-gray-500 mb-3">
                Select which days should be considered <strong>Weekend</strong>.
                All unselected days will be treated as Weekdays.
            </p>

            <div class="grid grid-cols-7 gap-2 text-center">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $abbr)
                    <label class="flex flex-col items-center">
                        <input
                            type="checkbox"
                            name="weekend[]"
                            value="{{ $i }}"
                            class="mb-1"
                            {{ in_array($i, $weekendDays ?? []) ? 'checked' : '' }}>
                        <span class="text-sm">{{ $abbr }}</span>
                    </label>
                @endforeach
            </div>

            <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                Save Weekend Rules
            </button>
        </form>
    </div>

    {{-- ========================= --}}
    {{-- Section 2: Durations --}}
    {{-- ========================= --}}
    <div class="bg-white p-5 rounded shadow" x-data="durationModal()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">
                Durations (Non-Peak / Peak / Super-Peak)
            </h2>
            <button
                @click="open = true"
                class="px-4 py-2 bg-blue-600 text-white rounded">
                + Add Duration
            </button>
        </div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">Type</th>
                    <th class="border px-2 py-1">Start</th>
                    <th class="border px-2 py-1">End</th>
                </tr>
            </thead>
            <tbody>
                @foreach($peakTypes as $peakType)
                    @foreach($peakType->periods as $period)
                        <tr>
                            <td class="border px-2 py-1">{{ $peakType->name }}</td>
                            <td class="border px-2 py-1">{{ $period->start_date }}</td>
                            <td class="border px-2 py-1">{{ $period->end_date }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Duration Modal -->
        <div
            x-show="open"
            x-transition
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

            <div
                @click.away="close()"
                class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">

                <h3 class="text-lg font-semibold mb-4">Add Duration</h3>

                <div class="mb-4">
                    <label class="block font-semibold mb-2">Duration Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="type in predefinedTypes" :key="type.id">
                            <button
                                type="button"
                                @click="selectType(type.id)"
                                class="px-3 py-2 text-sm rounded border transition"
                                :class="selectedType === type.id
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 hover:bg-gray-200'">
                                <span x-text="type.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <form x-on:submit.prevent="submitDuration" method="POST">
                    @csrf
                    <input type="hidden" name="peak_type_id" x-model="selectedType">

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Start Date</label>
                        <input type="date" name="start_date" x-model="startDate" class="border rounded w-full p-2" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">End Date</label>
                        <input type="date" name="end_date" x-model="endDate" class="border rounded w-full p-2" required>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="close()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" :disabled="!selectedType" class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ========================= --}}
    {{-- Section 3: Holidays --}}
    {{-- ========================= --}}
    <div class="bg-white p-5 rounded shadow" x-data="holidayModal()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Declared Holidays</h2>
            <button @click="open = true" class="px-4 py-2 bg-blue-600 text-white rounded">+ Add Holiday</button>
        </div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">Name</th>
                    <th class="border px-2 py-1">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($holidays as $holiday)
                    <tr>
                        <td class="border px-2 py-1">{{ $holiday->name }}</td>
                        <td class="border px-2 py-1">{{ $holiday->date }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Holiday Modal -->
        <div
            x-show="open"
            x-transition
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

            <div @click.away="close()" class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Add Holiday</h3>

                <form action="{{ route('admin.holidays.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Holiday Name</label>
                        <input type="text" name="name" class="border rounded w-full p-2" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Holiday Date</label>
                        <input type="date" name="date" class="border rounded w-full p-2" required>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="close()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>

<script>

    const peakPeriodStoreUrl = "{{ route('admin.peakPeriods.store') }}"; // outputs /admin/peak-periods

    
    function durationModal() {
        return {
            open: false,
            selectedType: '',
            startDate: '',
            endDate: '',
            predefinedTypes: [
                { id: 'non-peak', label: 'Non-Peak' },
                { id: 'peak', label: 'Peak' },
                { id: 'super-peak', label: 'Super-Peak' }
            ],
            selectType(type) {
                this.selectedType = type;
            },
            close() {
                this.open = false;
                this.selectedType = '';
                this.startDate = '';
                this.endDate = '';
            },
            async submitDuration() {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const res = await fetch(peakPeriodStoreUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": token
                        },
                        body: JSON.stringify({
                            peak_type_id: this.selectedType,
                            start_date: this.startDate,
                            end_date: this.endDate
                        }),
                        credentials: "same-origin"
                    });



                    if (!res.ok) throw new Error('Network response was not ok');

                    const data = await res.json(); // you can return JSON from controller if needed
                    console.log('API Response:', data); // check in DevTools → Network & Console

                    this.close(); // close modal
                    location.reload(); // refresh table if needed
                } catch (error) {
                    console.error('Error submitting duration:', error);
                }
            }
        }
    }

    function holidayModal() {
        return {
            open: false,
            close() {
                this.open = false;
            }
        }
    }
</script>

@endsection

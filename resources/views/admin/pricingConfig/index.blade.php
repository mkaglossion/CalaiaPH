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
    <div class="bg-white p-5 rounded shadow" x-data="{
            open: false,
            editMode: false,
            holidays: @js($holidays),
            form: { id: null, name: '', date: '' },

            resetForm() {
                this.form = { id: null, name: '', date: '' };
                this.editMode = false;
            },

            editHoliday(id) {
                const holiday = this.holidays.find(h => h.id === id);
                if (holiday) {
                    this.form = { id: holiday.id, name: holiday.name, date: holiday.date };
                    this.editMode = true;
                    this.open = true;
                }
            },

            close() {
                this.open = false;
                this.resetForm();
            },

            async saveHoliday() {
                const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                const formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('date', this.form.date);

                let url = '{{ route('admin.holidays.store') }}';
                let method = 'POST';

                if (this.editMode && this.form.id) {
                    url = `/admin/holidays/${this.form.id}`;
                    formData.append('_method', 'PUT');
                    method = 'POST';
                }

                const btn = document.querySelector('#holidayForm button[type=submit]');
                btn.innerText = this.editMode ? 'Updating...' : 'Saving...';
                btn.disabled = true;

                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    const data = await res.json();

                    if (res.ok) {
                        alert(data.message || (this.editMode ? 'Holiday updated successfully!' : 'Holiday added successfully!'));
                        this.resetForm();
                        this.open = false;
                        window.location.reload();
                    } else if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert(errorMessages);
                    } else {
                        alert(data.message || 'Operation failed.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error submitting holiday.');
                } finally {
                    btn.innerText = this.editMode ? 'Update' : 'Save';
                    btn.disabled = false;
                }
            }
        }">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Declared Holidays</h2>
            <button @click="open = true" class="px-4 py-2 bg-blue-600 text-white rounded">+ Add Holiday</button>
        </div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">Name</th>
                    <th class="border px-2 py-1">Date</th>
                    <th class="border px-2 py-1 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($holidays as $holiday)
                    <tr>
                        <td class="border px-2 py-1">{{ $holiday->name }}</td>
                        <td class="border px-2 py-1">{{ $holiday->date }}</td>
                        <td class="border px-2 py-1 text-right space-x-2">
                            <button
                                class="text-blue-600 hover:underline"
                                @click="editHoliday({{ $holiday->id }})"
                            >
                                Edit
                            </button>
                            <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this holiday?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
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
                <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'Edit Holiday' : 'Add Holiday'"></h3>

                <form
                    id="holidayForm"
                    @submit.prevent="saveHoliday()"
                >
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Holiday Name</label>
                        <input
                            type="text"
                            x-model="form.name"
                            class="border rounded w-full p-2"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Holiday Date</label>
                        <input
                            type="date"
                            x-model="form.date"
                            class="border rounded w-full p-2"
                            required
                        >
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="close()" class="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <span x-text="editMode ? 'Update' : 'Save'"></span>
                        </button>
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

@extends('layouts.admin')

@section('title', 'Day Tours')

@section('content')
<div x-data="dayTourComponent()">

    <hr class="my-6">

    <!-- Add Day Tour Button -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Day Tour Packages</h2>
        <button 
            @click="showDayTourModal = true; resetForm()" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Day Tour Package
        </button>
    </div>

    <!-- Table of Day Tour Packages -->
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Package Name</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Included Activities</th>
                    <th class="px-4 py-2">Created At</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tours as $index => $tour)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $tour->name }}</td>
                    <td class="px-4 py-2">{{ $tour->description ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $tour->price ? '₱'.number_format($tour->price, 2) : '-' }}</td>
                    <td class="px-4 py-2">
                        @foreach($tour->activities as $activity)
                            <span class="inline-block bg-gray-200 px-2 py-1 rounded text-xs mr-1 mb-1">{{ $activity->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-2">{{ $tour->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button class="text-blue-600 hover:underline" @click="editDayTour({{ $tour->id }})">Edit</button>
                        <form action="{{ route('day-tours.destroy', $tour->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this package?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">No packages added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal to Add/Edit Day Tour -->
    <div 
        x-show="showDayTourModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div 
            @click.away="showDayTourModal = false"
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'Edit Day Tour Package' : 'Add Day Tour Package'"></h3>

            <form id="dayTourForm" @submit.prevent="saveDayTour" class="space-y-5">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Package Name</label>
                    <input type="text" x-model="form.name" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Package Name" required>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea x-model="form.description" rows="3" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Description"></textarea>
                </div>

                <!-- Price -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Price</label>
                    <input type="number" x-model="form.price" step="0.01" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Price">
                </div>

                <!-- Pick Activities -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Pick Activities to Include</label>
                    <div class="max-h-64 overflow-y-auto border rounded p-2">
                        @foreach($activities as $activity)
                            <div class="flex items-center mb-1">
                                <input 
                                    type="checkbox" 
                                    value="{{ $activity->id }}" 
                                    x-model="form.activity_ids"
                                    class="mr-2"
                                >
                                <span>{{ $activity->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showDayTourModal = false; resetForm()" class="px-4 py-2 rounded border hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Text Content Management</h2>
        <button 
            @click="showTextModal = true" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Text Content
        </button>
    </div>

    {{-- Text Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Package Name</th>
                    <th class="px-4 py-2">Label</th>
                    <th class="px-4 py-2">Text</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($texts as $index => $text)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $text->daytour->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->content ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ Str::limit($text->text, 60) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <form action="{{ route('day-text.destroy', $text->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this text content?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No text content added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Text Content Modal -->
    <div 
        x-show="showTextModal" 
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div 
            @click.away="showTextModal = false" 
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form
                id="textUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="saveText()"
            >
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Package</label>
                    <select 
                        name="package_id" 
                        x-model="form.package_id" 
                        class="border rounded p-2 w-full" 
                        required
                    >
                        <option value="">Select Package</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                        @endforeach
                    </select>
                </div>

            
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Label</label>
                    <input 
                        type="text" 
                        name="label" 
                        x-model="form.label" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Optional"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Text</label>
                    <textarea 
                        name="text" 
                        x-model="form.text" 
                        rows="4" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter text text..."
                        required
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button 
                        type="button" 
                        @click="showTextModal = false" 
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
function dayTourComponent() {
    return {
        showDayTourModal: false,
        showTextModal: false,
        editMode: false,
        texts: @js($texts),
        dayTours: @js($tours),
        activities: @js($activities),
        form: { id: null, name: '', description: '', price: '', activity_ids: [] },
        textForm: { id: null, name: '', label: '', text: '' },

        resetForm() {
            this.form = { id: null, name: '', description: '', price: '', activity_ids: [] };
            this.editMode = false;
        },

        editDayTour(id) {
            const tour = this.dayTours.find(d => d.id === id);
            if (tour) {
                this.form.id = tour.id;
                this.form.name = tour.name;
                this.form.description = tour.description;
                this.form.price = tour.price;
                this.form.activity_ids = tour.activities.map(a => a.id);
                this.editMode = true;
                this.showDayTourModal = true;
            }
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form.id = text.id;
                this.form.label = text.label;
                this.form.text = text.text;
                this.form.tour = text.tour;
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        saveDayTour: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('name', this.form.name);
            formData.append('description', this.form.description);
            formData.append('price', this.form.price);
            this.form.activity_ids.forEach(id => formData.append('activity_ids[]', id));

            let url = '{{ route("day-tours.store") }}';
            if (this.editMode && this.form.id) {
                url = `/admin/day-tours/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#dayTourForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Day Tour updated!' : 'Day Tour added!'));
                    this.resetForm();
                    this.showDayTourModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting Day Tour.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        saveText: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();

            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('package_id', this.form.package_id); // make sure this is populated

            let url = '{{ route('day-text.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/day-text/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Uploading...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Text updated successfully!' : 'Text added successfully!'));
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting text.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },
    }
}
</script>
@endsection

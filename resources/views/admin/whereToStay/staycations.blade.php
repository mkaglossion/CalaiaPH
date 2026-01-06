@extends('layouts.admin')

@section('title', 'Staycations')

@section('content')
<div
    x-data="staycationComponent()"

>
    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Staycations</h2>
        <button
            type="button"
            @click="showStaycationModal = true; resetForm()"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Staycation
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staycations as $index => $act)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $act->name ?? 'Untitled' }}</td>
                    <td class="px-4 py-2">{{ $act->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button type="button" class="text-blue-600 hover:underline" @click="editStaycation({{ $act->id }})">
                            Edit
                        </button>
                        <form action="{{ route('staycation-list.destroy', $act->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="text-red-600 hover:underline"
                                onclick="return confirm('Delete this staycation?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No staycations added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showStaycationModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-cloak>
        <div @click.away="showStaycationModal = false" class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">

            <h3 class="text-lg font-semibold mb-4" x-text="editModeStaycation ? 'Edit Staycation' : 'Add Staycation'"></h3>

            <form id="staycationForm" @submit.prevent="saveStaycation()" class="space-y-5">
                @csrf

                <!-- Staycation Name & Quantity -->
                <div class="flex space-x-2">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Staycation Name</label>
                        <input type="text" x-model="form.name" class="w-full border rounded px-3 py-2" placeholder="Premium Glamping Tent" required>
                    </div>
                    <div class="w-32">
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input type="number" x-model.number="form.quantity" min="1" class="w-full border rounded px-3 py-2" placeholder="Rooms" required>
                    </div>
                </div>

                <!-- Dynamic Price Configs -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium">Price Configurations</label>
                    <template x-for="(config, index) in form.priceConfigs" :key="index">
                        <div class="border p-3 rounded space-y-2">

                            <!-- Config Name and Type -->
                            <div class="flex space-x-2 items-center">
                                <input type="text" x-model="config.name" placeholder="Config Name (Weekend, Holiday, etc.)" class="flex-1 border rounded px-2 py-1" required>

                                <select x-model="config.type" class="border rounded px-2 py-1">
                                    <option value="day">Day Type</option>
                                    <option value="date">Specific Dates</option>
                                </select>

                                <button type="button" @click="removePriceConfig(index)" class="text-red-500 font-bold">&times;</button>
                            </div>

                            <!-- Days checkboxes -->
                            <div x-show="config.type === 'day'" class="flex space-x-2">
                                <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                                    <label class="flex items-center space-x-1">
                                        <input type="checkbox" :value="day" x-model="config.days">
                                        <span x-text="day"></span>
                                    </label>
                                </template>
                            </div>

                            <!-- Dates input -->
                            <div x-show="config.type === 'date'" class="space-y-2">
                                <label class="block text-sm font-medium">Select Date(s)</label>

                                <!-- Single date -->
                                <template x-if="!config.multipleDates">
                                    <input type="date" x-model="config.dates" class="w-full border rounded px-2 py-1">
                                </template>

                                <!-- Multiple dates (comma-separated) -->
                                <template x-if="config.multipleDates">
                                    <input type="text" x-model="config.dates" placeholder="YYYY-MM-DD, YYYY-MM-DD" class="w-full border rounded px-2 py-1">
                                </template>

                                <!-- Toggle for single/multiple dates -->
                                <label class="flex items-center space-x-2 mt-1">
                                    <input type="checkbox" x-model="config.multipleDates">
                                    <span class="text-sm text-gray-600">Allow multiple dates (comma-separated)</span>
                                </label>
                            </div>

                            <!-- Rates per season -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold mb-1">Non-Peak</label>
                                    <input type="number" x-model.number="config.rates.nonPeak" class="border rounded px-2 py-1" min="0">
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold mb-1">Peak</label>
                                    <input type="number" x-model.number="config.rates.peak" class="border rounded px-2 py-1" min="0">
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold mb-1">Super Peak</label>
                                    <input type="number" x-model.number="config.rates.superPeak" class="border rounded px-2 py-1" min="0">
                                </div>
                            </div>

                        </div>
                    </template>

                    <button type="button" @click="addPriceConfig()" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">+ Add Price Config</button>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="showStaycationModal = false; resetForm()" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" x-text="editModeStaycation ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>





    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Swiper Images Management</h2>
        <button 
            @click="showModal = true" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Image
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Preview</th>
                    <th class="px-4 py-2">Staycation Name</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($images as $index => $img)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">
                            <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-20 h-12 object-cover rounded">
                        </td>
                        <td class="px-4 py-2">{{ $img->staycation->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form action="{{ route('staycation-image.destroy', $img->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this image?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No images uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button 
                @click="showModal = false"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4">Upload New Swiper Image</h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const fileInput = $refs.image.files[0];
                    const title = $refs.title.value;
                    const staycationId = $refs.staycation.value;

                    if (!fileInput) {
                        alert('Please select an image before uploading.');
                        return;
                    }

                    if (!staycationId) {
                        alert('Please select an staycation.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', fileInput);
                    formData.append('title', title);
                    formData.append('staycation_id', staycationId);

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = 'Uploading...';
                    btn.disabled = true;

                    fetch('{{ route('staycation-image.store') }}', {
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
                            alert(data.message || 'Image uploaded successfully!');
                            $refs.image.value = '';
                            $refs.title.value = '';
                            $refs.staycation.value = '';
                            showModal = false;
                            window.location.reload();
                        } else if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat().join('\\n');
                            alert(errorMessages);
                        } else {
                            alert(data.message || 'Upload failed.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error uploading image.');
                    })
                    .finally(() => {
                        btn.innerText = 'Upload';
                        btn.disabled = false;
                    });
                "
            >
                @csrf
                <div class="mb-3">
                    <label class="block font-semibold mb-1">Staycation</label>
                    <select x-ref="staycation" name="staycation_id" class="border rounded p-2 w-full" required>
                        <option value="">Select Staycation</option>
                        @foreach($staycations as $staycation)
                            <option value="{{ $staycation->id }}">{{ $staycation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded p-2 w-full">
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Upload Image</label>
                    <input type="file" x-ref="image" name="image" accept="image/*" class="border rounded p-2 w-full" required>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
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
                    <th class="px-4 py-2">Staycation Name</th>
                    <th class="px-4 py-2">Label</th>
                    <th class="px-4 py-2">Text</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($texts as $index => $text)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $text->staycation->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->content ?? 'Untitled' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <form action="{{ route('staycation-text.destroy', $text->id) }}" method="POST" class="inline">
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
                    <label class="block font-semibold mb-1">Staycation</label>
                    <select 
                        name="staycation_id" 
                        x-model="form.staycation_id" 
                        class="border rounded p-2 w-full" 
                        required
                    >
                        <option value="">Select Staycation</option>
                        @foreach($staycations as $staycation)
                            <option value="{{ $staycation->id }}">{{ $staycation->name }}</option>
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
function staycationComponent() {
    return {
        showModal: false,
        showStaycationModal: false,
        editModeStaycation: false,
        showTextModal: false,
        editMode: false,
        staycations: @js($staycations),
        form: {
            id: null,
            name: '',
            quantity: 1,
            priceConfigs: [],
            staycation_id: '',
            label: '',
            text: ''
        },

        resetForm() {
            this.form = { id: null, name: '', quantity: 1, priceConfigs: [] };
            this.editModeStaycation = false;
        },

        editStaycation(id) {
            this.resetForm();
            const staycation = this.staycations.find(s => s.id === id);
            if (staycation) {
                this.form.id = staycation.id;
                this.form.name = staycation.name;
                this.form.quantity = staycation.quantity;
                this.form.priceConfigs = (staycation.price_configs || []).map(pc => {
                    const datesStr = pc.dates || '';
                    const isMultiple = datesStr.includes(',');
                    return {
                        ...pc,
                        multipleDates: isMultiple,
                        dates: datesStr
                    };
                });
                this.editModeStaycation = true;
                this.showStaycationModal = true;
            }
        },

        addPriceConfig() {
            this.form.priceConfigs.push({
                name: '',
                type: 'day',
                days: [],
                dates: '',
                multipleDates: false,
                rates: { nonPeak: 0, peak: 0, superPeak: 0 }
            });
        },

        removePriceConfig(index) {
            this.form.priceConfigs.splice(index, 1);
        },

        saveStaycation: async function() {
            // Validate form data
            if (!this.form.name.trim()) {
                alert('Please enter a staycation name.');
                return;
            }
            if (!this.form.quantity || this.form.quantity < 1) {
                alert('Please enter a valid quantity.');
                return;
            }
            if (!this.form.priceConfigs || this.form.priceConfigs.length === 0) {
                alert('Please add at least one price configuration.');
                return;
            }

            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('name', this.form.name);
            formData.append('quantity', this.form.quantity);
            formData.append('priceConfigs', JSON.stringify(this.form.priceConfigs));

            let url = '{{ route("staycation-list.store") }}';
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
                    alert(data.message || (this.editModeStaycation ? 'Staycation updated!' : 'Staycation added!'));
                    this.resetForm();
                    this.showStaycationModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error('Error submitting staycation:', err);
                alert('Error submitting staycation: ' + (err.message || 'Unknown error'));
            } finally {
                btn.innerText = this.editModeStaycation ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        saveText: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('staycation_id', this.form.staycation_id);
            formData.append('title', this.form.label);
            formData.append('content', this.form.text);

            let url = '{{ route("staycation-text.store") }}';
            let method = 'POST';
            if (this.editMode && this.form.id) {
                url = `{{ url('/admin/staycation-text') }}/${this.form.id}`;
                method = 'PUT';
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
                    alert(data.message || (this.editMode ? 'Text updated!' : 'Text added!'));
                    this.form.staycation_id = '';
                    this.form.label = '';
                    this.form.text = '';
                    this.editMode = false;
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
        }
    };
}

</script>


@endsection

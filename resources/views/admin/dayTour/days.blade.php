@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')

<div
    x-data="dayTourManager()"
    class="p-8 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Day Tour Page Management
    </h1>
    <p class="text-gray-600 mb-10">Manage your Day Tour packages, activities, and text content.</p>

    {{-- ============================================================= --}}
    {{-- DAY TOUR PACKAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Day Tour Packages</h2>
            <p class="text-gray-500 text-sm">Manage packages and included activities.</p>
        </div>
        <button 
            @click="showDayTourModal = true; resetForm()" 
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Package
        </button>
    </div>

    {{-- Packages Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($tours as $tour)
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition">
                <h3 class="font-semibold text-lg text-gray-900">{{ $tour->name }}</h3>
                <p class="text-gray-500 text-sm mt-1">{{ Str::limit($tour->description, 100) ?: '-' }}</p>
                <p class="text-gray-600 mt-2 font-medium">{{ $tour->price ? '₱'.number_format($tour->price, 2) : '-' }}</p>

                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($tour->activities as $activity)
                        <span class="inline-block bg-gray-200 px-2 py-1 rounded text-xs">{{ $activity->name }}</span>
                    @endforeach
                </div>

                <p class="text-gray-400 text-xs mt-2">Created: {{ $tour->created_at->format('Y-m-d') }}</p>

                <div class="mt-4 flex justify-end gap-3">
                    <button 
                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                        @click="editDayTour({{ $tour->id }})">
                        Edit
                    </button>

                    <form action="{{ route('day-tours.destroy', $tour->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button 
                            onclick="return confirm('Delete this package?')"
                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-full">No packages added yet.</p>
        @endforelse
    </div>

    <hr class="my-14 border-gray-300">

    {{-- ============================================================= --}}
    {{-- TEXT CONTENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Text Content</h2>
            <p class="text-gray-500 text-sm">Paragraphs and captions displayed on the Day Tour page.</p>
        </div>

        <button 
            @click="showTextModal = true" 
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Text
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
    <table class="w-full text-left">
        <thead class="bg-gray-100/70 backdrop-blur">
            <tr>
                <th class="px-6 py-4 text-sm font-semibold text-gray-700">#</th>
                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Package</th>
                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Label</th>
                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Text</th>
                <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
            @forelse($texts as $index => $text)
                <tr class="hover:bg-gray-50/70 transition">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $text->daytour->name ?? 'Untitled' }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $text->title ?? 'Untitled' }}</td>
                    <td class="px-6 py-4 text-gray-600 text-sm">{{ $text->content ?? 'Untitled' }}</td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <button 
                            class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                            @click="editText({{ $text->id }})">
                            Edit
                        </button>

                        <form id="delete-text-{{ $text->id }}" 
                              action="{{ route('day-text.destroy', $text->id) }}" 
                              method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="button"
                                @click="deleteText({{ $text->id }})"
                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                        No text content added yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


    {{-- ============================================================= --}}
    {{-- MODALS --}}
    {{-- ============================================================= --}}

    {{-- Add/Edit Day Tour Modal --}}
    <div 
        x-show="showDayTourModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div @click.away="showDayTourModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Day Tour Package' : 'Add Day Tour Package'"></h3>

            <form id="dayTourForm" @submit.prevent="saveDayTour" class="space-y-5">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Package Name</label>
                    <input type="text" x-model="form.name" class="w-full border rounded-xl px-3 py-2" placeholder="Package Name" required>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Description</label>
                    <textarea x-model="form.description" rows="3" class="w-full border rounded-xl px-3 py-2" placeholder="Description"></textarea>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Price</label>
                    <input type="number" x-model="form.price" step="0.01" class="w-full border rounded-xl px-3 py-2" placeholder="Price">
                </div>

                <div>
                    <label class="block font-medium mb-2 text-gray-700">Included Activities</label>
                    <div class="max-h-64 overflow-y-auto border rounded-xl p-2">
                        @foreach($activities as $activity)
                            <div class="flex items-center mb-1">
                                <input type="checkbox" value="{{ $activity->id }}" x-model="form.activity_ids" class="mr-2">
                                <span>{{ $activity->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showDayTourModal = false; resetForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add/Edit Text Modal --}}
    <div x-show="showTextModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showTextModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-7 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

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

                <!-- Package Selection -->
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Package</label>
                    <select 
                        name="package_id" 
                        x-model="form.package_id" 
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                    >
                        <option value="">Select Package</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Label -->
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Label</label>
                    <input 
                        type="text" 
                        name="label" 
                        x-model="form.label" 
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Optional"
                    >
                </div>

                <!-- Text -->
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea 
                        name="text" 
                        x-model="form.text" 
                        rows="4" 
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter text..."
                        required
                    ></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-2">
                    <button 
                        type="button" 
                        @click="showTextModal = false" 
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition"
                    >
                        Cancel
                    </button>

                    <button 
                        type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                    >
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- GLOBAL LOADING OVERLAY -->
    <div 
        x-show="isLoading"
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999]"
    >
        <div class="flex flex-col items-center space-y-3">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-30" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                <path class="opacity-90" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white text-lg font-medium">Processing...</span>
        </div>
    </div>

</div>

<script>
function dayTourManager() {
    return {
        isLoading: false,
        showModal: false,
        showDayTourModal: false,
        showTextModal: false,
        editMode: false,
        texts: @js($texts),
        dayTours: @js($tours),
        activities: @js($activities),
        form: { id: null, name: '', description: '', price: '', activity_ids: [], package_id: '', label: '', text: '' },

        resetForm() {
            this.form = { id: null, name: '', description: '', price: '', activity_ids: [], package_id: '', label: '', text: '' };
            this.editMode = false;
        },

        // Edit Day Tour Package
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

        // Edit Text Content
        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form = { 
                    id: text.id, 
                    package_id: text.package_id, 
                    label: text.label, 
                    text: text.text 
                };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        // Save Day Tour Package
        async saveDayTour() {
            this.isLoading = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;
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

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: this.editMode ? 'Updated!' : 'Added!',
                        text: data.message || 'Day Tour saved successfully!',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        this.resetForm();
                        this.showDayTourModal = false;
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        html: Object.values(data.errors).flat().join('<br>')
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Operation failed.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: 'Something went wrong.'
                });
            } finally {
                this.isLoading = false;
            }
        },

        // Save Text Content
        async saveText() {
            this.isLoading = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            formData.append('package_id', this.form.package_id);
            formData.append('title', this.form.label);
            formData.append('content', this.form.text);

            let url = '{{ route('day-text.store') }}';
            if (this.editMode && this.form.id) {
                url = `/admin/day-text/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: this.editMode ? 'Updated!' : 'Added!',
                        text: data.message || 'Text content saved!',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        this.resetForm();
                        this.showTextModal = false;
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        html: Object.values(data.errors).flat().join('<br>')
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Operation failed.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: 'Something went wrong.'
                });
            } finally {
                this.isLoading = false;
            }
        },

        // Upload Swiper Image
        async uploadImage() {
            this.isLoading = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            formData.append('title', this.$refs.title.value);
            formData.append('image', this.$refs.image.files[0]);

            try {
                const res = await fetch('{{ route('day-tour-swiper-images.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Upload Complete',
                        text: data.message || 'Image uploaded successfully!',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        this.showModal = false;
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        html: Object.values(data.errors).flat().join('<br>')
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: 'Something went wrong. Please try again.'
                });
            } finally {
                this.isLoading = false;
            }
        },

        // Delete Day Tour
        deleteDayTour(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete Day Tour?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-daytour-${id}`).submit();
                }
            });
        },

        // Delete Text Content
        deleteText(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete Text Content?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-text-${id}`).submit();
                }
            });
        },

        // Delete Swiper Image
        deleteImage(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete Image?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-image-${id}`).submit();
                }
            });
        }
    };
}
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
</style>

@endsection

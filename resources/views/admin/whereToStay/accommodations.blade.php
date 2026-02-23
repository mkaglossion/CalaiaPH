ll n@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')

<div
    x-data="dayTourManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Accommodation Page Management
    </h1>
    <p class="text-gray-600 mb-10">Manage your Accommodation  packages, staycations, and text content.</p>

    {{-- ============================================================= --}}
    {{-- DAY TOUR PACKAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Accommodation  Packages</h2>
            <p class="text-gray-500 text-sm">Manage packages and included staycations.</p>
        </div>
        <button 
            @click="showAccommodationModal = true; resetForm()" 
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Package
        </button>
    </div>

    {{-- Packages Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($accommodations as $accommodation)
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition">
                
                {{-- Image --}}
                @if($accommodation->image_path)
                    <img 
                        src="{{ $accommodation->image_path }}" 
                        alt="{{ $accommodation->name }}" 
                        class="w-full h-48 object-cover rounded-xl mb-4"
                    >
                @else
                    <div class="w-full h-48 bg-gray-100 rounded-xl mb-4 flex items-center justify-center text-gray-400">
                        No Image
                    </div>
                @endif

                <h3 class="font-semibold text-lg text-gray-900">{{ $accommodation->name }}</h3>
                <p class="text-gray-500 text-sm mt-1">{{ Str::limit($accommodation->description, 100) ?: '-' }}</p>
                <!-- <p class="text-gray-600 mt-2 font-medium">{{ $accommodation->price ? '₱'.number_format($accommodation->price, 2) : '-' }}</p> -->

                <div class="mt-3 flex flex-wrap gap-1">
                    @forelse($accommodation->variants as $activity)
                        <span class="inline-block bg-gray-200 px-2 py-1 rounded text-xs">
                            {{ $activity->name }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400">No activities</span>
                    @endforelse
                </div>

                <p class="text-gray-400 text-xs mt-2">Created: {{ $accommodation->created_at->format('Y-m-d') }}</p>

                <div class="mt-4 flex justify-end gap-3">
                    <button 
                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                        @click="editAccommodation({{ $accommodation->id }})">
                        Edit
                    </button>

                    <form action="{{ route('staycation-accommodations.destroy', $accommodation->id) }}" method="POST">
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
                              action="{{ route('admin.staycation-text.destroy', $text->id) }}"
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
        x-show="showAccommodationModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div @click.away="showAccommodationModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Type of Accommodation' : 'Add Type of Accommodation'"></h3>

            <form id="dayTourForm" @submit.prevent="saveAccommodation" class="space-y-5" enctype="multipart/form-data">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Package Name -->
                <div>
                    <label for="package_name" class="block font-medium mb-1 text-gray-700">Package Name</label>
                    <input type="text" id="package_name" name="package_name" x-model="form.name" class="w-full border rounded-xl px-3 py-2" placeholder="Package Name" required>
                </div>

                <!-- Image -->
                <div>
                    <label for="package_image" class="block font-medium mb-1 text-gray-700">Image</label>
                    <input 
                        type="file" 
                        id="package_image"
                        name="package_image"
                        @change="form.image = $event.target.files[0]" 
                        class="w-full border rounded-xl px-3 py-2" 
                        accept="image/*"
                    >
                    
                    <!-- Preview: new image selected -->
                    <template x-if="form.image">
                        <img :src="URL.createObjectURL(form.image)" class="mt-2 h-32 w-32 object-cover rounded-xl" alt="Preview">
                    </template>

                    <!-- Preview: existing image if editing and no new image selected -->
                    <template x-if="editMode && !form.image && form.current_image">
                        <img :src="form.current_image" class="mt-2 h-32 w-32 object-cover rounded-xl" alt="Current Image">
                    </template>
                </div>

                <!-- Description -->
                <div>
                    <label for="package_description" class="block font-medium mb-1 text-gray-700">Description</label>
                    <textarea id="package_description" name="package_description" x-model="form.description" rows="3" class="w-full border rounded-xl px-3 py-2" placeholder="Description"></textarea>
                </div>

                <!-- Price -->
                <!-- <div>
                    <label class="block font-medium mb-1 text-gray-700">Price</label>
                    <input type="number" x-model="form.price" step="0.01" class="w-full border rounded-xl px-3 py-2" placeholder="Price">
                </div> -->

                <!-- Included Activities -->
                <div>
                    <label id="included_activities_label" class="block font-medium mb-2 text-gray-700">Included Activities</label>
                    <div class="max-h-64 overflow-y-auto border rounded-xl p-2" role="group" aria-labelledby="included_activities_label">
                        @foreach($staycations as $activity)
                            <div class="flex items-center mb-1">
                                <input type="checkbox" value="{{ $activity->id }}" x-model="form.staycation_ids" class="mr-2">
                                <span>{{ $activity->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showAccommodationModal = false; resetForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
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
                    <label for="accommodation_id" class="block font-medium mb-1 text-gray-700">Package</label>
                    <select 
                        id="accommodation_id"
                        name="accommodation_id" 
                        x-model="form.accommodation_id" 
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                    >
                        <option value="">Select Package</option>
                        @foreach($accommodations as $accommodation)
                            <option value="{{ $accommodation->id }}">{{ $accommodation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Label -->
                <div>
                    <label for="text_label" class="block font-medium mb-1 text-gray-700">Label</label>
                    <input 
                        type="text" 
                        id="text_label"
                        name="label" 
                        x-model="form.label" 
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Optional"
                    >
                </div>

                <!-- Text -->
                <div>
                    <label for="text_content" class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea 
                        id="text_content"
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

</div>

<script>
function dayTourManager() {
    return {
        isLoading: false,
        showModal: false,
        showAccommodationModal: false,
        showTextModal: false,
        editMode: false,
        texts: @js($texts),
        accommodations: @js($accommodations),
        staycations: @js($staycations),
        form: { id: null, name: '', description: '', price: '', staycation_ids: [], accommodation_id: '', label: '', text: '', image: null, current_image: null },

        resetForm() {
            this.form = { id: null, name: '', description: '', price: '', staycation_ids: [], accommodation_id: '', label: '', text: '', image: null, current_image: null  };
            this.editMode = false;
        },



        // Edit Day Tour Package

        editAccommodation(id) {
            const accommodation = this.accommodations.find(d => d.id === id);
            if (accommodation) {
                this.form.id = accommodation.id;
                this.form.name = accommodation.name;
                this.form.description = accommodation.description;
                this.form.price = accommodation.price;
                // Use the variants relation instead of accommodation_variants
                this.form.staycation_ids = (accommodation.variants || []).map(a => a.id);
                this.form.current_image = accommodation.image_path;
                this.form.image = null;
                this.editMode = true;
                this.showAccommodationModal = true;
            }
        },

        // Edit Text Content
        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form.id = text.id;
                this.form.accommodation_id = text.accommodation_id;
                this.form.label = text.title;
                this.form.text = text.content;

                this.editMode = true;
                this.showTextModal = true;
            }
        },

        // Save Day Tour Package
        async saveAccommodation() {
            this.isLoading = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;

            try {
                const formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('description', this.form.description);
                formData.append('price', 0);
                this.form.staycation_ids.forEach(id => formData.append('staycation_ids[]', id));

                // Only append image if user selected a new file
                if (this.form.image) {
                    formData.append('image', this.form.image);
                }

                let url = '{{ route("staycation-accommodations.store") }}';
                if (this.editMode && this.form.id) {
                    url = `/admin/staycation-accommodations/${this.form.id}`;
                    formData.append('_method', 'PUT');
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
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
                console.error(err);
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
            formData.append('accommodation_id', this.form.accommodation_id);
            formData.append('title', this.form.label);
            formData.append('content', this.form.text);

            let url = '{{ route('accommodations-text.store') }}';
            if (this.editMode && this.form.id) {
                url = `/admin/accommodations-text/${this.form.id}`;
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

    console.log(this.accommodations);

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

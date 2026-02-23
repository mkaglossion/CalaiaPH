@extends('layouts.admin')

@section('title', 'Event Experience Cards Management')

@section('content')

<div
    x-data="eventSectionsManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Event Experience Cards Management
    </h1>
    <p class="text-gray-600 mb-10">Manage the sections displayed in the Event Experience component.</p>

    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Event Sections</h2>
            <p class="text-gray-500 text-sm">Sections that appear in the event experience carousel.</p>
        </div>

        <button
            @click="showModal = true; resetForm()"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Section
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($sections as $section)
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-6 rounded-2xl shadow-md hover:shadow-xl transition">
                <div class="mb-4">
                    <img src="{{ asset($section->image_path) }}" class="w-full h-48 object-cover rounded-xl" alt="{{ $section->title }}">
                </div>

                <div class="mt-4">
                    <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $section->title }}</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center text-gray-600">
                            <i data-lucide="link" class="w-4 h-4 mr-2 text-blue-500"></i>
                            <span class="truncate">{{ $section->link_path }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i data-lucide="list-ordered" class="w-4 h-4 mr-2 text-green-500"></i>
                            <span>Order: {{ $section->order }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2 text-purple-500"></i>
                            <span>{{ Str::limit($section->description1, 50) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        @click="editSection({{ $section->id }})"
                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition flex items-center">
                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i>
                        Edit
                    </button>

                    <button
                        @click="confirmDelete({{ $section->id }})"
                        class="text-red-600 font-semibold hover:text-red-700 hover:underline transition flex items-center">
                        <i data-lucide="trash" class="w-4 h-4 mr-1"></i>
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="mb-4">
                    <i data-lucide="database" class="w-12 h-12 text-gray-400 mx-auto"></i>
                </div>
                <p class="text-gray-500">No event sections created yet.</p>
                <p class="text-sm text-gray-400 mt-2">Click "Add Section" to create your first event section.</p>
            </div>
        @endforelse
    </div>

    <!-- Section Modal -->
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 relative border border-gray-200">
            <button
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Event Section' : 'Add New Event Section'"></h3>

            <form id="sectionForm" class="space-y-5" @submit.prevent="saveSection">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="section_id" class="block font-medium mb-1 text-gray-700">Section ID</label>
                        <input type="text" id="section_id" name="section_id" x-model="form.section_id" class="border rounded-xl p-3 w-full" required>
                    </div>

                    <div>
                        <label for="title" class="block font-medium mb-1 text-gray-700">Title</label>
                        <input type="text" id="title" name="title" x-model="form.title" class="border rounded-xl p-3 w-full" required>
                    </div>
                </div>

                <div>
                    <label for="image_file" class="block font-medium mb-1 text-gray-700">Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 md:p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                         @click="$refs.imageInput.click()">
                        <div x-show="form.image_preview" class="mb-4 flex flex-col items-center gap-2">
                            <img :src="getImagePreviewUrl()" :key="form.image_preview" alt="Image Preview" class="max-h-32 md:max-h-40 lg:max-h-48 rounded-lg shadow">
                            <template x-if="editMode && form.image_path && !form.remove_image">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="form.remove_image" class="rounded">
                                    <span>Remove current image</span>
                                </label>
                            </template>
                        </div>

                        <template x-if="!form.image_path">
                            <div class="flex flex-col items-center gap-2 text-center">
                                <i data-lucide="cloud-upload" class="w-8 h-8 text-gray-400"></i>
                                <p class="text-gray-600 font-medium">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </template>

                        <input type="file" id="image_file" name="image_file" accept="image/*" class="hidden" x-ref="imageInput" @change="handleFileUpload">
                    </div>
                </div>

                <div>
                    <label for="description1" class="block font-medium mb-1 text-gray-700">Description 1</label>
                    <textarea id="description1" name="description1" x-model="form.description1" rows="3" class="w-full border rounded-xl px-3 py-2" required></textarea>
                </div>

                <div>
                    <label for="description2" class="block font-medium mb-1 text-gray-700">Description 2</label>
                    <textarea id="description2" name="description2" x-model="form.description2" rows="3" class="w-full border rounded-xl px-3 py-2" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="link_path" class="block font-medium mb-1 text-gray-700">Link Path</label>
                        <input type="text" id="link_path" name="link_path" x-model="form.link_path" class="border rounded-xl p-3 w-full" required>
                    </div>

                    <div>
                        <label for="order" class="block font-medium mb-1 text-gray-700">Order</label>
                        <input type="number" id="order" name="order" x-model="form.order" class="border rounded-xl p-3 w-full" required>
                    </div>

                    <div>
                        <label for="theme" class="block font-medium mb-1 text-gray-700">Theme</label>
                        <select id="theme" x-model="form.theme" :value="form.theme" name="theme" class="border rounded-xl p-3 w-full" required>
                            <option value="white" :selected="form.theme === 'white'">White</option>
                            <option value="tan" :selected="form.theme === 'tan'">Tan</option>
                            <option value="beige_swatch" :selected="form.theme === 'beige_swatch'">Beige Swatch</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
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
function eventSectionsManager() {
    return {
        isLoading: false,
        showModal: false,
        editMode: false,
        sections: @js($sections),
        form: {
            id: null,
            section_id: '',
            title: '',
            image_path: '',
            image_preview: null,
            description1: '',
            description2: '',
            link_path: '',
            theme: 'white',
            order: 1,
            remove_image: false
        },
        file: null,

        resetForm() {
            this.form = {
                id: null,
                section_id: '',
                title: '',
                image_path: '',
                image_preview: null,
                description1: '',
                description2: '',
                link_path: '',
                theme: 'white',
                order: 1,
                remove_image: false
            };
            this.file = null;
            this.editMode = false;
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.file = file;
                this.form.image_preview = URL.createObjectURL(file);
                this.form.remove_image = false;
            }
        },

        getImagePreviewUrl() {
            if (this.form.image_preview && this.form.image_preview.startsWith('blob:')) {
                return this.form.image_preview;
            }
            if (this.form.image_path) {
                // If it's already a full URL (Cloudinary), return as-is
                if (this.form.image_path.startsWith('http')) {
                    return this.form.image_path;
                }
                // Otherwise, construct full URL for local paths
                return window.location.origin + this.form.image_path;
            }
            return this.form.image_preview;
        },

        editSection(id) {
            const section = this.sections.find(s => s.id === id);
            if (section) {
                this.form = {
                    id: section.id,
                    section_id: section.section_id,
                    title: section.title,
                    image_path: section.image_path,
                    image_preview: section.image_path,
                    description1: section.description1,
                    description2: section.description2,
                    link_path: section.link_path,
                    theme: section.theme,
                    order: section.order,
                    remove_image: false
                };
                this.editMode = true;
                this.showModal = true;
            }
        },

        confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete this event section!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteSection(id);
                }
            });
        },

        async saveSection() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            // Add all form fields
            formData.append('section_id', this.form.section_id);
            formData.append('title', this.form.title);
            formData.append('image_path', this.form.image_path || '');
            formData.append('description1', this.form.description1);
            formData.append('description2', this.form.description2);
            formData.append('link_path', this.form.link_path);
            formData.append('theme', this.form.theme || 'white');
            formData.append('order', this.form.order);
            if (this.form.remove_image) formData.append('remove_image', 1);

            // Add file if present
            if (this.file) {
                formData.append('image_file', this.file);
            }

            let url = '{{ route('event-experience-sections.store') }}';
            let method = 'POST';

            if (this.editMode && this.form.id) {
                url = `/admin/event-experience-sections/${this.form.id}`;
                method = 'POST';
                formData.append('_method', 'PUT');
            }

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: "success",
                        title: this.editMode ? "Updated!" : "Added!",
                        text: data.message || "Section saved successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.resetForm();
                        this.showModal = false;
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: "warning",
                        title: "Validation Error",
                        html: Object.values(data.errors).flat().join("<br>")
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Something went wrong."
                });
            } finally {
                this.isLoading = false;
            }
        },

        async deleteSection(id) {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;

            try {
                const res = await fetch(`/admin/event-experience-sections/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "Section has been deleted.",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Failed to delete section."
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Something went wrong."
                });
            } finally {
                this.isLoading = false;
            }
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

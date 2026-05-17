@extends('layouts.admin')

@section('title', 'What to Do Page CMS')

@section('content')

<div
    x-data="whatToDoManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Page Information
    </h1>
    <p class="text-gray-600 mb-10">Manage the image slider and content displayed on your What to Do page.</p>

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Swiper Images</h2>
            <p class="text-gray-500 text-sm">Photos that appear inside the homepage carousel.</p>
        </div>

        <button 
            @click="showModal = true" 
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Image
        </button>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        @forelse($images as $img)
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative">
                <div class="flex items-center mb-2">
                    <input type="checkbox" x-model="selectedImages" value="{{ $img->id }}" class="mr-2">
                    <span class="text-xs text-gray-500">Select</span>
                </div>
                <div class="overflow-hidden rounded-xl">
                    @if(str_contains($img->image_path, '/video/'))
                        <video src="{{ $img->image_path }}" class="w-full h-48 object-cover transition hover:scale-105" muted></video>
                    @else
                        <img src="{{ $img->image_path }}" class="w-full h-48 object-cover transition hover:scale-105">
                    @endif
                </div>

                <div class="mt-4">
                    <h3 class="font-semibold text-lg text-gray-900">{{ $img->title ?: 'Untitled' }}</h3>
                    <p class="text-gray-500 text-sm">
                        Uploaded: {{ $img->created_at->format('Y-m-d') }}
                    </p>
                </div>

                <div class="mt-5 flex justify-end space-x-3">
                    <button class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                        @click="editImage({{ $img->id }}, '{{ $img->title }}')">Edit</button>
                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                        @click="deleteImage({{ $img->id }}, '{{ route('do-swiper-images.destroy', $img->id) }}')">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No images uploaded yet.</p>
        @endforelse
    </div>

    {{-- Bulk Actions --}}
    <div x-show="selectedImages.length > 0" class="mt-6 flex items-center space-x-4">
        <span class="text-sm text-gray-600">Selected: <span x-text="selectedImages.length"></span></span>
        <button @click="selectAll" class="px-3 py-1 bg-gray-500 text-white rounded text-sm hover:bg-gray-600">
            Select All
        </button>
        <button @click="deselectAll" class="px-3 py-1 bg-gray-400 text-white rounded text-sm hover:bg-gray-500">
            Deselect All
        </button>
        <button @click="bulkDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
            Delete Selected
        </button>
    </div>

    <hr class="my-14 border-gray-300">


    {{-- ============================================================= --}}
    {{-- TEXT CONTENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Text Content</h2>
            <p class="text-gray-500 text-sm">Paragraphs and captions displayed on the What to Do page.</p>
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
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Label</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Text</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($texts as $text)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $text->title ?: 'No Label' }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ Str::limit($text->content, 150) }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-4">
                            <button 
                                class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                                @click="editText({{ $text->id }})">Edit</button>
                            <button 
                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                @click="deleteText({{ $text->id }}, '{{ route('do-texts.destroy', $text->id) }}')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-gray-500">
                            No text content yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="main-packages-preview" class="mb-10 rounded-2xl border border-gray-200 bg-white/80 backdrop-blur p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Main Packages Preview</h2>
                <p class="text-sm text-gray-600">
                    Showing {{ ($mainPackages ?? collect())->count() }} of {{ $mainPackagesTotal ?? 0 }} package{{ ($mainPackagesTotal ?? 0) !== 1 ? 's' : '' }}.
                </p>
            </div>
        </div>

        @if(($mainPackages ?? collect())->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                @foreach($mainPackages as $pkg)
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                        @if($pkg->image)
                            <img src="{{ $pkg->image }}" alt="{{ $pkg->name }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400">
                                <i class="bi bi-image text-3xl"></i>
                            </div>
                        @endif

                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h3 class="text-base font-bold text-gray-900 leading-tight">{{ $pkg->name }}</h3>
                                <span class="shrink-0 inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $pkg->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($pkg->description, 95) }}</p>

                            <div class="space-y-1 text-sm mb-4">
                                <p class="text-gray-800 font-semibold">₱{{ number_format($pkg->price, 0) }} <span class="text-gray-500 font-normal">{{ $pkg->pricing_type === 'per_pax' ? '/person' : '/group' }}</span></p>
                                <p class="text-gray-600">Max guests: {{ $pkg->max_guests }}</p>
                            </div>

                            <div class="mt-auto flex items-center justify-between pt-3 border-t border-gray-100">
                                <span class="text-xs text-gray-500">{{ $pkg->created_at?->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10">
                <p class="text-gray-500 mb-3">No packages available yet.</p>
            </div>
        @endif
    </div>

    {{-- ============================================================= --}}
    {{-- MODALS --}}
    {{-- ============================================================= --}}

    {{-- Swiper Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-gray-200">
            <button 
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload Swiper Image'"></h3>

            <form id="swiperUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf
                <input type="hidden" name="_method" value="POST" x-bind:value="editImageMode ? 'PUT' : 'POST'">

                <div>
                    <label for="swiper_title" class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" id="swiper_title" name="title" x-ref="title" class="border rounded-xl p-3 w-full">
                </div>

                <div>
                    <label for="swiper_image" class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input type="file" id="swiper_image" name="image" x-ref="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showModal = false; resetImageForm()"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                        x-text="editImageMode ? 'Update' : 'Upload'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TEXT Modal --}}
    <div x-show="showTextModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showTextModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-7 border border-gray-200">
            
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form id="textUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="saveText()">
                @csrf

                <div>
                    <label for="text_label" class="block font-medium mb-1 text-gray-700">Label</label>
                    <input type="text" id="text_label" name="title" x-model="form.title" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label for="text_content" class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea id="text_content" name="content" x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter text..." required></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showTextModal = false" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
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
function whatToDoManager() {
    return {
        isLoading: false,
        showModal: false,
        showTextModal: false,
        editMode: false,
        editImageMode: false,
        currentImageId: null,
        currentImageTitle: '',
        selectedImages: [],
        texts: @js($texts),
        form: { id: null, title: '', content: '' },

        selectAll() {
            this.selectedImages = @js($images->pluck('id')->toArray());
        },

        deselectAll() {
            this.selectedImages = [];
        },

        resetForm() {
            this.form = { id: null, title: '', content: '' };
            this.editMode = false;
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            this.currentImageTitle = '';
            if (this.$refs.title) this.$refs.title.value = '';
            if (this.$refs.image) this.$refs.image.value = '';
        },

        editImage(id, title) {
            this.currentImageId = id;
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
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
                title: 'Delete Text?',
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
                            text: 'Text deleted successfully.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            window.location.reload();
                        });
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete text.'
                        });
                    });
                }
            });
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form = { id: text.id, title: text.title, content: text.content };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        async bulkDelete() {
            Swal.fire({
                title: `Delete ${this.selectedImages.length} selected image${this.selectedImages.length !== 1 ? 's' : ''}?`,
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                this.isLoading = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;

            try {
                const promises = this.selectedImages.map(id =>
                    fetch(`/admin/do-swiper-images/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: '_method=DELETE'
                    })
                );

                await Promise.all(promises);
                this.selectedImages = [];
                Swal.fire({
                    icon: "success",
                    title: "Delete Complete",
                    text: "Images deleted successfully!",
                    confirmButtonColor: "#2563eb"
                }).then(() => {
                    window.location.reload();
                });
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error deleting images"
                });
            } finally {
                this.isLoading = false;
            }
            });
        },

        async saveText() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            formData.append('title', this.form.title);
            formData.append('content', this.form.content);

            let url = '{{ route('do-texts.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/do-texts/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            try {
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
                        icon: "success",
                        title: this.editMode ? "Updated!" : "Added!",
                        text: data.message || "Saved successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.resetForm();
                        this.showTextModal = false;
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

        async uploadImage() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            const titleInput = this.$refs.title;
            const imageInput = this.$refs.image;

            if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
                if (!this.editImageMode) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Image Selected',
                        text: 'Please select an image before uploading.'
                    });
                    this.isLoading = false;
                    return;
                }
            }

            formData.append('title', titleInput?.value || '');
            if (imageInput && imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }
            if (this.editImageMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editImageMode ? '/admin/do-swiper-images/' + this.currentImageId : '{{ route('do-swiper-images.store') }}';

            try {
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
                        icon: "success",
                        title: this.editImageMode ? "Update Complete" : "Upload Complete",
                        text: data.message || (this.editImageMode ? "Image updated successfully!" : "Image uploaded successfully!"),
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.showModal = false;
                        this.resetImageForm();
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        html: Object.values(data.errors).flat().join("<br>")
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: data.message || "Failed to upload image. Please try again."
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Something went wrong. Please try again."
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

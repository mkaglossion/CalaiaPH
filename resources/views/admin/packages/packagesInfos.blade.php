@extends('layouts.admin')

@section('title', 'Packages Page CMS')

@section('content')

<div
    x-data="packageManager()"
    x-init="initialize()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Packages Page Information
    </h1>
    <p class="text-gray-600 mb-10">Manage the hero slider and content displayed on your Packages page.</p>

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="mb-10 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 md:p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Swiper Images</h2>
                <p class="text-gray-500 text-sm">Photos that appear inside the homepage carousel.</p>
            </div>

            <x-admin.action-button variant="primary" class="px-4 py-2 text-sm font-semibold" @click="showModal = true; resetImageForm()">
                + Add Image
            </x-admin.action-button>
        </div>

        {{-- Cards Grid --}}
        <div x-ref="imageGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <template x-for="(image, index) in images" :key="image.id">
            <div
                data-cms-image-card
                :data-cms-image-id="image.id"
                class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative"
                :class="canReorder ? 'cursor-grab active:cursor-grabbing' : ''"
            >
                <div class="absolute right-4 top-4 z-10 flex items-center gap-2">
                    <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-gray-500 shadow-sm"
                        x-text="`#${Number(image.sort_order || (index + 1))}`"></span>
                    <button
                        x-show="canReorder"
                        type="button"
                        data-drag-handle
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white/95 text-gray-500 shadow-sm transition hover:border-gray-300 hover:text-gray-700"
                        aria-label="Drag to reorder image"
                        title="Drag to reorder image"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <circle cx="6" cy="5" r="1.4" />
                            <circle cx="6" cy="10" r="1.4" />
                            <circle cx="6" cy="15" r="1.4" />
                            <circle cx="14" cy="5" r="1.4" />
                            <circle cx="14" cy="10" r="1.4" />
                            <circle cx="14" cy="15" r="1.4" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center mb-2">
                    <input type="checkbox" x-model="selectedImages" :value="Number(image.id)" class="mr-2">
                    <span class="text-xs text-gray-500">Select</span>
                </div>
                <div class="overflow-hidden rounded-xl">
                    <template x-if="isVideoPath(image.image_path)">
                        <img :src="resolveImageSrc(videoThumbnailSrc(image.image_path))" class="w-full h-48 object-cover transition hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                    </template>
                    <template x-if="!isVideoPath(image.image_path)">
                        <img :src="resolveImageSrc(image.image_path)" class="w-full h-48 object-cover transition hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                    </template>
                </div>

                <div class="mt-4">
                    <h3 class="font-semibold text-lg text-gray-900" x-text="image.title ? image.title : 'Untitled'"></h3>
                    <p class="text-gray-500 text-sm">
                        Uploaded: <span x-text="formatDate(image.created_at)"></span>
                    </p>
                </div>

                <div class="mt-5 flex justify-end space-x-3">
                    <x-admin.action-button variant="link" class="px-0" @click="editImage(image.id, image.title || '')">
                        Edit
                    </x-admin.action-button>
                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                        @click="deleteImage(image.id)">
                        Delete
                    </button>
                </div>
            </div>
        </template>
        <template x-if="images.length === 0">
            <p class="text-gray-500 col-span-full">No images uploaded yet.</p>
        </template>
        </div>

        {{-- Bulk Actions --}}
        <div x-show="selectedImages.length > 0" class="mt-6 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3" x-cloak>
            <span class="text-sm text-gray-600">Selected: <span x-text="selectedImages.length"></span></span>
            <x-admin.action-button variant="secondary" type="button" class="px-3 py-1.5 text-sm font-semibold" @click="selectAll">
                Select All
            </x-admin.action-button>
            <x-admin.action-button variant="secondary" type="button" class="px-3 py-1.5 text-sm font-semibold" @click="deselectAll">
                Deselect All
            </x-admin.action-button>
            <button @click="bulkDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                Delete Selected
            </button>
        </div>
    </div>

    <hr class="my-14 border-gray-300">


    {{-- ============================================================= --}}
    {{-- TEXT CONTENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="mb-10 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 md:p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Text Content</h2>
                <p class="text-gray-500 text-sm">Paragraphs and captions displayed on the Packages page.</p>
            </div>

            <x-admin.action-button variant="primary" class="px-4 py-2 text-sm font-semibold" @click="showTextModal = true; resetForm()">
                + Add Text
            </x-admin.action-button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/80 backdrop-blur">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Label</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Text</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                <template x-for="text in texts" :key="text.id">
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 font-medium text-gray-900" x-text="text.title || 'No Label'"></td>

                        <td class="px-6 py-4 text-gray-600 text-sm" x-text="truncateText(text.content || '', 150)"></td>

                        <td class="px-6 py-4 text-right space-x-4">
                            <x-admin.action-button
                                variant="link"
                                class="px-0"
                                @click="editText(text.id)"
                            >
                                Edit
                            </x-admin.action-button>
                            <button
                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                @click="deleteText(text.id)"
                            >Delete</button>
                        </td>
                    </tr>
                </template>
                <template x-if="texts.length === 0">
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-gray-500">
                            No text content yet.
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
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

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Slider Image' : 'Upload Slider Image'"></h3>

            <form id="swiperUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf
                <input type="hidden" name="_method" value="POST" x-bind:value="editImageMode ? 'PUT' : 'POST'">

                <div>
                    <label for="swiper_title" class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" id="swiper_title" x-ref="title" name="title" class="border rounded-xl p-3 w-full">
                </div>

                <div>
                    <label for="swiper_image" class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input type="file" id="swiper_image" x-ref="image" name="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <x-admin.action-button variant="secondary" type="button" @click="showModal = false; resetImageForm()">
                        Cancel
                    </x-admin.action-button>

                    <x-admin.action-button variant="primary" type="submit">
                        <span x-text="editImageMode ? 'Update' : 'Upload'"></span>
                    </x-admin.action-button>
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
                    <label for="title" class="block font-medium mb-1 text-gray-700">Label</label>
                    <input type="text" id="title" name="title" x-model="form.title" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label for="content" class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea id="content" name="content" x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter text..." required></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <x-admin.action-button variant="secondary" type="button" @click="showTextModal = false">
                        Cancel
                    </x-admin.action-button>

                    <x-admin.action-button variant="primary" type="submit">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </x-admin.action-button>
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
function packageManager() {
    return {
        isLoading: false,
        showModal: false,
        showTextModal: false,
        editMode: false,
        editImageMode: false,
        currentImageId: null,
        currentImageTitle: '',
        selectedImages: [],
        images: @js($images),
        texts: @js($texts),
        imageReorderRoute: '{{ route('package-swiper-images.reorder') }}',
        textDestroyBase: '/admin/packages/texts',
        sortableInstance: null,
        isReordering: false,
        canReorder: true,
        form: { id: null, title: '', content: '' },

        initialize() {
            this.images = this.sortImages(this.normalizeImages(this.images));
            this.texts = this.normalizeTexts(this.texts);
            this.$nextTick(() => this.initSortable());
        },

        normalizeImages(images) {
            return (Array.isArray(images) ? images : []).map((image) => ({
                ...image,
                id: Number(image?.id ?? 0),
                sort_order: Number(image?.sort_order ?? 0),
            })).filter((image) => image.id > 0);
        },

        normalizeTexts(texts) {
            return (Array.isArray(texts) ? texts : []).map((text) => ({
                ...text,
                id: Number(text?.id ?? 0),
            })).filter((text) => text.id > 0);
        },

        truncateText(text, limit = 150) {
            const value = String(text || '');
            if (value.length <= limit) {
                return value;
            }

            return `${value.slice(0, Math.max(0, limit - 3))}...`;
        },

        sortImages(images) {
            return [...images].sort((a, b) => {
                const aOrder = Number(a?.sort_order ?? 0);
                const bOrder = Number(b?.sort_order ?? 0);
                if (aOrder !== bOrder) {
                    return aOrder - bOrder;
                }

                const aDate = new Date(a?.created_at ?? 0).getTime();
                const bDate = new Date(b?.created_at ?? 0).getTime();
                if (aDate !== bDate) {
                    return aDate - bDate;
                }

                return Number(a?.id ?? 0) - Number(b?.id ?? 0);
            });
        },

        currentImageIds() {
            return this.images.map((image) => Number(image.id)).filter((id) => Number.isInteger(id) && id > 0);
        },

        selectAll() {
            this.selectedImages = this.currentImageIds();
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
            this.currentImageId = Number(id);
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
            this.$nextTick(() => {
                if (this.$refs.title) {
                    this.$refs.title.value = this.currentImageTitle;
                }
            });
        },

        removeImageById(id) {
            const normalizedId = Number(id);
            this.images = this.images.filter((image) => Number(image.id) !== normalizedId);
            this.selectedImages = this.selectedImages.filter((selectedId) => Number(selectedId) !== normalizedId);
            this.$nextTick(() => this.initSortable());
        },

        removeImagesByIds(ids) {
            const idSet = new Set((Array.isArray(ids) ? ids : []).map((value) => Number(value)));
            this.images = this.images.filter((image) => !idSet.has(Number(image.id)));
            this.selectedImages = this.selectedImages.filter((selectedId) => !idSet.has(Number(selectedId)));
            this.$nextTick(() => this.initSortable());
        },

        upsertImage(image) {
            if (!image || image.id === undefined || image.id === null) {
                return;
            }

            const normalized = this.normalizeImages([image])[0];
            if (!normalized) {
                return;
            }

            const current = Array.isArray(this.images) ? [...this.images] : [];
            const index = current.findIndex((item) => Number(item.id) === Number(normalized.id));

            if (index === -1) {
                current.push(normalized);
            } else {
                current.splice(index, 1, {
                    ...current[index],
                    ...normalized,
                });
            }

            this.images = this.sortImages(current);
            this.$nextTick(() => this.initSortable());
        },

        deleteImage(id) {
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
                    fetch(`/admin/packages/swiper-images/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: formData
                    }).then(async (response) => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to delete image.');
                        }
                        this.removeImageById(data.id || id);
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Image deleted successfully.',
                            confirmButtonColor: '#2563eb'
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

        deleteText(id) {
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
                    fetch(`${this.textDestroyBase}/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: formData
                    }).then(async (response) => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to delete text.');
                        }
                        this.removeTextById(data.id || id);
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Text deleted successfully.',
                            confirmButtonColor: '#2563eb'
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
            const text = this.texts.find((t) => Number(t.id) === Number(id));
            if (text) {
                this.form = { id: text.id, title: text.title, content: text.content };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        removeTextById(id) {
            const normalizedId = Number(id);
            this.texts = this.texts.filter((text) => Number(text.id) !== normalizedId);
        },

        upsertText(text) {
            if (!text || text.id === undefined || text.id === null) {
                return;
            }

            const normalized = this.normalizeTexts([text])[0];
            if (!normalized) {
                return;
            }

            const current = Array.isArray(this.texts) ? [...this.texts] : [];
            const index = current.findIndex((item) => Number(item.id) === Number(normalized.id));

            if (index === -1) {
                current.push(normalized);
            } else {
                current.splice(index, 1, {
                    ...current[index],
                    ...normalized,
                });
            }

            this.texts = current;
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
                    fetch(`/admin/packages/swiper-images/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        },
                        body: '_method=DELETE'
                    })
                );

                await Promise.all(promises);
                this.removeImagesByIds(this.selectedImages);
                this.selectedImages = [];
                Swal.fire({
                    icon: "success",
                    title: "Delete Complete",
                    text: "Images deleted successfully!",
                    confirmButtonColor: "#2563eb"
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

            let url = '{{ route('package-texts.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/packages/texts/${this.form.id}`;
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
                    if (data.text) {
                        this.upsertText(data.text);
                    }
                    Swal.fire({
                        icon: "success",
                        title: this.editMode ? "Updated!" : "Added!",
                        text: data.message || "Saved successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.resetForm();
                        this.showTextModal = false;
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

            const url = this.editImageMode ? '/admin/packages/swiper-images/' + this.currentImageId : '{{ route('package-swiper-images.store') }}';

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
                    if (data.image) {
                        this.upsertImage(data.image);
                    }
                    Swal.fire({
                        icon: "success",
                        title: this.editImageMode ? "Update Complete" : "Upload Complete",
                        text: data.message || (this.editImageMode ? "Image updated successfully!" : "Image uploaded successfully!"),
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.showModal = false;
                        this.resetImageForm();
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
        },

        initSortable() {
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }

            if (!this.canReorder || typeof Sortable === 'undefined' || !this.$refs.imageGrid) {
                return;
            }

            this.sortableInstance = Sortable.create(this.$refs.imageGrid, {
                animation: 180,
                handle: '[data-drag-handle]',
                draggable: '[data-cms-image-card]',
                ghostClass: 'cms-image-ghost',
                chosenClass: 'cms-image-chosen',
                dragClass: 'cms-image-drag',
                onEnd: async () => {
                    const previousImages = this.images.map((image) => ({ ...image }));
                    const orderedIds = Array.from(
                        this.$refs.imageGrid.querySelectorAll('[data-cms-image-id]')
                    )
                        .map((node) => Number(node.dataset.cmsImageId))
                        .filter((id) => Number.isInteger(id) && id > 0);

                    if (orderedIds.length !== this.images.length) {
                        return;
                    }

                    this.applyImageOrder(orderedIds);
                    await this.persistImageOrder(orderedIds, previousImages);
                },
            });
        },

        applyImageOrder(orderedIds) {
            const imageMap = new Map(this.images.map((image) => [Number(image.id), image]));
            this.images = orderedIds
                .map((id, index) => {
                    const image = imageMap.get(Number(id));
                    if (!image) {
                        return null;
                    }

                    return {
                        ...image,
                        sort_order: index + 1,
                    };
                })
                .filter(Boolean);
        },

        async persistImageOrder(orderedIds, previousImages = []) {
            if (this.isReordering) {
                return;
            }

            this.isReordering = true;
            this.sortableInstance?.option('disabled', true);

            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                const response = await fetch(this.imageReorderRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ ordered_ids: orderedIds }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok || payload.success === false) {
                    throw new Error(payload.message || 'Failed to save image order.');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Order Not Saved',
                    text: error.message || 'Failed to save image order.',
                    confirmButtonColor: '#ef4444',
                });
                this.images = Array.isArray(previousImages) ? previousImages : this.images;
                this.$nextTick(() => this.initSortable());
            } finally {
                this.isReordering = false;
                this.sortableInstance?.option('disabled', false);
            }
        },

        isVideoPath(path) {
            if (!path) {
                return false;
            }

            const normalized = String(path).trim();
            if (!normalized) {
                return false;
            }

            return /\/video\//i.test(normalized) || /\.(mp4|webm|ogg|mov|avi|wmv)$/i.test(normalized);
        },

        resolveImageSrc(path) {
            const normalized = String(path || '').trim();
            if (!normalized) {
                return '';
            }

            if (normalized.startsWith('data:')) {
                return normalized;
            }

            if (normalized.startsWith('//')) {
                return `https:${normalized}`;
            }

            if (/^https?:\/\//i.test(normalized)) {
                return normalized;
            }

            return normalized.startsWith('/') ? normalized : `/${normalized}`;
        },

        videoThumbnailSrc(path) {
            const normalized = String(path || '').trim();
            if (!normalized) {
                return '';
            }

            if (/res\.cloudinary\.com/i.test(normalized) && /\/video\/upload\//i.test(normalized)) {
                return normalized
                    .replace('/video/upload/', '/video/upload/so_0,w_1280/')
                    .replace(/\.(mp4|webm|ogg|mov|avi|wmv)(\?.*)?$/i, '.jpg');
            }

            return normalized;
        },

        formatDate(value) {
            if (!value) {
                return 'N/A';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return 'N/A';
            }

            return date.toLocaleDateString();
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

@extends('layouts.admin')

@section('title', 'Dining Category Images')

@section('content')

<div x-data="diningImageManager()" class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">
    
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Dining Page Images
    </h1>
    <p class="text-gray-600 mb-10">Manage hero category images and Learn More modal swiper images for the Dining page.</p>

    {{-- ============================================================= --}}
    {{-- ============================================================= --}}
    {{-- HERO CATEGORY IMAGES (Top of /dining page) --}}
    {{-- ============================================================= --}}

    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Hero Category Images</h2>
            <p class="text-gray-500 text-sm">Large hero images at the top of the Dining page (Messhall & Cook Your Own).</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-10">
        <!-- Messhall Card -->
        <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative">
            <div class="overflow-hidden rounded-xl mb-4 bg-linear-to-br from-gray-100 to-gray-200">
                @if(isset($diningImages['messhall']))
                    <img src="{{ $diningImages['messhall']->image_path }}" alt="Messhall" class="w-full h-48 object-cover transition hover:scale-105">
                @else
                    <div class="w-full h-48 flex items-center justify-center bg-gray-50">
                        <div class="text-center">
                            <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                            <span class="text-gray-500 text-sm">No image uploaded</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mb-4">
                <h3 class="font-bold text-lg text-gray-900">Messhall</h3>
                <p class="text-gray-500 text-sm">
                    @if(isset($diningImages['messhall']))
                        Updated: {{ $diningImages['messhall']->updated_at->format('M d, Y') }}
                    @else
                        No image uploaded yet
                    @endif
                </p>
            </div>

            <button @click="openUploadModal('messhall')" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold shadow-md hover:shadow-lg text-sm mb-2">
                <i data-lucide="upload-cloud" class="w-4 h-4 inline mr-2"></i>
                {{ isset($diningImages['messhall']) ? 'Update Image' : 'Upload Image' }}
            </button>

            @if(isset($diningImages['messhall']))
                <button type="button" @click="deleteImage('{{ $diningImages['messhall']->id }}', '{{ route('admin.dining.category-images.destroy', $diningImages['messhall']->id) }}')" 
                        class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold shadow-md hover:shadow-lg text-sm">
                    <i data-lucide="trash-2" class="w-4 h-4 inline mr-2"></i>
                    Delete Image
                </button>
            @endif
        </div>

        <!-- Cook Your Own Card -->
        <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative">
            <div class="overflow-hidden rounded-xl mb-4 bg-linear-to-br from-gray-100 to-gray-200">
                @if(isset($diningImages['cook_your_own']))
                    <img src="{{ $diningImages['cook_your_own']->image_path }}" alt="Cook Your Own" class="w-full h-48 object-cover transition hover:scale-105">
                @else
                    <div class="w-full h-48 flex items-center justify-center bg-gray-50">
                        <div class="text-center">
                            <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                            <span class="text-gray-500 text-sm">No image uploaded</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mb-4">
                <h3 class="font-bold text-lg text-gray-900">Cook Your Own</h3>
                <p class="text-gray-500 text-sm">
                    @if(isset($diningImages['cook_your_own']))
                        Updated: {{ $diningImages['cook_your_own']->updated_at->format('M d, Y') }}
                    @else
                        No image uploaded yet
                    @endif
                </p>
            </div>

            <button @click="openUploadModal('cook_your_own')" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold shadow-md hover:shadow-lg text-sm mb-2">
                <i data-lucide="upload-cloud" class="w-4 h-4 inline mr-2"></i>
                {{ isset($diningImages['cook_your_own']) ? 'Update Image' : 'Upload Image' }}
            </button>

            @if(isset($diningImages['cook_your_own']))
                <button type="button" @click="deleteImage('{{ $diningImages['cook_your_own']->id }}', '{{ route('admin.dining.category-images.destroy', $diningImages['cook_your_own']->id) }}')" 
                        class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold shadow-md hover:shadow-lg text-sm">
                    <i data-lucide="trash-2" class="w-4 h-4 inline mr-2"></i>
                    Delete Image
                </button>
            @endif
        </div>
    </div>

    {{--<hr class="my-14 border-gray-300"> --}}

    {{-- ============================================================= --}}
    {{-- LEARN MORE MODAL SWIPER IMAGES --}}
    {{-- ============================================================= --}}

    {{-- <div class="flex justify-between items-end mb-6 mt-12">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Learn More Modal Swiper</h2>
            <p class="text-gray-500 text-sm">Carousel images inside food item modals when clicking "Learn More".</p>
        </div>

        <button 
            @click="openSwiperModal()" 
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition"
        >
            + Add Image
        </button>
    </div> --}}

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Images Table --}}
    {{-- <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">#</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Preview</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Food Name</th>
                        <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Title</th>
                        <th class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Uploaded</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($images as $index => $img)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">
                                {{ ($images->currentPage() - 1) * $images->perPage() + $index + 1 }}
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-4">
                                <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-16 h-10 sm:w-20 sm:h-12 object-cover rounded-xl">
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->food->name ?? 'Untitled' }}</td>
                            <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->title ?? 'Untitled' }}</td>
                            <td class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->created_at->format('Y-m-d') }}</td>
                            <td class="px-3 sm:px-4 lg:px-6 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1 sm:gap-2 whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-xs sm:text-sm"
                                        @click="editSwiperImage({{ $img->id }}, '{{ $img->food_id }}', '{{ $img->title }}')"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-xs sm:text-sm"
                                        @click="deleteSwiperImage({{ $img->id }})"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 sm:px-4 lg:px-6 py-6 text-center text-gray-500">No images uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($images->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $images->links('pagination::tailwind') }}
        </div>
    @endif --}}

    <!-- Swiper Image Upload/Edit Modal -->
    <div 
        x-show="showSwiperModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-gray-200">
            <button 
                @click="closeSwiperModal()"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editSwiperMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form id="swiperUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadSwiperImage">
                @csrf

                <div>
                    <label for="food_id" class="block font-medium mb-1 text-gray-700">Food Item *</label>
                    <select id="food_id" x-ref="foodSelect" name="food_id" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <option value="">Select Food Item</option>
                        @foreach($foods as $food)
                            <option value="{{ $food->id }}">{{ $food->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="swiper_title" class="block font-medium mb-1 text-gray-700">Title (Optional)</label>
                    <input id="swiper_title" type="text" x-ref="swiperTitle" name="title" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter image title">
                </div>

                <div>
                    <label for="swiper_image" class="block font-medium mb-1 text-gray-700">Upload Image/Video <span x-show="!editSwiperMode" class="text-red-500">*</span></label>
                    <input id="swiper_image" type="file" x-ref="swiperImage" name="image" accept="image/*,video/*" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" :required="!editSwiperMode">
                    <p class="text-xs text-gray-500 mt-1">Max 100MB • JPG, PNG, GIF, MP4, MOV</p>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="closeSwiperModal()"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                        x-text="editSwiperMode ? 'Update' : 'Upload'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Image Upload Modal -->
    <div 
        x-show="showUploadModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-gray-200">
            <button 
                @click="showUploadModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900">Upload Category Image</h3>

            <form id="diningUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf

                <div>
                    <label for="category" class="block font-medium mb-1 text-gray-700">Category</label>
                    <input type="text" id="category" x-model="selectedCategory" disabled class="border rounded-xl p-3 w-full bg-gray-50 text-gray-600">
                </div>

                <div>
                    <label for="category_image" class="block font-medium mb-1 text-gray-700">Upload Image *</label>
                    <input type="file" id="category_image" x-ref="image" name="image" accept="image/*" class="border rounded-xl p-3 w-full" required>
                    <p class="text-xs text-gray-500 mt-1">Max 5MB • JPG, PNG, GIF</p>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showUploadModal = false"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        Upload
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
function diningImageManager() {
    return {
        isLoading: false,
        showUploadModal: false,
        showSwiperModal: false,
        selectedCategory: '',
        editSwiperMode: false,
        currentSwiperId: null,

        openUploadModal(category) {
            this.selectedCategory = category === 'messhall' ? 'Messhall' : 'Cook Your Own';
            this.showUploadModal = true;
        },

        openSwiperModal() {
            this.editSwiperMode = false;
            this.currentSwiperId = null;
            this.showSwiperModal = true;
            this.$nextTick(() => {
                if (this.$refs.foodSelect) this.$refs.foodSelect.value = '';
                if (this.$refs.swiperTitle) this.$refs.swiperTitle.value = '';
                if (this.$refs.swiperImage) this.$refs.swiperImage.value = '';
            });
        },

        closeSwiperModal() {
            this.showSwiperModal = false;
            this.editSwiperMode = false;
            this.currentSwiperId = null;
        },

        editSwiperImage(id, foodId, title) {
            this.currentSwiperId = id;
            this.editSwiperMode = true;
            this.showSwiperModal = true;
            
            this.$nextTick(() => {
                const foodSelect = this.$refs.foodSelect;
                const titleInput = this.$refs.swiperTitle;

                if (foodSelect) foodSelect.value = foodId || '';
                if (titleInput) titleInput.value = title || '';
            });
        },

        async uploadSwiperImage() {
            const fileInput = this.$refs.swiperImage;
            const titleInput = this.$refs.swiperTitle;
            const foodSelect = this.$refs.foodSelect;

            if (!fileInput?.files?.[0] && !this.editSwiperMode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Image Selected',
                    text: 'Please select an image before uploading.'
                });
                return;
            }

            if (!foodSelect?.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Food Selected',
                    text: 'Please select a food item.'
                });
                return;
            }

            const formData = new FormData();
            if (fileInput.files[0]) formData.append('image', fileInput.files[0]);
            formData.append('title', titleInput?.value || '');
            formData.append('food_id', foodSelect.value);

            if (this.editSwiperMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editSwiperMode
                ? `/admin/food-swiper-images/${this.currentSwiperId}`
                : "{{ route('food-swiper-images.store') }}";

            this.isLoading = true;

            try {
                const csrf = document.querySelector('meta[name=csrf-token]').content;
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
                        title: this.editSwiperMode ? "Updated!" : "Uploaded!",
                        text: data.message || "Image processed successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.closeSwiperModal();
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: "error",
                        title: "Validation Error",
                        html: Object.values(data.errors).flat().join("<br>")
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: data.message || "Failed to process image. Please try again."
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

        deleteSwiperImage(id) {
            Swal.fire({
                title: 'Delete Image?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    this.isLoading = true;

                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    
                    try {
                        const res = await fetch(`/admin/food-swiper-images/${id}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            body: formData
                        });

                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Image deleted successfully.',
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete image.',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting.',
                            confirmButtonColor: '#dc2626'
                        });
                    } finally {
                        this.isLoading = false;
                    }
                }
            });
        },

        async uploadImage() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            const imageInput = this.$refs.image;
            const categoryMap = {
                'Messhall': 'messhall',
                'Cook Your Own': 'cook_your_own'
            };

            if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Image Selected',
                    text: 'Please select an image before uploading.'
                });
                this.isLoading = false;
                return;
            }

            formData.append('category', categoryMap[this.selectedCategory]);
            formData.append('image', imageInput.files[0]);

            try {
                const res = await fetch('{{ route('admin.dining.category-images.store') }}', {
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
                        title: "Upload Complete",
                        text: "Image uploaded successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.showUploadModal = false;
                        imageInput.value = '';
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: "error",
                        title: "Validation Error",
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

        async deleteImage(id, route) {
            Swal.fire({
                title: 'Delete Image?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    this.isLoading = true;

                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    
                    try {
                        const res = await fetch(route, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            body: formData
                        });

                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Image deleted successfully.',
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete image.',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting.',
                            confirmButtonColor: '#dc2626'
                        });
                    } finally {
                        this.isLoading = false;
                    }
                }
            });
        }
    };
}
</script>

@endsection

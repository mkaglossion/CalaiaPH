@extends('layouts.admin')

@section('title', 'Dining Category Images')

@section('content')

<div x-data="diningImageManager()" class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">
    
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Page Information
    </h1>
    <p class="text-gray-600 mb-10">Manage the category images displayed on your Dining page.</p>

    {{-- ============================================================= --}}
    {{-- DINING CATEGORY IMAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dining Category Images</h2>
            <p class="text-gray-500 text-sm">Hero images for Messhall and Cook Your Own categories.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-10">
        <!-- Messhall Card -->
        <div class="bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition">
            <div class="overflow-hidden rounded-xl mb-4 bg-linear-to-br from-gray-100 to-gray-200">
                @if(isset($diningImages['messhall']))
                    <img src="{{ $diningImages['messhall']->image_path }}" alt="Messhall" class="w-full h-48 object-cover transition hover:scale-105">
                @else
                    <div class="w-full h-48 flex items-center justify-center">
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
        <div class="bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition">
            <div class="overflow-hidden rounded-xl mb-4 bg-linear-to-br from-gray-100 to-gray-200">
                @if(isset($diningImages['cook_your_own']))
                    <img src="{{ $diningImages['cook_your_own']->image_path }}" alt="Cook Your Own" class="w-full h-48 object-cover transition hover:scale-105">
                @else
                    <div class="w-full h-48 flex items-center justify-center">
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

    <!-- Upload Modal -->
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

            <h3 class="text-xl font-bold mb-4 text-gray-900">Upload Image</h3>

            <form id="diningUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Category</label>
                    <input type="text" x-model="selectedCategory" disabled class="border rounded-xl p-3 w-full bg-gray-50 text-gray-600">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Upload Image *</label>
                    <input type="file" x-ref="image" name="image" accept="image/*" class="border rounded-xl p-3 w-full" required>
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
        selectedCategory: '',

        openUploadModal(category) {
            this.selectedCategory = category === 'messhall' ? 'Messhall' : 'Cook Your Own';
            this.showUploadModal = true;
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

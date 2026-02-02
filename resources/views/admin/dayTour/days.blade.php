@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .swiper-button-next, .swiper-button-prev {
        color: #2563eb;
    }
    .swiper-pagination-bullet-active {
        background-color: #2563eb;
    }
</style>

<div
    x-data="dayTourManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
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
                {{-- Package Image --}}
                @if($tour->image)
                    <div class="mb-4 overflow-hidden rounded-xl">
                        <img src="{{ $tour->image }}" 
                             alt="{{ $tour->name }}" 
                             class="w-full h-32 object-cover hover:scale-105 transition-transform">
                    </div>
                @else
                    <div class="mb-4 bg-gray-100 rounded-xl h-32 flex items-center justify-center">
                        <span class="text-gray-400 text-sm">No image</span>
                    </div>
                @endif

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

                    <form id="delete-daytour-{{ $tour->id }}" action="{{ route('day-tours.destroy', $tour->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button
                            type="button"
                            @click="deleteDayTour({{ $tour->id }})"
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
    {{-- SWIPER IMAGES MANAGEMENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Swiper Images Management</h2>
            <p class="text-gray-500 text-sm">Manage images for the Day Tour swiper carousel.</p>
        </div>
        <button
            @click="showModal = true; resetImageForm()"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition"
        >
            + Add Image
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <table class="w-full text-left">
            <thead class="bg-gray-100/70 backdrop-blur">
                <tr>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Preview</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Package</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Title</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Uploaded</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse(($images ?? collect())->whereNotNull('daytour_id') as $index => $img)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            @if(str_contains($img->image_path, '/video/'))
                                <video src="{{ $img->image_path }}" class="w-20 h-12 object-cover rounded" muted></video>
                            @else
                                <img src="{{ $img->image_path }}" class="w-20 h-12 object-cover rounded">
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $img->daytour_id ? $tours->find($img->daytour_id)->name ?? 'Unknown Package' : 'No Package' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                                @click="editImage({{ $img->id }}, '{{ $img->daytour_id }}', '{{ $img->title }}')">
                                Edit
                            </button>
                            <form action="{{ route('day-tour-swiper-images.destroy', $img->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                    onclick="return confirm('Delete this image?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                            No images uploaded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
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

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                :action="editImageMode ? '/admin/day-tour-swiper-images/' + currentImageId : '{{ route('day-tour-swiper-images.store') }}'"
                method="POST"
                @submit.prevent="uploadImage()"
            >
                @csrf
                <input type="hidden" name="_method" value="POST" x-bind:value="editImageMode ? 'PUT' : 'POST'">
                <div class="mb-3">
                    <label class="block font-semibold mb-1">Day Tour Package</label>
                    <select x-ref="daytour" name="daytour_id" class="border rounded-xl p-3 w-full" x-bind:value="editImageMode ? currentDaytourId : ''" required>
                        <option value="">Select Day Tour Package</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full" x-bind:value="editImageMode ? currentImageTitle : ''">
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Upload Image/Video</label>
                    <input type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full" x-bind:required="!editImageMode">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition" x-text="editImageMode ? 'Update' : 'Upload'"></button>
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

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Package Image</label>
                    
                    {{-- Current Image Preview (only in edit mode) --}}
                    <div x-show="editMode && currentImage" class="mb-3">
                        <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                        <img :src="currentImage" alt="Current package image" class="w-full h-32 object-cover rounded-xl border">
                    </div>
                    
                    <input type="file" x-ref="image" accept="image/*" class="w-full border rounded-xl px-3 py-2">
                    <p class="text-sm text-gray-500 mt-1">Upload a new image to replace the current one (optional)</p>
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

    {{-- Add/Edit Swiper Image Modal --}}
    <div x-show="showImageModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showImageModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900">Upload New Swiper Image</h3>

            <form id="imageUploadForm" @submit.prevent="saveImage" class="space-y-5" enctype="multipart/form-data">
                @csrf
                <template x-if="editImageMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Day Tour Selection -->
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Day Tour Package</label>
                    <select 
                        x-ref="daytour"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                    >
                        <option value="">Select Package</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}" x-bind:selected="currentDaytourId == '{{ $tour->id }}'">{{ $tour->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Image Title -->
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Image Title (Optional)</label>
                    <input 
                        type="text" 
                        x-ref="title"
                        x-model="currentImageTitle"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter image title"
                    >
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input 
                        type="file" 
                        x-ref="imageFile"
                        accept="image/*,video/*"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        x-bind:required="!editImageMode"
                    >
                    <p class="text-sm text-gray-500 mt-1">No file chosen</p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-2">
                    <button 
                        type="button" 
                        @click="showImageModal = false; resetImageForm()" 
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                    >
                        <span x-text="editImageMode ? 'Update' : 'Upload'"></span>
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

    <hr class="my-14 border-gray-300">

    {{-- ============================================================= --}}
    {{-- IMPORTANT INFORMATION SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Important Information</h2>
            <p class="text-gray-500 text-sm">Manage the information section displayed at the bottom of the Day Tour page.</p>
        </div>
    </div>

    <div class="bg-white/60 backdrop-blur rounded-2xl border border-gray-200 shadow-sm p-8">
        {{-- Title and Subtitle --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block font-medium mb-2 text-gray-700">Section Title</label>
                <input 
                    type="text" 
                    x-model="infoForm.title" 
                    class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Important Information"
                >
                <button 
                    @click="saveInfoTitle" 
                    class="mt-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition text-sm"
                >
                    Update Title
                </button>
            </div>
            <div>
                <label class="block font-medium mb-2 text-gray-700">Section Subtitle</label>
                <input 
                    type="text" 
                    x-model="infoForm.subtitle" 
                    class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Everything you need to know before your visit"
                >
                <button 
                    @click="saveInfoSubtitle" 
                    class="mt-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition text-sm"
                >
                    Update Subtitle
                </button>
            </div>
        </div>

        {{-- Info Items --}}
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Information Items</h3>
                <button 
                    @click="showAddItemModal = true" 
                    class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700 shadow transition"
                >
                    + Add Item
                </button>
            </div>

            <div class="space-y-4" x-show="infoForm.items.length > 0">
                <template x-for="(item, index) in infoForm.items" :key="index">
                    <div class="flex items-center space-x-4 bg-gray-50 p-4 rounded-xl">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <select 
                                x-model="item.icon" 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="bi-clock">Clock (bi-clock)</option>
                                <option value="bi-calendar-check">Calendar Check (bi-calendar-check)</option>
                                <option value="bi-person">Person (bi-person)</option>
                                <option value="bi-people">People (bi-people)</option>
                                <option value="bi-cup-straw">Cup Straw (bi-cup-straw)</option>
                                <option value="bi-info-circle">Info Circle (bi-info-circle)</option>
                                <option value="bi-check-circle">Check Circle (bi-check-circle)</option>
                                <option value="bi-exclamation-triangle">Exclamation Triangle (bi-exclamation-triangle)</option>
                                <option value="bi-star">Star (bi-star)</option>
                                <option value="bi-heart">Heart (bi-heart)</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                            <input 
                                type="text" 
                                x-model="item.text" 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="Enter information text"
                            >
                        </div>
                        <button 
                            @click="confirmRemoveInfoItem(index)" 
                            class="px-3 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 shadow transition mt-6"
                        >
                            Remove
                        </button>
                    </div>
                </template>
            </div>

            <div x-show="infoForm.items.length === 0" class="text-center py-8 text-gray-500">
                No information items added yet.
            </div>

            <div class="mt-6" x-show="infoForm.items.length > 0">
                <button 
                    @click="saveInfoItems" 
                    class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                >
                    Save All Items
                </button>
            </div>
        </div>
    </div>

    {{-- Add Item Modal --}}
    <div x-show="showAddItemModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showAddItemModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900">Add Information Item</h3>

            <div class="space-y-4">
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Icon</label>
                    <select 
                        x-model="infoForm.newItem.icon" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        <option value="bi-clock">Clock (bi-clock)</option>
                        <option value="bi-calendar-check">Calendar Check (bi-calendar-check)</option>
                        <option value="bi-person">Person (bi-person)</option>
                        <option value="bi-people">People (bi-people)</option>
                        <option value="bi-cup-straw">Cup Straw (bi-cup-straw)</option>
                        <option value="bi-info-circle">Info Circle (bi-info-circle)</option>
                        <option value="bi-check-circle">Check Circle (bi-check-circle)</option>
                        <option value="bi-exclamation-triangle">Exclamation Triangle (bi-exclamation-triangle)</option>
                        <option value="bi-star">Star (bi-star)</option>
                        <option value="bi-heart">Heart (bi-heart)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Text</label>
                    <input 
                        type="text" 
                        x-model="infoForm.newItem.text" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter information text"
                    >
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button 
                    @click="showAddItemModal = false; infoForm.newItem = { icon: 'bi-info-circle', text: '' }" 
                    class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition"
                >
                    Cancel
                </button>
                <button 
                    @click="addInfoItem" 
                    class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                >
                    Add Item
                </button>
            </div>
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
        showDayTourModal: false,
        showTextModal: false,
        showImageModal: false,
        showAddItemModal: false,
        editMode: false,
        editImageMode: false,
        currentImage: null,
        currentImageId: null,
        currentDaytourId: '',
        currentImageTitle: '',
        texts: @js($texts),
        dayTours: @js($tours),
        activities: @js($activities),
        infoForm: {
            title: @js($infoTitle ? $infoTitle->content : 'Important Information'),
            subtitle: @js($infoSubtitle ? $infoSubtitle->content : 'Everything you need to know before your visit'),
            items: @js(count($infoItems) > 0 ? $infoItems : [
                ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
                ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
                ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
                ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
                ['icon' => 'bi-person', 'text' => 'Wear comfy clothing and footwear for outdoor activities'],
                ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site']
            ]),
            titleId: @js($infoTitle ? $infoTitle->id : null),
            subtitleId: @js($infoSubtitle ? $infoSubtitle->id : null),
            itemsId: @js($infoItemsJson ? $infoItemsJson->id : null),
            newItem: { icon: 'bi-info-circle', text: '' }
        },
        form: { id: null, name: '', description: '', price: '', activity_ids: [], package_id: '', label: '', text: '' },

        resetForm() {
            this.form = { id: null, name: '', description: '', price: '', activity_ids: [], package_id: '', label: '', text: '' };
            this.editMode = false;
            this.currentImage = null;
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            this.currentDaytourId = '';
            this.currentImageTitle = '';
        },

        editImage(id, daytourId, title) {
            this.currentImageId = id;
            this.currentDaytourId = daytourId;
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
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
                this.currentImage = tour.swiperImages && tour.swiperImages.length > 0 ? tour.swiperImages[0].image_path : null;
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
                    package_id: text.daytour_id,
                    label: text.title,
                    text: text.content
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
            if (this.$refs.image && this.$refs.image.files[0]) {
                formData.append('image', this.$refs.image.files[0]);
            }

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
        },

        // Info Management
        addInfoItem() {
            this.$set(this, 'infoForm', {
                ...this.infoForm,
                items: [...this.infoForm.items, { ...this.infoForm.newItem }]
            });
            this.infoForm.newItem = { icon: 'bi-info-circle', text: '' };
            this.showAddItemModal = false;
        },

        confirmRemoveInfoItem(index) {
            Swal.fire({
                icon: 'warning',
                title: 'Remove Item?',
                text: 'This will permanently remove this information item.',
                showCancelButton: true,
                confirmButtonText: 'Remove',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280'
            }).then(result => {
                if (result.isConfirmed) {
                    this.removeInfoItem(index);
                }
            });
        },

        removeInfoItem(index) {
            this.infoForm.items.splice(index, 1);
            // Auto-save after removal
            this.saveInfoItems();
        },

        async saveInfoTitle() {
            await this.saveInfoField('Title', this.infoForm.title, this.infoForm.titleId, 'info_title');
        },

        async saveInfoSubtitle() {
            await this.saveInfoField('Subtitle', this.infoForm.subtitle, this.infoForm.subtitleId, 'info_subtitle');
        },

        async saveInfoItems() {
            await this.saveInfoField('Items', JSON.stringify(this.infoForm.items), this.infoForm.itemsId, 'info_items');
        },

        async saveInfoField(fieldName, content, id, title) {
            this.isLoading = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);

            let url = '{{ route("day-tour-texts.store") }}';
            if (id) {
                url = `/admin/day-tour-texts/${id}`;
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
                        title: 'Updated!',
                        text: `${fieldName} saved successfully!`,
                        confirmButtonColor: '#2563eb'
                    });
                    if (!id) {
                        window.location.reload();
                    }
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
        uploadImage() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const fileInput = document.querySelector('#swiperUploadForm input[name="image"]').files[0];
            const title = document.querySelector('#swiperUploadForm input[name="title"]').value;
            const daytourId = document.querySelector('#swiperUploadForm select[name="daytour_id"]').value;

            if (!this.editImageMode && !fileInput) {
                alert('Please select an image before uploading.');
                return;
            }

            if (!daytourId) {
                alert('Please select a day tour package.');
                return;
            }

            const formData = new FormData();
            if (fileInput) formData.append('image', fileInput);
            formData.append('title', title);
            formData.append('daytour_id', daytourId);
            if (this.editImageMode) formData.append('_method', 'PUT');

            const btn = this.$el.querySelector('button[type=submit]');
            btn.innerText = this.editImageMode ? 'Updating...' : 'Uploading...';
            btn.disabled = true;

            const url = this.editImageMode ? '/admin/day-tour-swiper-images/' + this.currentImageId : '{{ route('day-tour-swiper-images.store') }}';

            fetch(url, {
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
                    Swal.fire({
                        icon: 'success',
                        title: this.editImageMode ? 'Updated!' : 'Uploaded!',
                        text: data.message || (this.editImageMode ? 'Image updated successfully!' : 'Image uploaded successfully!'),
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        document.querySelector('#swiperUploadForm input[name="image"]').value = '';
                        document.querySelector('#swiperUploadForm input[name="title"]').value = '';
                        document.querySelector('#swiperUploadForm select[name="daytour_id"]').value = '';
                        this.showModal = false;
                        this.resetImageForm();
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
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: 'Something went wrong.'
                });
            })
            .finally(() => {
                btn.innerText = this.editImageMode ? 'Update' : 'Upload';
                btn.disabled = false;
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

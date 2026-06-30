@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    [x-cloak] { display: none !important; }

    .swiper-button-next, .swiper-button-prev { color: #2563eb; }
    .swiper-pagination-bullet-active { background-color: #2563eb; }

    .tilt-card {
        transform-style: preserve-3d;
        perspective: 1200px;
        transition: transform .25s ease, box-shadow .25s ease;
        will-change: transform;
    }
    .tilt-card:hover {
        transform: rotateX(6deg) rotateY(-6deg) translateY(-6px) scale(1.02);
        box-shadow: 0 16px 35px rgba(0,0,0,0.18);
    }

    /* Safer table viewing on small screens */
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
</style>

<div
    x-data="dayTourManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
        <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
            Day Tour Page Management
        </h1>
        <p class="text-gray-600 mb-10">
            Manage your Day Tour packages, activities, and text content.
        </p>

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
                class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition"
            >
                + Add Package
            </button>
        </div>

        {{-- Packages Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            @forelse($tours as $tour)
                <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition">
                    {{-- Package Image --}}
                    @if($tour->image)
                        <div class="mb-4 overflow-hidden rounded-xl">
                            <img src="{{ $tour->image }}"
                                 alt="{{ $tour->name }}"
                                 class="w-full h-48 object-cover hover:scale-105 transition-transform">
                        </div>
                    @else
                        <div class="mb-4 bg-gray-100 rounded-xl h-48 flex items-center justify-center">
                            <span class="text-gray-400 text-sm">No image</span>
                        </div>
                    @endif

                    <h3 class="font-semibold text-lg text-gray-900 wrap-break-word">{{ $tour->name }}</h3>
                    <p class="text-gray-500 text-sm mt-1 wrap-break-word">
                        {{ Str::limit($tour->description, 100) ?: '-' }}
                    </p>

                    {{-- Package Capacity & Pricing --}}
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                            <i class="bi bi-people mr-1"></i> Good for {{ $tour->max_guests }} persons
                        </span>
                        @if(!$tour->is_active)
                            <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <p class="text-gray-600 mt-2 font-medium">
                        @php
                            $priceWhole = fmod($tour->price, 1) == 0;
                            $perPerson = ($tour->max_guests && $tour->max_guests > 0) ? $tour->price / $tour->max_guests : 0;
                            $perPersonWhole = fmod($perPerson, 1) == 0;
                            $totalPrice = $tour->price * ($tour->max_guests ?? 1);
                            $totalWhole = fmod($totalPrice, 1) == 0;
                        @endphp
                        @if($tour->pricing_type === 'total')
                            ₱{{ $priceWhole ? number_format($tour->price) : number_format($tour->price, 2) }} total
                            <span class="text-sm text-gray-500">
                                (₱{{ $perPersonWhole ? number_format($perPerson) : number_format($perPerson, 2) }}/person)
                            </span>
                        @else
                            ₱{{ $priceWhole ? number_format($tour->price) : number_format($tour->price, 2) }} per person
                            <span class="text-sm text-gray-500">
                                (₱{{ $totalWhole ? number_format($totalPrice) : number_format($totalPrice, 2) }} total)
                            </span>
                        @endif
                    </p>

                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach($tour->activities as $activity)
                            <span class="inline-block bg-gray-200 px-2 py-1 rounded text-xs">
                                {{ $activity->name }}
                            </span>
                        @endforeach
                    </div>

                    <p class="text-gray-400 text-xs mt-2">Created: {{ $tour->created_at->format('Y-m-d') }}</p>

                    <div class="mt-4 flex justify-end gap-3">
                        <button
                            class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                            @click="editDayTour({{ $tour->id }})"
                        >
                            Edit
                        </button>

                        <form id="delete-daytour-{{ $tour->id }}" action="{{ route('day-tours.destroy', $tour->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button
                                type="button"
                                @click="deleteDayTour({{ $tour->id }})"
                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                            >
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
                <div
                    data-admin-flash
                    data-admin-flash-library="toastr"
                    data-admin-flash-title="Success"
                    data-admin-flash-type="success"
                    data-admin-flash-message="{{ e(session('success')) }}"
                ></div>
        @endif

        @if(session('error'))
                <div
                    data-admin-flash
                    data-admin-flash-library="toastr"
                    data-admin-flash-title="Error"
                    data-admin-flash-type="error"
                    data-admin-flash-message="{{ e(session('error')) }}"
                ></div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <table class="w-full text-left min-w-150">
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
                                    <video src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-20 h-12 object-cover rounded" muted></video>
                                @else
                                    <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" class="w-20 h-12 object-cover rounded" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $img->daytour_id ? $tours->find($img->daytour_id)->name ?? 'Unknown Package' : 'No Package' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $img->title ?? 'Untitled' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $img->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                                    @click="editImage({{ $img->id }}, @js($img->daytour_id), @js($img->title))">
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

        <hr class="my-10 md:my-14 border-gray-300">

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
                                        @click="editText({{ $text->id }})"
                                    >
                                        Edit
                                    </button>

                                    <form id="delete-text-{{ $text->id }}"
                                          action="{{ route('day-tour-texts.destroy', $text->id) }}"
                                          method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="button"
                                            @click="deleteText({{ $text->id }})"
                                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                        >
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
                <div>
                    <label for="info_title" class="block font-medium mb-2 text-gray-700">Section Title</label>
                    <input
                        type="text"
                        id="info_title"
                        name="info_title"
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
                    <label for="info_subtitle" class="block font-medium mb-2 text-gray-700">Section Subtitle</label>
                    <input
                        type="text"
                        id="info_subtitle"
                        name="info_subtitle"
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
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 bg-gray-50 p-3 sm:p-4 rounded-xl">
                            <div class="w-full sm:w-1/4">
                                <label :for="'item_icon_' + index" class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                                <select
                                    :id="'item_icon_' + index"
                                    name="item_icon[]"
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
                            <div class="flex-1 w-full">
                                <label :for="'item_text_' + index" class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                                <input
                                    type="text"
                                    :id="'item_text_' + index"
                                    name="item_text[]"
                                    x-model="item.text"
                                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Enter information text"
                                >
                            </div>

                            <div class="w-full sm:w-auto flex justify-end">
                                <button
                                    @click="confirmRemoveInfoItem(index)"
                                    class="px-3 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 shadow transition sm:mt-6"
                                >
                                    Remove
                                </button>
                            </div>
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

    {{-- ============================================================= --}}
    {{-- MODALS (Responsive + safe z-index) --}}
    {{-- ============================================================= --}}

    {{-- Swiper Upload/Edit Modal --}}
    <div
        x-show="showModal"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-9990 px-4"
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-5 sm:p-6 relative border border-gray-200">
            <button
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
                aria-label="Close"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-4 sm:space-y-5"
                enctype="multipart/form-data"
                :action="editImageMode ? '/admin/day-tour-swiper-images/' + currentImageId : '{{ route('day-tour-swiper-images.store') }}'"
                method="POST"
                @submit.prevent="uploadImage()"
            >
                @csrf
                <input type="hidden" name="_method" value="POST" x-bind:value="editImageMode ? 'PUT' : 'POST'">

                <div>
                    <label for="daytour_id" class="block font-semibold mb-1">Day Tour Package</label>
                    <select id="daytour_id" x-ref="daytour" name="daytour_id" class="border rounded-xl p-3 w-full" x-bind:value="editImageMode ? currentDaytourId : ''" required>
                        <option value="">Select Day Tour Package</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="swiper_title" class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" id="swiper_title" x-ref="title" name="title" class="border rounded-xl p-3 w-full" x-bind:value="editImageMode ? currentImageTitle : ''">
                </div>

                <div>
                    <label for="swiper_image" class="block font-semibold mb-1">Upload Image/Video</label>
                    <input type="file" id="swiper_image" x-ref="image" name="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full" x-bind:required="!editImageMode">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-1">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition w-full sm:w-auto">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition w-full sm:w-auto" x-text="editImageMode ? 'Update' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add/Edit Day Tour Modal --}}
    <div
        x-show="showDayTourModal"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-9990 px-4"
    >
        <div
            @click.away="showDayTourModal = false"
            class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-6xl p-4 sm:p-6 lg:p-8 border border-gray-200 max-h-[90vh] overflow-y-auto"
        >
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Day Tour Package' : 'Add Day Tour Package'"></h3>

            <form id="dayTourForm" @submit.prevent="saveDayTour" class="space-y-5">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label for="package_name" class="block font-medium mb-1 text-gray-700">Package Name</label>
                    <input type="text" id="package_name" name="name" x-model="form.name" class="w-full border rounded-xl px-3 py-2" placeholder="Package Name" required autocomplete="name">
                </div>

                <div>
                    <label for="package_description" class="block font-medium mb-1 text-gray-700">Description</label>
                    <textarea id="package_description" name="description" x-model="form.description" rows="3" class="w-full border rounded-xl px-3 py-2" placeholder="Description"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="max_guests" class="block font-medium mb-1 text-gray-700">Good For (Persons)</label>
                        <input type="number" id="max_guests" name="max_guests" x-model="form.max_guests" min="1" class="w-full border rounded-xl px-3 py-2" placeholder="e.g., 7" required>
                        <p class="text-xs text-gray-500 mt-1">Maximum persons for this package</p>
                    </div>
                    <div>
                        <label for="pricing_type" class="block font-medium mb-1 text-gray-700">Pricing Type</label>
                        <select id="pricing_type" name="pricing_type" x-model="form.pricing_type" class="w-full border rounded-xl px-3 py-2">
                            <option value="per_person">Per Person</option>
                            <option value="total">Total Package Price</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1" x-text="form.pricing_type === 'total' ? 'Price is for entire group' : 'Price per individual guest'"></p>
                    </div>
                </div>

                <div>
                    <label for="package_price" class="block font-medium mb-1 text-gray-700">
                        Price
                        <span x-text="form.pricing_type === 'total' ? '(Total for all guests)' : '(Per person)'" class="text-sm text-gray-500"></span>
                    </label>
                    <input type="number" id="package_price" name="price" x-model="form.price" step="0.01" class="w-full border rounded-xl px-3 py-2" placeholder="Price" required>
                    <p class="text-xs text-gray-500 mt-1" x-show="form.max_guests && form.price">
                        <span x-text="form.pricing_type === 'total'
                            ? '₱' + (Number.isInteger(Number(form.price / form.max_guests)) ? Number(form.price / form.max_guests).toLocaleString() : Number(form.price / form.max_guests).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})) + ' per person equivalent'
                            : '₱' + (Number.isInteger(Number(form.price * form.max_guests)) ? Number(form.price * form.max_guests).toLocaleString() : Number(form.price * form.max_guests).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})) + ' total for ' + form.max_guests + ' persons'">
                        </span>
                    </p>
                </div>

                <div>
                    <label for="is_active" class="block font-medium mb-2 text-gray-700">Package Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" x-model="form.is_active" :value="true" class="mr-2 w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-gray-700">Active (visible to customers)</span>
                    </div>
                </div>

                <div>
                    <span id="included_activities_label" class="block font-medium mb-2 text-gray-700">Included Activities</span>
                    <div class="max-h-64 overflow-y-auto border rounded-xl p-2" role="group" aria-labelledby="included_activities_label">
                        @foreach($activities as $activity)
                            <div class="flex items-center mb-1">
                                <input type="checkbox" id="activity_{{ $activity->id }}" name="activity_ids[]" value="{{ $activity->id }}" x-model="form.activity_ids" class="mr-2">
                                <label for="activity_{{ $activity->id }}" class="cursor-pointer">{{ $activity->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <label for="package_image" class="block text-sm font-medium">Upload Image (optional)</label>
                    <input type="file" id="package_image" x-ref="image" accept="image/*" 
                        @change="
                            const file = $el.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => form.imagePreview = e.target.result;
                                reader.readAsDataURL(file);
                            }
                        "
                        class="w-full border rounded-xl px-3 py-2">

                    <p class="text-sm text-gray-500 mt-1">Upload a new image (optional)</p>
                    
                    <template x-if="form.imagePreview">
                        <div class="relative inline-block">
                            <img :src="form.imagePreview" alt="Preview" class="h-32 rounded-lg border">
                            <button type="button" @click="form.imagePreview = null; $refs.image.value = ''" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700">
                                ×
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" @click="showDayTourModal = false; resetForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition w-full sm:w-auto">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition w-full sm:w-auto">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add/Edit Text Modal --}}
    <div x-show="showTextModal" x-transition.opacity class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-9990 px-4">
        <div @click.away="showTextModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-5 sm:p-7 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form id="textUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="saveText()">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label for="text_package_id" class="block font-medium mb-1 text-gray-700">Package</label>
                    <select id="text_package_id" name="package_id" x-model="form.package_id" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <option value="">Select Package</option>
                        @foreach($tours as $tour)
                            <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="text_label" class="block font-medium mb-1 text-gray-700">Label</label>
                    <input type="text" id="text_label" name="label" x-model="form.label" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Optional">
                </div>

                <div>
                    <label for="text_content" class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea id="text_content" name="text" x-model="form.text" rows="4" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter text..." required></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" @click="showTextModal = false" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition w-full sm:w-auto">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition w-full sm:w-auto">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Item Modal --}}
    <div x-show="showAddItemModal" x-transition.opacity class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-9990 px-4">
        <div @click.away="showAddItemModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-5 sm:p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900">Add Information Item</h3>

            <div class="space-y-4">
                <div>
                    <label for="new_item_icon" class="block font-medium mb-2 text-gray-700">Icon</label>
                    <select id="new_item_icon" name="new_item_icon" x-model="infoForm.newItem.icon" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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
                    <label for="new_item_text" class="block font-medium mb-2 text-gray-700">Text</label>
                    <input type="text" id="new_item_text" name="new_item_text" x-model="infoForm.newItem.text" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter information text">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6">
                <button @click="showAddItemModal = false; infoForm.newItem = { icon: 'bi-info-circle', text: '' }"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition w-full sm:w-auto">
                    Cancel
                </button>
                <button @click="addInfoItem"
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition w-full sm:w-auto">
                    Add Item
                </button>
            </div>
        </div>
    </div>

    {{-- GLOBAL LOADING OVERLAY --}}
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
    // ===== state =====
    isLoading: false,

    showModal: false,        // swiper upload modal
    showDayTourModal: false, // add/edit package modal
    showTextModal: false,    // add/edit text modal
    showImageModal: false,   // (legacy) image modal
    showAddItemModal: false, // info item modal

    editMode: false,         // package edit
    editImageMode: false,    // swiper image edit

    currentImage: null,
    currentImageId: null,
    currentDaytourId: '',
    currentImageTitle: '',

    texts: @js($texts),
    dayTours: @js($tours),
    activities: @js($activities),

    // ===== important info =====
    infoForm: {
      title: @js($infoTitle ? $infoTitle->content : 'Important Information'),
      subtitle: @js($infoSubtitle ? $infoSubtitle->content : 'Everything you need to know before your visit'),
      items: @js(count($infoItems) > 0 ? $infoItems : [
        ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
        ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
        ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
        ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
        ['icon' => 'bi-person', 'text' => 'Wear comfy clothing and footwear for outdoor activities'],
        ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site'],
      ]),
      titleId: @js($infoTitle ? $infoTitle->id : null),
      subtitleId: @js($infoSubtitle ? $infoSubtitle->id : null),
      itemsId: @js($infoItemsJson ? $infoItemsJson->id : null),
      newItem: { icon: 'bi-info-circle', text: '' }
    },

    // ===== package/text form =====
    form: {
      id: null,
      name: '',
      description: '',
      price: '',
      max_guests: 1,
      pricing_type: 'per_person',
      is_active: true,
      activity_ids: [],

      // text content modal fields
      package_id: '',
      label: '',
      text: '',
    },

    // ===== inventory =====
    inventoryTab: 'quick',
    inventoryQty: 3,
    inventoryStartDate: '',
    inventoryEndDate: '',
    inventoryQtyRange: 3,
    inventoryAvailable: true,

    resetForm() {
      this.editMode = false;
      this.form = {
        id: null,
        name: '',
        description: '',
        price: '',
        max_guests: 1,
        pricing_type: 'per_person',
        is_active: true,
        activity_ids: [],
        package_id: '',
        label: '',
        text: '',
        priceConfigs: [],
        imagePreview: null
      };
      this.currentImage = null;
    },

    resetImageForm() {
      this.editImageMode = false;
      this.currentImageId = null;
      this.currentDaytourId = '';
      this.currentImageTitle = '';
      const f = document.querySelector('#swiperUploadForm input[name="image"]');
      if (f) f.value = '';
    },

    editDayTour(id) {
      const tour = this.dayTours.find(t => t.id === id);
      if (!tour) return;

      this.editMode = true;
      this.form = {
        id: tour.id,
        name: tour.name || '',
        description: tour.description || '',
        price: tour.price || '',
        max_guests: tour.max_guests || 1,
        pricing_type: tour.pricing_type || 'per_person',
        is_active: tour.is_active ?? true,
        activity_ids: tour.activities ? tour.activities.map(a => a.id) : [],
        package_id: '',
        label: '',
        text: '',
        imagePreview: null
      };
      this.currentImage = tour.image || null;
      this.showDayTourModal = true;
      // Load preview for this day tour
      this.$nextTick(() => this.loadInventoryPreview());
    },

    async saveDayTour() {
      this.isLoading = true;

      const csrf = document.querySelector('meta[name=csrf-token]').content;
      const formData = new FormData();

      formData.append('name', this.form.name);
      formData.append('description', this.form.description || '');
      formData.append('price', this.form.price || 0);
      formData.append('max_guests', this.form.max_guests);
      formData.append('pricing_type', this.form.pricing_type);
      formData.append('is_active', this.form.is_active ? 1 : 0);

      this.form.activity_ids.forEach(id => formData.append('activity_ids[]', id));

      const imageInput = this.$refs.image;
      if (imageInput && imageInput.files && imageInput.files[0]) {
        formData.append('image', imageInput.files[0]);
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
          // Close modal immediately on success (like staycation)
          this.showDayTourModal = false;
          this.resetForm();
          Swal.fire({
            icon: 'success',
            title: this.editMode ? 'Updated!' : 'Created!',
            text: data.message || (this.editMode ? 'Day Tour updated successfully!' : 'Day Tour created successfully!'),
            confirmButtonColor: '#2563eb'
          }).then(() => {
            window.location.reload();
          });
        } else if (data.errors) {
          Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            html: Object.values(data.errors).flat().join('<br>')
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Operation failed.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Unexpected Error', text: 'Something went wrong.' });
      } finally {
        this.isLoading = false;
      }
    },

    editText(id) {
      const text = this.texts.find(t => t.id === id);
      if (!text) return;

      this.editMode = true;
      this.form.id = text.id;
      this.form.package_id = text.daytour_id || '';
      this.form.label = text.title || '';
      this.form.text = text.content || '';
      this.showTextModal = true;
    },

    async saveText() {
      this.isLoading = true;

      const csrf = document.querySelector('meta[name=csrf-token]').content;
      const formData = new FormData();
      formData.append('daytour_id', this.form.package_id);
      formData.append('title', this.form.label || '');
      formData.append('content', this.form.text);

      let url = '{{ route("day-tour-texts.store") }}';
      if (this.editMode && this.form.id) {
        url = `/admin/day-tour-texts/${this.form.id}`;
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
          // Close modal immediately on success (like staycation)
          this.showTextModal = false;
          this.resetForm();
          Swal.fire({
            icon: 'success',
            title: this.editMode ? 'Updated!' : 'Created!',
            text: data.message || (this.editMode ? 'Text content updated successfully!' : 'Text content created successfully!'),
            confirmButtonColor: '#2563eb'
          }).then(() => {
            window.location.reload();
          });
        } else if (data.errors) {
          Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            html: Object.values(data.errors).flat().join('<br>')
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Operation failed.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Unexpected Error', text: 'Something went wrong.' });
      } finally {
        this.isLoading = false;
      }
    },

    editImage(id, daytourId, title) {
      this.editImageMode = true;
      this.currentImageId = id;
      this.currentDaytourId = daytourId ?? '';
      this.currentImageTitle = title ?? '';
      this.showModal = true;

      this.$nextTick(() => {
        if (this.$refs.daytour) this.$refs.daytour.value = this.currentDaytourId;
        if (this.$refs.title) this.$refs.title.value = this.currentImageTitle;
      });
    },

    quickSetInventory() {
      if (!this.form.id) {
        Swal.fire({
          icon: 'warning',
          title: 'Save First',
          text: 'Please save the package first before setting inventory.',
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      const qty = this.inventoryQty;
      if (!qty || qty < 1) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Quantity',
          text: 'Please enter a valid quantity.',
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      this.sendInventoryRequest({
        day_tour_id: this.form.id,
        quantity: qty,
        type: 'quickset'
      });
    },

    addDateRangeInventory() {
      if (!this.form.id) {
        Swal.fire({
          icon: 'warning',
          title: 'Save First',
          text: 'Please save the package first before setting inventory.',
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      if (!this.inventoryStartDate || !this.inventoryEndDate) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Dates',
          text: 'Please select start and end dates.',
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      if (this.inventoryStartDate > this.inventoryEndDate) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Date Range',
          text: 'Start date must be before end date.',
          confirmButtonColor: '#2563eb'
        });
        return;
      }
      this.sendInventoryRequest({
        day_tour_id: this.form.id,
        start_date: this.inventoryStartDate,
        end_date: this.inventoryEndDate,
        quantity: this.inventoryQtyRange,
        is_available: this.inventoryAvailable,
        type: 'range'
      });
    },

    sendInventoryRequest(data) {
      this.isLoading = true;
      const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
      let endpoint = '/admin/day-tour-inventory/store';
      
      if (data.type === 'quickset') {
        endpoint = '/admin/day-tour-inventory/quickset';
      }

      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      })
      .then(async (r) => {
        const raw = await r.text();
        let res = {};
        try {
          res = raw ? JSON.parse(raw) : {};
        } catch (e) {
          console.error('JSON parse error:', e, 'Raw response:', raw);
          throw new Error(`Server returned invalid response (${r.status}). Please try again.`);
        }

        if (!r.ok) {
          const msg = res.message
            || (res.errors ? Object.values(res.errors).flat().join('\n') : null)
            || `Request failed (${r.status})`;
          throw new Error(msg);
        }

        // Close modal on success
        this.showDayTourModal = false;
        
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: res.message || 'Inventory saved successfully.',
          confirmButtonColor: '#2563eb'
        }).then(() => {
          // Reset inventory fields after modal closes
          this.inventoryStartDate = '';
          this.inventoryEndDate = '';
          this.inventoryQtyRange = 3;
          this.inventoryQty = 3;
          // Reload page to show updated data
          window.location.reload();
        });
      })
      .catch(err => {
        console.error('Inventory request error:', err);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: err.message || 'Something went wrong. Please try again.',
          confirmButtonColor: '#dc2626'
        });
      })
      .finally(() => {
        this.isLoading = false;
      });
    },

    loadInventoryPreview() {
      if (!this.form.id) return;
      const previewDiv = document.getElementById('dayTourInventoryPreview');
      if (!previewDiv) return;

      previewDiv.innerHTML = '<span class="text-gray-600">Loading...</span>';

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 5000);

      fetch(`/api/day-tour-inventory/${this.form.id}`, { signal: controller.signal })
        .then(response => {
          clearTimeout(timeoutId);
          if (!response.ok) {
            throw new Error(`API returned ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (!data.items || data.items.length === 0) {
            previewDiv.innerHTML = '<span class="text-gray-500">No inventory set yet. Use Quick Set or Custom Date Range above.</span>';
            return;
          }
          let html = '<table class="w-full text-sm"><tr class="border-b"><th class="text-left py-1">Date</th><th class="text-center py-1">Qty</th><th class="text-center py-1">Status</th></tr>';
          data.items.slice(0, 30).forEach(inv => {
            const dateObj = new Date(inv.date);
            const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const status = inv.is_available ? '✅ Available' : '🚫 Blocked';
            html += `<tr class="border-b"><td class="py-1">${dateStr}</td><td class="text-center">${inv.min_quantity}</td><td class="text-center text-xs">${status}</td></tr>`;
          });
          html += '</table>';
          previewDiv.innerHTML = html;
        })
        .catch(err => {
          console.error(err);
          Swal.fire({ icon: 'error', title: 'Unexpected Error', text: 'Something went wrong.' });
        });
    },

    deleteDayTour(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the day tour package permanently!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('delete-daytour-' + id).submit();
        }
      });
    },

    deleteText(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the text content permanently!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('delete-text-' + id).submit();
        }
      });
    },

    async uploadImage() {
      this.isLoading = true;

      const form = document.getElementById('swiperUploadForm');
      const formData = new FormData(form);
      const csrf = document.querySelector('meta[name=csrf-token]').content;

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: formData
        });

        const data = await res.json();

        if (res.ok) {
          // Close modal immediately on success (like staycation)
          this.showModal = false;
          this.resetImageForm();
          Swal.fire({
            icon: 'success',
            title: this.editImageMode ? 'Updated!' : 'Uploaded!',
            text: data.message || (this.editImageMode ? 'Image updated successfully!' : 'Image uploaded successfully!'),
            confirmButtonColor: '#2563eb'
          }).then(() => {
            window.location.reload();
          });
        } else if (data.errors) {
          Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            html: Object.values(data.errors).flat().join('<br>')
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Upload failed.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Unexpected Error', text: 'Something went wrong.' });
      } finally {
        this.isLoading = false;
      }
    },

    async saveInfoTitle() {
      this.isLoading = true;
      const csrf = document.querySelector('meta[name=csrf-token]').content;

      try {
        const res = await fetch('{{ route("day-tour-texts.store") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({
            type: 'info_title',
            content: this.infoForm.title,
            id: this.infoForm.titleId
          })
        });

        const data = await res.json();

        if (res.ok) {
          this.infoForm.titleId = data.id;
          Swal.fire({ icon: 'success', title: 'Saved!', text: 'Title updated successfully.', confirmButtonColor: '#2563eb' });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save title.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
      } finally {
        this.isLoading = false;
      }
    },

    async saveInfoSubtitle() {
      this.isLoading = true;
      const csrf = document.querySelector('meta[name=csrf-token]').content;

      try {
        const res = await fetch('{{ route("day-tour-texts.store") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({
            type: 'info_subtitle',
            content: this.infoForm.subtitle,
            id: this.infoForm.subtitleId
          })
        });

        const data = await res.json();

        if (res.ok) {
          this.infoForm.subtitleId = data.id;
          Swal.fire({ icon: 'success', title: 'Saved!', text: 'Subtitle updated successfully.', confirmButtonColor: '#2563eb' });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save subtitle.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
      } finally {
        this.isLoading = false;
      }
    },

    async saveInfoItems() {
      this.isLoading = true;
      const csrf = document.querySelector('meta[name=csrf-token]').content;

      try {
        const res = await fetch('{{ route("day-tour-texts.store") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({
            type: 'info_items',
            content: JSON.stringify(this.infoForm.items),
            id: this.infoForm.itemsId
          })
        });

        const data = await res.json();

        if (res.ok) {
          this.infoForm.itemsId = data.id;
          Swal.fire({ icon: 'success', title: 'Saved!', text: 'Information items updated successfully.', confirmButtonColor: '#2563eb' });
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save items.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
      } finally {
        this.isLoading = false;
      }
    },

    addInfoItem() {
      if (!this.infoForm.newItem.text.trim()) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Please enter text for the information item.', confirmButtonColor: '#2563eb' });
        return;
      }
      this.infoForm.items.push({ ...this.infoForm.newItem });
      this.infoForm.newItem = { icon: 'bi-info-circle', text: '' };
      this.showAddItemModal = false;
    },

    confirmRemoveInfoItem(index) {
      Swal.fire({
        title: 'Remove this item?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, remove it!'
      }).then((result) => {
        if (result.isConfirmed) {
          this.infoForm.items.splice(index, 1);
        }
      });
    }
  };
}
</script>
@endsection

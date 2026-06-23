@extends('layouts.admin')

@section('title', 'What to Do Page CMS')

@section('content')

<div
    x-data="whatToDoManager()"
    x-init="init()"
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
    @php
        $contentTexts = collect($texts ?? [])->reject(function ($item) {
            $label = strtolower((string) ($item->label ?? $item->title ?? ''));
            return str_starts_with($label, 'packages_');
        })->values();
    @endphp

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
                @forelse($contentTexts as $text)
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

    <hr class="my-10 border-gray-300">

    <div class="mb-10 rounded-2xl border border-gray-200 bg-white/60 backdrop-blur p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Activities Page Copy</h2>
            </div>

            <button
                type="button"
                @click="saveActivitiesCopy()"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                Save Activities Copy
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div>
                <label for="packages_title" class="block font-semibold mb-2 text-gray-700">Section Title</label>
                <input
                    id="packages_title"
                    type="text"
                    x-model="activitiesCopy.title"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white"
                    placeholder="Day Packages">
            </div>

            <div>
                <label for="packages_intro" class="block font-semibold mb-2 text-gray-700">Section Intro</label>
                <textarea
                    id="packages_intro"
                    x-model="activitiesCopy.intro"
                    rows="4"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white"
                    placeholder="Start with our curated package options, then explore activities you can add to your stay."></textarea>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-3">Saved as <code>packages_title</code> and <code>packages_intro</code>.</p>
    </div>

    {{-- ============================================================= --}}
    {{-- MANAGE PACKAGES (EMBEDDED) --}}
    {{-- ============================================================= --}}
    <div x-data="packageManager()" class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Manage Packages</h2>
        <p class="text-gray-600 mb-6">Manage all vacation packages, bookings, and availability CalaiaPH offers</p>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Packages</h3>
                <p class="text-gray-500 text-sm mt-1">Create, edit, and manage packages</p>
            </div>
            <button
                @click="showPackageModal = true; resetForm()"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> New Package
            </button>
        </div>

        @if(($packages ?? collect())->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($packages as $package)
                    <div class="bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full" data-package-id="{{ $package->id }}">
                        @if($package->image)
                            <div class="w-full h-48 overflow-hidden">
                                <img src="{{ $package->image }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif

                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $package->name }}</h3>
                            <p class="text-gray-600 text-sm mb-3">{{ Str::limit((string)($package->description ?? ''), 100) }}</p>

                            <div class="mb-3">
                                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">
                                    Good for {{ $package->max_guests }} person{{ $package->max_guests > 1 ? 's' : '' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <p class="text-2xl font-bold text-gray-900">
                                    P{{ number_format($package->price, 0) }}
                                    <span class="text-sm font-normal text-gray-600">
                                        {{ $package->pricing_type === 'per_group' ? '/group' : '/head' }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $package->pricing_type === 'per_group' ? 'fixed price per group booking' : 'per head pricing, total depends on selected guests' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Duration: {{ $package->duration_label ?? (($package->duration_days ?? 1) . ' Day' . (($package->duration_days ?? 1) > 1 ? 's' : '')) }}</p>
                            </div>

                            <hr class="my-3 border-gray-200">

                            @if($package->inclusions && is_array($package->inclusions) && count($package->inclusions) > 0)
                                <div class="mb-3 flex flex-wrap gap-2">
                                    @foreach($package->inclusions as $inclusion)
                                        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-full font-medium">{{ $inclusion }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex justify-between items-center pt-3 mt-auto border-t border-gray-200">
                                <span class="text-xs text-gray-500">{{ optional($package->created_at)->format('Y-m-d') }}</span>
                                <div class="flex items-center space-x-4">
                                    <button class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-sm" @click="editPackage({{ $package->id }})">Edit</button>
                                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-sm" @click="deletePackage({{ $package->id }}, '{{ route('admin.packages.destroy', $package->id) }}')">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Packages Yet</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first package</p>
                <button @click="showPackageModal = true; resetForm()" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Create Package
                </button>
            </div>
        @endif

        <div
            x-show="showPackageModal"
            class="fixed inset-0 z-1000 bg-black/50 backdrop-blur-sm px-4 py-6 overflow-y-auto"
            x-transition.opacity
            @click.self="showPackageModal = false"
            @keydown.escape.window="showPackageModal = false"
        >
            <div class="min-h-full flex items-center justify-center">
                <div class="bg-white/95 rounded-2xl shadow-xl w-full max-w-2xl border border-gray-200 max-h-[88vh] flex flex-col" @click.stop>
                        <div class="flex justify-between items-center p-6 border-b border-gray-200">
                            <h3 class="text-2xl font-bold text-gray-900" x-text="isPackageEditMode ? 'Edit Package' : 'Create New Package'"></h3>
                            <button @click="showPackageModal = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6">
                            <form @submit.prevent="savePackage" class="space-y-5" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" x-model="form.id" name="id" />

                                <div>
                                    <label for="pkg_name" class="block font-semibold mb-2 text-gray-700">Package Name *</label>
                                    <input id="pkg_name" type="text" x-model="form.name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                </div>

                                <div>
                                    <label for="pkg_description" class="block font-semibold mb-2 text-gray-700">Description *</label>
                                    <textarea id="pkg_description" x-model="form.description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label for="pkg_price" class="block font-semibold mb-2 text-gray-700">Price (P) *</label>
                                        <input id="pkg_price" type="number" x-model="form.price" step="100" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                    </div>
                                    <div>
                                        <label for="pkg_duration_value" class="block font-semibold mb-2 text-gray-700">Duration *</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input id="pkg_duration_value" type="number" x-model="form.duration_value" min="1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                            <select id="pkg_duration_unit" x-model="form.duration_unit" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                                <option value="minutes">Minute(s)</option>
                                                <option value="hours">Hour(s)</option>
                                                <option value="days">Day(s)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="pkg_guests" class="block font-semibold mb-2 text-gray-700" x-text="form.pricing_type === 'per_group' ? 'Max Guests per Group *' : 'Guest Capacity *'"></label>
                                        <input id="pkg_guests" type="number" x-model="form.max_guests" min="1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                        <p class="text-xs text-gray-500 mt-1" x-text="form.pricing_type === 'per_group' ? 'Maximum people allowed in one group booking.' : 'Maximum people allowed in one booking (price is per head).'"></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="pkg_pricing_type" class="block font-semibold mb-2 text-gray-700">Pricing Type *</label>
                                        <select id="pkg_pricing_type" x-model="form.pricing_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                            <option value="per_pax">Per Head</option>
                                            <option value="per_group">Per Group</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="pkg_groups_per_day" class="block font-semibold mb-2 text-gray-700">Max Groups/Day *</label>
                                        <input id="pkg_groups_per_day" type="number" x-model="form.max_groups_per_day" min="1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="pkg_image" class="block font-semibold mb-2 text-gray-700">Package Image</label>
                                    <input id="pkg_image" type="file" x-ref="imageFile" accept="image/*" class="w-full border border-gray-300 rounded-xl px-4 py-2.5"
                                        @change="
                                            const file = $el.files[0];
                                            if (file) {
                                                const reader = new FileReader();
                                                reader.onload = (e) => form.imagePreview = e.target.result;
                                                reader.readAsDataURL(file);
                                            }
                                        ">
                                    <template x-if="form.imagePreview">
                                        <div class="relative inline-block mt-3">
                                            <img :src="form.imagePreview" alt="Preview" class="h-32 rounded-lg border">
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <span class="block font-semibold mb-2 text-gray-700">Inclusions</span>
                                    <div class="flex gap-2 mb-3">
                                        <input id="newInclusion" type="text" placeholder="e.g., Lunch, ATV Ride, Guide..." @keyup.enter="addInclusion()" class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm">
                                        <button type="button" @click="addInclusion()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm">Add</button>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3 space-y-2" x-show="form.inclusionsList.length > 0">
                                        <template x-for="(item, idx) in form.inclusionsList" :key="idx">
                                            <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-3 py-2">
                                                <span x-text="item" class="text-sm text-gray-700"></span>
                                                <button type="button" @click="removeInclusion(idx)" class="text-red-600 font-bold">x</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <span class="block font-semibold mb-2 text-gray-700">Available Sessions</span>
                                    <p class="text-xs text-gray-500 mb-3">Control the booking time slots shown on the Activities page.</p>
                                    <div class="bg-gray-50 rounded-xl p-3 space-y-3">
                                        <template x-for="(session, idx) in form.sessions" :key="session.name">
                                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                                <label class="flex items-center gap-2 cursor-pointer mb-2">
                                                    <input type="checkbox" x-model="session.enabled" class="w-4 h-4 rounded border-gray-300 text-blue-600">
                                                    <span class="font-medium text-sm text-gray-800" x-text="session.name"></span>
                                                </label>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" :class="!session.enabled ? 'opacity-50' : ''">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Start</label>
                                                        <input type="time" x-model="session.start" :disabled="!session.enabled" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm disabled:bg-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">End</label>
                                                        <input type="time" x-model="session.end" :disabled="!session.enabled" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm disabled:bg-gray-100">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <input type="checkbox" id="pkg_active" x-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-blue-600">
                                    <label for="pkg_active" class="font-medium text-gray-700">Active Package</label>
                                </div>

                                <div x-show="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-xl">
                                    <template x-for="error in errors" :key="error">
                                        <div x-text="error" class="text-red-800 text-sm"></div>
                                    </template>
                                </div>
                            </form>
                        </div>

                        <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                            <button type="button" @click="showPackageModal = false" class="px-6 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-100">Cancel</button>
                            <button
                                type="button"
                                @click="savePackage()"
                                :disabled="isLoading"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
                                x-text="isLoading ? (isPackageEditMode ? 'Updating...' : 'Creating...') : (isPackageEditMode ? 'Update Package' : 'Create Package')">
                            </button>
                        </div>
                </div>
            </div>
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
function packageManager() {
    return {
        isLoading: false,
        showPackageModal: false,
        isPackageEditMode: false,
        errors: [],
        packages: @js(($packages ?? collect())->all()),
        form: {
            id: null,
            name: '',
            description: '',
            price: '',
            duration_value: '',
            duration_unit: 'hours',
            max_guests: '',
            max_groups_per_day: '',
            pricing_type: 'per_pax',
            imagePreview: null,
            inclusionsList: [],
            sessions: [],
            is_active: true
        },

        defaultSessions() {
            return [
                { name: 'Morning', start: '08:00', end: '11:00', enabled: true },
                { name: 'Afternoon', start: '12:00', end: '15:00', enabled: true },
                { name: 'Late Session', start: '15:30', end: '18:30', enabled: true }
            ];
        },

        parseSessionsForForm(sourceSessions) {
            const defaults = this.defaultSessions();
            if (!Array.isArray(sourceSessions) || sourceSessions.length === 0) {
                return defaults;
            }

            const normalized = sourceSessions
                .filter(s => s && s.name)
                .map(s => ({
                    name: String(s.name || '').trim(),
                    start: String(s.start || '').trim(),
                    end: String(s.end || '').trim(),
                }));

            return defaults.map(def => {
                const match = normalized.find(s => s.name.toLowerCase() === def.name.toLowerCase());
                if (!match) {
                    return { ...def, enabled: false };
                }
                return {
                    ...def,
                    start: match.start || def.start,
                    end: match.end || def.end,
                    enabled: true
                };
            });
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                description: '',
                price: '',
                duration_value: '',
                duration_unit: 'hours',
                max_guests: '',
                max_groups_per_day: '',
                pricing_type: 'per_pax',
                imagePreview: null,
                inclusionsList: [],
                sessions: this.defaultSessions(),
                is_active: true
            };
            this.isPackageEditMode = false;
            this.errors = [];
            if (this.$refs.imageFile) this.$refs.imageFile.value = '';
            const input = document.getElementById('newInclusion');
            if (input) input.value = '';
        },

        addInclusion() {
            const input = document.getElementById('newInclusion');
            if (!input) return;
            const value = input.value.trim();
            if (!value) return;
            if (this.form.inclusionsList.includes(value)) return;
            this.form.inclusionsList.push(value);
            input.value = '';
        },

        removeInclusion(idx) {
            this.form.inclusionsList.splice(idx, 1);
        },

        editPackage(id) {
            this.isLoading = true;
            this.errors = [];

            fetch(`/admin/packages/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.form.id = data.id;
                this.form.name = data.name || '';
                this.form.description = data.description || '';
                this.form.price = String(parseFloat(data.price) || 0);
                this.form.duration_value = String(parseInt(data.duration_value) || parseInt(data.duration_days) || 1);
                this.form.duration_unit = data.duration_unit || 'days';
                this.form.max_guests = String(parseInt(data.max_guests) || 1);
                this.form.max_groups_per_day = String(parseInt(data.max_groups_per_day) || 1);
                this.form.pricing_type = data.pricing_type || 'per_pax';
                this.form.imagePreview = data.image || null;
                this.form.inclusionsList = Array.isArray(data.inclusions) ? data.inclusions : [];
                this.form.sessions = this.parseSessionsForForm(data.sessions);
                this.form.is_active = Boolean(data.is_active);
                if (this.$refs.imageFile) this.$refs.imageFile.value = '';
                this.isPackageEditMode = true;
                this.$nextTick(() => {
                    this.showPackageModal = true;
                });
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load package' });
            })
            .finally(() => {
                this.isLoading = false;
            });
        },

        deletePackage(id, route) {
            Swal.fire({
                title: 'Delete Package?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const csrf = document.querySelector('meta[name=csrf-token]').content;
                this.isLoading = true;

                fetch(route, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then((data) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Package deleted successfully.'
                    }).then(() => window.location.reload());
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Delete Failed', text: 'Failed to delete package' });
                })
                .finally(() => {
                    this.isLoading = false;
                });
            });
        },

        async savePackage() {
            this.isLoading = true;
            this.errors = [];

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            formData.append('name', this.form.name);
            formData.append('description', this.form.description);
            formData.append('price', Number(this.form.price || 0));
            formData.append('duration_value', Number(this.form.duration_value || 1));
            formData.append('duration_unit', this.form.duration_unit || 'hours');
            formData.append('max_guests', Number(this.form.max_guests || 1));
            formData.append('max_groups_per_day', Number(this.form.max_groups_per_day || 1));
            formData.append('pricing_type', this.form.pricing_type || 'per_pax');
            formData.append('inclusions', JSON.stringify(this.form.inclusionsList || []));

            const selectedSessions = (this.form.sessions || [])
                .filter(s => s.enabled)
                .map(s => ({
                    name: s.name,
                    start: s.start,
                    end: s.end
                }));

            if (selectedSessions.length === 0) {
                this.errors = ['Please enable at least one available session.'];
                this.isLoading = false;
                return;
            }

            if (selectedSessions.some(s => !s.start || !s.end)) {
                this.errors = ['Please complete start and end time for all enabled sessions.'];
                this.isLoading = false;
                return;
            }

            formData.append('sessions', JSON.stringify(selectedSessions));
            formData.append('is_active', this.form.is_active ? 1 : 0);

            if (this.$refs.imageFile && this.$refs.imageFile.files.length > 0) {
                formData.append('image', this.$refs.imageFile.files[0]);
            }

            let url = '{{ route('admin.packages.store') }}';
            if (this.isPackageEditMode && this.form.id) {
                url = `/admin/packages/${this.form.id}`;
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
                const raw = await res.text();
                let data = {};
                try {
                    data = raw ? JSON.parse(raw) : {};
                } catch (_) {
                    data = {};
                }

                if (res.ok) {
                    this.showPackageModal = false;
                    await Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: this.isPackageEditMode ? 'Package updated' : 'Package created',
                        text: data.message || 'Saved successfully.',
                        showConfirmButton: false,
                        timer: 1200,
                        timerProgressBar: true
                    });
                    window.location.reload();
                } else if (data.errors) {
                    this.errors = Object.values(data.errors).flat();
                } else {
                    this.errors = [data.message || 'Unable to save package.'];
                }
            } catch (e) {
                this.errors = ['An unexpected error occurred. Please try again.'];
            } finally {
                this.isLoading = false;
            }
        }
    };
}

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
        activitiesCopy: {
            title: '',
            intro: '',
        },
        form: { id: null, title: '', content: '' },

        init() {
            this.activitiesCopy = {
                title: this.getTextValue('packages_title', 'Day Packages'),
                intro: this.getTextValue('packages_intro', 'Start with our curated package options, then explore activities you can add to your stay.'),
            };
        },

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

        getTextValue(label, fallback = '') {
            const text = this.texts.find(t => ((t.label || t.title || '').toLowerCase() === label.toLowerCase()));
            const value = (text?.text ?? text?.content ?? '').trim();
            return value ? value : fallback;
        },

        getTextEntry(label) {
            return this.texts.find(t => ((t.label || t.title || '').toLowerCase() === label.toLowerCase())) || null;
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
                this.form = {
                    id: text.id,
                    title: text.label ?? text.title ?? '',
                    content: text.text ?? text.content ?? ''
                };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        async saveActivitiesCopy() {
            this.isLoading = true;

            try {
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const formData = new FormData();
                formData.append('page_title', this.activitiesCopy.title ?? '');
                formData.append('page_intro', this.activitiesCopy.intro ?? '');

                const res = await fetch('{{ route('admin.do.copy.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const raw = await res.text();
                let data = {};
                try {
                    data = raw ? JSON.parse(raw) : {};
                } catch (_) {
                    data = {};
                }

                if (!res.ok) {
                    const message = data.errors
                        ? Object.values(data.errors).flat().join('<br>')
                        : (data.message || 'Failed to save activities copy.');
                    throw new Error(message);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message || 'Activities page copy updated successfully.',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error?.message || 'Failed to save activities copy.'
                });
            } finally {
                this.isLoading = false;
            }
        },

        async upsertTextEntry(label, content) {
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const entry = this.getTextEntry(label);
            const formData = new FormData();
            formData.append('title', label);
            formData.append('content', content ?? '');

            let url = '{{ route('do-texts.store') }}';
            if (entry?.id) {
                url = `/admin/do-texts/${entry.id}`;
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

            const raw = await res.text();
            let data = {};
            try {
                data = raw ? JSON.parse(raw) : {};
            } catch (_) {
                data = {};
            }

            if (!res.ok) {
                const message = data.errors
                    ? Object.values(data.errors).flat().join('<br>')
                    : (data.message || 'Unable to save text content.');
                throw new Error(message);
            }

            return data;
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

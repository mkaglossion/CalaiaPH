@props([
    'title' => 'Page Information',
    'description' => 'Manage the image slider and content displayed on this page.',
    'images' => [],
    'texts' => [],
    'imageStoreRoute' => '',
    'imageDestroyRoute' => '',
    'imageReorderRoute' => '',
    'textStoreRoute' => '',
    'textDestroyRoute' => '',
    'pageCopyEnabled' => false,
    'pageCopyFields' => [],
    'pageCopyMeta' => [],
    'pageCopyStoreRoute' => '',
    'extraSections' => null,
])

<div
    x-data="cmsManager({
        images: @js($images),
        texts: @js($texts),
        imageStoreRoute: '{{ $imageStoreRoute }}',
        imageDestroyRoute: '{{ $imageDestroyRoute }}',
        imageReorderRoute: '{{ $imageReorderRoute }}',
        textStoreRoute: '{{ $textStoreRoute }}',
        textDestroyRoute: '{{ $textDestroyRoute }}',
    })"
    x-init="initialize()"
    class="p-4 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
    <h1 class="text-3xl md:text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        {{ $title }}
    </h1>
    <p class="text-gray-600 mb-10">{{ $description }}</p>

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="mb-10 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 md:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Swiper Images</h2>
                <p class="text-gray-500 text-sm">Photos that appear inside the page carousel.</p>
            </div>

            <x-admin.action-button variant="primary" class="px-4 py-2 text-sm font-semibold" @click="showModal = true; resetImageForm()">
                + Add Image
            </x-admin.action-button>
        </div>

        {{-- Cards Grid --}}
        <div x-ref="imageGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="image in images" :key="image.id">
                <div
                    data-cms-image-card
                    :data-cms-image-id="image.id"
                    class="tilt-card bg-white/80 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative"
                    :class="canReorder ? 'cursor-grab active:cursor-grabbing' : ''"
                >
                    <div class="absolute right-4 top-4 z-10 flex items-center gap-2">
                        <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-gray-500 shadow-sm"
                              x-text="`#${Number(image.sort_order || 0)}`"></span>
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
                        <button class="text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition"
                            @click="editImage(image.id, image.title || '')">Edit</button>
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
            <button @click="selectAll" class="px-3 py-1.5 bg-gray-500 text-white rounded-lg text-sm hover:bg-gray-600">
                Select All
            </button>
            <button @click="deselectAll" class="px-3 py-1.5 bg-gray-400 text-white rounded-lg text-sm hover:bg-gray-500">
                Deselect All
            </button>
            <button @click="bulkDelete" class="px-4 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                Delete Selected
            </button>
        </div>
    </div>

    <hr class="my-14 border-gray-300">

    {{-- ============================================================= --}}
    {{-- TEXT CONTENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="mb-10 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 md:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Text Content</h2>
                <p class="text-gray-500 text-sm">Paragraphs and captions displayed on this page.</p>
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
                            <td class="px-6 py-4 font-medium text-gray-900" x-text="text.title || text.label || 'No Label'"></td>

                            <td class="px-6 py-4 text-gray-600 text-sm" x-text="truncateText(text.text || text.content || '', 150)"></td>

                            <td class="px-6 py-4 text-right space-x-4">
                                <button
                                    class="text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition"
                                    @click="editText(text.id)">Edit</button>
                                <button
                                    class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                    @click="deleteText(text.id)">Delete</button>
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
    {{-- PAGE COPY SECTION (optional) --}}
    {{-- ============================================================= --}}
    @if($pageCopyEnabled)
        @php
            $pageCopyTransportMap = [
                'label' => 'page_label',
                'title' => 'page_title',
                'intro' => 'page_intro',
            ];
            $pageCopySectionLabels = [
                'page' => 'Page Copy',
                'cta' => 'CTA Copy',
                'messhall' => 'Mess Hall Copy',
                'cook' => 'Cook Your Own Copy',
            ];
            $pageCopySectionDescriptions = [
                'page' => 'Main heading, intro, and hero-facing copy shown at the top of the public page.',
                'cta' => 'Call-to-action text, supporting message, and button/link labels.',
                'messhall' => 'Content used on the Mess Hall dining card and related highlights.',
                'cook' => 'Content used on the Cook Your Own dining card and related highlights.',
            ];
            $resolvedCopy = $pageCopyFields;
            $pageCopyEntries = collect($resolvedCopy)->map(function ($value, $key) use ($pageCopyMeta, $pageCopyTransportMap) {
                $meta = $pageCopyMeta[$key] ?? [];
                $defaultLabel = str($key)->replace('_', ' ')->title()->toString();
                $defaultType = str_contains($key, 'url') ? 'url' : 'text';
                $section = $meta['section'] ?? match (true) {
                    str_starts_with($key, 'cta_') => 'cta',
                    str_starts_with($key, 'messhall_') => 'messhall',
                    str_starts_with($key, 'cook_') => 'cook',
                    default => 'page',
                };
                $columnClass = $meta['columnClass'] ?? match (true) {
                    ($meta['type'] ?? $defaultType) === 'textarea' => 'lg:col-span-2',
                    ($meta['type'] ?? $defaultType) === 'url' => 'lg:col-span-2',
                    in_array($key, ['intro'], true) => 'lg:col-span-2',
                    default => '',
                };

                return [
                    'key' => $key,
                    'transportKey' => $pageCopyTransportMap[$key] ?? $key,
                    'value' => $value,
                    'label' => $meta['label'] ?? $defaultLabel,
                    'type' => $meta['type'] ?? $defaultType,
                    'rows' => $meta['rows'] ?? 4,
                    'placeholder' => $meta['placeholder'] ?? '',
                    'section' => $section,
                    'columnClass' => $columnClass,
                ];
            })->values();
            $pageCopySections = $pageCopyEntries
                ->groupBy('section')
                ->map(function ($entries, $section) use ($pageCopySectionLabels, $pageCopySectionDescriptions) {
                    return [
                        'key' => $section,
                        'label' => $pageCopySectionLabels[$section] ?? str($section)->replace('_', ' ')->title()->toString(),
                        'description' => $pageCopySectionDescriptions[$section] ?? 'Update the copy used for this section of the public page.',
                        'entries' => $entries->values(),
                    ];
                })
                ->values();
        @endphp

    <hr class="my-10 border-gray-300">

    <div x-data="{ pageCopy: @js($resolvedCopy), pageCopyEntries: @js($pageCopyEntries), pageCopySections: @js($pageCopySections), loading: false }" class="mb-8 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-4 md:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Page Copy</h2>
                <p class="text-gray-500 text-sm">Controls the section heading and CTA copy on the public page.</p>
            </div>

            <button
                type="button"
                @click="(async () => {
                    loading = true;
                    try {
                        const csrf = document.querySelector('meta[name=csrf-token]').content;
                        const formData = new FormData();
                        pageCopyEntries.forEach((field) => {
                            formData.append(field.transportKey, pageCopy[field.key] ?? '');
                        });

                        const response = await fetch('{{ $pageCopyStoreRoute }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: formData
                        });

                        const data = await response.json().catch(() => ({}));
                        if (response.ok) {
                            Swal.fire({ icon: 'success', title: 'Saved!', text: 'Page copy updated.', confirmButtonColor: '#2563eb' });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save.' });
                        }
                    } catch (_) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
                    } finally {
                        loading = false;
                    }
                })()"
                :disabled="loading"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-[#8f5d54] text-white text-sm font-semibold shadow hover:bg-[#7a4d46] hover:shadow-lg transition disabled:opacity-60"
                x-text="loading ? 'Saving...' : 'Save Page Copy'">
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="section in pageCopySections" :key="section.key">
                <section class="rounded-xl border border-gray-200/80 bg-white/80 p-3 md:p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-500" x-text="section.label"></h3>
                        <p class="mt-1 text-xs text-gray-500" x-text="section.description"></p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <template x-for="field in section.entries" :key="field.key">
                            <div :class="field.columnClass || ''">
                                <label class="block text-sm font-semibold mb-1.5 text-gray-700" :for="'page-copy-' + field.key" x-text="field.label"></label>
                                <template x-if="field.type === 'textarea'">
                                    <textarea
                                        :id="'page-copy-' + field.key"
                                        x-model="pageCopy[field.key]"
                                        :rows="field.rows || 4"
                                        :placeholder="field.placeholder || ''"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white"
                                    ></textarea>
                                </template>
                                <template x-if="field.type !== 'textarea'">
                                    <input
                                        :id="'page-copy-' + field.key"
                                        :type="field.type || 'text'"
                                        x-model="pageCopy[field.key]"
                                        :placeholder="field.placeholder || ''"
                                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white"
                                    >
                                </template>
                            </div>
                        </template>
                    </div>
                </section>
            </template>
        </div>
        <p class="mt-3 text-xs text-gray-500">Saved using the page copy field keys configured for this page.</p>
    </div>
    @endif

    {{-- ============================================================= --}}
    {{-- EXTRA SECTIONS (slot for page-specific content like packages) --}}
    {{-- ============================================================= --}}
    @if($extraSections)
        <hr class="my-10 border-gray-300">
        {!! $extraSections !!}
    @endif

    {{-- ============================================================= --}}
    {{-- MODALS --}}
    {{-- ============================================================= --}}

    {{-- Swiper Modal --}}
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
        x-cloak
    >
        <div @click.away="showModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-gray-200">
            <button 
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload Swiper Image'"></h3>

            <form class="space-y-5" @submit.prevent="uploadImage">
                @csrf

                <div>
                    <label for="cms-swiper-title" class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input id="cms-swiper-title" type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full">
                </div>

                <div>
                    <label for="cms-swiper-image" class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input id="cms-swiper-image" type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isLoading" class="px-4 py-2 rounded-xl bg-[#8f5d54] text-white hover:bg-[#7a4d46] shadow transition disabled:opacity-60" x-text="isLoading ? (editImageMode ? 'Updating...' : 'Uploading...') : (editImageMode ? 'Update' : 'Upload')"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Text Modal --}}
    <div x-show="showTextModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity x-cloak>
        <div @click.away="showTextModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-7 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form class="space-y-5" @submit.prevent="saveText">
                @csrf

                <div>
                    <label for="cms-text-label" class="block font-medium mb-1 text-gray-700">Label</label>
                    <input id="cms-text-label" type="text" name="title" x-model="form.title" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label for="cms-text-content" class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea id="cms-text-content" x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter text..." required></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showTextModal = false" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" :disabled="isLoading" class="px-5 py-2 rounded-xl bg-[#8f5d54] text-white hover:bg-[#7a4d46] shadow transition disabled:opacity-60" x-text="isLoading ? (editMode ? 'Updating...' : 'Adding...') : (editMode ? 'Update' : 'Add')"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- GLOBAL LOADING OVERLAY --}}
    <div x-show="isLoading" x-transition.opacity x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-9999">
        <div class="flex flex-col items-center space-y-3">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-30" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                <path class="opacity-90" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white text-lg font-medium">Processing...</span>
        </div>
    </div>
</div>

@push('styles')
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
@endpush

@props([
    'title' => 'Page Information',
    'description' => 'Manage the image slider and content displayed on this page.',
    'images' => [],
    'texts' => [],
    'imageStoreRoute' => '',
    'imageDestroyRoute' => '',
    'textStoreRoute' => '',
    'textDestroyRoute' => '',
    'pageCopyEnabled' => false,
    'pageCopyFields' => [],
    'pageCopyStoreRoute' => '',
    'extraSections' => null,
])

@php
    $imageIds = $images->pluck('id')->toArray();
@endphp

<div
    x-data="cmsManager({
        images: @js($images),
        texts: @js($texts),
        imageStoreRoute: '{{ $imageStoreRoute }}',
        imageDestroyRoute: '{{ $imageDestroyRoute }}',
        textStoreRoute: '{{ $textStoreRoute }}',
        textDestroyRoute: '{{ $textDestroyRoute }}',
        imageIds: @js($imageIds)
    })"
    class="p-4 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
    <h1 class="text-3xl md:text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        {{ $title }}
    </h1>
    <p class="text-gray-600 mb-10">{{ $description }}</p>

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Swiper Images</h2>
            <p class="text-gray-500 text-sm">Photos that appear inside the page carousel.</p>
        </div>

        <button 
            @click="showModal = true; resetImageForm()"
            class="px-4 py-2 rounded-xl bg-[#8f5d54] text-white font-medium shadow hover:bg-[#7a4d46] hover:shadow-lg transition">
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
                    @if(str_contains($img->image_path ?? $img, '/video/'))
                        <video src="{{ \App\Helpers\ImageHelper::src($img->image_path ?? $img) }}" class="w-full h-48 object-cover transition hover:scale-105" muted></video>
                    @else
                        <img src="{{ \App\Helpers\ImageHelper::src($img->image_path ?? $img) }}" class="w-full h-48 object-cover transition hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                    @endif
                </div>

                <div class="mt-4">
                    <h3 class="font-semibold text-lg text-gray-900">{{ $img->title ?: 'Untitled' }}</h3>
                    <p class="text-gray-500 text-sm">
                        Uploaded: {{ $img->created_at?->format('Y-m-d') ?? 'N/A' }}
                    </p>
                </div>

                <div class="mt-5 flex justify-end space-x-3">
                    <button class="text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition"
                        @click="editImage({{ $img->id }}, '{{ $img->title }}')">Edit</button>
                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                        @click="deleteImage({{ $img->id }})">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-full">No images uploaded yet.</p>
        @endforelse
    </div>

    {{-- Bulk Actions --}}
    <div x-show="selectedImages.length > 0" class="mt-6 flex items-center space-x-4" x-cloak>
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
            <p class="text-gray-500 text-sm">Paragraphs and captions displayed on this page.</p>
        </div>

        <button 
            @click="showTextModal = true; resetForm()"
            class="px-4 py-2 rounded-xl bg-[#8f5d54] text-white font-medium shadow hover:bg-[#7a4d46] hover:shadow-lg transition">
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
                            {{ $text->title ?? $text->label ?? 'No Label' }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ Str::limit($text->text ?? $text->content ?? '', 150) }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-4">
                            <button 
                                class="text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition"
                                @click="editText({{ $text->id }})">Edit</button>
                            <button 
                                class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                @click="deleteText({{ $text->id }})">Delete</button>
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

    {{-- ============================================================= --}}
    {{-- PAGE COPY SECTION (optional) --}}
    {{-- ============================================================= --}}
    @if($pageCopyEnabled)
        @php
            $pageCopyDefaults = [
                'label' => '',
                'title' => '',
                'intro' => '',
            ];
            $resolvedCopy = array_merge($pageCopyDefaults, $pageCopyFields);
        @endphp

        <hr class="my-10 border-gray-300">

    <div x-data="{ pageCopy: @js($resolvedCopy), loading: false }" class="mb-10 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Page Copy</h2>
                <p class="text-gray-500 text-sm">Controls the section heading and intro on the public page.</p>
            </div>

            <button
                type="button"
                @click="(async () => {
                    loading = true;
                    try {
                        const csrf = document.querySelector('meta[name=csrf-token]').content;
                        const formData = new FormData();
                        formData.append('page_label', pageCopy.label ?? '');
                        formData.append('page_title', pageCopy.title ?? '');
                        formData.append('page_intro', pageCopy.intro ?? '');

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
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-[#8f5d54] text-white font-medium shadow hover:bg-[#7a4d46] hover:shadow-lg transition disabled:opacity-60"
                x-text="loading ? 'Saving...' : 'Save Page Copy'">
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @if(isset($pageCopyFields['label']))
            <div>
                <label class="block font-semibold mb-2 text-gray-700">Section Label</label>
                <input type="text" x-model="pageCopy.label" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white">
            </div>
            @endif
            @if(isset($pageCopyFields['title']))
            <div>
                <label class="block font-semibold mb-2 text-gray-700">Section Title</label>
                <input type="text" x-model="pageCopy.title" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white">
            </div>
            @endif
            @if(isset($pageCopyFields['intro']))
            <div class="lg:col-span-2">
                <label class="block font-semibold mb-2 text-gray-700">Section Intro</label>
                <textarea x-model="pageCopy.intro" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white"></textarea>
            </div>
            @endif
        </div>
        <p class="mt-4 text-xs text-gray-500">Saved as <code>page_label</code>, <code>page_title</code>, and <code>page_intro</code>.</p>
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
                    <label class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#8f5d54] text-white hover:bg-[#7a4d46] shadow transition" x-text="editImageMode ? 'Update' : 'Upload'">
                    </button>
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
                    <label class="block font-medium mb-1 text-gray-700">Label</label>
                    <input type="text" name="title" x-model="form.title" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter text..." required></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showTextModal = false" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#8f5d54] text-white hover:bg-[#7a4d46] shadow transition" x-text="editMode ? 'Update' : 'Add'"></button>
                </div>
            </form>
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

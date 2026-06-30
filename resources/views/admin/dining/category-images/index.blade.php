@extends('layouts.admin')

@section('title', 'Dining Category Images')

@section('content')

<div x-data="diningImageManager()" x-init="init()" class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">
    
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Dining Page Images
    </h1>
    <p class="text-gray-600 mb-10">Manage hero category images and Learn More modal swiper images for the Dining page.</p>

    <div class="mb-10 rounded-2xl border border-gray-200 bg-white/60 backdrop-blur p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dining Page Copy</h2>
            </div>

            <button
                type="button"
                @click="saveDiningCopy()"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                Save Dining Copy
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div>
                <label for="dining_page_title" class="block font-semibold mb-2 text-gray-700">Page Title</label>
                <input id="dining_page_title" type="text" x-model="diningCopy.pageTitle" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white" placeholder="Dining at CalaiaPH">
            </div>

            <div>
                <label for="dining_page_intro" class="block font-semibold mb-2 text-gray-700">Page Intro</label>
                <textarea id="dining_page_intro" x-model="diningCopy.pageIntro" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white" placeholder="Experience farm-to-table dining..."></textarea>
            </div>

            <div>
                <label for="cta_title" class="block font-semibold mb-2 text-gray-700">CTA Title</label>
                <input id="cta_title" type="text" x-model="diningCopy.ctaTitle" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white" placeholder="Ready to Dine With Us?">
            </div>

            <div>
                <label for="cta_intro" class="block font-semibold mb-2 text-gray-700">CTA Intro</label>
                <textarea id="cta_intro" x-model="diningCopy.ctaIntro" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white" placeholder="Message us to reserve your dining experience."></textarea>
            </div>

            <div>
                <label for="cta_button" class="block font-semibold mb-2 text-gray-700">CTA Button Text</label>
                <input id="cta_button" type="text" x-model="diningCopy.ctaButton" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white" placeholder="Make a Reservation on Messenger">
            </div>

            <div>
                <label for="cta_note" class="block font-semibold mb-2 text-gray-700">CTA Note</label>
                <textarea id="cta_note" x-model="diningCopy.ctaNote" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white" placeholder="Available for lunch and dinner reservations."></textarea>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- ============================================================= --}}
    {{-- HERO CATEGORY IMAGES (Top of /dining page) --}}
    {{-- ============================================================= --}}

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Hero Category Images</h2>
            <p class="text-gray-500 text-sm">Large hero images at the top of the Dining page (Messhall & Cook Your Own).</p>
        </div>

        <button
            type="button"
            @click="openHeroModal()"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Hero Category Image
        </button>
    </div>

    @php
        $resolveDiningText = function (string $label, string $fallback = '') use ($texts) {
            $match = collect($texts ?? [])->first(function ($item) use ($label) {
                $itemLabel = strtolower((string) ($item->label ?? $item->title ?? ''));
                return $itemLabel === strtolower($label);
            });

            $value = trim((string) ($match->text ?? $match->content ?? ''));
            return $value !== '' ? $value : $fallback;
        };

        $messhallCardTitle = $resolveDiningText('dining_messhall_title', 'Mess Hall Dining');
        $messhallCardIntro = $resolveDiningText('dining_messhall_intro', 'Our spacious and elegant mess hall is perfect for group dining and special occasions.');
        $cookYourOwnCardTitle = $resolveDiningText('dining_cook_title', 'Cook Your Own Experience');
        $cookYourOwnCardIntro = $resolveDiningText('dining_cook_intro', 'Enjoy cooking your own fresh ingredients harvested directly from our farm.');
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-10">
        <!-- Messhall Card -->
        <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-md hover:shadow-xl transition overflow-hidden flex flex-col h-full">
            <div class="overflow-hidden rounded-t-xl rounded-b-none mb-4 bg-linear-to-br from-gray-100 to-gray-200">
                @if(isset($diningImages['messhall']))
                    <img src="{{ \App\Helpers\ImageHelper::src($diningImages['messhall']->image_path) }}" alt="Messhall" class="w-full h-56 object-cover transition hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                @else
                    <div class="w-full h-56 flex items-center justify-center bg-gray-50">
                        <div class="text-center">
                            <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                            <span class="text-gray-500 text-sm">No image uploaded</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-5 flex-1 flex flex-col">
                <h3 class="font-bold text-xl text-gray-900 mb-2">{{ $messhallCardTitle }}</h3>
                <p class="text-gray-500 text-sm leading-6 mb-4">
                    {{ $messhallCardIntro }}
                </p>
                <p class="text-gray-500 text-sm mb-5">
                    @if(isset($diningImages['messhall']))
                        Updated: {{ $diningImages['messhall']->updated_at->format('M d, Y') }}
                    @else
                        No image uploaded yet
                    @endif
                </p>

                <div class="mt-auto pt-4 border-t border-gray-200 flex items-center justify-end gap-4">
                    <button
                        type="button"
                        @click="openHeroModal('messhall')"
                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-sm">
                        Edit
                    </button>

                    @if(isset($diningImages['messhall']))
                        <button
                            type="button"
                            @click="deleteImage('{{ $diningImages['messhall']->id }}', '{{ route('admin.dining.category-images.destroy', $diningImages['messhall']->id) }}')"
                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-sm">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cook Your Own Card -->
        <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-md hover:shadow-xl transition overflow-hidden flex flex-col h-full">
            <div class="overflow-hidden rounded-t-xl rounded-b-none mb-4 bg-linear-to-br from-gray-100 to-gray-200">
                @if(isset($diningImages['cook_your_own']))
                    <img src="{{ \App\Helpers\ImageHelper::src($diningImages['cook_your_own']->image_path) }}" alt="Cook Your Own" class="w-full h-56 object-cover transition hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                @else
                    <div class="w-full h-56 flex items-center justify-center bg-gray-50">
                        <div class="text-center">
                            <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                            <span class="text-gray-500 text-sm">No image uploaded</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-5 flex-1 flex flex-col">
                <h3 class="font-bold text-xl text-gray-900 mb-2">{{ $cookYourOwnCardTitle }}</h3>
                <p class="text-gray-500 text-sm leading-6 mb-4">
                    {{ $cookYourOwnCardIntro }}
                </p>
                <p class="text-gray-500 text-sm mb-5">
                    @if(isset($diningImages['cook_your_own']))
                        Updated: {{ $diningImages['cook_your_own']->updated_at->format('M d, Y') }}
                    @else
                        No image uploaded yet
                    @endif
                </p>

                <div class="mt-auto pt-4 border-t border-gray-200 flex items-center justify-end gap-4">
                    <button
                        type="button"
                        @click="openHeroModal('cook_your_own')"
                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-sm">
                        Edit
                    </button>

                    @if(isset($diningImages['cook_your_own']))
                        <button
                            type="button"
                            @click="deleteImage('{{ $diningImages['cook_your_own']->id }}', '{{ route('admin.dining.category-images.destroy', $diningImages['cook_your_own']->id) }}')"
                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-sm">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
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
                                <img src="{{ \App\Helpers\ImageHelper::src($img->image_path) }}" alt="{{ $img->title }}" class="w-16 h-10 sm:w-20 sm:h-12 object-cover rounded-xl" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
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
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 relative border border-gray-200">
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
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl w-[min(94vw,50rem)] max-h-[90vh] overflow-y-auto p-6 sm:p-8 relative border border-gray-200">
            <button 
                @click="showUploadModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editHeroMode ? 'Edit Hero Category Image' : 'Add Hero Category Image'"></h3>

            <form id="diningUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf

                <div>
                    <label for="category" class="block font-medium mb-1 text-gray-700">Category</label>
                    <select id="category" x-ref="heroCategory" x-model="selectedCategory" class="border rounded-xl p-3 w-full bg-white text-gray-700" required>
                        <option value="">Select category</option>
                        <option value="messhall">Messhall</option>
                        <option value="cook_your_own">Cook Your Own</option>
                    </select>
                </div>

                <template x-if="selectedCategory === 'messhall'">
                    <div class="space-y-5">
                        <div>
                            <label for="messhall_title" class="block font-medium mb-1 text-gray-700">Mess Hall Title</label>
                            <input id="messhall_title" type="text" x-model="diningCopy.messHallTitle" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Mess Hall Dining">
                        </div>

                        <div>
                            <label for="messhall_intro" class="block font-medium mb-1 text-gray-700">Mess Hall Intro</label>
                            <textarea id="messhall_intro" x-model="diningCopy.messHallIntro" rows="4" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Our spacious and elegant mess hall..."></textarea>
                        </div>
                    </div>
                </template>

                <template x-if="selectedCategory === 'cook_your_own'">
                    <div class="space-y-5">
                        <div>
                            <label for="cook_title" class="block font-medium mb-1 text-gray-700">Cook Your Own Title</label>
                            <input id="cook_title" type="text" x-model="diningCopy.cookTitle" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Cook Your Own Experience">
                        </div>

                        <div>
                            <label for="cook_intro" class="block font-medium mb-1 text-gray-700">Cook Your Own Intro</label>
                            <textarea id="cook_intro" x-model="diningCopy.cookIntro" rows="4" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enjoy cooking your own fresh ingredients..."></textarea>
                        </div>
                    </div>
                </template>

                <div>
                    <label for="category_image" class="block font-medium mb-1 text-gray-700">Upload Image *</label>
                    <input
                        type="file"
                        id="category_image"
                        x-ref="image"
                        name="image"
                        accept="image/*"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                        :required="!editHeroMode"
                        @change="
                            const file = $el.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => heroImagePreview = e.target.result;
                                reader.readAsDataURL(file);
                            }
                        "
                    >
                    <p class="text-xs text-gray-500 mt-2">Upload a hero image (JPG, PNG, GIF)</p>

                    <template x-if="heroImagePreview">
                        <div class="relative inline-block mt-3">
                            <img :src="heroImagePreview" alt="Preview" class="h-32 rounded-lg border object-cover">
                            <button
                                type="button"
                                @click="heroImagePreview = null; $refs.image.value = ''"
                                class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700"
                            >
                                ×
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="closeHeroModal()"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        <span x-text="editHeroMode ? 'Update' : 'Upload'"></span>
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
        editHeroMode: false,
        selectedCategory: '',
        heroImagePreview: null,
        editSwiperMode: false,
        currentSwiperId: null,
        texts: @js($texts),
        diningCopy: {
            pageTitle: '',
            pageIntro: '',
            messHallTitle: '',
            messHallIntro: '',
            cookTitle: '',
            cookIntro: '',
            ctaTitle: '',
            ctaIntro: '',
            ctaButton: '',
            ctaNote: '',
        },

        init() {
            this.diningCopy = {
                pageTitle: this.getTextValue('dining_page_title', 'Dining at CalaiaPH'),
                pageIntro: this.getTextValue('dining_page_intro', 'Experience farm-to-table dining in our elegant mess hall or enjoy cooking your own fresh ingredients straight from our farm. Perfect for celebrations, family gatherings, and intimate dinners.'),
                messHallTitle: this.getTextValue('dining_messhall_title', 'Mess Hall Dining'),
                messHallIntro: this.getTextValue('dining_messhall_intro', 'Our spacious and elegant mess hall is perfect for group dining and special occasions. Let our chefs prepare delicious farm-to-table meals using the freshest ingredients from our farm.'),
                cookTitle: this.getTextValue('dining_cook_title', 'Cook Your Own Experience'),
                cookIntro: this.getTextValue('dining_cook_intro', 'Enjoy cooking your own fresh ingredients harvested directly from our farm. Choose premium, locally sourced ingredients and create your own meal with guidance from our team.'),
                ctaTitle: this.getTextValue('dining_cta_title', 'Ready to Dine With Us?'),
                ctaIntro: this.getTextValue('dining_cta_intro', 'Message us to reserve your dining experience. We can accommodate lunch, dinner, group events, and special arrangements.'),
                ctaButton: this.getTextValue('dining_cta_button', 'Make a Reservation on Messenger'),
                ctaNote: this.getTextValue('dining_cta_note', 'Available for lunch and dinner reservations. Message us for inquiries.'),
            };
        },

        getTextValue(label, fallback = '') {
            const text = this.texts.find(item => {
                const itemLabel = (item.label || item.title || '').toLowerCase();
                return itemLabel === label.toLowerCase();
            });
            const value = (text?.text ?? text?.content ?? '').toString().trim();
            return value ? value : fallback;
        },

        async saveDiningCopy() {
            this.isLoading = true;

            try {
                await this.persistDiningCopy();

                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Dining page copy updated successfully.',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error?.message || 'Failed to save dining copy.'
                });
            } finally {
                this.isLoading = false;
            }
        },

        async persistDiningCopy() {
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const payload = {
                page_title: this.diningCopy.pageTitle,
                page_intro: this.diningCopy.pageIntro,
                messhall_title: this.diningCopy.messHallTitle,
                messhall_intro: this.diningCopy.messHallIntro,
                cook_title: this.diningCopy.cookTitle,
                cook_intro: this.diningCopy.cookIntro,
                cta_title: this.diningCopy.ctaTitle,
                cta_intro: this.diningCopy.ctaIntro,
                cta_button: this.diningCopy.ctaButton,
                cta_note: this.diningCopy.ctaNote,
            };

            const res = await fetch('{{ url('/admin/food/category-images/copy') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
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
                    : (data.message || 'Failed to save dining copy.');
                throw new Error(message);
            }

            return data;
        },

        openHeroModal(category = '') {
            this.selectedCategory = category || '';
            this.editHeroMode = Boolean(category);
            this.showUploadModal = true;
            const currentImage = category === 'messhall'
                ? @js(isset($diningImages['messhall']) ? \App\Helpers\ImageHelper::src($diningImages['messhall']->image_path) : null)
                : category === 'cook_your_own'
                    ? @js(isset($diningImages['cook_your_own']) ? \App\Helpers\ImageHelper::src($diningImages['cook_your_own']->image_path) : null)
                    : null;
            this.heroImagePreview = currentImage || null;
            this.$nextTick(() => {
                if (this.$refs.heroCategory) {
                    this.$refs.heroCategory.value = this.selectedCategory;
                }
                if (this.$refs.image && !this.heroImagePreview) {
                    this.$refs.image.value = '';
                }
            });
        },

        closeHeroModal() {
            this.showUploadModal = false;
            this.editHeroMode = false;
            this.selectedCategory = '';
            this.heroImagePreview = null;
            if (this.$refs.image) {
                this.$refs.image.value = '';
            }
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

            const imageInput = this.$refs.image;
            const hasImage = imageInput?.files?.[0];

            if (!this.selectedCategory) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Category Selected',
                    text: 'Please select a hero category first.'
                });
                this.isLoading = false;
                return;
            }

            if (!hasImage && !this.editHeroMode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Image Selected',
                    text: 'Please select an image before uploading.'
                });
                this.isLoading = false;
                return;
            }

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();
            formData.append('category', this.selectedCategory);
            if (this.selectedCategory === 'messhall') {
                formData.append('messhall_title', this.diningCopy.messHallTitle || '');
                formData.append('messhall_intro', this.diningCopy.messHallIntro || '');
            } else if (this.selectedCategory === 'cook_your_own') {
                formData.append('cook_title', this.diningCopy.cookTitle || '');
                formData.append('cook_intro', this.diningCopy.cookIntro || '');
            }

            if (hasImage) {
                formData.append('image', hasImage);
            }

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
                        title: this.editHeroMode ? "Updated!" : "Upload Complete",
                        text: this.editHeroMode ? "Hero section updated successfully!" : "Hero image uploaded successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.closeHeroModal();
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

@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')

<div
    x-data="{
        showModal: false,
        showTextModal: false,
        editMode: false,
        texts: @js($texts),
        form: { id: null, label: '', text: '' },

        resetForm() {
            this.form = { id: null, label: '', text: '' };
            this.editMode = false;
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form = { id: text.id, label: text.label, text: text.text };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        async saveText() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('label', this.form.label);
            formData.append('text', this.form.text);

            let url = '{{ route('day-tour-texts.store') }}';
            if (this.editMode && this.form.id) {
                url = `/admin/day-tour-texts/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Uploading...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Updated!' : 'Added!'));
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                }
            } catch (err) {
                alert('Error occurred.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        }
    }"
    class="p-8 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Day Tour Page Management
    </h1>
    <p class="text-gray-600 mb-10">Manage the image slider and content displayed on your Day Tour page.</p>

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
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition">
                <div class="overflow-hidden rounded-xl">
                    <img src="{{ $img->image_path }}" class="w-full h-48 object-cover transition hover:scale-105">
                </div>

                <div class="mt-4">
                    <h3 class="font-semibold text-lg text-gray-900">{{ $img->title ?: 'Untitled' }}</h3>
                    <p class="text-gray-500 text-sm">
                        Uploaded: {{ $img->created_at->format('Y-m-d') }}
                    </p>
                </div>

                <div class="mt-5 flex justify-end">
                    <form action="{{ route('day-tour-swiper-images.destroy', $img->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                            onclick="return confirm('Delete this image?')">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No images uploaded yet.</p>
        @endforelse
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

    {{-- Text Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        @forelse($texts as $text)
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-6 rounded-2xl shadow-md hover:shadow-xl transition relative">
                
                <h3 class="font-bold text-xl text-gray-900">{{ $text->label ?: 'No Label' }}</h3>

                <p class="text-gray-600 mt-4 text-sm leading-relaxed">
                    {{ Str::limit($text->text, 150) }}
                </p>

                <div class="flex justify-between mt-7">
                    <button 
                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                        @click="editText({{ $text->id }})">
                        Edit
                    </button>

                    <form action="{{ route('day-tour-texts.destroy', $text->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button 
                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                            onclick="return confirm('Delete this text?')">
                            Delete
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <p class="text-gray-500">No text content yet.</p>
        @endforelse
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

            <h3 class="text-xl font-bold mb-4 text-gray-900">Upload Swiper Image</h3>

            <form id="swiperUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Upload Image</label>
                    <input type="file" x-ref="image" name="image" accept="image/*" class="border rounded-xl p-3 w-full" required>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button 
                        type="button" 
                        @click="showModal = false" 
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

    {{-- TEXT Modal --}}
    <div x-show="showTextModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showTextModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-7 border border-gray-200">
            
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form id="textUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="saveText()">
                @csrf

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Label</label>
                    <input type="text" x-model="form.label" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea x-model="form.text" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter text..." required></textarea>
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

</div>

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

@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')

<div
    x-data="dayTourManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Page Information
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
                        @click="editImage({{ $img->id }}, '{{ $img->title }}')">
                        Edit
                    </button>
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
                            {{ $text->title ?: 'No Label' }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ Str::limit($text->content, 150) }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-4">
                            <button 
                                class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                                @click="editText({{ $text->id }})">
                                Edit
                            </button>

                            <form action="{{ route('day-tour-texts.destroy', $text->id) }}" 
                                method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button 
                                    onclick="return confirm('Delete this text?')"
                                    class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
                                    Delete
                                </button>
                            </form>
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
                    <label class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full" x-bind:value="editImageMode ? currentImageTitle : ''">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded-xl p-3 w-full" x-bind:required="!editImageMode">
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
                    <label class="block font-medium mb-1 text-gray-700">Label</label>
                    <input type="text" x-model="form.title" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter text..." required></textarea>
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
function dayTourManager() {
    return {
        isLoading: false,
        showModal: false,
        showTextModal: false,
        editMode: false,
        editImageMode: false,
        currentImageId: null,
        currentImageTitle: '',
        texts: @js($texts),
        form: { id: null, title: '', content: '' },

        resetForm() {
            this.form = { id: null, title: '', content: '' };
            this.editMode = false;
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            this.currentImageTitle = '';
        },

        editImage(id, title) {
            this.currentImageId = id;
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form = { id: text.id, title: text.title, content: text.content };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        async saveText() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            formData.append('title', this.form.title);
            formData.append('content', this.form.content);

            let url = '{{ route('day-tour-texts.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/day-tour-texts/${this.form.id}`;
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
            const fileInput = this.$refs.image.files[0];

            if (!fileInput && !this.editImageMode) {
                alert('Please select an image before uploading.');
                this.isLoading = false;
                return;
            }

            formData.append('title', this.$refs.title.value);
            if (fileInput) {
                formData.append('image', fileInput);
            }
            if (this.editImageMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editImageMode ? '/admin/day-tour-swiper-images/' + this.currentImageId : '{{ route('day-tour-swiper-images.store') }}';

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
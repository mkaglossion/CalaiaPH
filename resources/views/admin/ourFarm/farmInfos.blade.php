@extends('layouts.admin')

@section('title', 'Farm Info Page CMS')

@section('content')

<div
    x-data="farmInfoManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Farm Informations
    </h1>
    <p class="text-gray-600 mb-10">Manage farm information categories, images, and text content.</p>

    {{-- ============================================================= --}}
    {{-- FARM INFO CATEGORIES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Farm Info Categories</h2>
        </div>

        <button
            @click="showFarmInfoModal = true; resetForm()"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add FarmInfo
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($farmInfos as $index => $farmInfo)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $farmInfo->name ?? 'Untitled' }}</td>
                    <td class="px-4 py-2">{{ $farmInfo->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button class="text-blue-600 hover:underline" @click="editFarmInfo({{ $farmInfo->id }})">
                            Edit
                        </button>
                        <form action="{{ route('farmInfo-list.destroy', $farmInfo->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="text-red-600 hover:underline"
                                onclick="return confirm('Delete this farmInfo?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No farmInfos added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-14 border-gray-300">

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Swiper Images Management</h2>
        </div>

        <button
            @click="showModal = true"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Image
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Preview</th>
                    <th class="px-4 py-2">FarmInfo Name</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($images as $index => $img)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">
                            <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-20 h-12 object-cover rounded">
                        </td>
                        <td class="px-4 py-2">{{ $img->farmInfo->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form action="{{ route('farmInfo-image.destroy', $img->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this image?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No images uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-14 border-gray-300">

    {{-- ============================================================= --}}
    {{-- TEXT CONTENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Text Content Management</h2>
        </div>

        <button
            @click="showTextModal = true"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Text Content
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">FarmInfo Name</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Content</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($texts as $index => $text)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $text->farmInfo->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->title ?? 'No Title' }}</td>
                        <td class="px-4 py-2">{{ Str::limit($text->content, 60) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <button
                                class="text-blue-600 hover:underline"
                                @click="editText({{ $text->id }})">
                                Edit
                            </button>

                            <form action="{{ route('farmInfo-text.destroy', $text->id) }}"
                                method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button
                                    onclick="return confirm('Delete this text content?')"
                                    class="text-red-600 hover:underline"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No text content added yet.</td>
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

            <h3 class="text-xl font-bold mb-4 text-gray-900">Upload Swiper Image</h3>

            <form id="swiperUploadForm" class="space-y-5" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Farm Info Category</label>
                    <select x-ref="farmInfo" name="farm_info_id" class="border rounded-xl p-3 w-full" required>
                        <option value="">Select Farm Info</option>
                        @foreach($farmInfos as $farmInfo)
                            <option value="{{ $farmInfo->id }}">{{ $farmInfo->name }}</option>
                        @endforeach
                    </select>
                </div>

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
                    <label class="block font-medium mb-1 text-gray-700">Farm Info Category</label>
                    <select x-model="form.farm_info_id" name="farm_info_id" class="border rounded-xl p-3 w-full" required>
                        <option value="">Select Farm Info</option>
                        @foreach($farmInfos as $farmInfo)
                            <option value="{{ $farmInfo->id }}">{{ $farmInfo->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Title</label>
                    <input type="text" x-model="form.title" class="w-full border rounded-xl px-3 py-2" placeholder="Optional">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Content</label>
                    <textarea x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" placeholder="Enter content..." required></textarea>
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

    {{-- FARM INFO Modal --}}
    <div x-show="showFarmInfoModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showFarmInfoModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-7 border border-gray-200">

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editModeFarmInfo ? 'Edit Farm Info Category' : 'Add Farm Info Category'"></h3>

            <form id="farmInfoForm" class="space-y-5" @submit.prevent="saveFarmInfo()">
                @csrf
                <template x-if="editModeFarmInfo">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Category Name</label>
                    <input type="text" x-model="form.name" class="w-full border rounded-xl px-3 py-2" placeholder="Farm Info Name" required>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showFarmInfoModal = false" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        <span x-text="editModeFarmInfo ? 'Update' : 'Add'"></span>
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
function farmInfoManager() {
    return {
        isLoading: false,
        showModal: false,
        showTextModal: false,
        showFarmInfoModal: false,
        editMode: false,
        editModeFarmInfo: false,
        texts: {!! json_encode($texts) !!},
        farmInfos: {!! json_encode($farmInfos) !!},
        form: { id: null, farm_info_id: '', title: '', content: '', name: '' },

        resetForm() {
            this.form = { id: null, farm_info_id: '', title: '', content: '', name: '' };
            this.editMode = false;
            this.editModeFarmInfo = false;
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form = {
                    id: text.id,
                    farm_info_id: text.farm_info_id,
                    title: text.title,
                    content: text.content
                };
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        editFarmInfo(id) {
            const farmInfo = this.farmInfos.find(f => f.id === id);
            if (farmInfo) {
                this.form = {
                    id: farmInfo.id,
                    name: farmInfo.name
                };
                this.editModeFarmInfo = true;
                this.showFarmInfoModal = true;
            }
        },

        async saveText() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            formData.append('title', this.form.title);
            formData.append('content', this.form.content);
            formData.append('farm_info_id', this.form.farm_info_id);

            let url = '{{ route('farmInfo-text.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/farmInfo-text/${this.form.id}`;
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

        async saveFarmInfo() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            formData.append('name', this.form.name);

            let url = '{{ route('farmInfo-list.store') }}';

            if (this.editModeFarmInfo && this.form.id) {
                url = `/admin/farmInfo-list/${this.form.id}`;
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
                        title: this.editModeFarmInfo ? "Updated!" : "Added!",
                        text: data.message || "Saved successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.resetForm();
                        this.showFarmInfoModal = false;
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

            formData.append('title', this.$refs.title.value);
            formData.append('image', this.$refs.image.files[0]);
            formData.append('farm_info_id', this.$refs.farmInfo.value);

            try {
                const res = await fetch('{{ route('farmInfo-image.store') }}', {
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
                        text: data.message || "Image uploaded successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.showModal = false;
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Swal.fire({
                        icon: "error",
                        title: "Upload Failed",
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
@endsection

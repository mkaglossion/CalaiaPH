@extends('layouts.admin')

@section('title', 'Activities')

@section('content')
<div x-data="activityComponent(@js($texts), @js(\App\Models\Activity::all()))">
    <hr class="my-6">

    {{-- =======================
        ACTIVITIES MANAGEMENT
    ======================== --}}
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Activities</h2>
        <button
            @click="showActivityModal = true; resetForm()"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Activity
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Duration</th>
                    <th class="px-4 py-2">Pricing</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($activities as $index => $act)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">
                            {{ ($activities->currentPage() - 1) * $activities->perPage() + $index + 1 }}
                        </td>
                        <td class="px-4 py-2">{{ $act->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ ucfirst($act->category) ?? 'No category' }}</td>
                        <td class="px-4 py-2">{{ $act->description ?? 'No description' }}</td>
                        <td class="px-4 py-2">{{ $act->duration ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $act->price ? '₱' . number_format($act->price, 2) : 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $act->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <button class="text-blue-600 hover:underline" @click="editActivity({{ $act->id }})">
                                Edit
                            </button>

                            <form action="{{ route('activity-list.destroy', $act->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this activity?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                            No activities added yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>

    {{-- Activity Modal --}}
    <div
        x-show="showActivityModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div
            @click.away="showActivityModal = false"
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editModeActivity ? 'Edit Activity' : 'Add Activity'"></h3>

            <form id="activityForm" class="space-y-5" @submit.prevent="saveActivity()">
                @csrf

                <template x-if="editModeActivity">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input
                        type="text"
                        name="name"
                        x-model="form.name"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Name"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea
                        name="description"
                        x-model="form.description"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Description"
                        rows="3"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Duration</label>
                    <input
                        type="text"
                        name="duration"
                        x-model="form.duration"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="e.g., 2 hours"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Price</label>
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        x-model="form.price"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="e.g., 500.00"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select
                        name="category"
                        x-model="form.category"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                    >
                        <option value="">Select Category</option>
                        <option value="adventure">Adventure</option>
                        <option value="sports">Sports & Games</option>
                        <option value="rrcamp">R & R & Camp</option>
                        <option value="farm">Farm</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Upload Image</label>
                    <input
                        type="file"
                        name="image"
                        x-ref="activityImage"
                        accept="image/*"
                        @change="handleImageUpload($event)"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :required="!editModeActivity"
                    >
                    <small class="text-gray-500">Supported formats: JPG, PNG, GIF. Max size: 5MB</small>
                    <template x-if="form.imagePreview">
                        <div class="mt-2">
                            <img :src="form.imagePreview" alt="Image preview" class="w-32 h-20 object-cover rounded border">
                            <button type="button" @click="removeImage()" class="ml-2 text-red-500 hover:underline">Remove</button>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="showActivityModal = false; resetForm()"
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        <span x-text="editModeActivity ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =======================
        SWIPER IMAGES MANAGEMENT
    ======================== --}}
    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Swiper Images Management</h2>
        <button
            @click="showModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Image
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Preview</th>
                    <th class="px-4 py-2">Activity Name</th>
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
                        <td class="px-4 py-2">{{ $img->activity->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <button
                                class="text-blue-600 hover:underline"
                                @click="editImage({{ $img->id }}, '{{ $img->activity_id }}', '{{ $img->title }}')"
                            >
                                Edit
                            </button>

                            <form action="{{ route('activity-image.destroy', $img->id) }}" method="POST" class="inline">
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

    {{-- Swiper Modal (unchanged logic, just kept as-is) --}}
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                method="POST"
                @submit.prevent="uploadOrUpdateImage($el)"
            >
                @csrf

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Activity</label>
                    <select x-ref="activity" name="activity_id" class="border rounded p-2 w-full" required>
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full">
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Upload Image/Video</label>
                    <input type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded p-2 w-full" :required="!editImageMode">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" x-text="editImageMode ? 'Update' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function activityComponent(texts, activities) {
    return {
        // modals
        showModal: false,
        showActivityModal: false,
        showTextModal: false,

        // edit states
        editMode: false,
        editModeActivity: false,
        editImageMode: false,

        currentImageId: null,

        // data
        texts,
        activities: activities.data || activities,

        // one form object, consistent keys
        form: {
            id: null,
            name: '',
            description: '',
            duration: '',
            price: '',
            category: '',
            activity_id: '',
            label: '',
            text: '',
            title: '',
            image: null,
            imagePreview: null,
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                description: '',
                duration: '',
                price: '',
                category: '',
                activity_id: '',
                label: '',
                text: '',
                title: '',
                image: null,
                imagePreview: null,
            };
            this.editMode = false;
            this.editModeActivity = false;

            if (this.$refs.activityImage) this.$refs.activityImage.value = '';
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            if (this.$refs.image) this.$refs.image.value = '';
            if (this.$refs.title) this.$refs.title.value = '';
            if (this.$refs.activity) this.$refs.activity.value = '';
        },

        removeImage() {
            this.form.image = null;
            this.form.imagePreview = null;
            if (this.$refs.activityImage) this.$refs.activityImage.value = '';
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file');
                event.target.value = '';
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must not exceed 5MB');
                event.target.value = '';
                return;
            }

            // Store the file object
            this.form.image = file;

            // Create preview using FileReader
            const self = this;
            const reader = new FileReader();
            reader.onload = function(e) {
                // Set preview with a small delay to ensure Alpine detects the change
                setTimeout(() => {
                    self.form.imagePreview = e.target.result;
                }, 10);
            };
            reader.onerror = function() {
                alert('Error reading file');
                event.target.value = '';
                self.form.image = null;
                self.form.imagePreview = null;
            };
            reader.readAsDataURL(file);
        },

        editActivity(id) {
            const activity = this.activities.find(a => a.id === id);
            if (!activity) return;

            this.form.id = activity.id;
            this.form.name = activity.name || '';
            this.form.description = activity.description || '';
            this.form.duration = activity.duration || '';
            this.form.price = activity.price || '';
            this.form.category = activity.category || '';
            this.form.image = null; // Reset file input for new uploads

            // Handle existing image
            if (activity.image) {
                // Check if image is already a full URL (Cloudinary) or a local path
                if (activity.image.startsWith('http://') || activity.image.startsWith('https://')) {
                    this.form.imagePreview = activity.image; // Already a full URL
                } else {
                    this.form.imagePreview = '/storage/' + activity.image; // Local storage path
                }
            } else {
                this.form.imagePreview = null;
            }

            this.editModeActivity = true;
            this.showActivityModal = true;
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (!text) return;

            this.form.id = text.id;
            this.form.label = text.title || '';
            this.form.text = text.content || '';
            this.form.activity_id = text.activity_id || '';

            this.editMode = true;
            this.showTextModal = true;
        },

        editImage(id, activityId, title) {
            this.currentImageId = id;
            this.editImageMode = true;
            this.showModal = true;

            // fill modal refs after open
            this.$nextTick(() => {
                if (this.$refs.activity) this.$refs.activity.value = activityId || '';
                if (this.$refs.title) this.$refs.title.value = title || '';
            });
        },

        async saveText() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();

            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('activity_id', this.form.activity_id);

            let url = "{{ route('activity-text.store') }}";
            if (this.editMode && this.form.id) {
                url = `/admin/activity-text/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Text updated successfully!' : 'Text added successfully!'));
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch (err) {
                console.error(err);
                alert('Error submitting text.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        async saveActivity() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();

            formData.append('name', this.form.name);
            formData.append('description', this.form.description);
            formData.append('duration', this.form.duration);
            formData.append('price', this.form.price);
            formData.append('category', this.form.category);

            // Handle image upload
            if (this.form.image) {
                formData.append('image', this.form.image);
            }

            let url = "{{ route('activity-list.store') }}";
            if (this.editModeActivity && this.form.id) {
                url = `/admin/activity-list/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#activityForm button[type=submit]');
            btn.innerText = this.editModeActivity ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editModeActivity ? 'Activity updated!' : 'Activity added!'));
                    this.resetForm();
                    this.showActivityModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch (err) {
                console.error(err);
                alert('Error submitting activity.');
            } finally {
                btn.innerText = this.editModeActivity ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        async uploadOrUpdateImage(formEl) {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');

            const fileInput = this.$refs.image?.files?.[0];
            const title = this.$refs.title?.value || '';
            const activityId = this.$refs.activity?.value || '';

            if (!fileInput && !this.editImageMode) {
                alert('Please select an image before uploading.');
                return;
            }
            if (!activityId) {
                alert('Please select an activity.');
                return;
            }

            const formData = new FormData();
            if (fileInput) formData.append('image', fileInput);
            formData.append('title', title);
            formData.append('activity_id', activityId);
            if (this.editImageMode) formData.append('_method', 'PUT');

            const btn = formEl.querySelector('button[type=submit]');
            btn.innerText = this.editImageMode ? 'Updating...' : 'Uploading...';
            btn.disabled = true;

            const url = this.editImageMode
                ? `/admin/activity-image/${this.currentImageId}`
                : "{{ route('activity-image.store') }}";

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editImageMode ? 'Image updated successfully!' : 'Image uploaded successfully!'));
                    this.showModal = false;
                    this.resetImageForm();
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch (err) {
                console.error(err);
                alert('Error processing image.');
            } finally {
                btn.innerText = this.editImageMode ? 'Update' : 'Upload';
                btn.disabled = false;
            }
        },
    };
}
</script>
@endsection

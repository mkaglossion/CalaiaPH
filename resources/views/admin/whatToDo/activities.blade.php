@extends('layouts.admin')

@section('title', 'Activities Management')

@section('content')

<style>
    .swiper-button-next, .swiper-button-prev {
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        width: 40px;
        height: 40px;
    }
    .swiper-pagination-bullet-active {
        background-color: #a75e3e;
    }
</style>

<script>
  window.texts = @js($texts);
  window.activities = @js($activities);
</script>

<div
    x-data="activityManager()"
    class="p-4 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Activities Management
    </h1>
    <p class="text-gray-600 mb-10">Manage your activities, descriptions, and associated content.</p>
    {{-- ============================================================= --}}
    {{-- ACTIVITIES MANAGEMENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Activities</h2>
            <p class="text-gray-500 text-sm">Manage your activity listings and details.</p>
        </div>
        <button
            @click="showActivityModal = true; resetForm()"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition"
        >
            + Add Activity
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Activities Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">#</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Name</th>
                        <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Category</th>
                        <th class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Description</th>
                        <th class="hidden lg:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Duration</th>
                        <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Pricing</th>
                        <th class="hidden xl:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Uploaded</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900 text-right">Actions</th>
                    </tr>
                </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($activities as $index => $act)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">
                            {{ ($activities->currentPage() - 1) * $activities->perPage() + $index + 1 }}
                        </td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->name ?? 'Untitled' }}</td>
                        <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ ucfirst($act->category) ?? 'No category' }}</td>
                        <td class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->description ?? 'No description' }}</td>
                        <td class="hidden lg:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->duration ?? 'N/A' }}</td>
                        <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->price ? '₱' . number_format($act->price, 2) : 'N/A' }}</td>
                        <td class="hidden xl:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->created_at->format('Y-m-d') }}</td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-right">
  <div class="inline-flex items-center justify-end gap-1 sm:gap-2 whitespace-nowrap">
    <button
      type="button"
      class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-xs sm:text-sm"
      @click="editActivity({{ $act->id }})"
    >
      Edit
    </button>

    <button
      type="button"
      class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-xs sm:text-sm"
      @click="deleteActivity({{ $act->id }})"
    >
      Delete
    </button>
  </div>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 sm:px-4 lg:px-6 py-6 text-center text-gray-500">
                            No activities added yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $activities->links() }}
    </div>

    {{-- Add/Edit Activity Modal --}}
    <div
        x-cloak
        x-show="showActivityModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div @click.away="showActivityModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editModeActivity ? 'Edit Activity' : 'Add Activity'"></h3>

            <form id="activityForm" @submit.prevent="saveActivity()" class="space-y-5">
                @csrf
                <template x-if="editModeActivity">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label for="activity_name" class="block font-medium mb-1 text-gray-700">Name</label>
                    <input
                        id="activity_name"
                        type="text"
                        name="name"
                        x-model="form.name"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Name"
                        required
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label for="activity_description" class="block font-medium mb-1 text-gray-700">Description</label>
                    <textarea
                        id="activity_description"
                        name="description"
                        x-model="form.description"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Description"
                        rows="3"
                        autocomplete="off"
                    ></textarea>
                </div>

                <div>
                    <label for="activity_duration" class="block font-medium mb-1 text-gray-700">Duration</label>
                    <input
                        id="activity_duration"
                        type="text"
                        name="duration"
                        x-model="form.duration"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="e.g., 2 hours"
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label for="activity_price" class="block font-medium mb-1 text-gray-700">Price</label>
                    <input
                        id="activity_price"
                        type="number"
                        step="0.01"
                        name="price"
                        x-model="form.price"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="e.g., 500.00"
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label for="activity_category" class="block font-medium mb-1 text-gray-700">Category</label>
                    <select
                        id="activity_category"
                        name="category"
                        x-model="form.category"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                        autocomplete="off"
                    >
                        <option value="">Select Category</option>
                        <option value="adventure">Adventure</option>
                        <option value="sports">Sports & Games</option>
                        <option value="rrcamp">R & R & Camp</option>
                        <option value="farm">Farm</option>
                    </select>
                </div>

                <div>
                    <label for="activity_image" class="block font-medium mb-1 text-gray-700">Upload Image</label>
                    <input
                        id="activity_image"
                        type="file"
                        name="image"
                        x-ref="activityImage"
                        accept="image/*"
                        @change="handleImageUpload($event)"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showActivityModal = false; resetForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        <span x-text="editModeActivity ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES MANAGEMENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6 mt-12">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Swiper Images Management</h2>
            <p class="text-gray-500 text-sm">Manage carousel images for activities.</p>
        </div>
        <button
            @click="showModal = true"
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

    {{-- Images Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">#</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Preview</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Activity Name</th>
                        <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Title</th>
                        <th class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Uploaded</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($images as $index => $img)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $index + 1 }}</td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4">
                            <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-16 h-10 sm:w-20 sm:h-12 object-cover rounded-xl">
                        </td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->activity->name ?? 'Untitled' }}</td>
                        <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-right">
  <div class="inline-flex items-center justify-end gap-1 sm:gap-2 whitespace-nowrap">
    <button
      type="button"
      class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-xs sm:text-sm"
      @click="editImage({{ $img->id }}, '{{ $img->activity_id }}', '{{ $img->title }}')"
    >
      Edit
    </button>

    <button
      type="button"
      class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-xs sm:text-sm"
      @click="deleteImage({{ $img->id }})"
    >
      Delete
    </button>
  </div>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 sm:px-6 py-6 text-center text-gray-500">No images uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Add/Edit Swiper Image Modal --}}
    <div
        x-cloak
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div @click.away="showModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-200">
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                method="POST"
                @submit.prevent="uploadOrUpdateImage($el)"
            >
                @csrf

                <div>
                    <label for="image_activity" class="block font-medium mb-1 text-gray-700">Activity</label>
                    <select id="image_activity" x-ref="activity" name="activity_id" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required autocomplete="off">
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image_title" class="block font-medium mb-1 text-gray-700">Title (Optional)</label>
                    <input id="image_title" type="text" x-ref="title" name="title" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter image title" autocomplete="off">
                </div>

                <div>
                    <label for="image_file" class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input id="image_file" type="file" x-ref="image" name="image" accept="image/*,video/*" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" :required="!editImageMode">
                    <small class="text-gray-500">Supported formats: JPG, PNG, GIF, MP4. Max size: 5MB</small>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition" x-text="editImageMode ? 'Update' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- GLOBAL LOADING OVERLAY --}}
    <div
        x-cloak
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
function activityManager() {
    return {
        // State management
        isLoading: false,
        showModal: false,
        showActivityModal: false,
        showTextModal: false,
        editMode: false,
        editModeActivity: false,
        editImageMode: false,
        currentImageId: null,

        // Data
        texts: window.texts || [],
        activities: Array.isArray(window.activities?.data)
            ? window.activities.data
            : (Array.isArray(window.activities) ? window.activities : []),

        // Form data
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

        // Methods
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

            // Reset file input
            const activityImage = this.$refs.activityImage;
            if (activityImage) activityImage.value = '';
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;

            // Reset form elements
            const imageInput = this.$refs.image;
            const titleInput = this.$refs.title;
            const activitySelect = this.$refs.activity;

            if (imageInput) imageInput.value = '';
            if (titleInput) titleInput.value = '';
            if (activitySelect) activitySelect.value = '';
        },

        removeImage() {
            this.form.image = null;
            this.form.imagePreview = null;

            const activityImage = this.$refs.activityImage;
            if (activityImage) activityImage.value = '';
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                event.target.value = '';
                return;
            }

            // Validate file size (5MB limit)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('File size must not exceed 5MB.');
                event.target.value = '';
                return;
            }

            // Store file and create preview
            this.form.image = file;
            this.createImagePreview(file);
        },

        createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.form.imagePreview = e.target.result;
            };
            reader.onerror = () => {
                alert('Error reading file.');
                this.form.image = null;
                this.form.imagePreview = null;
            };
            reader.readAsDataURL(file);
        },

        editActivity(id) {
            const activity = this.activities.find(a => a.id === id);
            if (!activity) {
                console.error('Activity not found');
                return;
            }

            // Populate form
            this.form.id = activity.id;
            this.form.name = activity.name || '';
            this.form.description = activity.description || '';
            this.form.duration = activity.duration || '';
            this.form.price = activity.price || '';
            this.form.category = activity.category || '';
            this.form.image = null; // Reset for new uploads

            // Handle image preview
            this.setImagePreview(activity.image);

            this.editModeActivity = true;
            this.showActivityModal = true;
        },

        setImagePreview(imagePath) {
            if (imagePath) {
                // Check if full URL or local path
                const isFullUrl = imagePath.startsWith('http://') || imagePath.startsWith('https://');
                this.form.imagePreview = isFullUrl ? imagePath : `/storage/${imagePath}`;
            } else {
                this.form.imagePreview = null;
            }
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (!text) {
                console.error('Text not found');
                return;
            }

            this.form.id = text.id;
            this.form.label = text.title || '';
            this.form.text = text.content || '';
            this.form.activity_id = text.activity_id || '';

            this.editMode = true;
            this.showTextModal = true;
        },

        deleteActivity(id) {
            Swal.fire({
                title: 'Delete Activity?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                customClass: {
                    confirmButton: 'swal-confirm-btn',
                    cancelButton: 'swal-cancel-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitDeleteForm('activity-list', id);
                }
            });
        },

        deleteImage(id) {
            Swal.fire({
                title: 'Delete Image?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                customClass: {
                    confirmButton: 'swal-confirm-btn',
                    cancelButton: 'swal-cancel-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitDeleteForm('activity-image', id);
                }
            });
        },

        submitDeleteForm(route, id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin') }}/${route}/${id}`;

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            form.appendChild(csrfInput);

            // Add method spoofing
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        },

        async saveText() {
            const url = this.editMode
                ? `/admin/activity-text/${this.form.id}`
                : "{{ route('activity-text.store') }}";

            const formData = new FormData();
            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('activity_id', this.form.activity_id);
            if (this.editMode) formData.append('_method', 'PUT');

            this.isLoading = true;
            const submitBtn = document.querySelector('#textUploadForm button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editMode ? 'Updating...' : 'Adding...';
            submitBtn.disabled = true;

            try {
                const response = await this.submitForm(url, formData);

                if (response.ok) {
                    await this.handleSuccess(response, this.editMode ? 'Updated!' : 'Added!', 'Text saved successfully!');
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                console.error('Error saving text:', error);
                this.showErrorAlert('Error submitting text.');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        async saveActivity() {
            const url = this.editModeActivity
                ? `/admin/activity-list/${this.form.id}`
                : "{{ route('activity-list.store') }}";

            const formData = new FormData();
            formData.append('name', this.form.name);
            formData.append('description', this.form.description);
            formData.append('duration', this.form.duration);
            formData.append('price', this.form.price);
            formData.append('category', this.form.category);

            if (this.form.image) {
                formData.append('image', this.form.image);
            }

            if (this.editModeActivity) {
                formData.append('_method', 'PUT');
            }

            this.isLoading = true;
            const submitBtn = document.querySelector('#activityForm button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editModeActivity ? 'Updating...' : 'Adding...';
            submitBtn.disabled = true;

            try {
                const response = await this.submitForm(url, formData);

                if (response.ok) {
                    await this.handleSuccess(response, this.editModeActivity ? 'Updated!' : 'Added!', 'Activity saved successfully!');
                    this.resetForm();
                    this.showActivityModal = false;
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                console.error('Error saving activity:', error);
                this.showErrorAlert('Error submitting activity.');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        async uploadOrUpdateImage(formElement) {
            const fileInput = this.$refs.image;
            const titleInput = this.$refs.title;
            const activitySelect = this.$refs.activity;

            if (!fileInput?.files?.[0] && !this.editImageMode) {
                this.showWarningAlert('No Image Selected', 'Please select an image before uploading.');
                return;
            }

            if (!activitySelect?.value) {
                this.showWarningAlert('No Activity Selected', 'Please select an activity.');
                return;
            }

            const formData = new FormData();
            if (fileInput.files[0]) formData.append('image', fileInput.files[0]);
            formData.append('title', titleInput?.value || '');
            formData.append('activity_id', activitySelect.value);

            if (this.editImageMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editImageMode
                ? `/admin/activity-image/${this.currentImageId}`
                : "{{ route('activity-image.store') }}";

            this.isLoading = true;
            const submitBtn = formElement.querySelector('button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editImageMode ? 'Updating...' : 'Uploading...';
            submitBtn.disabled = true;

            try {
                const response = await this.submitForm(url, formData);

                if (response.ok) {
                    await this.handleSuccess(response, this.editImageMode ? 'Updated!' : 'Uploaded!', 'Image processed successfully!');
                    this.showModal = false;
                    this.resetImageForm();
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                console.error('Error processing image:', error);
                this.showErrorAlert('Error processing image.');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        async submitForm(url, formData) {
            const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');

            return await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin'
            });
        },

        async handleSuccess(response, title, defaultMessage) {
            const data = await response.json();
            const message = data.message || defaultMessage;

            await Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                confirmButtonColor: '#2563eb',
                customClass: {
                    confirmButton: 'swal-confirm-btn'
                }
            });
        },

        async handleError(response) {
            const data = await response.json();

            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('<br>');
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    html: errorMessages,
                    customClass: {
                        confirmButton: 'swal-confirm-btn'
                    }
                });
            } else {
                const message = data.message || 'Operation failed.';
                this.showErrorAlert(message);
            }
        },

        showWarningAlert(title, text) {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                confirmButtonColor: '#2563eb',
                customClass: {
                    confirmButton: 'swal-confirm-btn'
                }
            });
        },

        showErrorAlert(text) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: text,
                confirmButtonColor: '#2563eb',
                customClass: {
                    confirmButton: 'swal-confirm-btn'
                }
            });
        },

        editImage(id, activityId, title) {
            this.currentImageId = id;
            this.editImageMode = true;
            this.showModal = true;

            // Set form values after DOM update
            this.$nextTick(() => {
                const activitySelect = this.$refs.activity;
                const titleInput = this.$refs.title;

                if (activitySelect) activitySelect.value = activityId || '';
                if (titleInput) titleInput.value = title || '';
            });
        },
    };
}
</script>
@endsection

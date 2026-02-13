@extends('layouts.admin')

@section('title', 'Testimonials Management')

@section('content')

<div
    x-data="testimonialsManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Testimonials Management
    </h1>
    <p class="text-gray-600 mb-10">Manage customer testimonials displayed on the homepage.</p>

    <div class="flex justify-between items-end mb-6">
         <div>
             <h2 class="text-2xl font-bold text-gray-800">Customer Testimonials</h2>
             <p class="text-gray-500 text-sm">Testimonials that appear in the customer reviews section.</p>
         </div>

         <button
             @click="showModal = true; resetForm()"
             class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
             + Add Testimonial
         </button>
     </div>

     {{-- Search and Filter Bar --}}
     <div class="bg-white/50 backdrop-blur-sm border border-gray-200 rounded-xl p-4 mb-6">
         <form method="GET" action="{{ route('admin.testimonials.index') }}" class="flex flex-col sm:flex-row gap-4">
             <div class="flex-1">
                 <label class="block text-sm font-medium text-gray-700 mb-1">Search Testimonials</label>
                 <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, position, or content..."
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
             </div>

             <div class="sm:w-32">
                 <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                 <select name="rating" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                     <option value="">All Ratings</option>
                     <option value="5" {{ request('rating') === '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5)</option>
                     <option value="4" {{ request('rating') === '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4)</option>
                     <option value="3" {{ request('rating') === '3' ? 'selected' : '' }}>⭐⭐⭐ (3)</option>
                     <option value="2" {{ request('rating') === '2' ? 'selected' : '' }}>⭐⭐ (2)</option>
                     <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>⭐ (1)</option>
                 </select>
             </div>


             <div class="flex items-end gap-2">
                 <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                     <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                     Search
                 </button>
                 @if(request('search') || request('status'))
                     <a href="{{ route('admin.testimonials.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                         <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                         Clear
                     </a>
                 @endif
             </div>
         </form>
     </div>

    {{-- Testimonials Table --}}
    <div class="bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl overflow-hidden shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Testimonial</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($testimonials as $testimonial)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center mr-3">
                                        @if($testimonial->image_path)
                                            <img src="{{ $testimonial->image_path }}" alt="{{ $testimonial->name }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="user" class="w-6 h-6 text-gray-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $testimonial->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $testimonial->position }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs">
                                    <p class="text-sm text-gray-900 italic truncate">"{{ Str::limit($testimonial->content, 80) }}"</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i data-lucide="star" class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-yellow-400 fill-current' : 'text-gray-300' }}"></i>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600">{{ $testimonial->rating }}/5</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $testimonial->order }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <button
                                        @click="editTestimonial"
                                        data-id="{{ $testimonial->id }}"
                                        data-name="{{ $testimonial->name }}"
                                        data-position="{{ $testimonial->position }}"
                                        data-content="{{ $testimonial->content }}"
                                        data-image-path="{{ $testimonial->image_path }}"
                                        data-order="{{ $testimonial->order }}"
                                        data-rating="{{ $testimonial->rating }}"
                                        data-status="{{ $testimonial->status }}"
                                        class="text-blue-600 hover:text-blue-900 transition flex items-center">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i>
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete({{ $testimonial->id }})"
                                        class="text-red-600 hover:text-red-900 transition flex items-center">
                                        <i data-lucide="trash" class="w-4 h-4 mr-1"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mb-4">
                                    <i data-lucide="message-circle" class="w-12 h-12 text-gray-400 mx-auto"></i>
                                </div>
                                <p class="text-gray-500">No testimonials found.</p>
                                @if(request('search') || request('status'))
                                    <p class="text-sm text-gray-400 mt-2">Try adjusting your search or filter criteria.</p>
                                @else
                                    <p class="text-sm text-gray-400 mt-2">Click "Add Testimonial" to create your first customer testimonial.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($testimonials->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $testimonials->appends(request()->query())->links() }}
        </div>
    @endif

    <!-- Testimonial Modal -->
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-2xl p-6 relative border border-gray-200">
            <button
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Testimonial' : 'Add New Testimonial'"></h3>

            <form id="testimonialForm" class="space-y-5" @submit.prevent="saveTestimonial">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                         <label class="block font-medium mb-1 text-gray-700">Customer Name</label>
                         <input type="text" x-model="form.name" class="border rounded-xl p-3 w-full" required>
                     </div>

                     <div>
                         <label class="block font-medium mb-1 text-gray-700">Position/Title</label>
                         <input type="text" x-model="form.position" class="border rounded-xl p-3 w-full" required placeholder="e.g., Family Traveler">
                     </div>
                 </div>

                 <div>
                     <label class="block font-medium mb-1 text-gray-700">Testimonial Content</label>
                     <textarea x-model="form.content" rows="4" class="w-full border rounded-xl px-3 py-2" required placeholder="Enter the customer's testimonial..."></textarea>
                 </div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                         <label class="block font-medium mb-1 text-gray-700">Rating (1-5 stars)</label>
                         <select x-model="form.rating" class="border rounded-xl p-3 w-full" required>
                             <option value="5">⭐⭐⭐⭐⭐ (5 stars)</option>
                             <option value="4">⭐⭐⭐⭐ (4 stars)</option>
                             <option value="3">⭐⭐⭐ (3 stars)</option>
                             <option value="2">⭐⭐ (2 stars)</option>
                             <option value="1">⭐ (1 star)</option>
                         </select>
                     </div>
                 </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Avatar Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                         @click="$refs.avatarInput.click()">
                        <template x-if="form.avatar">
                            <div class="mb-4 flex flex-col items-center gap-2">
                                <img :src="getAvatarPreviewUrl()" alt="Avatar Preview" class="w-20 h-20 rounded-full object-cover shadow">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="form.remove_avatar" class="rounded">
                                    <span>Remove current avatar</span>
                                </label>
                            </div>
                        </template>

                        <template x-if="!form.avatar">
                            <div class="flex flex-col items-center gap-2 text-center">
                                <i data-lucide="user" class="w-8 h-8 text-gray-400"></i>
                                <p class="text-gray-600 font-medium">Click to upload avatar</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </template>

                        <input type="file" accept="image/*" class="hidden" x-ref="avatarInput" @change="handleAvatarUpload">
                    </div>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Display Order</label>
                    <input type="number" x-model="form.order" class="border rounded-xl p-3 w-full" required min="0" placeholder="0 = first, higher numbers = later">
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
                        class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
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
function testimonialsManager() {
    return {
        isLoading: false,
        showModal: false,
        editMode: false,
        form: {
            id: null,
            name: '',
            position: '',
            content: '',
            avatar: '',
            avatar_preview: null,
            order: 1,
            rating: 5,
            remove_avatar: false
        },
        avatarFile: null,

        resetForm() {
            this.form = {
                id: null,
                name: '',
                position: '',
                content: '',
                avatar: '',
                avatar_preview: null,
                order: 1,
                rating: 5,
                remove_avatar: false
            };
            this.avatarFile = null;
            this.editMode = false;
        },

        handleAvatarUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.avatarFile = file;
                this.form.avatar_preview = URL.createObjectURL(file);
                this.form.remove_avatar = false;
            }
        },

        getAvatarPreviewUrl() {
            if (this.form.avatar_preview && this.form.avatar_preview.startsWith('blob:')) {
                return this.form.avatar_preview;
            }
            if (this.form.avatar) {
                return this.form.avatar;
            }
            return this.form.avatar_preview;
        },

        editTestimonial(event) {
            const button = event.target.closest('button');
            this.form = {
                id: button.dataset.id,
                name: button.dataset.name,
                position: button.dataset.position,
                content: button.dataset.content,
                avatar: button.dataset.imagePath,
                avatar_preview: button.dataset.imagePath,
                order: parseInt(button.dataset.order),
                rating: parseInt(button.dataset.rating),
                remove_avatar: false
            };
            this.editMode = true;
            this.showModal = true;
        },

        confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete this testimonial!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteTestimonial(id);
                }
            });
        },

        async saveTestimonial() {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            // Add all form fields
            formData.append('name', this.form.name);
            formData.append('position', this.form.position);
            formData.append('content', this.form.content);
            formData.append('image_path', this.form.avatar || '');
            formData.append('order', this.form.order);
            formData.append('rating', this.form.rating);
            formData.append('status', 'approved'); // Always approved since only admins add testimonials
            if (this.form.remove_avatar) formData.append('remove_avatar', 1);

            // Add avatar file if present
            if (this.avatarFile) {
                formData.append('avatar_file', this.avatarFile);
            }

            let url = '{{ route('admin.testimonials.store') }}';
            let method = 'POST';

            if (this.editMode && this.form.id) {
                url = `/admin/testimonials/${this.form.id}`;
                method = 'POST';
                formData.append('_method', 'PUT');
            }

            try {
                const res = await fetch(url, {
                    method: method,
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
                        text: data.message || "Testimonial saved successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        this.resetForm();
                        this.showModal = false;
                        window.location.href = '/admin/testimonials';
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

        async deleteTestimonial(id) {
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;

            try {
                const res = await fetch(`/admin/testimonials/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "Testimonial has been deleted.",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Failed to delete testimonial."
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
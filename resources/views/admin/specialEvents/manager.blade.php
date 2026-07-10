@extends('layouts.admin')

@section('title', 'Manage Special Events')

@section('content')
@php
    $adminPrimaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-medium text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg';
    $adminSecondaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100';
    $adminActionLink = 'text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition';
@endphp

<div x-data="specialEventsManager()" class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">

    <h1 class="text-4xl font-extrabold mb-8 tracking-tight text-gray-900">
        Events Manager
    </h1>

    {{-- Add Button --}}
    <div class="flex justify-end mb-8">
        <button 
            @click="openAddModal()" 
            class="{{ $adminPrimaryButton }}">
            + Add Event
        </button>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 auto-rows-max">
        <template x-for="event in events" :key="event.id">
            <div class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative flex flex-col h-full">
                {{-- Image --}}
                <div class="overflow-hidden rounded-xl mb-4">
                    <template x-if="event.image">
                        <img :src="event.image" :alt="event.name" class="w-full h-48 object-cover transition hover:scale-105">
                    </template>
                    <template x-if="!event.image">
                        <div class="w-full h-48 bg-linear-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Info --}}
                <div class="mb-4">
                    <h3 class="font-semibold text-lg text-gray-900" x-text="event.name"></h3>
                    <p class="text-gray-500 text-sm mt-1 line-clamp-2" x-text="event.description"></p>
                </div>

                {{-- Created Date --}}
                <div class="mb-4">
                    <p class="text-xs text-gray-400">Created: <span x-text="new Date(event.created_at).toLocaleDateString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'})"></span></p>
                </div>

                {{-- Features --}}
                <div class="mb-4 flex flex-wrap gap-2">
                    <template x-for="feature in (event.features ? event.features.split(',').map(f => f.trim()) : []).slice(0, 3)" :key="feature">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full" x-text="feature"></span>
                    </template>
                    <template x-if="event.features && event.features.split(',').length > 3">
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">+<span x-text="event.features.split(',').length - 3"></span> more</span>
                    </template>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-auto">
                    <button class="{{ $adminActionLink }}"
                        @click="editEvent(event)">Edit</button>
                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                        @click="deleteEvent(event.id)">
                        Delete
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <template x-if="events.length === 0">
        <div class="text-center py-16">
            <p class="text-gray-500">No events yet. Create one to get started!</p>
        </div>
    </template>

    {{-- Add/Edit Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-40 p-3 sm:p-4 md:p-6"
        x-transition.opacity
        @click.self="closeModal()"
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-5 sm:p-6 md:p-8 relative border border-gray-200">
            <button 
                @click="closeModal()"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-2xl sm:text-3xl leading-none transition"
            >&times;</button>

            <h3 class="text-lg sm:text-xl md:text-2xl font-bold mb-1 text-gray-900 pr-8" x-text="isEditing ? 'Edit Event' : 'Add Event'"></h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-5 sm:mb-6">Fill in the details below to create or update an event</p>

            <form class="space-y-4 sm:space-y-5" enctype="multipart/form-data" @submit.prevent="saveEvent()">
                @csrf

                {{-- Event Name --}}
                <div>
                    <label for="event_name" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Event Name *</label>
                    <input 
                        type="text" 
                        id="event_name"
                        name="name"
                        autocomplete="off"
                        x-model="form.name" 
                        required 
                        placeholder="Enter event name"
                        class="w-full text-sm sm:text-base border border-gray-300 rounded-xl px-3 sm:px-4 py-2 sm:py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                {{-- Description --}}
                <div>
                    <label for="event_description" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Description *</label>
                    <textarea 
                        id="event_description"
                        name="description"
                        x-model="form.description" 
                        rows="3" 
                        required 
                        placeholder="Describe what makes this event special..."
                        class="w-full text-sm sm:text-base border border-gray-300 rounded-xl px-3 sm:px-4 py-2 sm:py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"></textarea>
                </div>

                {{-- Features --}}
                <div>
                    <label for="event_features" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Features (comma-separated) *</label>
                    <textarea 
                        id="event_features"
                        name="features"
                        x-model="form.features" 
                        rows="2" 
                        required 
                        placeholder="e.g., Custom planning, Beautiful setting, Professional service"
                        class="w-full text-sm sm:text-base border border-gray-300 rounded-xl px-3 sm:px-4 py-2 sm:py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Separate each feature with a comma</p>
                </div>

                {{-- Image Upload --}}
                <div>
                    <label for="imageFileInput" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Event Image</label>
                    <div>
                        <input 
                            type="file" 
                            @change="handleImageUpload" 
                            accept="image/*" 
                            id="imageFileInput"
                            name="image"
                            class="hidden">
                        <label for="imageFileInput" class="flex flex-col items-center justify-center w-full px-3 sm:px-4 py-6 sm:py-8 border-2 border-dashed border-[#d8bcb7] rounded-xl text-center cursor-pointer hover:border-[#8f5d54] hover:bg-[#fdf7f5] transition bg-gray-50">
                            <svg class="w-8 sm:w-10 h-8 sm:h-10 text-[#8f5d54] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Click to upload image</p>
                            <p class="text-xs sm:text-sm text-gray-600 mt-2">Max 100MB • JPG, PNG, GIF, WebP</p>
                        </label>
                    </div>
                </div>

                {{-- Current Image Preview --}}
                <template x-if="form.image && typeof form.image === 'string' && form.image.startsWith('http')">
                    <div class="border border-gray-200 rounded-xl p-3 sm:p-4 bg-gray-50">
                        <p class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                        <img :src="form.image" alt="Current" class="h-24 sm:h-32 w-24 sm:w-32 object-cover rounded-lg">
                    </div>
                </template>

                {{-- New Image Preview --}}
                <template x-if="form.imagePreview">
                    <div class="border border-gray-200 rounded-xl p-3 sm:p-4 bg-gray-50">
                        <p class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Preview:</p>
                        <img :src="form.imagePreview" alt="Preview" class="h-24 sm:h-32 w-24 sm:w-32 object-cover rounded-lg">
                    </div>
                </template>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-5 border-t border-gray-200">
                    <button
                        type="button"
                        @click="closeModal()"
                        class="w-full sm:flex-1 {{ $adminSecondaryButton }}">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full sm:flex-1 px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base rounded-xl {{ $adminPrimaryButton }} font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                        <span class="inline-block" x-text="isEditing ? 'Update Event' : 'Add Event'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- GLOBAL LOADING OVERLAY -->
    <div 
        x-show="isLoading"
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
        style="z-index: 9999 !important;"
    >
        <div class="flex flex-col items-center space-y-3">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-30" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                <path class="opacity-90" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white text-lg font-medium">Processing...</span>
        </div>
    </div>

<script>
function specialEventsManager() {
    return {
        events: @js($events ?? []),
        showModal: false,
        isEditing: false,
        isLoading: false,
        form: {
            id: null,
            name: '',
            description: '',
            features: '',
            image: null,
            public_id: null,
            imageFile: null,
            imagePreview: null
        },

        openAddModal() {
            this.isEditing = false;
            this.resetForm();
            this.showModal = true;
        },

        editEvent(event) {
            this.isEditing = true;
            this.form = {
                id: event.id,
                name: event.name,
                description: event.description,
                features: event.features,
                image: event.image,
                public_id: event.public_id || null,
                imageFile: null,
                imagePreview: null
            };
            this.showModal = true;
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.form.imageFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                description: '',
                features: '',
                image: null,
                public_id: null,
                imageFile: null,
                imagePreview: null
            };
            this.isEditing = false;
        },

        async saveEvent() {
            if (this.isLoading) return; // Prevent double submission
            
            this.isLoading = true;
            const wasEditing = this.isEditing;
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const formData = new FormData();

            try {
                const url = this.isEditing
                    ? `{{ url('/admin/event-list') }}/${this.form.id}`
                    : '{{ route('event-list.store') }}';

                let imageUrl = this.form.image;
                let publicId = this.form.public_id;

                if (this.form.imageFile) {
                    const uploadData = new FormData();
                    uploadData.append('image', this.form.imageFile);

                    const uploadResponse = await fetch('/api/special-events/upload-image', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: uploadData
                    });

                    const uploadResult = await uploadResponse.json();

                    if (!uploadResponse.ok || !uploadResult.success) {
                        throw new Error(uploadResult.message || 'Image upload failed');
                    }

                    imageUrl = uploadResult.imageUrl;
                    publicId = uploadResult.public_id;
                }

                formData.append('name', this.form.name);
                formData.append('description', this.form.description);
                formData.append('features', this.form.features);
                if (imageUrl) {
                    formData.append('image', imageUrl);
                }
                if (publicId) {
                    formData.append('public_id', publicId);
                }
                if (this.isEditing) {
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (response.ok) {
                    // Update events list
                    if (wasEditing) {
                        this.events = this.events.map((event) => (
                            event.id === this.form.id ? data.event : event
                        ));
                    } else {
                        this.events = [...this.events, data.event];
                    }
                    
                    // Close modal and reset form first
                    this.showModal = false;
                    this.resetForm();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: wasEditing ? 'Event updated successfully!' : 'Event created successfully!',
                        confirmButtonColor: '#2563eb',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.replace(`${window.location.pathname}?updated=${Date.now()}`);
                    });
                } else {
                    let errorMessage = data.message || 'Error saving event';
                    if (data.errors) {
                        const errors = Object.values(data.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#2563eb'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save event. Please try again.',
                    confirmButtonColor: '#2563eb'
                });
            } finally {
                this.isLoading = false;
            }
        },

        deleteEvent(eventId) {
            Swal.fire({
                title: 'Delete Event?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.isLoading = true;
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    fetch(`{{ url('/admin/event-list') }}/${eventId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json'
                        }
                    }).then(response => response.json())
                    .then(data => {
                        this.events = this.events.filter(e => e.id !== eventId);
                        this.isLoading = false;
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Event deleted successfully.',
                            confirmButtonColor: '#2563eb'
                        });
                    }).catch(error => {
                        this.isLoading = false;
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete event'
                        });
                    });
                }
            });
        }
    }
}
</script>

</div>

@endsection

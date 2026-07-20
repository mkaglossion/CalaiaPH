@extends('layouts.admin')

@section('title', 'Farm Experiences Management')

@section('content')
@push('styles')
<style>
    [data-farm-experience-card].sortable-ghost { opacity: .35; }
    [data-farm-experience-card].sortable-chosen { box-shadow: 0 18px 40px rgb(15 23 42 / .18); }
    [data-farm-experience-card].sortable-drag { transform: rotate(1deg); }
</style>
@endpush
@php
    $adminPrimaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-medium text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg';
    $adminSecondaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100';
    $adminActionLink = 'text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition';
@endphp

<div x-data="farmExperiencesManager()" x-init="init()" class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">

    <h1 class="text-4xl font-extrabold mb-8 tracking-tight text-gray-900">
        Farm Experiences Manager
    </h1>

    {{-- Add Button --}}
    <div class="flex justify-end mb-8">
        <button 
            @click="openAddModal()" 
            class="{{ $adminPrimaryButton }}">
            + Add Experience
        </button>
    </div>

    {{-- Cards Grid --}}
    <div x-ref="experienceGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 auto-rows-max">
        <template x-for="(experience, index) in experiences" :key="experience._key">
            <div data-farm-experience-card class="tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 p-5 rounded-2xl shadow-md hover:shadow-xl transition relative flex flex-col h-full">
                <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-full border border-gray-200 bg-white/95 px-2 text-xs font-bold text-gray-600 shadow-sm" x-text="`#${index + 1}`"></span>
                    <button type="button" data-drag-handle class="inline-flex h-12 w-12 cursor-grab items-center justify-center rounded-full border border-gray-300 bg-white/95 text-gray-600 shadow-md transition hover:border-[#8f5d54] hover:text-[#8f5d54] active:cursor-grabbing" title="Drag to reorder" aria-label="Drag to reorder">
                        <i class="fas fa-grip-vertical text-sm" aria-hidden="true"></i>
                    </button>
                </div>
                {{-- Image --}}
                <div class="overflow-hidden rounded-xl mb-4">
                    <template x-if="experience.img">
                        <img :src="experience.img" :alt="experience.title" class="w-full h-48 object-cover transition hover:scale-105">
                    </template>
                    <template x-if="!experience.img">
                        <div class="w-full h-48 bg-linear-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Info --}}
                <div class="mb-4">
                    <h3 class="font-semibold text-lg text-gray-900" x-text="experience.title"></h3>
                    <p class="text-gray-500 text-sm mt-1 line-clamp-2" x-text="experience.desc"></p>
                </div>

                {{-- Details --}}
                <div class="mb-4 text-xs text-gray-500 space-y-1">
                    <div><span class="font-medium">Time:</span> <span x-text="experience.time"></span></div>
                </div>

                {{-- Created Date --}}
                <div class="mb-4">
                    <p class="text-xs text-gray-400" x-text="formatDate(experience.created_at)"></p>
                </div>

                {{-- Badge --}}
                <template x-if="experience.badge">
                    <div class="mb-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full" x-text="experience.badge"></span>
                    </div>
                </template>

                {{-- Action Buttons --}}
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-auto">
                    <button class="{{ $adminActionLink }}"
                        @click="editExperience(index)">Edit</button>
                    <button class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                        @click="deleteExperience(index)">Delete</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <template x-if="experiences.length === 0">
        <div class="text-center py-16">
            <p class="text-gray-500">No experiences yet. Create one to get started!</p>
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

            <h3 class="text-lg sm:text-xl md:text-2xl font-bold mb-1 text-gray-900 pr-8" x-text="isEditing ? 'Edit Experience' : 'Add Experience'"></h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-5 sm:mb-6">Fill in the details below to create or update an experience</p>

            <form class="space-y-4 sm:space-y-5" @submit.prevent="saveExperience()">
                @csrf

                {{-- Experience Title --}}
                <div>
                    <label for="experience_title" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Experience Title *</label>
                    <input 
                        id="experience_title"
                        type="text" 
                        x-model="currentExperience.title" 
                        required 
                        placeholder="Enter experience title"
                        class="w-full rounded-xl border border-gray-400 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition sm:px-4 sm:py-3 sm:text-base focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                </div>

                {{-- Description --}}
                <div>
                    <label for="experience_desc" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Description *</label>
                    <textarea 
                        id="experience_desc"
                        x-model="currentExperience.desc" 
                        rows="3" 
                        required 
                        placeholder="Describe this experience..."
                        class="w-full resize-none rounded-xl border border-gray-400 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition sm:px-4 sm:py-3 sm:text-base focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20"></textarea>
                </div>

                {{-- Time/Schedule --}}
                <div>
                    <label for="experience_time" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Time/Schedule *</label>
                    <input 
                        id="experience_time"
                        type="text" 
                        x-model="currentExperience.time" 
                        required 
                        placeholder="e.g., Weekends 9AM–2PM"
                        class="w-full rounded-xl border border-gray-400 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition sm:px-4 sm:py-3 sm:text-base focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                </div>

                {{-- Badge (Optional) --}}
                <div>
                    <label for="experience_badge" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Badge (Optional)</label>
                    <input 
                        id="experience_badge"
                        type="text" 
                        x-model="currentExperience.badge" 
                        placeholder="e.g., Guided Tour"
                        class="w-full rounded-xl border border-gray-400 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition sm:px-4 sm:py-3 sm:text-base focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                </div>

                {{-- Image Upload --}}
                <div>
                    <label for="imageFileInput" class="block text-xs sm:text-sm font-medium mb-2 text-gray-700">Experience Image</label>
                    <div>
                        <input 
                            type="file" 
                            @change="handleImageUpload" 
                            accept="image/*" 
                            id="imageFileInput"
                            class="hidden">
                        <label for="imageFileInput" class="flex flex-col items-center justify-center w-full px-3 sm:px-4 py-6 sm:py-8 border-2 border-dashed border-[#d8bcb7] rounded-xl text-center cursor-pointer hover:border-[#8f5d54] hover:bg-[#fdf7f5] transition bg-gray-50">
                            <svg class="w-8 sm:w-10 h-8 sm:h-10 text-[#8f5d54] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Click to upload image</p>
                            <p class="text-xs sm:text-sm text-gray-600 mt-2">Max 5 MB • JPG, PNG, WebP</p>
                        </label>
                    </div>
                </div>

                {{-- Current Image Preview --}}
                <template x-if="currentExperience.img && typeof currentExperience.img === 'string' && currentExperience.img.startsWith('http')">
                    <div class="border border-gray-200 rounded-xl p-3 sm:p-4 bg-gray-50">
                        <p class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                        <img :src="currentExperience.img" alt="Current" class="h-24 sm:h-32 w-24 sm:w-32 object-cover rounded-lg">
                    </div>
                </template>

                {{-- New Image Preview --}}
                <template x-if="currentExperience.imagePreview">
                    <div class="border border-gray-200 rounded-xl p-3 sm:p-4 bg-gray-50">
                        <p class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Preview:</p>
                        <img :src="currentExperience.imagePreview" alt="Preview" class="h-24 sm:h-32 w-24 sm:w-32 object-cover rounded-lg">
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
                        :disabled="isSaving"
                        class="w-full sm:flex-1 px-3 sm:px-4 py-3 sm:py-3.5 text-sm sm:text-base rounded-xl {{ $adminPrimaryButton }} font-bold disabled:opacity-50 disabled:cursor-not-allowed transition"
                        x-text="isEditing ? 'Update Experience' : 'Add Experience'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- GLOBAL LOADING OVERLAY FOR IMAGE UPLOAD -->
    <div 
        x-show="isUploadingImage"
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
        style="z-index: 9999 !important;"
    >
        <div class="flex flex-col items-center space-y-3">
            <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-30" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                <path class="opacity-90" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white text-lg font-medium">Uploading Image...</span>
        </div>
    </div>

    <!-- GLOBAL LOADING OVERLAY FOR SAVE -->
    <div 
        x-show="isSaving"
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

</div>

<script>
function farmExperiencesManager() {
    return {
        experiences: {!! json_encode($experiences) !!}.filter(exp => exp && exp.title).map((exp, index) => ({
            ...exp,
            _key: exp.id ? `experience-${exp.id}` : `experience-new-${index}-${Date.now()}`,
        })),
        currentExperience: {},
        editingIndex: null,
        showModal: false,
        isEditing: false,
        isSaving: false,
        isUploadingImage: false,
        isReordering: false,
        sortableInstance: null,

        init() {
            this.$nextTick(() => this.initSortable());
        },

        initSortable() {
            this.sortableInstance?.destroy();
            this.sortableInstance = null;
            if (typeof Sortable === 'undefined' || !this.$refs.experienceGrid) return;

            this.sortableInstance = Sortable.create(this.$refs.experienceGrid, {
                animation: 180,
                handle: '[data-drag-handle]',
                draggable: '[data-farm-experience-card]',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: async (event) => {
                    if (event.oldIndex === event.newIndex) return;
                    const [movedExperience] = this.experiences.splice(event.oldIndex, 1);
                    this.experiences.splice(event.newIndex, 0, movedExperience);
                    await this.saveOrder();
                },
            });
        },

        async saveOrder() {
            if (this.isReordering) return;
            this.isReordering = true;
            try {
                const response = await fetch('{{ route("admin.farm.experiences.save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ experiences: this.experiences }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Unable to save the new order.');
                Swal.fire({ icon: 'success', title: 'Order saved', timer: 1100, showConfirmButton: false });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Reorder failed', text: error.message });
            } finally {
                this.isReordering = false;
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'Created: N/A';
            try {
                // Handle various date formats
                let date;
                if (typeof dateString === 'string') {
                    // Try parsing the date string
                    date = new Date(dateString.replace(' ', 'T')); // Convert '2024-11-27 10:30:00' to ISO format
                } else {
                    date = new Date(dateString);
                }
                
                if (isNaN(date.getTime())) {
                    console.warn('Invalid date:', dateString);
                    return 'Created: N/A';
                }
                
                const formatted = date.toLocaleDateString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'});
                return 'Created: ' + formatted;
            } catch (e) {
                console.error('Date parsing error:', e, dateString);
                return 'Created: N/A';
            }
        },

        openAddModal() {
            this.isEditing = false;
            this.editingIndex = null;
            this.resetForm();
            this.showModal = true;
        },

        editExperience(index) {
            this.isEditing = true;
            this.editingIndex = index;
            this.currentExperience = JSON.parse(JSON.stringify(this.experiences[index]));
            this.currentExperience.imagePreview = null;
            this.showModal = true;
        },

        resetForm() {
            this.currentExperience = {
                id: null,
                img: null,
                image_public_id: null,
                title: '',
                desc: '',
                time: '',
                capacity: '',
                icon: 'people',
                badge: null,
                created_at: new Date().toISOString(),
                imageFile: null,
                imagePreview: null
            };
            this.isEditing = false;
            this.editingIndex = null;
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.currentExperience.imageFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.currentExperience.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async saveExperience() {
            if (this.isSaving) return;
            
            this.isSaving = true;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            try {
                // Upload image if a new file is selected
                if (this.currentExperience.imageFile) {
                    const formData = new FormData();
                    formData.append('image', this.currentExperience.imageFile);

                    const uploadResponse = await fetch('{{ route("admin.farm.experiences.uploadImage") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const uploadData = await uploadResponse.json();

                    if (!uploadData.success) {
                        this.isSaving = false;
                        throw new Error(uploadData.message || 'Failed to upload image');
                    }

                    this.currentExperience.img = uploadData.imageUrl;
                    this.currentExperience.image_public_id = uploadData.publicId;
                    this.currentExperience.imageFile = null;
                }

                // Clean up the experience data (remove imagePreview and imageFile)
                const cleanExperience = {
                    ...this.currentExperience,
                    imagePreview: null,
                    imageFile: null
                };

                if (this.isEditing) {
                    // Update existing experience
                    this.experiences[this.editingIndex] = cleanExperience;
                } else {
                    // Add new experience
                    cleanExperience.id = null;
                    // Ensure created_at is set for new experiences
                    if (!cleanExperience.created_at) {
                        cleanExperience.created_at = new Date().toISOString();
                    }
                    this.experiences.push(cleanExperience);
                }

                const response = await fetch('{{ route("admin.farm.experiences.save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        experiences: this.experiences
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showModal = false;
                    this.resetForm();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: this.isEditing ? 'Experience updated successfully!' : 'Experience created successfully!',
                        confirmButtonColor: '#2563eb',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save experience'
                    });
                }
            } catch (error) {
                console.error('Error saving experience:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error saving experience'
                });
            } finally {
                this.isSaving = false;
            }
        },

        deleteExperience(index) {
            Swal.fire({
                title: 'Delete Experience?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.isSaving = true;
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    
                    // Remove from array
                    this.experiences.splice(index, 1);

                    // Save to cache
                    fetch('{{ route("admin.farm.experiences.save") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            experiences: this.experiences
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isSaving = false;
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Experience deleted successfully.',
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete experience'
                            });
                            // Restore the deleted item
                            this.experiences.splice(index, 0, this.currentExperience);
                        }
                    })
                    .catch(error => {
                        this.isSaving = false;
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete experience'
                        });
                        // Restore the deleted item
                        this.experiences.splice(index, 0, this.currentExperience);
                    });
                }
            });
        }
    };
}
</script>

@endsection

@extends('layouts.admin')

@section('title', 'Manage Packages')

@section('content')

<div 
    x-data="packageManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">Manage Packages</h1>
    <p class="text-gray-600 mb-10">Manage all vacation packages, bookings, and availability CalaiaPH offers</p>

    {{-- Section Header --}}
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">All Packages</h2>
            <p class="text-gray-500 text-sm mt-1">Create, edit, and manage packages</p>
        </div>
        <button 
            @click="showModal = true; resetForm()" 
            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> New Package
        </button>
    </div>

    {{-- Packages List --}}
    @if($packages->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($packages as $package)
                <div class="bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full" data-package-id="{{ $package->id }}">
<!-- Image -->
                        @if($package->image)
                            <div class="w-full h-48 overflow-hidden">
                                <img src="{{ \App\Helpers\ImageHelper::src($package->image) }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif

                    <!-- Content -->
                    <div class="p-6 flex flex-col flex-1">
                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $package->name }}</h3>

                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($package->description, 100) }}</p>

                        <!-- Capacity Badge -->
                        <div class="mb-3">
                            <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">
                                Good for {{ $package->max_guests }} person{{ $package->max_guests > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="mb-3">
                            <p class="text-2xl font-bold text-gray-900">
                                ₱{{ number_format($package->price, 0) }} 
                                <span class="text-sm font-normal text-gray-600">
                                    /head
                                </span>
                            </p>
                            <p class="text-sm text-gray-500">per head pricing, total depends on selected guests</p>
                            <p class="text-xs text-gray-500 mt-1">Duration: {{ $package->duration_label ?? (($package->duration_days ?? 1) . ' Day' . (($package->duration_days ?? 1) > 1 ? 's' : '')) }}</p>
                        </div>

                        <hr class="my-3 border-gray-200">

                        <!-- Inclusions -->
                        @if($package->inclusions && is_array($package->inclusions) && count($package->inclusions) > 0)
                            <div class="mb-3 flex flex-wrap gap-2">
                                @foreach($package->inclusions as $inclusion)
                                    <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-full font-medium">
                                        {{ $inclusion }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Footer -->
                        <div class="flex justify-between items-center pt-3 mt-auto border-t border-gray-200">
                            <span class="text-xs text-gray-500">{{ $package->created_at->format('Y-m-d') }}</span>
                            <div class="flex items-center space-x-4">
                                <button 
                                    class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-sm"
                                    @click="editPackage({{ $package->id }})">Edit</button>
                                <button 
                                    class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-sm"
                                    @click="deletePackage({{ $package->id }}, '{{ route('admin.packages.destroy', $package->id) }}')">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-24">
            <div class="mb-6 inline-block">
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-inbox text-gray-400 text-5xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Packages Yet</h3>
            <p class="text-gray-600 mb-6">Get started by creating your first package</p>
            <button 
                @click="showModal = true; resetForm()"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                <i class="fas fa-plus mr-2"></i> Create Package
            </button>
        </div>
    @endif

    {{-- Package Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
        @click.away="showModal = false"
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-2xl border border-gray-200 max-h-[85vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900" x-text="editMode ? 'Edit Package' : 'Create New Package'"></h3>
                <button 
                    @click="showModal = false"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                >&times;</button>
            </div>

            <!-- Modal Content - Scrollable -->
            <div class="flex-1 overflow-y-auto p-6">
                <form @submit.prevent="savePackage" class="space-y-5" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" x-model="form.id" name="id" />

                    <!-- Name -->
                    <div>
                        <label for="name" class="block font-semibold mb-2 text-gray-700">Package Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            x-model="form.name" 
                            name="name"
                            placeholder="e.g., Adventure Package"
                            autocomplete="off"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                            required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block font-semibold mb-2 text-gray-700">Description *</label>
                        <textarea 
                            id="description" 
                            x-model="form.description" 
                            name="description"
                            rows="3"
                            placeholder="Describe the package..."
                            autocomplete="off"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                            required></textarea>
                    </div>

                    <!-- Grid: Price, Duration, Guests -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="price" class="block font-semibold mb-2 text-gray-700">
                              Price (₱)
                              <span class="text-xs font-normal text-gray-500">per head</span>
                              *
                            </label>
                            <input 
                                type="number" 
                                id="price" 
                                x-model="form.price" 
                                name="price"
                                placeholder="5000"
                                step="100"
                                autocomplete="off"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                                required>
                            <p class="text-xs text-gray-500 mt-1">This amount is charged per head.</p>
                        </div>

                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Duration *</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input 
                                    type="number" 
                                    id="duration_value" 
                                    x-model="form.duration_value" 
                                    name="duration_value"
                                    placeholder="3"
                                    min="1"
                                    autocomplete="off"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                                    required>
                                <select
                                    id="duration_unit"
                                    x-model="form.duration_unit"
                                    name="duration_unit"
                                    autocomplete="off"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                                    required>
                                    <option value="minutes">Minute(s)</option>
                                    <option value="hours">Hour(s)</option>
                                    <option value="days">Day(s)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="max_guests" class="block font-semibold mb-2 text-gray-700">Max Guests *</label>
                            <input 
                                type="number" 
                                id="max_guests" 
                                x-model="form.max_guests" 
                                name="max_guests"
                                placeholder="4"
                                min="1"
                                autocomplete="off"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                                required>
                        </div>
                    </div>

                    <!-- Grid: Pricing Type, Max Groups -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Pricing Type</label>
                            <input type="text" value="Per Head" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-700" readonly>
                        </div>

                        <div>
                            <label for="max_groups_per_day" class="block font-semibold mb-2 text-gray-700">Max Groups/Day *</label>
                            <input 
                                type="number" 
                                id="max_groups_per_day" 
                                x-model="form.max_groups_per_day" 
                                name="max_groups_per_day"
                                placeholder="2"
                                min="1"
                                autocomplete="off"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition"
                                required>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label for="image" class="block font-semibold mb-2 text-gray-700">Package Image</label>
                        <input 
                            type="file" 
                            id="image" 
                            x-ref="imageFile"
                            name="image"
                            accept="image/*"
                            @change="
                                const file = $el.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => form.imagePreview = e.target.result;
                                    reader.readAsDataURL(file);
                                }
                            "
                            autocomplete="off"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        <p class="text-xs text-gray-500 mt-2">Upload a package image (JPG, PNG, GIF, WebP)</p>
                        
                        <template x-if="form.imagePreview">
                            <div class="relative inline-block mt-3">
                                <img :src="form.imagePreview" alt="Preview" class="h-32 rounded-lg border">
                                <button type="button" @click="form.imagePreview = null; $refs.imageFile.value = ''" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700">
                                    ×
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Inclusions -->
                    <div>
                        <span class="block font-semibold mb-2 text-gray-700">Inclusions</span>
                        <p class="text-xs text-gray-500 mb-3">Add items that are included in this package</p>
                        
                        <!-- Input for new inclusion -->
                        <div class="flex gap-2 mb-3">
                            <label for="newInclusion" class="sr-only">Add Inclusion</label>
                            <input 
                                type="text" 
                                id="newInclusion"
                                placeholder="e.g., Lunch, ATV Ride, Guide..."
                                autocomplete="off"
                                @keyup.enter="addInclusion()"
                                class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition text-sm">
                            <button 
                                type="button"
                                @click="addInclusion()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl transition font-medium text-sm">
                                Add
                            </button>
                        </div>

                        <!-- List of inclusions -->
                        <div class="bg-gray-50 rounded-xl p-3 space-y-2" x-show="form.inclusionsList && form.inclusionsList.length > 0">
                            <template x-for="(item, idx) in form.inclusionsList" :key="idx">
                                <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-3 py-2">
                                    <span x-text="item" class="text-sm text-gray-700"></span>
                                    <button 
                                        type="button"
                                        @click="removeInclusion(idx)"
                                        class="text-red-600 hover:text-red-700 text-lg leading-none font-bold">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Empty state -->
                        <div class="bg-gray-50 rounded-xl p-4 text-center text-gray-500 text-sm" x-show="!form.inclusionsList || form.inclusionsList.length === 0">
                            No inclusions added yet
                        </div>
                    </div>

                    <!-- Available Sessions -->
                    <div>
                        <span class="block font-semibold mb-2 text-gray-700">Available Sessions</span>
                        <p class="text-xs text-gray-500 mb-3">Select which time slots customers can book for this package</p>
                        
                        <div class="space-y-2 bg-gray-50 rounded-xl p-4">
                            <template x-for="(session, idx) in form.sessions" :key="idx">
                                <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2.5">
                                    <input 
                                        type="checkbox" 
                                        :id="'session_' + idx"
                                        x-model="form.sessions[idx].enabled"
                                        autocomplete="off"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600/20">
                                    <label :for="'session_' + idx" class="flex-1 cursor-pointer text-sm text-gray-700 font-medium">
                                        <span x-text="session.name"></span>
                                        <span class="text-gray-500 ml-1" x-text="'(' + session.start + ' - ' + session.end + ')'"></span>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div class="flex items-center gap-3">
                        <input 
                            type="checkbox" 
                            id="is_active" 
                            x-model="form.is_active" 
                            name="is_active"
                            autocomplete="off"
                            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-600/20">
                        <label for="is_active" class="font-medium text-gray-700">Active Package</label>
                    </div>

                    <!-- Error Messages -->
                    <div x-show="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="text-red-800 text-sm space-y-1">
                            <template x-for="error in errors" :key="error">
                                <div x-text="error"></div>
                            </template>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                <button
                    type="button"
                    @click="showModal = false"
                    class="px-6 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-100 transition font-medium text-gray-700">
                    Cancel
                </button>

                <button
                    type="button"
                    @click="savePackage()"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition font-medium"
                    x-text="editMode ? 'Update Package' : 'Create Package'">
                </button>
            </div>
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
function packageManager() {
    return {
        isLoading: false,
        showModal: false,
        editMode: false,
        errors: [],
        packages: @js($packages->all()),
        form: { 
            id: null, 
            name: '', 
            description: '', 
            price: '', 
            duration_value: '', 
            duration_unit: 'hours',
            max_guests: '', 
            max_groups_per_day: '',
            pricing_type: 'per_pax',
            image: null,
            imagePreview: null,
            inclusionsList: [],
            sessions: [
                { name: 'Morning', start: '8:00 AM', end: '11:00 AM', enabled: true },
                { name: 'Afternoon', start: '12:00 PM', end: '3:00 PM', enabled: true },
                { name: 'Late Session', start: '3:30 PM', end: '6:30 PM', enabled: true }
            ],
            is_active: true
        },

        resetForm() {
            this.form = { 
                id: null, 
                name: '', 
                description: '', 
                price: '', 
                duration_value: '', 
                duration_unit: 'hours',
                max_guests: '', 
                max_groups_per_day: '',
                pricing_type: 'per_pax',
                image: null,
                imagePreview: null,
                inclusionsList: [],
                sessions: [
                    { name: 'Morning', start: '8:00 AM', end: '11:00 AM', enabled: true },
                    { name: 'Afternoon', start: '12:00 PM', end: '3:00 PM', enabled: true },
                    { name: 'Late Session', start: '3:30 PM', end: '6:30 PM', enabled: true }
                ],
                is_active: true
            };
            this.editMode = false;
            this.errors = [];
            if (this.$refs.imageFile) this.$refs.imageFile.value = '';
            document.getElementById('newInclusion').value = '';
        },

        addInclusion() {
            const input = document.getElementById('newInclusion');
            const value = input.value.trim();
            
            if (!value) return;
            if (this.form.inclusionsList.includes(value)) {
                Swal.fire({ icon: 'info', title: 'Duplicate', text: 'This item is already added.' });
                return;
            }
            
            this.form.inclusionsList.push(value);
            input.value = '';
            input.focus();
        },

        removeInclusion(idx) {
            this.form.inclusionsList.splice(idx, 1);
        },

        refreshPackages() {
            window.location.reload();
        },

        editPackage(id) {
            this.isLoading = true;
            this.errors = [];
            
            fetch(`/admin/packages/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                // Update form with fetched data
                this.form.id = data.id;
                this.form.name = data.name || '';
                this.form.description = data.description || '';
                this.form.price = String(parseFloat(data.price) || 0);
                this.form.duration_value = String(parseInt(data.duration_value) || parseInt(data.duration_days) || 1);
                this.form.duration_unit = data.duration_unit || 'days';
                this.form.max_guests = String(parseInt(data.max_guests) || 1);
                this.form.max_groups_per_day = String(parseInt(data.max_groups_per_day) || 1);
                this.form.pricing_type = 'per_pax';
                this.form.image = data.image || '';
                this.form.imagePreview = data.image || null;
                // Parse inclusions JSON into array for the UI
                this.form.inclusionsList = Array.isArray(data.inclusions) ? data.inclusions : [];
                // Parse sessions - add enabled property based on whether session exists in database
                const allSessions = [
                    { name: 'Morning', start: '8:00 AM', end: '11:00 AM' },
                    { name: 'Afternoon', start: '12:00 PM', end: '3:00 PM' },
                    { name: 'Late Session', start: '3:30 PM', end: '6:30 PM' }
                ];
                const dbSessions = Array.isArray(data.sessions) ? data.sessions : [];
                const dbSessionNames = dbSessions.map(s => s.name);
                
                this.form.sessions = allSessions.map(session => ({
                    ...session,
                    enabled: dbSessionNames.includes(session.name)
                }));
                this.form.is_active = Boolean(data.is_active);
                
                this.editMode = true;
                this.isLoading = false;
                
                // Use $nextTick to ensure DOM updated before showing modal
                this.$nextTick(() => {
                    this.showModal = true;
                    // Clear file input
                    if (this.$refs.imageFile) this.$refs.imageFile.value = '';
                    document.getElementById('newInclusion').value = '';
                });
            })
            .catch(err => {
                console.error('Error:', err);
                this.isLoading = false;
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load package' });
            });
        },

        deletePackage(id, route) {
            Swal.fire({
                title: 'Delete Package?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    this.isLoading = true;
                    
                    console.log('Deleting package from route:', route);
                    
                    fetch(route, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log('Delete response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP Error: ${res.status}`);
                        }
                        return res.json().catch(e => {
                            console.error('JSON parse error:', e);
                            return { success: true, message: 'Package deleted' };
                        });
                    })
                    .then(data => {
                        console.log('Delete response data:', data);
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message || 'Package deleted successfully.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            this.refreshPackages();
                        });
                    })
                    .catch((err) => {
                        console.error('Delete error details:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: err.message || 'Failed to delete package'
                        });
                    })
                    .finally(() => { this.isLoading = false; });
                }
            });
        },

        async savePackage() {
            this.isLoading = true;
            this.errors = [];

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            // Use the inclusionsList array directly
            const inclusions = this.form.inclusionsList || [];
            
            // Filter only enabled sessions
            const sessions = (this.form.sessions || []).map(s => ({
                name: s.name,
                start: s.start,
                end: s.end
            })).filter(s => {
                const idx = this.form.sessions.findIndex(orig => orig.name === s.name);
                return this.form.sessions[idx]?.enabled;
            });

            formData.append('name', this.form.name);
            formData.append('description', this.form.description);
            formData.append('price', Number(this.form.price));
            formData.append('duration_value', Number(this.form.duration_value || 1));
            formData.append('duration_unit', this.form.duration_unit || 'hours');
            formData.append('max_guests', Number(this.form.max_guests));
            formData.append('max_groups_per_day', Number(this.form.max_groups_per_day));
            formData.append('pricing_type', 'per_pax');
            formData.append('inclusions', JSON.stringify(inclusions));
            formData.append('sessions', JSON.stringify(sessions));
            formData.append('is_active', this.form.is_active ? 1 : 0);

            // Handle file upload - only if a file is selected
            if (this.$refs.imageFile && this.$refs.imageFile.files.length > 0) {
                formData.append('image', this.$refs.imageFile.files[0]);
            }

            let url = '{{ route('admin.packages.store') }}';
            if (this.editMode && this.form.id) {
                url = `/admin/packages/${this.form.id}`;
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
                        title: this.editMode ? "Updated!" : "Created!",
                        text: data.message || "Package saved successfully!",
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        window.location.reload();
                    });
                } else if (data.errors) {
                    Object.values(data.errors).forEach(err => {
                        if (Array.isArray(err)) {
                            this.errors.push(...err);
                        } else {
                            this.errors.push(err);
                        }
                    });
                }
            } catch (err) {
                this.errors.push("An unexpected error occurred. Please try again.");
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>

@endsection


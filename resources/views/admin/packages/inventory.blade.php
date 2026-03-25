@extends('layouts.admin')

@section('title', 'Package Inventory')

@section('content')

<div 
    x-data="inventoryManager()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>
    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">Package Inventory</h1>
    <p class="text-gray-600 mb-10">Manage availability and daily inventory for all packages</p>

    {{-- Section Header --}}
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Inventory Management</h2>
            <p class="text-gray-500 text-sm mt-1">View and manage package availability by date</p>
        </div>
        <button 
            @click="showModal = true; resetForm()" 
            class="px-5 py-2.5 rounded-xl bg-brand text-white font-medium shadow hover:bg-[#a0675a] hover:shadow-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Inventory
        </button>
    </div>

    {{-- Packages with Inventory --}}
    @if($packages->count() > 0)
        <div class="space-y-8">
            @foreach($packages as $package)
                <div class="bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <!-- Package Header -->
                    <div class="bg-linear-to-r from-brand/10 to-blue-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $package->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Max Capacity: <span class="font-semibold">{{ $package->max_guests }} person{{ $package->max_guests > 1 ? 's' : '' }}</span>
                                </p>
                            </div>
                            <button 
                                @click="editPackage({{ $package->id }})"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-edit"></i> Manage Inventory
                            </button>
                        </div>
                    </div>

                    <!-- Inventory Table -->
                    @if($package->inventory && count($package->inventory) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Max Groups</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Booked</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Available</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($package->inventory->sortBy('date') as $inv)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <span class="font-medium text-gray-900">{{ $inv->date->format('M d, Y') }}</span>
                                                <span class="text-xs text-gray-500 ml-2">({{ $inv->date->format('D') }})</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-semibold text-gray-900">{{ $inv->max_groups }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-semibold text-orange-600">{{ $inv->booked_groups }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-semibold {{ $inv->available_groups > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $inv->available_groups }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($inv->is_blocked)
                                                    <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                                        <i class="fas fa-lock"></i> Blocked
                                                    </span>
                                                @else
                                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                                        <i class="fas fa-unlock"></i> Open
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex gap-2">
                                                    <button 
                                                        @click="editInventory({{ $package->id }}, {{ $inv->id }})"
                                                        class="text-blue-600 hover:text-blue-700 text-sm font-medium hover:underline">
                                                        Edit
                                                    </button>
                                                    <button 
                                                        @click="deleteInventory({{ $inv->id }})"
                                                        class="text-red-600 hover:text-red-700 text-sm font-medium hover:underline">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                                <i class="fas fa-inbox text-3xl mb-3 block text-gray-300"></i>
                                                No inventory slots configured for this package yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <i class="fas fa-calendar text-4xl text-gray-300 mb-4 block"></i>
                            <p class="text-gray-500 mb-4">No inventory slots configured for this package</p>
                            <button 
                                @click="editPackage({{ $package->id }})"
                                class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium">
                                Add Inventory
                            </button>
                        </div>
                    @endif
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
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Packages Available</h3>
            <p class="text-gray-600">Create packages first before managing inventory</p>
        </div>
    @endif

    {{-- Inventory Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
        @click.away="showModal = false"
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md border border-gray-200">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900" x-text="editMode ? 'Edit Inventory' : 'Add Inventory'"></h3>
                <button 
                    @click="showModal = false"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                >&times;</button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <form @submit.prevent="saveInventory" class="space-y-5">
                    @csrf
                    <input type="hidden" x-model="form.id" />
                    <input type="hidden" x-model="form.packageId" />

                    <!-- Package Selection (only on create) -->
                    <div x-show="!editMode">
                        <label for="package" class="block font-semibold mb-2 text-gray-700">Select Package *</label>
                        <select 
                            id="package"
                            x-model="form.packageId"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                            <option value="">-- Choose Package --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Selection Mode Toggle -->
                    <div x-show="!editMode" class="flex gap-3">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                type="radio" 
                                x-model="form.dateMode" 
                                value="specific"
                                class="w-4 h-4 text-brand cursor-pointer">
                            <span class="ml-2 font-medium text-gray-700">Specific Date</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input 
                                type="radio" 
                                x-model="form.dateMode" 
                                value="range"
                                class="w-4 h-4 text-brand cursor-pointer">
                            <span class="ml-2 font-medium text-gray-700">Date Range</span>
                        </label>
                    </div>

                    <!-- Specific Date -->
                    <div x-show="editMode || form.dateMode === 'specific'">
                        <label for="date" class="block font-semibold mb-2 text-gray-700">Date *</label>
                        <input 
                            type="date" 
                            id="date" 
                            x-model="form.date"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Date Range -->
                    <div x-show="!editMode && form.dateMode === 'range'" class="space-y-3">
                        <div>
                            <label for="dateFrom" class="block font-semibold mb-2 text-gray-700">From Date *</label>
                            <input 
                                type="date" 
                                id="dateFrom" 
                                x-model="form.dateFrom"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                        <div>
                            <label for="dateTo" class="block font-semibold mb-2 text-gray-700">To Date *</label>
                            <input 
                                type="date" 
                                id="dateTo" 
                                x-model="form.dateTo"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                    </div>

                    <!-- Max Groups -->
                    <div>
                        <label for="maxGroups" class="block font-semibold mb-2 text-gray-700">Max Groups *</label>
                        <input 
                            type="number" 
                            id="maxGroups" 
                            x-model.number="form.maxGroups"
                            min="1"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Block Status -->
                    <div class="flex items-center gap-3">
                        <input 
                            type="checkbox" 
                            id="isBlocked" 
                            x-model="form.isBlocked"
                            class="w-5 h-5 rounded border-gray-300 text-brand focus:ring-brand/20">
                        <label for="isBlocked" class="font-medium text-gray-700">Block this date</label>
                    </div>

                    <!-- Block Reason -->
                    <div x-show="form.isBlocked">
                        <label for="blockReason" class="block font-semibold mb-2 text-gray-700">Block Reason</label>
                        <input 
                            type="text" 
                            id="blockReason" 
                            x-model="form.blockReason"
                            placeholder="e.g., Maintenance, Staff Training..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Error Messages -->
                    <div x-show="errors.length > 0" class="p-3 bg-red-50 border border-red-200 rounded-xl">
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
                    @click="saveInventory()"
                    class="px-6 py-2.5 rounded-xl bg-brand text-white hover:bg-[#a0675a] shadow transition font-medium"
                    x-text="editMode ? 'Update' : 'Add Inventory'"></button>
            </div>
        </div>
    </div>
</div>

<script>
function inventoryManager() {
    return {
        isLoading: false,
        showModal: false,
        editMode: false,
        errors: [],
        packages: @js($packages->all()),
        form: {
            id: null,
            packageId: null,
            dateMode: 'specific',
            date: new Date().toISOString().split('T')[0],
            dateFrom: new Date().toISOString().split('T')[0],
            dateTo: new Date().toISOString().split('T')[0],
            maxGroups: 1,
            isBlocked: false,
            blockReason: ''
        },

        resetForm() {
            this.form = {
                id: null,
                packageId: null,
                dateMode: 'specific',
                date: new Date().toISOString().split('T')[0],
                dateFrom: new Date().toISOString().split('T')[0],
                dateTo: new Date().toISOString().split('T')[0],
                maxGroups: 1,
                isBlocked: false,
                blockReason: ''
            };
            this.editMode = false;
            this.errors = [];
        },

        refreshPage() {
            window.location.reload();
        },

        editPackage(packageId) {
            this.resetForm();
            this.form.packageId = packageId;
            this.form.dateMode = 'specific';
            this.showModal = true;
        },

        editInventory(packageId, inventoryId) {
            // In a real app, fetch inventory details
            this.editMode = true;
            this.form.packageId = packageId;
            this.form.id = inventoryId;
            this.form.dateMode = 'specific';  // Always use specific date mode in edit
            this.showModal = true;
        },

        deleteInventory(id) {
            Swal.fire({
                title: 'Delete Inventory?',
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
                    
                    fetch(`/admin/packages/inventory/${id}`, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            this.refreshPage();
                        });
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: err.message || 'Failed to delete'
                        });
                    })
                    .finally(() => { this.isLoading = false; });
                }
            });
        },

        async saveInventory() {
            this.isLoading = true;
            this.errors = [];

            if (!this.form.packageId) {
                this.errors.push('Please select a package');
                this.isLoading = false;
                return;
            }

            if (this.form.dateMode === 'specific' && !this.form.date) {
                this.errors.push('Please select a date');
                this.isLoading = false;
                return;
            }

            if (this.form.dateMode === 'range') {
                if (!this.form.dateFrom || !this.form.dateTo) {
                    this.errors.push('Please select both start and end dates');
                    this.isLoading = false;
                    return;
                }
                if (new Date(this.form.dateFrom) > new Date(this.form.dateTo)) {
                    this.errors.push('Start date must be before end date');
                    this.isLoading = false;
                    return;
                }
            }

            const csrf = document.querySelector('meta[name=csrf-token]').content;

            try {
                if (this.form.dateMode === 'range') {
                    // Handle date range - create inventory for each date
                    const startDate = new Date(this.form.dateFrom);
                    const endDate = new Date(this.form.dateTo);
                    let currentDate = new Date(startDate);
                    let successCount = 0;
                    let failureCount = 0;
                    const failedDates = [];

                    while (currentDate <= endDate) {
                        const dateStr = currentDate.toISOString().split('T')[0];
                        
                        const payload = {
                            package_id: this.form.packageId,
                            date: dateStr,
                            max_groups: this.form.maxGroups,
                            is_blocked: this.form.isBlocked,
                            block_reason: this.form.blockReason
                        };

                        try {
                            const res = await fetch('/admin/packages/inventory', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                            if (res.ok) {
                                successCount++;
                            } else {
                                const errData = await res.json();
                                if (errData.message && errData.message.includes('already exists')) {
                                    // Skip if already exists
                                    successCount++;
                                } else {
                                    failureCount++;
                                    failedDates.push(dateStr);
                                }
                            }
                        } catch (err) {
                            failureCount++;
                            failedDates.push(dateStr);
                        }

                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    let message = `Created inventory for ${successCount} days`;
                    if (failureCount > 0) {
                        message += ` (${failureCount} failed)`;
                    }

                    Swal.fire({
                        icon: failureCount === 0 ? "success" : "warning",
                        title: failureCount === 0 ? "Completed!" : "Partially Completed",
                        text: message,
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    // Handle specific date
                    const payload = {
                        package_id: this.form.packageId,
                        date: this.form.date,
                        max_groups: this.form.maxGroups,
                        is_blocked: this.form.isBlocked,
                        block_reason: this.form.blockReason
                    };

                    let url = '/admin/packages/inventory';
                    let method = 'POST';

                    if (this.editMode && this.form.id) {
                        url = `/admin/packages/inventory/${this.form.id}`;
                        method = 'PUT';
                    }

                    const res = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok) {
                        Swal.fire({
                            icon: "success",
                            title: this.editMode ? "Updated!" : "Created!",
                            text: data.message || "Inventory saved successfully!",
                            confirmButtonColor: "#2563eb"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        this.errors = data.errors ? Object.values(data.errors).flat() : [data.message || 'Failed to save'];
                    }
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

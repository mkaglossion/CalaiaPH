<div 
    x-data="dayTourInventoryManager()"
    class="space-y-6"
>
    {{-- Section Header --}}
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Day Tour Inventory</h2>
            <p class="text-gray-500 text-sm mt-1">View and manage day tour availability by date</p>
        </div>
        <button 
            @click="showModal = true; resetForm()" 
            class="px-5 py-2.5 rounded-xl bg-brand text-white font-medium shadow hover:bg-[#a0675a] hover:shadow-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Inventory
        </button>
    </div>
    {{-- Bulk Actions Toolbar --}}
    <div x-show="selectedItems.length > 0" x-transition class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl flex justify-between items-center">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-blue-600 text-lg"></i>
            <span class="text-blue-900 font-medium" x-text="`${selectedItems.length} selected`"></span>
        </div>
        <div class="flex gap-3">
            <button 
                @click="bulkDeleteInventory()"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                <i class="fas fa-trash"></i> Delete Selected
            </button>
            <button 
                @click="clearSelection()"
                class="px-4 py-2 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 rounded-lg transition text-sm font-medium">
                Clear Selection
            </button>
        </div>
    </div>
    {{-- Day Tours with Inventory --}}
    @if($dayTours->count() > 0)
        <div class="space-y-6">
            @foreach($dayTours as $dayTour)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <!-- Day Tour Header -->
                    <div class="bg-linear-to-r from-brand/10 to-blue-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $dayTour->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Total Groups: <span class="font-semibold">{{ $dayTour->max_groups_per_day ?? 3 }} group{{ ($dayTour->max_groups_per_day ?? 3) > 1 ? 's' : '' }}</span>
                                </p>
                            </div>
                            <button 
                                @click="editDayTour({{ $dayTour->id }})"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-edit"></i> Manage
                            </button>
                        </div>
                    </div>

                    <!-- Inventory Table -->
                    @if($dayTour->inventory && count($dayTour->inventory) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 w-12">
                                            <input 
                                                type="checkbox"
                                                @change="toggleSelectAll($event, {{ $dayTour->id }})"
                                                :checked="isAllSelected({{ $dayTour->id }})"
                                                class="w-5 h-5 rounded border-gray-300 text-brand focus:ring-brand/20">
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Date</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Groups</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Booked</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Available</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="inv in getPaginatedInventory({{ $dayTour->id }}, getDayTourInventory({{ $dayTour->id }}))" :key="inv.id">
                                        <tr class="hover:bg-gray-50 transition" :class="isSelected(inv.id) ? 'bg-blue-50' : ''">
                                            <td class="px-4 py-3">
                                                <input 
                                                    type="checkbox"
                                                    @change="toggleSelect(inv.id)"
                                                    :checked="isSelected(inv.id)"
                                                    class="w-5 h-5 rounded border-gray-300 text-brand focus:ring-brand/20">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-gray-900" x-text="new Date(inv.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})"></span>
                                                    <span class="text-xs text-gray-400" x-text="'(' + new Date(inv.date).toLocaleDateString('en-US', {weekday: 'short'}) + ')'"></span>
                                                    <span x-show="isToday(inv.date)" class="inline-block px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Today</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-gray-900" x-text="inv.max_groups ?? 3"></td>
                                            <td class="px-6 py-4 font-semibold text-orange-600" x-text="inv.booked_groups ?? 0"></td>
                                            <td class="px-6 py-4">
                                                <span class="font-semibold" :class="(inv.available_groups ?? 0) > 0 ? 'text-green-600' : 'text-red-600'" x-text="inv.available_groups ?? 0"></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span x-show="inv.is_blocked" class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded">
                                                    <i class="fas fa-lock"></i> Blocked
                                                </span>
                                                <span x-show="!inv.is_blocked" class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                                    <i class="fas fa-unlock"></i> Open
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex gap-4">
                                                    <button 
                                                        @click="editInventory({{ $dayTour->id }}, inv.id)"
                                                        class="text-blue-600 hover:text-blue-700 font-semibold text-base hover:underline">
                                                        Edit
                                                    </button>
                                                    <button 
                                                        @click="deleteInventory(inv.id)"
                                                        class="text-red-600 hover:text-red-700 font-semibold text-base hover:underline">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="getPaginatedInventory({{ $dayTour->id }}, getDayTourInventory({{ $dayTour->id }})).length === 0">
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            <p>No inventory configured yet</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- Pagination Controls --}}
                            <template x-if="getTotalPages({{ $dayTour->id }}, getDayTourInventory({{ $dayTour->id }})) > 1">
                                <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200">
                                    <div class="text-sm text-gray-600">
                                        <span x-text="`Page ${currentPage[{{ $dayTour->id }}] || 1} of ${getTotalPages({{ $dayTour->id }}, getDayTourInventory({{ $dayTour->id }}))}`"></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button 
                                            @click="setPage({{ $dayTour->id }}, (currentPage[{{ $dayTour->id }}] || 1) - 1)"
                                            :disabled="(currentPage[{{ $dayTour->id }}] || 1) === 1"
                                            class="px-3 py-1 border border-gray-300 rounded text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <template x-for="page in getPageButtons({{ $dayTour->id }})" :key="page">
                                            <button 
                                                @click="setPage({{ $dayTour->id }}, page)"
                                                :class="(currentPage[{{ $dayTour->id }}] || 1) === page ? 'bg-brand text-white' : 'border border-gray-300 hover:bg-gray-100'"
                                                class="px-3 py-1 rounded text-sm font-medium"
                                                x-text="page">
                                            </button>
                                        </template>
                                        <button 
                                            @click="setPage({{ $dayTour->id }}, (currentPage[{{ $dayTour->id }}] || 1) + 1)"
                                            :disabled="(currentPage[{{ $dayTour->id }}] || 1) === getTotalPages({{ $dayTour->id }}, getDayTourInventory({{ $dayTour->id }}))"
                                            class="px-3 py-1 border border-gray-300 rounded text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @else
                        <div class="px-6 py-8 text-center">
                            <p class="text-gray-500 mb-3">No inventory slots configured</p>
                            <button 
                                @click="editDayTour({{ $dayTour->id }})"
                                class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                                Add Inventory
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600">No day tours available</p>
        </div>
    @endif

    {{-- Inventory Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition.opacity
        @click.away="showModal = false"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md border border-gray-200">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900" x-text="editMode ? 'Edit Inventory' : 'Add Inventory'"></h3>
                <button 
                    @click="showModal = false"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                >&times;</button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <form @submit.prevent="saveInventory" class="space-y-4">
                    @csrf
                    <input type="hidden" x-model="form.id" />
                    <input type="hidden" x-model="form.dayTourId" />

                    <!-- Day Tour Selection -->
                    <div x-show="!editMode">
                        <label for="tour_select" class="block font-semibold mb-2 text-gray-700 text-sm">Select Day Tour *</label>
                        <select 
                            id="tour_select"
                            name="day_tour_id"
                            x-model="form.dayTourId"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                            <option value="">-- Choose --</option>
                            @foreach($dayTours as $tour)
                                <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Mode -->
                    <div x-show="!editMode" class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" x-model="form.dateMode" value="specific" class="w-4 h-4 text-brand">
                            <span class="ml-2 text-sm font-medium">Single Date</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" x-model="form.dateMode" value="range" class="w-4 h-4 text-brand">
                            <span class="ml-2 text-sm font-medium">Date Range</span>
                        </label>
                    </div>

                    <!-- Specific Date -->
                    <div x-show="editMode || form.dateMode === 'specific'">
                        <label for="tour_date" class="block font-semibold mb-2 text-gray-700 text-sm">Date *</label>
                        <input type="date" id="tour_date" name="date" x-model="form.date" @focus="$el.select()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition" autocomplete="off">
                    </div>

                    <!-- Date Range -->
                    <div x-show="!editMode && form.dateMode === 'range'" class="space-y-3">
                        <div>
                            <label for="tour_date_from" class="block font-semibold mb-2 text-gray-700 text-sm">From Date *</label>
                            <input type="date" id="tour_date_from" name="date_from" x-model="form.dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                        <div>
                            <label for="tour_date_to" class="block font-semibold mb-2 text-gray-700 text-sm">To Date *</label>
                            <input type="date" id="tour_date_to" name="date_to" x-model="form.dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                    </div>

                    <!-- Total Groups -->
                    <div>
                        <label for="tour_groups" class="block font-semibold mb-2 text-gray-700 text-sm">Total Groups *</label>
                        <input type="number" id="tour_groups" name="max_groups" x-model.number="form.maxGroups" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Block Status -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isBlocked" x-model="form.isBlocked" class="w-4 h-4 rounded text-brand">
                        <label for="isBlocked" class="text-sm font-medium text-gray-700">Block this date</label>
                    </div>

                    <!-- Block Reason -->
                    <div x-show="form.isBlocked">
                        <label for="tour_block_reason" class="block font-semibold mb-2 text-gray-700 text-sm">Block Reason</label>
                        <input type="text" id="tour_block_reason" name="block_reason" x-model="form.blockReason" placeholder="e.g., Maintenance..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Errors -->
                    <div x-show="errors.length > 0" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <template x-for="error in errors">
                            <div class="text-red-700 text-xs" x-text="error"></div>
                        </template>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
                <button @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 text-sm font-medium">Cancel</button>
                <button @click="saveInventory()" :disabled="isLoading" class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium" :class="isLoading ? 'opacity-75' : 'hover:bg-[#a0675a]'">
                    <span x-text="isLoading ? 'Processing...' : (editMode ? 'Update' : 'Add')"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function dayTourInventoryManager() {
    return {
        isLoading: false,
        showModal: false,
        editMode: false,
        errors: [],
        selectedItems: [],
        dayTours: @js($dayTours->all()),
        itemsPerPage: {{ $itemsPerPage ?? 10 }},
        currentPage: {},
        form: {
            id: null,
            dayTourId: null,
            dateMode: 'specific',
            date: new Date().toISOString().split('T')[0],
            dateFrom: new Date().toISOString().split('T')[0],
            dateTo: new Date().toISOString().split('T')[0],
            maxGroups: 3,
            isBlocked: false,
            blockReason: ''
        },

        resetForm() {
            this.form = {
                id: null,
                dayTourId: null,
                dateMode: 'specific',
                date: new Date().toISOString().split('T')[0],
                dateFrom: new Date().toISOString().split('T')[0],
                dateTo: new Date().toISOString().split('T')[0],
                maxGroups: 3,
                isBlocked: false,
                blockReason: ''
            };
            this.editMode = false;
            this.errors = [];
        },

        refreshPage() {
            window.location.reload();
        },

        editDayTour(dayTourId) {
            this.resetForm();
            this.form.dayTourId = dayTourId;
            this.form.dateMode = 'specific';
            this.showModal = true;
        },

        editInventory(dayTourId, inventoryId) {
            this.editMode = true;
            this.form.dayTourId = dayTourId;
            this.form.id = inventoryId;
            this.form.dateMode = 'specific';
            
            // Load existing inventory data
            const dayTour = this.dayTours.find(d => d.id === dayTourId);
            if (dayTour && dayTour.inventory) {
                const inventory = dayTour.inventory.find(inv => inv.id === inventoryId);
                if (inventory) {
                    // Ensure date is in YYYY-MM-DD format for HTML5 date input
                    const dateStr = inventory.date.includes('T') 
                        ? inventory.date.split('T')[0] 
                        : inventory.date;
                    this.form.date = dateStr;
                    this.form.maxGroups = inventory.max_groups;
                    this.form.isBlocked = inventory.is_blocked;
                    this.form.blockReason = inventory.block_reason || '';
                }
            }
            
            this.showModal = true;
            
            // Auto-focus and select date field after modal renders
            this.$nextTick(() => {
                setTimeout(() => {
                    const dateInput = document.getElementById('tour_date');
                    if (dateInput) {
                        dateInput.focus();
                        dateInput.select();
                    }
                }, 50);
            });
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
                    
                    fetch(`/admin/day-tour-inventory/${id}`, {
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

        toggleSelect(id) {
            const index = this.selectedItems.indexOf(id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(id);
            }
        },

        isSelected(id) {
            return this.selectedItems.includes(id);
        },

        toggleSelectAll(event, dayTourId) {
            if (event.target.checked) {
                const dayTourData = this.dayTours.find(d => d.id === dayTourId);
                if (dayTourData && dayTourData.inventory) {
                    dayTourData.inventory.forEach(inv => {
                        if (!this.selectedItems.includes(inv.id)) {
                            this.selectedItems.push(inv.id);
                        }
                    });
                }
            } else {
                const dayTourData = this.dayTours.find(d => d.id === dayTourId);
                if (dayTourData && dayTourData.inventory) {
                    dayTourData.inventory.forEach(inv => {
                        const index = this.selectedItems.indexOf(inv.id);
                        if (index > -1) {
                            this.selectedItems.splice(index, 1);
                        }
                    });
                }
            }
        },

        isAllSelected(dayTourId) {
            const dayTourData = this.dayTours.find(d => d.id === dayTourId);
            if (!dayTourData || !dayTourData.inventories || dayTourData.inventories.length === 0) return false;
            return dayTourData.inventory.every(inv => this.selectedItems.includes(inv.id));
        },

        clearSelection() {
            this.selectedItems = [];
        },

        async bulkDeleteInventory() {
            if (this.selectedItems.length === 0) return;

            const result = await Swal.fire({
                title: 'Delete Multiple Items?',
                text: `Delete ${this.selectedItems.length} item(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete All',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) return;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            this.isLoading = true;
            let successCount = 0;
            let failureCount = 0;

            for (const id of this.selectedItems) {
                try {
                    const res = await fetch(`/admin/day-tour-inventory/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    });

                    if (res.ok) {
                        successCount++;
                    } else {
                        failureCount++;
                    }
                } catch (err) {
                    failureCount++;
                }
            }

            this.isLoading = false;

            Swal.fire({
                icon: failureCount === 0 ? 'success' : 'warning',
                title: failureCount === 0 ? 'Deleted!' : 'Partially Deleted',
                text: `${successCount} deleted${failureCount > 0 ? ` (${failureCount} failed)` : ''}`,
                confirmButtonColor: '#2563eb'
            }).then(() => {
                this.refreshPage();
            });
        },

        async saveInventory() {
            this.isLoading = true;
            this.errors = [];

            if (!this.form.dayTourId) {
                this.errors.push('Please select a day tour');
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
                    this.errors.push('Please select both dates');
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
                    const startDate = new Date(this.form.dateFrom);
                    const endDate = new Date(this.form.dateTo);
                    let currentDate = new Date(startDate);
                    let successCount = 0;
                    let failureCount = 0;

                    while (currentDate < endDate) {
                        const dateStr = currentDate.toISOString().split('T')[0];
                        
                        const payload = {
                            day_tour_id: this.form.dayTourId,
                            date: dateStr,
                            max_groups: this.form.maxGroups,
                            is_blocked: this.form.isBlocked,
                            block_reason: this.form.blockReason || null
                        };

                        try {
                            const res = await fetch('/admin/day-tour-inventory/store', {
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
                                    successCount++;
                                } else {
                                    failureCount++;
                                }
                            }
                        } catch (err) {
                            failureCount++;
                        }

                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    Swal.fire({
                        icon: failureCount === 0 ? "success" : "warning",
                        title: failureCount === 0 ? "Done!" : "Partial",
                        text: `${successCount} created${failureCount > 0 ? ` (${failureCount} failed)` : ''}`,
                        confirmButtonColor: "#2563eb"
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const payload = {
                        day_tour_id: this.form.dayTourId,
                        date: this.form.date,
                        max_groups: this.form.maxGroups,
                        is_blocked: this.form.isBlocked,
                        block_reason: this.form.blockReason || null
                    };

                    let url = '/admin/day-tour-inventory/store';
                    let method = 'POST';

                    if (this.editMode && this.form.id) {
                        url = `/admin/day-tour-inventory/${this.form.dayTourId}/update`;
                        method = 'POST';
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
                            confirmButtonColor: "#2563eb"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        this.errors = data.errors ? Object.values(data.errors).flat() : [data.message || 'Failed'];
                    }
                }
            } catch (err) {
                this.errors.push("An unexpected error occurred. Please try again.");
            } finally {
                this.isLoading = false;
            }
        },

        getPaginatedInventory(dayTourId, inventory) {
            const page = this.currentPage[dayTourId] || 1;
            const start = (page - 1) * this.itemsPerPage;
            const future = inventory.filter(inv => {
                const invDate = new Date(inv.date);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return invDate >= today;
            }).sort((a, b) => new Date(a.date) - new Date(b.date));
            return future.slice(start, start + this.itemsPerPage);
        },

        getTotalPages(dayTourId, inventory) {
            const future = inventory.filter(inv => {
                const invDate = new Date(inv.date);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return invDate >= today;
            });
            return Math.ceil(future.length / this.itemsPerPage);
        },

        setPage(dayTourId, page) {
            const totalPages = this.getTotalPages(dayTourId, this.getDayTourInventory(dayTourId));
            if (page >= 1 && page <= totalPages) {
                this.currentPage[dayTourId] = page;
            }
        },

        getDayTourInventory(dayTourId) {
            const dayTour = this.dayTours.find(dt => dt.id === dayTourId);
            return dayTour ? dayTour.inventory : [];
        },

        getPageButtons(dayTourId) {
            const totalPages = this.getTotalPages(dayTourId, this.getDayTourInventory(dayTourId));
            const currentPage = this.currentPage[dayTourId] || 1;
            const buttons = [];
            for (let i = 1; i <= totalPages; i++) {
                buttons.push(i);
            }
            return buttons;
        },

        isToday(dateStr) {
            const date = new Date(dateStr);
            const today = new Date();
            return date.getFullYear() === today.getFullYear() &&
                   date.getMonth() === today.getMonth() &&
                   date.getDate() === today.getDate();
        }
    };
}
</script>

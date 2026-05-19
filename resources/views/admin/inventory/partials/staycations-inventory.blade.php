<div 
    x-data="staycationInventoryManager()"
    class="space-y-6"
>
    {{-- Section Header --}}
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Staycation Inventory</h2>
            <p class="text-gray-500 text-sm mt-1">View and manage staycation room availability by date</p>
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
    {{-- Staycations with Inventory --}}
    @if($staycations->count() > 0)
        <div class="space-y-6">
            @foreach($staycations as $staycation)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <!-- Staycation Header -->
                    <div class="bg-linear-to-r from-brand/10 to-blue-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $staycation->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Quantity: <span class="font-semibold">{{ $staycation->base_quantity }} {{ in_array($staycation->name, ['BYOT', 'Car Camping']) ? 'slot' : 'room' }}{{ $staycation->base_quantity > 1 ? 's' : '' }}</span>
                                </p>
                            </div>
                            <button 
                                @click="editStaycation({{ $staycation->id }})"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                                <i class="fas fa-edit"></i> Manage
                            </button>
                        </div>
                    </div>

                    <!-- Inventory Table -->
                    @if($staycation->inventories && count($staycation->inventories) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 w-12">
                                            <input 
                                                type="checkbox"
                                                @change="toggleSelectAll($event, {{ $staycation->id }})"
                                                :checked="isAllSelected({{ $staycation->id }})"
                                                class="w-5 h-5 rounded border-gray-300 text-brand focus:ring-brand/20">
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Date</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700" x-text="'{{ $staycation->name }}' === 'BYOT' || '{{ $staycation->name }}' === 'Car Camping' ? 'Total Slots' : 'Total Rooms'"></th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Available</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="inv in getPaginatedInventory({{ $staycation->id }}, getStaycationInventory({{ $staycation->id }}))" :key="inv.id">
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
                                            <td class="px-6 py-4 font-semibold text-gray-900" x-text="inv.total_quantity ?? 0"></td>
                                            <td class="px-6 py-4">
                                                <span class="font-semibold" :class="(inv.available_quantity ?? 0) > 0 ? 'text-green-600' : 'text-red-600'" x-text="inv.available_quantity ?? 0"></span>
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
                                                        @click="editInventory({{ $staycation->id }}, inv.id)"
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
                                    <tr x-show="getPaginatedInventory({{ $staycation->id }}, getStaycationInventory({{ $staycation->id }})).length === 0">
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            <p>No inventory configured yet</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- Pagination Controls --}}
                            <template x-if="getTotalPages({{ $staycation->id }}, getStaycationInventory({{ $staycation->id }})) > 1">
                                <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200">
                                    <div class="text-sm text-gray-600">
                                        <span x-text="`Page ${currentPage[{{ $staycation->id }}] || 1} of ${getTotalPages({{ $staycation->id }}, getStaycationInventory({{ $staycation->id }}))}`"></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button 
                                            @click="setPage({{ $staycation->id }}, (currentPage[{{ $staycation->id }}] || 1) - 1)"
                                            :disabled="(currentPage[{{ $staycation->id }}] || 1) === 1"
                                            class="px-3 py-1 border border-gray-300 rounded text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <template x-for="page in getPageButtons({{ $staycation->id }})" :key="page">
                                            <button 
                                                @click="setPage({{ $staycation->id }}, page)"
                                                :class="(currentPage[{{ $staycation->id }}] || 1) === page ? 'bg-brand text-white' : 'border border-gray-300 hover:bg-gray-100'"
                                                class="px-3 py-1 rounded text-sm font-medium"
                                                x-text="page">
                                            </button>
                                        </template>
                                        <button 
                                            @click="setPage({{ $staycation->id }}, (currentPage[{{ $staycation->id }}] || 1) + 1)"
                                            :disabled="(currentPage[{{ $staycation->id }}] || 1) === getTotalPages({{ $staycation->id }}, getStaycationInventory({{ $staycation->id }}))"
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
                                @click="editStaycation({{ $staycation->id }})"
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
            <p class="text-gray-600">No staycations available</p>
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
                    <input type="hidden" x-model="form.staycationId" />

                    <!-- Staycation Selection -->
                    <div x-show="!editMode">
                        <label for="stay_select" class="block font-semibold mb-2 text-gray-700 text-sm">Select Staycation *</label>
                        <select 
                            id="stay_select"
                            name="staycation_id"
                            x-model="form.staycationId"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                            <option value="">-- Choose --</option>
                            @foreach($staycations as $stay)
                                <option value="{{ $stay->id }}">{{ $stay->name }}</option>
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
                        <label for="stay_date" class="block font-semibold mb-2 text-gray-700 text-sm">Date *</label>
                        <input type="date" id="stay_date" name="date" x-model="form.date" @focus="$el.select()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition" autocomplete="off">
                    </div>

                    <!-- Date Range -->
                    <div x-show="!editMode && form.dateMode === 'range'" class="space-y-3">
                        <div>
                            <label for="stay_date_from" class="block font-semibold mb-2 text-gray-700 text-sm">From Date *</label>
                            <input type="date" id="stay_date_from" name="date_from" x-model="form.dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                        <div>
                            <label for="stay_date_to" class="block font-semibold mb-2 text-gray-700 text-sm">To Date *</label>
                            <input type="date" id="stay_date_to" name="date_to" x-model="form.dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                        </div>
                    </div>

                    <!-- Total Quantity -->
                    <div>
                        <label for="stay_quantity" class="block font-semibold mb-2 text-gray-700 text-sm">
                            <span x-text="getQuantityLabel()"></span> *
                        </label>
                        <input type="number" id="stay_quantity" name="total_quantity" x-model.number="form.totalQuantity" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
                    </div>

                    <!-- Block Status -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isBlocked" x-model="form.isBlocked" class="w-4 h-4 rounded text-brand">
                        <label for="isBlocked" class="text-sm font-medium text-gray-700">Block this date</label>
                    </div>

                    <!-- Block Reason -->
                    <div x-show="form.isBlocked">
                        <label for="stay_block_reason" class="block font-semibold mb-2 text-gray-700 text-sm">Block Reason</label>
                        <input type="text" id="stay_block_reason" name="block_reason" x-model="form.blockReason" placeholder="e.g., Maintenance..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 transition">
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
                <button @click="saveInventory()" :disabled="isLoading" class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium"  :class="isLoading ? 'opacity-75' : 'hover:bg-[#a0675a]'">
                    <span x-text="isLoading ? 'Processing...' : (editMode ? 'Update' : 'Add')"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function staycationInventoryManager() {
    return {
        isLoading: false,
        showModal: false,
        editMode: false,
        errors: [],
        selectedItems: [],
        staycations: @js($staycations->all()),
        itemsPerPage: {{ $itemsPerPage ?? 10 }},
        currentPage: {},
        form: {
            id: null,
            staycationId: null,
            dateMode: 'specific',
            date: new Date().toISOString().split('T')[0],
            dateFrom: new Date().toISOString().split('T')[0],
            dateTo: new Date().toISOString().split('T')[0],
            totalQuantity: 1,
            isBlocked: false,
            blockReason: ''
        },

        resetForm() {
            this.form = {
                id: null,
                staycationId: null,
                dateMode: 'specific',
                date: new Date().toISOString().split('T')[0],
                dateFrom: new Date().toISOString().split('T')[0],
                dateTo: new Date().toISOString().split('T')[0],
                totalQuantity: 1,
                isBlocked: false,
                blockReason: ''
            };
            this.editMode = false;
            this.errors = [];
        },

        normalizeDate(dateValue) {
            if (!dateValue) return '';
            const date = new Date(dateValue);
            if (Number.isNaN(date.getTime())) return '';
            return date.toISOString().split('T')[0];
        },

        sortInventories(staycation) {
            if (!staycation || !Array.isArray(staycation.inventories)) return;
            staycation.inventories.sort((a, b) => new Date(a.date) - new Date(b.date));
        },

        applyInventoryRecord(record, fallbackStaycationId = null) {
            const staycationId = Number(record?.staycation_id ?? fallbackStaycationId);
            if (!staycationId) return;

            const staycation = this.staycations.find(s => Number(s.id) === staycationId);
            if (!staycation) return;

            if (!Array.isArray(staycation.inventories)) {
                staycation.inventories = [];
            }

            const normalized = {
                ...record,
                date: this.normalizeDate(record.date),
                staycation_id: staycationId,
            };

            const idx = staycation.inventories.findIndex(inv => Number(inv.id) === Number(normalized.id));
            if (idx >= 0) {
                staycation.inventories.splice(idx, 1, normalized);
            } else {
                staycation.inventories.push(normalized);
            }
            this.sortInventories(staycation);
        },

        removeInventoryRecordById(id) {
            this.staycations.forEach(staycation => {
                if (!Array.isArray(staycation.inventories)) return;
                staycation.inventories = staycation.inventories.filter(inv => Number(inv.id) !== Number(id));
            });
            this.selectedItems = this.selectedItems.filter(itemId => Number(itemId) !== Number(id));
        },

        editStaycation(staycationId) {
            this.resetForm();
            this.form.staycationId = staycationId;
            this.form.dateMode = 'specific';
            this.showModal = true;
        },

        editInventory(staycationId, inventoryId) {
            this.editMode = true;
            this.form.staycationId = staycationId;
            this.form.id = inventoryId;
            this.form.dateMode = 'specific';
            
            // Load existing inventory data
            const staycation = this.staycations.find(s => s.id === staycationId);
            if (staycation && staycation.inventories) {
                const inventory = staycation.inventories.find(inv => inv.id === inventoryId);
                if (inventory) {
                    // Ensure date is in YYYY-MM-DD format for HTML5 date input
                    const dateStr = inventory.date.includes('T') 
                        ? inventory.date.split('T')[0] 
                        : inventory.date;
                    this.form.date = dateStr;
                    this.form.totalQuantity = inventory.total_quantity;
                    this.form.isBlocked = inventory.is_blocked;
                    this.form.blockReason = inventory.block_reason || '';
                }
            }
            
            this.showModal = true;
            
            // Auto-focus and select date field after modal renders
            this.$nextTick(() => {
                setTimeout(() => {
                    const dateInput = document.getElementById('stay_date');
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
                    
                    fetch(`/admin/stay/inventory/${id}`, {
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
                        });
                        this.removeInventoryRecordById(id);
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

        toggleSelectAll(event, staycationId) {
            if (event.target.checked) {
                const staycationData = this.staycations.find(s => s.id === staycationId);
                if (staycationData && staycationData.inventories) {
                    staycationData.inventories.forEach(inv => {
                        if (!this.selectedItems.includes(inv.id)) {
                            this.selectedItems.push(inv.id);
                        }
                    });
                }
            } else {
                const staycationData = this.staycations.find(s => s.id === staycationId);
                if (staycationData && staycationData.inventories) {
                    staycationData.inventories.forEach(inv => {
                        const index = this.selectedItems.indexOf(inv.id);
                        if (index > -1) {
                            this.selectedItems.splice(index, 1);
                        }
                    });
                }
            }
        },

        isAllSelected(staycationId) {
            const staycationData = this.staycations.find(s => s.id === staycationId);
            if (!staycationData || !staycationData.inventories || staycationData.inventories.length === 0) return false;
            return staycationData.inventories.every(inv => this.selectedItems.includes(inv.id));
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
            let responseMessage = '';

            try {
                const res = await fetch('/admin/stay/inventory/bulk-delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: this.selectedItems })
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    successCount = Number(data.deleted ?? this.selectedItems.length);
                    if (successCount > this.selectedItems.length) {
                        successCount = this.selectedItems.length;
                    }
                    failureCount = this.selectedItems.length - successCount;
                    responseMessage = data.message || '';
                    this.selectedItems.forEach(id => this.removeInventoryRecordById(id));
                    this.clearSelection();
                } else {
                    failureCount = this.selectedItems.length;
                    responseMessage = data.message || '';
                }
            } catch (err) {
                failureCount = this.selectedItems.length;
            }

            this.isLoading = false;

            Swal.fire({
                icon: failureCount === 0 ? 'success' : 'warning',
                title: failureCount === 0 ? 'Deleted!' : 'Partially Deleted',
                text: responseMessage || `${successCount} deleted${failureCount > 0 ? ` (${failureCount} failed)` : ''}`,
                confirmButtonColor: '#2563eb'
            });
        },

        async saveInventory() {
            this.isLoading = true;
            this.errors = [];

            if (!this.form.staycationId) {
                this.errors.push('Please select a staycation');
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
                    const payload = {
                        staycation_id: Number(this.form.staycationId),
                        date_from: this.form.dateFrom,
                        date_to: this.form.dateTo,
                        total_quantity: Number(this.form.totalQuantity),
                        is_blocked: this.form.isBlocked,
                        block_reason: this.form.blockReason || null
                    };

                    const res = await fetch('/admin/stay/inventory/bulk-upsert', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (res.ok) {
                        (data.data || []).forEach(inv => this.applyInventoryRecord(inv, payload.staycation_id));
                        this.showModal = false;
                        this.resetForm();
                        await Swal.fire({
                            icon: "success",
                            title: "Done!",
                            text: data.message || 'Inventory updated successfully.',
                            confirmButtonColor: "#2563eb"
                        });
                        window.location.reload();
                    } else {
                        this.errors = data.errors ? Object.values(data.errors).flat() : [data.message || 'Failed'];
                    }
                } else {
                    const payload = {
                        staycation_id: Number(this.form.staycationId),
                        date: this.form.date,
                        total_quantity: Number(this.form.totalQuantity),
                        is_blocked: this.form.isBlocked,
                        block_reason: this.form.blockReason || null
                    };

                    let url = '/admin/stay/inventory';
                    let method = 'POST';

                    if (this.editMode && this.form.id) {
                        url = `/admin/stay/inventory/${this.form.id}`;
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
                        this.applyInventoryRecord(data.data, payload.staycation_id);
                        this.showModal = false;
                        this.resetForm();
                        await Swal.fire({
                            icon: "success",
                            title: this.editMode ? "Updated!" : "Created!",
                            confirmButtonColor: "#2563eb"
                        });
                        window.location.reload();
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

        getPaginatedInventory(staycationId, inventory) {
            const page = this.currentPage[staycationId] || 1;
            const start = (page - 1) * this.itemsPerPage;
            const future = inventory.filter(inv => {
                const invDate = new Date(inv.date);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return invDate >= today;
            }).sort((a, b) => new Date(a.date) - new Date(b.date));
            return future.slice(start, start + this.itemsPerPage);
        },

        getTotalPages(staycationId, inventory) {
            const future = inventory.filter(inv => {
                const invDate = new Date(inv.date);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return invDate >= today;
            });
            return Math.ceil(future.length / this.itemsPerPage);
        },

        setPage(staycationId, page) {
            const totalPages = this.getTotalPages(staycationId, this.getStaycationInventory(staycationId));
            if (page >= 1 && page <= totalPages) {
                this.currentPage[staycationId] = page;
            }
        },

        getStaycationInventory(staycationId) {
            const staycation = this.staycations.find(s => s.id === staycationId);
            return staycation ? staycation.inventories : [];
        },

        getPageButtons(staycationId) {
            const totalPages = this.getTotalPages(staycationId, this.getStaycationInventory(staycationId));
            const buttons = [];
            for (let i = 1; i <= totalPages; i++) {
                buttons.push(i);
            }
            return buttons;
        },

        getQuantityLabel() {
            if (!this.form.staycationId) return 'Total Rooms';
            const staycationId = parseInt(this.form.staycationId);
            const staycation = this.staycations.find(s => s.id === staycationId);
            if (!staycation) return 'Total Rooms';
            const isByotOrCamping = staycation.name.trim() === 'BYOT' || staycation.name.trim() === 'Car Camping';
            return isByotOrCamping ? 'Total Slots' : 'Total Rooms';
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

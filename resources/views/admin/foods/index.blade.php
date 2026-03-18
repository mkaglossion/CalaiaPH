@extends('layouts.admin')

@section('title', 'Food Management')

@section('content')
<style>
    /* Toast notifications */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOut { to { transform: translateX(400px); opacity: 0; } }
    .toast.hide { animation: slideOut 0.3s ease-out forwards; }
    .toast.success { background: #10b981; color: white; }
    .toast.error { background: #ef4444; color: white; }
    .toast.info { background: #3b82f6; color: white; }
    
    /* Undo notification */
    .undo-banner {
        position: fixed;
        bottom: 20px;
        left: 20px;
        padding: 16px 20px;
        background: #374151;
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 9998;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .undo-banner button {
        background: #fbbf24;
        color: #1f2937;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }
    .undo-banner button:hover { background: #fcd34d; }
</style>
<div
    x-data="foodCrud()"
    x-init="setupKeyboardShortcuts()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Food Management
    </h1>
    <p class="text-gray-600 mb-10">Manage food & beverage items, categories, prices, and inventory.</p>

    {{-- ============================================================= --}}
    {{-- FOOD ITEMS SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Food & Beverage Items</h2>
            <p class="text-gray-500 text-sm">Manage all food and beverage items available for booking.</p>
        </div>

        <button 
            type="button" 
            @click="openCreate()" 
            class="px-6 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Food Item
        </button>
    </div>
    
    <div class="mb-6">
        <input 
            x-model="searchQuery"
            @input="filterFoods()"
            type="text" 
            placeholder="🔍 Search by name or category..." 
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition"
        >
    </div>

    @if ($message = Session::get('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-6 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-6 py-3" onclick="this.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Food Items Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full text-left table-fixed lg:table-auto">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700 w-12">#</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700 min-w-37.5">Name</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700">Category</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700">Price</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700">Stock</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700 w-20">Image</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700">Special</th>
                        <th class="px-2 lg:px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                    </tr>
                </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($foods as $index => $food)
                    <tr 
                        class="hover:bg-gray-50/70 transition"
                        x-show="!searchQuery || '{{ ($food->name ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase()) || '{{ ($food->category ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase())"
                        x-cloak
                    >
                        <td class="px-2 lg:px-6 py-4 font-medium text-gray-900">{{ $foods->firstItem() ? $foods->firstItem() + $index : $index + 1 }}</td>
                        <td class="px-2 lg:px-6 py-4">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 wrap-break-word">{{ $food->name }}</div>
                                @if($food->description)
                                    <div class="text-gray-600 text-sm wrap-break-word line-clamp-2">{{ $food->description }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 lg:px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    {{ ucfirst($food->category) }}
                                </span>
                                @if($food->subcategory)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($food->subcategory) }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 lg:px-6 py-4 font-medium text-gray-900">₱{{ number_format($food->price, 2) }}</td>
                        <td class="px-2 lg:px-6 py-4">
                            @if($food->is_inventory_managed)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $food->stock_quantity }} left
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Not tracked
                                </span>
                            @endif
                        </td>
                        <td class="px-2 lg:px-6 py-4">
                            @if ($food->image)
                                <img src="{{ $food->image }}" alt="{{ $food->name }}" class="h-10 w-10 rounded object-cover" loading="lazy">
                            @else
                                <span class="text-gray-400 text-xs">No image</span>
                            @endif
                        </td>
                        <td class="px-2 lg:px-6 py-4">
                            @if ($food->is_active)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-2 lg:px-6 py-4">
                            @if ($food->is_special_option)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i data-lucide="star" class="w-3 h-3 mr-1"></i> Special
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Regular
                                </span>
                            @endif
                        </td>
                        <td class="px-2 lg:px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 lg:gap-4 flex-wrap">
                                <button type="button"
                                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-sm"
                                        @click="openEdit({{ $food->id }})">
                                    Edit
                                </button>
                                <button type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-sm"
                                        @click="deleteFood({{ $food->id }}, '{{ $food->name }}')">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                <p>No food items found.</p>
                                <button type="button" @click="openCreate()" class="text-blue-600 hover:underline font-medium text-sm">Create your first food item →</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>

        @if ($foods->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $foods->links('pagination::tailwind') }}
            </div>
        @endif
    </div>



    {{-- Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div @click.away="showModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-3xl md:max-w-4xl max-h-[90vh] overflow-y-auto p-7 border border-gray-200">
            <button 
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Food Item' : 'Create Food Item'"></h3>

            <form class="p-4 sm:p-6 space-y-6" @submit.prevent="submitForm">
                <div>
                    <label for="food_name" class="block text-sm font-semibold text-gray-700 mb-2">Food Name *</label>
                    <input type="text" x-model="form.name" id="food_name" name="name" required
                           autocomplete="name"
                           class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="e.g., Breakfast Buffet">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="food_category" class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                        <select x-model="form.category" id="food_category" name="category" required
                                autocomplete="category"
                                class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            <option value="messhall">Messhall</option>
                            <option value="cook_your_own">Cook Your Own</option>
                        </select>
                    </div>

                    <div>
                        <label for="food_subcategory" class="block text-sm font-semibold text-gray-700 mb-2">Subcategory</label>
                        <input type="text" x-model="form.subcategory" id="food_subcategory" name="subcategory"
                               autocomplete="off"
                               class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="e.g., beef, pork, vegetables">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="food_price" class="block text-sm font-semibold text-gray-700 mb-2">Price (₱) *</label>
                        <input type="number" step="0.01" min="0" x-model="form.price" id="food_price" name="price" required
                               autocomplete="price"
                               class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="1500">
                    </div>

                    <div>
                        <label for="food_stock_quantity" class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity</label>
                        <input type="number" min="0" x-model="form.stock_quantity" id="food_stock_quantity" name="stock_quantity"
                               autocomplete="off"
                               class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="50">
                    </div>

                    <div class="flex items-end">
                        <label for="food_is_inventory_managed" class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="form.is_inventory_managed" id="food_is_inventory_managed" name="is_inventory_managed"
                                   autocomplete="off"
                                   class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <div>
                                <p class="font-semibold text-gray-900">Manage Inventory</p>
                                <p class="text-xs text-gray-600">Track stock levels</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="food_description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea rows="4" x-model="form.description" id="food_description" name="description"
                              autocomplete="off"
                              class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                              placeholder="Describe your food item..."></textarea>
                </div>

                <div>
                    <label for="food_image" class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                         @click="$refs.imageInput.click()">
                        <template x-if="form.image_preview">
                            <div class="mb-4 flex flex-col items-center gap-2">
                                <img :src="form.image_preview" alt="Preview" class="max-h-40 rounded-lg shadow">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="form.remove_image" id="food_remove_image" name="remove_image" class="rounded">
                                    <span>Remove current image</span>
                                </label>
                            </div>
                        </template>

                        <template x-if="!form.image_preview">
                            <div class="flex flex-col items-center gap-2 text-center">
                                <i data-lucide="cloud-upload" class="w-8 h-8 text-gray-400"></i>
                                <p class="text-gray-600 font-medium">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </template>

                        <input type="file" accept="image/*" class="hidden" x-ref="imageInput" id="food_image" name="image" @change="handleFile">
                    </div>
                </div>

                <div class="space-y-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" id="food_is_active" name="is_active"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-semibold text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-600">When checked, this item will be visible on the booking page</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_special_option" id="food_is_special_option" name="is_special_option"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div>
                            <p class="font-semibold text-gray-900">Special Option</p>
                            <p class="text-xs text-gray-600">Mark this as a special option (e.g., Cook Your Own)</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showModal = false" class="px-6 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading"
                            class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition disabled:opacity-60"
                            x-text="loading ? 'Saving...' : (editMode ? 'Update Food Item' : 'Create Food Item')">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function foodCrud() {
            return {
                showModal: false,
                editMode: false,
                loading: false,
                searchQuery: '',
                form: {
                    id: null,
                    name: '',
                    category: '',
                    subcategory: '',
                    price: '',
                    description: '',
                    is_active: true,
                    is_special_option: false,
                    stock_quantity: '',
                    is_inventory_managed: false,
                    image: null,
                    image_preview: null,
                    remove_image: false
                },
                
                // Toast notifications
                showToast(message, type = 'info') {
                    const toast = document.createElement('div');
                    toast.className = `toast ${type}`;
                    toast.innerHTML = `
                        <span>${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
                        <span>${message}</span>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.classList.add('hide');
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                },
                
                // Keyboard shortcuts
                setupKeyboardShortcuts() {
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            this.showModal = false;
                        } else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                            e.preventDefault();
                            const form = document.querySelector('#foodForm');
                            if (form) form.dispatchEvent(new Event('submit'));
                        }
                    });
                },
                
                // Search/Filter
                filterFoods() {
                    // Filtering handled by x-show directives on table rows
                },
                
                // Delete with undo
                deleteFood(id, name) {
                    this.scheduleDeleteFood(id, name);
                },
                
                scheduleDeleteFood(id, name) {
                    const banner = document.createElement('div');
                    banner.className = 'undo-banner';
                    let secondsLeft = 5;
                    banner.innerHTML = `
                        <span>Food item "${name}" marked for deletion</span>
                        <button id="undo_food_${id}">Undo (${secondsLeft}s)</button>
                    `;
                    document.body.appendChild(banner);
                    
                    const btn = banner.querySelector(`#undo_food_${id}`);
                    let timer = setInterval(() => {
                        secondsLeft--;
                        btn.textContent = `Undo (${secondsLeft}s)`;
                        if (secondsLeft <= 0) {
                            clearInterval(timer);
                            banner.remove();
                            this.confirmDeleteFood(id);
                        }
                    }, 1000);

                    btn.addEventListener('click', () => {
                        clearInterval(timer);
                        banner.remove();
                    });
                },
                
                confirmDeleteFood(id) {
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    fetch(`{{ route('admin.foods.destroy', ':id') }}`.replace(':id', id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({
                            '_method': 'DELETE'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.showToast('Food item deleted successfully!', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            this.showToast(data.message || 'Failed to delete food item.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showToast('An error occurred while deleting the food item.', 'error');
                    });
                },

                openCreate() {
                    this.resetForm();
                    this.showModal = true;
                },

                openEdit(foodId) {
                    this.resetForm();
                    this.loading = true;
                    
                    fetch(`/admin/foods/${foodId}`, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(food => {
                        this.editMode = true;
                        this.form.id = food.id;
                        this.form.name = food.name || '';
                        this.form.category = food.category || '';
                        this.form.subcategory = food.subcategory || '';
                        this.form.price = food.price ?? '';
                        this.form.description = food.description || '';
                        this.form.is_active = !!food.is_active;
                        this.form.is_special_option = !!food.is_special_option;
                        this.form.stock_quantity = food.stock_quantity ?? '';
                        this.form.is_inventory_managed = !!food.is_inventory_managed;
                        this.form.image_preview = food.image || null;
                        this.showModal = true;
                    })
                    .catch(() => this.showToast('Failed to load food data', 'error'))
                    .finally(() => this.loading = false);
                },

                resetForm() {
                    this.editMode = false;
                    this.loading = false;
                    this.form = {
                        id: null,
                        name: '',
                        category: '',
                        subcategory: '',
                        price: '',
                        description: '',
                        is_active: true,
                        is_special_option: false,
                        stock_quantity: '',
                        is_inventory_managed: false,
                        image: null,
                        image_preview: null,
                        remove_image: false
                    };
                    if (this.$refs.imageInput) {
                        this.$refs.imageInput.value = '';
                    }
                },

                handleFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.form.image = file;
                    this.form.image_preview = URL.createObjectURL(file);
                    this.form.remove_image = false;
                },

                async submitForm() {
                    if (this.loading) return;
                    this.loading = true;

                    const formData = new FormData();
                    formData.append('name', this.form.name);
                    formData.append('category', this.form.category);
                    formData.append('subcategory', this.form.subcategory ?? '');
                    formData.append('price', this.form.price ?? '');
                    formData.append('description', this.form.description ?? '');
                    formData.append('is_active', this.form.is_active ? 1 : 0);
                    formData.append('is_special_option', this.form.is_special_option ? 1 : 0);
                    formData.append('stock_quantity', this.form.stock_quantity ?? '');
                    formData.append('is_inventory_managed', this.form.is_inventory_managed ? 1 : 0);
                    if (this.form.image) {
                        console.log('Image file being added:', this.form.image);
                        formData.append('image', this.form.image);
                    }
                    if (this.form.remove_image) formData.append('remove_image', 1);

                    let url = '{{ route('admin.foods.store') }}';
                    if (this.editMode && this.form.id) {
                        url = `{{ route('admin.foods.update', ':id') }}`.replace(':id', this.form.id);
                        formData.append('_method', 'PUT');
                    }

                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        console.log('Response:', data);

                        if (res.ok) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || 'Food item saved successfully!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                this.showModal = false;
                                window.location.reload();
                            });
                        } else {
                            const errorMsg = data.message || data.errors?.name?.[0] || 'Failed to save food item.';
                            console.error('Server error:', data.errors);
                            Swal.fire({
                                title: 'Error!',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while saving the food item. Check the browser console for details.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</div>

@endsection

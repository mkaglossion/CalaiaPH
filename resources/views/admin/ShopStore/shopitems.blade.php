@extends('layouts.admin')

@section('title', 'Store Management')

@section('content')
<style>
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
    x-data="productCrud()"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Store Management
    </h1>
    <p class="text-gray-600 mb-10">Manage store products, categories, prices, and inventory.</p>

    {{-- ============================================================= --}}
    {{-- PRODUCTS SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Store Products</h2>
            <p class="text-gray-500 text-sm">Manage all products available for purchase.</p>
        </div>

        <button 
            type="button" 
            @click="openCreate()" 
            class="px-6 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Product
        </button>
    </div>

    {{-- Search Box --}}
    <div class="mb-6">
        <input 
            x-model="searchQuery"
            @input="filterProducts()"
            type="text" 
            placeholder="🔍 Search by name, category, or description..." 
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition"
        >
    </div>

    @if(session('success'))
        <script>
            toastr.success("{{ session('success') }}", 'Success');
        </script>
    @endif

    @if(session('error'))
        <script>
            toastr.error("{{ session('error') }}", 'Error');
        </script>
    @endif

    {{-- Products Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="w-12 px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap">#</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Title</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap">Category</th>
                        <th class="w-32 px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap">Price</th>
                        <th class="w-32 px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap">Stock</th>
                        <th class="w-24 px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap">Image</th>
                        <th class="w-32 px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap">Status</th>
                        <th class="w-36 px-6 py-4 text-sm font-semibold text-gray-700 whitespace-nowrap text-right">Actions</th>
                    </tr>
                </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($products as $index => $product)
                    <tr 
                        class="hover:bg-gray-50/70 transition"
                        x-show="!searchQuery || 
                            '{{ ($product->title ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            '{{ ($product->category ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            '{{ ($product->description ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase())"
                    >
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $products->firstItem() ? $products->firstItem() + $index : $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 wrap-break-word">{{ $product->title }}</div>
                                @if($product->description)
                                    <div class="text-gray-600 text-sm wrap-break-word">{{ $product->description }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ ucfirst($product->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">₱{{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($product->is_inventory_managed)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $product->stock_quantity }} left
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Not tracked
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($product->image)
                                @php
                                    $imageSrc = $product->image;
                                    if ($imageSrc && !str_contains($imageSrc, 'res.cloudinary.com')) {
                                        $imageSrc = asset('storage/' . $imageSrc);
                                    }
                                @endphp
                                <img src="{{ $imageSrc }}" alt="{{ $product->title }}" class="h-10 w-10 rounded object-cover" loading="lazy">
                            @else
                                <span class="text-gray-400 text-xs">No image</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($product->is_active)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-4">
                                <button type="button"
                                        class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                                        @click="openEdit({
                                            id: {{ $product->id }},
                                            title: @js($product->title),
                                            description: @js($product->description),
                                            price: {{ $product->price ?? 0 }},
                                            category: @js($product->category),
                                            image: @js($product->image),
                                            is_active: {{ $product->is_active ? 'true' : 'false' }},
                                            stock_quantity: {{ $product->stock_quantity ?? '' }},
                                            is_inventory_managed: {{ $product->is_inventory_managed ? 'true' : 'false' }}
                                        })">
                                    Edit
                                </button>
                                <button type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                        onclick="deleteProductItem({{ $product->id }}, '{{ addslashes($product->title) }}')">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                <p>No products found.</p>
                                <button type="button" @click="openCreate()" class="text-blue-600 hover:underline font-medium text-sm">Create your first product →</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $products->links('pagination::tailwind') }}
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

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Product' : 'Create Product'"></h3>

            <form class="p-4 sm:p-6 space-y-6" @submit.prevent="submitForm">
                <div>
                    <label for="product_title" class="block text-sm font-semibold text-gray-700 mb-2">Product Title *</label>
                    <input type="text" x-model="form.title" id="product_title" name="title" required
                           autocomplete="name"
                           class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="e.g., Fresh Organic Vegetables">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="product_category" class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                        <select x-model="form.category" id="product_category" name="category" required
                                autocomplete="category"
                                class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            <option value="frozen">Frozen</option>
                            <option value="fresh">Fresh</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="product_price" class="block text-sm font-semibold text-gray-700 mb-2">Price (₱) *</label>
                        <input type="number" step="0.01" min="0" x-model="form.price" id="product_price" name="price" required
                               autocomplete="price"
                               class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="1500">
                    </div>

                    <div>
                        <label for="product_stock_quantity" class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity</label>
                        <input type="number" min="0" x-model="form.stock_quantity" id="product_stock_quantity" name="stock_quantity"
                               autocomplete="off"
                               class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="50">
                    </div>

                    <div class="flex items-end">
                        <label for="product_is_inventory_managed" class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="form.is_inventory_managed" id="product_is_inventory_managed" name="is_inventory_managed"
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
                    <label for="product_description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea rows="4" x-model="form.description" id="product_description" name="description"
                              autocomplete="off"
                              class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                              placeholder="Describe your product..."></textarea>
                </div>

                <div>
                    <label for="product_image" class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                         @click="$refs.imageInput.click()">
                        <template x-if="form.image_preview">
                            <div class="mb-4 flex flex-col items-center gap-2">
                                <img :src="form.image_preview" alt="Preview" class="max-h-40 rounded-lg shadow">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="form.remove_image" id="product_remove_image" name="remove_image" class="rounded">
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

                        <input type="file" accept="image/*" class="hidden" x-ref="imageInput" id="product_image" name="image" @change="handleFile">
                    </div>
                </div>

                <div class="space-y-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" id="product_is_active" name="is_active"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-semibold text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-600">When checked, this item will be visible on the store page</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showModal = false" class="px-6 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading"
                            class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition disabled:opacity-60"
                            x-text="loading ? 'Saving...' : (editMode ? 'Update Product' : 'Create Product')">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function deleteProductItem(id, name) {
            // Show undo banner instead of Swal dialog
            const banner = document.createElement('div');
            banner.className = 'undo-banner';
            let secondsLeft = 5;
            banner.innerHTML = `
                <span>Product "${name}" marked for deletion</span>
                <button id="undo_product_${id}">Undo (${secondsLeft}s)</button>
            `;
            document.body.appendChild(banner);
            
            const btn = banner.querySelector(`#undo_product_${id}`);
            let timer = setInterval(() => {
                secondsLeft--;
                btn.textContent = `Undo (${secondsLeft}s)`;
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    banner.remove();
                    // Perform actual deletion after undo window closes
                    performProductDeletion(id, name);
                }
            }, 1000);

            btn.addEventListener('click', () => {
                clearInterval(timer);
                banner.remove();
            });
        }

        function performProductDeletion(id, name) {
            fetch(`{{ route('products.destroy', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
                    Swal.fire(
                        'Deleted!',
                        'Product has been deleted successfully.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.message || 'Failed to delete product.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'An error occurred while deleting the product.',
                    'error'
                );
            });
        }

        function productCrud() {
            return {
                showModal: false,
                editMode: false,
                loading: false,
                searchQuery: '',
                form: {
                    id: null,
                    title: '',
                    description: '',
                    price: '',
                    category: '',
                    is_active: true,
                    stock_quantity: '',
                    is_inventory_managed: false,
                    image: null,
                    image_preview: null,
                    remove_image: false
                },

                openCreate() {
                    this.resetForm();
                    this.showModal = true;
                },

                filterProducts() {
                    // This method is called by @input on the search box,
                    // but the actual filtering is handled by x-show directives on table rows
                },

                openEdit(product) {
                    this.resetForm();
                    this.editMode = true;
                    this.form.id = product.id;
                    this.form.title = product.title || '';
                    this.form.description = product.description || '';
                    this.form.price = product.price ?? '';
                    this.form.category = product.category || '';
                    this.form.is_active = !!product.is_active;
                    this.form.stock_quantity = product.stock_quantity ?? '';
                    this.form.is_inventory_managed = !!product.is_inventory_managed;
                    
                    // Determine image preview URL based on whether it's a Cloudinary URL or local path
                    if (product.image) {
                        if (product.image.includes('res.cloudinary.com')) {
                            this.form.image_preview = product.image;
                        } else {
                            this.form.image_preview = '/storage/' + product.image;
                        }
                    } else {
                        this.form.image_preview = null;
                    }
                    
                    this.showModal = true;
                },

                resetForm() {
                    this.editMode = false;
                    this.loading = false;
                    this.form = {
                        id: null,
                        title: '',
                        description: '',
                        price: '',
                        category: '',
                        is_active: true,
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
                    formData.append('title', this.form.title);
                    formData.append('description', this.form.description ?? '');
                    formData.append('price', this.form.price ?? '');
                    formData.append('category', this.form.category);
                    formData.append('is_active', this.form.is_active ? 1 : 0);
                    formData.append('stock_quantity', this.form.stock_quantity ?? '');
                    formData.append('is_inventory_managed', this.form.is_inventory_managed ? 1 : 0);
                    if (this.form.image) {
                        console.log('Image file being added:', this.form.image);
                        formData.append('image', this.form.image);
                    }
                    if (this.form.remove_image) formData.append('remove_image', 1);

                    let url = '{{ route('products.store') }}';
                    if (this.editMode && this.form.id) {
                        url = `{{ route('products.update', ':id') }}`.replace(':id', this.form.id);
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
                                text: data.message || 'Product saved successfully!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                this.showModal = false;
                                window.location.reload();
                            });
                        } else {
                            const errorMsg = data.message || data.errors?.title?.[0] || 'Failed to save product.';
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
                            text: 'An error occurred while saving the product. Check the browser console for details.',
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

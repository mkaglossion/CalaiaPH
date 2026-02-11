@extends('layouts.admin')

@section('title', 'Store Management')

@section('content')
<div
    x-data="{
        showModal: false,
        editMode: false,
        form: { id: null, title: '', description: '', price: '', image: null, imagePreview: null, category: 'frozen', is_active: true, stock_quantity: '', is_inventory_managed: false },

        resetForm() {
            this.form = { id: null, title: '', description: '', price: '', image: null, imagePreview: null, category: 'frozen', is_active: true, stock_quantity: '', is_inventory_managed: false };
            this.editMode = false;
        },

        handleImageChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.form.image = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.form.image = null;
                this.form.imagePreview = null;
            }
        },

        editProduct(product) {
            // Determine image preview URL based on whether it's a Cloudinary URL or local path
            let imagePreview = null;
            if (product.image) {
                if (product.image.includes('res.cloudinary.com')) {
                    imagePreview = product.image;
                } else {
                    imagePreview = '/storage/' + product.image;
                }
            }
            this.form = {
                id: product.id,
                title: product.title,
                description: product.description,
                price: product.price,
                image: null,
                imagePreview: imagePreview,
                category: product.category,
                is_active: product.is_active,
                stock_quantity: product.stock_quantity || '',
                is_inventory_managed: !!product.is_inventory_managed
            };
            this.editMode = true;
            this.showModal = true;
        },

        async saveProduct() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('title', this.form.title);
            formData.append('description', this.form.description);
            formData.append('price', this.form.price);
            if (this.form.image) {
                formData.append('image', this.form.image);
            }
            formData.append('category', this.form.category);
            if (this.form.is_active) formData.append('is_active', '1');
            formData.append('stock_quantity', this.form.stock_quantity || '');
            formData.append('is_inventory_managed', this.form.is_inventory_managed ? '1' : '0');

            let url = '{{ route("products.store") }}';
            let method = 'POST';

            if (this.editMode && this.form.id) {
                url = `{{ url("/admin/products") }}/${this.form.id}`;
                formData.append('_method', 'PUT');
                method = 'POST';
            }

            const btn = document.querySelector('#productForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Saving...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Product updated successfully!' : 'Product added successfully!'));
                    this.resetForm();
                    this.showModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    alert(errorMessages);
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch (err) {
                console.error(err);
                alert('Error saving product.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        }
    }"
>
    <h1 class="text-2xl font-bold mb-4">Store Management</h1>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Store Products</h2>
        <button
            @click="showModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Product
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Products Table --}}
    <div class="overflow-x-auto border rounded-lg shadow bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Title</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Category</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Price</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Stock</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Image</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $index => $product)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-600">{{ $products->firstItem() ? $products->firstItem() + $index : $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $product->title }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                {{ ucfirst($product->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">₱{{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($product->is_inventory_managed)
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                    {{ $product->stock_quantity }}
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">
                                    Not tracked
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $imageSrc = $product->image;
                                if ($imageSrc && !str_contains($imageSrc, 'res.cloudinary.com')) {
                                    $imageSrc = asset('storage/' . $imageSrc);
                                }
                            @endphp
                            @if ($imageSrc)
                                <img src="{{ $imageSrc }}" alt="{{ $product->title }}" class="h-12 w-12 rounded object-cover border border-gray-200" loading="lazy">
                            @else
                                <span class="text-gray-400 text-xs">No image</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($product->is_active)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Active
                                </span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button type="button"
                                    class="text-blue-600 hover:text-blue-900 font-medium text-sm transition"
                                    @click="editProduct({{ $product->toJson() }})">
                                Edit
                            </button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-900 font-medium text-sm transition"
                                        onclick="return confirm('Delete this product?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i data-lucide="package" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                            <p>No products added yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $products->links() }}
    </div>

    {{-- Modal --}}
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
            <button
                @click="showModal = false; resetForm()"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'Edit Product' : 'Add Product'"></h3>

            <form
                id="productForm"
                class="space-y-5"
                @submit.prevent="saveProduct()"
            >
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Title</label>
                    <input type="text" x-model="form.title" name="title" class="border rounded p-2 w-full" required>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Category</label>
                    <select x-model="form.category" name="category" class="border rounded p-2 w-full" required>
                        <option value="frozen">Frozen</option>
                        <option value="fresh">Fresh</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Price</label>
                    <input type="number" step="0.01" x-model="form.price" name="price" class="border rounded p-2 w-full" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="block font-semibold mb-1">Stock Quantity</label>
                        <input type="number" min="0" x-model="form.stock_quantity" name="stock_quantity" class="border rounded p-2 w-full" placeholder="50">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" x-model="form.is_inventory_managed" name="is_inventory_managed" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <div>
                                <p class="font-semibold text-gray-900">Manage Inventory</p>
                                <p class="text-xs text-gray-600">Track stock levels</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Image</label>
                    <input type="file" @change="handleImageChange($event)" name="image" class="border rounded p-2 w-full" accept="image/*" x-ref="imageInput">
                    <div x-show="form.imagePreview" class="mt-2">
                        <img :src="form.imagePreview" alt="Preview" class="max-w-32 max-h-32 object-cover border rounded">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Description</label>
                    <textarea x-model="form.description" name="description" rows="3" class="border rounded p-2 w-full" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="flex items-center">
                        <input type="checkbox" x-model="form.is_active" name="is_active" class="mr-2">
                        Active
                    </label>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false; resetForm()" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
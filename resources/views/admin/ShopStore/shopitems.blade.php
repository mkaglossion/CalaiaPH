@extends('layouts.admin')

@section('title', 'Shop Items Management')

@section('content')
<div
    x-data="{
        showModal: false,
        editMode: false,
        form: { id: null, title: '', description: '', price: '', image: '', category: 'frozen', is_active: true },

        resetForm() {
            this.form = { id: null, title: '', description: '', price: '', image: '', category: 'frozen', is_active: true };
            this.editMode = false;
        },

        editProduct(product) {
            this.form = {
                id: product.id,
                title: product.title,
                description: product.description,
                price: product.price,
                image: product.image,
                category: product.category,
                is_active: product.is_active
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
            formData.append('image', this.form.image);
            formData.append('category', this.form.category);
            if (this.form.is_active) formData.append('is_active', '1');

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
    <h1 class="text-2xl font-bold mb-4">Shop Items Management</h1>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Products Management</h2>
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

    {{-- Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Active</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $product->title }}</td>
                        <td class="px-4 py-2">{{ ucfirst($product->category) }}</td>
                        <td class="px-4 py-2">₱{{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-2">
                            @if($product->is_active)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <button
                                class="text-blue-600 hover:underline"
                                @click="editProduct({{ $product->toJson() }})"
                            >
                                Edit
                            </button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this product?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No products added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Image</label>
                    <input type="text" x-model="form.image" name="image" class="border rounded p-2 w-full" placeholder="e.g. product.jpg" required>
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
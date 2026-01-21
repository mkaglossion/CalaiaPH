@extends('layouts.admin')

@section('title', 'Food Management')

@section('content')
<div x-data="foodCrud()">
    <hr class="my-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Food & Beverage Items</h2>
        <button type="button" @click="openCreate()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Food Item
        </button>
    </div>

    @if ($message = Session::get('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-start gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold">Success</p>
                <p class="text-sm">{{ $message }}</p>
            </div>
        </div>
    @endif

    {{-- Food Items Table --}}
    <div class="overflow-x-auto border rounded-lg shadow bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Category</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Price</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Image</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($foods as $index => $food)
                    <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-600">{{ $foods->firstItem() ? $foods->firstItem() + $index : $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $food->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                {{ ucfirst($food->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">₱{{ number_format($food->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if ($food->image)
                                <img src="{{ $food->image }}" alt="{{ $food->name }}" class="h-12 w-12 rounded object-cover border border-gray-200">
                            @else
                                <span class="text-gray-400 text-xs">No image</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($food->is_active)
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
                                    @click="openEdit({
                                        id: {{ $food->id }},
                                        name: @js($food->name),
                                        category: @js($food->category),
                                        price: {{ $food->price ?? 0 }},
                                        description: @js($food->description),
                                        image: @js($food->image),
                                        is_active: {{ $food->is_active ? 'true' : 'false' }}
                                    })">
                                <i data-lucide="edit" class="w-4 h-4 inline mr-1"></i>Edit
                            </button>
                            <form method="POST" action="{{ route('admin.foods.destroy', $food->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this food item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm transition">
                                    <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                <p class="text-gray-500">No food items found.</p>
                                <button type="button" @click="openCreate()" class="text-blue-600 hover:underline font-medium text-sm">Create your first food item →</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-6">

    @if ($foods->hasPages())
        <div class="mt-6 flex items-center justify-between flex-wrap gap-3">
            <p class="text-sm text-gray-600">
                Showing
                <span class="font-semibold">{{ $foods->firstItem() }}</span>
                to
                <span class="font-semibold">{{ $foods->lastItem() }}</span>
                of
                <span class="font-semibold">{{ $foods->total() }}</span>
                items
            </p>
            <div class="">{{ $foods->links('pagination::tailwind') }}</div>
        </div>
    @endif

    {{-- Modal --}}
    <div
        x-show="showModal"
        x-transition
        x-cloak
        class="fixed inset-0 z-50 flex items-start md:items-center justify-center bg-black/50 px-3 sm:px-4 py-6"
        @keydown.escape.window="showModal = false"
    >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl md:max-w-4xl max-h-[90vh] overflow-y-auto"
             @click.away="showModal = false">
            <div class="px-4 sm:px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-xl font-bold" x-text="editMode ? 'Edit Food Item' : 'Create Food Item'"></h3>
                <button type="button" class="text-gray-500 hover:text-gray-800" @click="showModal = false">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form class="p-4 sm:p-6 space-y-6" @submit.prevent="submitForm">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Food Name *</label>
                    <input type="text" x-model="form.name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="e.g., Breakfast Buffet">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                        <select x-model="form.category" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                            <option value="package">Package</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Price (₱) *</label>
                        <input type="number" step="0.01" min="0" x-model="form.price" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="1500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea rows="4" x-model="form.description"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                              placeholder="Describe your food item..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                         @click="$refs.imageInput.click()">
                        <template x-if="form.image_preview">
                            <div class="mb-4 flex flex-col items-center gap-2">
                                <img :src="form.image_preview" alt="Preview" class="max-h-40 rounded-lg shadow">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="form.remove_image" class="rounded">
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

                        <input type="file" accept="image/*" class="hidden" x-ref="imageInput" @change="handleFile">
                    </div>
                </div>

                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-semibold text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-600">When checked, this item will be visible on the booking page</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-2 border-t">
                    <button type="submit" :disabled="loading"
                            class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span x-text="loading ? 'Saving...' : (editMode ? 'Update Food Item' : 'Create Food Item')"></span>
                    </button>
                    <button type="button" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition" @click="showModal = false">
                        Cancel
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
                form: {
                    id: null,
                    name: '',
                    category: '',
                    price: '',
                    description: '',
                    is_active: true,
                    image: null,
                    image_preview: null,
                    remove_image: false
                },

                openCreate() {
                    this.resetForm();
                    this.showModal = true;
                },

                openEdit(food) {
                    this.resetForm();
                    this.editMode = true;
                    this.form.id = food.id;
                    this.form.name = food.name || '';
                    this.form.category = food.category || '';
                    this.form.price = food.price ?? '';
                    this.form.description = food.description || '';
                    this.form.is_active = !!food.is_active;
                    this.form.image_preview = food.image || null;
                    this.showModal = true;
                },

                resetForm() {
                    this.editMode = false;
                    this.loading = false;
                    this.form = {
                        id: null,
                        name: '',
                        category: '',
                        price: '',
                        description: '',
                        is_active: true,
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
                    formData.append('price', this.form.price ?? '');
                    formData.append('description', this.form.description ?? '');
                    formData.append('is_active', this.form.is_active ? 1 : 0);
                    if (this.form.image) formData.append('image', this.form.image);
                    if (this.form.remove_image) formData.append('remove_image', 1);

                    let url = '{{ route('admin.foods.store') }}';
                    if (this.editMode && this.form.id) {
                        url = `/admin/foods/${this.form.id}`;
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

                        if (res.ok) {
                            this.showModal = false;
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to save food item.');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('An error occurred while saving the food item.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</div>

@endsection

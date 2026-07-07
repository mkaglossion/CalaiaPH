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
    x-init="window.foodCrudInstance = $data; $data.setupKeyboardShortcuts(); $data.loadPackages(); $data.loadAvailableFoods()"
    class="p-3 sm:p-6 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-lg sm:rounded-xl"
>
    @php
        $mobileLayoutStep2 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step2_food')->value('value') ?? 3);
    @endphp

    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2 sm:mb-3 tracking-tight text-gray-900">
        Food Management
    </h1>
    <p class="text-gray-600 text-sm sm:text-base mb-6 sm:mb-10">Manage food & beverage items, categories, prices, and inventory.</p>

    <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-indigo-900">Booking Step 2 Mobile Layout</h3>
                <p class="text-xs text-indigo-700 mt-1">Controls mobile card style for `/booking/staycation/food` only. Desktop layout stays unchanged.</p>
            </div>
            <div class="flex gap-2 items-center">
                <select id="mobileLayoutStep2"
                        class="px-3 py-2 border border-indigo-300 rounded-lg text-sm bg-white">
                    <option value="1" {{ $mobileLayoutStep2 === 1 ? 'selected' : '' }}>2 Columns</option>
                    <option value="2" {{ $mobileLayoutStep2 === 2 ? 'selected' : '' }}>1 Card Per Row</option>
                    <option value="3" {{ $mobileLayoutStep2 === 3 ? 'selected' : '' }}>Carousel (1 Card Swipe)</option>
                </select>
                <button type="button"
                        onclick="saveMobileLayoutSetting('mobile_layout_step2_food', document.getElementById('mobileLayoutStep2').value)"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                    Save
                </button>
            </div>
        </div>
    </div>

    {{-- Search + Sort --}}
    <div class="mb-4 flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
        <form method="GET" action="{{ route('admin.foods.index') }}" class="w-full sm:w-auto">
            <label for="sort_by" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Sort by</label>
            <select id="sort_by" name="sort_by"
                    onchange="this.form.submit()"
                    class="w-full sm:w-72 px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white">
                @foreach(($sortOptions ?? ['featured' => 'Featured']) as $key => $label)
                    <option value="{{ $key }}" {{ ($sortBy ?? 'featured') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mb-8 sm:mb-10">
        <input 
            id="search_foods"
            name="search"
            x-model="searchQuery"
            @input="filterFoods()"
            type="text"
            autocomplete="off"
            placeholder="🔍 Search by name or category..." 
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border-2 border-gray-200 rounded-lg sm:rounded-xl focus:border-blue-500 focus:outline-none transition"
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

    {{-- ============================================================= --}}
    {{-- FOOD PACKAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="mt-8 sm:mt-12">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 sm:gap-0 mb-4 sm:mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Food Packages</h2>
                <p class="text-gray-500 text-xs sm:text-sm">Create and manage bundled food packages for customers.</p>
            </div>

            <button
                type="button"
                @click="openCreatePackage()"
                class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-green-600 text-white text-sm sm:text-base font-medium shadow hover:bg-green-700 hover:shadow-lg transition inline-flex items-center justify-center sm:justify-start gap-2">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Create Package</span>
                <span class="sm:hidden">New Package</span>
            </button>
        </div>

        <div class="overflow-hidden rounded-lg sm:rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-gray-100/70 backdrop-blur">
                        <tr>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 w-8 sm:w-12">#</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 min-w-32 sm:min-w-40">Package Name</th>
                            <th class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Category</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-center">Price</th>
                            <th class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Items</th>
                            <th class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Status</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-right sticky right-0 bg-gray-100/70 z-20">Actions</th>
                        </tr>
                    </thead>
                <tbody class="divide-y divide-gray-200" x-html="packagesHtml"></tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 text-center text-gray-500" x-show="!packagesHtml || packagesHtml === ''">
                <p>Loading packages...</p>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- FOOD ITEMS SECTION --}}
    {{-- ============================================================= --}}
    <div class="mt-12 sm:mt-16 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 sm:gap-0 mb-4 sm:mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Food & Beverage Items</h2>
            <p class="text-gray-500 text-xs sm:text-sm">Manage all food and beverage items available for booking.</p>
        </div>

        <button
            type="button"
            @click="openCreate()"
            class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-blue-600 text-white text-sm sm:text-base font-medium shadow hover:bg-blue-700 hover:shadow-lg transition inline-flex items-center justify-center sm:justify-start gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Add New Food Item</span>
            <span class="sm:hidden">Add Item</span>
        </button>
    </div>

    {{-- Food Items Table --}}
    <div class="overflow-hidden rounded-lg sm:rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 w-8 sm:w-12">#</th>
                        <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 min-w-32 sm:min-w-40">Name</th>
                        <th class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Category</th>
                        <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-center">Price</th>
                        <th class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Stock</th>
                        <th class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 w-16 sm:w-20">Image</th>
                        <th class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Status</th>
                        <th class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Special</th>
                        <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-right sticky right-0 bg-gray-100/70 z-20">Actions</th>
                    </tr>
                </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($foods as $index => $food)
                    <tr 
                        class="hover:bg-gray-50/70 transition border-b border-gray-200 last:border-b-0"
                        x-show="!searchQuery || '{{ ($food->name ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase()) || '{{ ($food->category ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase())"
                        x-cloak
                    >
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900">{{ $foods->firstItem() ? $foods->firstItem() + $index : $index + 1 }}</td>
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            <div class="min-w-0 max-w-xs sm:max-w-sm md:max-w-none">
                                <div class="text-xs sm:text-sm font-medium text-gray-900 truncate">{{ $food->name }}</div>
                                @if($food->description)
                                    <div class="text-gray-500 text-xs line-clamp-2 sm:line-clamp-1">{{ $food->description }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            <div class="flex flex-wrap gap-1">
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 truncate">
                                    {{ ucfirst($food->category) }}
                                </span>
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 truncate">
                                    {{ ucwords(str_replace('_', ' ', $food->meal_type ?? 'all_day')) }}
                                </span>
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 truncate">
                                    Priority: {{ ($food->sort_order ?: 1) }}{{ substr(($food->sort_order ?: 1), -1) === '1' ? 'st' : (substr(($food->sort_order ?: 1), -1) === '2' ? 'nd' : (substr(($food->sort_order ?: 1), -1) === '3' ? 'rd' : 'th')) }}
                                </span>
                                @if($food->subcategory)
                                    <span class="hidden sm:inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 truncate">
                                        {{ ucfirst($food->subcategory) }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 text-center">₱{{ number_format($food->price, 2) }}</td>
                        <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            @if($food->is_inventory_managed)
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $food->stock_quantity }} left
                                </span>
                            @else
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Not tracked
                                </span>
                            @endif
                        </td>
                        <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            @if ($food->image)
                                <img src="{{ $food->image }}" alt="{{ $food->name }}" class="h-8 sm:h-10 w-8 sm:w-10 rounded object-cover" loading="lazy">
                            @else
                                <span class="text-gray-400 text-xs">No image</span>
                            @endif
                        </td>
                        <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            @if ($food->is_active)
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> <span class="hidden sm:inline">Active</span><span class="sm:hidden">✓</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> <span class="hidden sm:inline">Inactive</span><span class="sm:hidden">✕</span>
                                </span>
                            @endif
                        </td>
                        <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            @if ($food->is_special_option)
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i data-lucide="star" class="w-3 h-3 mr-1"></i> Special
                                </span>
                            @else
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Regular
                                </span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-right sticky right-0 bg-white/60 md:bg-transparent z-10 md:z-auto">
                            <div class="flex justify-end gap-2 sm:gap-3 md:gap-4 flex-nowrap">
                                <button type="button"
                                        class="text-blue-600 font-semibold hover:text-blue-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="openEdit({{ $food->id }})"
                                        title="Edit">
                                    <span class="hidden sm:inline">Edit</span><span class="sm:hidden">✎</span>
                                </button>
                                <button type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="deleteFood({{ $food->id }}, '{{ $food->name }}')"
                                        title="Delete">
                                    <span class="hidden sm:inline">Delete</span><span class="sm:hidden">✕</span>
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
        x-cloak
        style="display: none;"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-3 sm:p-4"
        x-transition.opacity
    >
        <div @click.away="showModal = false" class="bg-white/90 backdrop-blur-lg rounded-lg sm:rounded-2xl shadow-xl w-full max-w-2xl sm:max-w-3xl md:max-w-4xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 md:p-7 border border-gray-200 my-4">
            <button 
                @click="showModal = false; isPackageMode = false"
                class="absolute top-2 right-3 sm:top-3 sm:right-4 text-gray-400 hover:text-gray-600 text-2xl sm:text-3xl leading-none"
            >&times;</button>

            <h3 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4 text-gray-900 pr-8" x-text="getModalTitle()"></h3>

            <form class="space-y-4 sm:space-y-6" @submit.prevent="submitForm" x-show="!isPackageMode">
                <div>
                    <label for="food_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Food Name *</label>
                    <input type="text" x-model="form.name" id="food_name" name="name" required
                           autocomplete="name"
                           class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="e.g., Breakfast Buffet">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                    <div>
                        <label for="food_category" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Category *</label>
                        <select x-model="form.category" id="food_category" name="category" required
                                autocomplete="category"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            <option value="messhall">Messhall</option>
                            <option value="cook_your_own">Cook Your Own</option>
                        </select>
                    </div>

                    <div>
                        <label for="food_subcategory" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Subcategory</label>
                        <input type="text" x-model="form.subcategory" id="food_subcategory" name="subcategory"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="e.g., beef, pork, vegetables">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-6">
                    <div>
                        <label for="food_meal_type" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Meal Type *</label>
                        <select x-model="form.meal_type" id="food_meal_type" name="meal_type" required
                                @change="if (!editMode) { form.sort_order = getNextSortOrderForMealType(form.meal_type); }"
                                autocomplete="off"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="all_day">All Day</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                        </select>
                    </div>

                    <div>
                        <label for="food_sort_order" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Sort Priority</label>
                        <input type="number" min="1" step="1" x-model.number="form.sort_order" id="food_sort_order" name="sort_order"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="1">
                        <p class="mt-1 text-xs text-gray-500">
                            Lower number appears first. Suggested for
                            <span class="font-medium" x-text="(form.meal_type || 'all_day').replace('_',' ')"></span>:
                            <span class="font-semibold" x-text="getNextSortOrderForMealType(form.meal_type)"></span>
                        </p>
                    </div>
                    <div>
                        <label for="food_price" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Price (₱) *</label>
                        <input type="number" step="0.01" min="0" x-model="form.price" id="food_price" name="price" required
                               autocomplete="price"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="1500">
                    </div>

                    <div>
                        <label for="food_stock_quantity" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Stock Quantity</label>
                        <input type="number" min="0" x-model="form.stock_quantity" id="food_stock_quantity" name="stock_quantity"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="50">
                    </div>

                    <div class="flex items-end">
                        <label for="food_is_inventory_managed" class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                            <input type="checkbox" x-model="form.is_inventory_managed" id="food_is_inventory_managed" name="is_inventory_managed"
                                   autocomplete="off"
                                   class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <div>
                                <p class="font-semibold text-xs sm:text-sm text-gray-900">Manage Inventory</p>
                                <p class="text-xs text-gray-600">Track stock levels</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="food_description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Description</label>
                    <textarea rows="4" x-model="form.description" id="food_description" name="description"
                              autocomplete="off"
                              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                              placeholder="Describe your food item..."></textarea>
                </div>

                <div>
                    <label for="food_image" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                         @click="$refs.imageInput.click()">
                        <template x-if="form.image_preview">
                            <div class="mb-3 sm:mb-4 flex flex-col items-center gap-2">
                                <img :src="form.image_preview" alt="Preview" class="max-h-32 sm:max-h-40 rounded-lg shadow">
                                <label class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="form.remove_image" id="food_remove_image" name="remove_image" class="rounded">
                                    <span>Remove current image</span>
                                </label>
                            </div>
                        </template>

                        <template x-if="!form.image_preview">
                            <div class="flex flex-col items-center gap-1 sm:gap-2 text-center">
                                <i data-lucide="cloud-upload" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400"></i>
                                <p class="text-sm sm:text-base text-gray-600 font-medium">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </template>

                        <input type="file" accept="image/*" class="hidden" x-ref="imageInput" id="food_image" name="image" @change="handleFile">
                    </div>
                </div>

                <div class="space-y-3 p-3 sm:p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" id="food_is_active" name="is_active"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-semibold text-xs sm:text-sm text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-600">When checked, this item will be visible on the booking page</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_special_option" id="food_is_special_option" name="is_special_option"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div>
                            <p class="font-semibold text-xs sm:text-sm text-gray-900">Special Option</p>
                            <p class="text-xs text-gray-600">Mark this as a special option (e.g., Cook Your Own)</p>
                        </div>
                    </label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 sm:px-6 py-2 text-sm sm:text-base border rounded-lg sm:rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading || isEditHydrating"
                            class="px-4 sm:px-5 py-2 text-sm sm:text-base rounded-lg sm:rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition disabled:opacity-60"
                            x-text="loading ? 'Saving...' : (editMode ? 'Update Food Item' : 'Create Food Item')">
                    </button>
                </div>
            </form>

            {{-- Package Creation Form --}}
            <form class="space-y-4 sm:space-y-6" @submit.prevent="submitPackageForm" x-show="isPackageMode">
                <div>
                    <label for="package_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Package Name *</label>
                    <input type="text" x-model="packageForm.name" id="package_name" name="name" required
                           autocomplete="name"
                           class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                           placeholder="e.g., Breakfast Bundle, Romantic Dinner for 2">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    <div>
                        <label for="package_category" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Category *</label>
                        <select x-model="packageForm.category" id="package_category" name="category" required
                                autocomplete="category"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            <option value="messhall">Messhall</option>
                            <option value="cook_your_own">Cook Your Own</option>
                            <option value="special">Special</option>
                        </select>
                    </div>

                    <div>
                        <label for="package_meal_type" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Meal Type *</label>
                        <select x-model="packageForm.meal_type" id="package_meal_type" name="meal_type" required
                                autocomplete="off"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            <option value="all_day">All Day</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                        </select>
                    </div>

                    <div>
                        <label for="package_sort_order" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Sort Priority</label>
                        <input type="number" min="1" step="1" x-model.number="packageForm.sort_order" id="package_sort_order" name="sort_order"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                               placeholder="1">
                        <p class="mt-1 text-xs text-gray-500">Lower number appears first.</p>
                    </div>
                    <div>
                        <h3 class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Package Price (₱)</h3>
                        <div class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-50 text-gray-900">
                            ₱<span x-text="getPackageTotal()"></span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Automatically computed from selected items.</p>
                    </div>
                </div>

                <div>
                    <label for="package_description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Description</label>
                    <textarea rows="3" x-model="packageForm.description" id="package_description" name="description"
                              autocomplete="off"
                              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                              placeholder="Describe what's included in this package..."></textarea>
                </div>

                {{-- Package Items --}}
                <div>
                    <h3 class="block text-xs sm:text-sm font-semibold text-gray-700 mb-3">Package Items *</h3>
                    <div class="space-y-3">
                        <template x-for="(item, index) in packageForm.items" :key="item.row_key">
                            <div class="flex gap-3 items-end bg-gray-50 p-3 rounded-lg">
                                <div class="flex-1">
                                    <select :name="`foods[${index}]`"
                                            :value="String(item.food_id || '')"
                                            @change="item.food_id = String($event.target.value || '')"
                                            required
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                        <option value="">Select Food Item</option>
                                        <template x-for="food in availableFoods" :key="food.id">
                                            <option :value="String(food.id)" x-text="food.name + ' (₱' + food.price + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="w-20">
                                    <input type="number" :name="`quantities[${index}]`" x-model.number="item.quantity"
                                           required min="1" max="20"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                </div>

                                <div class="w-24 text-sm text-gray-600 px-2 py-2 bg-white border border-gray-300 rounded-lg">
                                    ₱<span x-text="getItemSubtotal(item)"></span>
                                </div>

                                <button type="button" @click="removePackageItem(index)"
                                        class="px-3 py-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="addPackageItem()"
                                class="w-full py-2 px-4 border-2 border-dashed border-green-300 text-green-600 rounded-lg hover:bg-green-50 transition font-medium">
                            <i data-lucide="plus" class="w-4 h-4 mr-2 inline"></i>Add Another Item
                        </button>
                    </div>

                    <div class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-900">Total Items Value:</span>
                            <span class="text-lg font-bold text-green-600">₱<span x-text="getPackageTotal()"></span></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                    <div>
                        <label for="package_stock_quantity" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Stock Quantity</label>
                        <input type="number" min="0" x-model="packageForm.stock_quantity" id="package_stock_quantity" name="stock_quantity"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                               placeholder="0">
                    </div>

                    <div class="flex items-end">
                        <label for="package_is_inventory_managed" class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                            <input type="checkbox" x-model="packageForm.is_inventory_managed" id="package_is_inventory_managed" name="is_inventory_managed"
                                   autocomplete="off"
                                   class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <div>
                                <p class="font-semibold text-xs sm:text-sm text-gray-900">Manage Inventory</p>
                                <p class="text-xs text-gray-600">Track stock levels</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-3 p-3 sm:p-4 bg-green-50 rounded-lg border border-green-200">
                    <label class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                        <input type="checkbox" x-model="packageForm.is_active" id="package_is_active" name="is_active"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div>
                            <p class="font-semibold text-xs sm:text-sm text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-600">When checked, this package will be visible on the booking page</p>
                        </div>
                    </label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="px-4 sm:px-6 py-2 text-sm sm:text-base border rounded-lg sm:rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading || isEditHydrating"
                            class="px-4 sm:px-5 py-2 text-sm sm:text-base rounded-lg sm:rounded-xl bg-green-600 text-white hover:bg-green-700 shadow transition disabled:opacity-60"
                            x-text="loading ? (packageEditMode ? 'Updating...' : 'Creating...') : (packageEditMode ? 'Update Package' : 'Create Package')">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        if (typeof window.saveMobileLayoutSetting !== 'function') {
            window.saveMobileLayoutSetting = function(settingKey, value) {
                const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                fetch('{{ route("admin.booking-settings.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ setting_key: settingKey, value: Number(value) })
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) {
                        throw new Error(data.message || 'Failed to update mobile layout setting.');
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Mobile layout updated.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1800
                    });
                })
                .catch((err) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Failed to update mobile layout setting.'
                    });
                });
            }
        }

        function foodCrud() {
            return {
                showModal: false,
                editMode: false,
                loading: false,
                isEditHydrating: false,
                searchQuery: '',
                isPackageMode: false,
                packageEditMode: false,
                availableFoods: [],
                packagesHtml: '',
                form: {
                    id: null,
                    name: '',
                    category: '',
                    meal_type: 'all_day',
                    sort_order: 1,
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
                packageForm: {
                    id: null,
                    name: '',
                    category: '',
                    meal_type: 'all_day',
                    sort_order: 1,
                    price: '',
                    description: '',
                    is_active: true,
                    stock_quantity: 0,
                    is_inventory_managed: false,
                    items: [{ food_id: '', quantity: 1 }]
                },
                
                // Toast notifications
                showToast(message, type = 'info') {
                    const icon = ['success', 'error', 'warning', 'info', 'question'].includes(type) ? type : 'info';
                    Swal.fire({
                        icon,
                        title: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2200
                    });
                },

                // Alert helper for Swal.fire
                showAlert(config) {
                    return Swal.fire({
                        confirmButtonText: 'OK',
                        title: config.title || 'Alert',
                        text: config.text || '',
                        icon: config.icon || 'info',
                        ...config
                    });
                },
                
                // Modal title
                getModalTitle() {
                    if (this.isPackageMode) {
                        return this.packageEditMode ? 'Edit Food Package' : 'Create Food Package';
                    }
                    return this.editMode ? 'Edit Food Item' : 'Create Food Item';
                },

                closeModal() {
                    this.showModal = false;
                    this.isPackageMode = false;
                    this.packageEditMode = false;
                    this.isEditHydrating = false;
                    this.resetForm();
                    this.resetPackageForm();
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
                
                // Helper: Get CSRF token
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },

                // Helper: Get fetch headers
                getFetchHeaders() {
                    return {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    };
                },

                normalizeMealType(mealType) {
                    const allowed = ['breakfast', 'lunch', 'dinner', 'all_day'];
                    return allowed.includes(mealType) ? mealType : 'all_day';
                },

                getNextSortOrderForMealType(mealType) {
                    const normalizedMealType = this.normalizeMealType(mealType);
                    const sameMealItems = (this.availableFoods || []).filter(food => {
                        const foodMealType = this.normalizeMealType(food.meal_type || 'all_day');
                        return foodMealType === normalizedMealType;
                    });

                    if (sameMealItems.length === 0) {
                        return 1;
                    }

                    const maxSortOrder = Math.max(...sameMealItems.map(food => {
                        const parsed = Number(food.sort_order);
                        return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
                    }));

                    return maxSortOrder + 1;
                },
                 
                // Search/Filter
                filterFoods() {
                    // Filtering handled by x-show directives on table rows
                },
                
                // Delete with undo
                deleteFood(id, name) {
                    this.scheduleDelete(id, name, 'food');
                },
                
                scheduleDelete(id, name, type) {
                    const banner = document.createElement('div');
                    banner.className = 'undo-banner';
                    let secondsLeft = 5;
                    const itemType = type === 'food' ? 'Food item' : 'Package';
                    banner.innerHTML = `
                        <span>${itemType} "${name}" marked for deletion</span>
                        <button id="undo_${type}_${id}">Undo (${secondsLeft}s)</button>
                    `;
                    document.body.appendChild(banner);
                    
                    const btn = banner.querySelector(`#undo_${type}_${id}`);
                    let timer = setInterval(() => {
                        secondsLeft--;
                        btn.textContent = `Undo (${secondsLeft}s)`;
                        if (secondsLeft <= 0) {
                            clearInterval(timer);
                            banner.remove();
                            this.confirmDelete(id, type);
                        }
                    }, 1000);

                    btn.addEventListener('click', () => {
                        clearInterval(timer);
                        banner.remove();
                    });
                },
                
                confirmDelete(id, type) {
                    const routes = {
                        food: `{{ route('admin.foods.destroy', ':id') }}`.replace(':id', id),
                        package: `{{ route('admin.food.packages.destroy', ':id') }}`.replace(':id', id)
                    };
                    
                    const url = routes[type];
                    fetch(url, {
                        method: 'POST',
                        headers: this.getFetchHeaders(),
                        body: new URLSearchParams({ '_method': 'DELETE' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const msg = type === 'food' ? 'Food item deleted successfully!' : 'Package deleted successfully!';
                            this.showToast(msg, 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            this.showToast(data.message || `Failed to delete ${type}.`, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showToast(`An error occurred while deleting the ${type}.`, 'error');
                    });
                },
                
                scheduleDeleteFood(id, name) {
                    this.scheduleDelete(id, name, 'food');
                },

                openCreate() {
                    this.resetForm();
                    this.isEditHydrating = false;
                    this.form.sort_order = this.getNextSortOrderForMealType(this.form.meal_type);
                    this.showModal = true;
                },

                openEdit(foodId) {
                    this.resetForm();
                    this.editMode = true;
                    this.isEditHydrating = true;
                    this.showModal = true;
                    
                    fetch(`{{ route('admin.foods.show', ':id') }}`.replace(':id', foodId), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(food => {
                        this.form.id = food.id;
                        this.form.name = food.name || '';
                        this.form.category = food.category || '';
                        this.form.meal_type = food.meal_type || 'all_day';
                        this.form.sort_order = food.sort_order || 1;
                        this.form.subcategory = food.subcategory || '';
                        this.form.price = food.price ?? '';
                        this.form.description = food.description || '';
                        this.form.is_active = !!food.is_active;
                        this.form.is_special_option = !!food.is_special_option;
                        this.form.stock_quantity = food.stock_quantity ?? '';
                        this.form.is_inventory_managed = !!food.is_inventory_managed;
                        this.form.image_preview = food.image || null;
                    })
                    .catch(() => {
                        this.showModal = false;
                        this.showToast('Failed to load food data', 'error');
                    })
                    .finally(() => this.isEditHydrating = false);
                },

                resetForm() {
                    this.editMode = false;
                    this.loading = false;
                    this.isEditHydrating = false;
                    this.form = {
                        id: null,
                        name: '',
                        category: '',
                        meal_type: 'all_day',
                        sort_order: null,
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

                // Package methods
                async loadPackages() {
                    try {
                        const response = await fetch('{{ route('admin.food.packages.api.index') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        this.renderPackages(data.packages || []);
                    } catch (error) {
                        console.error('Failed to load packages:', error);
                        this.packagesHtml = '<tr><td colspan="7" class="px-6 py-6 text-center text-gray-500">Failed to load packages</td></tr>';
                    }
                },

                renderPackages(packages) {
                    if (packages.length === 0) {
                        this.packagesHtml = '<tr><td colspan="7" class="px-6 py-6 text-center text-gray-500"><div class="flex flex-col items-center justify-center gap-3"><i data-lucide="package" class="w-8 h-8 text-gray-400"></i><p>No packages found.</p><button type="button" @click="openCreatePackage()" class="text-green-600 hover:underline font-medium text-sm">Create your first package →</button></div></td></tr>';
                        return;
                    }

                    this.packagesHtml = packages.map((pkg, index) => `
                        <tr class="hover:bg-gray-50/70 transition border-b border-gray-200 last:border-b-0">
                            <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 w-8 sm:w-12">${index + 1}</td>
                            <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 min-w-32 sm:min-w-40">
                                <div class="min-w-0 max-w-xs sm:max-w-sm md:max-w-none">
                                    <div class="text-xs sm:text-sm font-medium text-gray-900 truncate">${pkg.name}</div>
                                    ${pkg.description ? `<div class="text-gray-500 text-xs line-clamp-2 sm:line-clamp-1">${pkg.description}</div>` : ''}
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 truncate">
                                        ${pkg.category.charAt(0).toUpperCase() + pkg.category.slice(1).replace('_', ' ')}
                                    </span>
                                    <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 truncate">
                                        ${(pkg.meal_type || 'all_day').replace('_', ' ').replace(/\\b\\w/g, c => c.toUpperCase())}
                                    </span>
                                    <span class="text-[11px] text-gray-500">Sort: ${pkg.sort_order ?? 1}</span>
                                </div>
                            </td>
                            <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 text-center">₱${pkg.effective_price ? pkg.effective_price.toLocaleString() : '0'}</td>
                            <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    ${pkg.foods_count || 0} items
                                </span>
                            </td>
                            <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                ${pkg.is_active ? '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i data-lucide="check-circle" class="w-3 h-3 mr-1"></i><span class="hidden sm:inline">Active</span><span class="sm:hidden">✓</span></span>' : '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i data-lucide="x-circle" class="w-3 h-3 mr-1"></i><span class="hidden sm:inline">Inactive</span><span class="sm:hidden">✕</span></span>'}
                            </td>
                            <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-right sticky right-0 bg-white/60 md:bg-transparent z-10 md:z-auto">
                                <div class="flex justify-end gap-2 sm:gap-3 md:gap-4 flex-nowrap">
                                    <button type="button" class="text-blue-600 font-semibold hover:text-blue-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline" @click="openEditPackage(${pkg.id})">
                                        <span class="hidden sm:inline">Edit</span><span class="sm:hidden">✎</span>
                                    </button>
                                    <button type="button" class="text-red-600 font-semibold hover:text-red-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline" @click="deletePackage(${pkg.id}, '${pkg.name.replace(/'/g, '\\u0027')}')" title="Delete">
                                        <span class="hidden sm:inline">Delete</span><span class="sm:hidden">✕</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                },

                async openCreatePackage() {
                    this.isPackageMode = true;
                    this.packageEditMode = false;
                    this.isEditHydrating = false;
                    this.resetPackageForm();
                    await this.loadAvailableFoods();
                    this.showModal = true;
                },

                async openEditPackage(packageId) {
                    this.isPackageMode = true;
                    this.packageEditMode = true;
                    this.resetPackageForm();
                    this.isEditHydrating = true;
                    this.showModal = true;
                    
                    try {
                        await this.loadAvailableFoods();
                        const response = await fetch(`{{ route('admin.food.packages.details', ':id') }}`.replace(':id', packageId), {
                            headers: { 'Accept': 'application/json' }
                        });
                        const pkg = await response.json();
                        
                        if (pkg && pkg.id) {
                            this.packageForm.id = pkg.id;
                            this.packageForm.name = pkg.name || '';
                            this.packageForm.category = pkg.category || '';
                            this.packageForm.meal_type = pkg.meal_type || 'all_day';
                            this.packageForm.sort_order = pkg.sort_order ?? 1;
                            this.packageForm.description = pkg.description || '';
                            this.packageForm.is_active = !!pkg.is_active;
                            this.packageForm.stock_quantity = pkg.stock_quantity ?? 0;
                            this.packageForm.is_inventory_managed = !!pkg.is_inventory_managed;
                            
                            // Load foods for this package
                            if (pkg.foods && pkg.foods.length > 0) {
                                this.packageForm.items = pkg.foods.map(f => this.makePackageItem(f.id, f.quantity || 1));
                            }
                            
                            this.syncPackageItemSelections();
                        } else {
                            this.showToast('Package not found', 'error');
                        }
                    } catch (error) {
                        console.error('Error loading package:', error);
                        this.showModal = false;
                        this.showToast('Failed to load package data', 'error');
                    } finally {
                        this.isEditHydrating = false;
                    }
                },

                resetPackageForm() {
                    this.packageForm = {
                        id: null,
                        name: '',
                        category: '',
                        meal_type: 'all_day',
                        sort_order: 1,
                        price: '',
                        description: '',
                        is_active: true,
                        stock_quantity: 0,
                        is_inventory_managed: false,
                        items: [this.makePackageItem()]
                    };
                },
                
                deletePackage(id, name) {
                    this.scheduleDelete(id, name, 'package');
                },
                
                scheduleDeletePackage(id, name) {
                    this.scheduleDelete(id, name, 'package');
                },

                async loadAvailableFoods() {
                    try {
                        const response = await fetch('{{ route('admin.foods.api.index') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        this.availableFoods = (data.foods || []).map(food => ({
                            ...food,
                            id: String(food.id),
                            price: Number(food.price || 0),
                        }));
                        if (this.isPackageMode && this.packageForm.items.length > 0) {
                            this.syncPackageItemSelections();
                        }
                        if (!this.editMode && !this.isPackageMode && !this.form.sort_order) {
                            this.form.sort_order = this.getNextSortOrderForMealType(this.form.meal_type);
                        }
                    } catch (error) {
                        console.error('Failed to load foods:', error);
                        this.availableFoods = [];
                    }
                },

                makePackageItem(foodId = '', quantity = 1) {
                    return {
                        row_key: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                        food_id: foodId === null || foodId === undefined || foodId === '' ? '' : String(foodId),
                        quantity: Number(quantity) || 1,
                    };
                },

                syncPackageItemSelections() {
                    this.$nextTick(() => {
                        const selects = document.querySelectorAll('select[name^="foods["]');
                        selects.forEach((select, index) => {
                            const item = this.packageForm.items[index];
                            if (!item) return;
                            select.value = String(item.food_id || '');
                        });
                    });
                },

                addPackageItem() {
                    this.packageForm.items.push(this.makePackageItem());
                    this.syncPackageItemSelections();
                },

                removePackageItem(index) {
                    this.packageForm.items.splice(index, 1);
                    if (this.packageForm.items.length === 0) {
                        this.addPackageItem();
                    }
                },

                getItemSubtotal(item) {
                    const food = this.availableFoods.find(f => f.id === String(item.food_id || ''));
                    return food ? (food.price * item.quantity).toFixed(2) : '0.00';
                },

                getPackageTotal() {
                    return this.packageForm.items.reduce((total, item) => {
                        const food = this.availableFoods.find(f => f.id === String(item.food_id || ''));
                        return total + (food ? food.price * item.quantity : 0);
                    }, 0).toFixed(2);
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
                    formData.append('meal_type', this.form.meal_type || 'all_day');
                    formData.append('sort_order', this.form.sort_order ?? 1);
                    formData.append('subcategory', this.form.subcategory ?? '');
                    formData.append('price', this.form.price ?? '');
                    formData.append('description', this.form.description ?? '');
                    formData.append('is_active', this.form.is_active ? 1 : 0);
                    formData.append('is_special_option', this.form.is_special_option ? 1 : 0);
                    formData.append('stock_quantity', this.form.stock_quantity ?? '');
                    formData.append('is_inventory_managed', this.form.is_inventory_managed ? 1 : 0);
                    if (this.form.image) {
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
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok) {
                            this.showAlert({
                                title: 'Success!',
                                text: data.message || 'Food item saved successfully!',
                                icon: 'success'
                            }).then(() => {
                                this.showModal = false;
                                window.location.reload();
                            });
                        } else {
                            const errorMsg = data.message || data.errors?.name?.[0] || 'Failed to save food item.';
                            this.showAlert({
                                title: 'Error!',
                                text: errorMsg,
                                icon: 'error'
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showAlert({
                            title: 'Error!',
                            text: 'An error occurred while saving the food item. Check the browser console for details.',
                            icon: 'error'
                        });
                    } finally {
                        this.loading = false;
                    }
                },

                async submitPackageForm() {
                    if (this.loading) return;
                    this.loading = true;

                    const formData = new FormData();
                    formData.append('name', this.packageForm.name);
                    formData.append('category', this.packageForm.category);
                    formData.append('meal_type', this.packageForm.meal_type || 'all_day');
                    formData.append('sort_order', this.packageForm.sort_order ?? 1);
                    formData.append('price', this.getPackageTotal());
                    formData.append('description', this.packageForm.description || '');
                    formData.append('is_active', this.packageForm.is_active ? 1 : 0);
                    formData.append('stock_quantity', this.packageForm.stock_quantity ?? 0);
                    formData.append('is_inventory_managed', this.packageForm.is_inventory_managed ? 1 : 0);

                    // Add foods and quantities
                    this.packageForm.items.forEach((item, index) => {
                        if (item.food_id) {
                            formData.append(`foods[${index}]`, item.food_id);
                            formData.append(`quantities[${index}]`, item.quantity);
                        }
                    });

                    let url = '{{ route('admin.food.packages.store') }}';
                    if (this.packageEditMode && this.packageForm.id) {
                        url = `{{ route('admin.food.packages.update', ':id') }}`.replace(':id', this.packageForm.id);
                        formData.append('_method', 'PUT');
                    }

                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok) {
                            const successMsg = this.packageEditMode ? 'Package updated successfully!' : 'Package created successfully!';
                            this.showAlert({
                                title: 'Success!',
                                text: data.message || successMsg,
                                icon: 'success'
                            }).then(() => {
                                this.showModal = false;
                                this.loadPackages();
                                this.resetPackageForm();
                                this.packageEditMode = false;
                            });
                        } else {
                            const errorMsg = data.message || (this.packageEditMode ? 'Failed to update package.' : 'Failed to create package.');
                            this.showAlert({
                                title: 'Error!',
                                text: errorMsg,
                                icon: 'error'
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showAlert({
                            title: 'Error!',
                            text: `An error occurred while ${this.packageEditMode ? 'updating' : 'creating'} the package.`,
                            icon: 'error'
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

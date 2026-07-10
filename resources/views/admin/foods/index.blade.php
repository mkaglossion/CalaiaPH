@extends('layouts.admin')

@section('title', 'Food Management')

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
    .settings-accordion > summary::-webkit-details-marker {
        display: none;
    }
    .settings-accordion > summary {
        list-style: none;
    }
    .settings-chevron {
        transition: transform 0.2s ease;
    }
    .settings-accordion[open] .settings-chevron {
        transform: rotate(180deg);
    }
</style>
<div
    x-data="foodCrud()"
    x-init="window.foodCrudInstance = $data; $data.setupKeyboardShortcuts(); $data.loadPackages(); $data.loadAvailableFoods()"
    class="p-3 sm:p-6 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-lg sm:rounded-xl"
>
    @php
        $mobileLayoutStep2 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step2_food')->value('value') ?? 3);
        $packageCategoryLabels = $packageCategoryLabels ?? \App\Models\FoodPackageSetting::getPackageCategoryLabels();
        $packageCategoryDefinitions = \App\Models\FoodPackageSetting::getPackageCategoryLabelDefinitions();
        $foodBookingSectionCopy = $foodBookingSectionCopy ?? \App\Models\FoodPackageSetting::getFoodBookingSectionCopy();
        $foodBookingSectionDefinitions = \App\Models\FoodPackageSetting::getFoodBookingSectionDefinitions();
        $messhallLabel = $packageCategoryLabels['messhall'] ?? 'Messhall';
        $overnightStayMenuLabel = $packageCategoryLabels['overnight_stay_menu'] ?? 'Messhall';
        $cookYourOwnLabel = $packageCategoryLabels['cook_your_own'] ?? 'Cook Your Own';
        $specialLabel = $packageCategoryLabels['special'] ?? 'Special';
        $packageCategoryOptions = [
            'messhall' => $messhallLabel,
            'overnight_stay_menu' => $overnightStayMenuLabel,
            'cook_your_own' => $cookYourOwnLabel,
            'special' => $specialLabel,
        ];
        $defaultPackageCategory = array_key_first($packageCategoryOptions) ?? 'messhall';
        $adminSectionCard = 'rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 md:p-6 shadow-sm';
        $adminSubtleCard = 'rounded-2xl border border-gray-200 bg-white/80 backdrop-blur p-4 md:p-5 shadow-sm';
        $adminPrimaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg';
        $adminDarkButton = 'inline-flex items-center justify-center rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800';
        $adminModalOverlay = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-3 sm:p-4';
        $adminModalPanel = 'relative w-full max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 p-4 sm:p-6 md:p-7 shadow-xl';
        $adminModalPanelLg = $adminModalPanel . ' max-w-4xl';
        $adminModalCloseButton = 'absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-2xl leading-none text-gray-400 transition hover:border-gray-300 hover:text-gray-700';
        $adminModalTitle = 'pr-12 text-lg sm:text-xl font-bold text-gray-900';
        $adminModalCancelButton = 'rounded-xl border border-gray-300 px-4 sm:px-6 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100';
    @endphp

    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2 sm:mb-3 tracking-tight text-gray-900">
        Food Management
    </h1>
    <p class="text-gray-600 text-sm sm:text-base mb-6 sm:mb-10">Manage food & beverage items, categories, prices, and inventory.</p>

    <div class="mb-6 {{ $adminSectionCard }}">
        <form method="POST" action="{{ route('admin.food.category-label.update') }}" class="space-y-6">
            @csrf
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Food Package Display Settings</h3>
                <p class="text-xs text-gray-600 mt-1">Edit category labels and booking headings in one place.</p>
            </div>

            <div class="space-y-4">
                <details class="settings-accordion group rounded-xl border border-gray-200 bg-gray-50/60 p-4" open>
                    <summary class="cursor-pointer">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Package Category Labels</h4>
                                <p class="text-xs text-gray-600 mt-1">Used in filters, package cards, and category choices.</p>
                                <p class="text-[11px] text-gray-500 mt-1">Click to expand or collapse this section.</p>
                            </div>
                            <span class="settings-chevron inline-flex h-8 w-8 flex-none items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-sm">
                                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>
                    </summary>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($packageCategoryDefinitions as $category => $definition)
                            @php
                                $fieldName = $definition['setting_key'];
                                $fieldValue = $packageCategoryLabels[$category] ?? $definition['default'];
                            @endphp
                            <div>
                                <label for="{{ $fieldName }}" class="block text-xs font-semibold text-gray-800 mb-1">
                                    {{ $definition['label'] }}
                                </label>
                                <input
                                    id="{{ $fieldName }}"
                                    name="{{ $fieldName }}"
                                    type="text"
                                    value="{{ old($fieldName, $fieldValue) }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gray-400 focus:ring-2 focus:ring-gray-200"
                                    placeholder="{{ $definition['default'] }}"
                                    required
                                >
                            </div>
                        @endforeach
                    </div>
                </details>

                <details class="settings-accordion group rounded-xl border border-gray-200 bg-gray-50/60 p-4" @if($errors->any()) open @endif>
                    <summary class="cursor-pointer">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Booking Section Copy</h4>
                                <p class="text-xs text-gray-600 mt-1">Used on <code>/booking/staycation/food</code>.</p>
                                <p class="text-[11px] text-gray-500 mt-1">Click to expand or collapse this section.</p>
                            </div>
                            <span class="settings-chevron inline-flex h-8 w-8 flex-none items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-sm">
                                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>
                    </summary>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach($foodBookingSectionDefinitions as $section => $definition)
                            @php
                                $headingField = $definition['heading_key'];
                                $descriptionField = $definition['description_key'];
                                $headingValue = $foodBookingSectionCopy[$section]['heading'] ?? $definition['default_heading'];
                                $descriptionValue = $foodBookingSectionCopy[$section]['description'] ?? $definition['default_description'];
                            @endphp
                            <div class="rounded-lg border border-gray-200 bg-white p-3">
                                <h5 class="text-xs font-semibold text-gray-900 mb-3">{{ $definition['label'] }}</h5>
                                <div class="space-y-3">
                                    <div>
                                        <label for="{{ $headingField }}" class="block text-[11px] font-semibold text-gray-700 mb-1">Heading</label>
                                        <input
                                            id="{{ $headingField }}"
                                            name="{{ $headingField }}"
                                            type="text"
                                            value="{{ old($headingField, $headingValue) }}"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gray-400 focus:ring-2 focus:ring-gray-200"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label for="{{ $descriptionField }}" class="block text-[11px] font-semibold text-gray-700 mb-1">Description</label>
                                        <textarea
                                            id="{{ $descriptionField }}"
                                            name="{{ $descriptionField }}"
                                            rows="2"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gray-400 focus:ring-2 focus:ring-gray-200"
                                            required
                                        >{{ old($descriptionField, $descriptionValue) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="{{ $adminDarkButton }}">
                    Save Display Settings
                </button>
            </div>
        </form>
    </div>

    <div class="mb-6 {{ $adminSubtleCard }}">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Booking Step 2 Mobile Layout</h3>
                <p class="text-xs text-gray-600 mt-1">Controls mobile card style for `/booking/staycation/food` only. Desktop layout stays unchanged.</p>
            </div>
            <div class="flex gap-2 items-center">
                <select id="mobileLayoutStep2"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="1" {{ $mobileLayoutStep2 === 1 ? 'selected' : '' }}>2 Columns</option>
                    <option value="2" {{ $mobileLayoutStep2 === 2 ? 'selected' : '' }}>1 Card Per Row</option>
                    <option value="3" {{ $mobileLayoutStep2 === 3 ? 'selected' : '' }}>Carousel (1 Card Swipe)</option>
                </select>
                <button type="button"
                        onclick="saveMobileLayoutSetting('mobile_layout_step2_food', document.getElementById('mobileLayoutStep2').value)"
                        class="{{ $adminPrimaryButton }}">
                    Save
                </button>
            </div>
        </div>
    </div>

    {{-- Search + Sort --}}
    <div class="mb-4 {{ $adminSubtleCard }} flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
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

    <div class="mb-8 sm:mb-10 {{ $adminSubtleCard }}">
        <input 
            id="search_foods"
            name="search"
            x-model="searchQuery"
            @input="filterFoods()"
            type="text"
            autocomplete="off"
            placeholder="&#128269; Search by name or category..."
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border-2 border-gray-200 rounded-lg sm:rounded-xl focus:border-[#8f5d54] focus:outline-none transition"
        >
    </div>

    @if ($message = Session::get('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: @js($message),
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'swal2-confirm swal2-styled'
                    }
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Please check the form',
                    html: @js(collect($errors->all())->map(fn ($error) => e($error))->implode('<br>')),
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    {{-- ============================================================= --}}
    {{-- FOOD PACKAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="mt-8 sm:mt-12 {{ $adminSectionCard }}">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 sm:gap-0 mb-4 sm:mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Food Packages</h2>
                <p class="text-gray-500 text-xs sm:text-sm">Create and manage bundled food packages for customers.</p>
            </div>

            <button
                type="button"
                @click="openCreatePackage()"
                class="w-full sm:w-auto {{ $adminPrimaryButton }}">
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
    <div class="mt-12 sm:mt-16 {{ $adminSectionCard }}">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 sm:gap-0 mb-4 sm:mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Food & Beverage Items</h2>
                <p class="text-gray-500 text-xs sm:text-sm">Manage all food and beverage items available for booking.</p>
            </div>

            <button
                type="button"
                @click="openCreate()"
                class="w-full sm:w-auto {{ $adminPrimaryButton }}">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Add New Food Item</span>
                <span class="sm:hidden">Add Item</span>
            </button>
        </div>

    {{-- Food Items Table --}}
    <div class="overflow-hidden rounded-lg sm:rounded-2xl border border-gray-200 shadow-sm bg-white/80 backdrop-blur">
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
            <tbody class="divide-y divide-gray-200" x-show="hasSearchQuery()" x-html="filteredFoodsHtml"></tbody>
            <tbody class="divide-y divide-gray-200" x-show="!hasSearchQuery()">
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
                                    {{ $food->category === 'messhall' ? $messhallLabel : ($food->category === 'cook_your_own' ? $cookYourOwnLabel : ucfirst($food->category)) }}
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
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 text-center">&#8369;{{ number_format($food->price, 2) }}</td>
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
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> <span class="hidden sm:inline">Active</span><span class="sm:hidden">âœ“</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> <span class="hidden sm:inline">Inactive</span><span class="sm:hidden">âœ•</span>
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
                                    <span class="hidden sm:inline">Edit</span><span class="sm:hidden">âœŽ</span>
                                </button>
                                <button type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="deleteFood({{ $food->id }}, '{{ $food->name }}')"
                                        title="Delete">
                                    <span class="hidden sm:inline">Delete</span><span class="sm:hidden">âœ•</span>
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
                                <button type="button" @click="openCreate()" class="text-blue-600 hover:underline font-medium text-sm">Create your first food item â†’</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>

        @if ($foods->hasPages())
            <div class="px-6 py-4 border-t border-gray-200" x-show="!hasSearchQuery()">
                {{ $foods->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
    </div>

    {{-- Modal --}}
    <div 
        x-show="showModal"
        x-cloak
        style="display: none;"
        class="{{ $adminModalOverlay }}"
        x-transition.opacity
    >
        <div @click.away="closeModal()" class="{{ $adminModalPanelLg }}">
            <button 
                @click="showModal = false; isPackageMode = false"
                class="{{ $adminModalCloseButton }}"
            >&times;</button>

            <h3 class="{{ $adminModalTitle }} mb-3 sm:mb-4" x-text="getModalTitle()"></h3>

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
                            <option value="messhall">{{ $messhallLabel }}</option>
                            <option value="cook_your_own">{{ $cookYourOwnLabel }}</option>
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
                        <label for="food_price" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Price (&#8369;) *</label>
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

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" @click="closeModal()" class="{{ $adminModalCancelButton }}">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading || isEditHydrating"
                            class="{{ $adminPrimaryButton }} disabled:opacity-60"
                            x-text="loading ? 'Saving...' : (editMode ? 'Update Food Item' : 'Create Food Item')">
                    </button>
                </div>
            </form>

            {{-- Package Creation Form --}}
            <form class="space-y-4 sm:space-y-6" @submit.prevent="submitPackageForm" x-show="isPackageMode" x-effect="if (isPackageMode) { syncPackagePrice(); }">
                <div>
                    <label for="package_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Package Name *</label>
                    <input type="text" x-model="packageForm.name" id="package_name" name="name" required
                           autocomplete="name"
                           class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                           placeholder="e.g., Breakfast Bundle, Romantic Dinner for 2">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                    <div>
                        <label for="package_category" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Category *</label>
                        <select x-model="packageForm.category" id="package_category" name="category" required
                                @change="handlePackageCategoryChange()"
                                autocomplete="category"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            @foreach($packageCategoryOptions as $categoryKey => $categoryLabel)
                                <option value="{{ $categoryKey }}">{{ $categoryLabel }}</option>
                            @endforeach
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
                        <label for="package_price" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Package Price (&#8369;)</label>
                        <input type="number" min="0" step="0.01" x-model="packageForm.price" id="package_price" name="price"
                               @input="packagePriceManualOverride = String(packageForm.price ?? '') !== getPackageTotal()"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                               placeholder="0.00">
                        <div class="mt-1 flex items-center justify-between gap-3">
                            <p class="text-xs text-gray-500">Suggested total: &#8369;<span x-text="getPackageTotal()"></span></p>
                            <button type="button"
                                    @click="packagePriceManualOverride = false; syncPackagePrice(true)"
                                    x-show="packagePriceManualOverride"
                                    class="text-xs font-semibold text-green-600 hover:text-green-700">
                                Use Suggested
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="package_description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Description</label>
                    <textarea rows="3" x-model="packageForm.description" id="package_description" name="description"
                              autocomplete="off"
                              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                              placeholder="Describe what's included in this package..."></textarea>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Image</label>
                    <div
                        class="rounded-2xl border-2 border-dashed border-blue-400 bg-blue-50/60 px-4 py-8 sm:px-6 sm:py-10 text-center cursor-pointer hover:bg-blue-50 transition"
                        @click="$refs.packageImageInput.click()"
                    >
                        <template x-if="packageForm.image_preview">
                            <div class="flex flex-col items-center gap-3">
                                <img :src="packageForm.image_preview" alt="Package preview" class="max-h-40 sm:max-h-48 rounded-xl shadow-md object-cover">
                                <label
                                    x-show="packageEditMode && !packageForm.image"
                                    class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer"
                                    @click.stop
                                >
                                    <input type="checkbox" x-model="packageForm.remove_image" id="package_remove_image" name="remove_image" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>Remove current image</span>
                                </label>
                            </div>
                        </template>

                        <template x-if="!packageForm.image_preview">
                            <div class="flex flex-col items-center justify-center gap-2 text-gray-600">
                                <i data-lucide="image-plus" class="w-10 h-10 text-blue-400"></i>
                                <p class="text-sm sm:text-base font-medium">Click to upload an image</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 20MB</p>
                            </div>
                        </template>

                        <input type="file" accept="image/*" class="hidden" x-ref="packageImageInput" id="package_image" name="image" @change="handlePackageFile">
                    </div>
                </div>

                {{-- Package Items / Sections --}}
                <div>
                    <template x-if="isStructuredPackage(packageForm.category)">
                        <div>
                            <div class="mb-3">
                                <h3 class="block text-xs sm:text-sm font-semibold text-gray-700">Package Sections *</h3>
                                <p class="mt-1 text-xs text-gray-500">Use Lunch, Dinner, and Big Breakfast. Set Big Breakfast to "Choose one".</p>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(section, sectionIndex) in packageForm.sections" :key="section.row_key">
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_180px_auto] gap-3 items-end">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Section Title</label>
                                                <input type="text"
                                                       x-model="section.title"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                       placeholder="Lunch">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Section Type</label>
                                                <select x-model="section.mode"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                    <option value="included">Included Items</option>
                                                    <option value="choice">Choose One</option>
                                                </select>
                                            </div>

                                            <button type="button"
                                                    @click="removePackageSection(sectionIndex)"
                                                    class="inline-flex items-center justify-center h-10 px-3 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-300 transition text-sm font-medium">
                                                Remove Section
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Note</label>
                                                <input type="text"
                                                       x-model="section.note"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                       placeholder="Choose one">
                                            </div>

                                            <div x-show="section.mode === 'choice'">
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Choice Limit</label>
                                                <input type="number"
                                                       min="1"
                                                       step="1"
                                                       x-model.number="section.choice_limit"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                       placeholder="1">
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs sm:text-sm font-semibold text-gray-700">Section Items</h4>
                                                <button type="button"
                                                        @click="addPackageSectionItem(sectionIndex)"
                                                        class="inline-flex items-center gap-2 text-xs font-semibold text-green-600 hover:text-green-700">
                                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                                    Add Item
                                                </button>
                                            </div>

                                            <template x-for="(item, itemIndex) in section.items" :key="item.row_key">
                                                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_80px_110px_auto] gap-3 items-center bg-white p-3 rounded-lg border border-gray-200">
                                                    <div class="min-w-0">
                                                        <select :name="`sections[${sectionIndex}][items][${itemIndex}][food_id]`"
                                                                x-model="item.food_id"
                                                                x-init="$nextTick(() => { $el.value = String(item.food_id || '') })"
                                                                required
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition bg-white">
                                                            <option value="">Select Food Item</option>
                                                            <template x-for="food in availableFoods" :key="food.id">
                                                                <option :value="String(food.id)" x-text="food.name + ' (&#8369;' + food.price + ')'"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <div class="w-full">
                                                        <input type="number" :name="`sections[${sectionIndex}][items][${itemIndex}][quantity]`"
                                                               x-model.number="item.quantity"
                                                               required min="1" max="20"
                                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition bg-white text-center">
                                                    </div>

                                                    <div class="w-full text-sm text-gray-700 px-3 py-2 bg-white border border-gray-300 rounded-lg">
                                                        <span>&#8369;</span><span x-text="getItemSubtotal(item)"></span>
                                                    </div>

                                                    <button type="button"
                                                            @click="removePackageSectionItem(sectionIndex, itemIndex)"
                                                            title="Remove item"
                                                            aria-label="Remove item"
                                                            class="inline-flex h-10 w-10 flex-none items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 shadow-sm hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition">
                                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                                                            <path d="M3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                            <path d="M8 6V4.8c0-.9.7-1.8 1.8-1.8h4.4c1.1 0 1.8.7 1.8 1.8V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                            <path d="M6.5 6l.7 12.1c0 1 .8 1.9 1.8 1.9h6.8c1 0 1.8-.8 1.8-1.9L17.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M10 10v6M14 10v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" @click="addPackageSection()"
                                        class="w-full py-2 px-4 border-2 border-dashed border-green-300 text-green-600 rounded-lg hover:bg-green-50 transition font-medium">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2 inline"></i>Add Another Section
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="!isStructuredPackage(packageForm.category)">
                        <div>
                            <h3 class="block text-xs sm:text-sm font-semibold text-gray-700 mb-3">Package Items *</h3>
                            <div class="space-y-3">
                                <template x-for="(item, index) in packageForm.items" :key="item.row_key">
                                    <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_80px_110px_auto] gap-3 items-center bg-gray-50 p-3 rounded-lg">
                                        <div class="min-w-0">
                                            <select :name="`foods[${index}]`"
                                                    x-model="item.food_id"
                                                    x-init="$nextTick(() => { $el.value = String(item.food_id || '') })"
                                                    required
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition bg-white">
                                                <option value="">Select Food Item</option>
                                                <template x-for="food in availableFoods" :key="food.id">
                                                    <option :value="String(food.id)" x-text="food.name + ' (&#8369;' + food.price + ')'"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="w-full">
                                            <input type="number" :name="`quantities[${index}]`" x-model.number="item.quantity"
                                                   required min="1" max="20"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition bg-white text-center">
                                        </div>

                                        <div class="w-full text-sm text-gray-700 px-3 py-2 bg-white border border-gray-300 rounded-lg">
                                            <span>&#8369;</span><span x-text="getItemSubtotal(item)"></span>
                                        </div>

                                        <button type="button"
                                                @click="removePackageItem(index)"
                                                title="Remove item"
                                                aria-label="Remove item"
                                                class="inline-flex h-10 w-10 flex-none items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 shadow-sm hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                <path d="M8 6V4.8c0-.9.7-1.8 1.8-1.8h4.4c1.1 0 1.8.7 1.8 1.8V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                <path d="M6.5 6l.7 12.1c0 1 .8 1.9 1.8 1.9h6.8c1 0 1.8-.8 1.8-1.9L17.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M10 10v6M14 10v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                            </svg>
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
                                    <span class="text-lg font-bold text-green-600">&#8369;<span x-text="getPackageTotal()"></span></span>
                                </div>
                            </div>
                        </div>
                    </template>
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

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" @click="closeModal()" class="{{ $adminModalCancelButton }}">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading || isEditHydrating"
                            class="{{ $adminPrimaryButton }} disabled:opacity-60"
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
                        title: 'Success!',
                        text: 'Mobile layout updated.',
                        confirmButtonText: 'OK'
                    });
                })
                .catch((err) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
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
                packagePriceManualOverride: false,
                categoryLabels: @js($packageCategoryOptions),
                availableFoods: [],
                filteredFoodsHtml: '',
                packages: [],
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
                    sort_order: 1,
                    price: '',
                    description: '',
                    is_active: true,
                    stock_quantity: 0,
                    is_inventory_managed: false,
                    image: null,
                    image_preview: null,
                    remove_image: false,
                    items: [{ row_key: `pkg-${Date.now()}`, food_id: '', quantity: 1 }],
                    sections: []
                },
                
                // Notification helper
                showToast(message, type = 'info') {
                    const icon = ['success', 'error', 'warning', 'info', 'question'].includes(type) ? type : 'info';
                    return Swal.fire({
                        icon,
                        title: type === 'success' ? 'Success!' : (type === 'error' ? 'Error!' : 'Notice'),
                        text: message,
                        confirmButtonText: 'OK'
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
                        if (this.packageEditMode) {
                            return `Edit ${this.formatCategoryLabel(this.packageForm.category || 'package')} Package`;
                        }
                        return `Create ${this.formatCategoryLabel(this.packageForm.category || 'package')} Package`;
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

                getNextPackageSortOrder(category = '') {
                    const normalizedCategory = String(category || '').trim().toLowerCase();
                    const matchingPackages = (this.packages || []).filter(pkg => {
                        if (!normalizedCategory) {
                            return true;
                        }

                        return String(pkg.category || '').trim().toLowerCase() === normalizedCategory;
                    });

                    if (matchingPackages.length === 0) {
                        return 1;
                    }

                    const maxSortOrder = Math.max(...matchingPackages.map(pkg => {
                        const parsed = Number(pkg.sort_order);
                        return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
                    }));

                    return maxSortOrder + 1;
                },

                hasSearchQuery() {
                    return String(this.searchQuery || '').trim() !== '';
                },

                escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                },

                formatPriorityOrdinal(value) {
                    const parsed = Number(value) || 1;
                    const mod100 = parsed % 100;
                    if (mod100 >= 11 && mod100 <= 13) {
                        return `${parsed}th`;
                    }

                    switch (parsed % 10) {
                        case 1:
                            return `${parsed}st`;
                        case 2:
                            return `${parsed}nd`;
                        case 3:
                            return `${parsed}rd`;
                        default:
                            return `${parsed}th`;
                    }
                },

                renderFilteredFoodRows(foods) {
                    if (!Array.isArray(foods) || foods.length === 0) {
                        return '<tr><td colspan="9" class="px-6 py-6 text-center text-gray-500">No matching food items found.</td></tr>';
                    }

                    return foods.map((food, index) => {
                        const categoryLabel = this.formatCategoryLabel(food.category || '');
                        const mealTypeLabel = String(food.meal_type || 'all_day').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                        const description = this.escapeHtml(food.description || '');
                        const name = this.escapeHtml(food.name || '');
                        const subcategory = this.escapeHtml(food.subcategory || '');
                        const imageUrl = food.image_url || food.image || '';
                        const encodedName = JSON.stringify(String(food.name || ''));
                        const sortLabel = this.formatPriorityOrdinal(food.sort_order || 1);
                        const statusHtml = food.is_active
                            ? '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i data-lucide="check-circle" class="w-3 h-3 mr-1"></i><span class="hidden sm:inline">Active</span></span>'
                            : '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i data-lucide="x-circle" class="w-3 h-3 mr-1"></i><span class="hidden sm:inline">Inactive</span></span>';
                        const stockHtml = food.is_inventory_managed
                            ? `<span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">${Number(food.stock_quantity || 0)} left</span>`
                            : '<span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Not tracked</span>';
                        const specialHtml = food.is_special_option
                            ? '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i data-lucide="star" class="w-3 h-3 mr-1"></i> Special</span>'
                            : '<span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Regular</span>';

                        return `
                            <tr class="hover:bg-gray-50/70 transition border-b border-gray-200 last:border-b-0">
                                <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900">${index + 1}</td>
                                <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                    <div class="min-w-0 max-w-xs sm:max-w-sm md:max-w-none">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900 truncate">${name}</div>
                                        ${description ? `<div class="text-gray-500 text-xs line-clamp-2 sm:line-clamp-1">${description}</div>` : ''}
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 truncate">${this.escapeHtml(categoryLabel)}</span>
                                        <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 truncate">${this.escapeHtml(mealTypeLabel)}</span>
                                        <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 truncate">Priority: ${sortLabel}</span>
                                        ${subcategory ? `<span class="hidden sm:inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 truncate">${subcategory}</span>` : ''}
                                    </div>
                                </td>
                                <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 text-center">&#8369;${Number(food.price || 0).toFixed(2)}</td>
                                <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">${stockHtml}</td>
                                <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                    ${imageUrl ? `<img src="${this.escapeHtml(imageUrl)}" alt="${name}" class="h-8 sm:h-10 w-8 sm:w-10 rounded object-cover" loading="lazy">` : '<span class="text-gray-400 text-xs">No image</span>'}
                                </td>
                                <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">${statusHtml}</td>
                                <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">${specialHtml}</td>
                                <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-right sticky right-0 bg-white/60 md:bg-transparent z-10 md:z-auto">
                                    <div class="flex justify-end gap-2 sm:gap-3 md:gap-4 flex-nowrap">
                                        <button type="button" class="text-blue-600 font-semibold hover:text-blue-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline" onclick='window.foodCrudInstance.openEdit(${Number(food.id)})'>
                                            <span class="hidden sm:inline">Edit</span><span class="sm:hidden">Edit</span>
                                        </button>
                                        <button type="button" class="text-red-600 font-semibold hover:text-red-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline" onclick='window.foodCrudInstance.deleteFood(${Number(food.id)}, ${encodedName})'>
                                            <span class="hidden sm:inline">Delete</span><span class="sm:hidden">Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                },
                 
                // Search/Filter
                filterFoods() {
                    if (!this.hasSearchQuery()) {
                        this.filteredFoodsHtml = '';
                        return;
                    }

                    const query = String(this.searchQuery || '').trim().toLowerCase();
                    const filteredFoods = (this.availableFoods || []).filter(food => {
                        const haystack = [
                            food.name,
                            food.category,
                            food.subcategory,
                            food.description,
                            food.meal_type,
                        ].map(value => String(value || '').toLowerCase());

                        return haystack.some(value => value.includes(query));
                    });

                    this.filteredFoodsHtml = this.renderFilteredFoodRows(filteredFoods);
                    this.$nextTick(() => {
                        if (window.lucide?.createIcons) {
                            window.lucide.createIcons();
                        }
                    });
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
                    this.packages = Array.isArray(packages) ? packages : [];
                    if (packages.length === 0) {
                        this.packagesHtml = '<tr><td colspan="7" class="px-6 py-6 text-center text-gray-500"><div class="flex flex-col items-center justify-center gap-3"><i data-lucide="package" class="w-8 h-8 text-gray-400"></i><p>No packages found.</p><button type="button" @click="openCreatePackage()" class="text-green-600 hover:underline font-medium text-sm">Create your first package â†’</button></div></td></tr>';
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
                                        ${this.formatCategoryLabel(pkg.category)}
                                    </span>
                                    <span class="text-[11px] text-gray-500">Sort: ${pkg.sort_order ?? 1}</span>
                                </div>
                            </td>
                            <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 text-center">&#8369;${pkg.effective_price ? pkg.effective_price.toLocaleString() : '0'}</td>
                            <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    ${pkg.foods_count || 0} items
                                </span>
                            </td>
                            <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                                ${pkg.is_active ? '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i data-lucide="check-circle" class="w-3 h-3 mr-1"></i><span class="hidden sm:inline">Active</span><span class="sm:hidden">âœ“</span></span>' : '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i data-lucide="x-circle" class="w-3 h-3 mr-1"></i><span class="hidden sm:inline">Inactive</span><span class="sm:hidden">âœ•</span></span>'}
                            </td>
                            <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-right sticky right-0 bg-white/60 md:bg-transparent z-10 md:z-auto">
                                <div class="flex justify-end gap-2 sm:gap-3 md:gap-4 flex-nowrap">
                                    <button type="button" class="text-blue-600 font-semibold hover:text-blue-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline" @click="openEditPackage(${pkg.id})">
                                        <span class="hidden sm:inline">Edit</span><span class="sm:hidden">âœŽ</span>
                                    </button>
                                    <button type="button" class="text-red-600 font-semibold hover:text-red-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline" @click="deletePackage(${pkg.id}, '${pkg.name.replace(/'/g, '\\u0027')}')" title="Delete">
                                        <span class="hidden sm:inline">Delete</span><span class="sm:hidden">âœ•</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                },

                formatCategoryLabel(category) {
                    if (!category) return 'Package';
                    return this.categoryLabels?.[category]
                        || category.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                },

                async openCreatePackage() {
                    this.isPackageMode = true;
                    this.packageEditMode = false;
                    this.packagePriceManualOverride = false;
                    this.isEditHydrating = false;
                    this.resetPackageForm(@js($defaultPackageCategory));
                    this.packageForm.sort_order = this.getNextPackageSortOrder(this.packageForm.category);
                    await this.loadAvailableFoods();
                    this.showModal = true;
                },

                async openEditPackage(packageId) {
                    this.isPackageMode = true;
                    this.packageEditMode = true;
                    this.packagePriceManualOverride = true;
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
                            this.packageForm.sort_order = pkg.sort_order ?? 1;
                            this.packageForm.price = pkg.price ?? '';
                            this.packageForm.description = pkg.description || '';
                            this.packageForm.is_active = !!pkg.is_active;
                            this.packageForm.stock_quantity = pkg.stock_quantity ?? 0;
                            this.packageForm.is_inventory_managed = !!pkg.is_inventory_managed;
                            this.packageForm.image = null;
                            this.packageForm.image_preview = pkg.image || null;
                            this.packageForm.remove_image = false;
                            this.packageForm.sections = Array.isArray(pkg.sections) && pkg.sections.length > 0
                                ? pkg.sections.map(section => this.hydratePackageSection(section))
                                : this.defaultPackageSections(pkg.category || '');
                            
                            // Load foods for this package
                            if (pkg.foods && pkg.foods.length > 0) {
                                this.packageForm.items = pkg.foods.map(f => this.makePackageItem(f.id, f.quantity || 1));
                            } else if (!this.isStructuredPackage(pkg.category || '') && (!this.packageForm.items || this.packageForm.items.length === 0)) {
                                this.packageForm.items = [this.makePackageItem()];
                            }
                            
                            this.syncPackageItemSelections();
                            this.syncPackageSectionSelections();

                            const suggestedPrice = this.getPackageTotal();
                            const savedPrice = pkg.price === null || pkg.price === undefined || pkg.price === ''
                                ? ''
                                : Number(pkg.price).toFixed(2);

                            this.packagePriceManualOverride = savedPrice !== '' && savedPrice !== suggestedPrice;
                            if (!this.packagePriceManualOverride) {
                                this.packageForm.price = suggestedPrice;
                            }
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

                resetPackageForm(category = '') {
                    if (this.$refs.packageImageInput) {
                        this.$refs.packageImageInput.value = '';
                    }
                    this.packagePriceManualOverride = false;
                    this.packageForm = {
                        id: null,
                        name: '',
                        category,
                        sort_order: this.getNextPackageSortOrder(category),
                        price: '',
                        description: '',
                        is_active: true,
                        stock_quantity: 0,
                        is_inventory_managed: false,
                        image: null,
                        image_preview: null,
                        remove_image: false,
                        items: [this.makePackageItem()],
                        sections: this.defaultPackageSections(category)
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
                        const sortBy = document.getElementById('sort_by')?.value || '{{ $sortBy ?? 'featured' }}';
                        const response = await fetch(`{{ route('admin.foods.api.index') }}?sort_by=${encodeURIComponent(sortBy)}`, {
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
                        if (this.isPackageMode && this.packageForm.sections.length > 0) {
                            this.syncPackageSectionSelections();
                        }
                        if (!this.editMode && !this.isPackageMode && !this.form.sort_order) {
                            this.form.sort_order = this.getNextSortOrderForMealType(this.form.meal_type);
                        }
                        if (this.hasSearchQuery()) {
                            this.filterFoods();
                        }
                    } catch (error) {
                        console.error('Failed to load foods:', error);
                        this.availableFoods = [];
                        this.filteredFoodsHtml = '<tr><td colspan="9" class="px-6 py-6 text-center text-gray-500">Failed to load food items.</td></tr>';
                    }
                },

                isStructuredPackage(category) {
                    return String(category || '').toLowerCase() === 'overnight_stay_menu';
                },

                makePackageSection(title = '', mode = 'included', note = '', choiceLimit = 1, items = []) {
                    return {
                        row_key: `section-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                        title,
                        mode,
                        note,
                        choice_limit: Number(choiceLimit) || 1,
                        items: items.length > 0 ? items : [this.makePackageSectionItem()],
                    };
                },

                makePackageSectionItem(foodId = '', quantity = 1) {
                    return {
                        row_key: `section-item-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                        food_id: foodId === null || foodId === undefined || foodId === '' ? '' : String(foodId),
                        quantity: Number(quantity) || 1,
                    };
                },

                hydratePackageSection(section) {
                    const normalizedSection = this.makePackageSection(
                        section?.title || '',
                        section?.mode || 'included',
                        section?.note || '',
                        section?.choice_limit ?? 1,
                        Array.isArray(section?.items)
                            ? section.items.map(item => this.makePackageSectionItem(item?.food_id ?? '', item?.quantity ?? 1))
                            : []
                    );

                    return normalizedSection;
                },

                defaultPackageSections(category = '') {
                    if (!this.isStructuredPackage(category)) {
                        return [];
                    }

                    return [
                        this.makePackageSection('Lunch', 'included', 'Included meals for lunch', 1, [
                            this.makePackageSectionItem(),
                            this.makePackageSectionItem(),
                        ]),
                        this.makePackageSection('Dinner', 'included', 'Included meals for dinner', 1, [
                            this.makePackageSectionItem(),
                            this.makePackageSectionItem(),
                        ]),
                        this.makePackageSection('Big Breakfast', 'choice', 'Choose one', 1, [
                            this.makePackageSectionItem(),
                            this.makePackageSectionItem(),
                        ]),
                    ];
                },

                handlePackageCategoryChange() {
                    if (!this.packageEditMode) {
                        this.packageForm.sort_order = this.getNextPackageSortOrder(this.packageForm.category);
                    }

                    if (this.isStructuredPackage(this.packageForm.category)) {
                        if (!Array.isArray(this.packageForm.sections) || this.packageForm.sections.length === 0) {
                            this.packageForm.sections = this.defaultPackageSections(this.packageForm.category);
                        }
                    } else {
                        if (!Array.isArray(this.packageForm.items) || this.packageForm.items.length === 0) {
                            this.packageForm.items = [this.makePackageItem()];
                        }
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

                syncPackageSectionSelections() {
                    this.$nextTick(() => {
                        const selects = document.querySelectorAll('select[name^="sections["][name*="[food_id]"]');
                        const sectionItems = this.packageForm.sections.flatMap(section => Array.isArray(section.items) ? section.items : []);

                        selects.forEach((select, index) => {
                            const item = sectionItems[index];
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

                addPackageSection() {
                    this.packageForm.sections.push(this.makePackageSection());
                },

                removePackageSection(sectionIndex) {
                    this.packageForm.sections.splice(sectionIndex, 1);
                    if (this.packageForm.sections.length === 0) {
                        this.packageForm.sections = this.defaultPackageSections(this.packageForm.category);
                    }
                },

                addPackageSectionItem(sectionIndex) {
                    const section = this.packageForm.sections[sectionIndex];
                    if (!section) return;
                    section.items.push(this.makePackageSectionItem());
                },

                removePackageSectionItem(sectionIndex, itemIndex) {
                    const section = this.packageForm.sections[sectionIndex];
                    if (!section) return;
                    section.items.splice(itemIndex, 1);
                    if (section.items.length === 0) {
                        section.items.push(this.makePackageSectionItem());
                    }
                },

                getItemSubtotal(item) {
                    const food = this.availableFoods.find(f => f.id === String(item.food_id || ''));
                    return food ? (food.price * item.quantity).toFixed(2) : '0.00';
                },

                getPackageSectionSubtotal(section) {
                    const items = Array.isArray(section?.items) ? section.items : [];
                    const mode = String(section?.mode || 'included');
                    const limit = Math.max(1, parseInt(section?.choice_limit || 1, 10));
                    const selectedItems = mode === 'choice' ? items.slice(0, limit) : items;

                    return selectedItems.reduce((sectionTotal, item) => {
                        const food = this.availableFoods.find(f => f.id === String(item.food_id || ''));
                        return sectionTotal + (food ? food.price * Number(item.quantity || 1) : 0);
                    }, 0);
                },

                getPackageTotal() {
                    if (this.isStructuredPackage(this.packageForm.category)) {
                        return this.packageForm.sections.reduce((total, section) => {
                            return total + this.getPackageSectionSubtotal(section);
                        }, 0).toFixed(2);
                    }

                    return this.packageForm.items.reduce((total, item) => {
                        const food = this.availableFoods.find(f => f.id === String(item.food_id || ''));
                        return total + (food ? food.price * item.quantity : 0);
                    }, 0).toFixed(2);
                },

                syncPackagePrice(force = false) {
                    const computedPrice = this.getPackageTotal();
                    if ((force || !this.packagePriceManualOverride) && String(this.packageForm.price ?? '') !== computedPrice) {
                        this.packageForm.price = computedPrice;
                    }

                    return computedPrice;
                },

                handlePackageFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.packageForm.image = file;
                    this.packageForm.image_preview = URL.createObjectURL(file);
                    this.packageForm.remove_image = false;
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

                    const resolvedPackagePrice = this.packagePriceManualOverride
                        ? (this.packageForm.price === '' || this.packageForm.price === null ? this.getPackageTotal() : this.packageForm.price)
                        : this.syncPackagePrice(true);

                    const formData = new FormData();
                    formData.append('name', this.packageForm.name);
                    formData.append('category', this.packageForm.category || @js($defaultPackageCategory));
                    formData.append('sort_order', this.packageForm.sort_order ?? 1);
                    formData.append('price', resolvedPackagePrice);
                    formData.append('description', this.packageForm.description || '');
                    formData.append('is_active', this.packageForm.is_active ? 1 : 0);
                    formData.append('stock_quantity', this.packageForm.stock_quantity ?? 0);
                    formData.append('is_inventory_managed', this.packageForm.is_inventory_managed ? 1 : 0);
                    if (this.packageForm.image) {
                        formData.append('image', this.packageForm.image);
                    }
                    if (this.packageForm.remove_image) {
                        formData.append('remove_image', 1);
                    }

                    if (this.isStructuredPackage(this.packageForm.category)) {
                        formData.append('sections', JSON.stringify(this.packageForm.sections || []));
                    } else {
                        // Add foods and quantities
                        this.packageForm.items.forEach((item, index) => {
                            if (item.food_id) {
                                formData.append(`foods[${index}]`, item.food_id);
                                formData.append(`quantities[${index}]`, item.quantity);
                            }
                        });
                    }

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
                            }).then(async () => {
                                this.showModal = false;
                                await this.loadPackages();
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

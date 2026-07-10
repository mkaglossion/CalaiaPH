@extends('layouts.admin')

@section('title', 'Activities Management')

@section('content')
<style>
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

@php
    $mobileLayoutStep3 = (int) (\App\Models\BookingSetting::where('key', 'mobile_layout_step3_activities')->value('value') ?? 3);
    $activityCategoryLabels = \App\Models\ActivityPackageSetting::getCategoryLabels();
    $activityCategoryDefinitions = \App\Models\ActivityPackageSetting::getCategoryLabelDefinitions();
    $activityBookingSectionCopy = \App\Models\ActivityPackageSetting::getBookingSectionCopy();
    $activityBookingSectionDefinitions = \App\Models\ActivityPackageSetting::getBookingSectionDefinitions();
    $activityCategoryOptions = [];
    foreach ($activityCategoryDefinitions as $categoryKey => $definition) {
        $activityCategoryOptions[$categoryKey] = $activityCategoryLabels[$categoryKey] ?? $definition['default'];
    }
    $activityOnlyCategoryOptions = array_filter(
        $activityCategoryOptions,
        fn ($label, $categoryKey) => $categoryKey !== 'special',
        ARRAY_FILTER_USE_BOTH
    );
    $adminSectionCard = 'rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 md:p-6 shadow-sm';
    $adminSubtleCard = 'rounded-2xl border border-gray-200 bg-white/80 backdrop-blur p-4 md:p-5 shadow-sm';
    $adminPrimaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg';
    $adminDarkButton = 'inline-flex items-center justify-center rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800';
    $adminModalOverlay = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-3 sm:p-4';
    $adminModalPanel = 'relative w-full max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 p-5 sm:p-6 md:p-7 shadow-xl';
    $adminModalPanelSm = $adminModalPanel . ' max-w-lg';
    $adminModalPanelLg = $adminModalPanel . ' max-w-3xl';
    $adminModalCloseButton = 'absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-2xl leading-none text-gray-400 transition hover:border-gray-300 hover:text-gray-700';
    $adminModalTitle = 'pr-12 text-lg sm:text-xl font-bold text-gray-900';
    $adminModalCancelButton = 'rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100';
@endphp

<div x-data="activityManager()" x-init="window.__alpineApp = $data; $data.setupKeyboardShortcuts(); $data.loadPackages(); $data.loadAvailableActivities()" x-cloak class="p-3 sm:p-6 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-lg sm:rounded-xl w-full">

    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2 sm:mb-3 tracking-tight text-gray-900">
        Activities Management
    </h1>
    <p class="text-gray-600 text-sm sm:text-base mb-6 sm:mb-10">Manage activities & experience packages available for booking.</p>
    <div class="mb-6 {{ $adminSectionCard }}">
        <form method="POST" action="{{ route('admin.do.category-label.update') }}" class="space-y-6">
            @csrf
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Activity Display Settings</h3>
                <p class="text-xs text-gray-600 mt-1">Edit category labels and booking headings in one place.</p>
            </div>

            <div class="space-y-4">
                <details class="settings-accordion group rounded-xl border border-gray-200 bg-gray-50/60 p-4" open>
                    <summary class="cursor-pointer">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Activity Category Labels</h4>
                                <p class="text-xs text-gray-600 mt-1">Used in filters, cards, and admin category choices.</p>
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
                        @foreach($activityCategoryDefinitions as $category => $definition)
                            @php
                                $fieldName = $definition['setting_key'];
                                $fieldValue = $activityCategoryLabels[$category] ?? $definition['default'];
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
                                <p class="text-xs text-gray-600 mt-1">Used on <code>/booking/staycation/activities</code>.</p>
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
                        @foreach($activityBookingSectionDefinitions as $section => $definition)
                            @php
                                $headingField = $definition['heading_key'];
                                $descriptionField = $definition['description_key'];
                                $headingValue = $activityBookingSectionCopy[$section]['heading'] ?? $definition['default_heading'];
                                $descriptionValue = $activityBookingSectionCopy[$section]['description'] ?? $definition['default_description'];
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
                <h3 class="text-sm font-semibold text-gray-900">Booking Step 3 Mobile Layout</h3>
                <p class="text-xs text-gray-600 mt-1">Controls mobile card style for `/booking/staycation/activities` only. Desktop layout stays unchanged.</p>
            </div>
            <div class="flex gap-2 items-center">
                <select id="mobileLayoutStep3"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="1" {{ $mobileLayoutStep3 === 1 ? 'selected' : '' }}>2 Columns</option>
                    <option value="2" {{ $mobileLayoutStep3 === 2 ? 'selected' : '' }}>1 Card Per Row</option>
                    <option value="3" {{ $mobileLayoutStep3 === 3 ? 'selected' : '' }}>Carousel (1 Card Swipe)</option>
                </select>
                <button type="button"
                        onclick="saveMobileLayoutSetting('mobile_layout_step3_activities', document.getElementById('mobileLayoutStep3').value)"
                        class="{{ $adminPrimaryButton }}">
                    Save
                </button>
            </div>
        </div>
    </div>

    {{-- Search + Sort --}}
    <div class="mb-4 {{ $adminSubtleCard }} flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
        <form method="GET" action="{{ route('admin.do.activities') }}" class="w-full sm:w-auto">
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
            id="search_activities"
            name="search"
            x-model="searchQuery"
            @input="filterActivities()"
            type="text"
            autocomplete="off"
            placeholder="&#128269; Search by name or category..."
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border-2 border-gray-200 rounded-lg sm:rounded-xl focus:border-[#8f5d54] focus:outline-none transition"
        >
    </div>

    @if(session('success'))
        <div
            data-admin-flash
            data-admin-flash-library="toastr"
            data-admin-flash-title="Success"
            data-admin-flash-type="success"
            data-admin-flash-message="{{ e(session('success')) }}"
        ></div>
    @endif

    @if(session('error'))
        <div
            data-admin-flash
            data-admin-flash-library="toastr"
            data-admin-flash-title="Error"
            data-admin-flash-type="error"
            data-admin-flash-message="{{ e(session('error')) }}"
        ></div>
    @endif

    {{-- ============================================================= --}}
    {{-- ACTIVITY PACKAGES SECTION --}}
    {{-- ============================================================= --}}
    <div class="mt-8 sm:mt-12 {{ $adminSectionCard }}">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 sm:gap-0 mb-4 sm:mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Activity Packages</h2>
                <p class="text-gray-500 text-xs sm:text-sm">Create and manage bundled activity packages.</p>
            </div>

            <button type="button" @click="openCreatePackage()"
                    class="w-full sm:w-auto {{ $adminPrimaryButton }}">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">Create Package</span>
                <span class="sm:hidden">New Package</span>
            </button>
        </div>

        {{-- Activity Packages Table --}}
        <div class="overflow-hidden rounded-lg sm:rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <div class="overflow-x-auto md:overflow-visible">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-gray-100/70 backdrop-blur">
                        <tr>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 w-8 sm:w-12">#</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 min-w-32 sm:min-w-40">Name</th>
                            <th class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Category</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-center">Price</th>
                            <th class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Activities</th>
                            <th class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Status</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" x-html="packagesHtml"></tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 text-center text-gray-500 bg-white rounded-lg" x-show="!packagesHtml || packagesHtml === ''">
                <p>Loading packages...</p>
            </div>
        </div>
    </div>

    {{-- Add/Edit Activity Package Modal --}}
    <div
        x-cloak
        x-show="showPackageModal"
        class="{{ $adminModalOverlay }}"
        x-transition.opacity
    >
        <div @click.away="showPackageModal = false" class="{{ $adminModalPanelLg }}">
            <button
                @click="showPackageModal = false"
                class="{{ $adminModalCloseButton }}"
            >&times;</button>

            <h3 class="{{ $adminModalTitle }} mb-3 sm:mb-4" x-text="editPackageMode ? 'Edit Activity Package' : 'Create Activity Package'"></h3>

            <form class="space-y-4 sm:space-y-6" @submit.prevent="saveActivityPackage">
                @csrf

                <!-- Package Name -->
                <div>
                    <label for="package_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Package Name *</label>
                    <input type="text" x-model="packageForm.name" id="package_name" name="name" required
                           autocomplete="name"
                           class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                           placeholder="e.g., Adventure Bundle, Team Building Pack">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                    <div>
                        <label for="package_category" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Category *</label>
                        <select x-model="packageForm.category" id="package_category" name="category" required
                                autocomplete="category"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            <option value="">Select Category</option>
                            @foreach($activityCategoryOptions as $categoryKey => $categoryLabel)
                                <option value="{{ $categoryKey }}">{{ $categoryLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <h3 class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Package Price (₱)</h3>
                        <div class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-semibold">
                            ₱<span x-text="getPackageActivityTotal()"></span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Automatically computed from selected activities.</p>
                    </div>
                </div>

                <div>
                    <label for="package_description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Description</label>
                    <textarea rows="3" x-model="packageForm.description" id="package_description" name="description"
                              autocomplete="off"
                              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                              placeholder="Describe what's included in this package..."></textarea>
                </div>

                {{-- Package Activities --}}
                <div>
                    <h3 class="block text-xs sm:text-sm font-semibold text-gray-700 mb-3">Include Activities *</h3>
                    <div class="space-y-3">
                        <template x-for="(item, index) in packageForm.items" :key="item.row_key">
                            <div class="flex gap-3 items-end bg-gray-50 p-3 rounded-lg">
                                <div class="flex-1">
                                    <select :name="`activities[${index}]`"
                                            :value="String(item.activity_id || '')"
                                            @change="item.activity_id = String($event.target.value || '')"
                                            required
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                        <option value="">Select Activity</option>
                                        <template x-for="activity in availableActivities" :key="activity.id">
                                            <option :value="activity.id" x-text="activity.name + ' (₱' + activity.price + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="w-20">
                                    <input type="number" :name="`quantities[${index}]`" x-model.number="item.quantity"
                                           required min="1" max="20"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                </div>

                                <div class="w-24 text-sm text-gray-600 px-2 py-2 bg-white border border-gray-300 rounded-lg">
                                    ₱<span x-text="getActivityItemSubtotal(item)"></span>
                                </div>

                                <button type="button" @click="removePackageActivityItem(index)"
                                        class="px-3 py-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="addPackageActivityItem()"
                                class="w-full py-2 px-4 border-2 border-dashed border-green-300 text-green-600 rounded-lg hover:bg-green-50 transition font-medium text-sm">
                            <i class="bi bi-plus mr-2"></i>Add Another Activity
                        </button>
                    </div>

                    <div class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-900">Total Activities Value:</span>
                            <span class="text-lg font-bold text-green-600">₱<span x-text="getPackageActivityTotal()"></span></span>
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

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" @click="showPackageModal = false" class="{{ $adminModalCancelButton }}">
                        Cancel
                    </button>

                    <button type="submit" :disabled="isLoading || isEditHydrating"
                            class="{{ $adminPrimaryButton }} disabled:opacity-60"
                            x-text="isLoading ? (editPackageMode ? 'Updating...' : 'Creating...') : (editPackageMode ? 'Update Package' : 'Create Package')">
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ORIGINAL ACTIVITIES SECTION CONTINUES BELOW --}}

    <div class="py-12"></div>

<style>
    .swiper-button-next, .swiper-button-prev {
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        width: 40px;
        height: 40px;
    }
    .swiper-pagination-bullet-active {
        background-color: #a75e3e;
    }
    
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

<script>
  // Minimal data - no serialization of large objects
  window.activitiesData = {
    page: {{ $activities->currentPage() }},
    lastPage: {{ $activities->lastPage() }},
    total: {{ $activities->total() }},
    perPage: {{ $activities->perPage() }}
  };
</script>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Activities Management
    </h1>
    <p class="text-gray-600 mb-10">Manage your activities, descriptions, and associated content.</p>
    {{-- ============================================================= --}}
    {{-- ACTIVITIES MANAGEMENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="{{ $adminSectionCard }}">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Activities</h2>
                <p class="text-gray-500 text-sm">Manage your activity listings and details.</p>
            </div>
            <button
                @click="showActivityModal = true; resetForm()"
                class="{{ $adminPrimaryButton }}"
            >
                + Add Activity
            </button>
        </div>

    {{-- Activities Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/80 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">#</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Name</th>
                        <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Category</th>
                        <th class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Description</th>
                        <th class="hidden lg:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Duration</th>
                        <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Pricing</th>
                        <th class="hidden xl:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Uploaded</th>
                        <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900 text-right">Actions</th>
                    </tr>
                </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($activities as $index => $act)
                    <tr 
                        class="hover:bg-gray-50/70 transition"
                        x-show="!searchQuery || 
                            '{{ ($act->name ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            '{{ ($act->category ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            '{{ ($act->description ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase())"
                        x-cloak
                    >
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">
                            {{ ($activities->currentPage() - 1) * $activities->perPage() + $index + 1 }}
                        </td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->name ?? 'Untitled' }}</td>
                        @php
                            $activityCategoryKey = strtolower((string) ($act->category ?? ''));
                        @endphp
                        <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $activityCategoryOptions[$activityCategoryKey] ?? ($activityCategoryKey !== '' ? ucfirst(str_replace('_', ' ', $activityCategoryKey)) : 'No category') }}</td>
                        <td class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->description ?? 'No description' }}</td>
                        <td class="hidden lg:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->duration ?? 'N/A' }}</td>
                        <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->price ? '₱' . number_format($act->price, 2) : 'N/A' }}</td>
                        <td class="hidden xl:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $act->created_at->format('Y-m-d') }}</td>
                        <td class="px-3 sm:px-4 lg:px-6 py-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1 sm:gap-2 whitespace-nowrap">
                                <button
                                    type="button"
                                    class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-xs sm:text-sm"
                                    @click="editActivity({{ $act->id }})"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-xs sm:text-sm"
                                    @click="deleteActivity({{ $act->id }})"
                                    :id="`undo_click_${{{ $act->id }}}`"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 sm:px-4 lg:px-6 py-6 text-center text-gray-500">
                            No activities added yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    </div>

    <div class="mt-6">
        {{ $activities->links() }}
    </div>

    {{-- Add/Edit Activity Modal --}}
    <div
        x-cloak
        x-show="showActivityModal"
        class="{{ $adminModalOverlay }}"
        x-transition.opacity
    >
        <div @click.away="showActivityModal = false; resetForm()" class="{{ $adminModalPanelSm }}">
            <button
                @click="showActivityModal = false; resetForm()"
                class="{{ $adminModalCloseButton }}"
            >&times;</button>

            <h3 class="{{ $adminModalTitle }} mb-4" x-text="editModeActivity ? 'Edit Activity' : 'Add Activity'"></h3>

            <form id="activityForm" @submit.prevent="saveActivity()" class="space-y-5">
                @csrf
                <template x-if="editModeActivity">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label for="activity_name" class="block font-medium mb-1 text-gray-700">Name</label>
                    <input
                        id="activity_name"
                        type="text"
                        name="name"
                        x-model="form.name"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Name"
                        required
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label for="activity_description" class="block font-medium mb-1 text-gray-700">Description</label>
                    <textarea
                        id="activity_description"
                        name="description"
                        x-model="form.description"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Description"
                        rows="3"
                        autocomplete="off"
                    ></textarea>
                </div>

                <div>
                    <label for="activity_duration" class="block font-medium mb-1 text-gray-700">Duration</label>
                    <input
                        id="activity_duration"
                        type="text"
                        name="duration"
                        x-model="form.duration"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="e.g., 2 hours"
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label for="activity_price" class="block font-medium mb-1 text-gray-700">Price</label>
                    <input
                        id="activity_price"
                        type="number"
                        step="0.01"
                        name="price"
                        x-model="form.price"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="e.g., 500.00"
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label for="activity_category" class="block font-medium mb-1 text-gray-700">Category</label>
                    <select
                        id="activity_category"
                        name="category"
                        x-model="form.category"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        required
                        autocomplete="off"
                    >
                        <option value="">Select Category</option>
                        @foreach($activityOnlyCategoryOptions as $categoryKey => $categoryLabel)
                            <option value="{{ $categoryKey }}">{{ $categoryLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="activity_image" class="block font-medium mb-1 text-gray-700">Upload Image</label>
                    <input
                        id="activity_image"
                        type="file"
                        name="image"
                        x-ref="activityImage"
                        accept="image/*"
                        @change="handleImageUpload($event)"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :required="!editModeActivity"
                    >
                    <small class="text-gray-500">Supported formats: JPG, PNG, GIF. Max size: 5MB</small>
                    <template x-if="form.imagePreview">
                        <div class="mt-2">
                            <img :src="form.imagePreview" alt="Image preview" class="w-32 h-20 object-cover rounded border">
                            <button type="button" @click="removeImage()" class="ml-2 text-red-500 hover:underline">Remove</button>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" @click="showActivityModal = false; resetForm()" class="{{ $adminModalCancelButton }}">Cancel</button>
                    <button type="submit" :disabled="isLoading || isEditHydrating" class="{{ $adminPrimaryButton }} disabled:opacity-60">
                        <span x-text="editModeActivity ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- SWIPER IMAGES MANAGEMENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="mt-12 {{ $adminSectionCard }}">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Swiper Images Management</h2>
                <p class="text-gray-500 text-sm">Manage carousel images for activities.</p>
            </div>
            <button
                @click="showModal = true"
                class="{{ $adminPrimaryButton }}"
            >
                + Add Image
            </button>
        </div>

        {{-- Images Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100/70 backdrop-blur">
                        <tr>
                            <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">#</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Preview</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Activity Name</th>
                            <th class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Title</th>
                            <th class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900">Uploaded</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-4 font-medium text-gray-900 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($images as $index => $img)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4">
                                    <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-16 h-10 sm:w-20 sm:h-12 object-cover rounded-xl">
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->activity->name ?? 'Untitled' }}</td>
                                <td class="hidden sm:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->title ?? 'Untitled' }}</td>
                                <td class="hidden md:table-cell px-3 sm:px-4 lg:px-6 py-4 text-gray-700">{{ $img->created_at->format('Y-m-d') }}</td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1 sm:gap-2 whitespace-nowrap">
                                        <button
                                            type="button"
                                            class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition text-xs sm:text-sm"
                                            @click="editImage({{ $img->id }}, '{{ $img->activity_id }}', '{{ $img->title }}')"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="text-red-600 font-semibold hover:text-red-700 hover:underline transition text-xs sm:text-sm"
                                            @click="deleteImage({{ $img->id }})"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 sm:px-6 py-6 text-center text-gray-500">No images uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Swiper Image Modal --}}
    <div
        x-cloak
        x-show="showModal"
        class="{{ $adminModalOverlay }}"
        x-transition.opacity
    >
        <div @click.away="showModal = false; resetImageForm()" class="{{ $adminModalPanelSm }}">
            <button
                @click="showModal = false; resetImageForm()"
                class="{{ $adminModalCloseButton }}"
            >&times;</button>

            <h3 class="{{ $adminModalTitle }} mb-4" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                method="POST"
                @submit.prevent="uploadOrUpdateImage($el)"
            >
                @csrf

                <div>
                    <label for="image_activity" class="block font-medium mb-1 text-gray-700">Activity</label>
                    <select id="image_activity" x-ref="activity" name="activity_id" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required autocomplete="off">
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="image_title" class="block font-medium mb-1 text-gray-700">Title (Optional)</label>
                    <input id="image_title" type="text" x-ref="title" name="title" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter image title" autocomplete="off">
                </div>

                <div>
                    <label for="image_file" class="block font-medium mb-1 text-gray-700">Upload Image/Video</label>
                    <input id="image_file" type="file" x-ref="image" name="image" accept="image/*,video/*" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" :required="!editImageMode">
                    <small class="text-gray-500">Supported formats: JPG, PNG, GIF, MP4. Max size: 5MB</small>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" @click="showModal = false; resetImageForm()" class="{{ $adminModalCancelButton }}">Cancel</button>
                    <button type="submit" class="{{ $adminPrimaryButton }}" x-text="editImageMode ? 'Update' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- TEXT CONTENT MANAGEMENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6 mt-12">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Text Content</h2>
            <p class="text-gray-500 text-sm">Paragraphs and captions displayed on this page.</p>
        </div>
        <button 
            @click="showTextModal = true; resetTextForm()"
            class="px-4 py-2 rounded-xl bg-[#8f5d54] text-white font-medium shadow hover:bg-[#7a4d46] hover:shadow-lg transition">
            + Add Text
        </button>
    </div>

    {{-- Text Content Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-4 lg:px-6 py-4 font-medium text-gray-900">Label</th>
                        <th class="px-4 lg:px-6 py-4 font-medium text-gray-900">Text</th>
                        <th class="px-4 lg:px-6 py-4 font-medium text-gray-900 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($texts as $text)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-4 lg:px-6 py-4 text-gray-700 font-medium">{{ $text->label ?? 'N/A' }}</td>
                            <td class="px-4 lg:px-6 py-4 text-gray-700 max-w-md truncate">{{ $text->text ?? 'N/A' }}</td>
                            <td class="px-4 lg:px-6 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="text-[#8f5d54] font-semibold hover:text-[#7a4d46] hover:underline transition"
                                        @click="editText({{ $text->id }}, '{{ addslashes($text->label) }}', '{{ addslashes($text->text) }}')"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 hover:underline transition"
                                        @click="deleteText({{ $text->id }})"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 lg:px-6 py-6 text-center text-gray-500">No text content yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Text Content Modal --}}
    <div
        x-cloak
        x-show="showTextModal"
        class="{{ $adminModalOverlay }}"
        x-transition.opacity
    >
        <div @click.away="showTextModal = false; resetTextForm()" class="{{ $adminModalPanelSm }}">
            <button
                @click="showTextModal = false; resetTextForm()"
                class="{{ $adminModalCloseButton }}"
            >&times;</button>

            <h3 class="{{ $adminModalTitle }} mb-4" x-text="editTextMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form
                id="textForm"
                class="space-y-5"
                method="POST"
                @submit.prevent="addOrUpdateText($el)"
            >
                @csrf

                <div>
                    <label for="text_label" class="block font-medium mb-1 text-gray-700">Label</label>
                    <input id="text_label" type="text" x-ref="textLabel" name="label" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#8f5d54] focus:outline-none" placeholder="Enter label (e.g., section_title)" required>
                </div>

                <div>
                    <label for="text_content" class="block font-medium mb-1 text-gray-700">Text Content</label>
                    <textarea id="text_content" x-ref="textContent" name="text" rows="4" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#8f5d54] focus:outline-none" placeholder="Enter text content" required></textarea>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" @click="showTextModal = false; resetTextForm()" class="{{ $adminModalCancelButton }}">Cancel</button>
                    <button type="submit" class="{{ $adminPrimaryButton }}" x-text="editTextMode ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
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
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved',
                    text: 'Mobile layout updated.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1800
                });
            } else {
                alert('Mobile layout updated.');
            }
        })
        .catch((err) => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to update mobile layout setting.' });
            } else {
                alert(err.message || 'Failed to update mobile layout setting.');
            }
        });
    }
}

const activityCategoryLabels = @json($activityCategoryOptions);

function activityManager() {
    return {
        // State management
        isLoading: false,
        isEditHydrating: false,
        showModal: false,
        showActivityModal: false,
        showTextModal: false,
        showPackageModal: false,
        editMode: false,
        editModeActivity: false,
        editPackageMode: false,
        editImageMode: false,
        editTextMode: false,
        currentImageId: null,
        currentTextId: null,
        searchQuery: '',
        textLabel: '',
        textContent: '',

        // Load packages on initialization
        loadPackages() {
            this.packages = @js($activityPackages ?? []);
        },

        // Load available activities on initialization
        loadAvailableActivities() {
            this.availableActivities = @js(($allActivities ?? $activities)->map(function($a) { 
                return [
                    'id' => (string) $a->id,
                    'name' => $a->name, 
                    'price' => (float)($a->price ?? 0)
                ]; 
            })->toArray());
            if (this.showPackageModal && this.packageForm?.items?.length > 0) {
                this.syncPackageActivitySelections();
            }
        },

        // Data
        texts: [],
        packages: @js($activityPackages ?? []),
        availableActivities: [],

        // Form data
        form: {
            id: null,
            name: '',
            description: '',
            duration: '',
            price: '',
            category: '',
            activity_id: '',
            label: '',
            text: '',
            title: '',
            image: null,
            imagePreview: null,
        },

        // Package form data
        packageForm: {
            id: null,
            name: '',
            category: '',
            description: '',
            is_active: true,
            stock_quantity: 0,
            is_inventory_managed: false,
            items: [{ activity_id: '', quantity: 1 }]
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

        getActivityCategoryLabel(category) {
            const key = String(category || '').toLowerCase();
            if (key && activityCategoryLabels[key]) {
                return activityCategoryLabels[key];
            }

            if (!key) {
                return activityCategoryLabels.special || 'Special';
            }

            return key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
        },

        // Search and filter (simplified - uses x-show directives on rows)
        filterActivities() {
            // This method is called by @input on the search box,
            // but the actual filtering is handled by x-show directives on table rows
        },

        // Keyboard shortcuts
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.showActivityModal = false;
                    this.showModal = false;
                    this.showTextModal = false;
                } else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    const activeForm = document.querySelector('form');
                    if (activeForm) activeForm.dispatchEvent(new Event('submit'));
                }
            });
        },

        // Undo for delete
        scheduleDelete(id, name) {
            this.showUndoBanner(`Activity "${name}" marked for deletion`, id);
        },

        showUndoBanner(message, activityId) {
            const banner = document.createElement('div');
            banner.className = 'undo-banner';
            let secondsLeft = 5;
            banner.innerHTML = `
                <span>${message}</span>
                <button id="undo_action_${activityId}">Undo (${secondsLeft}s)</button>
            `;
            document.body.appendChild(banner);
            
            const btn = banner.querySelector(`#undo_action_${activityId}`);
            let timer = setInterval(() => {
                secondsLeft--;
                btn.textContent = `Undo (${secondsLeft}s)`;
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    banner.remove();
                    this.confirmDelete(activityId);
                }
            }, 1000);

            btn.addEventListener('click', () => {
                clearInterval(timer);
                banner.remove();
            });
        },

        // Methods
        resetForm() {
            this.isEditHydrating = false;
            this.form = {
                id: null,
                name: '',
                description: '',
                duration: '',
                price: '',
                category: '',
                activity_id: '',
                label: '',
                text: '',
                title: '',
                image: null,
                imagePreview: null,
            };
            this.editMode = false;
            this.editModeActivity = false;

            // Reset file input
            const activityImage = this.$refs.activityImage;
            if (activityImage) activityImage.value = '';
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;

            // Reset form elements
            const imageInput = this.$refs.image;
            const titleInput = this.$refs.title;
            const activitySelect = this.$refs.activity;

            if (imageInput) imageInput.value = '';
            if (titleInput) titleInput.value = '';
            if (activitySelect) activitySelect.value = '';
        },

        removeImage() {
            this.form.image = null;
            this.form.imagePreview = null;

            const activityImage = this.$refs.activityImage;
            if (activityImage) activityImage.value = '';
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                this.showToast('Please select a valid image file.', 'error');
                event.target.value = '';
                return;
            }

            // Validate file size (5MB limit)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                this.showToast('File size must not exceed 5MB.', 'error');
                event.target.value = '';
                return;
            }

            // Store file and create preview
            this.form.image = file;
            this.createImagePreview(file);
        },

        createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.form.imagePreview = e.target.result;
            };
            reader.onerror = () => {
                this.showToast('Error reading file.', 'error');
                this.form.image = null;
                this.form.imagePreview = null;
            };
            reader.readAsDataURL(file);
        },

        async editActivity(id) {
            this.resetForm();
            this.editModeActivity = true;
            this.showActivityModal = true;
            this.isEditHydrating = true;

            try {
                const response = await fetch(`/admin/activity-list/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    this.showToast('Failed to load activity', 'error');
                    return;
                }
                
                const activity = await response.json();

                // Populate form
                this.form.id = activity.id;
                this.form.name = activity.name || '';
                this.form.description = activity.description || '';
                this.form.duration = activity.duration || '';
                this.form.price = activity.price || '';
                this.form.category = activity.category || '';
                this.form.image = null;

                // Handle image preview
                this.setImagePreview(activity.image);
            } catch (error) {
                this.showActivityModal = false;
                this.showToast('Error loading activity data.', 'error');
            } finally {
                this.isEditHydrating = false;
            }
        },

        setImagePreview(imagePath) {
            if (imagePath) {
                const isFullUrl = imagePath.startsWith('http://') || imagePath.startsWith('https://');
                this.form.imagePreview = isFullUrl ? imagePath : `/storage/${imagePath}`;
            } else {
                this.form.imagePreview = null;
            }
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (!text) return;

            this.form.id = text.id;
            this.form.label = text.title || '';
            this.form.text = text.content || '';
            this.form.activity_id = text.activity_id || '';

            this.editMode = true;
            this.showTextModal = true;
        },

        deleteActivity(id) {
            const nameEl = event.target.closest('tr')?.querySelector('td:nth-child(2)');
            const actName = nameEl?.textContent || 'Activity';
            this.scheduleDelete(id, actName);
        },

        confirmDelete(id) {
            this.submitDeleteForm('activity-list', id);
        },

        deleteImage(id) {
            this.scheduleDeleteImage(id);
        },

        scheduleDeleteImage(id) {
            const image = document.querySelector(`[data-image-id="${id}"]`);
            if (image) {
                const actName = image.textContent;
                const banner = document.createElement('div');
                banner.className = 'undo-banner';
                let secondsLeft = 5;
                banner.innerHTML = `
                    <span>Image marked for deletion</span>
                    <button id="undo_img_${id}">Undo (${secondsLeft}s)</button>
                `;
                document.body.appendChild(banner);
                
                const btn = banner.querySelector(`#undo_img_${id}`);
                let timer = setInterval(() => {
                    secondsLeft--;
                    btn.textContent = `Undo (${secondsLeft}s)`;
                    if (secondsLeft <= 0) {
                        clearInterval(timer);
                        banner.remove();
                        this.submitDeleteForm('activity-image', id);
                    }
                }, 1000);

                btn.addEventListener('click', () => {
                    clearInterval(timer);
                    banner.remove();
                });
            }
        },

        submitDeleteForm(route, id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin') }}/${route}/${id}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        },

        async saveText() {
            const url = this.editMode
                ? `/admin/activity-text/${this.form.id}`
                : "{{ route('activity-text.store') }}";

            const formData = new FormData();
            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('activity_id', this.form.activity_id);
            if (this.editMode) formData.append('_method', 'PUT');

            this.isLoading = true;
            const submitBtn = document.querySelector('#textUploadForm button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editMode ? 'Updating...' : 'Adding...';
            submitBtn.disabled = true;

            const safetyTimeout = setTimeout(() => {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 45000);

            try {
                const response = await this.submitForm(url, formData);
                clearTimeout(safetyTimeout);

                if (response.ok) {
                    this.showToast(this.editMode ? 'Text updated!' : 'Text added!', 'success');
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                clearTimeout(safetyTimeout);
                this.showToast('Error submitting text.', 'error');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        async saveActivity() {
            const url = this.editModeActivity
                ? `/admin/activity-list/${this.form.id}`
                : "{{ route('activity-list.store') }}";

            const formData = new FormData();
            formData.append('name', this.form.name);
            formData.append('description', this.form.description);
            formData.append('duration', this.form.duration);
            formData.append('price', this.form.price);
            formData.append('category', this.form.category);

            if (this.form.image) {
                formData.append('image', this.form.image);
            }

            if (this.editModeActivity) {
                formData.append('_method', 'PUT');
            }

            this.isLoading = true;
            const submitBtn = document.querySelector('#activityForm button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editModeActivity ? 'Updating...' : 'Adding...';
            submitBtn.disabled = true;

            const safetyTimeout = setTimeout(() => {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 45000);

            try {
                const response = await this.submitForm(url, formData);
                clearTimeout(safetyTimeout);

                if (response.ok) {
                    this.showToast(this.editModeActivity ? 'Activity updated!' : 'Activity added!', 'success');
                    this.resetForm();
                    this.showActivityModal = false;
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                clearTimeout(safetyTimeout);
                this.showToast('Error submitting activity.', 'error');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        async uploadOrUpdateImage(formElement) {
            const fileInput = this.$refs.image;
            const titleInput = this.$refs.title;
            const activitySelect = this.$refs.activity;

            if (!fileInput?.files?.[0] && !this.editImageMode) {
                this.showToast('Please select an image before uploading.', 'error');
                return;
            }

            if (!activitySelect?.value) {
                this.showToast('Please select an activity.', 'error');
                return;
            }

            const formData = new FormData();
            if (fileInput.files[0]) formData.append('image', fileInput.files[0]);
            formData.append('title', titleInput?.value || '');
            formData.append('activity_id', activitySelect.value);

            if (this.editImageMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editImageMode
                ? `/admin/activity-image/${this.currentImageId}`
                : "{{ route('activity-image.store') }}";

            this.isLoading = true;
            const submitBtn = formElement.querySelector('button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editImageMode ? 'Updating...' : 'Uploading...';
            submitBtn.disabled = true;

            const safetyTimeout = setTimeout(() => {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 45000);

            try {
                const response = await this.submitForm(url, formData);
                clearTimeout(safetyTimeout);

                if (response.ok) {
                    this.showToast(this.editImageMode ? 'Image updated!' : 'Image uploaded!', 'success');
                    this.showModal = false;
                    this.resetImageForm();
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                clearTimeout(safetyTimeout);
                this.showToast('Error processing image.', 'error');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        async submitForm(url, formData) {
            const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                return response;
            } catch (error) {
                clearTimeout(timeoutId);
                throw error;
            }
        },

        async handleSuccess(response, title, defaultMessage) {
            const data = await response.json();
            const message = data.message || defaultMessage;
            this.showToast(message, 'success');
        },

        async handleError(response) {
            try {
                const data = await response.json();
                if (data.errors) {
                    const errorMessage = Object.values(data.errors).flat().join(', ');
                    this.showToast(errorMessage, 'error');
                } else {
                    this.showToast(data.message || 'Operation failed.', 'error');
                }
            } catch (e) {
                this.showToast('An error occurred.', 'error');
            }
        },

        editImage(id, activityId, title) {
            this.currentImageId = id;
            this.editImageMode = true;
            this.showModal = true;

            this.$nextTick(() => {
                const activitySelect = this.$refs.activity;
                const titleInput = this.$refs.title;

                if (activitySelect) activitySelect.value = activityId || '';
                if (titleInput) titleInput.value = title || '';
            });
        },

        // Text Management Methods
        resetTextForm() {
            this.editTextMode = false;
            this.currentTextId = null;
            this.textLabel = '';
            this.textContent = '';

            const labelInput = this.$refs.textLabel;
            const contentInput = this.$refs.textContent;

            if (labelInput) labelInput.value = '';
            if (contentInput) contentInput.value = '';
        },

        editText(id, label, content) {
            this.currentTextId = id;
            this.editTextMode = true;
            this.textLabel = label;
            this.textContent = content;
            this.showTextModal = true;

            this.$nextTick(() => {
                const labelInput = this.$refs.textLabel;
                const contentInput = this.$refs.textContent;

                if (labelInput) labelInput.value = label || '';
                if (contentInput) contentInput.value = content || '';
            });
        },

        deleteText(id) {
            if (!confirm('Are you sure you want to delete this text?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/activity-text/${id}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        },

        async addOrUpdateText(formElement) {
            const labelInput = this.$refs.textLabel;
            const contentInput = this.$refs.textContent;

            if (!labelInput?.value || !contentInput?.value) {
                this.showToast('Please fill in all fields.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('label', labelInput.value);
            formData.append('text', contentInput.value);

            if (this.editTextMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editTextMode
                ? `/admin/activity-text/${this.currentTextId}`
                : "{{ route('activity-text.store') }}";

            this.isLoading = true;
            const submitBtn = formElement.querySelector('button[type=submit]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = this.editTextMode ? 'Updating...' : 'Adding...';
            submitBtn.disabled = true;

            const safetyTimeout = setTimeout(() => {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 45000);

            try {
                const response = await this.submitForm(url, formData);
                clearTimeout(safetyTimeout);

                if (response.ok) {
                    this.showToast(this.editTextMode ? 'Text updated!' : 'Text added!', 'success');
                    this.resetTextForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else {
                    await this.handleError(response);
                }
            } catch (error) {
                clearTimeout(safetyTimeout);
                this.showToast('Error submitting text.', 'error');
            } finally {
                this.isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        },

        // Package management functions
        openCreatePackage() {
            this.editPackageMode = false;
            this.isEditHydrating = false;
            this.packageForm = {
                id: null,
                name: '',
                category: '',
                description: '',
                is_active: true,
                stock_quantity: 0,
                is_inventory_managed: false,
                items: [this.makePackageActivityItem()]
            };
            this.loadAvailableActivities();
            this.showPackageModal = true;
            this.syncPackageActivitySelections();
        },

        async editActivityPackage(id) {
            this.editPackageMode = true;
            this.isEditHydrating = true;
            this.showPackageModal = true;
            
            try {
                this.loadAvailableActivities();
                const res = await fetch(`/admin/do/activity-packages/${id}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                
                this.packageForm = {
                    id: data.id,
                    name: data.name || '',
                    category: data.category || '',
                    description: data.description || '',
                    is_active: !!data.is_active,
                    stock_quantity: data.stock_quantity ?? 0,
                    is_inventory_managed: !!data.is_inventory_managed,
                    items: data.activities && data.activities.length > 0 
                        ? data.activities.map(a => this.makePackageActivityItem(a.id, a.pivot?.quantity || 1))
                        : [this.makePackageActivityItem()]
                };
                
                this.syncPackageActivitySelections();
            } catch (err) {
                console.error('Error:', err);
                this.showPackageModal = false;
                this.showToast('Failed to load package', 'error');
            } finally {
                this.isEditHydrating = false;
            }
        },

        makePackageActivityItem(activityId = '', quantity = 1) {
            return {
                row_key: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                activity_id: activityId === null || activityId === undefined || activityId === '' ? '' : String(activityId),
                quantity: Number(quantity) || 1,
            };
        },

        syncPackageActivitySelections() {
            this.$nextTick(() => {
                const selects = document.querySelectorAll('select[name^="activities["]');
                selects.forEach((select, index) => {
                    const item = this.packageForm.items[index];
                    if (!item) return;
                    select.value = String(item.activity_id || '');
                });
            });
        },

        addPackageActivityItem() {
            this.packageForm.items.push(this.makePackageActivityItem());
            this.syncPackageActivitySelections();
        },

        removePackageActivityItem(index) {
            this.packageForm.items.splice(index, 1);
            if (this.packageForm.items.length === 0) {
                this.addPackageActivityItem();
            }
        },

        getActivityItemSubtotal(item) {
            const activity = this.availableActivities.find(a => a.id === String(item.activity_id || ''));
            return activity ? (activity.price * item.quantity).toFixed(2) : '0.00';
        },

        getPackageActivityTotal() {
            return this.packageForm.items.reduce((total, item) => {
                const activity = this.availableActivities.find(a => a.id === String(item.activity_id || ''));
                return total + (activity ? activity.price * item.quantity : 0);
            }, 0).toFixed(2);
        },

        async saveActivityPackage() {
            if (this.isLoading) return;
            this.isLoading = true;

            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const formData = new FormData();

            formData.append('name', this.packageForm.name);
            formData.append('category', this.packageForm.category);
            formData.append('description', this.packageForm.description || '');
            formData.append('price', this.getPackageActivityTotal());
            formData.append('is_active', this.packageForm.is_active ? 1 : 0);
            formData.append('stock_quantity', this.packageForm.stock_quantity ?? 0);
            formData.append('is_inventory_managed', this.packageForm.is_inventory_managed ? 1 : 0);

            // Add activities and quantities
            this.packageForm.items.forEach((item, index) => {
                if (item.activity_id) {
                    formData.append(`activities[${index}]`, item.activity_id);
                    formData.append(`quantities[${index}]`, item.quantity);
                }
            });

            let url = '{{ route("admin.activity-packages.store") }}';
            if (this.editPackageMode && this.packageForm.id) {
                url = `/admin/do/activity-packages/${this.packageForm.id}`;
                formData.append('_method', 'PUT');
            }

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();

                if (res.ok) {
                    const successMsg = this.editPackageMode ? 'Package updated successfully!' : 'Package created successfully!';
                    this.showToast(data.message || successMsg, 'success');
                    this.showPackageModal = false;
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    this.showToast(data.message || 'Failed to save package', 'error');
                }
            } catch (err) {
                console.error('Error:', err);
                this.showToast('An unexpected error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        deleteActivityPackage(id) {
            Swal.fire({
                title: 'Delete Package?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    this.isLoading = true;

                    fetch(`/admin/do/activity-packages/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showToast('Package deleted successfully!', 'success');
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            this.showToast(data.message || 'Failed to delete package', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        this.showToast('Failed to delete package', 'error');
                    })
                    .finally(() => { this.isLoading = false; });
                }
            });
        },

        get packagesHtml() {
            // Compute the HTML for packages table
            if (!this.packages || this.packages.length === 0) {
                return '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><div class="flex flex-col items-center justify-center gap-3"><i class="bi bi-package text-gray-400 text-3xl"></i><p>No packages yet. Click "Create Package" to add one.</p></div></td></tr>';
            }

            return this.packages.map((pkg, idx) => `
                <tr class="hover:bg-gray-50">
                    <td class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">${idx + 1}</td>
                    <td class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 text-sm font-medium text-gray-900">${pkg.name || ''}</td>
                    <td class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 text-sm text-gray-600">${this.getActivityCategoryLabel(pkg.category)}</td>
                    <td class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱${pkg.price ? parseFloat(pkg.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                    <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 text-sm text-gray-600">${pkg.activities?.length || 0}</td>
                    <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 text-sm"><span class="px-2 py-1 text-gray-700 rounded text-xs font-semibold">${pkg.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 whitespace-nowrap text-right text-sm space-x-2">
                        <button onclick="window.__alpineApp.editActivityPackage(${pkg.id})" class="text-blue-600 hover:text-blue-700 font-semibold">Edit</button>
                        <button onclick="window.__alpineApp.deleteActivityPackage(${pkg.id})" class="text-red-600 hover:text-red-700 font-semibold">Delete</button>
                    </td>
                </tr>
            `).join('');
        },
    };
}
</script>
@endsection

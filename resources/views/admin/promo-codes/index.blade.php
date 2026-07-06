@extends('layouts.admin')

@section('title', 'Promo Code Management')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('content')
<style>
    /* Toast notifications - matching Food Management */
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
    
    /* Undo notification - matching Food Management */
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
        font-size: 14px;
    }
    .undo-banner button {
        background: #fbbf24;
        color: #1f2937;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.2s;
    }
    .undo-banner button:hover { background: #fcd34d; }
</style>

<div
    x-data="promoCrud()"
    x-init="window.promoCrudInstance = $data; $data.setupKeyboardShortcuts(); $data.loadPromoCodes()"
    class="p-3 sm:p-6 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-lg sm:rounded-xl"
>
    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2 sm:mb-3 tracking-tight text-gray-900">
        Promo Code Management
    </h1>
    <p class="text-gray-600 text-sm sm:text-base mb-6 sm:mb-10">Manage discount codes, promotions, and special offers for bookings.</p>

    {{-- Search Box - Full width at top --}}
    <div class="mb-8 sm:mb-10">
        <input 
            id="search_promo"
            name="search"
            x-model="searchQuery"
            @input="filterPromoCodes()"
            type="text"
            autocomplete="off"
            placeholder="🔍 Search by promo code or name..." 
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border-2 border-gray-200 rounded-lg sm:rounded-xl focus:border-blue-500 focus:outline-none transition"
        >
    </div>

    {{-- Database Setup Notice --}}
    <div id="db-notice" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4 sm:mb-6 hidden">
        <div class="flex items-start">
            <div class="shrink-0">
                <i class="bi bi-exclamation-triangle text-amber-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-amber-800">Database Setup Required</h3>
                <p class="mt-1 text-sm text-amber-700">
                    The promo_codes table doesn't exist. Please run the migration command below to enable functionality.
                </p>
                <div class="mt-3">
                    <code class="bg-amber-100 px-3 py-2 rounded text-sm font-mono text-amber-800">php artisan migrate</code>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Alert --}}
    <div id="error-alert" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 sm:mb-6 hidden">
        <div class="flex items-start">
            <div class="shrink-0">
                <i class="bi bi-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error Loading Data</h3>
                <p class="mt-1 text-sm text-red-700" id="error-message"></p>
            </div>
        </div>
    </div>

    <div id="promo-analytics-panel" class="mb-6 sm:mb-8"></div>

    {{-- Promo Codes Table Section --}}
    <div class="mt-8 sm:mt-12">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 sm:gap-0 mb-4 sm:mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Promotional Codes</h2>
                <p class="text-gray-500 text-xs sm:text-sm">Create and manage discount codes for customers.</p>
            </div>

            <button
                type="button"
                @click="openCreate()"
                class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-blue-600 text-white text-sm sm:text-base font-medium shadow hover:bg-blue-700 hover:shadow-lg transition inline-flex items-center justify-center sm:justify-start gap-2">
                <i class="bi bi-plus-lg w-4 h-4"></i>
                <span class="hidden sm:inline">Add Promo Code</span>
                <span class="sm:hidden">Add Promo</span>
            </button>
        </div>

        <div class="overflow-hidden rounded-lg sm:rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <div class="overflow-x-auto md:overflow-visible">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-gray-100/70 backdrop-blur">
                        <tr>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 w-8 sm:w-12">#</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 min-w-32 sm:min-w-40">Code</th>
                            <th class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Name</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-center">Discount</th>
                            <th class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Validity</th>
                            <th class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Usage</th>
                            <th class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700">Status</th>
                            <th class="px-2 sm:px-3 md:px-6 py-3 sm:py-4 font-semibold text-gray-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="promo-codes-tbody">
                        <tr>
                            <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <i class="bi bi-hourglass-split animate-spin text-3xl text-gray-400"></i>
                                    <p>Loading promo codes...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-8 sm:mt-10">
        <div class="flex items-end justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Recent Promo Usage</h2>
                <p class="text-gray-500 text-xs sm:text-sm">Latest successful bookings that consumed a promo code.</p>
            </div>
        </div>
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-xl border border-gray-200 bg-white/70 p-3 sm:p-4 shadow-sm">
                <label for="usage_from" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">From</label>
                <input
                    type="date"
                    id="usage_from"
                    x-model="usageFrom"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none"
                >
            </div>
            <div class="rounded-xl border border-gray-200 bg-white/70 p-3 sm:p-4 shadow-sm">
                <label for="usage_to" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">To</label>
                <input
                    type="date"
                    id="usage_to"
                    x-model="usageTo"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none"
                >
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 items-end">
                <button
                    type="button"
                    @click="applyUsageDateFilter()"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Apply Filter
                </button>
                <button
                    type="button"
                    @click="resetUsageDateFilter()"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Reset
                </button>
                <button
                    type="button"
                    @click="exportUsageCsv()"
                    class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                    Export CSV
                </button>
            </div>
        </div>
        <div id="promo-usage-history-panel" class="overflow-hidden rounded-lg sm:rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur"></div>
    </div>

    {{-- Add/Edit Modal --}}
    <div 
        x-show="showModal"
        x-cloak
        style="display: none;"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-3 sm:p-4"
        x-transition.opacity
    >
        <div @click.away="showModal = false" class="bg-white/90 backdrop-blur-lg rounded-lg sm:rounded-2xl shadow-xl w-full max-w-2xl sm:max-w-3xl md:max-w-4xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 md:p-7 border border-gray-200 my-4">
            <button 
                @click="closeModal()"
                class="absolute top-2 right-3 sm:top-3 sm:right-4 text-gray-400 hover:text-gray-600 text-2xl sm:text-3xl leading-none"
            >&times;</button>

            <h3 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4 text-gray-900 pr-8" x-text="getModalTitle()"></h3>

            <form class="space-y-4 sm:space-y-6" @submit.prevent="submitForm">
                <div>
                    <label for="promo_code" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Promo Code *</label>
                    <input type="text" x-model="form.code" id="promo_code" name="code" required
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition uppercase"
                           placeholder="e.g., SUMMER25">
                    <p class="mt-1 text-xs text-gray-500">Unique code for the promotion</p>
                </div>

                <div>
                    <label for="promo_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Name *</label>
                    <input type="text" x-model="form.name" id="promo_name" name="name" required
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="e.g., Summer Sale 2025">
                    <p class="mt-1 text-xs text-gray-500">Display name for the promo code</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                    <div>
                        <label for="discount_type" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Discount Type *</label>
                        <select x-model="form.discount_type" id="discount_type" name="discount_type" required
                                autocomplete="off"
                                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₱)</option>
                        </select>
                    </div>

                    <div>
                        <label for="discount_value" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Discount Value *</label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" x-model="form.discount_value" id="discount_value" name="discount_value" required
                                   autocomplete="off"
                                   class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                   placeholder="0.00">
                            <span x-text="form.discount_type === 'percentage' ? '%' : '₱'" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium"></span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500" x-text="form.discount_type === 'percentage' ? 'Max 100%' : 'Amount in Philippine Peso (₱)'"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                    <div>
                        <label for="start_date" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Start Date *</label>
                        <input type="date" x-model="form.start_date" id="start_date" name="start_date" required
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">End Date *</label>
                        <input type="date" x-model="form.end_date" id="end_date" name="end_date" required
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                    <div>
                        <label for="usage_limit" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Usage Limit</label>
                        <input type="number" min="1" x-model="form.usage_limit" id="usage_limit" name="usage_limit"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Unlimited">
                        <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited</p>
                    </div>

                    <div>
                        <label for="min_booking_amount" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Min Booking Amount</label>
                        <input type="number" step="0.01" min="0" x-model="form.min_booking_amount" id="min_booking_amount" name="min_booking_amount"
                               autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="₱0.00">
                        <p class="mt-1 text-xs text-gray-500">Minimum booking amount to apply discount</p>
                    </div>
                </div>

                <div>
                    <label for="promo_description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Description</label>
                    <textarea rows="3" x-model="form.description" id="promo_description" name="description"
                              autocomplete="off"
                              class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                              placeholder="Optional description of the promotion..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Optional description of the promotion</p>
                </div>

                <div class="space-y-3 p-3 sm:p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" id="promo_is_active" name="is_active"
                               autocomplete="off"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-semibold text-xs sm:text-sm text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-600">When checked, this promo code will be available for customers to use</p>
                        </div>
                    </label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 sm:px-6 py-2 text-sm sm:text-base border rounded-lg sm:rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading"
                            class="px-4 sm:px-5 py-2 text-sm sm:text-base rounded-lg sm:rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition disabled:opacity-60"
                            x-text="loading ? 'Saving...' : (editMode ? 'Update Promo Code' : 'Create Promo Code')">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function promoCrud() {
    return {
        showModal: false,
        editMode: false,
        loading: false,
        searchQuery: '',
        promoCodes: [],
        analytics: {},
        recentUsages: [],
        selectedPromoId: null,
        selectedPromo: null,
        usageFrom: '',
        usageTo: '',
        pendingDelete: null,
        
        form: {
            id: null,
            code: '',
            name: '',
            discount_type: 'percentage',
            discount_value: '',
            start_date: '',
            end_date: '',
            usage_limit: '',
            min_booking_amount: '',
            description: '',
            is_active: true
        },
        
        getModalTitle() {
            return this.editMode ? 'Edit Promo Code' : 'Create Promo Code';
        },
        
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.showModal = false;
                }
            });
        },
        
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },
        
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
        
        showAlert(config) {
            return Swal.fire({
                confirmButtonText: 'OK',
                title: config.title || 'Alert',
                text: config.text || '',
                icon: config.icon || 'info',
                ...config
            });
        },
        
        async loadPromoCodes(promoId = null, usageFrom = null, usageTo = null) {
            const tbody = document.getElementById('promo-codes-tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <i class="bi bi-hourglass-split animate-spin text-3xl text-gray-400"></i>
                            <p>Loading promo codes...</p>
                        </div>
                    </td>
                </tr>
            `;

            try {
                const params = new URLSearchParams();
                if (promoId) {
                    params.set('promo_id', promoId);
                }
                if (usageFrom) {
                    params.set('usage_from', usageFrom);
                }
                if (usageTo) {
                    params.set('usage_to', usageTo);
                }

                const response = await fetch(params.toString()
                    ? `{{ route('admin.promo-codes.index') }}?${params.toString()}`
                    : `{{ route('admin.promo-codes.index') }}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                if (data.error) {
                    document.getElementById('db-notice').classList.remove('hidden');
                    this.showToast(data.message, 'warning');
                    this.promoCodes = [];
                    this.analytics = this.emptyAnalytics();
                    this.recentUsages = [];
                    this.selectedPromoId = null;
                    this.selectedPromo = null;
                    this.usageFrom = '';
                    this.usageTo = '';
                } else {
                    document.getElementById('db-notice').classList.add('hidden');
                    this.promoCodes = data.data || (Array.isArray(data) ? data : []);
                    this.analytics = data.analytics || this.emptyAnalytics();
                    this.recentUsages = data.recent_usages || [];
                    this.selectedPromoId = promoId || null;
                    this.selectedPromo = data.selected_promo || null;
                    this.usageFrom = data.usage_filters?.from || usageFrom || '';
                    this.usageTo = data.usage_filters?.to || usageTo || '';
                }
                this.renderTable();
                this.renderAnalytics();
                this.renderUsageHistory();
            } catch (error) {
                document.getElementById('error-alert').classList.remove('hidden');
                document.getElementById('error-message').textContent = error.message;
                this.showToast('Error loading promo codes: ' + error.message, 'error');
                this.promoCodes = [];
                this.analytics = this.emptyAnalytics();
                this.recentUsages = [];
                this.selectedPromoId = null;
                this.selectedPromo = null;
                this.usageFrom = '';
                this.usageTo = '';
                this.renderTable();
                this.renderAnalytics();
                this.renderUsageHistory();
            }
        },

        clearUsageFilter() {
            this.usageFrom = '';
            this.usageTo = '';
            this.loadPromoCodes();
        },

        filterPromoUsage(promoId) {
            this.loadPromoCodes(promoId, this.usageFrom, this.usageTo);
        },

        applyUsageDateFilter() {
            this.loadPromoCodes(this.selectedPromoId, this.usageFrom, this.usageTo);
        },

        resetUsageDateFilter() {
            this.usageFrom = '';
            this.usageTo = '';
            this.loadPromoCodes(this.selectedPromoId);
        },

        exportUsageCsv() {
            const rows = this.recentUsages || [];
            if (!rows.length) {
                this.showToast('No usage rows to export', 'info');
                return;
            }

            const headers = ['Promo Code', 'Promo Name', 'Customer', 'Email', 'Booking ID', 'Transaction ID', 'Checkout ID', 'Payment Reference', 'Discount', 'Used At', 'Booking URL'];
            const lines = [
                headers.join(',')
            ];

            rows.forEach((usage) => {
                const values = [
                    usage.promo_code || '',
                    usage.promo_name || '',
                    usage.booking?.customer_name || '',
                    usage.booking?.customer_email || '',
                    usage.booking_id || '',
                    usage.booking_transaction_id || '',
                    usage.checkout_id || '',
                    usage.payment_reference || '',
                    (usage.discount_amount ?? 0).toString(),
                    usage.created_at || '',
                    usage.booking_url || ''
                ].map(value => `"${String(value).replace(/"/g, '""')}"`);
                lines.push(values.join(','));
            });

            const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = this.selectedPromo?.code
                ? `promo-usage-${this.selectedPromo.code.toLowerCase()}.csv`
                : 'promo-usage-history.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        },
        
        filterPromoCodes() {
            this.renderTable();
        },

        emptyAnalytics() {
            return {
                total_promo_codes: 0,
                active_promo_codes: 0,
                inactive_promo_codes: 0,
                expired_promo_codes: 0,
                usage_limit_reached: 0,
                expiring_soon: 0,
                total_uses: 0,
                total_usage_limit: 0,
                remaining_uses: 0,
                top_promo: null
            };
        },

        formatMoney(value) {
            const amount = Number(value || 0);
            return `₱${amount.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        },

        renderAnalytics() {
            const panel = document.getElementById('promo-analytics-panel');
            if (!panel) return;

            const analytics = this.analytics || this.emptyAnalytics();
            const topPromo = analytics.top_promo;

            panel.innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
                    <div class="rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Total Codes</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">${analytics.total_promo_codes ?? 0}</p>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600">
                                <i class="bi bi-ticket-perforated text-lg"></i>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Active</p>
                                <p class="mt-1 text-2xl font-bold text-green-700">${analytics.active_promo_codes ?? 0}</p>
                            </div>
                            <div class="rounded-full bg-green-100 p-3 text-green-700">
                                <i class="bi bi-check-circle text-lg"></i>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Total Uses</p>
                                <p class="mt-1 text-2xl font-bold text-blue-700">${analytics.total_uses ?? 0}</p>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3 text-blue-700">
                                <i class="bi bi-graph-up-arrow text-lg"></i>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Remaining Uses</p>
                                <p class="mt-1 text-2xl font-bold text-amber-700">${analytics.remaining_uses ?? 0}</p>
                            </div>
                            <div class="rounded-full bg-amber-100 p-3 text-amber-700">
                                <i class="bi bi-hourglass-split text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 xl:grid-cols-3 gap-3 sm:gap-4">
                    <div class="rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Usage Cap Reached</p>
                        <p class="mt-2 text-2xl font-bold text-red-600">${analytics.usage_limit_reached ?? 0}</p>
                        <p class="mt-1 text-sm text-gray-500">Promos that already hit their limit.</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Expiring Soon</p>
                        <p class="mt-2 text-2xl font-bold text-orange-600">${analytics.expiring_soon ?? 0}</p>
                        <p class="mt-1 text-sm text-gray-500">Ends within the next 7 days.</p>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white/80 shadow-sm">
                        ${
                            topPromo
                                ? `
                                    <button
                                        type="button"
                                        class="block w-full p-4 text-left transition hover:bg-blue-50/70 cursor-pointer"
                                        onclick="window.promoCrudInstance.filterPromoUsage(${topPromo.id})"
                                        title="View usage history for ${this.escapeHtml(topPromo.code || '')}">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Top Promo</p>
                                        <p class="mt-2 text-lg font-bold text-gray-900">${this.escapeHtml(topPromo.code || '')}</p>
                                        <p class="text-sm text-gray-600">${this.escapeHtml(topPromo.name || '')}</p>
                                        <p class="mt-2 text-sm text-gray-500">${topPromo.usage_count ?? 0}${topPromo.usage_limit ? ` / ${topPromo.usage_limit} used` : ' uses recorded'}</p>
                                        ${topPromo.remaining_uses !== null && topPromo.remaining_uses !== undefined ? `<p class="text-sm text-gray-500">${topPromo.remaining_uses} left</p>` : ''}
                                        <p class="mt-3 text-xs font-semibold text-blue-600">Click to view usage history</p>
                                    </button>
                                `
                                : '<div class="p-4"><p class="text-xs uppercase tracking-wide text-gray-500">Top Promo</p><p class="mt-2 text-sm text-gray-500">No usage yet.</p></div>'
                        }
                    </div>
                </div>
            `;
        },

        renderUsageHistory() {
            const panel = document.getElementById('promo-usage-history-panel');
            if (!panel) return;

            const rows = this.recentUsages || [];
            const selectedPromo = this.selectedPromo;
            const scopeLabel = selectedPromo
                ? `Promo usage for ${this.escapeHtml(selectedPromo.code || '')}`
                : 'Recent Promo Usage';
            const scopeMeta = selectedPromo
                ? `${this.escapeHtml(selectedPromo.name || '')} • ${selectedPromo.usage_count ?? 0}${selectedPromo.usage_limit ? ` / ${selectedPromo.usage_limit} used` : ' uses'}`
                : 'Latest successful bookings that consumed a promo code.';

            const headerHtml = `
                <div class="px-6 py-5 border-b border-gray-200 bg-white/70">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-800">${scopeLabel}</h3>
                            <p class="text-sm text-gray-500">${scopeMeta}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">${rows.length} record${rows.length === 1 ? '' : 's'}</span>
                            ${selectedPromo && selectedPromo.remaining_uses !== null && selectedPromo.remaining_uses !== undefined ? `<span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">${selectedPromo.remaining_uses} left</span>` : ''}
                            ${selectedPromo ? '<button type="button" class="text-sm font-semibold text-blue-600 hover:text-blue-700" onclick="window.promoCrudInstance.clearUsageFilter()">View all</button>' : ''}
                        </div>
                    </div>
                </div>
            `;

            if (rows.length === 0) {
                const emptyMessage = selectedPromo && (selectedPromo.usage_count ?? 0) > 0
                    ? 'This promo has usage count, but no history log rows were found yet. Older bookings may predate usage logging, or the current date filter may be too narrow.'
                    : selectedPromo
                        ? 'Successful bookings for this promo will appear here.'
                        : 'Successful bookings that use a promo will appear here.';

                panel.innerHTML = `
                    ${headerHtml}
                    <div class="px-6 py-10 text-center text-gray-500">
                        <i class="bi bi-clock-history text-3xl text-gray-300"></i>
                        <p class="mt-3 font-medium">${selectedPromo ? 'No usage found for this promo yet' : 'No promo usage history yet'}</p>
                        <p class="text-sm">${emptyMessage}</p>
                    </div>
                `;
                return;
            }

            panel.innerHTML = `
                ${headerHtml}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-gray-100/70 backdrop-blur">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Promo</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Booking</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Transaction</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Checkout</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Reference</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700 text-right">Discount</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Used At</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700 text-right">Booking Link</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${rows.map((usage) => `
                                <tr class="bg-white/70 hover:bg-gray-50/80 transition">
                                    <td class="px-3 sm:px-4 py-3">
                                        <div class="font-medium text-gray-900">${this.escapeHtml(usage.promo_code || '')}</div>
                                        <div class="text-xs text-gray-500">${this.escapeHtml(usage.promo_name || '')}</div>
                                    </td>
                                    <td class="px-3 sm:px-4 py-3">
                                        ${
                                            usage.booking_url
                                                ? `<a href="${usage.booking_url}" class="block font-medium text-blue-700 hover:text-blue-800 hover:underline" target="_blank" rel="noopener">${this.escapeHtml(usage.booking?.customer_name || '-')}</a>`
                                                : `<div class="font-medium text-gray-900">${this.escapeHtml(usage.booking?.customer_name || '-')}</div>`
                                        }
                                        <div class="text-xs text-gray-500">${this.escapeHtml(usage.booking?.customer_email || '')}</div>
                                        <div class="text-xs text-gray-500">#${this.escapeHtml(String(usage.booking_id || '-'))}</div>
                                        <div class="text-xs text-gray-500">${this.escapeHtml((usage.booking?.payment_status || '-') + ' / ' + (usage.booking?.status || '-'))}</div>
                                    </td>
                                    <td class="px-3 sm:px-4 py-3 font-mono text-xs text-gray-700">${this.escapeHtml(usage.booking_transaction_id || '')}</td>
                                    <td class="px-3 sm:px-4 py-3 font-mono text-xs text-gray-700">${this.escapeHtml(usage.checkout_id || '-')}</td>
                                    <td class="px-3 sm:px-4 py-3 font-mono text-xs text-gray-700">${this.escapeHtml(usage.payment_reference || '-')}</td>
                                    <td class="px-3 sm:px-4 py-3 text-right font-semibold text-blue-700">${this.formatMoney(usage.discount_amount || 0)}</td>
                                    <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 whitespace-nowrap">${this.formatDateTime(usage.created_at)}</td>
                                    <td class="px-3 sm:px-4 py-3 text-right">
                                        ${usage.booking_url
                                            ? `<a href="${usage.booking_url}" class="inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 hover:text-blue-800 transition" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i>View</a>`
                                            : '<span class="text-xs text-gray-400">-</span>'
                                        }
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        },

        renderTable() {
            const tbody = document.getElementById('promo-codes-tbody');
            if (!tbody) return;
            
            let filteredCodes = this.promoCodes;
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filteredCodes = this.promoCodes.filter(promo => 
                    promo.code.toLowerCase().includes(query) || 
                    promo.name.toLowerCase().includes(query)
                );
            }
            
            if (filteredCodes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="bi bi-ticket-perforated text-5xl text-gray-300"></i>
                                <p class="text-lg font-medium">No promo codes found</p>
                                <p class="text-sm">Click "Add Promo Code" to create your first promo code.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = filteredCodes.map((promo, index) => {
                const discountDisplay = promo.discount_type === 'percentage'
                    ? `${promo.discount_value}%`
                    : `₱${parseFloat(promo.discount_value).toFixed(2)}`;
                
                const isActive = promo.is_active;
                const statusBadge = isActive
                    ? '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i class="bi bi-check-circle-fill mr-1 text-xs"></i>Active</span>'
                    : '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i class="bi bi-x-circle-fill mr-1 text-xs"></i>Inactive</span>';

                const usageBadge = promo.usage_limit
                    ? `<span class="font-medium text-gray-700">${promo.usage_count ?? 0}/${promo.usage_limit} used</span>`
                    : '<span class="text-green-600 font-medium">Unlimited</span>';
                
                return `
                    <tr class="hover:bg-gray-50/70 transition border-b border-gray-200 last:border-b-0">
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900">${index + 1}</td>
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            <div class="min-w-0 max-w-xs sm:max-w-sm md:max-w-none">
                                <div class="text-xs sm:text-sm font-medium text-gray-900 font-mono">${this.escapeHtml(promo.code)}</div>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            <div class="text-xs sm:text-sm font-medium text-gray-900">${this.escapeHtml(promo.name)}</div>
                            ${promo.description ? `<div class="text-gray-500 text-xs line-clamp-1">${this.escapeHtml(promo.description.substring(0, 50))}</div>` : ''}
                        </td>
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 font-medium text-gray-900 text-center">
                            <span class="text-sm font-bold text-blue-600">${discountDisplay}</span>
                        </td>
                        <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-xs text-gray-500">
                            ${this.formatDate(promo.start_date)}<br><span class="text-xs">to ${this.formatDate(promo.end_date)}</span>
                        </td>
                        <td class="hidden lg:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-xs">
                            ${usageBadge}
                        </td>
                        <td class="hidden md:table-cell px-2 sm:px-3 md:px-6 py-2 sm:py-4">
                            ${statusBadge}
                        </td>
                        <td class="px-2 sm:px-3 md:px-6 py-2 sm:py-4 text-right sticky right-0 bg-white/60 md:bg-transparent z-10 md:z-auto">
                            <div class="flex justify-end gap-2 sm:gap-3 md:gap-4 flex-nowrap">
                                <button type="button"
                                        class="text-emerald-600 font-semibold hover:text-emerald-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="filterPromoUsage(${promo.id})"
                                        title="View Usage History">
                                    <span class="hidden sm:inline">History</span><span class="sm:hidden">Hist</span>
                                </button>
                                <button type="button"
                                        class="text-blue-600 font-semibold hover:text-blue-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="openEdit(${promo.id})"
                                        title="Edit">
                                    <span class="hidden sm:inline">Edit</span><span class="sm:hidden">✎</span>
                                </button>
                                <button type="button"
                                        class="text-yellow-600 font-semibold hover:text-yellow-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="toggleStatus(${promo.id})"
                                        title="${isActive ? 'Deactivate' : 'Activate'}">
                                    <span class="hidden sm:inline">${isActive ? 'Disable' : 'Enable'}</span><span class="sm:hidden">${isActive ? '🔘' : '⚡'}</span>
                                </button>
                                <button type="button"
                                        class="text-red-600 font-semibold hover:text-red-700 transition text-xs sm:text-sm whitespace-nowrap hover:underline"
                                        @click="deletePromoCode(${promo.id}, '${this.escapeHtml(promo.code)}')"
                                        title="Delete">
                                    <span class="hidden sm:inline">Delete</span><span class="sm:hidden">✕</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        },
        
        openCreate() {
            this.resetForm();
            this.showModal = true;
        },
        
        // FIXED: Edit function with proper date format handling
        async openEdit(id) {
            this.resetForm();
            this.loading = true;
            
            try {
                const response = await fetch(`{{ url('admin/promo-codes') }}/${id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const promo = await response.json();
                
                this.editMode = true;
                this.form.id = promo.id;
                this.form.code = promo.code;
                this.form.name = promo.name;
                this.form.discount_type = promo.discount_type;
                this.form.discount_value = promo.discount_value;
                
                // FIX: Ensure dates are in YYYY-MM-DD format for date input
                // Handle possible formats: "2024-05-05", "2024-05-05 00:00:00", or "2024-05-05T00:00:00.000Z"
                if (promo.start_date) {
                    let startDate = promo.start_date;
                    if (startDate.includes(' ')) {
                        startDate = startDate.split(' ')[0];
                    }
                    if (startDate.includes('T')) {
                        startDate = startDate.split('T')[0];
                    }
                    this.form.start_date = startDate;
                }
                
                if (promo.end_date) {
                    let endDate = promo.end_date;
                    if (endDate.includes(' ')) {
                        endDate = endDate.split(' ')[0];
                    }
                    if (endDate.includes('T')) {
                        endDate = endDate.split('T')[0];
                    }
                    this.form.end_date = endDate;
                }
                
                this.form.usage_limit = promo.usage_limit || '';
                this.form.min_booking_amount = promo.min_booking_amount || '';
                this.form.description = promo.description || '';
                this.form.is_active = !!promo.is_active;
                
                this.showModal = true;
            } catch (error) {
                this.showToast('Failed to load promo code data', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        resetForm() {
            this.editMode = false;
            this.loading = false;
            this.form = {
                id: null,
                code: '',
                name: '',
                discount_type: 'percentage',
                discount_value: '',
                start_date: '',
                end_date: '',
                usage_limit: '',
                min_booking_amount: '',
                description: '',
                is_active: true
            };
        },
        
        async submitForm() {
            if (this.loading) return;
            this.loading = true;
            
            const formData = new FormData();
            formData.append('code', this.form.code.toUpperCase());
            formData.append('name', this.form.name);
            formData.append('discount_type', this.form.discount_type);
            formData.append('discount_value', this.form.discount_value);
            formData.append('start_date', this.form.start_date);
            formData.append('end_date', this.form.end_date);
            formData.append('usage_limit', this.form.usage_limit || '');
            formData.append('min_booking_amount', this.form.min_booking_amount || '');
            formData.append('description', this.form.description || '');
            formData.append('is_active', this.form.is_active ? 1 : 0);
            
            let url = '{{ route('admin.promo-codes.store') }}';
            if (this.editMode && this.form.id) {
                url = `{{ url('admin/promo-codes') }}/${this.form.id}`;
                formData.append('_method', 'PUT');
            }
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && (data.success || data.message)) {
                    this.showAlert({
                        title: 'Success!',
                        text: data.message || 'Promo code saved successfully!',
                        icon: 'success'
                    }).then(() => {
                        this.showModal = false;
                        this.loadPromoCodes();
                    });
                } else if (data.errors) {
                    let errorMsg = Object.values(data.errors).flat().join('\n');
                    this.showAlert({
                        title: 'Validation Error!',
                        text: errorMsg,
                        icon: 'error'
                    });
                } else {
                    this.showAlert({
                        title: 'Error!',
                        text: data.message || 'Failed to save promo code.',
                        icon: 'error'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert({
                    title: 'Error!',
                    text: 'An error occurred while saving the promo code.',
                    icon: 'error'
                });
            } finally {
                this.loading = false;
            }
        },
        
        async toggleStatus(id) {
            const promo = this.promoCodes.find(p => p.id === id);
            if (!promo) {
                this.showToast('Promo code not found', 'error');
                return;
            }
            const newStatus = !promo.is_active;
            
            try {
                const response = await fetch(`{{ url('admin/promo-codes') }}/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ is_active: newStatus })
                });
                
                const data = await response.json();
                
                if (data.success || data.message) {
                    this.showToast(`Promo code ${newStatus ? 'activated' : 'deactivated'} successfully!`, 'success');
                    this.loadPromoCodes();
                } else {
                    this.showToast(data.message || 'Error toggling status', 'error');
                }
            } catch (error) {
                this.showToast('Error toggling status: ' + error.message, 'error');
            }
        },
        
        // DELETE WITH UNDO BANNER - Matching Food Management style
        deletePromoCode(id, code) {
            this.scheduleDelete(id, code);
        },
        
        scheduleDelete(id, code) {
            const banner = document.createElement('div');
            banner.className = 'undo-banner';
            let secondsLeft = 5;
            banner.innerHTML = `
                <span>Promo code "${code}" marked for deletion</span>
                <button id="undo_promo_${id}">Undo (${secondsLeft}s)</button>
            `;
            document.body.appendChild(banner);
            
            const btn = banner.querySelector(`#undo_promo_${id}`);
            let timer = setInterval(() => {
                secondsLeft--;
                btn.textContent = `Undo (${secondsLeft}s)`;
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    banner.remove();
                    this.confirmDelete(id);
                }
            }, 1000);
            
            btn.addEventListener('click', () => {
                clearInterval(timer);
                banner.remove();
                this.showToast('Deletion cancelled', 'info');
            });
        },
        
        async confirmDelete(id) {
            try {
                const response = await fetch(`{{ url('admin/promo-codes') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && (data.success || data.message)) {
                    this.showToast(data.message || 'Promo code deleted successfully!', 'success');
                    this.loadPromoCodes();
                } else {
                    this.showToast(data.message || 'Failed to delete promo code.', 'error');
                }
            } catch (error) {
                this.showToast('Error deleting promo code: ' + error.message, 'error');
            }
        },
        
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },

        formatDateTime(dateStr) {
            if (!dateStr) return '—';
            const date = new Date(dateStr);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        },

        escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
    };
}
</script>
@endsection

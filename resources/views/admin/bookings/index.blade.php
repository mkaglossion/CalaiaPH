@extends('layouts.admin')

@section('title', 'Bookings Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-gray-500">Operational · Inventory-connected</p>
            <h1 class="text-3xl font-semibold text-gray-900">Bookings</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.bookings.inventory') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4v10a2 2 0 01-2 2h-4a2 2 0 01-2-2V9H9v8a2 2 0 01-2 2H3z" />
                </svg>
                <span>Inventory</span>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5 19l14-14" />
                </svg>
                <span>Reset Filters</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        @php
            // Explicit chip styles keep icons visible even if utility classes are not available.
            $statCards = [
                array_merge($statCardsData[0], ['accent' => 'border-slate-700 text-slate-900', 'chip' => 'bg-slate-700 text-white', 'chipStyle' => 'background-color:#0f172a;color:#fff;', 'icon' => 'grid']),
                array_merge($statCardsData[1], ['accent' => 'border-emerald-600 text-emerald-900', 'chip' => 'bg-emerald-600 text-white', 'chipStyle' => 'background-color:#059669;color:#fff;', 'icon' => 'check']),
                array_merge($statCardsData[2], ['accent' => 'border-blue-600 text-blue-900', 'chip' => 'bg-blue-600 text-white', 'chipStyle' => 'background-color:#1d4ed8;color:#fff;', 'icon' => 'flag']),
                array_merge($statCardsData[3], ['accent' => 'border-rose-600 text-rose-900', 'chip' => 'bg-rose-600 text-white', 'chipStyle' => 'background-color:#e11d48;color:#fff;', 'icon' => 'ban']),
                array_merge($statCardsData[4], ['accent' => 'border-amber-600 text-amber-900', 'chip' => 'bg-amber-500 text-white', 'chipStyle' => 'background-color:#f59e0b;color:#fff;', 'icon' => 'sun']),
            ];
        @endphp
        @foreach($statCards as $card)
        <div class="p-4 rounded-xl shadow-sm bg-white border {{ $card['accent'] }} flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full {{ $card['chip'] }} shadow-sm" style="{{ $card['chipStyle'] ?? '' }}">
                @if($card['icon'] === 'grid')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>
                @elseif($card['icon'] === 'check')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @elseif($card['icon'] === 'flag')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5v16M5 5h10l-2 4 4 4H5"/></svg>
                @elseif($card['icon'] === 'ban')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 19L19 5"/></svg>
                @elseif($card['icon'] === 'sun')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                @endif
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                @if(isset($card['subtitle']) && $card['subtitle'])
                <p class="text-xs text-gray-500 mt-0.5">{{ $card['subtitle'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col md:flex-row gap-3 items-end flex-wrap">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" style="color:#6b7280;">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 16.5L21 21"/>
                    </svg>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                        placeholder="Name, email, ID"
                        class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                        style="padding-left:2.5rem;">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <label for="status" class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select id="status" name="status" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label for="booking_type" class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                <select id="booking_type" name="booking_type" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="glamping" {{ request('booking_type') == 'glamping' ? 'selected' : '' }}>Glamping</option>
                    <option value="camping" {{ request('booking_type') == 'camping' ? 'selected' : '' }}>Camping</option>
                    <option value="dayvisit" {{ request('booking_type') == 'dayvisit' ? 'selected' : '' }}>Day Visit</option>
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label for="date_from" class="block text-xs font-semibold text-gray-600 mb-1">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div class="w-full sm:w-auto">
                <label for="date_to" class="block text-xs font-semibold text-gray-600 mb-1">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-sm rounded-lg shadow hover:bg-amber-700" style="background-color:#f59e0b;color:#fff;border:1px solid #d97706;white-space:nowrap;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18L13 12.5V19l-2-1v-5.5z"/></svg>
                <span>Filter</span>
            </button>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200" style="white-space:nowrap;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5 19l14-14"/></svg>
                <span>Clear</span>
            </a>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Booking</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $booking->transaction_id }}</div>
                            <div class="text-xs text-gray-500">{{ $booking->created_at->format('M d, Y · H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $booking->display_customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $booking->customer_email }}</div>
                            @if($booking->customer_phone)
                            <div class="text-xs text-gray-500">{{ $booking->customer_phone }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeLabel = $booking->display_type_label;
                                $badgeColor = match($booking->booking_type) {
                                    'glamping' => 'bg-purple-100 text-purple-800',
                                    'camping' => 'bg-green-100 text-green-800',
                                    'dayvisit' => 'bg-amber-100 text-amber-800',
                                    'staycation' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($booking->checkin_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $booking->checkout_date ? \Carbon\Carbon::parse($booking->checkout_date)->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ₱{{ number_format($booking->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusBadge = [
                                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                                    'cancelled' => 'bg-rose-100 text-rose-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusBadge[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $paymentStatus = $booking->payment_status ?? 'pending';
                                $paymentColors = [
                                    'paid' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                ];
                                $pColor = $paymentColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $pColor }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                                @if($paymentStatus !== 'paid')
                                    <button type="button" 
                                        data-booking-id="{{ (int)$booking->id }}"
                                        data-booking-amount="{{ (float)$booking->total_amount }}"
                                        data-booking-name="{{ addslashes($booking->display_customer_name ?? $booking->customer_name) }}"
                                        onclick="handlePaymentClick(this)"
                                        class="inline-flex items-center px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition text-xs font-medium">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h10M5 21h14M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                        Pay
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.522 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z"/></svg>
                                <span>View</span>
                            </a>
                            
                            <!-- Status Menu Button -->
                            <div class="relative">
                                <button type="button" 
                                    onclick="toggleActionMenu(this)" 
                                    class="inline-flex items-center justify-center px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition" 
                                    title="More actions">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div class="action-menu hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-40">
                                    @if($booking->status !== 'completed')
                                        <button type="button" 
                                            onclick="updateReservationStatus({{ $booking->id }}, 'completed')"
                                            class="w-full text-left px-4 py-2 text-green-600 hover:bg-green-50 text-xs font-medium flex items-center gap-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Mark as Completed
                                        </button>
                                    @endif
                                    
                                    @if($booking->status !== 'confirmed')
                                        <button type="button" 
                                            onclick="updateReservationStatus({{ $booking->id }}, 'confirmed')"
                                            class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50 text-xs font-medium flex items-center gap-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Confirm
                                        </button>
                                    @endif
                                    
                                    @if($booking->status !== 'cancelled')
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <button type="button" 
                                            onclick="cancelReservation({{ $booking->id }}, '{{ addslashes($booking->display_customer_name) }}')"
                                            class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-xs font-medium flex items-center gap-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6l-1.41 1.41L8.18 7H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-1.18l.59-.59L14 6"/></svg>
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-inbox text-4xl"></i>
                                <p class="text-sm">No bookings found for the selected filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($bookings->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600">
                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results
            </p>
            <div class="sm:ml-auto">
                {{ $bookings->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Payment Recording Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Record Payment</h2>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="px-6 py-4 space-y-4">
            <div>
                <p class="text-sm text-gray-600">Customer</p>
                <p id="paymentCustomerName" class="text-lg font-semibold text-gray-900">-</p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Amount Due</p>
                <p id="paymentAmount" class="text-2xl font-bold text-gray-900">₱0.00</p>
            </div>
            
            <div>
                <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="paymongo">PayMongo</option>
                    <option value="check">Check</option>
                </select>
            </div>
            
            <div>
                <label for="paymentReference" class="block text-sm font-medium text-gray-700 mb-2">Reference/Note</label>
                <input type="text" id="paymentReference" placeholder="e.g., Transaction ID, Check #, etc." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        
        <div class="flex gap-3 px-6 py-4 border-t border-gray-200">
            <button type="button" onclick="closePaymentModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                Cancel
            </button>
            <button type="button" onclick="submitPayment()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                Record Payment
            </button>
        </div>
    </div>
    </div>
</div>

<script>
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Single source of truth for payment context
    let paymentContext = {
        bookingId: null,
        amount: null,
        customerName: null,
        isValid: function() {
            return this.bookingId && this.amount;
        }
    };
    
    const statusLabels = {
        'completed': 'Completed',
        'confirmed': 'Confirmed',
        'cancelled': 'Cancelled',
        'pending': 'Pending'
    };
    
    // Dropdown menu functions
    function toggleActionMenu(button) {
        event.stopPropagation();
        const menu = button.nextElementSibling;
        document.querySelectorAll('.action-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        menu.classList.toggle('hidden');
    }
    
    function closeAllMenus() {
        document.querySelectorAll('.action-menu').forEach(m => {
            m.classList.add('hidden');
        });
    }
    
    // Close menus on outside click
    document.addEventListener('click', function(event) {
        const isActionButton = event.target.closest('button[onclick*="toggleActionMenu"]');
        if (!isActionButton) {
            closeAllMenus();
        }
    });
    
    // Payment Modal Functions
    function handlePaymentClick(button) {
        const bookingId = button.getAttribute('data-booking-id');
        const amount = button.getAttribute('data-booking-amount');
        const customerName = button.getAttribute('data-booking-name');
        
        console.log('Button clicked - Raw attributes:', { bookingId, amount, customerName });
        console.log('Button HTML:', button.outerHTML.substring(0, 200));
        
        openPaymentModal(bookingId, customerName, amount);
    }
    
    function openPaymentModal(bookingId, customerName, amount) {
        console.log('Opening payment modal with raw values:', { bookingId, customerName, amount });
        console.log('Raw types:', { bookingIdType: typeof bookingId, amountType: typeof amount });
        
        // Parse values properly
        const parsedId = parseInt(bookingId, 10);
        const parsedAmount = parseFloat(amount);
        
        console.log('Parsed values:', { parsedId, parsedAmount });
        console.log('Valid numbers?', { idValid: !isNaN(parsedId), amountValid: !isNaN(parsedAmount) });
        
        // Validate before setting
        if (isNaN(parsedId) || isNaN(parsedAmount) || parsedId <= 0 || parsedAmount <= 0) {
            console.error('Invalid parsed values:', { parsedId, parsedAmount });
            Swal.fire({
                title: 'Error',
                text: `Invalid booking data. ID: ${bookingId}, Amount: ${amount}`,
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Set the payment context
        paymentContext.bookingId = parsedId;
        paymentContext.amount = parsedAmount;
        paymentContext.customerName = customerName;
        
        console.log('✓ Payment context successfully set:', paymentContext);
        console.log('✓ Is valid?', paymentContext.isValid());
        
        // Update UI
        document.getElementById('paymentCustomerName').textContent = customerName || '-';
        document.getElementById('paymentAmount').textContent = '₱' + parsedAmount.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentModal').classList.remove('hidden');
        
        // Reset form fields
        document.getElementById('paymentMethod').value = '';
        document.getElementById('paymentReference').value = '';
        
        // Focus on first input
        document.getElementById('paymentMethod').focus();
    }
    
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        // Clear the payment context values (keep the isValid method)
        paymentContext.bookingId = null;
        paymentContext.amount = null;
        paymentContext.customerName = null;
    }
    
    async function submitPayment() {
        console.log('Submit payment called. Context:', paymentContext);
        console.log('Is valid?', paymentContext.isValid());
        
        // Validate payment context
        if (!paymentContext.isValid()) {
            console.error('Invalid payment context:', paymentContext);
            Swal.fire({
                title: 'Error',
                text: `Payment information is incomplete. BookingID: ${paymentContext.bookingId}, Amount: ${paymentContext.amount}`,
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        const paymentMethod = document.getElementById('paymentMethod').value;
        const paymentNotes = document.getElementById('paymentReference').value;
        
        // Validate form inputs
        if (!paymentMethod) {
            Swal.fire({
                title: 'Error',
                text: 'Please select a payment method',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        console.log('Submitting payment with data:', {
            booking_id: parseInt(paymentContext.bookingId),
            payment_method: paymentMethod,
            amount: parseFloat(paymentContext.amount),
            notes: paymentNotes
        });
        
        try {
            const response = await fetch('/admin/reservations/record-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    booking_id: parseInt(paymentContext.bookingId),
                    payment_method: paymentMethod,
                    amount: parseFloat(paymentContext.amount),
                    notes: paymentNotes
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Success',
                    text: 'Payment recorded successfully!',
                    icon: 'success',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    closePaymentModal();
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Failed to record payment',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            }
        } catch (error) {
            console.error('Payment submission error:', error);
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while recording payment',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
        }
    }
    
    // Status Management Functions
    async function updateReservationStatus(bookingId, newStatus) {
        closeAllMenus();
        
        try {
            const response = await fetch(`/admin/reservations/${bookingId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ status: newStatus })
            });
            
            const data = await response.json();
            
            if (data.success) {
                toastr.success(`Booking marked as ${statusLabels[newStatus]}!`);
                location.reload();
            } else {
                toastr.error(data.message || 'Failed to update status');
            }
        } catch (error) {
            console.error('Status update error:', error);
            toastr.error('An error occurred while updating status');
        }
    }
    
    async function cancelReservation(bookingId, customerName) {
        if (!confirm(`Are you sure you want to cancel the booking for ${customerName}? Inventory will be restored.`)) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/reservations/${bookingId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                toastr.success('Booking cancelled and inventory restored!');
                location.reload();
            } else {
                toastr.error(data.message || 'Failed to cancel booking');
            }
        } catch (error) {
            console.error('Cancellation error:', error);
            toastr.error('An error occurred while cancelling the booking');
        }
    }
</script>
@endsection

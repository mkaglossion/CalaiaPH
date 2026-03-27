@extends('layouts.admin')

@section('title', 'Reservation Monitor')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Header -->
    <div class="px-4 md:px-0">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">Dining Reservations</h1>
        <p class="text-sm md:text-base text-gray-500 mt-1">Manage all dining reservations in real-time</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 px-4 md:px-0">
        <!-- Bookings Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs md:text-sm font-medium text-gray-600">Total Bookings</h3>
                <i data-lucide="calendar" class="w-4 h-4 md:w-5 md:h-5 text-blue-500"></i>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900" id="total-bookings">{{ $stats['total_bookings'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending: <span id="pending-bookings">{{ $stats['pending_bookings'] }}</span></p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs md:text-sm font-medium text-gray-600">Completed Bookings</h3>
                <i data-lucide="check-circle" class="w-4 h-4 md:w-5 md:h-5 text-green-500"></i>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900" id="completed-bookings">{{ $stats['completed_bookings'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Cancelled: <span id="cancelled-bookings">{{ $stats['cancelled_bookings'] }}</span></p>
        </div>

        <!-- Dining Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs md:text-sm font-medium text-gray-600">Dining Reservations</h3>
                <i data-lucide="utensils" class="w-4 h-4 md:w-5 md:h-5 text-orange-500"></i>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900" id="total-dining">{{ $stats['total_dining'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Confirmed: <span id="confirmed-dining">{{ $stats['confirmed_dining'] }}</span></p>
        </div>

        <!-- Revenue Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs md:text-sm font-medium text-gray-600">Revenue (Today)</h3>
                <i data-lucide="trending-up" class="w-4 h-4 md:w-5 md:h-5 text-yellow-500"></i>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900" id="revenue-today">₱{{ number_format($revenueStats['today'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1 truncate">Month: ₱<span id="revenue-month">{{ number_format($revenueStats['this_month'], 2) }}</span></p>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4 mx-4 md:mx-0">
        <form method="GET" class="flex flex-col gap-3 md:flex-row md:gap-4">
            <div class="flex-1">
                <label for="status" class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $statusFilter === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="flex items-end gap-2 flex-col w-full sm:flex-row sm:w-auto">
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-3 md:px-4 py-2 bg-blue-600 text-white text-xs md:text-sm rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('admin.reservations.monitor') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-3 md:px-4 py-2 bg-gray-200 text-gray-700 text-xs md:text-sm rounded-lg hover:bg-gray-300 transition whitespace-nowrap">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Reservations Display -->
    <div class="mx-4 md:mx-0">
        <!-- Desktop View (Hidden on Mobile) -->
        <div class="hidden md:block bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <!-- Bulk Actions Toolbar (hidden by default) -->
            <div id="bulkActionsBar" class="hidden bg-blue-50 border-b border-blue-200 px-4 md:px-6 py-3 md:py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-square" class="w-5 h-5 text-blue-600"></i>
                        <span class="text-xs md:text-sm font-medium text-blue-700">
                            <span id="selectedCount">0</span> selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="bulkMarkCompleted()" class="inline-flex items-center px-2 md:px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition">
                            <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                            <span class="hidden sm:inline">Complete</span>
                        </button>
                        <button type="button" onclick="bulkMarkConfirmed()" class="inline-flex items-center px-2 md:px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                            <span class="hidden sm:inline">Confirm</span>
                        </button>
                        <button type="button" onclick="bulkCancel()" class="inline-flex items-center px-2 md:px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">
                            <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                            <span class="hidden sm:inline">Cancel</span>
                        </button>
                        <button type="button" onclick="bulkExport()" class="inline-flex items-center px-2 md:px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-lg hover:bg-gray-700 transition">
                            <i data-lucide="download" class="w-3 h-3 mr-1"></i>
                            <span class="hidden sm:inline">Export</span>
                        </button>
                        <button type="button" onclick="clearSelection()" class="inline-flex items-center px-2 md:px-3 py-1.5 bg-gray-200 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-300 transition">
                            <span class="hidden sm:inline">Clear</span>
                            <i data-lucide="x" class="w-3 h-3 sm:hidden"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-3 md:px-4 py-3 text-left">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Name</th>
                            <th class="hidden md:table-cell px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Contact</th>
                            <th class="px-2 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Reference</th>
                            <th class="hidden sm:table-cell px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Date</th>
                            <th class="px-2 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Status</th>
                            <th class="hidden md:table-cell px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Payment</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($bookings as $reservation)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-2 md:px-4 py-2 md:py-4">
                                <input type="checkbox" class="reservation-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $reservation['id'] }}" onchange="updateBulkSelection()">
                            </td>
                            <td class="px-2 md:px-6 py-2 md:py-4">
                                <div class="text-xs md:text-sm font-medium text-gray-900">{{ $reservation['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $reservation['booking_type_display'] }}</div>
                            </td>
                            <td class="hidden md:table-cell px-3 md:px-6 py-2 md:py-4 text-xs md:text-sm text-gray-600">
                                <div class="text-xs md:text-sm truncate">{{ substr($reservation['email'], 0, 20) }}{{ strlen($reservation['email']) > 20 ? '...' : '' }}</div>
                                <div class="text-xs text-gray-500">{{ $reservation['phone'] }}</div>
                            </td>
                            <td class="px-2 md:px-6 py-2 md:py-4 text-xs md:text-sm font-mono text-gray-900">{{ $reservation['reference'] }}</td>
                            <td class="hidden sm:table-cell px-3 md:px-6 py-2 md:py-4 text-xs md:text-sm text-gray-900">
                                @if($reservation['type'] === 'Booking')
                                    <div>{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}</div>
                                    @if($reservation['checkout_date'])
                                        <div class="text-xs text-gray-500">to {{ \Carbon\Carbon::parse($reservation['checkout_date'])->format('M d, Y') }}</div>
                                    @endif
                                @else
                                    <div>{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="px-2 md:px-6 py-2 md:py-4">
                                @php
                                    $statusColors = [
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $color = $statusColors[$reservation['status']] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                    {{ ucfirst($reservation['status']) }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell px-3 md:px-6 py-2 md:py-4 text-xs md:text-sm">
                                @if($reservation['type'] === 'Booking')
                                    @php
                                        $paymentStatus = $reservation['payment_status'] ?? 'pending';
                                        $paymentColors = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                        ];
                                        $pColor = $paymentColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <div class="flex flex-col gap-2">
                                        <div class="font-semibold text-gray-900 text-xs md:text-sm">₱{{ number_format($reservation['amount'], 2) }}</div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $pColor }} w-fit">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                            @if($paymentStatus !== 'paid')
                                                <button type="button" 
                                                    onclick="openPaymentModal('{{ $reservation['id'] }}', '{{ addslashes($reservation['name']) }}', {{ $reservation['amount'] }})"
                                                    class="inline-flex items-center px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition text-xs font-medium">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h10M5 21h14M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                                    Pay
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-2 md:px-6 py-2 md:py-4 text-xs md:text-sm">
                                @if($reservation['type'] === 'Booking')
                                    <div class="flex items-center gap-0.5 flex-wrap">
                                        <a href="{{ $reservation['route'] }}" class="inline-flex items-center px-1.5 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded text-xs font-medium" title="View booking details">
                                            <i data-lucide="eye" class="w-3 h-3"></i>
                                        </a>
                                        
                                        <button type="button" 
                                            onclick="showBookingTimeline('{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                            class="inline-flex items-center px-1.5 py-1 text-indigo-600 hover:bg-indigo-50 rounded text-xs font-medium">
                                            <i data-lucide="history" class="w-3 h-3"></i>
                                        </button>
                                        
                                        <div class="relative z-30">
                                            <button type="button" 
                                                onclick="toggleActionMenu(this)" 
                                                class="inline-flex items-center justify-center px-1.5 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded" 
                                                title="More actions">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <div class="action-menu hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-2xl border border-gray-200 py-1 z-30">
                                                @if($reservation['status'] === 'confirmed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('checkout-complete', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}', {{ $reservation['amount'] }})"
                                                        class="w-full text-left px-4 py-2 text-emerald-600 hover:bg-emerald-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="credit-card" class="w-3 h-3"></i>
                                                        Checkout & Complete
                                                    </button>
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                @endif
                                                @if($reservation['status'] !== 'completed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('mark-completed', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-green-600 hover:bg-green-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Mark as Completed
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'confirmed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('confirm', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                        Confirm
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'cancelled')
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('cancel', '{{ $reservation['id'] }}', '{{ addslashes($reservation['name']) }}')"
                                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                        Cancel
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" onclick="showDiningDetails('{{ $reservation['name'] }}', '{{ $reservation['reference'] }}', '{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}', '{{ isset($reservation['time']) ? date('H:i', strtotime($reservation['time'])) : 'N/A' }}', {{ $reservation['guests'] ?? 1 }}, '{{ $reservation['email'] }}', '{{ $reservation['phone'] }}')" class="inline-flex items-center px-1.5 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded text-xs font-medium" title="View details">
                                            <i data-lucide="info" class="w-3 h-3"></i>
                                        </button>
                                        
                                        <div class="relative z-30">
                                            <button type="button" 
                                                onclick="toggleActionMenu(this)" 
                                                class="inline-flex items-center justify-center px-1.5 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded" 
                                                title="More actions">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <div class="action-menu hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-2xl border border-gray-200 py-1 z-30">
                                                @if($reservation['status'] !== 'completed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('mark-completed', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-green-600 hover:bg-green-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Mark as Completed
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'confirmed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('confirm', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                        Confirm
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'cancelled')
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('cancel', '{{ $reservation['id'] }}', '{{ addslashes($reservation['name']) }}')"
                                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                        Cancel
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 md:py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-2 text-gray-300"></i>
                                    <p class="text-sm">No reservations found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($bookings->hasPages())
        <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-gray-50">
            <p class="text-xs md:text-sm text-gray-600">
                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results
            </p>
            <div class="sm:ml-auto overflow-x-auto">
                {{ $bookings->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        </div>
        @endif
        </div>

        <!-- Mobile View (Hidden on Desktop) -->
        <div class="md:hidden bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <!-- Bulk Actions Toolbar for Mobile -->
            <div id="bulkActionsBarMobile" class="hidden bg-blue-50 border-b border-blue-200 px-4 py-3">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-square" class="w-5 h-5 text-blue-600"></i>
                        <span class="text-xs font-medium text-blue-700">
                            <span id="selectedCountMobile">0</span> selected
                        </span>
                    </div>
                    <button type="button" onclick="clearSelectionMobile()" class="text-blue-600 hover:text-blue-800">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="bulkMarkCompletedMobile()" class="inline-flex items-center justify-center px-2 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition">
                        <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                        Complete
                    </button>
                    <button type="button" onclick="bulkMarkConfirmedMobile()" class="inline-flex items-center justify-center px-2 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition">
                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                        Confirm
                    </button>
                    <button type="button" onclick="bulkCancelMobile()" class="inline-flex items-center justify-center px-2 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition">
                        <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                        Cancel
                    </button>
                    <button type="button" onclick="bulkExportMobile()" class="inline-flex items-center justify-center px-2 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition">
                        <i data-lucide="download" class="w-3 h-3 mr-1"></i>
                        Export
                    </button>
                </div>
            </div>

            <!-- Mobile Cards -->
            <div class="divide-y divide-gray-100">
                @forelse($bookings as $reservation)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" class="reservation-checkbox-mobile rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1" value="{{ $reservation['id'] }}" onchange="updateBulkSelectionMobile()">
                            <div class="flex-1 min-w-0">
                                <!-- Header: Name and Type -->
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $reservation['name'] }}</h3>
                                        <p class="text-xs text-gray-500">{{ $reservation['booking_type_display'] }}</p>
                                    </div>
                                    @php
                                        $statusColors = [
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                        ];
                                        $color = $statusColors[$reservation['status']] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $color }} shrink-0">
                                        {{ ucfirst($reservation['status']) }}
                                    </span>
                                </div>

                                <!-- Contact and Reference -->
                                <div class="grid grid-cols-2 gap-2 mb-2 text-xs">
                                    <div>
                                        <p class="text-gray-500">Email</p>
                                        <p class="text-gray-900 truncate">{{ substr($reservation['email'], 0, 18) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Ref</p>
                                        <p class="text-gray-900 font-mono">{{ $reservation['reference'] }}</p>
                                    </div>
                                </div>

                                <!-- Date and Details -->
                                <div class="mb-2 text-xs space-y-1">
                                    <p class="text-gray-600">
                                        @if($reservation['type'] === 'Booking')
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}</span>
                                            @if($reservation['checkout_date'])
                                                <span class="text-gray-500">- {{ \Carbon\Carbon::parse($reservation['checkout_date'])->format('M d') }}</span>
                                            @endif
                                        @else
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}</span>
                                            @if(isset($reservation['time']))
                                                <span class="text-gray-500">at {{ date('H:i', strtotime($reservation['time'])) }}</span>
                                            @endif
                                        @endif
                                    </p>
                                    @if($reservation['type'] === 'Dining' && isset($reservation['guests']))
                                        <p class="text-gray-600"><span class="font-medium">{{ $reservation['guests'] }}</span> guest{{ $reservation['guests'] != 1 ? 's' : '' }}</p>
                                    @endif
                                </div>

                                <!-- Payment Status for Bookings -->
                                @if($reservation['type'] === 'Booking')
                                    <div class="mb-3 text-xs">
                                        <p class="text-gray-500 mb-1">Payment</p>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($reservation['amount'], 2) }}</span>
                                            @php
                                                $paymentStatus = $reservation['payment_status'] ?? 'pending';
                                                $paymentColors = [
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'failed' => 'bg-red-100 text-red-800',
                                                ];
                                                $pColor = $paymentColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold {{ $pColor }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                            @if($paymentStatus !== 'paid')
                                                <button type="button" 
                                                    onclick="openPaymentModal('{{ $reservation['id'] }}', '{{ addslashes($reservation['name']) }}', {{ $reservation['amount'] }})"
                                                    class="inline-flex items-center px-2 py-1 text-blue-600 hover:bg-blue-50 rounded text-xs font-medium transition">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h10M5 21h14M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                                    Pay
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1 flex-wrap pt-2 border-t border-gray-100">
                                    @if($reservation['type'] === 'Booking')
                                        <a href="{{ $reservation['route'] }}" class="inline-flex items-center justify-center px-2 py-1.5 text-blue-600 hover:bg-blue-50 rounded text-xs font-medium transition" title="View booking">
                                            <i data-lucide="eye" class="w-3 h-3"></i>
                                        </a>
                                        <button type="button" 
                                            onclick="showBookingTimeline('{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                            class="inline-flex items-center justify-center px-2 py-1.5 text-indigo-600 hover:bg-indigo-50 rounded text-xs font-medium transition">
                                            <i data-lucide="history" class="w-3 h-3"></i>
                                        </button>
                                    @else
                                        <button type="button" onclick="showDiningDetails('{{ $reservation['name'] }}', '{{ $reservation['reference'] }}', '{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}', '{{ isset($reservation['time']) ? date('H:i', strtotime($reservation['time'])) : 'N/A' }}', {{ $reservation['guests'] ?? 1 }}, '{{ $reservation['email'] }}', '{{ $reservation['phone'] }}')" class="inline-flex items-center justify-center px-2 py-1.5 text-blue-600 hover:bg-blue-50 rounded text-xs font-medium transition" title="View details">
                                            <i data-lucide="info" class="w-3 h-3"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- More Actions Dropdown -->
                                    <div class="relative z-50">
                                        <button type="button" 
                                            onclick="toggleActionMenuMobile(this)" 
                                            class="inline-flex items-center justify-center px-2 py-1.5 text-gray-600 hover:bg-gray-100 rounded transition" 
                                            title="More actions">
                                            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                        </button>
                                        
                                        <div class="action-menu-mobile hidden absolute -right-12 sm:right-0 mt-2 w-44 sm:w-48 bg-white rounded-lg shadow-2xl border border-gray-200 py-1 z-50">
                                            @if($reservation['status'] !== 'completed')
                                                <button type="button" 
                                                    onclick="showConfirmDialog('mark-completed', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                                    class="w-full text-left px-3 py-2 text-green-600 hover:bg-green-50 text-xs font-medium flex items-center gap-2">
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                    Complete
                                                </button>
                                            @endif
                                            
                                            @if($reservation['status'] !== 'confirmed')
                                                <button type="button" 
                                                    onclick="showConfirmDialog('confirm', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}')"
                                                    class="w-full text-left px-3 py-2 text-blue-600 hover:bg-blue-50 text-xs font-medium flex items-center gap-2">
                                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                    Confirm
                                                </button>
                                            @endif
                                            
                                            @if($reservation['status'] !== 'cancelled')
                                                <div class="border-t border-gray-100 my-1"></div>
                                                <button type="button" 
                                                    onclick="showConfirmDialog('cancel', '{{ $reservation['id'] }}', '{{ addslashes($reservation['name']) }}')"
                                                    class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 text-xs font-medium flex items-center gap-2">
                                                    <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                    Cancel
                                                </button>
                                            @endif
                                            
                                            @if($reservation['type'] === 'Booking' && $reservation['status'] === 'confirmed')
                                                <div class="border-t border-gray-100 my-1"></div>
                                                <button type="button" 
                                                    onclick="showConfirmDialog('checkout-complete', '{{ $reservation['id'] }}', '{{ $reservation['name'] }}', {{ $reservation['amount'] }})"
                                                    class="w-full text-left px-3 py-2 text-emerald-600 hover:bg-emerald-50 text-xs font-medium flex items-center gap-2">
                                                    <i data-lucide="credit-card" class="w-3 h-3"></i>
                                                    Checkout & Complete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="inbox" class="w-12 h-12 mb-2 text-gray-300"></i>
                            <p class="text-sm">No reservations found</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination for Mobile -->
            @if($bookings->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 flex flex-col gap-3 items-center bg-gray-50">
                <p class="text-xs text-gray-600">
                    Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }}
                </p>
                <div class="overflow-x-auto w-full">
                    {{ $bookings->onEachSide(1)->links('pagination::tailwind') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Real-time Updates Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"></i>
            <div>
                <h3 class="font-semibold text-blue-900">Real-time Monitoring</h3>
                <p class="text-sm text-blue-700 mt-1">This page automatically displays the most recent reservations and bookings. Refresh the page to see the latest updates, or check individual booking details for more information.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // ========== HELPER: Parse prefixed ID ==========
    function parseReservationId(prefixedId) {
        const [type, id] = prefixedId.split('_');
        return { type, id };
    }

    // ========== STATS REFRESH ==========
    async function refreshStats() {
        try {
            const response = await fetch('{{ route("admin.reservations.stats") }}');
            const data = await response.json();
            
            if (data.stats) {
                // Update booking stats
                document.getElementById('total-bookings').textContent = data.stats.total_bookings;
                document.getElementById('pending-bookings').textContent = data.stats.pending_bookings;
                document.getElementById('completed-bookings').textContent = data.stats.completed_bookings;
                document.getElementById('cancelled-bookings').textContent = data.stats.cancelled_bookings;
                
                // Update dining stats
                document.getElementById('total-dining').textContent = data.stats.total_dining;
                document.getElementById('confirmed-dining').textContent = data.stats.confirmed_dining;
                
                // Update revenue stats
                const today = parseFloat(data.revenueStats.today).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const month = parseFloat(data.revenueStats.this_month).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                document.getElementById('revenue-today').textContent = '₱' + today;
                document.getElementById('revenue-month').textContent = month;
            }
        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    // ========== BULK SELECTION MANAGEMENT ==========
    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.reservation-checkbox:checked');
        const bulkBar = document.getElementById('bulkActionsBar');
        const selectedCount = document.getElementById('selectedCount');
        
        selectedCount.textContent = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    function toggleSelectAll(checkbox) {
        const reservationCheckboxes = document.querySelectorAll('.reservation-checkbox');
        reservationCheckboxes.forEach(cb => cb.checked = checkbox.checked);
        updateBulkSelection();
    }

    function clearSelection() {
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.reservation-checkbox').forEach(cb => cb.checked = false);
        updateBulkSelection();
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.reservation-checkbox:checked'))
            .map(cb => cb.value);
    }

    // ========== BULK ACTIONS ==========
    async function bulkMarkCompleted() {
        const ids = getSelectedIds();
        if (!ids.length) return;

        // Extract actual IDs and check for mixed types
        const bookingIds = [];
        const diningIds = [];
        
        ids.forEach(id => {
            const { type, id: actualId } = parseReservationId(id);
            if (type === 'booking') {
                bookingIds.push(actualId);
            } else {
                diningIds.push(actualId);
            }
        });

        const result = await Swal.fire({
            icon: 'question',
            title: 'Mark as Completed?',
            text: `Mark ${ids.length} reservation(s) as completed?`,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Complete',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/reservations/bulk-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    booking_ids: bookingIds,
                    dining_ids: diningIds,
                    status: 'completed'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: `${ids.length} reservation(s) marked as completed`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => {
                        refreshStats();
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to update reservations', 'error');
        }
    }

    async function bulkMarkConfirmed() {
        const ids = getSelectedIds();
        if (!ids.length) return;

        // Extract actual IDs and check for mixed types
        const bookingIds = [];
        const diningIds = [];
        
        ids.forEach(id => {
            const { type, id: actualId } = parseReservationId(id);
            if (type === 'booking') {
                bookingIds.push(actualId);
            } else {
                diningIds.push(actualId);
            }
        });

        const result = await Swal.fire({
            icon: 'question',
            title: 'Confirm Reservations?',
            text: `Confirm ${ids.length} reservation(s)?`,
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Confirm',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/reservations/bulk-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    booking_ids: bookingIds,
                    dining_ids: diningIds,
                    status: 'confirmed'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: `${ids.length} reservation(s) confirmed`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => {
                        refreshStats();
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to confirm reservations', 'error');
        }
    }

    async function bulkCancel() {
        const ids = getSelectedIds();
        if (!ids.length) return;

        // Extract actual IDs and check for mixed types
        const bookingIds = [];
        const diningIds = [];
        
        ids.forEach(id => {
            const { type, id: actualId } = parseReservationId(id);
            if (type === 'booking') {
                bookingIds.push(actualId);
            } else {
                diningIds.push(actualId);
            }
        });

        const result = await Swal.fire({
            icon: 'warning',
            title: 'Cancel Reservations?',
            text: `Cancel ${ids.length} reservation(s)? This cannot be undone.`,
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Cancel',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/reservations/bulk-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    booking_ids: bookingIds,
                    dining_ids: diningIds,
                    status: 'cancelled'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cancelled',
                    text: `${ids.length} reservation(s) cancelled`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => {
                        refreshStats();
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to cancel reservations', 'error');
        }
    }

    async function bulkExport() {
        const ids = getSelectedIds();
        if (!ids.length) {
            Swal.fire('No Selection', 'Please select reservations to export', 'info');
            return;
        }

        try {
            const response = await fetch('/admin/reservations/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: ids })
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reservations-export-${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            } else {
                Swal.fire('Error', 'Failed to export reservations', 'error');
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }

    // ========== CONFIRMATION DIALOGS ==========
    function showConfirmDialog(action, bookingId, customerName, amount = null) {
        closeAllMenus();
        
        const dialogConfigs = {
            'mark-completed': {
                title: 'Mark as Completed?',
                message: `Mark ${customerName}'s booking as completed?`,
                icon: 'question',
                confirmColor: '#10b981',
                confirmText: 'Yes, Complete'
            },
            'confirm': {
                title: 'Confirm Booking?',
                message: `Confirm ${customerName}'s booking?`,
                icon: 'question',
                confirmColor: '#3b82f6',
                confirmText: 'Yes, Confirm'
            },
            'cancel': {
                title: 'Cancel Booking?',
                message: `Are you sure you want to cancel ${customerName}'s booking?`,
                icon: 'warning',
                confirmColor: '#dc2626',
                confirmText: 'Yes, Cancel'
            },
            'checkout-complete': {
                title: 'Checkout & Complete?',
                message: `Complete checkout for ${customerName} with amount ₱${amount ? parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00'}?`,
                icon: 'question',
                confirmColor: '#059669',
                confirmText: 'Yes, Complete'
            }
        };

        const config = dialogConfigs[action];
        if (!config) return;

        Swal.fire({
            title: config.title,
            text: config.message,
            icon: config.icon,
            showCancelButton: true,
            confirmButtonColor: config.confirmColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: config.confirmText,
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                if (action === 'checkout-complete') {
                    checkoutAndComplete(bookingId, customerName, amount);
                } else {
                    updateReservationStatus(bookingId, action === 'mark-completed' ? 'completed' : action === 'confirm' ? 'confirmed' : 'cancelled');
                }
            }
        });
    }

    // ========== BOOKING TIMELINE ==========
    async function showBookingTimeline(bookingId, customerName) {
        const { type, id } = parseReservationId(bookingId);
        
        // Only bookings have timeline
        if (type === 'dining') {
            Swal.fire('Info', 'Dining reservations do not have timeline history', 'info');
            return;
        }
        
        try {
            const response = await fetch(`/admin/reservations/${id}/timeline`);
            const data = await response.json();

            if (!data.success) {
                Swal.fire('Error', 'Failed to load timeline', 'error');
                return;
            }

            const timeline = data.timeline || [];
            let timelineHtml = '<div class="text-left space-y-3 max-h-96 overflow-y-auto">';

            if (timeline.length === 0) {
                timelineHtml += '<p class="text-gray-500 text-sm">No timeline events yet</p>';
            } else {
                timeline.forEach(event => {
                    const date = new Date(event.created_at).toLocaleString();
                    timelineHtml += `
                        <div class="flex gap-3 pb-3 border-b border-gray-200">
                            <div class="pt-1">
                                <i data-lucide="${event.event_icon || 'info'}" class="w-4 h-4 text-${event.event_color || 'blue'}-500"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-sm text-gray-900">${event.event_label}</div>
                                <div class="text-xs text-gray-600 mt-1">${event.description || ''}</div>
                                <div class="text-xs text-gray-400 mt-1">${date}</div>
                                ${event.triggered_by ? `<div class="text-xs text-gray-500 mt-1">By: ${event.triggered_by}</div>` : ''}
                            </div>
                        </div>
                    `;
                });
            }

            timelineHtml += '</div>';

            Swal.fire({
                title: `${customerName} - Booking Timeline`,
                html: timelineHtml,
                icon: 'info',
                confirmButtonText: 'Close',
                width: '600px'
            });
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }

    // ========== MENU MANAGEMENT ==========
    function closeAllMenus() {
        document.querySelectorAll('.action-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    function toggleActionMenu(button) {
        event.stopPropagation();
        const menu = button.nextElementSibling;
        
        document.querySelectorAll('.action-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        
        menu.classList.toggle('hidden');
    }

    // ========== MOBILE BULK SELECTION ==========
    function updateBulkSelectionMobile() {
        const checkboxes = document.querySelectorAll('.reservation-checkbox-mobile:checked');
        const bulkBar = document.getElementById('bulkActionsBarMobile');
        const selectedCount = document.getElementById('selectedCountMobile');
        
        selectedCount.textContent = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    function getSelectedIdsMobile() {
        return Array.from(document.querySelectorAll('.reservation-checkbox-mobile:checked'))
            .map(cb => cb.value);
    }

    function clearSelectionMobile() {
        document.querySelectorAll('.reservation-checkbox-mobile').forEach(cb => cb.checked = false);
        updateBulkSelectionMobile();
    }

    // Mobile bulk actions (reuse same backend functions)
    async function bulkMarkCompletedMobile() {
        const ids = getSelectedIdsMobile();
        if (!ids.length) return;
        
        const bookingIds = [];
        const diningIds = [];
        ids.forEach(id => {
            const { type, id: actualId } = parseReservationId(id);
            if (type === 'booking') bookingIds.push(actualId);
            else diningIds.push(actualId);
        });

        const result = await Swal.fire({
            icon: 'question',
            title: 'Mark as Completed?',
            text: `Mark ${ids.length} reservation(s) as completed?`,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Complete',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/reservations/bulk-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ booking_ids: bookingIds, dining_ids: diningIds, status: 'completed' })
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Success', text: `${ids.length} reservation(s) marked as completed`, timer: 1500, timerProgressBar: true, didClose: () => { refreshStats(); location.reload(); } });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to update reservations', 'error');
        }
    }

    async function bulkMarkConfirmedMobile() {
        const ids = getSelectedIdsMobile();
        if (!ids.length) return;
        
        const bookingIds = [];
        const diningIds = [];
        ids.forEach(id => {
            const { type, id: actualId } = parseReservationId(id);
            if (type === 'booking') bookingIds.push(actualId);
            else diningIds.push(actualId);
        });

        const result = await Swal.fire({
            icon: 'question', title: 'Confirm Reservations?', text: `Confirm ${ids.length} reservation(s)?`, showCancelButton: true,
            confirmButtonColor: '#3b82f6', cancelButtonColor: '#6b7280', confirmButtonText: 'Yes, Confirm',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/reservations/bulk-update', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ booking_ids: bookingIds, dining_ids: diningIds, status: 'confirmed' })
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Success', text: `${ids.length} reservation(s) confirmed`, timer: 1500, timerProgressBar: true, didClose: () => { refreshStats(); location.reload(); } });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to confirm reservations', 'error');
        }
    }

    async function bulkCancelMobile() {
        const ids = getSelectedIdsMobile();
        if (!ids.length) return;
        
        const bookingIds = [];
        const diningIds = [];
        ids.forEach(id => {
            const { type, id: actualId } = parseReservationId(id);
            if (type === 'booking') bookingIds.push(actualId);
            else diningIds.push(actualId);
        });

        const result = await Swal.fire({
            icon: 'warning', title: 'Cancel Reservations?', text: `Cancel ${ids.length} reservation(s)? This cannot be undone.`,
            showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Yes, Cancel',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/reservations/bulk-update', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ booking_ids: bookingIds, dining_ids: diningIds, status: 'cancelled' })
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Cancelled', text: `${ids.length} reservation(s) cancelled`, timer: 1500, timerProgressBar: true, didClose: () => { refreshStats(); location.reload(); } });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to cancel reservations', 'error');
        }
    }

    async function bulkExportMobile() {
        const ids = getSelectedIdsMobile();
        if (!ids.length) {
            Swal.fire('No Selection', 'Please select reservations to export', 'info');
            return;
        }

        try {
            const response = await fetch('/admin/reservations/export', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ ids: ids })
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reservations-export-${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            } else {
                Swal.fire('Error', 'Failed to export reservations', 'error');
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }

    function toggleActionMenuMobile(button) {
        event.stopPropagation();
        const menu = button.nextElementSibling;
        
        document.querySelectorAll('.action-menu-mobile').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        
        menu.classList.toggle('hidden');
    }

    // Close mobile menus on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.action-menu-mobile') && !e.target.closest('button[onclick*="toggleActionMenuMobile"]')) {
            document.querySelectorAll('.action-menu-mobile').forEach(menu => menu.classList.add('hidden'));
        }
    });

    // ========== DINING DETAILS ==========
    function showDiningDetails(name, reference, date, time, guests, email, phone) {
        Swal.fire({
            title: 'Dining Reservation Details',
            html: `
                <div class="text-left space-y-2 text-sm">
                    <div><strong>Name:</strong> ${name}</div>
                    <div><strong>Reference:</strong> <code class="bg-gray-100 px-2 py-1 rounded">${reference}</code></div>
                    <div><strong>Date:</strong> ${date}</div>
                    <div><strong>Time:</strong> ${time}</div>
                    <div><strong>Guests:</strong> ${guests}</div>
                    <div><strong>Email:</strong> ${email}</div>
                    <div><strong>Phone:</strong> ${phone}</div>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Close',
            confirmButtonColor: '#3b82f6'
        });
    }

    // ========== RESERVATION STATUS UPDATES ==========
    async function updateReservationStatus(bookingId, newStatus) {
        closeAllMenus();
        
        const { type, id } = parseReservationId(bookingId);
        
        const statusLabels = {
            'confirmed': 'Confirmed',
            'completed': 'Completed', 
            'cancelled': 'Cancelled'
        };

        try {
            // Determine endpoint based on type
            const endpoint = type === 'dining' 
                ? `/admin/reservations/dining/${id}/status`
                : `/admin/reservations/${id}/status`;
            
            const response = await fetch(endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: `${type === 'dining' ? 'Dining reservation' : 'Booking'} marked as ${statusLabels[newStatus]}!`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => {
                        refreshStats();
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to update status', 'error');
        }
    }

    // ========== CANCEL RESERVATION ==========
    async function cancelReservation(bookingId, customerName) {
        closeAllMenus();

        try {
            const response = await fetch(`/admin/reservations/${bookingId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cancelled!',
                    text: 'Booking has been cancelled successfully',
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => {
                        refreshStats();
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to cancel booking', 'error');
        }
    }

    // ========== PAYMENT MODAL ==========
    let isCheckoutComplete = false;
    
    // Payment context for monitor page
    let monitorPaymentContext = {
        bookingId: null,
        amount: null,
        customerName: null,
        isCheckoutComplete: false,
        isValid: function() {
            return this.bookingId && this.amount;
        }
    };

    function openPaymentModal(bookingId, customerName, amount, isCheckout = false) {
        console.log('Opening payment modal with:', { bookingId, customerName, amount, isCheckout });
        
        const { type, id } = parseReservationId(bookingId);
        
        // Only bookings support payment
        if (type === 'dining') {
            Swal.fire('Info', 'Dining reservations do not require payment processing', 'info');
            return;
        }
        
        // Ensure proper types
        const actualId = parseInt(id);
        const actualAmount = parseFloat(amount);
        
        if (!actualId || !actualAmount) {
            console.error('Invalid payment data:', { id, amount });
            Swal.fire('Error', 'Invalid booking data', 'error');
            return;
        }
        
        // Set payment context
        monitorPaymentContext.bookingId = actualId;
        monitorPaymentContext.amount = actualAmount;
        monitorPaymentContext.customerName = customerName;
        monitorPaymentContext.isCheckoutComplete = isCheckout;
        
        console.log('Payment context set:', monitorPaymentContext);
        
        // Update UI
        document.getElementById('customerName').textContent = customerName || '-';
        document.getElementById('amountDue').textContent = '₱' + actualAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Reset form
        document.getElementById('paymentMethod').value = '';
        document.getElementById('paymentNotes').value = '';
        
        // Show modal
        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('paymentMethod').focus();
    }
    
    function checkoutAndComplete(bookingId, customerName, amount) {
        console.log('checkoutAndComplete called:', { bookingId, customerName, amount });
        closeAllMenus();
        openPaymentModal(bookingId, customerName, amount, true);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentForm').reset();
        monitorPaymentContext = {
            bookingId: null,
            amount: null,
            customerName: null,
            isCheckoutComplete: false,
            isValid: function() {
                return this.bookingId && this.amount;
            }
        };
    }

    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            console.log('Payment form submitted. Context:', monitorPaymentContext);
            
            // Validate context
            if (!monitorPaymentContext.isValid()) {
                console.error('Invalid payment context:', monitorPaymentContext);
                Swal.fire({
                    title: 'Error',
                    text: `Invalid payment data. ID: ${monitorPaymentContext.bookingId}, Amount: ${monitorPaymentContext.amount}`,
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            const method = document.getElementById('paymentMethod').value;
            const notes = document.getElementById('paymentNotes').value;
            
            if (!method) {
                Swal.fire('Error', 'Please select a payment method', 'error');
                return;
            }

            try {
                const payload = {
                    booking_id: monitorPaymentContext.bookingId,
                    amount: monitorPaymentContext.amount,
                    payment_method: method,
                    notes: notes
                };
                
                console.log('Sending payment request:', payload);
                
                const response = await fetch('{{ route("admin.reservations.record-payment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                console.log('Payment response:', data);
                
                if (data.success) {
                    if (monitorPaymentContext.isCheckoutComplete) {
                        try {
                            const completeResponse = await fetch(`/admin/reservations/${monitorPaymentContext.bookingId}/status`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    status: 'completed'
                                })
                            });
                            const completeData = await completeResponse.json();
                            if (completeData.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Checkout Complete!',
                                    html: `<div class="text-left space-y-2">
                                        <p><strong>Payment:</strong> ₱${parseFloat(monitorPaymentContext.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
                                        <p><strong>Method:</strong> ${method}</p>
                                        <p class="pt-2 border-t"><strong>Status:</strong> <span class="text-emerald-600">Completed</span></p>
                                    </div>`,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didClose: () => {
                                        refreshStats();
                                        location.reload();
                                    }
                                });
                                closePaymentModal();
                            }
                        } catch (error) {
                            console.error('Error completing booking:', error);
                        }
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Recorded!',
                            text: `₱${parseFloat(monitorPaymentContext.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })} payment received`,
                            timer: 1500,
                            timerProgressBar: true,
                            didClose: () => {
                                refreshStats();
                                location.reload();
                            }
                        });
                        closePaymentModal();
                    }
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Failed to record payment', 'error');
            }
        });
    }

    const paymentModal = document.getElementById('paymentModal');
    if (paymentModal) {
        paymentModal.addEventListener('click', (e) => {
            if (e.target === paymentModal) {
                closePaymentModal();
            }
        });
    }
</script>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center w-full h-full px-2 md:px-0">
        <div class="bg-white rounded-lg shadow-lg max-w-sm md:max-w-md w-full mx-2 md:mx-4">
            <div class="p-4 md:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Record Payment</h3>
                    <button type="button" onclick="closePaymentModal()" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <form id="paymentForm" class="p-4 md:p-6 space-y-4">
                <input type="hidden" id="bookingId">

                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-1">Customer</p>
                    <p id="customerName" class="text-gray-900 font-medium"></p>
                </div>

                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-1">Amount Due</p>
                    <p id="amountDue" class="text-lg font-bold text-gray-900"></p>
                </div>

                <div>
                    <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₱</span>
                        <input type="number" id="paymentAmount" step="0.01" min="0.01" required
                            class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="paymentMethod" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select payment method</option>
                        <option value="online">Online Payment</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="paymentNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea id="paymentNotes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Add any payment notes..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closePaymentModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                        <i data-lucide="check" class="w-4 h-4 inline mr-2"></i>
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

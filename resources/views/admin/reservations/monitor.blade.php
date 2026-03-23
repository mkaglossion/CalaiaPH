@extends('layouts.admin')

@section('title', 'Reservation Monitor')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-semibold text-gray-900">Reservation Monitor</h1>
        <p class="text-gray-500 mt-1">Track all incoming bookings and dining reservations in real-time</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Bookings Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-600">Total Bookings</h3>
                <i data-lucide="calendar" class="w-5 h-5 text-blue-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_bookings'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending: {{ $stats['pending_bookings'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-600">Completed Bookings</h3>
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_bookings'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Cancelled: {{ $stats['cancelled_bookings'] }}</p>
        </div>

        <!-- Dining Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-600">Dining Reservations</h3>
                <i data-lucide="utensils" class="w-5 h-5 text-orange-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_dining'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Confirmed: {{ $stats['confirmed_dining'] }}</p>
        </div>

        <!-- Revenue Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-600">Revenue (Today)</h3>
                <i data-lucide="trending-up" class="w-5 h-5 text-yellow-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($revenueStats['today'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">This Month: ₱{{ number_format($revenueStats['this_month'], 2) }}</p>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="confirmed" {{ $statusFilter === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('admin.reservations.monitor') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition whitespace-nowrap">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Reservations Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <!-- Bulk Actions Toolbar (hidden by default) -->
        <div id="bulkActionsBar" class="hidden bg-blue-50 border-b border-blue-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-square" class="w-5 h-5 text-blue-600"></i>
                    <span class="text-sm font-medium text-blue-700">
                        <span id="selectedCount">0</span> selected
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="bulkMarkCompleted()" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition">
                        <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                        Complete Selected
                    </button>
                    <button type="button" onclick="bulkMarkConfirmed()" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                        Confirm Selected
                    </button>
                    <button type="button" onclick="bulkCancel()" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">
                        <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                        Cancel Selected
                    </button>
                    <button type="button" onclick="bulkExport()" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-lg hover:bg-gray-700 transition">
                        <i data-lucide="download" class="w-3 h-3 mr-1"></i>
                        Export
                    </button>
                    <button type="button" onclick="clearSelection()" class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-300 transition">
                        Cancel Selection
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Contact</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Reference</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Payment</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Time</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bookings as $reservation)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4">
                                <input type="checkbox" class="reservation-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $reservation['id'] }}" onchange="updateBulkSelection()">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $reservation['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $reservation['booking_type_display'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="text-sm">{{ substr($reservation['email'], 0, 20) }}{{ strlen($reservation['email']) > 20 ? '...' : '' }}</div>
                                <div class="text-xs text-gray-500">{{ $reservation['phone'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ $reservation['reference'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($reservation['type'] === 'Booking')
                                    <div>{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}</div>
                                    @if($reservation['checkout_date'])
                                        <div class="text-xs text-gray-500">to {{ \Carbon\Carbon::parse($reservation['checkout_date'])->format('M d, Y') }}</div>
                                    @endif
                                @else
                                    <div>{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
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
                            <td class="px-6 py-4 text-sm">
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
                                    <div class="flex flex-col gap-1">
                                        <div class="font-semibold text-gray-900">₱{{ number_format($reservation['amount'], 2) }}</div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $pColor }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                            @if($paymentStatus !== 'paid')
                                                <button type="button" 
                                                    onclick="openPaymentModal({{ $reservation['id'] }}, '{{ $reservation['name'] }}', {{ $reservation['amount'] }})"
                                                class="inline-flex items-center px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-lg transition text-xs font-medium">
                                                <i data-lucide="credit-card" class="w-3 h-3 mr-1"></i>
                                                Pay
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $reservation['created_at']->diffForHumans() }}
                                @if($reservation['type'] === 'Dining' && isset($reservation['time']))
                                    <div class="text-xs mt-1 text-gray-700">{{ date('H:i', strtotime($reservation['time'])) }}</div>
                                    <div class="text-xs text-gray-500">{{ ($reservation['guests'] ?? 1) }} guests</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($reservation['type'] === 'Booking')
                                    <div class="flex items-center gap-1">
                                        <a href="{{ $reservation['route'] }}" class="inline-flex items-center px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition text-xs font-medium whitespace-nowrap" title="View booking details">
                                            <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                                            View
                                        </a>
                                        
                                        <!-- Booking Timeline Button -->
                                        <button type="button" 
                                            onclick="showBookingTimeline({{ $reservation['id'] }}, '{{ $reservation['name'] }}')"
                                            class="inline-flex items-center px-2 py-1 text-indigo-600 hover:bg-indigo-50 rounded-lg transition text-xs font-medium">
                                            <i data-lucide="history" class="w-3 h-3 mr-1"></i>
                                            Timeline
                                        </button>
                                        
                                        <!-- Status Menu Button -->
                                        <div class="relative">
                                            <button type="button" 
                                                onclick="toggleActionMenu(this)" 
                                                class="inline-flex items-center justify-center px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition" 
                                                title="More actions">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div class="action-menu hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-40">
                                                @if($reservation['status'] !== 'completed' && $reservation['status'] !== 'cancelled')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('checkout-complete', {{ $reservation['id'] }}, '{{ $reservation['name'] }}', {{ $reservation['amount'] }})"
                                                        class="w-full text-left px-4 py-2 text-emerald-600 hover:bg-emerald-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="credit-card" class="w-3 h-3"></i>
                                                        Checkout & Complete
                                                    </button>
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                @endif
                                                @if($reservation['status'] !== 'completed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('mark-completed', {{ $reservation['id'] }}, '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-green-600 hover:bg-green-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Mark as Completed
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'confirmed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('confirm', {{ $reservation['id'] }}, '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                        Confirm
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'cancelled')
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('cancel', {{ $reservation['id'] }}, '{{ addslashes($reservation['name']) }}')"
                                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                        Cancel
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="showDiningDetails('{{ $reservation['name'] }}', '{{ $reservation['reference'] }}', '{{ \Carbon\Carbon::parse($reservation['date'])->format('M d, Y') }}', '{{ isset($reservation['time']) ? date('H:i', strtotime($reservation['time'])) : 'N/A' }}', {{ $reservation['guests'] ?? 1 }}, '{{ $reservation['email'] }}', '{{ $reservation['phone'] }}')" class="inline-flex items-center px-3 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition text-xs font-medium" title="View dining reservation details">
                                            <i data-lucide="info" class="w-3 h-3 mr-1"></i>
                                            Details
                                        </button>
                                        
                                        <!-- Status Menu Button for Dining -->
                                        <div class="relative">
                                            <button type="button" 
                                                onclick="toggleActionMenu(this)" 
                                                class="inline-flex items-center justify-center px-2 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition" 
                                                title="More actions">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <!-- Dropdown Menu for Dining -->
                                            <div class="action-menu hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-40">
                                                @if($reservation['status'] !== 'completed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('mark-completed', {{ $reservation['id'] }}, '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-green-600 hover:bg-green-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Mark as Completed
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'confirmed')
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('confirm', {{ $reservation['id'] }}, '{{ $reservation['name'] }}')"
                                                        class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50 text-xs font-medium flex items-center gap-2">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                        Confirm
                                                    </button>
                                                @endif
                                                
                                                @if($reservation['status'] !== 'cancelled')
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <button type="button" 
                                                        onclick="showConfirmDialog('cancel', {{ $reservation['id'] }}, '{{ addslashes($reservation['name']) }}')"
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
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
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
        <div class="px-6 py-4 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-gray-50">
            <p class="text-sm text-gray-600">
                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results
            </p>
            <div class="sm:ml-auto">
                {{ $bookings->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        </div>
        @endif
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
                    ids: ids,
                    status: 'completed'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: `${ids.length} booking(s) marked as completed`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => location.reload()
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to update bookings', 'error');
        }
    }

    async function bulkMarkConfirmed() {
        const ids = getSelectedIds();
        if (!ids.length) return;

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
                    ids: ids,
                    status: 'confirmed'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: `${ids.length} booking(s) confirmed`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => location.reload()
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to confirm bookings', 'error');
        }
    }

    async function bulkCancel() {
        const ids = getSelectedIds();
        if (!ids.length) return;

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
                    ids: ids,
                    status: 'cancelled'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cancelled',
                    text: `${ids.length} booking(s) cancelled`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => location.reload()
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to cancel bookings', 'error');
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
        try {
            const response = await fetch(`/admin/reservations/${bookingId}/timeline`);
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

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.action-menu') && !e.target.closest('button[onclick*="toggleActionMenu"]')) {
            closeAllMenus();
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
        
        const statusLabels = {
            'confirmed': 'Confirmed',
            'completed': 'Completed', 
            'cancelled': 'Cancelled'
        };

        try {
            const response = await fetch(`/admin/reservations/${bookingId}/status`, {
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
                    text: `Booking marked as ${statusLabels[newStatus]}!`,
                    timer: 1500,
                    timerProgressBar: true,
                    didClose: () => location.reload()
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
                    didClose: () => location.reload()
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
    
    function openPaymentModal(bookingId, customerName, amount, isCheckout = false) {
        isCheckoutComplete = isCheckout;
        document.getElementById('bookingId').value = bookingId;
        document.getElementById('customerName').textContent = customerName;
        document.getElementById('amountDue').textContent = '₱' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('paymentAmount').value = amount;
        document.getElementById('paymentModal').classList.remove('hidden');
    }
    
    function checkoutAndComplete(bookingId, customerName, amount) {
        closeAllMenus();
        openPaymentModal(bookingId, customerName, amount, true);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentForm').reset();
    }

    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const bookingId = document.getElementById('bookingId').value;
            const amount = document.getElementById('paymentAmount').value;
            const method = document.getElementById('paymentMethod').value;
            const notes = document.getElementById('paymentNotes').value;

            try {
                const response = await fetch('{{ route("admin.reservations.record-payment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        amount: amount,
                        payment_method: method,
                        notes: notes
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    if (isCheckoutComplete) {
                        try {
                            const completeResponse = await fetch(`/admin/reservations/${bookingId}/status`, {
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
                                        <p><strong>Payment:</strong> ₱${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
                                        <p><strong>Method:</strong> ${method}</p>
                                        <p class="pt-2 border-t"><strong>Status:</strong> <span class="text-emerald-600">Completed</span></p>
                                    </div>`,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didClose: () => location.reload()
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
                            text: `₱${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })} payment received`,
                            timer: 1500,
                            timerProgressBar: true,
                            didClose: () => location.reload()
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
    <div class="flex items-center justify-center w-full h-full">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Record Payment</h3>
                    <button type="button" onclick="closePaymentModal()" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <form id="paymentForm" class="p-6 space-y-4">
                <input type="hidden" id="bookingId">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <p id="customerName" class="text-gray-900 font-medium"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Due</label>
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

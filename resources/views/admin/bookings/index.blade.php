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
                ['label' => 'Total Bookings', 'value' => $stats['total'], 'accent' => 'border-slate-700 text-slate-900', 'chip' => 'bg-slate-700 text-white', 'chipStyle' => 'background-color:#0f172a;color:#fff;', 'icon' => 'grid'],
                ['label' => 'Confirmed', 'value' => $stats['confirmed'], 'accent' => 'border-emerald-600 text-emerald-900', 'chip' => 'bg-emerald-600 text-white', 'chipStyle' => 'background-color:#059669;color:#fff;', 'icon' => 'check'],
                ['label' => 'Completed', 'value' => $stats['completed'], 'accent' => 'border-blue-600 text-blue-900', 'chip' => 'bg-blue-600 text-white', 'chipStyle' => 'background-color:#1d4ed8;color:#fff;', 'icon' => 'flag'],
                ['label' => 'Cancelled', 'value' => $stats['cancelled'], 'accent' => 'border-rose-600 text-rose-900', 'chip' => 'bg-rose-600 text-white', 'chipStyle' => 'background-color:#e11d48;color:#fff;', 'icon' => 'ban'],
                ['label' => 'Today', 'value' => $stats['today'], 'accent' => 'border-amber-600 text-amber-900', 'chip' => 'bg-amber-500 text-white', 'chipStyle' => 'background-color:#f59e0b;color:#fff;', 'icon' => 'sun'],
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
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" style="color:#6b7280;">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 16.5L21 21"/>
                    </svg>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                        placeholder="Name, email, transaction ID"
                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                        style="padding-left:2.5rem;">
                </div>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label for="booking_type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select id="booking_type" name="booking_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="glamping" {{ request('booking_type') == 'glamping' ? 'selected' : '' }}>Glamping</option>
                    <option value="camping" {{ request('booking_type') == 'camping' ? 'selected' : '' }}>Camping</option>
                    <option value="dayvisit" {{ request('booking_type') == 'dayvisit' ? 'selected' : '' }}>Day Visit</option>
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div class="md:col-span-5 flex flex-wrap gap-2 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg shadow hover:bg-amber-700" style="background-color:#f59e0b;color:#fff;border:1px solid #d97706;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18L13 12.5V19l-2-1v-5.5z"/></svg>
                    <span>Apply Filters</span>
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5 19l14-14"/></svg>
                    <span>Clear</span>
                </a>
            </div>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-3">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.522 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z"/></svg>
                                <span>View</span>
                            </a>
                            @if($booking->status == 'confirmed')
                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline" 
                                onsubmit="return confirm('Cancel this booking? Inventory will be restored.');">
                                @csrf
                                @method('POST')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 inline-flex items-center gap-1" style="color:#e11d48;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" style="color:#e11d48;">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l6 6m0-6l-6 6" />
                                    </svg>
                                    <span>Cancel</span>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
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
@endsection

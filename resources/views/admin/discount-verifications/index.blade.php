@extends('layouts.admin')

@section('title', 'Discount Verifications')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Discount Verifications</h1>
        <div class="flex gap-2">
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                Pending Bookings: {{ $pendingBookings ?? 0 }}
            </span>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                Pending Shop Orders: {{ $pendingShopOrders ?? 0 }}
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.discount-verifications.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#8f5d54]">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="claimed" {{ $status === 'claimed' ? 'selected' : '' }}>Claimed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name, email, or ID number..." 
                       class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#8f5d54] w-64">
            </div>
            <button type="submit" class="bg-[#8f5d54] text-white px-4 py-2 rounded-lg hover:bg-[#7a4a42]">
                <i class="bi bi-search mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.discount-verifications.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                <i class="bi bi-x-circle mr-1"></i> Clear
            </a>
        </form>
    </div>

    <!-- Bookings Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Booking Discounts</h2>
            @if(($bookings ?? collect())->where('discount_verification_status', 'pending')->count() > 0)

                <button onclick="bulkApprove('bookings')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    <i class="bi bi-check-all mr-1"></i> Bulk Approve Pending
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings ?? collect() as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.discount-verifications.show', $booking->id) }}" class="text-[#8f5d54] hover:underline font-medium">
                                    {{ $booking->booking_reference ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $booking->customer_name }}</div>
                                <div class="text-gray-500 text-xs">{{ $booking->customer_email }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $booking->discount_type === 'senior' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $booking->discount_type === 'senior' ? 'Senior' : 'PWD' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $booking->discount_id_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm">₱{{ number_format($booking->discount_amount ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'verified' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'claimed' => 'bg-blue-100 text-blue-800',
                                    ];
                                    $color = $statusColors[$booking->discount_verification_status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucfirst($booking->discount_verification_status) }}

                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.discount-verifications.show', $booking->id) }}" class="text-[#8f5d54] hover:text-[#7a4a42] mr-2">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No booking discount verifications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ ($bookings ?? collect())->links() }}
        </div>
    </div>

    <!-- Shop Orders Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Shop Order Discounts</h2>
            @if(($shopOrders ?? collect())->where('discount_status', 'pending')->count() > 0)
                <button onclick="bulkApprove('shop_orders')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    <i class="bi bi-check-all mr-1"></i> Bulk Approve Pending
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($shopOrders ?? collect() as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.discount-verifications.show-shop-order', $order->id) }}" class="text-[#8f5d54] hover:underline font-medium">
                                    {{ $order->order_reference ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $order->customer_name }}</div>
                                <div class="text-gray-500 text-xs">{{ $order->customer_email }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $order->discount_type === 'senior' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $order->discount_type === 'senior' ? 'Senior' : 'PWD' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $order->discount_id_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm">₱{{ number_format($order->discount_amount ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'verified' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'claimed' => 'bg-blue-100 text-blue-800',
                                    ];
                                    $color = $statusColors[$order->discount_status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucfirst($order->discount_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.discount-verifications.show-shop-order', $order->id) }}" class="text-[#8f5d54] hover:text-[#7a4a42] mr-2">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No shop order discount verifications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ ($shopOrders ?? collect())->links() }}
        </div>
    </div>
</div>

<script>
function bulkApprove(type) {
    if (!confirm('Are you sure you want to approve all pending ' + type + '?')) {
        return;
    }
    
    // Collect all pending IDs
    const pendingIds = [];
    document.querySelectorAll('input[name="selected_ids[]"]:checked').forEach(cb => {
        pendingIds.push(cb.value);
    });
    
    if (pendingIds.length === 0) {
        alert('No items selected for approval.');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.discount-verifications.bulk-approve") }}';
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    
    pendingIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection

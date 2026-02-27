@extends('layouts.admin')

@section('title', 'Discount Verification Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Discount Verification Details</h1>
        <a href="{{ route('admin.discount-verifications.index') }}" class="text-[#8f5d54] hover:underline">
            <i class="bi bi-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- ID Image -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">ID Image</h2>
            @if($item->id_image_path)
                <div class="relative">
                    <img src="{{ $item->id_image_path }}" alt="ID Document" 
                         class="w-full rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition"
                         onclick="window.open('{{ $item->id_image_path }}', '_blank')">
                    <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                        Click to enlarge
                    </div>
                </div>
            @else
                <div class="bg-gray-100 rounded-lg p-8 text-center text-gray-500">
                    <i class="bi bi-image text-4xl mb-2"></i>
                    <p>No ID image uploaded</p>
                </div>
            @endif
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Name:</span>
                    <span class="font-medium">{{ $item->customer_name }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Email:</span>
                    <span class="font-medium">{{ $item->customer_email }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Phone:</span>
                    <span class="font-medium">{{ $item->customer_phone }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Reference:</span>
                    <span class="font-medium">{{ $item->booking_reference ?? $item->order_reference ?? 'N/A' }}</span>
                </div>
                @if($type === 'booking')
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Booking Type:</span>
                        <span class="font-medium">{{ ucfirst($item->booking_type ?? 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Check-in:</span>
                        <span class="font-medium">{{ $item->checkin_date ? date('M d, Y', strtotime($item->checkin_date)) : 'N/A' }}</span>
                    </div>
                @else
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Pickup Date:</span>
                        <span class="font-medium">{{ $item->pickup_date ? $item->pickup_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Delivery Option:</span>
                        <span class="font-medium">{{ ucfirst($item->delivery_option ?? 'N/A') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Discount Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Discount Details</h2>
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Discount Type:</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $item->discount_type === 'senior' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $item->discount_type === 'senior' ? 'Senior Citizen' : 'PWD' }}
                    </span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">ID Number:</span>
                    <span class="font-medium font-mono">{{ $item->discount_id_number ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Original Amount:</span>
                    <span class="font-medium">₱{{ number_format($item->original_total ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Discount Amount:</span>
                    <span class="font-medium text-green-600">-₱{{ number_format($item->discount_amount ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Final Amount:</span>
                    <span class="font-medium text-lg">₱{{ number_format($item->final_total ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Status:</span>
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'verified' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'claimed' => 'bg-blue-100 text-blue-800',
                        ];
                        $color = $statusColors[$item->discount_status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                        {{ ucfirst($item->discount_status) }}
                    </span>
                </div>
                @if($item->verified_at)
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Verified At:</span>
                        <span class="font-medium">{{ $item->verified_at->format('M d, Y H:i') }}</span>
                    </div>
                @endif
                @if($item->verified_by)
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Verified By:</span>
                        <span class="font-medium">{{ $item->verified_by }}</span>
                    </div>
                @endif
                @if($item->claimed_at)
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Claimed At:</span>
                        <span class="font-medium">{{ $item->claimed_at->format('M d, Y H:i') }}</span>
                    </div>
                @endif
                @if($item->rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded p-3 mt-3">
                        <span class="text-red-700 font-medium">Rejection Reason:</span>
                        <p class="text-red-600 mt-1">{{ $item->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Actions</h2>
            
            @if($item->discount_status === 'pending')
                <div class="space-y-3">
                    <form action="{{ route('admin.discount-verifications.approve', ['type' => $type, 'id' => $item->id]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center justify-center">
                            <i class="bi bi-check-circle mr-2"></i> Approve Discount
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.discount-verifications.reject', ['type' => $type, 'id' => $item->id]) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason (required)</label>
                            <textarea name="reason" required rows="2" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500" placeholder="Enter reason for rejection..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center justify-center">
                            <i class="bi bi-x-circle mr-2"></i> Reject Discount
                        </button>
                    </form>
                </div>
            @elseif($item->discount_status === 'verified')
                <div class="space-y-3">
                    <div class="bg-green-50 border border-green-200 rounded p-3">
                        <p class="text-green-700 text-sm">
                            <i class="bi bi-check-circle mr-1"></i>
                            This discount has been verified and is ready for claim at {{ $type === 'booking' ? 'check-in' : 'pickup' }}.
                        </p>
                    </div>
                    
                    <form action="{{ route('admin.discount-verifications.claim', ['type' => $type, 'id' => $item->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                            <i class="bi bi-check2-square mr-2"></i> Mark as Claimed at {{ $type === 'booking' ? 'Check-in' : 'Pickup' }}
                        </button>
                    </form>
                </div>
            @elseif($item->discount_status === 'rejected')
                <div class="space-y-3">
                    <div class="bg-red-50 border border-red-200 rounded p-3">
                        <p class="text-red-700 text-sm">
                            <i class="bi bi-exclamation-triangle mr-1"></i>
                            This discount was rejected. Customer must pay the difference.
                        </p>
                    </div>
                    
                    <form action="{{ route('admin.discount-verifications.record-payment', ['type' => $type, 'id' => $item->id]) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid</label>
                            <input type="number" name="amount_paid" step="0.01" required class="w-full border rounded-lg px-3 py-2" placeholder="0.00">
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_method" required class="w-full border rounded-lg px-3 py-2">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="card">Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                            <input type="text" name="reference" class="w-full border rounded-lg px-3 py-2" placeholder="Optional">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                            <i class="bi bi-cash-coin mr-2"></i> Record Payment
                        </button>
                    </form>
                </div>
            @elseif($item->discount_status === 'claimed')
                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                    <p class="text-blue-700 text-sm">
                        <i class="bi bi-check2-all mr-1"></i>
                        This discount has been claimed at {{ $type === 'booking' ? 'check-in' : 'pickup' }}.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

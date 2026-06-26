@extends('layouts.admin')

@section('title', 'Booking Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-semibold text-gray-800">Booking Details</h1>
        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Booking Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Transaction ID</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $booking->transaction_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Booking Type</label>
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
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full {{ $badgeColor }}">
                            {{ $typeLabel }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Check-in Date</label>
                        <p class="text-lg text-gray-900">{{ \Carbon\Carbon::parse($booking->checkin_date)->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Check-out Date</label>
                        <p class="text-lg text-gray-900">
                            {{ $booking->display_checkout_date?->format('F d, Y') ?? 'N/A' }}
                        </p>
                    </div>
                    @if($booking->isStaycationFamilyBooking() && $booking->display_nights)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Number of Nights</label>
                        <p class="text-lg text-gray-900">{{ $booking->display_nights }} night(s)</p>
                    </div>
                    @endif
                    @if(!$booking->isStaycationFamilyBooking() && !empty($selectedPackages))
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Selected Package Count</label>
                        <p class="text-lg text-gray-900">{{ count($selectedPackages) }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        @if($booking->status == 'confirmed')
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                        @elseif($booking->status == 'cancelled')
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                        @elseif($booking->status == 'completed')
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Booking Date</label>
                        <p class="text-lg text-gray-900">{{ $booking->created_at->format('F d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            @if($booking->isStaycationFamilyBooking() && isset($booking->booking_data['accommodations']))
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Accommodations</h2>
                <div class="space-y-4">
                    @foreach($booking->booking_data['accommodations'] as $accommodation)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-lg text-gray-900">
                                    @php
                                        $embeddedName = trim((string) (
                                            $accommodation['staycation']['name']
                                            ?? $accommodation['name']
                                            ?? $accommodation['accommodation_name']
                                            ?? ''
                                        ));
                                        $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                                    @endphp
                                    {{ $embeddedName !== '' ? $embeddedName : ($staycationId && isset($staycations[$staycationId]) ? $staycations[$staycationId]->name : 'N/A') }}
                                </h3>
                                <div class="mt-2 space-y-1 text-sm text-gray-600">
                                    <p><span class="font-medium">Quantity:</span> {{ $accommodation['quantity'] ?? 1 }}</p>
                                    @if(isset($accommodation['adults']))
                                    <p><span class="font-medium">Adults:</span> {{ $accommodation['adults'] }}</p>
                                    @endif
                                    @if(isset($accommodation['children']) && $accommodation['children'] > 0)
                                    <p><span class="font-medium">Children:</span> {{ $accommodation['children'] }}</p>
                                    @endif
                                    @if(isset($accommodation['pet']) && $accommodation['pet'] > 0)
                                    <p><span class="font-medium">Pets:</span> {{ $accommodation['pet'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900">₱{{ number_format((float) ($accommodation['total_price'] ?? 0), 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($selectedPackages))
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    {{ $booking->booking_type === 'dayvisit' ? 'Selected Day Visit Packages' : 'Selected Packages' }}
                </h2>
                <div class="space-y-3">
                    @foreach($selectedPackages as $package)
                    <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $package['name'] }}</p>
                            <div class="text-sm text-gray-500 space-y-1">
                                <p>Guests: {{ $package['guests'] ?? 1 }}</p>
                                <p>Quantity: {{ $package['quantity'] ?? 1 }}</p>
                                <p>Rate: ₱{{ number_format((float) ($package['price_per_person'] ?? 0), 2) }}</p>
                            </div>
                        </div>
                        <p class="font-semibold text-gray-900">₱{{ number_format((float) ($package['total_price'] ?? 0), 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($foodItems))
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Food Orders</h2>
                <div class="space-y-3">
                    @foreach($foodItems as $food)
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $food['name'] }}</p>
                            <p class="text-sm text-gray-500">Quantity: {{ $food['quantity'] }}</p>
                            <p class="text-sm text-gray-500">Rate: ₱{{ number_format((float) ($food['unit_price'] ?? 0), 2) }}</p>
                        </div>
                        <p class="font-semibold text-gray-900">₱{{ number_format((float) ($food['total_price'] ?? 0), 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($singleActivities))
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Selected Activities</h2>
                <div class="space-y-3">
                    @foreach($singleActivities as $activity)
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $activity['name'] }}</p>
                            <p class="text-sm text-gray-500">Guests / Qty: {{ $activity['quantity'] }}</p>
                            <p class="text-sm text-gray-500">Rate: ₱{{ number_format((float) ($activity['unit_price'] ?? 0), 2) }}</p>
                        </div>
                        <p class="font-semibold text-gray-900">₱{{ number_format((float) ($activity['total_price'] ?? 0), 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($activityPackages))
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Selected Activity Packages</h2>
                <div class="space-y-3">
                    @foreach($activityPackages as $activityPackage)
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $activityPackage['name'] }}</p>
                            <p class="text-sm text-gray-500">Quantity: {{ $activityPackage['quantity'] }}</p>
                            <p class="text-sm text-gray-500">Rate: ₱{{ number_format((float) ($activityPackage['unit_price'] ?? 0), 2) }}</p>
                        </div>
                        <p class="font-semibold text-gray-900">₱{{ number_format((float) ($activityPackage['total_price'] ?? 0), 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Customer Information</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Name</label>
                        <p class="text-lg text-gray-900">{{ $booking->display_customer_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900">{{ $booking->customer_email }}</p>
                    </div>
                    @if($booking->customer_phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900">{{ $booking->customer_phone }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Payment Information</h2>
                @php
                    $paymentStatus = strtolower((string) ($booking->payment_status ?? 'pending'));
                    $attemptState = data_get($booking, 'booking_data.payment_attempt.state');
                    $isPendingExpired = in_array($paymentStatus, ['pending', 'failed'], true)
                        || in_array($attemptState, ['pending', 'expired', 'failed'], true);
                    $attemptLabel = match ($attemptState) {
                        'expired' => 'Expired Attempt',
                        'failed' => 'Failed Attempt',
                        'pending' => 'Active Attempt',
                        default => null,
                    };
                @endphp
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold text-gray-900">₱{{ number_format((float) $booking->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 bg-[#8f5d54] text-white px-3 rounded-lg">
                        <span class="font-semibold">Total Amount</span>
                        <span class="text-xl font-bold">₱{{ number_format((float) $booking->total_amount, 2) }}</span>
                    </div>
                    <div class="pt-2">
                        <label class="block text-sm font-medium text-gray-500">Payment Method</label>
                        <p class="text-gray-900">{{ $booking->payment_method ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Payment Status</label>
                        @php
                            $paymentBadge = $isPendingExpired ? 'bg-amber-100 text-amber-800' : match ($paymentStatus) {
                                'paid' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'refunded' => 'bg-purple-100 text-purple-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                            $paymentLabel = $isPendingExpired ? 'Pending/Expired' : ucfirst($paymentStatus);
                        @endphp
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full {{ $paymentBadge }}">
                            {{ ucfirst($booking->payment_status) }}
                            {{ $paymentLabel }}
                        </span>
                        @if($attemptLabel)
                        <span class="inline-block mt-1 ml-2 px-3 py-1 text-sm font-semibold rounded-full bg-slate-100 text-slate-700">
                            {{ $attemptLabel }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($booking->status == 'confirmed')
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Actions</h2>
                <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking? Inventory will be restored.');">
                    @csrf
                    @method('POST')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-times-circle mr-2"></i>Cancel Booking
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

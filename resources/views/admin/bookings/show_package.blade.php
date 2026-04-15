@extends('layouts.admin')

@section('title', 'Package Booking')

@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h1 class="text-2xl font-semibold mb-4">Package Booking: {{ $booking->booking_reference }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-600">Name</p>
            <p class="font-medium">{{ $booking->full_name }}</p>

            <p class="text-sm text-gray-600 mt-3">Email</p>
            <p class="font-medium">{{ $booking->email }}</p>

            <p class="text-sm text-gray-600 mt-3">Phone</p>
            <p class="font-medium">{{ $booking->phone ?? 'N/A' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-600">Booking Date</p>
            <p class="font-medium">{{ optional($booking->booking_date)->format('M d, Y') }}</p>

            <p class="text-sm text-gray-600 mt-3">Total Guests</p>
            <p class="font-medium">{{ $booking->total_guests }}</p>

            <p class="text-sm text-gray-600 mt-3">Total Amount</p>
            <p class="font-medium">₱{{ number_format($booking->total_amount, 2) }}</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Back to bookings</a>
    </div>
</div>
@endsection

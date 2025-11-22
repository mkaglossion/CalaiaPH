<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in/out - Calaia Farm</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-[#f5efe7]">
    <x-header active="checkinout" />

    <main class="min-h-screen py-8">
        <div class="container mx-auto py-12 max-w-7xl">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-6 ml-24 text-3xl font-bold text-[#4d3327]">Check-in/out</h1>

                    @if(empty($activeBookings ?? []))
                        <div class="bg-white border border-blue-200 rounded-lg p-8 text-center shadow-sm">
                            <i class="bi bi-calendar-x text-4xl mb-3 text-blue-500" aria-hidden="true"></i>
                            <h2 class="text-xl font-semibold text-[#4d3327] mb-2">No Active Bookings</h2>
                            <p class="text-gray-600 mb-6">
                                You don't have any active bookings that require check-in or check-out at this time.
                            </p>
                            <a
                                href="/booking/staycation"
                                class="inline-flex items-center gap-2 px-6 py-2 rounded-lg bg-[#db8636] text-[#f6f3ec] text-sm font-medium
                               shadow-sm transition hover:bg-[#db8636]/90 hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                Book a Stay
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($activeBookings as $booking)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="p-6 border-b border-gray-200">
                                        <div class="flex justify-between items-center mb-4">
                                            <h5 class="text-xl font-semibold text-[#4d3327]">Booking #{{ $booking->id ?? 'N/A' }}</h5>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                                @if(($booking->status ?? '') === 'confirmed') bg-blue-100 text-blue-800
                                                @elseif(($booking->status ?? '') === 'checked_in') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $booking->status ?? 'Unknown')) }}
                                            </span>
                                        </div>

                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Booking Type:</span>
                                                <span class="text-sm text-[#4d3327] capitalize">{{ $booking->type ?? 'Staycation' }}</span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Check-in Date:</span>
                                                <span class="text-sm text-[#4d3327]">
                                                    {{ $booking->check_in_date ? \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') : 'N/A' }}
                                                </span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Check-out Date:</span>
                                                <span class="text-sm text-[#4d3327]">
                                                    {{ $booking->check_out_date ? \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') : 'N/A' }}
                                                </span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Total Amount:</span>
                                                <span class="text-lg font-bold text-green-600">₱{{ number_format($booking->total_amount ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6">
                                        <div class="flex gap-2">
                                            @if(($booking->status ?? '') === 'confirmed')
                                                <button class="flex-1 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors checkin-btn"
                                                        data-booking-id="{{ $booking->id }}">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                                    Check In
                                                </button>
                                            @elseif(($booking->status ?? '') === 'checked_in')
                                                <button class="flex-1 px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors checkout-btn"
                                                        data-booking-id="{{ $booking->id }}">
                                                    <i class="bi bi-box-arrow-right me-1"></i>
                                                    Check Out
                                                </button>
                                            @else
                                                <button class="flex-1 px-4 py-2 bg-gray-500 text-white text-sm rounded-lg" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $booking->status ?? 'completed')) }}
                                                </button>
                                            @endif

                                            <button class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors">
                                                <i class="bi bi-eye me-1"></i>
                                                Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Check-in/Check-out Confirmation Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" id="checkInOutModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h5 class="text-lg font-semibold mb-4" id="checkInOutModalLabel">Confirm Action</h5>
                    <p class="text-gray-600 mb-6" id="checkInOutMessage">Are you sure you want to check in?</p>
                    <div class="flex gap-3">
                        <button type="button" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors" id="cancelCheckInOutBtn">
                            Cancel
                        </button>
                        <button type="button" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors" id="confirmCheckInOutBtn">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentBookingId = null;
            let currentAction = null;

            // Check-in button handler
            document.querySelectorAll('.checkin-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentBookingId = this.getAttribute('data-booking-id');
                    currentAction = 'checkin';
                    document.getElementById('checkInOutMessage').textContent = 'Are you sure you want to check in to this booking?';
                    document.getElementById('confirmCheckInOutBtn').textContent = 'Check In';
                    document.getElementById('confirmCheckInOutBtn').className = 'flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors';
                    document.getElementById('checkInOutModal').classList.remove('hidden');
                });
            });

            // Check-out button handler
            document.querySelectorAll('.checkout-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentBookingId = this.getAttribute('data-booking-id');
                    currentAction = 'checkout';
                    document.getElementById('checkInOutMessage').textContent = 'Are you sure you want to check out from this booking?';
                    document.getElementById('confirmCheckInOutBtn').textContent = 'Check Out';
                    document.getElementById('confirmCheckInOutBtn').className = 'flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors';
                    document.getElementById('checkInOutModal').classList.remove('hidden');
                });
            });

            // Cancel button
            document.getElementById('cancelCheckInOutBtn').addEventListener('click', function() {
                document.getElementById('checkInOutModal').classList.add('hidden');
                currentBookingId = null;
                currentAction = null;
            });

            // Confirm check-in/check-out
            document.getElementById('confirmCheckInOutBtn').addEventListener('click', function() {
                if (!currentBookingId || !currentAction) return;

                // For now, just show a success message and close modal
                // In a real implementation, this would make an AJAX call to update the booking status
                showAlert(`Successfully ${currentAction === 'checkin' ? 'checked in' : 'checked out'}!`, 'success');

                document.getElementById('checkInOutModal').classList.add('hidden');
                currentBookingId = null;
                currentAction = null;

                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            });

            // Utility function to show alerts
            function showAlert(message, type) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg text-sm font-medium ${
                    type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'
                }`;
                alertDiv.textContent = message;

                document.body.appendChild(alertDiv);

                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 3000);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
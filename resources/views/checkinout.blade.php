<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in/out - Calaia Farm</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-[#f5efe7]">
    <x-header active="checkinout" />

    <main class="min-h-screen py-8">
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
                                            @php
                                                $statusClasses = match($booking->status ?? '') {
                                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                                    'checked_in' => 'bg-green-100 text-green-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClasses }}">
                                                {{ ucfirst(str_replace('_', ' ', $booking->status ?? 'Unknown')) }}
                                            </span>
                                        </div>

                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Booking Type:</span>
                                                <span class="text-sm text-[#4d3327] capitalize">{{ $booking->booking_type ?? 'Staycation' }}</span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Check-in Date:</span>
                                                <span class="text-sm text-[#4d3327]">
                                                    {{ $booking->checkin_date ? \Carbon\Carbon::parse($booking->checkin_date)->format('M d, Y') : 'N/A' }}
                                                </span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-600">Check-out Date:</span>
                                                <span class="text-sm text-[#4d3327]">
                                                    {{ $booking->checkout_date ? \Carbon\Carbon::parse($booking->checkout_date)->format('M d, Y') : 'N/A' }}
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
                                            @if(($booking->checkin_status ?? 'pending') === 'pending')
                                                <button class="flex-1 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors checkin-btn"
                                                        data-booking-id="{{ $booking->id }}">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                                    Check In
                                                </button>
                                            @elseif(($booking->checkin_status ?? 'pending') === 'checked_in')
                                                <button class="flex-1 px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors checkout-btn"
                                                        data-booking-id="{{ $booking->id }}">
                                                    <i class="bi bi-box-arrow-right me-1"></i>
                                                    Check Out
                                                </button>
                                            @else
                                                <button class="flex-1 px-4 py-2 bg-gray-500 text-white text-sm rounded-lg" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $booking->checkin_status ?? 'checked_out')) }}
                                                </button>
                                            @endif

                                            <button class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors details-btn"
                                                    data-booking-id="{{ $booking->id }}"
                                                    data-booking='@json($booking)'>
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
    <div class="fixed inset-0 hidden z-50" id="checkInOutModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <i class="bi bi-question-circle text-blue-600 text-2xl mr-3"></i>
                        <h5 class="text-lg font-semibold text-gray-800" id="checkInOutModalLabel">Confirm Action</h5>
                    </div>
                    <p class="text-gray-600 mb-6" id="checkInOutMessage">Are you sure you want to check in?</p>
                    <div class="flex gap-3">
                        <button type="button" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium" id="cancelCheckInOutBtn">
                            Cancel
                        </button>
                        <button type="button" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium" id="confirmCheckInOutBtn">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="fixed inset-0 hidden z-50" id="detailsModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl overflow-y-auto max-h-[90vh]">
                <div class="flex items-center justify-between p-4 md:p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="bi bi-info-circle text-blue-600 text-lg md:text-xl mr-2 md:mr-3"></i>
                        <h5 class="text-lg md:text-xl font-bold text-gray-800">Booking Details</h5>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" id="closeDetailsModal">
                        <i class="bi bi-x-lg text-xl md:text-2xl"></i>
                    </button>
                </div>
                <div class="p-4 md:p-6" id="detailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <x-footer />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentBookingId = null;
            let currentAction = null;

            // Next button handler
            document.querySelectorAll('.next-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');
                    window.location.href = `/checkinout/next/${bookingId}`;
                });
            });
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
            // Details button handler
            document.querySelectorAll('.details-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookingData = JSON.parse(this.getAttribute('data-booking'));
                    const content = document.getElementById('detailsContent');

                    let html = `
                        <div class="space-y-6">
                            <!-- Booking Information Card -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <i class="bi bi-calendar-check text-blue-600 mr-2"></i>
                                    <h6 class="font-semibold text-gray-800">Booking Information</h6>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div><strong class="text-gray-600">Booking ID:</strong> <span class="text-gray-800">#${bookingData.id}</span></div>
                                    <div><strong class="text-gray-600">Type:</strong> <span class="text-gray-800 capitalize">${bookingData.booking_type || 'N/A'}</span></div>
                                    <div><strong class="text-gray-600">Status:</strong> <span class="px-2 py-1 rounded-full text-xs font-medium ${bookingData.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${bookingData.status || 'N/A'}</span></div>
                                    <div><strong class="text-gray-600">Check-in Status:</strong> <span class="px-2 py-1 rounded-full text-xs font-medium ${bookingData.checkin_status === 'checked_in' ? 'bg-blue-100 text-blue-800' : bookingData.checkin_status === 'checked_out' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800'}">${(bookingData.checkin_status || 'pending').replace('_', ' ')}</span></div>
                                    <div><strong class="text-gray-600">Check-in Date:</strong> <span class="text-gray-800">${bookingData.checkin_date ? new Date(bookingData.checkin_date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</span></div>
                                    <div><strong class="text-gray-600">Check-out Date:</strong> <span class="text-gray-800">${bookingData.checkout_date ? new Date(bookingData.checkout_date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</span></div>
                                    <div><strong class="text-gray-600">Checked In At:</strong> <span class="text-gray-800">${bookingData.checkin_timestamp ? new Date(bookingData.checkin_timestamp).toLocaleString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Not checked in'}</span></div>
                                    <div><strong class="text-gray-600">Checked Out At:</strong> <span class="text-gray-800">${bookingData.checkout_timestamp ? new Date(bookingData.checkout_timestamp).toLocaleString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Not checked out'}</span></div>
                                    <div class="md:col-span-2"><strong class="text-gray-600">Total Amount:</strong> <span class="text-lg font-bold text-green-600">₱${(bookingData.total_amount || 0).toLocaleString()}</span></div>
                                </div>
                            </div>

                            <!-- Customer Information Card -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <i class="bi bi-person text-blue-600 mr-2"></i>
                                    <h6 class="font-semibold text-gray-800">Customer Information</h6>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div><strong class="text-gray-600">Name:</strong> <span class="text-gray-800">${bookingData.customer_name || 'N/A'}</span></div>
                                    <div><strong class="text-gray-600">Email:</strong> <span class="text-gray-800">${bookingData.customer_email || 'N/A'}</span></div>
                                    <div><strong class="text-gray-600">Phone:</strong> <span class="text-gray-800">${bookingData.customer_phone || 'N/A'}</span></div>
                                </div>
                            </div>
                        </div>
                    `;

                    if (bookingData.booking_data) {
                        const data = bookingData.booking_data;

                        if (data.accommodations && data.accommodations.length > 0) {
                            html += `
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i class="bi bi-house text-blue-600 mr-2"></i>
                                        <h6 class="font-semibold text-gray-800">Accommodations</h6>
                                    </div>
                                    <div class="space-y-2">
                                        ${data.accommodations.map(acc => `
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white p-3 rounded border">
                                                <div class="mb-2 sm:mb-0">
                                                    <div class="font-medium text-gray-800">${acc.name || acc.room_type || 'N/A'}</div>
                                                    <div class="text-sm text-gray-600">${acc.description || ''}</div>
                                                </div>
                                                <div class="text-left sm:text-right">
                                                    <div class="font-semibold text-green-600">₱${(acc.price || 0).toLocaleString()}</div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        if (data.selected_activities && data.selected_activities.length > 0) {
                            html += `
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i class="bi bi-activity text-blue-600 mr-2"></i>
                                        <h6 class="font-semibold text-gray-800">Activities</h6>
                                    </div>
                                    <div class="space-y-2">
                                        ${data.selected_activities.map(act => `
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white p-3 rounded border">
                                                <div class="mb-2 sm:mb-0">
                                                    <div class="font-medium text-gray-800">${act.name || 'N/A'}</div>
                                                    <div class="text-sm text-gray-600">${act.description || ''}</div>
                                                </div>
                                                <div class="text-left sm:text-right">
                                                    <div class="text-sm text-gray-600">${act.guests || 1} guest(s)</div>
                                                    <div class="font-semibold text-green-600">₱${(act.price || 0).toLocaleString()}</div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        if (data.selected_foods && data.selected_foods.length > 0) {
                            html += `
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i class="bi bi-cup-hot text-blue-600 mr-2"></i>
                                        <h6 class="font-semibold text-gray-800">Food & Dining</h6>
                                    </div>
                                    <div class="space-y-2">
                                        ${data.selected_foods.map(food => `
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white p-3 rounded border">
                                                <div class="mb-2 sm:mb-0">
                                                    <div class="font-medium text-gray-800">${food.name || 'N/A'}</div>
                                                    <div class="text-sm text-gray-600">${food.category || ''}</div>
                                                </div>
                                                <div class="text-left sm:text-right">
                                                    <div class="text-sm text-gray-600">Qty: ${food.quantity || 1}</div>
                                                    <div class="font-semibold text-green-600">₱${(food.price || 0).toLocaleString()}</div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        if (data.package) {
                            html += `
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i class="bi bi-box-seam text-blue-600 mr-2"></i>
                                        <h6 class="font-semibold text-gray-800">Package</h6>
                                    </div>
                                    <div class="bg-white p-3 rounded border">
                                        <span class="font-medium text-gray-800">${data.package}</span>
                                    </div>
                                </div>
                            `;
                        }

                        if (data.guests) {
                            html += `
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i class="bi bi-people text-blue-600 mr-2"></i>
                                        <h6 class="font-semibold text-gray-800">Guests</h6>
                                    </div>
                                    <div class="bg-white p-3 rounded border">
                                        <span class="font-medium text-gray-800">${data.guests} guest(s)</span>
                                    </div>
                                </div>
                            `;
                        }
                    }

                    content.innerHTML = html;
                    document.getElementById('detailsModal').classList.remove('hidden');
                });
            });

            // Close details modal
            document.getElementById('closeDetailsModal').addEventListener('click', function() {
                document.getElementById('detailsModal').classList.add('hidden');
            });

            // Close modal when clicking outside
            document.getElementById('detailsModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });

            // Cancel button
            document.getElementById('cancelCheckInOutBtn').addEventListener('click', function() {
                document.getElementById('checkInOutModal').classList.add('hidden');
                currentBookingId = null;
                currentAction = null;
            });

            // Close check-in/out modal when clicking outside
            document.getElementById('checkInOutModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    currentBookingId = null;
                    currentAction = null;
                }
            });

            // Keyboard support
            document.addEventListener('keydown', function(e) {
                if (document.getElementById('checkInOutModal').classList.contains('hidden')) return;

                if (e.key === 'Escape') {
                    document.getElementById('checkInOutModal').classList.add('hidden');
                    currentBookingId = null;
                    currentAction = null;
                } else if (e.key === 'Enter') {
                    document.getElementById('confirmCheckInOutBtn').click();
                }
            });

            // Confirm check-in/check-out
            document.getElementById('confirmCheckInOutBtn').addEventListener('click', function() {
                if (!currentBookingId || !currentAction) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/checkinout/${currentAction}/${currentBookingId}`;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }

                document.body.appendChild(form);
                form.submit();
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
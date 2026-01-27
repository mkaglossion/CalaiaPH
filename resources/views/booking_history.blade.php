<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Calaia Farm</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-[#f5efe7] min-h-screen">
    <x-header active="booking_history" />

    <main class="py-8 min-h-screen">
        <div class="max-w-7xl mx-auto py-12">
            <h1 class="mb-6 ml-24 text-3xl font-bold text-[#4d3327]">My Bookings</h1>

            @if(!isset($bookings) || $bookings->isEmpty())
                {{-- Empty state --}}
                <div class="rounded-2xl bg-white border border-[#cccbbd] p-8 text-center shadow-sm">
                    <i class="bi bi-info-circle text-4xl mb-3 text-[#00848d]" aria-hidden="true"></i>
                    <h2 class="text-xl font-semibold text-[#4d3327] mb-2">No Bookings Yet</h2>
                    <p class="text-sm text-[#4d3327] mb-6">
                        You haven't made any bookings yet.
                    </p>
                    <a
                        href="{{ route('booking.staycation') }}"
                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg bg-[#db8636] text-[#f6f3ec] text-sm font-medium
                               shadow-sm transition hover:bg-[#db8636]/90 hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                        Make Your First Booking
                    </a>
                </div>
            @else
                {{-- Bookings Table --}}
                <div class="bg-white rounded-2xl border border-[#cccbbd] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead class="bg-[#f6f3ec] border-b border-[#cccbbd]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Transaction ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Check-in</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Check-out</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider">Booked On</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#cccbbd]">
                                @foreach($bookings as $booking)
                                    @php
                                        $status = strtolower($booking->status ?? 'unknown');
                                        $paymentStatus = strtolower($booking->payment_status ?? 'pending');

                                        $statusClass = match ($status) {
                                            'confirmed'   => 'bg-[#00848d]/10 text-[#00848d]',
                                            'pending'     => 'bg-[#db8636]/10 text-[#db8636]',
                                            'completed'   => 'bg-[#7a7d52]/10 text-[#7a7d52]',
                                            'cancelled'   => 'bg-red-100 text-red-800',
                                            default       => 'bg-[#f6f3ec] text-[#4d3327]',
                                        };

                                        $paymentClass = match ($paymentStatus) {
                                            'paid'        => 'bg-[#222c1e]/10 text-[#222c1e]',
                                            default       => 'bg-[#db8636]/10 text-[#db8636]',
                                        };
                                    @endphp
                                    <tr class="hover:bg-[#f6f3ec]/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#4d3327]">
                                            {{ $booking->transaction_id ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4d3327] capitalize">
                                            {{ ucfirst($booking->booking_type ?? 'Staycation') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4d3327]">
                                            {{ $booking->checkin_date ? \Carbon\Carbon::parse($booking->checkin_date)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4d3327]">
                                            {{ $booking->checkout_date ? \Carbon\Carbon::parse($booking->checkout_date)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#98655b]">
                                            ₱{{ number_format($booking->total_amount ?? 0, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $paymentClass }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4d3327]">
                                            {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('M d, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 bg-[#f6f3ec] border-t border-[#cccbbd]">
                        {{ $bookings->links() }}
                    </div>
                </div>
            @endif
        </div>
    </main>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>

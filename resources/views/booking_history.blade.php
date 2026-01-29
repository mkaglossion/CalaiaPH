<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.favicon')
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
                {{-- Desktop Table View --}}
                <div class="hidden md:block bg-white rounded-2xl border border-[#cccbbd] shadow-sm overflow-hidden">
                    <div class="w-full">
                        <table class="w-full table-auto min-w-full">
                            <thead class="bg-[#f6f3ec] border-b border-[#cccbbd]">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-20">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-16">Type</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-20">Check-in</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-20">Check-out</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-20">Amount</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-16">Payment</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-16">Status</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#7a7d52] uppercase tracking-wider w-20">Booked</th>
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
                                    <tr class="hover:bg-linear-to-r hover:from-[#f6f3ec]/80 hover:to-[#e8dcc0]/60 hover:shadow-lg hover:shadow-[#db8636]/20 hover:scale-[1.02] hover:ring-1 hover:ring-[#db8636]/30 transition-all duration-300 cursor-pointer border-b border-[#cccbbd]/30">
                                        <td class="px-3 py-3 text-xs font-medium text-[#4d3327] truncate max-w-20" title="{{ $booking->transaction_id ?? 'N/A' }}">
                                            {{ $booking->transaction_id ?? 'N/A' }}
                                        </td>
                                        <td class="px-3 py-3 text-xs text-[#4d3327] capitalize">
                                            {{ ucfirst($booking->booking_type ?? 'Staycation') }}
                                        </td>
                                        <td class="px-3 py-3 text-xs text-[#4d3327]">
                                            {{ $booking->checkin_date ? \Carbon\Carbon::parse($booking->checkin_date)->format('M d') . ($booking->checkin_timestamp ? ' (' . \Carbon\Carbon::parse($booking->checkin_timestamp)->format('g:i A') . ')' : '') : 'N/A' }}
                                        </td>
                                        <td class="px-3 py-3 text-xs text-[#4d3327]">
                                            {{ $booking->checkout_date ? \Carbon\Carbon::parse($booking->checkout_date)->format('M d') . ($booking->checkout_timestamp ? ' (' . \Carbon\Carbon::parse($booking->checkout_timestamp)->format('g:i A') . ')' : '') : 'N/A' }}
                                        </td>
                                        <td class="px-3 py-3 text-xs font-bold text-[#98655b]">
                                            ₱{{ number_format($booking->total_amount ?? 0, 0) }}
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $paymentClass }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-xs text-[#4d3327]">
                                            {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('M d') : 'N/A' }}
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

                {{-- Mobile Card View --}}
                <div class="md:hidden space-y-4">
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
                        <div class="bg-white rounded-xl border border-[#cccbbd] shadow-sm p-4">
                            {{-- Header --}}
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-[#4d3327]">#{{ $booking->transaction_id ?? 'N/A' }}</h3>
                                    <p class="text-sm text-[#7a7d52] capitalize">{{ ucfirst($booking->booking_type ?? 'Staycation') }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $paymentClass }}">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Dates --}}
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs font-medium text-[#7a7d52] uppercase tracking-wider">Check-in</p>
                                    <p class="text-sm text-[#4d3327]">{{ $booking->checkin_date ? \Carbon\Carbon::parse($booking->checkin_date)->format('M d, Y') . ($booking->checkin_timestamp ? ' at ' . \Carbon\Carbon::parse($booking->checkin_timestamp)->format('H:i') : '') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-[#7a7d52] uppercase tracking-wider">Check-out</p>
                                    <p class="text-sm text-[#4d3327]">{{ $booking->checkout_date ? \Carbon\Carbon::parse($booking->checkout_date)->format('M d, Y') . ($booking->checkout_timestamp ? ' at ' . \Carbon\Carbon::parse($booking->checkout_timestamp)->format('H:i') : '') : 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Amount and Booked On --}}
                            <div class="flex justify-between items-center pt-3 border-t border-[#cccbbd]">
                                <div>
                                    <p class="text-xs font-medium text-[#7a7d52] uppercase tracking-wider">Booked On</p>
                                    <p class="text-sm text-[#4d3327]">{{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('M d, Y') : 'N/A' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-[#7a7d52] uppercase tracking-wider">Amount</p>
                                    <p class="text-lg font-bold text-[#98655b]">₱{{ number_format($booking->total_amount ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Mobile Pagination --}}
                    <div class="mt-6">
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

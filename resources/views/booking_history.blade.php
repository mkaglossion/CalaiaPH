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

            @if(empty($bookings ?? []))
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($bookings as $booking)
                        @php
                            $status = strtolower($booking->status ?? 'unknown');
                            $paymentStatus = strtolower($booking->payment_status ?? 'pending');

                            $statusClass = match ($status) {
                                'confirmed'   => 'bg-[#00848d]/10 text-[#00848d]',
                                'pending'     => 'bg-[#db8636]/10 text-[#db8636]',
                                'completed'   => 'bg-[#7a7d52]/10 text-[#7a7d52]',
                                default       => 'bg-[#f6f3ec] text-[#4d3327]',
                            };

                            $paymentClass = match ($paymentStatus) {
                                'paid'        => 'bg-[#222c1e]/10 text-[#222c1e]',
                                default       => 'bg-[#db8636]/10 text-[#db8636]',
                            };

                            $primaryBtnLabel = null;
                            $primaryBtnClass = '';
                            $primaryBtnIcon  = '';

                            if ($status === 'confirmed') {
                                $primaryBtnLabel = 'Check In';
                                $primaryBtnIcon  = 'bi-box-arrow-in-right';
                                $primaryBtnClass = 'bg-[#00848d] hover:bg-[#00848d]/90';
                            } elseif ($status === 'checked_in') {
                                $primaryBtnLabel = 'Check Out';
                                $primaryBtnIcon  = 'bi-box-arrow-right';
                                $primaryBtnClass = 'bg-[#db8636] hover:bg-[#db8636]/90';
                            } elseif ($status === 'completed') {
                                $primaryBtnLabel = 'Completed';
                                $primaryBtnIcon  = 'bi-check-circle';
                                $primaryBtnClass = 'bg-[#7a7d52]';
                            }
                        @endphp

                        <article class="flex flex-col h-full rounded-2xl bg-[#f6f3ec] border border-[#cccbbd] shadow-sm overflow-hidden">
                            {{-- Header / summary --}}
                            <div class="p-6 border-b border-[#cccbbd]">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <h2 class="text-lg font-semibold text-[#4d3327] truncate">
                                        Booking #{{ $booking->id ?? 'N/A' }}
                                    </h2>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide {{ $statusClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                <dl class="space-y-3 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-[#7a7d52] font-medium">Booking Type</dt>
                                        <dd class="text-[#4d3327] capitalize">
                                            {{ $booking->type ?? 'Staycation' }}
                                        </dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-[#7a7d52] font-medium">Total Amount</dt>
                                        <dd class="text-[#98655b] text-lg font-bold">
                                            ₱{{ number_format($booking->total_amount ?? 0, 2) }}
                                        </dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-[#7a7d52] font-medium">Payment Status</dt>
                                        <dd class="px-2 py-1 rounded text-[0.7rem] font-semibold {{ $paymentClass }}">
                                            {{ ucfirst($paymentStatus) }}
                                        </dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-[#7a7d52] font-medium">Created</dt>
                                        <dd class="text-[#4d3327]">
                                            {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('M d, Y') : 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Footer / actions --}}
                            <div class="p-6 mt-auto">
                                <div class="flex gap-2">
                                    @if($primaryBtnLabel)
                                        <button
                                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg text-[#f6f3ec]
                                                   transition transform hover:-translate-y-0.5 hover:shadow-md {{ $primaryBtnClass }}"
                                            @if($status === 'completed') disabled @endif
                                        >
                                            <i class="bi {{ $primaryBtnIcon }}" aria-hidden="true"></i>
                                            {{ $primaryBtnLabel }}
                                        </button>
                                    @endif

                                    <button
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg
                                               border border-[#cccbbd] text-[#4d3327] bg-white hover:bg-[#f6f3ec] transition"
                                    >
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        Details
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>

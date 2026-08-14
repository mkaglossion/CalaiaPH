<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $booking->transaction_id }} - Booking Details</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="min-h-screen bg-[#f5efe7]">
    <x-header active="profile" />

    <main class="py-10 min-h-screen">
        <div class="container mx-auto px-4 max-w-5xl">
            <a href="{{ route('profile.show') }}#bookings" class="inline-flex items-center gap-2 mb-6 text-sm font-semibold" style="color:#00848d;text-decoration:none;">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to My Bookings
            </a>

            @if(session('success'))
                <div class="rounded-xl p-4 mb-6" style="background-color:rgba(122,125,82,.12);border:1px solid #7a7d52;color:#222c1e;">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="rounded-xl p-4 mb-6" style="background-color:rgba(219,134,54,.12);border:1px solid #db8636;color:#4d3327;">
                    {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-xl p-4 mb-6" style="background-color:rgba(185,28,28,.08);border:1px solid #b91c1c;color:#7f1d1d;">
                    {{ session('error') }}
                </div>
            @endif

            <section class="rounded-2xl bg-white shadow-sm overflow-hidden" style="border:1px solid #cccbbd;">
                <div class="p-6 md:p-8" style="background:linear-gradient(135deg,#98655b,#4d3327);color:#f6f3ec;">
                    <p class="text-sm uppercase tracking-wider opacity-80">Booking Reference</p>
                    <h1 class="text-3xl font-bold mt-1">{{ $booking->transaction_id }}</h1>
                    <p class="mt-2 opacity-90">{{ $booking->display_type_label }}</p>
                </div>

                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Stay Dates</p>
                            <p class="font-semibold mt-1" style="color:#4d3327;">
                                {{ optional($booking->checkin_date)->format('M d, Y') ?? 'N/A' }}
                                @if($booking->display_checkout_date)<br>to {{ $booking->display_checkout_date->format('M d, Y') }}@endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Booking Status</p>
                            <p class="font-semibold uppercase mt-1" style="color:#00848d;">{{ $booking->status }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Payment</p>
                            <p class="font-semibold uppercase mt-1" style="color:#222c1e;">{{ $booking->payment_status }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Original Booking Total</p>
                            <p class="text-xl font-bold mt-1" style="color:#98655b;">&#8369;{{ number_format((float) $booking->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white shadow-sm mt-8 p-6 md:p-8" style="border:1px solid #cccbbd;">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold" style="color:#4d3327;">Add-on Transactions</h2>
                        <p class="text-sm mt-1" style="color:#7a7d52;">Meals and activities are paid and recorded separately.</p>
                    </div>
                    <div class="flex flex-col sm:items-end gap-3">
                        <div class="sm:text-right">
                            <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Total Add-ons</p>
                            <p class="text-xl font-bold" style="color:#98655b;">&#8369;{{ number_format($booking->add_ons_total, 2) }}</p>
                        </div>
                        @if($booking->payment_status === 'paid' && $booking->status === 'confirmed' && (!$booking->display_checkout_date || !$booking->display_checkout_date->isPast()))
                            <a href="{{ route('profile.bookings.add-ons.create', $booking) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold"
                               style="background-color:#db8636;color:#fff;text-decoration:none;">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                Add Meals or Activities
                            </a>
                        @endif
                    </div>
                </div>

                @forelse($booking->addOnTransactions as $addOn)
                    @php
                        $displayStatus = $addOn->status === 'confirmed' ? $addOn->payment_status : $addOn->status;
                    @endphp
                    <article id="add-on-{{ $addOn->id }}"
                             class="rounded-xl p-5 mb-4 last:mb-0 scroll-mt-6"
                             style="background-color:#f6f3ec;border:1px solid #cccbbd;"
                             @if($addOn->canResumePayment()) data-pending-add-on-card data-expires-at="{{ $addOn->paymentExpiresAt()?->toIso8601String() }}" @endif>
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <a href="{{ route('profile.bookings.add-ons.show', [$booking, $addOn]) }}" class="font-bold" style="color:#00848d;text-decoration:none;">
                                    {{ $addOn->reference }}
                                </a>
                                <p class="text-xs uppercase font-semibold mt-1" style="color:#7a7d52;">{{ $displayStatus }}</p>
                            </div>
                            <div class="sm:text-right">
                                <p class="text-xl font-bold" style="color:#98655b;">&#8369;{{ number_format((float) $addOn->total_amount, 2) }}</p>
                                <p class="text-xs uppercase mt-1" style="color:#4d3327;">Payment: {{ $addOn->payment_status }}</p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 space-y-2" style="border-top:1px solid #cccbbd;">
                            @foreach($addOn->items ?? [] as $item)
                                @php
                                    $quantity = max(1, (int) ($item['quantity'] ?? $item['qty'] ?? 1));
                                    $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                                    $lineTotal = (float) ($item['total_price'] ?? ($unitPrice * $quantity));
                                @endphp
                                <div class="flex justify-between gap-4 text-sm" style="color:#4d3327;">
                                    <span>{{ $item['name'] ?? 'Add-on item' }} <span style="color:#7a7d52;">&times; {{ $quantity }}</span></span>
                                    <strong>&#8369;{{ number_format($lineTotal, 2) }}</strong>
                                </div>
                            @endforeach
                        </div>

                        @if($addOn->canResumePayment())
                            <div class="mt-4 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-top:1px solid #cccbbd;">
                                <p class="text-sm" style="color:#7a7d52;">
                                    Payment expires in
                                    <strong data-payment-countdown style="color:#4d3327;">60:00</strong>
                                </p>
                                <a href="{{ route('profile.bookings.add-ons.pay', [$booking, $addOn]) }}"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold"
                                   style="background-color:#00848d;color:#fff;text-decoration:none;">
                                    <i class="bi bi-credit-card" aria-hidden="true"></i>
                                    Continue to Separate Payment
                                </a>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-xl p-6 text-center" style="background-color:#f6f3ec;color:#4d3327;">
                        No add-on transactions for this booking.
                    </div>
                @endforelse
            </section>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
                <div class="rounded-xl bg-white p-5" style="border:1px solid #cccbbd;">
                    <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Original Booking Total</p>
                    <p class="text-2xl font-bold mt-1" style="color:#4d3327;">&#8369;{{ number_format((float) $booking->total_amount, 2) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5" style="border:1px solid #cccbbd;">
                    <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Add-ons Total</p>
                    <p class="text-2xl font-bold mt-1" style="color:#4d3327;">&#8369;{{ number_format($booking->add_ons_total, 2) }}</p>
                </div>
            </div>
        </div>
    </main>

    <x-footer />
    <script>
        document.querySelectorAll('[data-pending-add-on-card]').forEach(function (card) {
            const expiresAt = Date.parse(card.dataset.expiresAt || '');
            const countdown = card.querySelector('[data-payment-countdown]');
            if (!Number.isFinite(expiresAt) || !countdown) return;

            const updateCountdown = function () {
                const remainingSeconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = String(remainingSeconds % 60).padStart(2, '0');
                countdown.textContent = minutes + ':' + seconds;

                if (remainingSeconds <= 0) {
                    window.location.reload();
                }
            };

            updateCountdown();
            window.setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>

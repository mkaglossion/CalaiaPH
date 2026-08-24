@php
    $bookingCartCount = collect(session('booking_cart.items', []))->sum(function ($item) {
        $selection = is_array($item['selection'] ?? null) ? $item['selection'] : [];

        return max(1, (int) (($item['item_type'] ?? null) === 'activity'
            ? ($selection['guests'] ?? 1)
            : ($selection['quantity'] ?? 1)));
    });
@endphp

<a
    href="{{ route('booking.cart.index') }}"
    class="relative inline-flex items-center justify-center rounded-lg px-2 py-2 text-white transition hover:bg-white/15"
    aria-label="Open Booking Cart"
    title="Booking Cart"
>
    <i class="bi bi-bag text-xl"></i>
    <span data-booking-cart-count class="{{ $bookingCartCount ? '' : 'hidden' }} absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-center text-[11px] font-bold leading-5 text-white">{{ $bookingCartCount }}</span>
</a>

@php
    $bookingWishlistCount = Auth::check() ? Auth::user()->bookingWishlistItems()->count() : 0;
@endphp

<a href="{{ route('booking.wishlist.index') }}" class="relative inline-flex items-center justify-center rounded-lg px-2 py-2 text-white transition hover:bg-white/15" aria-label="Open Booking Wishlist" title="Wishlist">
    <i class="bi bi-heart text-xl"></i>
    <span data-booking-wishlist-count class="{{ $bookingWishlistCount ? '' : 'hidden' }} absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-center text-[11px] font-bold leading-5 text-white">{{ $bookingWishlistCount }}</span>
</a>

@php
    $selectionExpression = $selectionExpression ?? '{}';
@endphp
<button
    type="button"
    data-booking-wishlist-button
    data-wishlist-key="{{ $itemType }}:{{ $itemId }}"
    class="absolute left-3 top-3 z-20 inline-flex items-center gap-1.5 rounded-full border border-white/70 bg-white/95 px-3 py-1.5 text-xs font-bold text-[#8f5d54] shadow-md backdrop-blur transition hover:bg-[#8f5d54] hover:text-white"
    @click.stop="window.toggleBookingWishlist('{{ $itemType }}', {{ $itemId }}, @js(json_decode($selectionExpression, true) ?? []), $event)"
    aria-label="Save for later"
>
    <i data-wishlist-icon class="bi bi-heart"></i>
    <span data-wishlist-label>Save for later</span>
</button>

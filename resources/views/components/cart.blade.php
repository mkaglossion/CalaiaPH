@php
    $cart = session('cart', []);
    $cartCount = 0;

    if (is_array($cart)) {
        foreach ($cart as $item) {
            $cartCount += $item['quantity'] ?? 0;
        }
    }
@endphp

{{-- CART BUTTON --}}
<a href="#" data-js-cart-button
   class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#f6f0eb] hover:bg-[#e9ddd2] border border-[#d3b7aa] transition">
  <i class="bi bi-cart3 text-lg text-[#8f5d54]"></i>

  @if($cartCount > 0)
    <span data-js-cart-count class="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-[#8f5d54] text-white text-[10px] flex items-center justify-center px-1">
      {{ $cartCount }}
    </span>
  @endif
</a>

{{-- Cart Modal --}}
<div id="cart-modal" style="display:none; position:fixed; z-index:9999; width:400px; max-width:calc(100% - 24px); max-height:80vh; overflow-y:auto;">
  <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold">Shopping Cart</h3>
      <button id="cart-modal-close" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>

    <div id="cart-modal-content" class="p-5">
      <!-- Content will be injected here -->
    </div>
  </div>
</div>
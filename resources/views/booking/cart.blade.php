<x-app-layout active-header="booking">
    <main class="min-h-screen bg-[#f7f3ed] px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#8f5d54]">Staycation only</p>
                    <h1 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Booking Cart</h1>
                    <p class="mt-2 text-gray-600">This cart is separate from your Store Cart.</p>
                </div>
                <a href="{{ route('booking.wishlist.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#8f5d54] px-5 py-3 font-semibold text-[#8f5d54] hover:bg-[#8f5d54] hover:text-white"><i class="bi bi-heart"></i> Booking Wishlist</a>
            </div>

            @if(session('success'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
            @endif

            @if($items->isEmpty())
                <section class="rounded-2xl border border-[#ded5ca] bg-white px-6 py-16 text-center shadow-sm">
                    <i class="bi bi-bag text-5xl text-[#8f5d54]"></i>
                    <h2 class="mt-5 text-2xl font-bold">Your Booking Cart is empty</h2>
                    <a href="{{ route('booking.wishlist.index') }}" class="mt-6 inline-flex rounded-xl bg-[#df862e] px-6 py-3 font-semibold text-white">Open Booking Wishlist</a>
                </section>
            @else
                <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
                    <section class="space-y-4">
                        @foreach($items as $item)
                            <article class="rounded-2xl border border-[#ded5ca] bg-white p-4 shadow-sm">
                                <div class="flex gap-4">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-24 w-28 shrink-0 rounded-xl object-cover sm:h-28 sm:w-36">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#8f5d54]">{{ $item['type_label'] }}</p>
                                        <h2 class="booking-cart-item-title mt-1 text-gray-900">{{ $item['name'] }}</h2>
                                        <p class="mt-1 text-sm text-gray-600">{{ \Carbon\Carbon::parse($item['booking_context']['checkin'])->format('M j') }}–{{ \Carbon\Carbon::parse($item['booking_context']['checkout'])->format('M j, Y') }}</p>
                                        @if($item['price_label'])
                                            <p class="mt-1 text-xs font-medium text-gray-500">{{ $item['price_label'] }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('booking.cart.destroy', $item['key']) }}">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-2 text-red-600 hover:bg-red-50" aria-label="Remove {{ $item['name'] }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-4 rounded-xl bg-gray-50 px-3 py-2.5">
                                    @if($item['item_type'] !== 'staycation')
                                        <form method="POST" action="{{ route('booking.cart.quantity.update', $item['key']) }}" class="flex items-center gap-2" aria-label="Update quantity for {{ $item['name'] }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-lg text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40" @disabled($item['quantity'] <= 1) aria-label="Decrease {{ $item['name'] }} quantity">&minus;</button>
                                            <output class="inline-flex h-10 min-w-14 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 font-semibold text-gray-900" aria-label="Current quantity">{{ $item['quantity'] }}</output>
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-lg text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40" @disabled($item['quantity'] >= $item['max_quantity']) aria-label="Increase {{ $item['name'] }} quantity">&plus;</button>
                                        </form>
                                    @else
                                        <span class="flex items-start gap-2 text-xs leading-5 text-gray-600 sm:text-sm">
                                            <i class="bi bi-info-circle mt-0.5 shrink-0 text-[#8f5d54]" aria-hidden="true"></i>
                                            <span>Room/tent quantity must be changed in the booking step.</span>
                                        </span>
                                    @endif
                                    <p class="shrink-0 text-lg font-bold text-[#8f5d54]">₱{{ number_format($item['line_total'], 2) }}</p>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <aside class="h-fit rounded-2xl border border-[#ded5ca] bg-white p-6 shadow-sm lg:sticky lg:top-28">
                        <h2 class="text-xl font-bold">Booking Summary</h2>
                        <div class="mt-5 flex justify-between border-b border-gray-200 pb-4 text-sm"><span>Current subtotal</span><strong>₱{{ number_format($subtotal, 2) }}</strong></div>
                        <p class="mt-4 text-xs leading-5 text-gray-500">Prices and availability are checked again by the server before checkout.</p>
                        <form method="POST" action="{{ route('booking.cart.checkout') }}" class="mt-5">
                            @csrf
                            <button class="w-full rounded-xl bg-[#df862e] px-5 py-3 font-semibold text-white hover:bg-[#c97426]">Proceed to Checkout</button>
                        </form>
                        <a href="{{ route('booking.staycationAvailability') }}" class="mt-3 block w-full rounded-xl border border-[#8f5d54] px-5 py-3 text-center font-semibold text-[#8f5d54]">Continue Booking</a>
                        <form method="POST" action="{{ route('booking.cart.clear') }}" class="mt-3 text-center">
                            @csrf @method('DELETE')
                            <button class="text-sm font-semibold text-red-600 hover:underline">Clear Booking Cart</button>
                        </form>
                    </aside>
                </div>
            @endif
        </div>
    </main>
</x-app-layout>

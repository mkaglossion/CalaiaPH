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
            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ $errors->first() }}</div>
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

                                @if($item['item_type'] === 'staycation')
                                    @php
                                        $selection = $item['selection'];
                                        $hasPets = in_array($selection['pets'] ?? false, [true, 1, '1', 'yes'], true);
                                    @endphp
                                    <form
                                        method="POST"
                                        action="{{ route('booking.cart.staycation.update', $item['key']) }}"
                                        class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4"
                                        x-data="{ pets: @js($hasPets ? 'yes' : 'no') }"
                                    >
                                        @csrf @method('PATCH')
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h3 class="font-semibold text-gray-900">Configure your stay</h3>
                                                <p class="text-xs text-gray-500">Guest and pet values below apply per room.</p>
                                            </div>
                                            <span class="text-xs font-medium {{ $item['max_quantity'] > 0 ? 'text-green-700' : 'text-red-600' }}">
                                                {{ $item['max_quantity'] > 0 ? $item['max_quantity'] . ' available' : 'Unavailable for these dates' }}
                                            </span>
                                        </div>

                                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                            <label class="block rounded-lg bg-white p-3 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200">
                                                {{ $item['is_special_staycation'] ? 'Unit quantity' : 'Room quantity' }}
                                                <input type="number" name="quantity" min="1" max="{{ max(1, $item['max_quantity']) }}" value="{{ $selection['quantity'] ?? 1 }}" class="mt-2 w-full rounded-lg border border-gray-400 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 shadow-sm focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20" @disabled($item['max_quantity'] < 1)>
                                            </label>
                                            <label class="block rounded-lg bg-white p-3 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200">
                                                Adults per room
                                                <input type="number" name="adults" min="1" max="{{ $item['guest_limits']['adults'] }}" value="{{ $selection['adults'] ?? 1 }}" class="mt-2 w-full rounded-lg border border-gray-400 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 shadow-sm focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                                            </label>
                                            <label class="block rounded-lg bg-white p-3 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200">
                                                Children per room
                                                <input type="number" name="children" min="0" max="{{ $item['guest_limits']['children'] }}" value="{{ $selection['children'] ?? 0 }}" class="mt-2 w-full rounded-lg border border-gray-400 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 shadow-sm focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                                            </label>
                                        </div>

                                        <fieldset class="mt-4">
                                            <legend class="text-sm font-semibold text-gray-700">Pets per room</legend>
                                            <div class="mt-2 flex gap-5">
                                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="radio" name="pets" value="no" x-model="pets" class="text-[#8f5d54] focus:ring-[#8f5d54]">
                                                    No
                                                </label>
                                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="radio" name="pets" value="yes" x-model="pets" class="text-[#8f5d54] focus:ring-[#8f5d54]">
                                                    Yes
                                                </label>
                                            </div>
                                        </fieldset>

                                        <div x-show="pets === 'yes'" x-cloak class="mt-4 grid gap-4 rounded-lg border border-amber-200 bg-amber-50 p-3 sm:grid-cols-2">
                                            <label class="block text-sm font-semibold text-gray-700">
                                                Number of pets per room
                                                <input type="number" name="num_pets" min="1" max="{{ $item['max_pets_per_room'] }}" value="{{ max(1, (int) ($selection['num_pets'] ?? 1)) }}" class="mt-2 w-full rounded-lg border border-amber-400 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 shadow-sm focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                                            </label>
                                            <label class="flex items-start gap-2 self-end pb-2 text-sm text-gray-700">
                                                <input type="hidden" name="pet_agreement" value="0">
                                                <input type="checkbox" name="pet_agreement" value="1" @checked($selection['pet_agreement'] ?? false) class="mt-0.5 rounded border-gray-300 text-[#8f5d54] focus:ring-[#8f5d54]">
                                                <span>I agree to the <button type="button" @click.stop="$dispatch('open-pet-policy')" class="font-semibold text-[#8f5d54] underline">Pet Policy</button>.</span>
                                            </label>
                                        </div>

                                        <div class="mt-4 flex flex-col gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-xs leading-5 text-gray-500">Defaults: 1 room, 1 adult, 0 children, no pets. Price and availability refresh after saving.</p>
                                            <button type="submit" class="shrink-0 rounded-lg bg-[#8f5d54] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#7a4e48] disabled:cursor-not-allowed disabled:opacity-50" @disabled($item['max_quantity'] < 1)>Save room details</button>
                                        </div>
                                    </form>
                                    <div data-staycation-price-breakdown class="mt-3 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm">
                                        <div class="flex justify-between gap-4 text-gray-600">
                                            <span>Accommodation{{ $item['price_breakdown']['room_quantity'] > 1 ? ' (' . $item['price_breakdown']['room_quantity'] . ' rooms)' : '' }}</span>
                                            <strong class="text-gray-900">&#8369;{{ number_format($item['price_breakdown']['accommodation_total'], 2) }}</strong>
                                        </div>
                                        @if($item['price_breakdown']['additional_adult_total'] > 0)
                                            <div class="mt-2 flex justify-between gap-4 text-gray-600">
                                                <span>Additional {{ $item['price_breakdown']['additional_adults_per_room'] }} {{ \Illuminate\Support\Str::plural('guest', $item['price_breakdown']['additional_adults_per_room']) }} per room</span>
                                                <strong class="text-gray-900">&#8369;{{ number_format($item['price_breakdown']['additional_adult_total'], 2) }}</strong>
                                            </div>
                                        @endif
                                        @if($item['price_breakdown']['pet_total'] > 0)
                                            <div class="mt-2 flex justify-between gap-4 text-gray-600">
                                                <span>{{ $item['price_breakdown']['pets_per_room'] }} {{ \Illuminate\Support\Str::plural('Pet', $item['price_breakdown']['pets_per_room']) }} per room</span>
                                                <strong class="text-gray-900">&#8369;{{ number_format($item['price_breakdown']['pet_total'], 2) }}</strong>
                                            </div>
                                        @endif
                                        <div class="mt-3 flex justify-between gap-4 border-t border-gray-200 pt-3">
                                            <strong class="text-gray-900">Estimated total</strong>
                                            <strong class="text-lg text-[#8f5d54]">&#8369;{{ number_format($item['line_total'], 2) }}</strong>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 flex items-center justify-between gap-4 rounded-xl bg-gray-50 px-3 py-2.5">
                                        <form method="POST" action="{{ route('booking.cart.quantity.update', $item['key']) }}" class="flex items-center gap-2" aria-label="Update quantity for {{ $item['name'] }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-lg text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40" @disabled($item['quantity'] <= 1) aria-label="Decrease {{ $item['name'] }} quantity">&minus;</button>
                                            <output class="inline-flex h-10 min-w-14 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 font-semibold text-gray-900" aria-label="Current quantity">{{ $item['quantity'] }}</output>
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-lg text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40" @disabled($item['quantity'] >= $item['max_quantity']) aria-label="Increase {{ $item['name'] }} quantity">&plus;</button>
                                        </form>
                                        <p class="shrink-0 text-lg font-bold text-[#8f5d54]">₱{{ number_format($item['line_total'], 2) }}</p>
                                    </div>
                                @endif
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
        @include('booking.partials.pet-policy-modal')
    </main>
</x-app-layout>

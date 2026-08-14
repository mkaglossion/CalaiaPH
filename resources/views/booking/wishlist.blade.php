<x-app-layout active-header="booking">
    <main class="min-h-screen bg-white px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <section class="grid gap-8 border-b border-gray-200 pb-9 lg:grid-cols-[1fr_360px] lg:items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#8f5d54]">Saved for later</p>
                    <h1 class="mt-3 text-5xl font-black tracking-tight text-gray-950 sm:text-6xl">Wishlist</h1>
                    <p class="mt-5 max-w-3xl text-base leading-7 text-gray-600 sm:text-lg">
                        Keep rooms, meals, and activities in one place. Select what you want, then add them to your Booking Cart for payment.
                    </p>
                </div>

                <aside class="rounded-3xl border border-gray-200 bg-gray-50 p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-gray-600">Items saved</p>
                    <div class="mt-3 flex items-end gap-3">
                        <strong class="text-4xl font-black text-gray-950">{{ $items->count() }}</strong>
                        <span class="pb-1 text-sm text-gray-500">booking {{ Str::plural('item', $items->count()) }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Saved to your account so you can return and review them later.</p>
                </aside>
            </section>

            <div class="flex flex-wrap gap-3 py-7">
                <a href="{{ route('booking.staycationAvailability') }}" class="inline-flex items-center justify-center rounded-full bg-gray-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#8f5d54]">Continue booking</a>
                <a href="{{ route('booking.cart.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-900 px-6 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-100">
                    <i class="bi bi-bag"></i> Open Booking Cart
                </a>
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

            @if(!$isAuthenticated)
                <section class="mx-auto mt-4 max-w-2xl border border-gray-200 bg-gray-50 px-6 py-14 text-center sm:px-12">
                    <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white text-3xl text-gray-900 shadow-sm"><i class="bi bi-person-lock"></i></span>
                    <h2 class="mt-6 text-3xl font-semibold text-gray-950">Log in to use your Wishlist</h2>
                    <p class="mx-auto mt-3 max-w-lg leading-7 text-gray-600">Your saved rooms, meals, and activities are securely connected to your account.</p>
                    <button type="button" @click="$store.modals.showLogin = true" class="mt-7 inline-flex rounded-full bg-gray-950 px-7 py-3 font-semibold text-white transition hover:bg-[#8f5d54]">Log in to continue</button>
                </section>
            @elseif($items->isEmpty())
                <section class="mx-auto mt-4 max-w-2xl border border-gray-200 bg-gray-50 px-6 py-14 text-center sm:px-12">
                    <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white text-3xl text-gray-900 shadow-sm"><i class="bi bi-heart"></i></span>
                    <h2 class="mt-6 text-3xl font-semibold text-gray-950">Your wishlist is empty</h2>
                    <p class="mx-auto mt-3 max-w-lg leading-7 text-gray-600">Save rooms, meals, and activities here so you can review them before adding them to your Booking Cart.</p>
                    <a href="{{ route('booking.staycationAvailability') }}" class="mt-7 inline-flex rounded-full bg-gray-950 px-7 py-3 font-semibold text-white transition hover:bg-[#8f5d54]">Browse booking options</a>
                </section>
            @else
                @php
                    $wishlistIds = $items->pluck('record_id')->map(fn ($id) => (string) $id)->values();
                @endphp

                <div
                    x-data="{
                        selected: [],
                        allIds: @js($wishlistIds),
                        get allSelected() { return this.selected.length === this.allIds.length && this.allIds.length > 0 },
                        toggleAll() { this.selected = this.allSelected ? [] : [...this.allIds] },
                        submitSelection(url, method = 'POST') {
                            if (this.selected.length === 0) {
                                if (window.Swal) Swal.fire('Select an item', 'Choose at least one wishlist item first.', 'info');
                                return;
                            }
                            const form = this.$refs.selectionForm;
                            form.action = url;
                            form.querySelector('input[name=_method]')?.remove();
                            if (method !== 'POST') {
                                const methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = method;
                                form.appendChild(methodInput);
                            }
                            this.$nextTick(() => form.requestSubmit());
                        }
                    }"
                >
                    <div class="mb-7 flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                        <label class="inline-flex cursor-pointer items-center gap-3 font-semibold text-gray-900">
                            <input type="checkbox" class="h-5 w-5 rounded border-gray-300 text-[#8f5d54] focus:ring-[#8f5d54]" :checked="allSelected" @change="toggleAll()">
                            <span>Select all</span>
                            <span class="font-normal text-gray-500" x-text="selected.length + ' selected'"></span>
                        </label>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="button" @click="submitSelection(@js(route('booking.wishlist.selected.destroy')), 'DELETE')" :disabled="selected.length === 0" class="rounded-full border border-gray-900 px-6 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">Remove selected</button>
                            <button type="button" @click="submitSelection(@js(route('booking.wishlist.add-to-cart')))" :disabled="selected.length === 0" class="rounded-full bg-gray-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#8f5d54] disabled:cursor-not-allowed disabled:opacity-40">Add selected to Booking Cart</button>
                        </div>
                    </div>

                    <form method="POST" x-ref="selectionForm">
                        @csrf
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($items as $item)
                                <article class="relative overflow-hidden rounded-3xl border bg-white p-4 transition" :class="selected.includes('{{ $item['record_id'] }}') ? 'border-[#8f5d54] ring-2 ring-[#8f5d54]/20' : 'border-gray-200 hover:shadow-lg'">
                                    <label class="absolute left-7 top-7 z-10 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-white shadow-md">
                                        <input type="checkbox" name="items[]" value="{{ $item['record_id'] }}" x-model="selected" class="h-5 w-5 rounded border-gray-300 text-[#8f5d54] focus:ring-[#8f5d54]">
                                        <span class="sr-only">Select {{ $item['name'] }}</span>
                                    </label>

                                    <button type="submit" form="remove-wishlist-{{ $item['record_id'] }}" class="absolute right-7 top-7 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white text-xl text-gray-800 shadow-md transition hover:bg-red-50 hover:text-red-600" aria-label="Remove {{ $item['name'] }}"><i class="bi bi-x-lg"></i></button>

                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="aspect-[4/3] w-full rounded-2xl bg-gray-100 object-cover">
                                    <div class="px-1 pb-2 pt-5">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#8f5d54]">{{ $item['type_label'] }}</p>
                                        <h2 class="mt-2 text-xl font-bold text-gray-950">{{ $item['name'] }}</h2>
                                        <p class="mt-2 text-sm leading-6 text-gray-600">
                                            {{ \Carbon\Carbon::parse($item['booking_context']['checkin'])->format('M j') }}–{{ \Carbon\Carbon::parse($item['booking_context']['checkout'])->format('M j, Y') }}
                                            @if($item['quantity'] > 1) · Qty: {{ $item['quantity'] }} @endif
                                        </p>
                                        <p class="mt-3 text-lg font-bold text-[#8f5d54]">&#8369;{{ number_format($item['line_total'], 2) }}</p>
                                        @if($item['price_label'])
                                            <p class="mt-1 text-xs font-medium text-gray-500">{{ $item['price_label'] }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </form>
                </div>

                @foreach($items as $item)
                    <form id="remove-wishlist-{{ $item['record_id'] }}" method="POST" action="{{ route('booking.wishlist.destroy', $item['record_id']) }}" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                @endforeach
            @endif
        </div>
    </main>
</x-app-layout>

@php
    $cart      = session('cart', []);
    $cartCount = 0;

    if (is_array($cart)) {
        foreach ($cart as $row) {
            $cartCount += $row['quantity'] ?? 0;
        }
    }
@endphp

<div x-data="cartDropdown({ initialCount: {{ $cartCount }} })" class="relative">
    {{-- CART BUTTON --}}
    <button
        @click="toggle()"
        class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#f6f0eb] hover:bg-[#e9ddd2] border border-[#d3b7aa] transition focus:outline-none"
    >
        <i class="bi bi-cart3 text-lg text-[#8f5d54]"></i>

        <template x-if="cart.count > 0">
            <span
                class="absolute -top-1 -right-1 min-w-4.5 h-4.5 rounded-full bg-[#8f5d54] text-white text-[10px] flex items-center justify-center px-1"
                x-text="cart.count"
            ></span>
        </template>
    </button>

    {{-- DROPDOWN --}}
    <div
        x-show="open"
        x-transition
        @click.away="open = false"
        class="absolute right-0 mt-2 w-80 sm:w-96 max-w-[calc(100vw-1rem)] bg-white border border-gray-200 rounded-lg shadow-2xl z-50 max-h-80 sm:max-h-96 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-[#8f5d54]">Shopping Cart</h3>
            <button @click="open = false" class="text-[#8f5d54] hover:text-[#6d3c2a] text-lg sm:text-xl">✕</button>
        </div>

        <div class="p-4">
            {{-- LOADING --}}
            <div x-show="loading" class="text-center py-6">
                <div class="animate-spin rounded-full h-7 w-7 border-b-2 border-[#8f5d54] mx-auto"></div>
                <p class="text-gray-500 mt-2 text-sm">Loading cart...</p>
            </div>

            {{-- EMPTY --}}
            <div x-show="!loading && cart.count === 0" class="text-center py-8">
                <i class="bi bi-cart-x text-5xl text-gray-300 mb-3"></i>
                <h2 class="text-lg font-semibold text-gray-600 mb-1">Your cart is empty</h2>
                <p class="text-gray-500 mb-4 text-sm">Start booking your staycation, activities, and shop</p>
                <a href="{{ route('booking.calendar') }}"
                   class="bg-[#8f5d54] text-white px-5 py-2 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition text-sm inline-flex items-center">
                    <i class="bi bi-calendar-event mr-1"></i>
                    Book Now
                </a>
            </div>

            {{-- WITH ITEMS --}}
            <div x-show="!loading && cart.count > 0" class="space-y-4">
                <!-- ICONS FOR BOOKING, FOOD, ACTIVITIES -->
                <div class="flex gap-3 items-center mb-2">
                    <template x-if="cart.items.some(item => item.type === 'booking')">
                        <span title="Booking"><i class="bi bi-calendar-event text-[#8f5d54] text-xl"></i></span>
                    </template>
                    <template x-if="cart.items.some(item => item.type === 'food')">
                        <span title="Food"><i class="bi bi-egg-fried text-[#8f5d54] text-xl"></i></span>
                    </template>
                    <template x-if="cart.items.some(item => item.type === 'activity')">
                        <span title="Activity"><i class="bi bi-bicycle text-[#8f5d54] text-xl"></i></span>
                    </template>
                </div>

                <!-- Grouped Cart Items by Type -->
                <template x-if="cart.items.filter(item => item.type === 'booking').length">
                    <div>
                        <div class="font-bold text-[#8f5d54] flex items-center gap-2 mb-1"><i class="bi bi-calendar-event"></i> Accommodation</div>
                        <template x-for="item in cart.items.filter(item => item.type === 'booking')" :key="item.id">
                            <div class="relative border-b pb-3 last:border-0 last:pb-0">
                                <button @click.prevent="removeItem(item.id)" class="absolute top-1.5 right-0 text-xs text-gray-400 hover:text-red-500 px-1" aria-label="Remove item">✕</button>
                                <div class="flex items-center gap-3 pr-4">
                                    <div class="w-10 h-10 shrink-0 rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <template x-if="item.image">
                                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <i class="bi bi-image text-gray-300 text-xl"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm leading-tight line-clamp-2" x-text="item.title"></h3>
                                        <div class="flex items-center justify-between mt-1 text-xs">
                                            <span class="text-gray-600" x-text="formatCurrency(item.price) + ' × ' + item.quantity"></span>
                                            <span class="font-semibold text-[#8f5d54]" x-text="formatCurrency(item.price * item.quantity)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="cart.items.filter(item => item.type === 'food').length">
                    <div>
                        <div class="font-bold text-[#8f5d54] flex items-center gap-2 mb-1 mt-2"><i class="bi bi-egg-fried"></i> Food</div>
                        <template x-for="item in cart.items.filter(item => item.type === 'food')" :key="item.id">
                            <div class="relative border-b pb-3 last:border-0 last:pb-0">
                                <button @click.prevent="removeItem(item.id)" class="absolute top-1.5 right-0 text-xs text-gray-400 hover:text-red-500 px-1" aria-label="Remove item">✕</button>
                                <div class="flex items-center gap-3 pr-4">
                                    <div class="w-10 h-10 shrink-0 rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <template x-if="item.image">
                                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <i class="bi bi-image text-gray-300 text-xl"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm leading-tight line-clamp-2" x-text="item.title"></h3>
                                        <div class="flex items-center justify-between mt-1 text-xs">
                                            <span class="text-gray-600" x-text="formatCurrency(item.price) + ' × ' + item.quantity"></span>
                                            <span class="font-semibold text-[#8f5d54]" x-text="formatCurrency(item.price * item.quantity)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="cart.items.filter(item => item.type === 'activity').length">
                    <div>
                        <div class="font-bold text-[#8f5d54] flex items-center gap-2 mb-1 mt-2"><i class="bi bi-bicycle"></i> Activities</div>
                        <template x-for="item in cart.items.filter(item => item.type === 'activity')" :key="item.id">
                            <div class="relative border-b pb-3 last:border-0 last:pb-0">
                                <button @click.prevent="removeItem(item.id)" class="absolute top-1.5 right-0 text-xs text-gray-400 hover:text-red-500 px-1" aria-label="Remove item">✕</button>
                                <div class="flex items-center gap-3 pr-4">
                                    <div class="w-10 h-10 shrink-0 rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <template x-if="item.image">
                                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <i class="bi bi-image text-gray-300 text-xl"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm leading-tight line-clamp-2" x-text="item.title"></h3>
                                        <div class="flex items-center justify-between mt-1 text-xs">
                                            <span class="text-gray-600" x-text="formatCurrency(item.price) + ' × ' + item.quantity"></span>
                                            <span class="font-semibold text-[#8f5d54]" x-text="formatCurrency(item.price * item.quantity)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Fallback: Show all items if no known types are present -->
                <template x-if="!cart.items.some(item => ['booking','food','activity'].includes(item.type)) && cart.items.length">
                    <div>
                        <div class="font-bold text-[#8f5d54] flex items-center gap-2 mb-1 mt-2"><i class="bi bi-bag"></i> Your Selections</div>
                        <template x-for="item in cart.items" :key="item.id">
                            <div class="relative border-b pb-3 last:border-0 last:pb-0">
                                <button @click.prevent="removeItem(item.id)" class="absolute top-1.5 right-0 text-xs text-gray-400 hover:text-red-500 px-1" aria-label="Remove item">✕</button>
                                <div class="flex items-center gap-3 pr-4">
                                    <div class="w-10 h-10 shrink-0 rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <template x-if="item.image">
                                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <i class="bi bi-image text-gray-300 text-xl"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm leading-tight line-clamp-2" x-text="item.title"></h3>
                                        <div class="flex items-center justify-between mt-1 text-xs">
                                            <span class="text-gray-600" x-text="formatCurrency(item.price) + ' × ' + item.quantity"></span>
                                            <span class="font-semibold text-[#8f5d54]" x-text="formatCurrency(item.price * item.quantity)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="border-t pt-4 mt-2">
                    <div class="flex justify-between items-center text-lg font-bold text-[#8f5d54]">
                        <span>Total:</span>
                        <span x-text="formatCurrency(cart.total)"></span>
                    </div>
                </div>

                <div class="mt-4 flex justify-center gap-2">
                    <a href="/shop"
                       class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition text-sm text-center">
                        Continue Shopping
                    </a>
                    <a href="/checkout"
                       class="bg-[#8f5d54] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition text-sm text-center">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alpine helper (can be anywhere after Alpine is loaded) --}}
<script>
    function cartDropdown({ initialCount = 0 } = {}) {
        return {
            open: false,
            loading: false,
            cart: { items: [], total: 0, count: initialCount },

            formatCurrency(value) {
                const num = Number(value ?? 0);
                return '₱' + num.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            },

            async fetchCart() {
                this.loading = true;
                try {
                    const res = await fetch('/cart', {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });

                    if (res.ok) {
                        this.cart = await res.json();
                    }
                } catch (err) {
                    console.error('Failed to load cart:', err);
                } finally {
                    this.loading = false;
                }
            },

            async removeItem(id) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrf) return;

                try {
                    const res = await fetch(`/cart/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    if (res.ok) {
                        await this.fetchCart();
                    }
                } catch (err) {
                    console.error('Failed to remove item:', err);
                }
            },

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.fetchCart();
                }
            },

            init() {
                this.fetchCart();
                // Listen for cart-updated event from other pages
                document.addEventListener('cart-updated', () => {
                    this.fetchCart();
                });
            }
        }
    }
</script>

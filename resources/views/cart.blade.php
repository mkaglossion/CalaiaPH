<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Shopping Cart - CalaiaPH">
    <meta name="keywords" content="cart, shopping cart, CalaiaPH">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>Shopping Cart - CalaiaPH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  </head>
  <body class="bg-[#f5efe7]">
    <x-header active="" />

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-[#8f5d54] mb-6 text-center">Shopping Cart</h1>

        @if($cartCount === 0)
            <div class="text-center py-12">
                <i class="bi bi-cart-x text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-600 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Add some activities to get started!</p>
                <a href="{{ url('/activities') }}" class="bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition">
                    Browse Activities
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="space-y-4">
                    @foreach($cart as $id => $item)
                        <div class="flex items-center gap-4 border-b pb-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg">{{ $item['title'] }}</h3>
                                <p class="text-gray-600">₱{{ number_format($item['price'], 2) }} each</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                    <button type="submit" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                                </form>
                                <span class="text-gray-600">{{ $item['quantity'] }}</span>
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                    <button type="submit" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">+</button>
                                </form>
                            </div>

                            <div class="text-right">
                                <p class="font-semibold text-lg">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                <form action="{{ route('cart.remove', $id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 border-t pt-6">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span>Total:</span>
                        <span>₱{{ number_format($cartTotal, 2) }}</span>
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <a href="{{ url('/activities') }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Continue Shopping
                    </a>
                    <a href="{{ route('checkout.form') }}" class="bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <x-footer />

    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
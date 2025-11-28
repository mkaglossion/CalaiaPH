<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Checkout - CalaiaPH">
    <meta name="keywords" content="checkout, payment, CalaiaPH">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>Checkout - CalaiaPH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  </head>
  <body class="bg-[#f5efe7]">
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('success') }}
        </div>
      @endif
    </div>

    <x-header active="" />

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-[#8f5d54] mb-6">Checkout</h1>

        @if($cartCount === 0)
            <div class="text-center py-12">
                <i class="bi bi-cart-x text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-600 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Add some activities before checking out!</p>
                <a href="{{ url('/activities') }}" class="bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition">
                    Browse Activities
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    <div class="space-y-4">
                        @foreach($cart as $id => $item)
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <h3 class="font-medium">{{ $item['title'] }}</h3>
                                    <p class="text-sm text-gray-600">Qty: {{ $item['quantity'] }} × ₱{{ number_format($item['price'], 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t mt-4 pt-4">
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span>₱{{ number_format($cartTotal, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Contact & Delivery Information</h2>
                    <form action="{{ route('checkout.submit') }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" required
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                <textarea id="address" name="address" rows="3" required
                                          class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none"></textarea>
                            </div>

                            <div>
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                                <input type="date" id="booking_date" name="booking_date" required
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select id="payment_method" name="payment_method" required
                                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                                    <option value="">Select Payment Method</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-[#8f5d54] text-white py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition mt-6">
                            Place Order
                        </button>
                    </form>
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
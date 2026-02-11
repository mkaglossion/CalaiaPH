<!doctype html>
<html lang="en">
  <head>
    @include('layouts.partials.favicon')
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
  <body class="bg-[#f5efe7] min-h-screen">
    <!-- Logo -->
    <div class="bg-linear-to-r from-white via-gray-50 to-white border-b border-gray-100 shadow-sm">
      <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-center">
          <a href="{{ route('home') }}" class="group transition-transform duration-300 hover:scale-105">
            <img
              src="/assets/logo/Calaia_LogoRust3.png"
              alt="CalaiaPH Logo"
              class="h-16 w-auto drop-shadow-sm group-hover:drop-shadow-md transition-all duration-300"
            >
          </a>
        </div>
      </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border-b border-gray-100">
      <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-center">
          <i class="bi bi-cart3 text-2xl text-[#8f5d54] mr-3"></i>
          <h1 class="text-2xl font-semibold text-[#8f5d54]" style="">Shopping Cart</h1>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div class="container mx-auto px-4 py-6">
      <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
        <i class="bi bi-arrow-left mr-2"></i>
        Back
      </a>
    </div>

    <div class="container mx-auto px-4 pb-16">
      @if($cartCount === 0)
        <!-- Empty Cart State -->
        <div class="max-w-md mx-auto text-center py-16">
          <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-cart-x text-3xl text-gray-400"></i>
          </div>
          <h2 class="text-xl font-medium text-gray-700 mb-3" style="">Your cart is empty</h2>
          <p class="text-gray-500 mb-8">Add some activities to get started</p>
          <a href="{{ url('/activities') }}" class="inline-flex items-center bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#7a4a42] transition-colors">
            Browse Activities
          </a>
        </div>
      @else
        <!-- Cart Content -->
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Cart Items -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
              <div class="bg-[#8f5d54] text-white px-6 py-4">
                <h2 class="text-xl font-semibold flex items-center" style="">
                  <i class="bi bi-receipt mr-2"></i>
                  Cart Items ({{ $cartCount }})
                </h2>
              </div>

              <div class="divide-y divide-gray-100">
                @foreach($cart as $id => $item)
                  <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center gap-6">
                      <!-- Item Info -->
                      <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $item['title'] }}</h3>
                        <div class="flex items-center text-gray-600 mb-3">
                          <i class="bi bi-tag mr-1"></i>
                          <span class="font-medium">₱{{ number_format($item['price'], 2) }}</span>
                          @if(isset($item['isDining']) && $item['isDining'])
                            <span class="text-gray-400 mx-2">
                              @if($item['price'] == 5980)
                                (3–4 Person)
                              @elseif($item['price'] == 8790)
                                (5–6 Person)
                              @else
                                (7–9 Person)
                              @endif
                            </span>
                          @else
                            <span class="text-gray-400 mx-2">per person</span>
                          @endif
                        </div>
                      </div>

                      <!-- Quantity Controls -->
                      <div class="flex items-center justify-between md:justify-end gap-4">
                        @if(isset($item['isDining']) && $item['isDining'])
                          <div class="flex items-center bg-gray-50 rounded-xl p-3">
                            <span class="text-sm text-gray-600">Set Menu</span>
                          </div>
                        @else
                          <form action="{{ route('cart.update', str_replace('%2F', '-', urlencode($id))) }}" method="POST" class="flex items-center bg-gray-50 rounded-xl p-1">
                            @csrf
                            @method('PATCH')
                            <button
                              type="button"
                              onclick="this.form.quantity.value = Math.max(1, parseInt(this.form.quantity.value || 1) - 1); this.form.submit();"
                              class="w-10 h-10 flex items-center justify-center rounded-lg bg-white hover:bg-gray-100 text-gray-600 hover:text-gray-800 transition-all duration-200 shadow-sm {{ $item['quantity'] <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                              {{ $item['quantity'] <= 1 ? 'disabled' : '' }}
                            >
                              <i class="bi bi-dash"></i>
                            </button>

                            <input
                              type="number"
                              name="quantity"
                              value="{{ $item['quantity'] }}"
                              min="1"
                              class="px-2 py-1 w-12 text-center text-lg font-semibold text-gray-800 bg-transparent border-0 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] rounded"
                              onchange="this.form.submit()"
                            />

                            <button
                              type="button"
                              onclick="this.form.quantity.value = (parseInt(this.form.quantity.value || 1) + 1); this.form.submit();"
                              class="w-10 h-10 flex items-center justify-center rounded-lg bg-white hover:bg-gray-100 text-gray-600 hover:text-gray-800 transition-all duration-200 shadow-sm"
                            >
                              <i class="bi bi-plus"></i>
                            </button>
                          </form>
                        @endif

                        <!-- Subtotal & Remove -->
                        <div class="text-right">
                          <div class="text-2xl font-bold text-[#8f5d54] mb-2">
                            ₱{{ number_format($item['price'] * $item['quantity'], 2) }}
                          </div>
                          <form action="{{ route('cart.remove', str_replace('%2F', '-', urlencode($id))) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                              type="submit"
                              class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors duration-200 flex items-center"
                              onclick="return confirm('Remove this item from cart?')"
                            >
                              <i class="bi bi-trash mr-1"></i>
                              Remove
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center" style="">
                <i class="bi bi-calculator mr-2 text-[#8f5d54]"></i>
                Order Summary
              </h3>

              <div class="space-y-4 mb-6">
                <div class="flex justify-between text-gray-600">
                  <span>Items ({{ $cartCount }})</span>
                  <span>₱{{ number_format($cartTotal, 2) }}</span>
                </div>
                @if(!empty($cartReference))
                <div class="flex justify-between text-gray-500 text-sm mt-2">
                  <span>Reference</span>
                  <span class="font-medium">#{{ $cartReference }}</span>
                </div>
                @endif
                <div class="flex justify-between text-gray-600">
                  <span>Service Fee</span>
                  <span>₱0.00</span>
                </div>
                <div class="border-t border-gray-200 pt-4">
                  <div class="flex justify-between text-2xl font-bold text-[#8f5d54]">
                    <span>Total</span>
                    <span>₱{{ number_format($cartTotal, 2) }}</span>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <a
                  href="{{ route('checkout.form') }}"
                  class="w-full bg-[#8f5d54] text-white py-4 px-6 rounded-xl font-semibold hover:bg-[#7a4a42] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center"
                >
                  <i class="bi bi-credit-card mr-2"></i>
                  Proceed to Checkout
                </a>

                <a
                  href="{{ url('/activities') }}"
                  class="w-full bg-gray-100 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-200 transition-all duration-300 flex items-center justify-center"
                >
                  <i class="bi bi-arrow-left mr-2"></i>
                  Continue Shopping
                </a>
              </div>

              <!-- Security Badges -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                  <div class="flex items-center">
                    <i class="bi bi-shield-check text-green-500 mr-1"></i>
                    <span>Secure</span>
                  </div>
                  <div class="flex items-center">
                    <i class="bi bi-house-heart text-blue-500 mr-1"></i>
                    <span>Farm Booking</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Activities Suggestion -->
        <div class="mt-12 bg-white rounded-2xl shadow-xl p-8">
          <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center" style="">
            <i class="bi bi-lightbulb text-[#8f5d54] mr-2"></i>
            You might also like
          </h3>
          <div class="grid md:grid-cols-3 gap-6">
            <a href="{{ url('/daytour') }}" class="group">
              <div class="bg-linear-to-br from-blue-50 to-blue-100 p-6 rounded-xl hover:shadow-lg transition-all duration-300 transform group-hover:-translate-y-1">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-4">
                  <i class="bi bi-sun text-white text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Day Tours</h4>
                <p class="text-gray-600 text-sm">Explore Calaia with guided day tours</p>
              </div>
            </a>

            <a href="{{ url('/staycation') }}" class="group">
              <div class="bg-linear-to-br from-green-50 to-green-100 p-6 rounded-xl hover:shadow-lg transition-all duration-300 transform group-hover:-translate-y-1">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mb-4">
                  <i class="bi bi-house-heart text-white text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Staycations</h4>
                <p class="text-gray-600 text-sm">Relax in our beautiful accommodations</p>
              </div>
            </a>

            <a href="{{ url('/dining') }}" class="group">
              <div class="bg-linear-to-br from-orange-50 to-orange-100 p-6 rounded-xl hover:shadow-lg transition-all duration-300 transform group-hover:-translate-y-1">
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center mb-4">
                  <i class="bi bi-cup-hot text-white text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Food & Dining</h4>
                <p class="text-gray-600 text-sm">Savor local delicacies and cuisine</p>
              </div>
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

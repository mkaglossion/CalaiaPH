<!doctype html>
<html lang="en">
  <head>
    @include('layouts.partials.favicon')
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

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .flatpickr-day.flatpickr-disabled {
            opacity: 0.35;
        }

        .flatpickr-day.today {
            border-color: #8f5d54;
        }

        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #8f5d54;
        }

        .flatpickr-day.inRange {
            background: rgba(143, 93, 84, 0.12);
        }

        .flatpickr-day.weekend {
            background-color: #8f5d54;
            border-color: #8f5d54;
            color: #ffffff;
            border-radius: 9999px;
        }
    </style>
  </head>
  <body class="bg-[#f5efe7]">
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('success') }}
        </div>

        <!-- Related Activities Suggestion -->
        <div class="mt-12 bg-white rounded-2xl shadow-xl p-8">
          <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center" style="font-family: 'Gotham Book', sans-serif;">
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

    <!-- Logo -->
    <div class="bg-linear-to-r from-white via-gray-50 to-white border-b border-gray-100 shadow-sm relative">
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
        <a href="{{ route('cart.index') }}" class="group transition-transform duration-300 hover:scale-105 absolute right-4 top-1/2 transform -translate-y-1/2">
          <i class="bi bi-bag text-2xl text-[#8f5d54] group-hover:text-[#7a4a42] transition-colors duration-300"></i>
        </a>
      </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border-b border-gray-100">
      <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-center">
          <i class="bi bi-credit-card text-2xl text-[#8f5d54] mr-3"></i>
          <h1 class="text-2xl font-semibold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">Checkout</h1>
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
            <div class="max-w-md mx-auto text-center py-16">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-cart-x text-3xl text-gray-400"></i>
                </div>
                <h2 class="text-xl font-medium text-gray-700 mb-3" style="font-family: 'Gotham Book', sans-serif;">Your cart is empty</h2>
                <p class="text-gray-500 mb-8">Add some activities before checking out!</p>
                <a href="{{ url('/activities') }}" class="inline-flex items-center bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#7a4a42] transition-colors">
                    Browse Activities
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Checkout Form -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
                        <i class="bi bi-person-lines-fill mr-2 text-[#8f5d54]"></i>
                        Contact & Delivery Information
                    </h2>
                    <form id="checkout-form" action="{{ route('checkout.submit') }}" method="POST">
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
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                                <input type="text" id="booking_date" name="booking_date" required
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

                    </form>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
                        <i class="bi bi-calculator mr-2 text-[#8f5d54]"></i>
                        Order Summary
                    </h2>
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
                        <button type="submit" form="checkout-form" class="w-full bg-[#8f5d54] text-white py-4 px-6 rounded-xl font-semibold hover:bg-[#7a4a42] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center mt-6">
                            <i class="bi bi-check-circle mr-2"></i>
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <x-footer />

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function markWeekends(dayElem) {
            if (!dayElem || !dayElem.dateObj) return;
            const date = dayElem.dateObj;
            const day = date.getDay();
            if (day === 0 || day === 6) {
                dayElem.classList.add('weekend');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#booking_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                disableMobile: true,
                onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
                    markWeekends(dayElem);
                }
            });
        });
    </script>

    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
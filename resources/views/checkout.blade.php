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
    @vite(['resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
          <h1 class="text-2xl font-semibold text-[#8f5d54]" style="">Checkout</h1>
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
                <h2 class="text-xl font-medium text-gray-700 mb-3" style="">Your cart is empty</h2>
                <p class="text-gray-500 mb-8">Add some products before checking out!</p>
                <a href="{{ url('/shop') }}" class="inline-flex items-center bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#7a4a42] transition-colors">
                    Browse Shop
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <!-- Checkout Form -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center" style="">
                        <i class="bi bi-person-lines-fill mr-2 text-[#8f5d54]"></i>
                        Contact Information
                    </h2>
                    <form id="checkout-form" action="{{ route('checkout.submit') }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" required autocomplete="name"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" required autocomplete="email"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required autocomplete="tel"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <!-- Delivery/Pickup Option -->
                            <div class="mt-4">
                                <p class="block text-sm font-medium text-gray-700 mb-3">Delivery Option</p>
                                <div class="space-y-2">
                                    <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-[#8f5d54] transition-colors">
                                        <input type="radio" name="delivery_option" value="pickup" class="w-4 h-4 text-[#8f5d54]" checked onchange="toggleAddressField()">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Pickup at Farm</span>
                                            <p class="text-xs text-gray-500">Collect your order at CalaiaPH farm</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-[#8f5d54] transition-colors">
                                        <input type="radio" name="delivery_option" value="delivery" class="w-4 h-4 text-[#8f5d54]" onchange="toggleAddressField()">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Home Delivery</span>
                                            <p class="text-xs text-gray-500">We'll deliver to your address (additional fee may apply)</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Address Field (shown when delivery is selected) -->
                            <div id="address-field" class="hidden">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" rows="3" placeholder="Enter your complete delivery address (street, barangay, city, province)..." autocomplete="street-address"
                                          class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none resize-none"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Please provide complete address including landmarks if applicable.</p>
                            </div>

                            <div>
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Pickup/Delivery Date</label>
                                <input type="text" id="booking_date" name="booking_date" required autocomplete="off"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <!-- Special Requests -->
                            <div class="mt-4">
                                <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-1">
                                    Special Requests (Optional)
                                </label>
                                <textarea
                                    id="special_requests"
                                    name="special_requests"
                                    rows="3"
                                    class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none resize-none"
                                    placeholder="Any special instructions for your order..."
                                ></textarea>
                            </div>

                            <!-- Payment Method Section -->
                            <div class="mt-8 pt-6 border-t">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Payment Method</h3>
                                <p class="text-sm text-gray-600 mb-4">Secure payment via PayMongo. Choose your preferred payment method including card, GCash, PayMaya, or QR PH.</p>
                                
                                <div class="space-y-3">
                                    <!-- Credit/Debit Card Option -->
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#8f5d54] transition-colors" onclick="document.getElementById('payment_method_card').checked = true;">
                                        <input type="radio" id="payment_method_card" name="payment_method" value="card" required class="w-5 h-5 text-[#8f5d54] shrink-0" checked>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-credit-card text-[#8f5d54] text-lg"></i>
                                                <span class="font-semibold text-gray-900">Credit/Debit Card</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1 ml-6">Secure payment via PayMongo</p>
                                        </div>
                                    </label>

                                    <!-- Scan QR Code Option -->
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#8f5d54] transition-colors" onclick="document.getElementById('payment_method_qr').checked = true;">
                                        <input type="radio" id="payment_method_qr" name="payment_method" value="qr_code" required class="w-5 h-5 text-[#8f5d54] shrink-0">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-qr-code text-[#8f5d54] text-lg"></i>
                                                <span class="font-semibold text-gray-900">Scan QR Code</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1 ml-6">Pay using GCash, PayMaya, QR PH, or other QR-enabled wallets</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-2xl shadow-xl p-6 h-fit self-start">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center" style="">
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
                    
                    <!-- Price Breakdown -->
                    <div class="border-t mt-4 pt-4 space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>₱{{ number_format($cartTotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-gray-600">
                            <span>VAT (12%)</span>
                            <span>₱{{ number_format($vatAmount, 2) }}</span>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-xl font-bold text-[#8f5d54]">Total</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-[#8f5d54]">₱{{ number_format($finalTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs text-blue-700 flex items-center">
                            <i class="bi bi-info-circle mr-2"></i>
                            You'll be redirected to PayMongo to complete your payment securely
                        </p>
                    </div>
                    <button type="submit" form="checkout-form" class="w-full bg-[#8f5d54] text-white py-4 px-6 rounded-xl font-semibold hover:bg-[#7a4a42] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center mt-6">
                        <i class="bi bi-credit-card mr-2"></i>
                        Proceed to PayMongo Payment
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <x-footer />

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function toggleAddressField() {
            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked').value;
            const addressField = document.getElementById('address-field');
            const addressInput = document.getElementById('address');
            
            if (deliveryOption === 'delivery') {
                addressField.classList.remove('hidden');
                addressInput.setAttribute('required', 'required');
            } else {
                addressField.classList.add('hidden');
                addressInput.removeAttribute('required');
                addressInput.value = '';
            }
        }

        // Validate form before submission
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            // Check if date is selected
            const bookingDate = document.getElementById('booking_date').value;
            if (!bookingDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Date Required',
                    text: 'Please select a pickup/delivery date before proceeding.',
                    confirmButtonColor: '#8f5d54'
                });
                document.getElementById('booking_date').focus();
                return false;
            }
        });

        function markWeekends(dayElem) {
            if (!dayElem || !dayElem.dateObj) return;
            const date = dayElem.dateObj;
            const day = date.getDay();
            if (day === 0 || day === 6) {
                dayElem.classList.add('weekend');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize address field visibility
            toggleAddressField();
            
            flatpickr('#booking_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                disableMobile: true,
                onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
                    markWeekends(dayElem);
                },
                onReady: function(selectedDates, dateStr, instance) {
                    const ensureFlatpickrAttrs = () => {
                        document.querySelectorAll('input.numInput.cur-year:not([name])').forEach((input, idx) => {
                            if (!input.id) input.id = `fp-year-input-${Date.now()}-${idx}`;
                            if (!input.name) input.name = 'flatpickr_year';
                        });
                        document.querySelectorAll('input.numInput.cur-month:not([name])').forEach((input, idx) => {
                            if (!input.id) input.id = `fp-month-input-${Date.now()}-${idx}`;
                            if (!input.name) input.name = 'flatpickr_month';
                        });
                        document.querySelectorAll('select.flatpickr-monthDropdown-months:not([name])').forEach((select, idx) => {
                            if (!select.id) select.id = `fp-month-select-${Date.now()}-${idx}`;
                            if (!select.name) select.name = 'flatpickr_month_select';
                        });
                    };
                    ensureFlatpickrAttrs();
                    setTimeout(ensureFlatpickrAttrs, 50);
                    setTimeout(ensureFlatpickrAttrs, 200);
                    const observer = new MutationObserver(() => ensureFlatpickrAttrs());
                    const calendarContainer = instance.calendarContainer;
                    if (calendarContainer) observer.observe(calendarContainer, { childList: true, subtree: true });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>

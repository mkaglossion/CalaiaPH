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

                      <!-- Discount Selection -->
                      <div class="mt-4 sm:mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <h4 class="text-sm font-semibold text-amber-800 mb-3 flex items-center">
                          <i class="bi bi-person-hearts mr-2"></i>
                          Special Discount
                        </h4>
                        <div class="space-y-2">
                          <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-amber-400 transition-colors {{ $discountType === 'none' ? 'ring-2 ring-amber-500' : '' }}">
                            <input type="radio" name="discount_type" value="none" class="w-4 h-4 text-amber-600" 
                                   {{ $discountType === 'none' ? 'checked' : '' }} onchange="toggleIdCapture()">
                            <span class="ml-3 text-sm text-gray-700">No Discount</span>
                          </label>
                          <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-amber-400 transition-colors {{ $discountType === 'senior' ? 'ring-2 ring-amber-500' : '' }}">
                            <input type="radio" name="discount_type" value="senior" class="w-4 h-4 text-amber-600"
                                   {{ $discountType === 'senior' ? 'checked' : '' }} onchange="toggleIdCapture()">
                            <div class="ml-3">
                              <span class="text-sm font-medium text-gray-700">Senior Citizen (20% off + VAT Exempt)</span>
                              <p class="text-xs text-gray-500">Valid Senior ID required at pickup. Subject to verification.</p>
                            </div>
                          </label>
                          <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-amber-400 transition-colors {{ $discountType === 'pwd' ? 'ring-2 ring-amber-500' : '' }}">
                            <input type="radio" name="discount_type" value="pwd" class="w-4 h-4 text-amber-600"
                                   {{ $discountType === 'pwd' ? 'checked' : '' }} onchange="toggleIdCapture()">
                            <div class="ml-3">
                              <span class="text-sm font-medium text-gray-700">PWD (20% off + VAT Exempt)</span>
                              <p class="text-xs text-gray-500">Valid PWD ID required at pickup. Subject to verification.</p>
                            </div>
                          </label>
                        </div>
                        <div class="mt-3 p-2 bg-white rounded border border-amber-200">
                          <p class="text-xs text-amber-800">
                            <i class="bi bi-shield-check mr-1"></i>
                            <strong>Verification Process:</strong> Your discount will be verified by our admin team before pickup. You'll receive a confirmation email once verified. Please bring your physical ID for final verification at pickup.
                          </p>
                        </div>
                      </div>


                            <!-- ID Capture Section (shown when discount is selected) -->
                            <div id="id-capture-section" class="mt-6 {{ $discountType === 'none' || !$discountType ? 'hidden' : '' }}">
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-[#8f5d54] bg-opacity-10 rounded-full flex items-center justify-center">
                                            <i class="bi bi-person-badge text-[#8f5d54] text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800">ID Verification</h3>
                                            <p class="text-sm text-gray-500">Required for discount validation at pickup</p>
                                        </div>
                                    </div>

                                    {{-- ID Format Notice --}}
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                                        <div class="flex items-start gap-2">
                                            <i class="bi bi-info-circle text-amber-600 mt-0.5"></i>
                                            <div class="text-sm text-amber-800">
                                                <p class="font-medium mb-1">ID Format Requirements:</p>
                                                <ul class="list-disc list-inside space-y-1 text-xs">
                                                    <li>Senior Citizen: <strong>SC-XXXXXX</strong> (e.g., SC-123456)</li>
                                                    <li>PWD: <strong>PWD-XXXXXX</strong> (e.g., PWD-123456)</li>
                                                </ul>
                                                <p class="mt-2 text-xs font-semibold text-amber-900">
                              <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                              Important: Your 20% discount is subject to ID verification. Please present your valid ID at pickup. Invalid or missing IDs will require payment of the discount difference.
                            </p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ID Number Input --}}
                                    <div class="mb-4">
                                        <label for="id-number" class="block text-sm font-medium text-gray-700 mb-2">
                                            ID Number <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            id="id-number" 
                                            name="id_number"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none transition-colors"
                                            placeholder="SC-123456 or PWD-123456"
                                            pattern="(SC|PWD)-[0-9]{6,}"
                                            oninput="validateIdFormat(this)"
                                        >
                                        <p id="id-format-error" class="text-xs text-red-600 mt-1 hidden">
                                            <i class="bi bi-exclamation-circle mr-1"></i>
                                            Invalid format. Use SC-XXXXXX or PWD-XXXXXX
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">Enter the ID number exactly as shown on your card</p>
                                    </div>

                                    {{-- Capture/Upload Options --}}
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        {{-- Camera Capture Button --}}
                                        <button 
                                            type="button"
                                            id="btn-capture"
                                            class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-[#8f5d54] rounded-lg hover:bg-[#8f5d54] hover:bg-opacity-5 transition-colors group"
                                        >
                                            <i class="bi bi-camera text-2xl text-[#8f5d54] group-hover:scale-110 transition-transform mb-2"></i>
                                            <span class="text-sm font-medium text-[#8f5d54]">Capture with Camera</span>
                                            <span class="text-xs text-gray-500 mt-1">Take a photo</span>
                                        </button>

                                        {{-- File Upload Button --}}
                                        <button 
                                            type="button"
                                            id="btn-upload"
                                            class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-[#8f5d54] hover:bg-[#8f5d54] hover:bg-opacity-5 transition-colors group"
                                        >
                                            <i class="bi bi-upload text-2xl text-gray-400 group-hover:text-[#8f5d54] transition-colors mb-2"></i>
                                            <span class="text-sm font-medium text-gray-600 group-hover:text-[#8f5d54]">Upload File</span>
                                            <span class="text-xs text-gray-500 mt-1">Select from device</span>
                                        </button>
                                    </div>

                                    {{-- Hidden File Inputs --}}
                                    <input 
                                        type="file" 
                                        id="id-file-input" 
                                        accept="image/jpeg,image/png,image/jpg"
                                        class="hidden"
                                    >
                                    <input 
                                        type="file" 
                                        id="id-camera-input" 
                                        accept="image/*" 
                                        capture="environment"
                                        class="hidden"
                                    >
                                    
                                    {{-- Hidden input to store ID image data for form submission --}}
                                    <input 
                                        type="hidden" 
                                        id="id_image_data" 
                                        name="id_image_data"
                                        value=""
                                    >

                                    {{-- Image Preview --}}
                                    <div id="preview-container" class="hidden mb-4">
                                        <p class="block text-sm font-medium text-gray-700 mb-2">Preview</p>
                                        <div class="relative">
                                            <img 
                                                id="id-preview" 
                                                src="" 
                                                alt="ID Preview" 
                                                class="w-full max-h-64 object-contain rounded-lg border border-gray-200"
                                            >
                                            <button 
                                                type="button"
                                                id="btn-remove-image"
                                                class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors shadow-md"
                                                title="Remove image"
                                            >
                                                <i class="bi bi-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Instructions --}}
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                        <div class="flex items-start gap-2">
                                            <i class="bi bi-info-circle text-blue-500 mt-0.5"></i>
                                            <div class="text-sm text-blue-700">
                                                <p class="font-medium mb-1">Tips for best results:</p>
                                                <ul class="list-disc list-inside space-y-1 text-xs">
                                                    <li>Ensure good lighting</li>
                                                    <li>Keep the ID flat and clearly visible</li>
                                                    <li>All corners of the ID should be visible</li>
                                                    <li>Text should be readable</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Status Message --}}
                                    <div id="id-status-message" class="hidden"></div>
                                </div>
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

                        @if($discountType !== 'none')
                        <div class="flex justify-between text-green-600 font-medium">
                            <span class="flex items-center">
                                <i class="bi bi-tag-fill mr-1"></i>
                                {{ $discountType === 'senior' ? 'Senior Citizen' : 'PWD' }} Discount (20%)
                            </span>
                            <span>-₱{{ number_format($discountAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>After Discount</span>
                            <span>₱{{ number_format($discountedTotal, 2) }}</span>
                        </div>
                        @endif

                        @if($isVatExempt)
                        <div class="flex justify-between text-blue-600 text-sm">
                            <span class="flex items-center">
                                <i class="bi bi-shield-check mr-1"></i>
                                VAT Exempt (12%)
                            </span>
                            <span>₱0.00</span>
                        </div>
                        @else
                        <div class="flex justify-between text-gray-600">
                            <span>VAT (12%)</span>
                            <span>₱{{ number_format($vatAmount, 2) }}</span>
                        </div>
                        @endif

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-xl font-bold text-[#8f5d54]">Total</span>
                                    @if($discountType !== 'none')
                                    <p class="text-xs text-green-600 font-medium">
                                        <i class="bi bi-check-circle mr-1"></i>
                                        You saved ₱{{ number_format($discountAmount, 2) }}
                                    </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($discountType !== 'none')
                                    <span class="text-sm text-gray-400 line-through block">₱{{ number_format($cartTotal + ($isVatExempt ? 0 : $vatAmount), 2) }}</span>
                                    @endif
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

        function toggleIdCapture() {
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            const idCaptureSection = document.getElementById('id-capture-section');
            const idNumberInput = document.getElementById('id-number');
            
            if (discountType === 'senior' || discountType === 'pwd') {
                idCaptureSection.classList.remove('hidden');
                idNumberInput.setAttribute('required', 'required');
                // Update placeholder based on discount type
                if (discountType === 'senior') {
                    idNumberInput.placeholder = 'SC-123456';
                } else {
                    idNumberInput.placeholder = 'PWD-123456';
                }
            } else {
                idCaptureSection.classList.add('hidden');
                idNumberInput.removeAttribute('required');
                // Reset ID capture when discount is deselected
                resetIdCapture();
            }
        }

        function validateIdFormat(input) {
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            const value = input.value.toUpperCase();
            const errorEl = document.getElementById('id-format-error');
            
            // Auto-format: add prefix if missing
            if (value && !value.startsWith('SC-') && !value.startsWith('PWD-')) {
                if (discountType === 'senior' && /^\d/.test(value)) {
                    input.value = 'SC-' + value;
                } else if (discountType === 'pwd' && /^\d/.test(value)) {
                    input.value = 'PWD-' + value;
                }
            }
            
            // Validate format
            const seniorPattern = /^SC-\d{6,}$/;
            const pwdPattern = /^PWD-\d{6,}$/i;
            
            let isValid = false;
            if (discountType === 'senior') {
                isValid = seniorPattern.test(input.value);
            } else if (discountType === 'pwd') {
                isValid = pwdPattern.test(input.value);
            }
            
            if (input.value && !isValid) {
                errorEl.classList.remove('hidden');
                input.classList.add('border-red-500');
                input.classList.remove('border-gray-300', 'focus:ring-[#8f5d54]');
            } else {
                errorEl.classList.add('hidden');
                input.classList.remove('border-red-500');
                input.classList.add('border-gray-300', 'focus:ring-[#8f5d54]');
            }
            
            return isValid;
        }

        function resetIdCapture() {
            document.getElementById('id-number').value = '';
            document.getElementById('id-preview').src = '';
            document.getElementById('preview-container').classList.add('hidden');
            document.getElementById('id-file-input').value = '';
            document.getElementById('id-camera-input').value = '';
            document.getElementById('id-format-error').classList.add('hidden');
            const idInput = document.getElementById('id-number');
            idInput.classList.remove('border-red-500');
            idInput.classList.add('border-gray-300');
            capturedImage = null;
            // Clear the hidden input
            document.getElementById('id_image_data').value = '';
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

            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            
            if (discountType === 'senior' || discountType === 'pwd') {
                const idNumber = document.getElementById('id-number').value;
                const idImageData = document.getElementById('id_image_data').value;
                
                // Validate ID format
                const seniorPattern = /^SC-\d{6,}$/;
                const pwdPattern = /^PWD-\d{6,}$/i;
                let isValidFormat = false;
                
                if (discountType === 'senior') {
                    isValidFormat = seniorPattern.test(idNumber);
                } else {
                    isValidFormat = pwdPattern.test(idNumber);
                }
                
                if (!isValidFormat) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid ID Format',
                        html: 'Please enter a valid ID number format:<br><br>' +
                              '<strong>Senior:</strong> SC-123456<br>' +
                              '<strong>PWD:</strong> PWD-123456',
                        confirmButtonColor: '#8f5d54'
                    });
                    document.getElementById('id-number').focus();
                    return false;
                }
                
                // Validate ID image is uploaded - check the hidden input value
                if (!idImageData || idImageData === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'ID Image Required',
                        text: 'Please upload or capture your ID image to claim the discount.',
                        confirmButtonColor: '#8f5d54'
                    });
                    return false;
                }
                
                // Show confirmation - prevent default and handle async
                e.preventDefault();
                
                const discountLabel = discountType === 'senior' ? 'Senior Citizen' : 'PWD';
                
                Swal.fire({
                    icon: 'info',
                    title: 'Discount Verification Notice',
                    html: `<div class="text-left">
                        <p class="mb-2">You have selected <strong>${discountLabel}</strong> discount.</p>
                        <p class="mb-3">ID Number: <strong>${idNumber}</strong></p>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm">
                            <p class="font-semibold text-amber-800 mb-2">⚠️ Important:</p>
                            <ul class="list-disc list-inside text-amber-700 space-y-1">
                                <li>Your ID will be verified at pickup/arrival</li>
                                <li>Invalid or fake IDs will require full payment</li>
                                <li>Discount is subject to admin approval</li>
                            </ul>
                        </div>
                    </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, proceed',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#8f5d54',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.submit();
                    }
                });
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

        // ID Capture functionality
        let capturedImage = null;

        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        function openCamera() {
            if (isMobileDevice()) {
                document.getElementById('id-camera-input').click();
            } else {
                // Desktop - use getUserMedia
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    startCameraStream();
                } else {
                    document.getElementById('id-file-input').click();
                }
            }
        }

        async function startCameraStream() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                showCameraModal(stream);
            } catch (err) {
                console.error('Camera access denied:', err);
                document.getElementById('id-file-input').click();
            }
        }

        function showCameraModal(stream) {
            let modal = document.getElementById('camera-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'camera-modal';
                modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg p-4 max-w-lg w-full mx-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Capture ID</h3>
                            <button id="close-camera" class="text-gray-500 hover:text-gray-700">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="relative">
                            <video id="camera-video" class="w-full rounded-lg" autoplay playsinline></video>
                            <canvas id="camera-canvas" class="hidden"></canvas>
                        </div>
                        <div class="mt-4 flex justify-center">
                            <button id="btn-snap" class="bg-[#8f5d54] text-white px-6 py-2 rounded-lg hover:bg-[#7a4a42]">
                                <i class="bi bi-camera"></i> Take Photo
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const video = document.getElementById('camera-video');
            const canvas = document.getElementById('camera-canvas');
            
            video.srcObject = stream;
            video.play();

            document.getElementById('btn-snap').onclick = () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                
                capturedImage = canvas.toDataURL('image/jpeg', 0.9);
                showPreview(capturedImage);
                
                stream.getTracks().forEach(track => track.stop());
                modal.remove();
            };

            document.getElementById('close-camera').onclick = () => {
                stream.getTracks().forEach(track => track.stop());
                modal.remove();
            };
        }

        function showPreview(imageSrc) {
            document.getElementById('id-preview').src = imageSrc;
            document.getElementById('preview-container').classList.remove('hidden');
            // Store image data in hidden input for form submission
            document.getElementById('id_image_data').value = imageSrc;
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showIdStatus('error', 'Please select an image file.');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showIdStatus('error', 'File size must be less than 2MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                capturedImage = e.target.result;
                showPreview(capturedImage);
            };
            reader.readAsDataURL(file);
        }

        function showIdStatus(type, message) {
            const statusEl = document.getElementById('id-status-message');
            statusEl.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
            
            if (type === 'success') {
                statusEl.classList.add('bg-green-100', 'text-green-700', 'p-3', 'rounded-lg', 'text-sm');
            } else {
                statusEl.classList.add('bg-red-100', 'text-red-700', 'p-3', 'rounded-lg', 'text-sm');
            }
            
            statusEl.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize address field visibility
            toggleAddressField();
            
            // Initialize ID capture visibility
            toggleIdCapture();
            
            // Bind ID capture events
            document.getElementById('btn-capture').addEventListener('click', openCamera);
            document.getElementById('btn-upload').addEventListener('click', () => {
                document.getElementById('id-file-input').click();
            });
            document.getElementById('id-file-input').addEventListener('change', handleFileSelect);
            document.getElementById('id-camera-input').addEventListener('change', handleFileSelect);
            document.getElementById('btn-remove-image').addEventListener('click', resetIdCapture);
            
            flatpickr('#booking_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                disableMobile: true,
                onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
                    markWeekends(dayElem);
                },
                onReady: function(selectedDates, dateStr, instance) {
                    // Add id to month dropdown and year input for accessibility
                    const monthSelect = instance.calendarContainer.querySelector('.flatpickr-monthDropdown-months');
                    if (monthSelect) monthSelect.id = 'flatpickr-month';
                    const yearInput = instance.calendarContainer.querySelector('.numInput.cur-year');
                    if (yearInput) yearInput.id = 'flatpickr-year';
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>

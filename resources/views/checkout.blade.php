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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">


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

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151;
            line-height: 40px;
            padding-left: 12px;
            padding-right: 28px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }

        .select2-dropdown {
            border-color: #d1d5db;
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
  </head>
  <body class="bg-[#f5efe7]">


    <!-- Logo -->
    @php
      $checkoutLogoSource = $systemSettings['logo_url'] ?? null;
      $checkoutLogoUrl = $checkoutLogoSource
        ? \App\Helpers\CloudinaryHelper::optimize($checkoutLogoSource, 420, false, false, 'good')
        : null;
    @endphp
    <div class="relative bg-brand border-b border-white/10 shadow-sm">
      <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-center">
          <a href="{{ route('home') }}" class="group transition-transform duration-300 hover:scale-105">
            @if($checkoutLogoUrl)
              <img
                src="{{ $checkoutLogoUrl }}"
                alt="{{ $systemSettings['app_name'] ?? 'CalaiaPH' }}"
                class="h-16 w-auto object-contain drop-shadow-sm transition-all duration-300 group-hover:drop-shadow-md md:h-20"
              >
            @else
              <span class="text-xl font-semibold tracking-[0.3em] text-white uppercase">
                {{ $systemSettings['app_name'] ?? 'CalaiaPH' }}
              </span>
            @endif
          </a>
        </div>
        <a href="{{ route('cart.index') }}" class="group transition-transform duration-300 hover:scale-105 absolute right-4 top-1/2 transform -translate-y-1/2">
          <i class="bi bi-bag text-2xl text-white transition-colors duration-300 group-hover:text-white/80"></i>
        </a>
      </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border-b border-gray-100">
      <div class="container mx-auto px-4 py-6 md:py-8">
        <div class="flex items-center justify-center">
          <i class="bi bi-credit-card text-2xl text-[#8f5d54] mr-3"></i>
          <h1 class="text-xl md:text-2xl font-semibold text-[#8f5d54]" style="">Checkout</h1>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div class="container mx-auto px-4 py-4 md:py-6">
      <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-[#d9c8bf] rounded-lg text-sm font-medium text-[#8f5d54] no-underline bg-white hover:bg-[#f8f2ef] transition shadow-xs">
        <i class="bi bi-arrow-left"></i>
        Back
      </a>
    </div>

    <div class="container mx-auto px-4 pb-16">
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 md:gap-8 items-start">
                <!-- Checkout Form -->
                <div class="bg-white rounded-2xl shadow-xl p-4 md:p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center" style="">
                        <i class="bi bi-person-lines-fill mr-2 text-[#8f5d54]"></i>
                        Contact Information
                    </h2>
                    <form id="checkout-form" action="{{ route('checkout.submit') }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                                       class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
                            </div>

                            <!-- Delivery/Pickup Option -->
                            <div class="mt-4">
                                <p class="block text-sm font-medium text-gray-700 mb-3">Delivery Option</p>
                                <div class="space-y-2">
                                    <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-[#8f5d54] transition-colors">
                                        <input type="radio" name="delivery_option" value="pickup" class="w-4 h-4 text-[#8f5d54]" {{ old('delivery_option', 'pickup') === 'pickup' ? 'checked' : '' }} onchange="toggleAddressField()">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Pickup at Farm</span>
                                            <p class="text-xs text-gray-500">Collect your order at CalaiaPH farm</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-[#8f5d54] transition-colors">
                                        <input type="radio" name="delivery_option" value="delivery" class="w-4 h-4 text-[#8f5d54]" {{ old('delivery_option') === 'delivery' ? 'checked' : '' }} onchange="toggleAddressField()">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Home Delivery</span>
                                            <p class="text-xs text-gray-500">We'll deliver to your address (additional fee may apply)</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <x-address.philippine-address-form
                                :fixed-region-id="$fixedRegionId"
                                :fixed-province-id="$fixedProvinceId"
                                :fixed-city-id="$fixedCityId"
                                :fixed-location-label="$fixedLocationLabel"
                                :barangays="$barangays"
                            />

                            <div>
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Pickup/Delivery Date</label>
                                <input type="text" id="booking_date" name="booking_date" value="{{ old('booking_date') }}" required autocomplete="off"
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
                                >{{ old('special_requests') }}</textarea>
                            </div>

                        </div>

                    </form>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-2xl shadow-xl p-4 md:p-6 h-fit self-start">
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

                        <div class="hidden text-gray-600" aria-hidden="true">
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
    <script>
        const barangaySelect = document.getElementById('barangay_id');
        const addressHiddenInput = document.getElementById('address');
        const fixedLocationLabel = {!! json_encode($fixedLocationLabel ?? 'Calaca, Batangas, CALABARZON, Philippines') !!};

        function composeDeliveryAddress() {
            const streetAddress = (document.getElementById('street_address')?.value || '').trim();
            const subdivision = (document.getElementById('subdivision')?.value || '').trim();
            const zipCode = (document.getElementById('zip_code')?.value || '').trim();
            const landmark = (document.getElementById('landmark')?.value || '').trim();

            const barangayText = barangaySelect?.selectedOptions?.[0]?.textContent?.trim() || '';

            const line1 = [streetAddress, subdivision].filter(Boolean).join(', ');
            const line2 = [barangayText, fixedLocationLabel].filter(Boolean).join(', ');
            const line3 = [zipCode ? `ZIP ${zipCode}` : '', landmark ? `Landmark: ${landmark}` : ''].filter(Boolean).join(', ');

            return [line1, line2, line3].filter(Boolean).join('\n');
        }

        function syncHiddenAddress() {
            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked')?.value;
            addressHiddenInput.value = deliveryOption === 'delivery' ? composeDeliveryAddress() : '';
        }

        function setAddressFieldRequirements(isRequired) {
            ['barangay_id', 'street_address', 'zip_code'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                if (isRequired) {
                    el.setAttribute('required', 'required');
                } else {
                    el.removeAttribute('required');
                }
            });
        }

        function initializeSelect2() {
            $('.address-select').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });
        }

        function bindAddressEvents() {
            $('#barangay_id').on('change', syncHiddenAddress);
            ['street_address', 'subdivision', 'zip_code', 'landmark'].forEach((id) => {
                document.getElementById(id)?.addEventListener('input', syncHiddenAddress);
            });
        }

        function toggleAddressField() {
            const checkedDeliveryOption = document.querySelector('input[name="delivery_option"]:checked');
            if (!checkedDeliveryOption) return;
            const deliveryOption = checkedDeliveryOption.value;
            const addressField = document.getElementById('address-field');
            if (!addressField) return;

            if (deliveryOption === 'delivery') {
                addressField.classList.remove('hidden');
                setAddressFieldRequirements(true);
            } else {
                addressField.classList.add('hidden');
                setAddressFieldRequirements(false);
            }

            syncHiddenAddress();
        }

        // Validate form before submission
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) checkoutForm.addEventListener('submit', function(e) {
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

            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked')?.value;
            if (deliveryOption === 'delivery') {
                const zipCode = (document.getElementById('zip_code')?.value || '').trim();
                if (!zipCode) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'ZIP Code Required',
                        text: 'Please provide ZIP code for delivery address.',
                        confirmButtonColor: '#8f5d54'
                    });
                    document.getElementById('zip_code')?.focus();
                    return false;
                }

                const barangayValue = barangaySelect?.value || '';
                if (!barangayValue) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Barangay Required',
                        text: 'Please select a barangay within Calaca service area.',
                        confirmButtonColor: '#8f5d54'
                    });
                    return false;
                }

                syncHiddenAddress();
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
            if (checkoutForm) {
                initializeSelect2();
                bindAddressEvents();
                toggleAddressField();
                syncHiddenAddress();
            }
            
            if (!document.getElementById('booking_date')) return;

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

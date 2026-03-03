<x-page-layout title="CalaiaPH - Day Visit Availability" active-header="daytour">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Progress Indicator --}}
    <div class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
                {{-- Step 1: Availability (current) --}}
                <div class="flex flex-col items-center text-[#8f5d54]">
                    <div class="w-8 h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-sm font-bold mb-1">
                        1
                    </div>
                    <span class="text-sm font-medium">Availability</span>
                </div>

                <div class="flex-1 h-px bg-gray-300"></div>

                {{-- Step 2: Activities --}}
                <div class="flex flex-col items-center text-gray-400">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold mb-1">
                        2
                    </div>
                    <span class="text-sm font-medium">Activities</span>
                </div>

                <div class="flex-1 h-px bg-gray-300"></div>

                {{-- Step 3: Payment --}}
                <div class="flex flex-col items-center text-gray-400">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold mb-1">
                        3
                    </div>
                    <span class="text-sm font-medium">Payment</span>
                </div>

                <div class="flex-1 h-px bg-gray-300"></div>

                {{-- Step 4: Confirmation --}}
                <div class="flex flex-col items-center text-gray-400">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold mb-1">
                        4
                    </div>
                    <span class="text-sm font-medium">Confirmation</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Availability Check --}}
    <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh] bg-[#f5efe7]">
        <div class="rounded-xl max-w- mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-8 sm:py-12 lg:py-16">

            {{-- Header --}}
            <div class="text-center mb-8 sm:mb-12">
                <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
                    CalaiaPH Day Visit
                </p>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#8f5d54]">
                    Check Availability
                </h1>
                <p class="mt-3 text-base sm:text-lg text-gray-500 max-w-3xl mx-auto">
                     Select a package that fits your group size and check available package.
                </p>
            </div>

            {{-- Main Grid Layout - Same as Staycation --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
                {{-- LEFT: Package Cards (2/3 width) --}}
                <div class="lg:col-span-2">
                    {{-- Availability Result --}}
                    <div id="availability-result" class="hidden">
                        {{-- Available Packages --}}
                        <div class="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-6 flex items-center">
                                <i class="bi bi-check-circle mr-2 text-green-600"></i>
                                Available Packages
                            </h3>

                            <div id="packages-container" class="grid gap-4 sm:gap-6 sm:grid-cols-2">
                                {{-- Packages will be loaded here --}}
                            </div>
                        </div>

                        {{-- Unavailable Message --}}
                        <div id="unavailable-message" class="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm hidden">
                            <div class="text-center">
                                <i class="bi bi-calendar-x text-6xl sm:text-7xl mb-4 block text-gray-300"></i>
                                <h3 class="text-xl sm:text-2xl font-semibold mb-2 text-gray-700">No Availability</h3>
                                <p class="text-gray-500 mb-4 text-base">There are no day visit packages available.</p>
                                <p class="text-gray-500 text-sm">Please check back later or contact us for assistance.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Booking Summary (1/3 width) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">

                        <h3 class="text-xl font-bold mb-6">Booking Summary</h3>

                        {{-- Visit Date --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-calendar-check mr-2 text-[#8f5d54]"></i> Visit Date
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Selected Date</span>
                                    <span id="summary-date" class="font-semibold">{{ date('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Guests --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-people mr-2 text-[#8f5d54]"></i> Guests
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Number of Guests</span>
                                    <span id="summary-guests" class="font-semibold">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Selected Package --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-box mr-2 text-[#8f5d54]"></i> Selected Package
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div id="summary-package" class="text-sm text-gray-600">—</div>
                            </div>
                        </div>

                        {{-- Pricing --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-currency-peso mr-2 text-[#8f5d54]"></i> Pricing
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Package Price per Person</span>
                                    <span id="summary-price-per-person" class="font-semibold">—</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Price</span>
                                    <span id="summary-total-price" class="font-semibold text-[#8f5d54] text-lg">—</span>
                                </div>
                                <div id="vat-display" class="flex justify-between items-center text-xs text-gray-500">
                                    <span>12% VAT:</span>
                                    <span id="summary-vat">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-info-circle mr-2 text-[#8f5d54]"></i> Status
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div id="summary-status" class="text-sm text-gray-600">
                                    Please select a package to view booking details
                                </div>
                            </div>
                        </div>

                        {{-- Continue to Activities Button --}}
                        <div class="mb-3">
                            <button
                                type="button"
                                id="summary-continue-btn"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gray-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-600/20 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-600 transition-all duration-300 transform hover:-translate-y-0.5"
                                disabled
                            >
                                <i class="bi bi-arrow-right-circle mr-2"></i>
                                <span>Continue to Activities</span>
                            </button>
                        </div>

                        {{-- Proceed to Payment Button --}}
                        <div>
                            <button
                                type="button"
                                id="proceed-to-payment-btn"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
                                disabled
                            >
                                <i class="bi bi-credit-card mr-2"></i>
                                <span>Proceed to Payment</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-page-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const summaryContinueBtn = document.getElementById('summary-continue-btn');
    const proceedToPaymentBtn = document.getElementById('proceed-to-payment-btn');
    const availabilityResult = document.getElementById('availability-result');
    const packagesContainer = document.getElementById('packages-container');
    const unavailableMessage = document.getElementById('unavailable-message');
    const guestsInput = document.getElementById('guests-input');
    const checkAvailabilityBtn = document.getElementById('check-availability-btn');

    let selectedPackage = null;
    let currentGuests = 1;

    // Load all packages on page load
    console.log('DOM loaded, calling loadPackages');
    loadAllPackages();




    // Continue to Activities button in Booking Summary
    summaryContinueBtn.addEventListener('click', function() {
        if (selectedPackage) {
            // Use the package's max capacity as the guest count
            const guests = selectedPackage.max_guests;
            
            // Store selected package in session and redirect to activities
            // Use price_exclusive (VAT-exclusive) to avoid double VAT calculation
            fetch('{{ route("dayvisit.storeAvailability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    guests: guests,
                    package: selectedPackage.id,
                    package_name: selectedPackage.name,
                    price: selectedPackage.price_exclusive || selectedPackage.price
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to activities
                    window.location.href = data.redirect;
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            });
        }
    });


    // Proceed to Payment button functionality
    proceedToPaymentBtn.addEventListener('click', function() {
        if (selectedPackage) {
            // Use the package's max capacity as the guest count
            const guests = selectedPackage.max_guests;
            
            // Store selected package in session and redirect to payment
            // Use price_exclusive (VAT-exclusive) to avoid double VAT calculation
            fetch('{{ route("dayvisit.storeAvailability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    guests: guests,
                    package: selectedPackage.id,
                    package_name: selectedPackage.name,
                    price: selectedPackage.price_exclusive || selectedPackage.price
                })
            })

            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to payment instead of activities
                    window.location.href = '{{ route("dayvisit.payment") }}';
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            });
        }
    });

    function loadAllPackages() {
        // Show loading state
        packagesContainer.innerHTML = '<div class="text-center py-8"><i class="bi bi-arrow-repeat animate-spin text-3xl text-[#8f5d54]"></i><p class="mt-2 text-gray-600">Loading packages...</p></div>';
        availabilityResult.classList.remove('hidden');

        fetch(`{{ route("api.dayvisit.availability") }}`)

            .then(response => response.json())
            .then(data => {
                if (data.available && data.packages.length > 0) {
                    displayPackages(data.packages);

                    unavailableMessage.classList.add('hidden');
                    updateBookingSummary();
                } else {
                    packagesContainer.innerHTML = '';
                    unavailableMessage.classList.remove('hidden');
                    // Update unavailable message with custom message from API
                    const unavailableDiv = unavailableMessage.querySelector('p.text-gray-500');
                    if (unavailableDiv && data.message) {
                        unavailableDiv.textContent = data.message;
                    }
                    summaryContinueBtn.disabled = true;
                    proceedToPaymentBtn.disabled = true;
                    selectedPackage = null;
                    updateBookingSummary();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load packages');
                unavailableMessage.classList.remove('hidden');
                packagesContainer.innerHTML = '';
                summaryContinueBtn.disabled = true;
                proceedToPaymentBtn.disabled = true;
                selectedPackage = null;
                updateBookingSummary();
            });
    }


    function displayPackages(packages) {
        packagesContainer.innerHTML = '';
        selectedPackage = null;

        packages.forEach(packageData => {

            const packageDiv = document.createElement('div');
            packageDiv.className = 'bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow cursor-pointer';
            packageDiv.setAttribute('data-package-id', packageData.id);
            
            let imageHtml;
            if (packageData.image) {
                imageHtml = '<div class="relative rounded-lg overflow-hidden h-60 bg-gray-200">' +
                    '<img src="' + packageData.image + '" alt="' + packageData.name + '" class="w-full h-full object-cover">' +
                    '</div>';
            } else {
                imageHtml = '<div class="relative rounded-lg overflow-hidden h-48 bg-gray-200 flex items-center justify-center">' +
                    '<i class="bi bi-box text-4xl sm:text-5xl text-gray-400"></i>' +
                    '</div>';
            }
            
            // Build activities list
            let activitiesHtml = '';
            if (packageData.activities && packageData.activities.length > 0) {
                activitiesHtml = '<div class="mt-3 flex flex-wrap gap-1">' +
                    packageData.activities.map(a => '<span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">' + a.name + '</span>').join('') +
                    '</div>';
            }
            
            packageDiv.innerHTML = '<div class="space-y-4">' +
                '<!-- Package Image -->' +
                imageHtml +
                '<!-- Package Details -->' +
                '<div>' +
                    '<div class="flex items-start justify-between">' +
                        '<h4 class="font-semibold text-gray-900 text-base sm:text-lg">' + packageData.name + '</h4>' +
                        '<span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">' +
                            '<i class="bi bi-people mr-1"></i> Max ' + packageData.max_guests + ' persons' +
                        '</span>' +
                    '</div>' +
                    '<p class="text-sm text-gray-600 mt-1 line-clamp-2">' + (packageData.description || '') + '</p>' +
                    activitiesHtml +
                    '<div class="mt-3 p-3 bg-white rounded-lg border border-gray-200">' +
                        '<div class="flex items-center justify-between">' +
                            '<span class="text-lg font-bold text-[#8f5d54]">' + packageData.price_display + '</span>' +
                            '<span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full">Available</span>' +
                        '</div>' +
                        '<p class="text-xs text-gray-500 mt-1">' + packageData.price_breakdown + '</p>' +
                    '</div>' +
                '</div>' +
            '</div>';

            packageDiv.addEventListener('click', function(e) {
                // Don't trigger if clicking on buttons or inputs
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
                
                console.log('Selected package:', packageData);
                selectedPackage = {
                    ...packageData,
                    guests: packageData.max_guests // Use the package's max capacity
                };

                
                // Visual selection
                document.querySelectorAll('#packages-container > div').forEach(el => {
                    el.classList.remove('ring-2', 'ring-[#8f5d54]', 'bg-[#f5efe7]');
                    el.classList.add('bg-gray-50');
                });
                packageDiv.classList.remove('bg-gray-50');
                packageDiv.classList.add('ring-2', 'ring-[#8f5d54]', 'bg-[#f5efe7]');
                
                updateBookingSummary();
                
                // Show SweetAlert toast for package selection
                Swal.fire({
                    icon: 'success',
                    title: `${packageData.name} selected`,
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            });

            packagesContainer.appendChild(packageDiv);
        });
    }


    // Helper function to format price without .00
    function formatPrice(price) {
        const num = parseFloat(price);
        if (Number.isInteger(num)) {
            return `₱${num.toLocaleString()}`;
        }
        return `₱${num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }

    function updateBookingSummary() {
        // Update package display
        const summaryPackage = document.getElementById('summary-package');
        const summaryPricePerPerson = document.getElementById('summary-price-per-person');
        const summaryTotalPrice = document.getElementById('summary-total-price');
        const summaryStatus = document.getElementById('summary-status');
        const summaryGuests = document.getElementById('summary-guests');
        const vatDisplay = document.getElementById('vat-display');
        const summaryVat = document.getElementById('summary-vat');

        if (selectedPackage) {
            summaryPackage.innerHTML = `
                <div class="font-medium">${selectedPackage.name}</div>
                <div class="text-xs text-gray-500 mt-1">
                    <i class="bi bi-people mr-1"></i> Good for ${selectedPackage.max_guests} persons
                </div>
            `;
            summaryPricePerPerson.textContent = formatPrice(selectedPackage.price);
            summaryTotalPrice.textContent = formatPrice(selectedPackage.total_price);
            summaryGuests.textContent = selectedPackage.max_guests;
            
            // Show VAT display
            if (vatDisplay && selectedPackage.vat) {
                vatDisplay.classList.remove('hidden');
                summaryVat.textContent = formatPrice(selectedPackage.vat.amount);
            }

            summaryStatus.innerHTML = `
                <span class="text-green-600 font-semibold">
                    <i class="bi bi-check-circle mr-1"></i>Ready to book!
                </span>
            `;
            // Enable buttons
            summaryContinueBtn.disabled = false;
            proceedToPaymentBtn.disabled = false;
        } else {
            summaryPackage.textContent = '—';
            summaryPricePerPerson.textContent = '—';
            summaryTotalPrice.textContent = '—';
            summaryGuests.textContent = currentGuests > 1 ? currentGuests : '—';
            summaryStatus.textContent = 'Please select a package to view booking details';
            // Hide VAT display
            if (vatDisplay) {
                vatDisplay.classList.add('hidden');
            }
            // Disable buttons
            summaryContinueBtn.disabled = true;
            proceedToPaymentBtn.disabled = true;
        }
    }


    function showError(message) {
        // Show SweetAlert error toast
        Swal.fire({
            icon: 'error',
            title: message,
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Initial summary update
    updateBookingSummary();
});
</script>

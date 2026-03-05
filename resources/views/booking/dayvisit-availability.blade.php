<x-page-layout title="CalaiaPH - Day Visit Availability" active-header="daytour">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Real-time inventory updates via Pusher --}}
    @if(config('broadcasting.default') !== 'null')
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
        <script>
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                encrypted: true
            });
        </script>
    @endif
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

                        {{-- Selected Packages (Multiple like Staycation) --}}
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-semibold text-gray-800 flex items-center">
                                    <i class="bi bi-box mr-2 text-[#8f5d54]"></i> Selected Packages
                                </h4>
                                <span id="package-count" class="text-xs bg-[#8f5d54] text-white px-2 py-1 rounded-full">0</span>
                            </div>

                            {{-- Slot Visualization (like staycation rooms) --}}
                            <div id="package-slots" class="flex flex-wrap items-center gap-2 mb-4">
                                {{-- Slots will be rendered dynamically --}}
                            </div>

                            <div id="summary-packages" class="space-y-3">
                                {{-- Package items will be added here dynamically --}}
                                <div class="text-sm text-gray-500 text-center py-4">No packages selected</div>
                            </div>

                            {{-- Reset All Button --}}
                            <button
                                id="reset-all-btn"
                                type="button"
                                class="w-full mt-3 px-4 py-2 rounded-lg font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="resetAllPackages()"
                                disabled
                            >
                                <i class="bi bi-arrow-counterclockwise mr-2"></i> Reset All
                            </button>
                        </div>

                        {{-- Pricing Summary --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="bi bi-currency-peso mr-2 text-[#8f5d54]"></i> Pricing
                            </h4>

                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Subtotal</span>
                                    <span id="summary-subtotal" class="font-semibold">—</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">12% VAT</span>
                                    <span id="summary-vat-amount" class="font-semibold">—</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-800">Grand Total</span>
                                    <span id="summary-grand-total" class="font-semibold text-[#8f5d54] text-lg">—</span>
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
// Global variables and functions for package management
let selectedPackages = []; // Array to store multiple packages (like staycation)
let currentGuests = 1;
let allPackagesData = []; // Store all packages data for re-rendering

// Remove package from selection (global function for onclick handlers)
function removePackage(packageId) {
    selectedPackages = selectedPackages.filter(p => p.id !== packageId);
    updateBookingSummary();
    refreshPackageDisplay();
}

// Reset all packages (global function for onclick handlers)
function resetAllPackages() {
    if (selectedPackages.length === 0) return;

    Swal.fire({
        title: 'Reset all packages?',
        text: 'This will remove all selected packages.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#8f5d54',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, reset all',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedPackages = [];
            updateBookingSummary();
            refreshPackageDisplay();

            Swal.fire({
                icon: 'success',
                title: 'All packages cleared',
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const summaryContinueBtn = document.getElementById('summary-continue-btn');
    const proceedToPaymentBtn = document.getElementById('proceed-to-payment-btn');
    const availabilityResult = document.getElementById('availability-result');
    const packagesContainer = document.getElementById('packages-container');
    const unavailableMessage = document.getElementById('unavailable-message');
    const guestsInput = document.getElementById('guests-input');
    const checkAvailabilityBtn = document.getElementById('check-availability-btn');

    // Variables moved to global scope above

    // Load all packages on page load
    console.log('DOM loaded, calling loadPackages');
    loadAllPackages();

    // Listen for real-time inventory updates via Pusher
    if (window.Echo && window.Echo.channel) {
        window.Echo.channel('daytour-inventory')
            .listen('.daytour.inventory.updated', (e) => {
                console.log('Day tour inventory updated:', e);
                // Refresh packages to show updated availability
                loadAllPackages();

                // Show toast notification if any selected package became unavailable
                if (selectedPackages.length > 0 && e.availability && !e.availability.available) {
                    const updatedPackageId = e.day_tour_id;
                    const affectedPackage = selectedPackages.find(p => p.id === updatedPackageId);
                    if (affectedPackage) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Package No Longer Available',
                            text: `${affectedPackage.name} has just been booked by another customer. It will be removed from your selection.`,
                            toast: true,
                            position: 'center',
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        });
                        // Remove the unavailable package
                        selectedPackages = selectedPackages.filter(p => p.id !== updatedPackageId);
                        updateBookingSummary();
                    }
                }
            });
    }

    // Continue to Activities button in Booking Summary
    summaryContinueBtn.addEventListener('click', function() {
        if (selectedPackages.length > 0) {
            // Prepare packages data for submission (like staycation)
            const packagesData = selectedPackages.map(pkg => ({
                id: pkg.id,
                name: pkg.name,
                price: pkg.price_exclusive || pkg.price,
                guests: pkg.max_guests,
                quantity: pkg.quantity || 1
            }));

            // Store selected packages in session and redirect to activities
            fetch('{{ route("dayvisit.storeAvailability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    selected_packages: packagesData
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
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No Package Selected',
                text: 'Please select at least one package before continuing.',
                confirmButtonColor: '#8f5d54',
                confirmButtonText: 'OK'
            });
        }
    });


    // Proceed to Payment button functionality
    proceedToPaymentBtn.addEventListener('click', function() {
        if (selectedPackages.length > 0) {
            // Prepare packages data for submission
            const packagesData = selectedPackages.map(pkg => ({
                id: pkg.id,
                name: pkg.name,
                price: pkg.price_exclusive || pkg.price,
                guests: pkg.max_guests,
                quantity: pkg.quantity || 1
            }));

            // Store selected packages in session and redirect to payment
            fetch('{{ route("dayvisit.storeAvailability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    selected_packages: packagesData
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
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No Package Selected',
                text: 'Please select at least one package before proceeding to payment.',
                confirmButtonColor: '#8f5d54',
                confirmButtonText: 'OK'
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
                    allPackagesData = data.packages; // Store for re-rendering
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
                    selectedPackage = null;
                    updateBookingSummary();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load packages');
                unavailableMessage.classList.remove('hidden');
                packagesContainer.innerHTML = '';
                selectedPackage = null;
                updateBookingSummary();
            });
    }


    function displayPackages(packages) {
        packagesContainer.innerHTML = '';

        packages.forEach(packageData => {
            const packageDiv = document.createElement('div');
            packageDiv.className = 'bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow';
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

            // Show slot availability info from admin panel (dynamic)
            const inventory = packageData.inventory || {};
            const totalSlots = inventory.total_slots || packageData.max_groups || 5;
            const availableSlots = inventory.available_slots || totalSlots;
            const bookedSlots = inventory.booked_slots || 0;

            // Visual indicator for slot availability
            let slotStatusClass = 'bg-green-100 text-green-800';
            if (availableSlots === 0) slotStatusClass = 'bg-red-100 text-red-800';
            else if (availableSlots <= Math.ceil(totalSlots * 0.3)) slotStatusClass = 'bg-yellow-100 text-yellow-800';

            const slotInfo = `<span class="inline-flex items-center px-2 py-1 ${slotStatusClass} text-xs rounded-full">` +
                `<i class="bi bi-ticket mr-1"></i> ${availableSlots}/${totalSlots} slots` +
                '</span>';

            // Check how many of this package are already selected
            const selectedQty = selectedPackages.find(p => p.id === packageData.id)?.quantity || 0;
            const remainingAfterSelection = availableSlots - selectedQty;

            // Dynamic slot info based on current selection
            const dynamicSlotInfo = `<span class="inline-flex items-center px-2 py-1 ${slotStatusClass} text-xs rounded-full">` +
                `<i class="bi bi-ticket mr-1"></i> ${remainingAfterSelection}/${totalSlots} slots` +
                '</span>';

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
                        '<div class="flex items-center justify-between mb-2">' +
                            '<span class="text-lg font-bold text-[#8f5d54]">' + packageData.price_display + '</span>' +
                            dynamicSlotInfo +
                        '</div>' +
                        '<p class="text-xs text-gray-500 mt-1">' + packageData.price_breakdown + '</p>' +
                        '<!-- Add Package Button -->' +
                        '<button class="add-package-btn w-full mt-3 py-2 px-4 rounded-lg font-medium transition-all duration-200 ' +
                            (remainingAfterSelection <= 0 ? 'bg-gray-400 text-white cursor-not-allowed' :
                             selectedQty > 0 ? 'bg-green-600 text-white hover:bg-green-700' :
                             'bg-[#8f5d54] text-white hover:bg-[#a0675a]') +
                            '" data-package-id="' + packageData.id + '" ' +
                            (remainingAfterSelection <= 0 ? 'disabled' : '') + '>' +
                            (remainingAfterSelection <= 0 ? '<i class="bi bi-x-circle mr-1"></i> Fully Booked' :
                             selectedQty > 0 ? `<i class="bi bi-check mr-1"></i> Added (${selectedQty}) - ${remainingAfterSelection} left` :
                             `<i class="bi bi-plus mr-1"></i> Add Package (${remainingAfterSelection} available)`) +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

            // Add click handler for the Add Package button
            const addBtn = packageDiv.querySelector('.add-package-btn');
            addBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                addPackage(packageData);
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

    // Maximum number of package slots - will be set dynamically from package data
    let currentPackageMaxSlots = 5; // Default fallback

    // Add package to selection (like staycation add room)
    function addPackage(packageData) {
        // Get max slots from package inventory (from admin panel)
        const maxSlots = packageData.inventory?.total_slots || packageData.max_groups || 5;
        const availableSlots = packageData.inventory?.available_slots || maxSlots;

        // Check how many of this package are already selected
        const existingIndex = selectedPackages.findIndex(p => p.id === packageData.id);
        const currentQty = existingIndex >= 0 ? selectedPackages[existingIndex].quantity : 0;

        // Check if we've reached the available slots limit
        if (currentQty >= availableSlots) {
            Swal.fire({
                icon: 'warning',
                title: 'No slots available',
                text: `Only ${availableSlots} slot${availableSlots !== 1 ? 's' : ''} available for ${packageData.name}.`,
                toast: true,
                position: 'center',
                showConfirmButton: true
            });
            return;
        }

        if (existingIndex >= 0) {
            // Increment quantity if already selected
            selectedPackages[existingIndex].quantity++;
            selectedPackages[existingIndex].total_price = selectedPackages[existingIndex].price *
                selectedPackages[existingIndex].guests *
                selectedPackages[existingIndex].quantity;
        } else {
            // Add new package
            selectedPackages.push({
                ...packageData,
                guests: packageData.max_guests,
                quantity: 1,
                total_price: packageData.price * packageData.max_guests,
                max_slots: maxSlots // Store max slots for this package
            });
        }

        updateBookingSummary();
        refreshPackageDisplay();

        // Show success toast with remaining slots
        const remainingSlots = availableSlots - currentQty - 1;
        Swal.fire({
            icon: 'success',
            title: `${packageData.name} added`,
            text: remainingSlots > 0 ? `${remainingSlots} slot${remainingSlots !== 1 ? 's' : ''} remaining` : 'Last slot!',
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }

    // Note: resetAllPackages and removePackage are defined globally above DOMContentLoaded

    // Update quantity for a package
    function updatePackageQuantity(packageId, delta) {
        const pkg = selectedPackages.find(p => p.id === packageId);
        if (pkg) {
            pkg.quantity = Math.max(1, pkg.quantity + delta);
            pkg.total_price = pkg.price * pkg.guests * pkg.quantity;
            updateBookingSummary();
            refreshPackageDisplay();
        }
    }

    // Refresh package cards display (update button states and slot counts)
    function refreshPackageDisplay() {
        // Re-render packages with updated slot counts based on current selections
        if (allPackagesData.length > 0) {
            displayPackages(allPackagesData);
        }
        updateBookingSummary();
    }

    // Get slot package name (like staycation getSlotRoomName)
    function getSlotPackageName(slotNum) {
        let currentSlot = 0;
        for (const pkg of selectedPackages) {
            for (let i = 0; i < pkg.quantity; i++) {
                currentSlot++;
                if (currentSlot === slotNum) {
                    return pkg.name;
                }
            }
        }
        return 'Empty';
    }

    // Remove package by slot number (like staycation removeRoomBySlot)
    function removePackageBySlot(slotNum) {
        let currentSlot = 0;
        for (let pkgIndex = 0; pkgIndex < selectedPackages.length; pkgIndex++) {
            const pkg = selectedPackages[pkgIndex];
            for (let i = 0; i < pkg.quantity; i++) {
                currentSlot++;
                if (currentSlot === slotNum) {
                    // Decrement quantity or remove package
                    if (pkg.quantity > 1) {
                        pkg.quantity--;
                        pkg.total_price = pkg.price * pkg.guests * pkg.quantity;
                    } else {
                        selectedPackages.splice(pkgIndex, 1);
                    }
                    updateBookingSummary();
                    refreshPackageDisplay();
                    return;
                }
            }
        }
    }

    // Update booking summary with multiple packages (like staycation)
    function updateBookingSummary() {
        const summaryPackages = document.getElementById('summary-packages');
        const packageSlots = document.getElementById('package-slots');
        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryVatAmount = document.getElementById('summary-vat-amount');
        const summaryGrandTotal = document.getElementById('summary-grand-total');
        const packageCount = document.getElementById('package-count');
        const summaryGuests = document.getElementById('summary-guests');
        const summaryStatus = document.getElementById('summary-status');
        const resetAllBtn = document.getElementById('reset-all-btn');

        if (selectedPackages.length > 0) {
            // Update package count badge
            const totalQty = selectedPackages.reduce((sum, p) => sum + p.quantity, 0);
            packageCount.textContent = totalQty;

            // Calculate totals
            let subtotal = 0;
            let totalGuests = 0;

            selectedPackages.forEach(pkg => {
                subtotal += pkg.price * pkg.guests * pkg.quantity;
                totalGuests += pkg.guests * pkg.quantity;
            });

            const vatAmount = subtotal * 0.12; // 12% VAT
            const grandTotal = subtotal; // Price already includes VAT

            // Render slot visualization (like staycation rooms) - dynamic based on highest max_slots
            const maxSlotsAcrossPackages = selectedPackages.reduce((max, pkg) =>
                Math.max(max, pkg.max_slots || pkg.inventory?.total_slots || 5), 5);

            let slotsHtml = '';
            for (let i = 1; i <= Math.max(totalQty, maxSlotsAcrossPackages); i++) {
                const isFilled = i <= totalQty;
                const slotClass = isFilled
                    ? 'border-[#8f5d54] bg-[#8f5d54]/5 cursor-pointer hover:bg-[#8f5d54]/10'
                    : 'border-gray-200 bg-gray-50';
                const textClass = isFilled ? 'text-[#8f5d54]' : 'text-gray-400';
                const pkgName = isFilled ? getSlotPackageName(i) : 'Empty';
                const removeIcon = isFilled
                    ? `<div class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-md" onclick="event.stopPropagation(); removePackageBySlot(${i})"><i class="bi bi-x text-xs"></i></div>`
                    : '';

                slotsHtml += `
                    <div class="flex-1 min-w-[70px] max-w-[100px]">
                        <div class="border-2 rounded-lg p-2 text-center transition-all relative group ${slotClass}"
                             ${isFilled ? `onclick="removePackageBySlot(${i})" title="Click to remove this package"` : 'title="Empty slot"'}>
                            <div class="text-xs font-semibold uppercase tracking-wider mb-1 ${textClass}">Pkg ${i}</div>
                            <div class="text-xs font-medium truncate ${isFilled ? 'text-gray-800' : 'text-gray-400'}">${pkgName}</div>
                            ${removeIcon}
                        </div>
                    </div>
                `;
            }
            packageSlots.innerHTML = slotsHtml;

            // Render packages list
            summaryPackages.innerHTML = selectedPackages.map(pkg => `
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-medium text-sm">${pkg.name}</div>
                            <div class="text-xs text-gray-500">
                                <i class="bi bi-people mr-1"></i> ${pkg.guests} persons × ${pkg.quantity} slot${pkg.quantity > 1 ? 's' : ''}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-sm text-[#8f5d54]">${formatPrice(pkg.price * pkg.guests * pkg.quantity)}</div>
                            <button onclick="removePackage(${pkg.id})" class="text-xs text-red-500 hover:text-red-700 mt-1">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Update totals
            summarySubtotal.textContent = formatPrice(subtotal / 1.12); // Remove VAT from display
            summaryVatAmount.textContent = formatPrice(vatAmount);
            summaryGrandTotal.textContent = formatPrice(grandTotal);
            summaryGuests.textContent = totalGuests;

            summaryStatus.innerHTML = `
                <span class="text-green-600 font-semibold">
                    <i class="bi bi-check-circle mr-1"></i>Ready to book!
                </span>
            `;

            // Enable reset button
            resetAllBtn.disabled = false;
        } else {
            packageCount.textContent = '0';
            packageSlots.innerHTML = '';
            summaryPackages.innerHTML = '<div class="text-sm text-gray-500 text-center py-4">No packages selected</div>';
            summarySubtotal.textContent = '—';
            summaryVatAmount.textContent = '—';
            summaryGrandTotal.textContent = '—';
            summaryGuests.textContent = '—';
            summaryStatus.textContent = 'Please select a package to view booking details';

            // Disable reset button only
            resetAllBtn.disabled = true;
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

    // Expose functions to global scope for onclick handlers
    window.updateBookingSummary = updateBookingSummary;
    window.refreshPackageDisplay = refreshPackageDisplay;
    window.removePackageBySlot = removePackageBySlot;
    window.displayPackages = displayPackages;
    window.addPackage = addPackage;
});
</script>

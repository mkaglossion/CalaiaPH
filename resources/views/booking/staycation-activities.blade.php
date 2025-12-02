 <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Select Staycation Activities</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

@php
$backUrl = route('booking.staycation');

// Initialize / read booking session
$booking = session('booking', [
    'checkin'        => null,
    'checkout'       => null,
    'accommodations' => [],
    'booking_type'   => 'general',
    'timestamp'      => now()->timestamp,
    'completed_steps'=> [],
    'current_step'   => 1,
]);

$bookingType  = $booking['booking_type'] ?? 'general';
$stepNumber   = '1'; // landing page
$totalSteps   = $bookingType === 'staycation' ? '3' : '4';
$backLink     = url('/home');

$completedSteps  = $booking['completed_steps'] ?? [];
$step1Completed  = in_array(1, $completedSteps);

$checkinDate  = $booking['checkin'] ?? null;
$checkoutDate = $booking['checkout'] ?? null;
$datesSelected = !empty($checkinDate) && !empty($checkoutDate);

// Calculate nights
$nights = 0;
if ($datesSelected) {
    $start = new DateTime($checkinDate);
    $end = new DateTime($checkoutDate);
    $diff = $start->diff($end);
    $nights = max(1, $diff->days);
}

// Calculate total guests
$totalGuests = collect($booking['accommodations'])->sum('guests');

// Check if accommodations are selected
$accommodationsSelected = !empty($booking['accommodations']) && count(array_filter($booking['accommodations'])) > 0;

// For staycation, adjust step display
if ($bookingType === 'staycation') {
    if ($accommodationsSelected) {
        $stepNumber = '1'; // Show as step 1 (completed booking)
    }
}

// Calculate accommodation total with dynamic pricing
$accommodationTotal = 0;
if ($datesSelected) {
    $start = new DateTime($checkinDate);
    $end = new DateTime($checkoutDate);

    foreach ($booking['accommodations'] ?? [] as $acc) {
        if (!empty($acc) && !empty($acc['staycation'])) {
            $quantity = $acc['guests'] ?? 1;
            $name = strtolower($acc['staycation']['name']);
            $isBYOT = str_contains($name, 'byot');
            $isCarCamping = str_contains($name, 'car camping');

            if (str_contains($name, 'standard') || str_contains($name, 'premium')) {
                $current = clone $start;
                while ($current < $end) {
                    $day = (int)$current->format('w'); // 0=Sun, 6=Sat
                    $isWeekend = $day === 5 || $day === 6; // Fri=5, Sat=6
                    $basePrice = 0;
                    if (str_contains($name, 'standard')) {
                        $basePrice = $isWeekend ? 6000 : 4800;
                    } elseif (str_contains($name, 'premium')) {
                        $basePrice = $isWeekend ? 7200 : 5760;
                    }
                    $extraGuests = max(0, $quantity - 4);
                    $accommodationTotal += $basePrice + ($extraGuests * 400);
                    $current->modify('+1 day');
                }
            } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                $accommodationTotal += 3000 * $nights;
            } elseif ($isBYOT || $isCarCamping) {
                $accommodationTotal += 300 * $quantity * $nights;
            }
        }
    }

    // Add pet fees
    foreach ($booking['accommodations'] ?? [] as $acc) {
        if (!empty($acc['pet_agreement']) && !empty($acc['num_pets'])) {
            $accommodationTotal += 2000 * $acc['num_pets'];
        }
    }

    // Add breakfast fees
    $breakfastTotal = 0;
    foreach ($booking['accommodations'] ?? [] as $acc) {
        if (!empty($acc['has_breakfast'])) {
            $breakfastTotal += 500 * ($acc['guests'] ?? 1);
        }
    }
}
@endphp

<body class="bg-[#f5efe7]">
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
          <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
              {{ session('success') }}
          </div>
      @endif
    </div>
    <x-header active="booking" />
    <div class="min-h-screen">

<div class="pt-6 md:pt-12">
    {{-- Header --}}
    <div class="text-center mb-10">
        <h1 class="text-2xl font-bold tracking-tight" style="font-family: 'Gotham Book', sans-serif;">Select Staycation Activities</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">Enhance your staycation experience with optional activities</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mx-0 md:mx-10 py-6">
        {{-- Main content --}}
        <div class="lg:col-span-2">
            <div class="flex flex-col">
                {{-- Progress indicator --}}
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <small class="text-gray-500 text-sm">
                            Step 2 of 3: Select Activities
                        </small>
                        <small class="text-gray-500 text-sm">67% Complete</small>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-[#8f5d54] h-2 rounded-full transition-all duration-300" style="width: 67%"></div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="flex items-end justify-between my-3">
                    <a href="{{ $backUrl }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Back
                    </a>

                    <div class="flex items-center gap-2">
                        @if (session()->has('user.is_guest') && session('user.is_guest'))
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                                style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                                <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Activities Selection --}}
                <x-activities-page-no-sidebar
                    pretitle="Staycation Activities"
                    title="Select Staycation Activities"
                    subtitle="Enhance your staycation experience with optional activities"
                    :back-route="route('booking.staycation')"
                    :payment-route="route('booking.staycationPayment')"
                    :store-route="route('booking.storeStaycationActivities')"
                    activities-endpoint="/api/activities"
                    :total-guests="$totalGuests"
                    :auto-guests="true"
                    :is-staycation="true"
                    :accommodation-total="$accommodationTotal + $breakfastTotal"
                />
            </div>
        </div>

        {{-- Sidebar: booking summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Booking Summary</h3>

                @if($datesSelected)
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Check-in:</span>
                            <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkinDate)->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Check-out:</span>
                            <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkoutDate)->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nights:</span>
                            <span class="text-sm font-medium">{{ $nights }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Guests:</span>
                            <span class="text-sm font-medium">{{ $totalGuests }}</span>
                        </div>
                        <hr class="my-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Accommodations:</span>
                            <span class="text-sm font-medium">{{ count(array_filter($booking['accommodations'] ?? [])) }} selected</span>
                        </div>
                        @if($bookingType === 'staycation')
                        <div class="mt-2 space-y-1">
                            @if(!empty($booking['accommodations']))
                                @php
                                $start = new DateTime($checkinDate);
                                $end = new DateTime($checkoutDate);
                                @endphp
                                @foreach($booking['accommodations'] as $index => $accommodation)
                                    @if(!empty($accommodation) && !empty($accommodation['staycation']))
                                        @php
                                        $quantity = $accommodation['guests'] ?? 1;
                                        $name = strtolower($accommodation['staycation']['name']);
                                        $isBYOT = str_contains($name, 'byot');
                                        $isCarCamping = str_contains($name, 'car camping');
                                        $accommodationPrice = 0;
                                        $basePriceTotal = 0;
                                        $extraPriceTotal = 0;
                                        $extraGuests = 0;

                                        if (str_contains($name, 'standard') || str_contains($name, 'premium')) {
                                            $current = clone $start;
                                            while ($current < $end) {
                                                $day = (int)$current->format('w');
                                                $isWeekend = $day === 5 || $day === 6;
                                                $basePrice = 0;
                                                if (str_contains($name, 'standard')) {
                                                    $basePrice = $isWeekend ? 6000 : 4800;
                                                } elseif (str_contains($name, 'premium')) {
                                                    $basePrice = $isWeekend ? 7200 : 5760;
                                                }
                                                $extraGuests = max(0, $quantity - 4);
                                                $accommodationPrice += $basePrice + ($extraGuests * 400);
                                                $basePriceTotal += $basePrice;
                                                $extraPriceTotal += $extraGuests * 400;
                                                $current->modify('+1 day');
                                            }
                                        } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                                            $accommodationPrice = 3000 * $nights;
                                        } elseif ($isBYOT || $isCarCamping) {
                                            $accommodationPrice = 300 * $quantity * $nights;
                                        }

                                        $petFee = (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) ? 2000 * $accommodation['num_pets'] : 0;
                                        $totalPrice = $accommodationPrice + $petFee;

                                        // Calculate base rate per night (weekday rate for standard/premium)
                                        $baseRate = 0;
                                        if (str_contains($name, 'standard')) {
                                            $baseRate = 4800; // weekday rate
                                        } elseif (str_contains($name, 'premium')) {
                                            $baseRate = 5760; // weekday rate
                                        } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                                            $baseRate = 3000;
                                        } elseif ($isBYOT || $isCarCamping) {
                                            $baseRate = 300 * $quantity;
                                        }

                                        $extraRate = $extraGuests * 400; // ₱400 per extra guest per night
                                        @endphp
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <div>ACCOMMODATION {{ (int)$index + 1 }}: {{ $accommodation['staycation']['name'] }} - ₱{{ number_format($baseRate) }}/night ({{ $accommodation['guests'] }} guests)</div>
                                            @if($extraGuests > 0)
                                                <div>Extra Mattress ({{ $extraGuests }} guest{{ $extraGuests > 1 ? 's' : '' }}): ₱{{ number_format($extraRate) }}/night</div>
                                            @endif
                                            @if($petFee > 0)
                                                <div>Pet Fee ({{ $accommodation['num_pets'] }} pet{{ $accommodation['num_pets'] > 1 ? 's' : '' }}): ₱{{ number_format($petFee) }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        @endif
                         @if($breakfastTotal > 0)
                         <div class="flex justify-between items-center">
                             <span class="text-sm text-gray-600">Breakfast:</span>
                             <span class="text-sm font-medium">₱ {{ number_format($breakfastTotal) }}</span>
                         </div>
                         @endif
                         <div class="flex justify-between items-center text-lg font-bold pt-2 border-t border-gray-200">
                             <span>Accommodation Total:</span>
                             <span>₱ {{ number_format($accommodationTotal + $breakfastTotal) }}</span>
                         </div>
                    </div>
                @else
                    <p class="text-xs text-gray-500 mb-6" style="font-family: 'Gotham Book', sans-serif;">Please select your dates to see the booking summary.</p>
                @endif

                <hr class="my-4">

                <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Selected Activities</h3>

                <div id="selected-activities" class="space-y-3 mb-4">
                  <!-- Selected activities will be added here -->
                </div>

                <div class="border-t border-gray-200 pt-4">
                  <div class="flex justify-between items-center text-lg font-bold">
                    <span>Activities Total:</span>
                    <span id="total-cost">₱0.00</span>
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                  <div class="flex justify-between items-center text-xl font-bold text-[#8f5d54]">
                    <span>Grand Total:</span>
                    <span id="grand-total">₱ {{ number_format($accommodationTotal + $breakfastTotal) }}</span>
                  </div>
                </div>

                <div class="mt-6 space-y-3">
                  <button
                    type="button"
                    id="skip-btn"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                  >
                    Proceed to Payment Without Activities
                    <i class="bi bi-arrow-right"></i>
                  </button>

                  <button
                    type="button"
                    id="confirm-btn"
                    class="w-full bg-emerald-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                  >
                    Confirm and Proceed
                  </button>
                </div>

                {{-- Trust elements --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3" style="font-family: 'Gotham Book', sans-serif;">Secure Booking</h4>
                        <div class="flex justify-center items-center space-x-4">
                            <div class="flex items-center">
                                <i class="fas fa-lock text-green-600 mr-2"></i>
                                <span class="text-xs text-gray-600">SSL Secured</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                                <span class="text-xs text-gray-600">Data Protected</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Your information is safe with us</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers --}}
  <style>
    .font-gotham {
      font-family: 'Gotham Book', sans-serif;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const selectedActivities = [];
      let totalCost = 0;

      // Get data from attributes
      const container = document.querySelector('.pt-6.md\\:pt-12');
      const accommodationTotal = parseFloat(container.dataset.accommodationTotal) || 0;
      const totalGuests = parseInt(container.dataset.totalGuests) || 0;
      const nights = parseInt(container.dataset.nights) || 0;

      // Load activities from API
      loadActivities();

      // Function to load activities from API
      function loadActivities() {
        fetch('/api/activities')
          .then(response => response.json())
          .then(activities => {
            const container = document.getElementById('activities-container');
            container.innerHTML = '';

            activities.forEach(activity => {
              const activityDiv = document.createElement('div');
              activityDiv.className = 'bg-gray-50 rounded-xl border border-gray-100 p-4 hover:shadow-md transition-shadow';
              activityDiv.innerHTML = `
                <div class="flex items-start space-x-3">
                  <input
                    type="checkbox"
                    id="activity-${activity.id}"
                    name="activities[]"
                    value="${activity.id}"
                    class="activity-checkbox mt-1 text-[#8f5d54] focus:ring-[#8f5d54]"
                    data-price="${activity.price}"
                    data-name="${activity.name}"
                  >
                  <div class="flex-1">
                    <label for="activity-${activity.id}" class="cursor-pointer">
                      <div class="flex items-start space-x-3">
                        ${activity.image ? `<img src="${activity.image}" alt="${activity.name}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">` : ''}
                        <div class="flex-1">
                          <h4 class="font-semibold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">${activity.name}</h4>
                          <p class="text-xs text-gray-600 mt-1" style="font-family: 'Gotham Book', sans-serif;">${activity.description}</p>
                          <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">
                              <i class="bi bi-clock mr-1"></i>${activity.duration}
                            </span>
                            <span class="text-xs font-semibold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">₱${parseFloat(activity.price).toFixed(2)}</span>
                          </div>
                          <span class="inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full" style="font-family: 'Gotham Book', sans-serif;">
                            ${activity.availability}
                          </span>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>
              `;
              container.appendChild(activityDiv);
            });
          })
          .catch(error => {
            console.error('Error loading activities:', error);
            showToast('Error loading activities. Please refresh the page.', 'error');
          });
      }

    // Handle activity checkboxes (using event delegation)
    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('activity-checkbox')) {
        const checkbox = e.target;
        const activityId = checkbox.value;
        const activityName = checkbox.dataset.name;
        const activityPrice = parseFloat(checkbox.dataset.price);

        if (checkbox.checked) {
          selectedActivities.push({ id: activityId, name: activityName, price: activityPrice, guests: totalGuests, type: 'predefined' });
          totalCost += activityPrice * totalGuests;
          showToast(`${activityName} added to selection`, 'success');
        } else {
          const index = selectedActivities.findIndex(a => a.id === activityId);
          if (index > -1) {
            totalCost -= selectedActivities[index].price * selectedActivities[index].guests;
            selectedActivities.splice(index, 1);
            showToast(`${activityName} removed from selection`, 'info');
          }
        }

        updateSummary();
      }
    });


      // Update summary display
      function updateSummary() {
        const container = document.getElementById('selected-activities');
        const totalElement = document.getElementById('total-cost');
        const grandTotalElement = document.getElementById('grand-total');
        const confirmBtn = document.getElementById('confirm-btn');

        container.innerHTML = '';

        if (selectedActivities.length === 0) {
          container.innerHTML = '<p class="text-xs text-gray-500" style="font-family: \'Gotham Book\', sans-serif;">No activities selected</p>';
          confirmBtn.disabled = true;
        } else {
          selectedActivities.forEach(activity => {
            const item = document.createElement('div');
            item.className = 'flex justify-between items-start p-2 bg-white rounded border';
            item.innerHTML = `
              <div class="flex-1">
                <p class="text-xs font-medium text-gray-900" style="font-family: 'Gotham Book', sans-serif;">${activity.name}</p>
                <div class="mt-2">
                  <label class="block text-xs text-gray-600 mb-1" style="font-family: 'Gotham Book', sans-serif;">Number of Guests</label>
                  <input type="number" class="activity-guests w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent" value="${activity.guests}" min="1" max="${totalGuests}" data-activity-id="${activity.id}">
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-[#8f5d54]">₱${(activity.price * activity.guests).toFixed(2)}</span>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeActivity('${activity.id}')">
                  <i class="bi bi-x"></i>
                </button>
              </div>
            `;
            container.appendChild(item);

            // Add event listener for guest input
            const guestInput = item.querySelector('.activity-guests');
            guestInput.addEventListener('input', function() {
              const newGuests = parseInt(this.value) || 1;
              const index = selectedActivities.findIndex(a => a.id === activity.id);
              if (index > -1) {
                const oldGuests = selectedActivities[index].guests;
                selectedActivities[index].guests = newGuests;
                totalCost += (newGuests - oldGuests) * activity.price;
                updateSummary();
              }
            });
          });
          confirmBtn.disabled = false;
        }

        totalElement.textContent = `₱${totalCost.toFixed(2)}`;

        // Calculate grand total (accommodation total + activities total)
        const grandTotal = accommodationTotal + totalCost;
        if (grandTotalElement) {
          grandTotalElement.textContent = `₱${grandTotal.toFixed(2)}`;
        }
      }

      // Remove activity
      window.removeActivity = function(id) {
        const index = selectedActivities.findIndex(a => a.id === id);
        if (index > -1) {
          const activityName = selectedActivities[index].name;
          totalCost -= selectedActivities[index].price * totalGuests;
          selectedActivities.splice(index, 1);
          updateSummary();
          showToast(`${activityName} removed from selection`, 'info');
        }
      };

      // Toast function
      function showToast(message, type = 'info') {
        const toastEl = document.querySelector('.toast');
        if (toastEl) {
          const toastBody = toastEl.querySelector('.toast-body');
          if (toastBody) {
            toastBody.textContent = message;
          }
          toastEl.classList.remove('hidden');
          setTimeout(() => {
            toastEl.classList.add('hidden');
          }, 3000);
        }
      }

      // Skip button
      document.getElementById('skip-btn').addEventListener('click', function() {
        // Store empty activities and proceed to payment
        fetch('{{ route("booking.storeStaycationActivities") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ activities: [] })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = '{{ route("booking.staycationPayment") }}';
          } else {
            showToast('Error saving. Please try again.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error saving. Please try again.', 'error');
        });
      });

      // Confirm button
      document.getElementById('confirm-btn').addEventListener('click', function() {
        // Validation: Check if activities are selected
        if (selectedActivities.length === 0) {
          showToast('Please select at least one activity or skip to proceed.', 'error');
          return;
        }

        // Confirmation dialog
        const totalCost = document.getElementById('total-cost')?.textContent || '₱ 0.00';
        const grandTotal = document.getElementById('grand-total')?.textContent || '₱ 0.00';

        if (!confirm(`Are you sure you want to proceed with ${selectedActivities.length} selected activities?\n\nActivities Total: ${totalCost}\nGrand Total: ${grandTotal}\n\nYou will be taken to payment next.`)) {
          return;
        }

        // Show loading state
        const confirmBtn = document.getElementById('confirm-btn');
        if (confirmBtn) {
          confirmBtn.disabled = true;
          confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }

        // Store selected activities in session and proceed
        fetch('{{ route("booking.storeStaycationActivities") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ activities: selectedActivities })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = '{{ route("booking.staycationPayment") }}';
          } else {
            // Reset button
            if (confirmBtn) {
              confirmBtn.disabled = false;
              confirmBtn.innerHTML = 'Confirm and Proceed';
            }
            showToast('Error saving activities. Please try again.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          // Reset button
          if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm and Proceed';
          }
          showToast('Network error. Please check your connection and try again.', 'error');
        });
      });
    });
  </script>
</body>
</html>
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
    $nights = $diff->days;
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

// Calculate accommodation total for JavaScript (matching backend logic)
$accommodationTotal = collect($booking['accommodations'] ?? [])->sum(function($acc) use ($nights) {
    if (!empty($acc) && !empty($acc['staycation'])) {
        $price = $acc['staycation']['price'];
        $quantity = $acc['guests'] ?? 1;
        $isBYOT = str_contains(strtolower($acc['staycation']['name']), 'byot');
        $isCarCamping = str_contains(strtolower($acc['staycation']['name']), 'car camping');
        if ($isBYOT || $isCarCamping) {
            $price = $price * $quantity;
        }
        return $price * $nights;
    }
    return 0;
});
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

<div class="pt-6 md:pt-12" data-accommodation-total="{{ $accommodationTotal }}" data-total-guests="{{ $totalGuests }}" data-nights="{{ $nights }}">
    {{-- Header --}}
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Select Staycation Activities</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-gray-500">Enhance your staycation experience with optional activities</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mx-0 md:mx-10 py-6">
        {{-- Main content --}}
        <div class="lg:col-span-2">
            <div class="flex flex-col">
                {{-- Step indicator --}}
                <small class="text-gray-500 text-sm mb-2">
                    Step 2 of 3: Select Activities
                </small>

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
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
                    {{-- Activities List --}}
                    <section aria-labelledby="activities-heading">
                        <h2 id="activities-heading" class="sr-only">Available Activities</h2>

                        {{-- Pre-defined Activities --}}
                        <div class="mb-8">
                          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pre-defined Activities</h3>
                          <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($activities as $activity)
                            <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 hover:shadow-md transition-shadow">
                              <div class="flex items-start space-x-3">
                                <input
                                  type="checkbox"
                                  id="activity-{{ $activity['id'] }}"
                                  name="activities[]"
                                  value="{{ $activity['id'] }}"
                                  class="activity-checkbox mt-1 text-[#8f5d54] focus:ring-[#8f5d54]"
                                  data-price="{{ $activity['price'] }}"
                                  data-name="{{ $activity['name'] }}"
                                >
                                <div class="flex-1">
                                  <label for="activity-{{ $activity['id'] }}" class="cursor-pointer">
                                    <h4 class="font-semibold text-gray-900">{{ $activity['name'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $activity['description'] }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                      <span class="text-sm text-gray-500">
                                        <i class="bi bi-clock mr-1"></i>{{ $activity['duration'] }}
                                      </span>
                                      <span class="font-semibold text-[#8f5d54]">₱{{ number_format($activity['price'], 2) }}</span>
                                    </div>
                                    <span class="inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                      {{ $activity['availability'] }}
                                    </span>
                                  </label>
                                </div>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </div>

                    </section>
                </div>
            </div>
        </div>

        {{-- Sidebar: booking summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Booking Summary</h3>

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
                        <div class="mt-2 space-y-1">
                            @if(!empty($booking['accommodations']))
                                @foreach($booking['accommodations'] as $index => $accommodation)
                                    @if(!empty($accommodation) && !empty($accommodation['staycation']))
                                        <div class="text-xs text-gray-600">
                                            ACCOMMODATION {{ $index + 1 }}: {{ $accommodation['staycation']['name'] }} - ₱{{ number_format($accommodation['staycation']['price']) }}/night ({{ $accommodation['guests'] }} guests)
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold pt-2 border-t border-gray-200">
                            <span>Accommodation Total:</span>
                            <span>₱ {{ number_format($accommodationTotal) }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-6">Please select your dates to see the booking summary.</p>
                @endif

                <hr class="my-4">

                <h3 class="text-lg font-bold text-gray-900 mb-4">Selected Activities</h3>

                <div id="selected-activities" class="space-y-3 mb-4">
                  <!-- Selected activities will be added here -->
                </div>

                <div class="border-t border-gray-200 pt-4">
                  <div class="flex justify-between items-center text-lg font-bold">
                    <span>Activities Total:</span>
                    <span id="total-cost">₱ 0.00</span>
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                  <div class="flex justify-between items-center text-xl font-bold text-[#8f5d54]">
                    <span>Grand Total:</span>
                    <span id="grand-total">₱ {{ number_format($accommodationTotal) }}</span>
                  </div>
                </div>

                <div class="mt-6 space-y-3">
                  <button
                    type="button"
                    id="skip-btn"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                  >
                    Skip
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
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast fixed bottom-4 right-4 flex items-center text-white bg-emerald-600 border-0 rounded-lg shadow-lg px-4 py-3 hidden"
     role="alert" aria-live="assertive" aria-atomic="true">
   <div class="flex items-center w-full">
      <div class="toast-body text-sm flex-1"></div>
      <button type="button"
              class="ml-3 text-white text-xs font-semibold uppercase tracking-wide btn-close"
              data-bs-dismiss="toast"
              aria-label="Close">
         ✕
      </button>
   </div>
</div>

  {{-- Footer --}}
  <x-footer />

  {{-- Temporary inline font helpers --}}
  <style>
    .font-vila-zilka {
      font-family: 'Vila Zilka Serif', ui-serif, Georgia, 'Times New Roman', serif;
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

    // Handle activity checkboxes
    document.querySelectorAll('.activity-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const activityId = this.value;
        const activityName = this.dataset.name;
        const activityPrice = parseFloat(this.dataset.price);

        if (this.checked) {
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
      });
    });


      // Update summary display
      function updateSummary() {
        const container = document.getElementById('selected-activities');
        const totalElement = document.getElementById('total-cost');
        const grandTotalElement = document.getElementById('grand-total');
        const confirmBtn = document.getElementById('confirm-btn');

        container.innerHTML = '';

        if (selectedActivities.length === 0) {
          container.innerHTML = '<p class="text-sm text-gray-500">No activities selected</p>';
          confirmBtn.disabled = true;
        } else {
          selectedActivities.forEach(activity => {
            const item = document.createElement('div');
            item.className = 'flex justify-between items-start p-2 bg-white rounded border';
            item.innerHTML = `
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">${activity.name}</p>
                <div class="mt-2">
                  <label class="block text-xs text-gray-600 mb-1">Number of Guests</label>
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
            showToast('Error saving activities. Please try again.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error saving activities. Please try again.', 'error');
        });
      });
    });
  </script>
</body>
</html>
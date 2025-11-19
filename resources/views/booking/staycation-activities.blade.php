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
@endphp

<body class="bg-gray-50 font-sans antialiased min-h-screen">
  {{-- Header --}}
  <x-header active="staycation-activities"/>

  {{-- Progress Indicator --}}
  <div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
        {{-- Step 1: Booking --}}
        <div class="flex flex-col items-center text-green-600">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            <i class="bi bi-check-circle-fill text-sm"></i>
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Booking</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 2: Activities (current) --}}
        <div class="flex flex-col items-center text-[#8f5d54]">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#8f5d54] text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            2
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Activities</span>
        </div>

        <div class="flex-1 h-px bg-gray-300"></div>

        {{-- Step 3: Payment --}}
        <div class="flex flex-col items-center text-gray-400">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
            3
          </div>
          <span class="text-xs sm:text-sm font-medium text-center">Payment</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Activities Selection --}}
  <main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
        {{-- Title --}}
        <header class="mb-6 sm:mb-8 text-center">
          <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
            CalaiaPH Staycation Booking
          </p>
          <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-[#8f5d54] font-vila-zilka">
            Select or Add Activities
          </h1>
          <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto px-2">
            Enhance your staycation experience with these optional activities. Choose from our curated experiences or add your own custom activity.
          </p>
        </header>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] items-start">
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

            {{-- Add Custom Activity --}}
            <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="bi bi-plus-circle mr-2 text-[#8f5d54]"></i>
                Add Custom Activity
              </h3>

              <div class="space-y-4">
                <div>
                  <label for="custom_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Activity Name
                  </label>
                  <input
                    type="text"
                    id="custom_name"
                    name="custom_name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                    placeholder="e.g., Private Yoga Session"
                  >
                </div>

                <div>
                  <label for="custom_description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                  </label>
                  <textarea
                    id="custom_description"
                    name="custom_description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                    placeholder="Brief description of the activity"
                  ></textarea>
                </div>

                <div>
                  <label for="custom_cost" class="block text-sm font-medium text-gray-700 mb-2">
                    Estimated Cost (₱)
                  </label>
                  <input
                    type="number"
                    id="custom_cost"
                    name="custom_cost"
                    min="0"
                    step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                    placeholder="0.00"
                  >
                </div>

                <button
                  type="button"
                  id="add-custom-btn"
                  class="inline-flex items-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                >
                  <i class="bi bi-plus"></i>
                  Add to Selection
                </button>
              </div>
            </div>
          </section>

          {{-- Summary Sidebar --}}
          <section
            aria-labelledby="summary-heading"
            class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4 order-2 lg:order-1"
          >
            <h2 id="summary-heading" class="text-base sm:text-lg font-semibold text-gray-900 mb-4">
              Selected Activities
            </h2>

            <div id="selected-activities" class="space-y-3 mb-4">
              <!-- Selected activities will be added here -->
            </div>

            <div class="border-t border-gray-200 pt-4">
              <div class="flex justify-between items-center text-lg font-semibold">
                <span>Total:</span>
                <span id="total-cost" class="text-[#8f5d54]">₱0.00</span>
              </div>
            </div>

            <div class="mt-6 space-y-3">
              <button
                type="button"
                onclick="window.location.href = '{{ $backUrl }}';"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
              >
                <i class="bi bi-arrow-left"></i>
                Back
              </button>

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
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                disabled
              >
                Confirm and Proceed
                <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </section>
        </div>
      </div>
    </div>
  </main>

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

      // Handle pre-defined activity checkboxes
      document.querySelectorAll('.activity-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          const activityId = this.value;
          const activityName = this.dataset.name;
          const activityPrice = parseFloat(this.dataset.price);

          if (this.checked) {
            selectedActivities.push({ id: activityId, name: activityName, price: activityPrice, type: 'predefined' });
            totalCost += activityPrice;
          } else {
            const index = selectedActivities.findIndex(a => a.id === activityId && a.type === 'predefined');
            if (index > -1) {
              totalCost -= selectedActivities[index].price;
              selectedActivities.splice(index, 1);
            }
          }

          updateSummary();
        });
      });

      // Handle add custom activity
      document.getElementById('add-custom-btn').addEventListener('click', function() {
        const name = document.getElementById('custom_name').value.trim();
        const description = document.getElementById('custom_description').value.trim();
        const cost = parseFloat(document.getElementById('custom_cost').value) || 0;

        if (!name || !description || cost <= 0) {
          alert('Please fill in all fields with valid values.');
          return;
        }

        selectedActivities.push({
          id: 'custom-' + Date.now(),
          name: name,
          price: cost,
          type: 'custom',
          description: description
        });
        totalCost += cost;

        // Clear form
        document.getElementById('custom_name').value = '';
        document.getElementById('custom_description').value = '';
        document.getElementById('custom_cost').value = '';

        updateSummary();
      });

      // Update summary display
      function updateSummary() {
        const container = document.getElementById('selected-activities');
        const totalElement = document.getElementById('total-cost');
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
                ${activity.type === 'custom' ? `<p class="text-xs text-gray-500">${activity.description}</p>` : ''}
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-[#8f5d54]">₱${activity.price.toFixed(2)}</span>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeActivity('${activity.id}')">
                  <i class="bi bi-x"></i>
                </button>
              </div>
            `;
            container.appendChild(item);
          });
          confirmBtn.disabled = false;
        }

        totalElement.textContent = `₱${totalCost.toFixed(2)}`;
      }

      // Remove activity
      window.removeActivity = function(id) {
        const index = selectedActivities.findIndex(a => a.id === id);
        if (index > -1) {
          totalCost -= selectedActivities[index].price;
          selectedActivities.splice(index, 1);
          updateSummary();
        }
      };

      // Skip button
      document.getElementById('skip-btn').addEventListener('click', function() {
        // Proceed to payment without activities
        window.location.href = '{{ route("booking.staycationPayment") }}';
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
            alert('Error saving activities. Please try again.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error saving activities. Please try again.');
        });
      });
    });
  </script>
</body>
</html>
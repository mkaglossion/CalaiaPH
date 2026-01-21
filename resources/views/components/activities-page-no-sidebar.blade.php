{{-- resources/views/components/activities-page-no-sidebar.blade.php --}}
@props([
    'pretitle'          => '',
    'title'             => 'Select or Add Activities',
    'subtitle'          => '',
    'backRoute',
    'paymentRoute',
    'storeRoute',
    'activitiesEndpoint' => '/api/activities',
    'totalGuests'       => 1,
    // false = start at 0 guests (general booking)
    // true  = auto-fill guests = totalGuests (dayvisit, special-event)
    'autoGuests'        => false,
    // Staycation specific props
    'isStaycation'      => false,
    'accommodationTotal' => 0,
    'grandTotalElement' => null,
])

<div
    id="activities-root"
    data-total-guests="{{ $totalGuests }}"
    data-payment-url="{{ $paymentRoute }}"
    data-store-url="{{ $storeRoute }}"
    data-activities-endpoint="{{ $activitiesEndpoint }}"
    data-auto-guests="{{ $autoGuests ? '1' : '0' }}"
    data-is-staycation="{{ $isStaycation ? '1' : '0' }}"
    data-accommodation-total="{{ $accommodationTotal }}"
>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
        {{-- Title --}}
        <header class="mb-6 sm:mb-8 text-center">
            @if($pretitle)
                <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
                    {{ $pretitle }}
                </p>
            @endif

            <h1 class="text-2xl font-bold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">
                {{ $title }}
            </h1>

            @if($subtitle)
                <p class="mt-2 text-xs text-gray-500 max-w-2xl mx-auto px-2"
                   style="font-family: 'Gotham Book', sans-serif;">
                    {{ $subtitle }}
                </p>
            @endif
        </header>

        {{-- Activities List --}}
        <section aria-labelledby="activities-heading">
            <h2 id="activities-heading" class="sr-only">Available Activities</h2>

            {{-- Curated Activities --}}
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4"
                    style="font-family: 'Gotham Book', sans-serif;">
                    Curated Activities
                </h3>
                <div id="activities-container" class="grid gap-4 sm:grid-cols-2">
                    <!-- Activities will be loaded here -->
                </div>
            </div>
        </section>
    </div>

    {{-- Toast (shared) --}}
    <div class="toast fixed bottom-4 right-4 z-50 items-center text-white bg-emerald-600 border-0 rounded-lg shadow-lg px-4 py-3 hidden"
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
</div>

{{-- Shared JS just for this component --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('activities-root');
    if (!root) return;

    const selectedActivities = [];
    let totalCost = 0;

    const totalGuests = parseInt(root.dataset.totalGuests) || 1;
    const activitiesEndpoint = root.dataset.activitiesEndpoint;
    const paymentUrl = root.dataset.paymentUrl;
    const storeUrl = root.dataset.storeUrl;
    const autoGuests = root.dataset.autoGuests === '1';
    const isStaycation = root.dataset.isStaycation === '1';
    const accommodationTotal = parseFloat(root.dataset.accommodationTotal) || 0;
    
    // Get food total from grand-total element's data attribute
    const grandTotalElement = document.getElementById('grand-total');
    const foodTotal = parseFloat(grandTotalElement?.dataset.foodTotal) || 0;

    // Helpers
    function showToast(message) {
        const toastEl = root.querySelector('.toast') || document.querySelector('.toast');
        if (toastEl) {
            const toastBody = toastEl.querySelector('.toast-body');
            if (toastBody) toastBody.textContent = message;
            toastEl.classList.remove('hidden');
            toastEl.classList.add('flex');
            setTimeout(() => {
                toastEl.classList.remove('flex');
                toastEl.classList.add('hidden');
            }, 3000);
        }
    }

    function updateSummary() {
        const container = document.querySelector('#selected-activities');
        const totalElement = document.querySelector('#total-cost');
        const activitiesTotalElement = document.querySelector('#activities-total');
        const confirmBtn = document.querySelector('#confirm-btn');

        // Update total cost display (if element exists)
        if (totalElement) {
            totalElement.textContent = `₱${totalCost.toFixed(2)}`;
        }

        // Update activities total in sidebar (if element exists)
        if (activitiesTotalElement) {
            activitiesTotalElement.textContent = `₱${totalCost.toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
        }

        // Update grand total for staycation
        if (isStaycation) {
            const grandTotalElement = document.getElementById('grand-total');
            if (grandTotalElement) {
                const grandTotal = accommodationTotal + foodTotal + totalCost;
                grandTotalElement.textContent = `₱${grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
            }
        }

        // Update selected activities list (if container exists)
        if (container) {
            container.innerHTML = '';

            if (selectedActivities.length === 0) {
                container.innerHTML =
                    '<p class="text-xs text-gray-500" style="font-family: \'Gotham Book\', sans-serif;">No activities selected</p>';
            } else {
                selectedActivities.forEach(activity => {
                    const item = document.createElement('div');
                    item.className = 'flex justify-between items-start p-2 bg-white rounded border';
                    const minGuests = autoGuests ? 1 : 0;

                    item.innerHTML = `
                      <div class="flex-1">
                        <p class="text-xs font-medium text-gray-900" style="font-family: 'Gotham Book', sans-serif;">${activity.name}</p>
                        <p class="text-xs text-gray-500 mt-1">₱${activity.price.toLocaleString()} / pax</p>
                        <div class="mt-2">
                          <label class="block text-xs text-gray-600 mb-1" style="font-family: 'Gotham Book', sans-serif;">Number of Guests</label>
                          <div class="flex items-center gap-2">
                            <button type="button" class="decrease-guest-btn w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" data-activity-id="${activity.id}">-</button>
                            <input type="number"
                              class="activity-guests w-16 text-center border border-gray-300 rounded-md py-1 text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent"
                              value="${activity.guests}"
                              min="1"
                              data-activity-id="${activity.id}">
                            <button type="button" class="increase-guest-btn w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" data-activity-id="${activity.id}">+</button>
                          </div>
                        </div>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-[#8f5d54]">₱${(activity.price * activity.guests).toLocaleString()}</span>
                        <button type="button" class="text-red-500 hover:text-red-700 remove-activity-btn" data-activity-id="${activity.id}">
                          <i class="bi bi-x"></i>
                        </button>
                      </div>
                    `;
                    container.appendChild(item);

                    // guest input handler - both input and change events for real-time updates
                    const guestInput = item.querySelector('.activity-guests');
                    const updateComputation = function() {
                        let newGuests = parseInt(this.value);
                        if (isNaN(newGuests) || newGuests < 1) newGuests = 1;
                        
                        // Update the input value to the validated value
                        this.value = newGuests;

                        const index = selectedActivities.findIndex(a => a.id === activity.id);
                        if (index > -1) {
                            const oldGuests = selectedActivities[index].guests;
                            selectedActivities[index].guests = newGuests;
                            totalCost += (newGuests - oldGuests) * activity.price;
                            
                            // Update displays
                            const totalElement = document.querySelector('#total-cost');
                            const activitiesTotalElement = document.querySelector('#activities-total');
                            const grandTotalElement = document.getElementById('grand-total');
                            
                            if (totalElement) {
                                totalElement.textContent = `₱${totalCost.toFixed(2)}`;
                            }
                            if (activitiesTotalElement) {
                                activitiesTotalElement.textContent = `₱${Math.round(totalCost).toLocaleString()}`;
                            }
                            if (isStaycation && grandTotalElement) {
                                const grandTotal = accommodationTotal + foodTotal + totalCost;
                                grandTotalElement.textContent = `₱${Math.round(grandTotal).toLocaleString()}`;
                            }
                            
                            // update only this row's value
                            item.querySelector('span.text-sm.font-semibold').textContent =
                                `₱${Math.round(activity.price * newGuests).toLocaleString()}`;
                        }
                    };
                    
                    guestInput.addEventListener('input', updateComputation);
                    guestInput.addEventListener('change', updateComputation);
                    
                    // Add +/- button listeners
                    const decreaseBtn = item.querySelector('.decrease-guest-btn');
                    const increaseBtn = item.querySelector('.increase-guest-btn');
                    
                    decreaseBtn.addEventListener('click', function() {
                        let currentValue = parseInt(guestInput.value);
                        if (currentValue > 1) {
                            guestInput.value = currentValue - 1;
                            updateComputation.call(guestInput);
                        }
                    });
                    
                    increaseBtn.addEventListener('click', function() {
                        let currentValue = parseInt(guestInput.value);
                        guestInput.value = currentValue + 1;
                        updateComputation.call(guestInput);
                    });
                });
            }
        }

        // Update confirm button state (if exists)
        if (confirmBtn) {
            confirmBtn.disabled = selectedActivities.length === 0;
        }
    }

    function loadActivities() {
        fetch(activitiesEndpoint)
            .then(response => response.json())
            .then(activities => {
                const container = root.querySelector('#activities-container');
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
                                    <span class="text-xs font-semibold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">₱${parseFloat(activity.price).toFixed(2)} / pax</span>
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
                showToast('Error loading activities. Please refresh the page.');
            });
    }

    // Checkbox handler (event delegation but limited to root)
    root.addEventListener('change', function(e) {
        if (!e.target.classList.contains('activity-checkbox')) return;

        const checkbox = e.target;
        const activityId = checkbox.value;
        const activityName = checkbox.dataset.name;
        const activityPrice = parseFloat(checkbox.dataset.price);

        if (checkbox.checked) {
            const guests = autoGuests ? totalGuests : 0;
            selectedActivities.push({ id: activityId, name: activityName, price: activityPrice, guests, type: 'predefined' });
            if (autoGuests) {
                totalCost += activityPrice * totalGuests;
            }
            showToast(`${activityName} added to selection`);
        } else {
            const index = selectedActivities.findIndex(a => a.id === activityId);
            if (index > -1) {
                totalCost -= selectedActivities[index].price * selectedActivities[index].guests;
                showToast(`${selectedActivities[index].name} removed from selection`);
                selectedActivities.splice(index, 1);
            }
        }

        updateSummary();
    });

    // Remove via button
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.remove-activity-btn')) return;
        const btn = e.target.closest('.remove-activity-btn');
        const id = btn.dataset.activityId;
        const index = selectedActivities.findIndex(a => a.id === id);
        if (index > -1) {
            const activity = selectedActivities[index];
            totalCost -= activity.price * activity.guests;
            selectedActivities.splice(index, 1);
            showToast(`${activity.name} removed from selection`);
            updateSummary();
        }
    });

    // Skip button: for staycation, POST empty activities first
    const skipBtn = document.querySelector('#skip-btn');
    if (skipBtn) {
        skipBtn.addEventListener('click', function() {
            if (isStaycation) {
                fetch(storeUrl, {
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
                        window.location.href = paymentUrl;
                    } else {
                        showToast('Error saving. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error saving. Please try again.');
                });
            } else {
                window.location.href = paymentUrl;
            }
        });
    }

    // Confirm button: show confirm dialog
    const confirmBtn = document.querySelector('#confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (selectedActivities.length === 0) {
                showToast('Please select at least one activity.');
                return;
            }
            const activitiesTotal = totalCost;
            const grandTotal = accommodationTotal + totalCost;
            const message = `Are you sure you want to proceed with ${selectedActivities.length} selected activities?\n\nActivities Total: ₱${activitiesTotal.toFixed(2)}\nGrand Total: ₱${grandTotal.toFixed(2)}\n\nYou will be taken to payment next.`;
            if (!confirm(message)) {
                return;
            }
            fetch(storeUrl, {
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
                    window.location.href = paymentUrl + '?activities=' + encodeURIComponent(btoa(JSON.stringify(selectedActivities)));
                } else {
                    showToast('Error saving activities. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error saving activities. Please try again.');
            });
        });
    }

    // initial load
    loadActivities();
});
</script>

{{-- Alpine.js --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

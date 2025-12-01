{{-- resources/views/components/activities-page.blade.php --}}
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

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] items-start">
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

            {{-- Summary Sidebar --}}
            <section
                aria-labelledby="summary-heading"
                class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4 order-2 lg:order-1"
            >
                <h2 id="summary-heading" class="text-2xl font-semibold text-gray-900 mb-4"
                    style="font-family: 'Gotham Book', sans-serif;">
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
                    <a
                        href="{{ $backRoute }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back to Details
                    </a>

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

    {{-- Toast (shared) --}}
    <div class="toast fixed bottom-4 right-4 items-center text-white bg-emerald-600 border-0 rounded-lg shadow-lg px-4 py-3 hidden"
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
                showToast('Error loading activities. Please refresh the page.');
            });
    }

    function updateSummary() {
        const container = root.querySelector('#selected-activities');
        const totalElement = root.querySelector('#total-cost');
        const confirmBtn = root.querySelector('#confirm-btn');

        container.innerHTML = '';

        if (selectedActivities.length === 0) {
            container.innerHTML =
                '<p class="text-xs text-gray-500" style="font-family: \'Gotham Book\', sans-serif;">No activities selected</p>';
            confirmBtn.disabled = true;
        } else {
            selectedActivities.forEach(activity => {
                const item = document.createElement('div');
                item.className = 'flex justify-between items-start p-2 bg-white rounded border';
                const minGuests = autoGuests ? 1 : 0;

                item.innerHTML = `
                  <div class="flex-1">
                    <p class="text-xs font-medium text-gray-900" style="font-family: 'Gotham Book', sans-serif;">${activity.name}</p>
                    <div class="mt-2">
                      <label class="block text-xs text-gray-600 mb-1" style="font-family: 'Gotham Book', sans-serif;">Number of Guests</label>
                      <input type="number"
                        class="activity-guests w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent"
                        value="${activity.guests}"
                        min="${minGuests}"
                        max="${totalGuests}"
                        data-activity-id="${activity.id}">
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-[#8f5d54]">₱${(activity.price * activity.guests).toFixed(2)}</span>
                    <button type="button" class="text-red-500 hover:text-red-700 remove-activity-btn" data-activity-id="${activity.id}">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                `;
                container.appendChild(item);

                // guest input handler
                const guestInput = item.querySelector('.activity-guests');
                guestInput.addEventListener('input', function() {
                    let newGuests = parseInt(this.value);
                    if (isNaN(newGuests)) newGuests = autoGuests ? 1 : 0;
                    if (newGuests < (autoGuests ? 1 : 0)) newGuests = autoGuests ? 1 : 0;
                    if (newGuests > totalGuests) newGuests = totalGuests;

                    const index = selectedActivities.findIndex(a => a.id === activity.id);
                    if (index > -1) {
                        const oldGuests = selectedActivities[index].guests;
                        selectedActivities[index].guests = newGuests;
                        totalCost += (newGuests - oldGuests) * activity.price;
                        totalElement.textContent = `₱${totalCost.toFixed(2)}`;
                        // update only this row's value
                        item.querySelector('span.text-sm.font-semibold').textContent =
                            `₱${(activity.price * newGuests).toFixed(2)}`;
                    }
                });
            });
            confirmBtn.disabled = false;
        }

        totalElement.textContent = `₱${totalCost.toFixed(2)}`;

        // Update grand total for staycation
        if (isStaycation) {
            const grandTotalElement = document.getElementById('grand-total');
            if (grandTotalElement) {
                const grandTotal = accommodationTotal + totalCost;
                grandTotalElement.textContent = `₱${grandTotal.toFixed(2)}`;
            }
        }
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
    root.addEventListener('click', function(e) {
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
    const skipBtn = root.querySelector('#skip-btn');
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

    // Confirm button: POST then redirect
    const confirmBtn = root.querySelector('#confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
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
                    window.location.href = paymentUrl;
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
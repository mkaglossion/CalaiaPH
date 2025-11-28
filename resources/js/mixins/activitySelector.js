/**
 * Activity Selector Mixin
 * Reusable JavaScript functionality for activity selection pages
 */
export const activitySelectorMixin = {
    data() {
        return {
            selectedActivities: [],
            totalCost: 0,
            totalGuests: 1,
            apiUrl: '/api/activities',
            containerId: 'activities-container',
            selectedContainerId: 'selected-activities',
            totalElementId: 'total-cost',
            confirmButtonId: 'confirm-btn',
            skipButtonId: 'skip-btn'
        }
    },

    mounted() {
        this.initializeActivitySelector();
    },

    methods: {
        initializeActivitySelector() {
            // Get total guests from data attribute or default
            this.totalGuests = parseInt(document.body.dataset.totalGuests) ||
                              parseInt(document.querySelector('[data-total-guests]')?.dataset.totalGuests) || 1;

            // Load activities from API
            this.loadActivities();

            // Set up event listeners
            this.setupEventListeners();
        },

        async loadActivities() {
            try {
                const response = await fetch(this.apiUrl);
                const activities = await response.json();
                this.renderActivities(activities);
            } catch (error) {
                console.error('Error loading activities:', error);
                this.showToast('Error loading activities. Please refresh the page.', 'error');
            }
        },

        renderActivities(activities) {
            const container = document.getElementById(this.containerId);
            if (!container) return;

            container.innerHTML = '';

            activities.forEach(activity => {
                const activityDiv = document.createElement('div');
                activityDiv.className = 'bg-gray-50 rounded-xl border border-gray-100 p-4 hover:shadow-md transition-shadow';
                activityDiv.innerHTML = this.getActivityTemplate(activity);
                container.appendChild(activityDiv);
            });
        },

        getActivityTemplate(activity) {
            return `
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
        },

        setupEventListeners() {
            // Handle activity checkboxes
            document.addEventListener('change', (e) => {
                if (e.target.classList.contains('activity-checkbox')) {
                    this.handleActivityToggle(e.target);
                }
            });

            // Handle guest number changes
            document.addEventListener('input', (e) => {
                if (e.target.classList.contains('activity-guests')) {
                    this.handleGuestChange(e.target);
                }
            });
        },

        handleActivityToggle(checkbox) {
            const activityId = checkbox.value;
            const activityName = checkbox.dataset.name;
            const activityPrice = parseFloat(checkbox.dataset.price);

            if (checkbox.checked) {
                this.selectedActivities.push({
                    id: activityId,
                    name: activityName,
                    price: activityPrice,
                    guests: this.totalGuests,
                    type: 'predefined'
                });
                this.totalCost += activityPrice * this.totalGuests;
                this.showToast(`${activityName} added to selection`, 'success');
            } else {
                const index = this.selectedActivities.findIndex(a => a.id === activityId && a.type === 'predefined');
                if (index > -1) {
                    this.totalCost -= this.selectedActivities[index].price * this.selectedActivities[index].guests;
                    this.selectedActivities.splice(index, 1);
                    this.showToast(`${activityName} removed from selection`, 'info');
                }
            }

            this.updateSummary();
        },

        handleGuestChange(input) {
            const activityId = input.dataset.activityId;
            const newGuests = parseInt(input.value) || 1;
            const index = this.selectedActivities.findIndex(a => a.id === activityId);

            if (index > -1) {
                const oldGuests = this.selectedActivities[index].guests;
                this.selectedActivities[index].guests = newGuests;
                this.totalCost += (newGuests - oldGuests) * this.selectedActivities[index].price;
                this.updateSummary();
            }
        },

        updateSummary() {
            const container = document.getElementById(this.selectedContainerId);
            const totalElement = document.getElementById(this.totalElementId);
            const confirmBtn = document.getElementById(this.confirmButtonId);

            if (!container || !totalElement || !confirmBtn) return;

            container.innerHTML = '';

            if (this.selectedActivities.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-500" style="font-family: \'Gotham Book\', sans-serif;">No activities selected</p>';
                confirmBtn.disabled = true;
            } else {
                this.selectedActivities.forEach(activity => {
                    const item = document.createElement('div');
                    item.className = 'flex justify-between items-start p-2 bg-white rounded border';
                    item.innerHTML = this.getSelectedActivityTemplate(activity);
                    container.appendChild(item);

                    // Add guest input listener
                    const guestInput = item.querySelector('.activity-guests');
                    if (guestInput) {
                        guestInput.addEventListener('input', (e) => this.handleGuestChange(e.target));
                    }
                });
                confirmBtn.disabled = false;
            }

            totalElement.textContent = `₱${this.totalCost.toFixed(2)}`;
        },

        getSelectedActivityTemplate(activity) {
            return `
                <div class="flex-1">
                    <p class="text-xs font-medium text-gray-900" style="font-family: 'Gotham Book', sans-serif;">${activity.name}</p>
                    <div class="mt-2">
                        <label class="block text-xs text-gray-600 mb-1" style="font-family: 'Gotham Book', sans-serif;">Number of Guests</label>
                        <input type="number" class="activity-guests w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent"
                               value="${activity.guests}" min="1" max="${this.totalGuests}" data-activity-id="${activity.id}">
                    </div>
                    ${activity.type === 'custom' ? `<p class="text-xs text-gray-500 mt-1" style="font-family: 'Gotham Book', sans-serif;">${activity.description}</p>` : ''}
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-[#8f5d54]">₱${(activity.price * activity.guests).toFixed(2)}</span>
                    <button type="button" class="text-red-500 hover:text-red-700" onclick="removeActivity('${activity.id}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        },

        showToast(message, type = 'info') {
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
        },

        removeActivity(activityId) {
            const index = this.selectedActivities.findIndex(a => a.id === activityId);
            if (index > -1) {
                const activityName = this.selectedActivities[index].name;
                this.totalCost -= this.selectedActivities[index].price * this.selectedActivities[index].guests;
                this.selectedActivities.splice(index, 1);
                this.updateSummary();
                this.showToast(`${activityName} removed from selection`, 'info');
            }
        }
    }
};

// Global function for activity removal (called from HTML)
window.removeActivity = function(id) {
    // This will be overridden by the component using the mixin
    console.log('Remove activity:', id);
};
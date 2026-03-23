{{-- Dining Reservation Modal --}}
<div
  id="diningReservationModal"
  x-data="diningReservationModal()"
  x-cloak
  x-show="modalOpen"
  :hidden="!modalOpen"
  :inert="!modalOpen"
  @keydown.escape.window="closeModal()"
  @open-dining-modal.window="openModal($event.detail)"
  class="fixed inset-0 z-9999 flex items-center justify-center p-3 sm:p-6 md:p-8 bg-black/50 backdrop-blur-sm transition-opacity duration-300 overflow-y-auto"
  role="dialog"
  aria-modal="true"
>
  {{-- Toast Notification --}}
  <div class="fixed top-4 right-4 z-10000 pointer-events-none">
    <div
      x-show="toastMessage"
      :class="{
        'bg-red-100 border-red-400 text-red-800': toastType === 'error',
        'bg-green-100 border-green-400 text-green-800': toastType === 'success',
        'bg-yellow-100 border-yellow-400 text-yellow-800': toastType === 'warning',
      }"
      class="border-l-4 p-4 rounded shadow-lg pointer-events-auto animate-slide-in"
      x-transition
    >
      <p class="text-sm font-semibold" x-text="toastMessage"></p>
    </div>
  </div>
  <div
    @click.away="closeModal()"
    class="w-full max-w-4xl bg-white rounded-xl sm:rounded-2xl shadow-2xl overflow-hidden relative my-6 sm:my-8"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
  >
    {{-- Close Button --}}
    <button
      type="button"
      @click="closeModal()"
      class="absolute top-3 right-3 sm:top-4 sm:right-4 p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full z-10"
      aria-label="Close"
    >
      <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>

    <div class="p-4 sm:p-6 md:p-8 max-h-[85vh] overflow-y-auto">
      <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-[#97655A] mb-6 sm:mb-8 pr-8" x-text="foodName"></h2>

      <form @submit.prevent="submitReservation()" class="space-y-5 sm:space-y-6">
        {{-- Reservation Details Section --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
          {{-- Meal Period --}}
          <div>
            <label for="reservation_meal_period" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Meal Period *</label>
            <select 
              id="reservation_meal_period"
              name="meal_period"
              x-model="form.meal_period" 
              autocomplete="off"
              @change="onMealPeriodChange()"
              class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A] focus:border-transparent"
              required
            >
              <option value="">-- Select Period --</option>
              <option value="breakfast">Breakfast</option>
              <option value="lunch">Lunch</option>
              <option value="snack">Snack</option>
              <option value="dinner">Dinner</option>
              <option value="supper">Supper</option>
            </select>
          </div>

          {{-- Date Picker --}}
          <div>
            <label for="reservation_date" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Date *</label>
            <input 
              id="reservation_date"
              name="reservation_date"
              type="date" 
              x-model="form.reservation_date"
              autocomplete="off"
              :min="today"
              class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A] focus:border-transparent"
              required
            />
          </div>

          {{-- Time Selection --}}
          <div>
            <label for="reservation_time" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Time *</label>
            <select 
              id="reservation_time"
              name="reservation_time"
              x-model="form.reservation_time"
              autocomplete="off"
              :disabled="!form.meal_period"
              class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
              required
            >
              <option value="">-- Select Time --</option>
              <template x-for="time in getAvailableTimes()" :key="time.value">
                <option :value="time.value" x-text="time.label"></option>
              </template>
            </select>
          </div>

          {{-- Guest Count --}}
          <div>
            <label for="reservation_guests" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Total Guests *</label>
            <input 
              id="reservation_guests"
              name="guests"
              type="number" 
              x-model.number="form.adults"
              min="1"
              max="50"
              autocomplete="off"
              class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A]"
              required
            />
          </div>
        </div>

        {{-- Contact Information Section --}}
        <div class="pt-2">
          <h3 class="text-sm font-semibold text-gray-700 mb-4">Contact Information</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            {{-- First Name --}}
            <div>
              <label for="reservation_first_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">First Name *</label>
              <input 
                id="reservation_first_name"
                name="first_name"
                type="text" 
                x-model="form.first_name"
                autocomplete="given-name"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A]"
                required
              />
            </div>

            {{-- Last Name --}}
            <div>
              <label for="reservation_last_name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
              <input 
                id="reservation_last_name"
                name="last_name"
                type="text" 
                x-model="form.last_name"
                autocomplete="family-name"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A]"
                required
              />
            </div>

            {{-- Phone --}}
            <div>
              <label for="reservation_phone" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Phone *</label>
              <div class="flex gap-2 sm:gap-3">
                <div class="px-2 sm:px-3 py-2 sm:py-3 border border-gray-300 rounded-lg bg-gray-50 text-xs sm:text-sm whitespace-nowrap flex items-center" aria-hidden="true">
                  🇵🇭 +63
                </div>
                <input 
                  id="reservation_phone"
                  name="phone"
                  type="tel" 
                  x-model="form.phone"
                  autocomplete="tel-national"
                  placeholder="9691309574"
                  class="flex-1 px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A]"
                  required
                />
              </div>
            </div>

            {{-- Email --}}
            <div>
              <label for="reservation_email" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Email *</label>
              <input 
                id="reservation_email"
                name="email"
                type="email" 
                x-model="form.email"
                @blur="validateEmail()"
                autocomplete="email"
                :class="{
                  'border-red-400 focus:ring-red-500': emailError,
                  'border-gray-300 focus:ring-[#97655A]': !emailError,
                }"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border rounded-lg focus:ring-2 focus:border-transparent transition"
                required
              />
              <p x-show="emailError" class="text-xs text-red-600 mt-1" x-text="emailError"></p>
            </div>
          </div>
        </div>

        {{-- Special Requests --}}
        <div>
          <label for="reservation_special_requests" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Special Requests or Dietary Requirements</label>
          <textarea 
            id="reservation_special_requests"
            name="special_requests"
            x-model="form.special_requests"
            rows="2"
            autocomplete="off"
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#97655A] resize-none"
            placeholder="e.g., Vegetarian menu, nut allergies, etc."
          ></textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-6 sm:pt-8">
          <button 
            type="button"
            @click="closeModal()"
            :disabled="isSubmitting"
            class="flex-1 px-4 sm:px-6 py-3 sm:py-3 text-sm sm:text-base border-2 border-[#97655A] text-[#97655A] rounded-full font-semibold hover:bg-[#97655A] hover:text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
          >
            Cancel
          </button>
          <button 
            type="submit"
            :disabled="isSubmitting || !isFormValid()"
            class="flex-1 px-4 sm:px-6 py-3 sm:py-3 text-sm sm:text-base bg-[#97655A] text-white rounded-full font-semibold hover:bg-[#7a4d45] disabled:bg-gray-400 transition-all disabled:cursor-not-allowed active:scale-95"
          >
            <span x-show="!isSubmitting">✓ Confirm Reservation</span>
            <span x-show="isSubmitting" class="inline-flex items-center justify-center gap-2 w-full">
              <span class="inline-block w-3 h-3 sm:w-4 sm:h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>Processing...</span>
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  @keyframes slideIn {
    from {
      transform: translateX(400px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  .animate-slide-in {
    animation: slideIn 0.3s ease-out;
  }
</style>

<script>
function diningReservationModal() {
  return {
    modalOpen: false,
    isSubmitting: false,
    foodName: '',
    foodId: null,
    foodPrice: 0,
    maxStock: 100,
    maxAdults: 10,
    maxChildren: 5,
    inventoryError: '',
    toastMessage: '',
    toastType: 'error',
    toastTimeout: null,
    today: new Date().toISOString().split('T')[0],
    
    form: {
      meal_period: '',
      reservation_date: '',
      reservation_time: '',
      adults: 1,
      first_name: '',
      last_name: '',
      phone: '',
      email: '',
      special_requests: ''
    },
    emailError: '',

    openModal(button) {
      this.foodName = button.dataset.title || '';
      this.foodId = button.dataset.foodId || null;
      this.foodPrice = parseFloat(button.dataset.price) || 0;
      this.maxStock = parseInt(button.dataset.maxStock) || 100;
      
      // Set max guests based on stock
      this.maxAdults = Math.min(this.maxStock, 10);
      this.maxChildren = Math.min(Math.floor(this.maxStock / 2), 5);
      
      this.resetForm();
      this.modalOpen = true;
    },

    closeModal() {
      this.modalOpen = false;
      this.toastMessage = '';
    },

    showToast(message, type = 'error') {
      this.toastMessage = message;
      this.toastType = type;
      
      if (this.toastTimeout) {
        clearTimeout(this.toastTimeout);
      }
      
      this.toastTimeout = setTimeout(() => {
        this.toastMessage = '';
      }, 4000);
    },

    resetForm() {
      this.form = {
        meal_period: '',
        reservation_date: '',
        reservation_time: '',
        adults: 1,
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        special_requests: ''
      };
      this.emailError = '';
    },

    checkInventory() {
      // Inventory check removed - not needed for simplified form
    },

    validateEmail() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (this.form.email && !emailRegex.test(this.form.email)) {
        this.emailError = 'Please enter a valid email address';
      } else {
        this.emailError = '';
      }
    },

    getAvailableTimes() {
      const timeRanges = {
        breakfast: [
          { value: '06:00', label: '06:00 am' },
          { value: '06:30', label: '06:30 am' },
          { value: '07:00', label: '07:00 am' },
          { value: '07:30', label: '07:30 am' },
          { value: '08:00', label: '08:00 am' },
          { value: '08:30', label: '08:30 am' },
          { value: '09:00', label: '09:00 am' },
          { value: '09:30', label: '09:30 am' },
          { value: '10:00', label: '10:00 am' },
          { value: '10:30', label: '10:30 am' }
        ],
        lunch: [
          { value: '11:00', label: '11:00 am' },
          { value: '11:30', label: '11:30 am' },
          { value: '12:00', label: '12:00 pm' },
          { value: '12:30', label: '12:30 pm' },
          { value: '13:00', label: '01:00 pm' },
          { value: '13:30', label: '01:30 pm' },
          { value: '14:00', label: '02:00 pm' },
          { value: '14:30', label: '02:30 pm' },
          { value: '15:00', label: '03:00 pm' }
        ],
        snack: [
          { value: '15:00', label: '03:00 pm' },
          { value: '15:30', label: '03:30 pm' },
          { value: '16:00', label: '04:00 pm' },
          { value: '16:30', label: '04:30 pm' },
          { value: '17:00', label: '05:00 pm' },
          { value: '17:30', label: '05:30 pm' }
        ],
        dinner: [
          { value: '17:00', label: '05:00 pm' },
          { value: '17:30', label: '05:30 pm' },
          { value: '18:00', label: '06:00 pm' },
          { value: '18:30', label: '06:30 pm' },
          { value: '19:00', label: '07:00 pm' },
          { value: '19:30', label: '07:30 pm' },
          { value: '20:00', label: '08:00 pm' }
        ],
        supper: [
          { value: '19:00', label: '07:00 pm' },
          { value: '19:30', label: '07:30 pm' },
          { value: '20:00', label: '08:00 pm' },
          { value: '20:30', label: '08:30 pm' },
          { value: '21:00', label: '09:00 pm' }
        ]
      };
      
      return timeRanges[this.form.meal_period] || [];
    },

    onMealPeriodChange() {
      this.form.reservation_time = '';
      this.checkInventory();
    },

    isFormValid() {
      return this.form.first_name && this.form.last_name && this.form.phone && 
             this.form.email && this.form.meal_period && 
             this.form.reservation_date && this.form.reservation_time && 
             this.form.adults >= 1 && !this.emailError;
    },

    async submitReservation() {
      if (this.isSubmitting) {
        return;
      }

      if (!this.isFormValid()) {
        Swal.fire({
          title: 'Incomplete Form',
          text: 'Please fill in all required fields correctly.',
          icon: 'warning',
          confirmButtonColor: '#97655A'
        });
        return;
      }

      if (!this.foodId) {
        Swal.fire({
          title: 'Error',
          text: 'No food item selected',
          icon: 'error',
          confirmButtonColor: '#97655A'
        });
        return;
      }

      this.isSubmitting = true;

      // Increased timeout for synchronous email sending (15 seconds)
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 15000);

      try {
        const response = await fetch('{{ route("dining.reserve") }}', {
          method: 'POST',
          signal: controller.signal,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            food_id: parseInt(this.foodId),
            meal_period: this.form.meal_period,
            reservation_date: this.form.reservation_date,
            reservation_time: this.form.reservation_time,
            adults: parseInt(this.form.adults),
            first_name: this.form.first_name,
            last_name: this.form.last_name,
            phone: this.form.phone,
            email: this.form.email,
            special_requests: this.form.special_requests
          })
        });

        clearTimeout(timeoutId);
        const data = await response.json();

        if (response.ok && data.success) {
          const confirmationNumber = 'DR' + String(data.reservation_id).padStart(5, '0');
          
          // Format date from YYYY-MM-DD to "March 20, 2026"
          const dateObj = new Date(this.form.reservation_date + 'T00:00:00');
          const options = { year: 'numeric', month: 'long', day: 'numeric' };
          const formattedDate = dateObj.toLocaleDateString('en-US', options);
          
          Swal.fire({
            title: '✓ Reservation Confirmed!',
            html: `
              <div style="text-align: left; line-height: 1.8;">
                <p style="font-size: 14px; margin-bottom: 20px;">Your dining reservation has been successfully confirmed.</p>
                <div style="background: #f5efe7; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #97655A;">
                  <strong style="color: #97655A; display: block; margin-bottom: 8px;">Confirmation Number:</strong>
                  <div style="font-size: 20px; font-weight: bold; color: #97655A; word-break: break-all; font-family: monospace;">${confirmationNumber}</div>
                </div>
                <div style="background: #f5efe7; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; color: #666;">
                  <strong>${this.form.first_name} ${this.form.last_name}</strong><br>
                  ${this.form.meal_period.charAt(0).toUpperCase() + this.form.meal_period.slice(1)} • ${formattedDate} • ${this.form.reservation_time}<br>
                  ${this.form.adults} Guest${this.form.adults > 1 ? 's' : ''}
                </div>
                <p style="font-size: 13px; color: #666; margin-top: 15px;">
                  <strong>📧 A confirmation email has been sent to:</strong><br>
                  <span style="word-break: break-all;">${this.form.email}</span>
                </p>
              </div>
            `,
            icon: 'success',
            confirmButtonColor: '#97655A',
            confirmButtonText: 'Close',
            allowOutsideClick: false,
            allowEscapeKey: false
          });
          
          this.closeModal();
          this.resetForm();
          this.isSubmitting = false;
        } else {
          clearTimeout(timeoutId);
          if (data.errors) {
            let errorHtml = '<div style="text-align: left;"><ul style="margin: 0; padding-left: 20px;">';
            for (const [field, errors] of Object.entries(data.errors)) {
              errorHtml += `<li style="margin-bottom: 8px;"><strong>${field}:</strong> ${errors.join(', ')}</li>`;
            }
            errorHtml += '</ul></div>';
            Swal.fire({
              title: 'Validation Error',
              html: errorHtml,
              icon: 'error',
              confirmButtonColor: '#97655A'
            });
          } else {
            Swal.fire({
              title: 'Error',
              text: data.message || 'Error submitting reservation',
              icon: 'error',
              confirmButtonColor: '#97655A'
            });
          }
          this.isSubmitting = false;
        }
      } catch (error) {
        clearTimeout(timeoutId);
        
        if (error.name === 'AbortError') {
          Swal.fire({
            title: 'Request Timeout',
            text: 'The request took too long. Please try again.',
            icon: 'error',
            confirmButtonColor: '#97655A'
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: 'Error submitting reservation: ' + error.message,
            icon: 'error',
            confirmButtonColor: '#97655A'
          });
        }
        this.isSubmitting = false;
      }
    }
  };
}

// Global function to open modal
window.openDiningReservationModal = function(button) {
  // Dispatch custom event to modal
  window.dispatchEvent(new CustomEvent('open-dining-modal', {
    detail: button,
    bubbles: true
  }));
};
</script>

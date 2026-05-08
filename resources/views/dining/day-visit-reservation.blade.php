@extends('layouts.app')

@section('title', 'Day Visit Dining Reservation')

@section('content')
<div class="min-h-screen bg-linear-to-br from-amber-50 to-orange-50">
    <!-- Header -->
    <div class="bg-white border-b border-amber-100">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">☀️ Day Visit Dining</h1>
            <p class="text-gray-600">Reserve your meal and enjoy our day-visit menu</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8" x-data="dayVisitReservation()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Reservation Form -->
            <div class="lg:col-span-2">
                <form @submit.prevent="submitReservation" class="space-y-6">
                    <!-- Date and Time -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">📅 When</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reservation Date</label>
                                <input type="date" x-model="form.reservation_date" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                                <input type="time" x-model="form.reservation_time" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>

                    <!-- Guest Count -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">👥 Guests</h2>
                        <div class="flex items-center gap-4">
                            <button type="button" @click="form.guests = Math.max(1, form.guests - 1)"
                                class="w-10 h-10 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center font-semibold">−</button>
                            <span class="text-2xl font-bold text-gray-800 w-12 text-center" x-text="form.guests"></span>
                            <button type="button" @click="form.guests = Math.min(50, form.guests + 1)"
                                class="w-10 h-10 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center font-semibold">+</button>
                        </div>
                    </div>

                    <!-- Food Selection -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">🍽️ Select Meals</h2>
                        
                        @forelse($foods as $category => $categoryFoods)
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">{{ $category }}</h3>
                                <div class="space-y-3">
                                    @foreach($categoryFoods as $food)
                                        <div class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:border-amber-300 hover:bg-amber-50 transition-colors"
                                            @click="toggleFood({{ $food->id }})">
                                            <input type="checkbox" :checked="hasFood({{ $food->id }})"
                                                class="mt-1 w-5 h-5 text-amber-600 rounded cursor-pointer">
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-800">{{ $food->name }}</p>
                                                <p class="text-sm text-gray-600">{{ $food->description }}</p>
                                                <p class="text-sm font-semibold text-amber-600 mt-1">₱{{ number_format($food->price, 2) }} per person</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-8">No menu items available for day visits</p>
                        @endforelse
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">👤 Your Information</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" x-model="form.customer_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" x-model="form.email" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input type="tel" x-model="form.phone" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests (Optional)</label>
                                <textarea x-model="form.notes" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="isSubmitting || selectedFoods.length === 0"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 text-white font-semibold rounded-lg transition-colors">
                        <span x-show="!isSubmitting">Submit Reservation</span>
                        <span x-show="isSubmitting">Submitting...</span>
                    </button>
                </form>
            </div>

            <!-- Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4" x-data="{}" @update-summary.window="$dispatch('calculate-total')">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>

                    <!-- Selected Foods List -->
                    <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                        <template x-for="(foodId, index) in selectedFoods" :key="index">
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex-1">
                                    <p class="font-medium" x-text="getFoodName(foodId)"></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold" x-text="'₱' + getFoodPrice(foodId).toLocaleString('en-PH', {minimumFractionDigits: 2})"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="selectedFoods.length === 0" class="text-center py-8 text-gray-500">
                        <p class="text-sm">Select meals to see pricing</p>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div x-show="selectedFoods.length > 0" class="space-y-2 border-t border-gray-200 pt-4">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span x-text="'₱' + subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>VAT (12%):</span>
                            <span x-text="'₱' + vatAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between font-semibold text-amber-600 border-t border-gray-200 pt-2 mt-2">
                            <span>Total:</span>
                            <span x-text="'₱' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs text-blue-800">
                            ℹ️ We'll send a confirmation via Messenger after reviewing your reservation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccess" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Reservation Submitted!</h3>
                <p class="text-gray-600 mb-6">We'll confirm your reservation via Messenger shortly. Thank you!</p>
                <button @click="window.location.href = '/dining'"
                    class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg">
                    Return to Dining
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dayVisitReservation() {
        return {
            form: {
                reservation_date: '',
                reservation_time: '',
                guests: 1,
                customer_name: '',
                email: '',
                phone: '',
                notes: ''
            },
            selectedFoods: [],
            isSubmitting: false,
            showSuccess: false,
            subtotal: 0,
            vatAmount: 0,
            grandTotal: 0,
            foodsData: @json($foods->flatten()->mapWithKeys(fn($f) => [$f->id => $f])->toArray()),

            init() {
                const today = new Date().toISOString().split('T')[0];
                this.form.reservation_date = today;
            },

            toggleFood(foodId) {
                const index = this.selectedFoods.indexOf(foodId);
                if (index > -1) {
                    this.selectedFoods.splice(index, 1);
                } else {
                    this.selectedFoods.push(foodId);
                }
                this.calculateTotals();
            },

            hasFood(foodId) {
                return this.selectedFoods.includes(foodId);
            },

            getFoodName(foodId) {
                return this.foodsData[foodId]?.name || 'Unknown';
            },

            getFoodPrice(foodId) {
                return (this.foodsData[foodId]?.price || 0) * this.form.guests;
            },

            calculateTotals() {
                this.subtotal = this.selectedFoods.reduce((sum, foodId) => {
                    return sum + this.getFoodPrice(foodId);
                }, 0);

                this.vatAmount = this.subtotal * this.vatRate;
                this.grandTotal = this.subtotal + this.vatAmount;
            },

            async submitReservation() {
                if (this.selectedFoods.length === 0) {
                    alert('Please select at least one meal');
                    return;
                }

                this.isSubmitting = true;

                try {
                    const response = await fetch('/dining-reservation/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            reservation_date: this.form.reservation_date,
                            reservation_time: this.form.reservation_time,
                            guests: this.form.guests,
                            selected_foods: this.selectedFoods.map(id => ({
                                id: id,
                                quantity: 1
                            })),
                            customer_name: this.form.customer_name,
                            email: this.form.email,
                            phone: this.form.phone,
                            notes: this.form.notes
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess = true;
                        setTimeout(() => {
                            window.location.href = '/dining';
                        }, 3000);
                    } else {
                        alert(data.message || 'Failed to submit reservation');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endsection

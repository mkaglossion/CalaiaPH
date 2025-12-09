{{-- resources/views/components/payment-form.blade.php --}}
@props([
    'action' => '',
    'bookingData' => [],
    'grandTotal' => 0,
    'errors' => null
])

<section aria-labelledby="payment-method-heading">
    <form action="{{ $action }}" method="POST" class="space-y-8">
        @csrf

        {{-- Validation Errors --}}
        @if ($errors && $errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <p class="font-semibold mb-1">There were some problems with your submission:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Customer Information --}}
        <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center" style="">
                <i class="bi bi-person-circle mr-2 text-[#8f5d54]"></i>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900">
                        {{ ($bookingData['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? '') }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900">
                        {{ $bookingData['email'] ?? '' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900">
                        {{ $bookingData['phone'] ?? '' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
                    <div class="bg-white px-3 py-2 rounded-lg border border-gray-200 text-gray-900 min-h-10">
                        {{ $bookingData['special_requests'] ?: 'None' }}
                    </div>
                </div>
            </div>
            {{-- Hidden inputs for form submission --}}
            <input type="hidden" name="first_name" value="{{ $bookingData['first_name'] ?? '' }}">
            <input type="hidden" name="last_name" value="{{ $bookingData['last_name'] ?? '' }}">
            <input type="hidden" name="email" value="{{ $bookingData['email'] ?? '' }}">
            <input type="hidden" name="phone" value="{{ $bookingData['phone'] ?? '' }}">
            <input type="hidden" name="special_requests" value="{{ $bookingData['special_requests'] ?? '' }}">
        </div>

        {{-- Payment Method --}}
        <fieldset class="space-y-3">
            <legend
                id="payment-method-heading"
                class="block text-sm sm:text-base font-medium text-gray-800 mb-1"
            >
                Payment Method
            </legend>
            <p class="text-xs sm:text-sm text-gray-500 mb-2" style="">
                Secure and encrypted payment via your chosen provider.
            </p>

            <div class="space-y-3">
                {{-- GCash --}}
                <label
                    for="payment_gcash"
                    class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-[#8f5d54]/60 hover:bg-gray-50 transition-colors"
                >
                    <span class="flex items-center gap-3">
                        <input
                            id="payment_gcash"
                            type="radio"
                            name="payment_method"
                            value="gcash"
                            class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                            {{ old('payment_method', 'gcash') === 'gcash' ? 'checked' : '' }}
                            required
                        >
                        <span class="flex flex-col">
                            <span class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                <i class="bi bi-wallet2 text-base"></i>
                                GCash
                            </span>
                            <span class="text-xs text-gray-500">
                                Pay instantly using your GCash wallet.
                            </span>
                        </span>
                    </span>
                    <i class="fa-solid fa-shield-halved text-gray-300 text-sm" aria-hidden="true"></i>
                </label>

                {{-- PayMaya --}}
                <label
                    for="payment_paymaya"
                    class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-[#8f5d54]/60 hover:bg-gray-50 transition-colors"
                >
                    <span class="flex items-center gap-3">
                        <input
                            id="payment_paymaya"
                            type="radio"
                            name="payment_method"
                            value="paymaya"
                            class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                            {{ old('payment_method') === 'paymaya' ? 'checked' : '' }}
                        >
                        <span class="flex flex-col">
                            <span class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                <i class="bi bi-credit-card text-base"></i>
                                PayMaya
                            </span>
                            <span class="text-xs text-gray-500">
                                Use your Maya account or linked cards.
                            </span>
                        </span>
                    </span>
                    <i class="fa-solid fa-shield-halved text-gray-300 text-sm" aria-hidden="true"></i>
                </label>

                {{-- Bank Transfer --}}
                <label
                    for="payment_bank"
                    class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-[#8f5d54]/60 hover:bg-gray-50 transition-colors"
                >
                    <span class="flex items-center gap-3">
                        <input
                            id="payment_bank"
                            type="radio"
                            name="payment_method"
                            value="bank"
                            class="h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                            {{ old('payment_method') === 'bank' ? 'checked' : '' }}
                        >
                        <span class="flex flex-col">
                            <span class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                <i class="bi bi-bank2 text-base"></i>
                                Bank Transfer
                            </span>
                            <span class="text-xs text-gray-500">
                                Transfer via your preferred local bank.
                            </span>
                        </span>
                    </span>
                    <i class="fa-solid fa-shield-halved text-gray-300 text-sm" aria-hidden="true"></i>
                </label>
            </div>
        </fieldset>

        {{-- Terms and Conditions --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6">
            <div class="flex items-start">
                <input
                    type="checkbox"
                    id="terms"
                    name="terms"
                    class="mt-1 mr-3 h-4 w-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]"
                    {{ old('terms') ? 'checked' : '' }}
                    required
                >
                <label for="terms" class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                    I agree to the
                    <a href="#" class="text-[#8f5d54] hover:underline font-medium">Terms and Conditions</a> and
                    <a href="#" class="text-[#8f5d54] hover:underline font-medium">Cancellation Policy</a>.
                    I understand that this booking is subject to availability and confirmation. I also acknowledge that payment is non-refundable except in cases specified in the policy.
                </label>
            </div>
        </div>

        {{-- Pay Now Button --}}
        <div class="pt-4">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-lg shadow-[#8f5d54]/20 hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-all duration-300 transform hover:-translate-y-0.5"
            >
                <i class="bi bi-lock-fill mr-2"></i>
                <span>Complete Payment</span>
            </button>
            <p class="mt-3 text-xs sm:text-sm text-center text-gray-500" style="">
                Your payment is secured with 256-bit SSL encryption. By proceeding, you agree to our terms of service.
            </p>
        </div>
    </form>
</section>
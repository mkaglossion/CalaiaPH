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
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center" style="font-family: 'Gotham Book', sans-serif;">
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
            <p class="text-xs sm:text-sm text-gray-500 mb-2" style="font-family: 'Gotham Book', sans-serif;">
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

        {{-- Cancellation Policy Selection --}}
        <div class="space-y-6">
            {{-- Header --}}
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="bi bi-calendar-check text-2xl text-[#8f5d54]"></i>
                    Cancellation Policy
                </h3>
                <p class="text-sm text-gray-600">Select your preferred cancellation terms. Different policies offer different flexibility and refund amounts.</p>
            </div>

            {{-- Policy Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Standard Policy --}}
                <div class="border-2 border-green-200 rounded-xl p-5 bg-gradient-to-br from-green-50 to-emerald-50 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <span class="text-4xl">📅</span>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">STANDARD</span>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Free Cancellation</h4>
                    <div class="space-y-2 mb-4 pb-4 border-b border-green-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>≥ 7 days</strong></span>
                            <span class="text-sm font-bold text-green-600">100% refund</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>3-6 days</strong></span>
                            <span class="text-sm font-bold text-amber-600">50% refund</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>&lt; 3 days</strong></span>
                            <span class="text-sm font-bold text-red-600">No refund</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 flex items-center gap-2">
                        <span class="text-green-600 font-bold">✓</span>
                        Refunds processed within 3-5 business days
                    </p>
                </div>

                {{-- Flexible Policy --}}
                <div class="border-2 border-amber-300 rounded-xl p-5 bg-gradient-to-br from-amber-50 to-yellow-50 hover:shadow-lg transition-shadow ring-2 ring-amber-200 ring-offset-2">
                    <div class="flex items-start justify-between mb-4">
                        <span class="text-4xl">🔄</span>
                        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">BEST VALUE</span>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Flexible Graduated</h4>
                    <div class="space-y-2 mb-4 pb-4 border-b border-amber-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>30+ days</strong></span>
                            <span class="text-sm font-bold text-green-600">100%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>15-29 days</strong></span>
                            <span class="text-sm font-bold text-green-600">75%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>7-14 days</strong></span>
                            <span class="text-sm font-bold text-amber-600">50%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>3-6 days</strong></span>
                            <span class="text-sm font-bold text-orange-600">25%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700"><strong>&lt; 3 days</strong></span>
                            <span class="text-sm font-bold text-red-600">No refund</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 flex items-center gap-2">
                        <span class="text-amber-600 font-bold">✓</span>
                        Maximum flexibility for cancellations
                    </p>
                </div>

                {{-- Economy Policy --}}
                <div class="border-2 border-red-200 rounded-xl p-5 bg-gradient-to-br from-red-50 to-rose-50 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <span class="text-4xl">💰</span>
                        <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">LOWEST PRICE</span>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Non-Refundable</h4>
                    <div class="space-y-2 mb-4 pb-4 border-b border-red-200">
                        <div class="text-sm text-gray-700">
                            <p class="font-bold text-gray-900">Refund Policy:</p>
                            <p class="text-red-600 font-bold mt-1">No refund after booking</p>
                        </div>
                        <div class="text-sm text-gray-700">
                            <p class="font-bold text-gray-900 mt-2">Best for:</p>
                            <p class="text-gray-600 mt-1">Confirmed travel dates</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 flex items-center gap-2">
                        <span class="text-red-600 font-bold">✓</span>
                        Rebook anytime at no extra cost
                    </p>
                </div>
            </div>

            {{-- Important Information --}}
            <div class="bg-slate-50 border-2 border-slate-200 rounded-xl p-5">
                <p class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="bi bi-info-circle text-slate-700 text-lg"></i>
                    Important Information
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex gap-3">
                        <span class="text-xl flex-shrink-0">📅</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Peak Season Rules</p>
                            <p class="text-xs text-gray-600 mt-1">Dec 15 - Jan 5, Holy Week & Summer: Stricter policies may apply</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-xl flex-shrink-0">🔄</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Rebook Anytime</p>
                            <p class="text-xs text-gray-600 mt-1">Change your dates after cancellation without additional fees</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-xl flex-shrink-0">💳</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Credit Validity</p>
                            <p class="text-xs text-gray-600 mt-1">Refunds as credits valid for 6-12 months from issue date</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-xl flex-shrink-0">⏱️</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Processing Timeline</p>
                            <p class="text-xs text-gray-600 mt-1">Refunds issued within 3-5 business days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
            <p class="mt-3 text-xs sm:text-sm text-center text-gray-500" style="font-family: 'Gotham Book', sans-serif;">
                Your payment is secured with 256-bit SSL encryption. By proceeding, you agree to our terms of service.
            </p>
        </div>
    </form>
</section>
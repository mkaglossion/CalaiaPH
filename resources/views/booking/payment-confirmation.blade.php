<x-page-layout
    title="CalaiaPH - Package Payment"
    description="Review your package booking and continue to payment."
    active-header="packages"
    bodyClass="bg-[#f5efe7]"
>
    @php
        $booking->loadMissing('package');
        $package = $booking->package;
        $packageName = $package?->name ?? 'Package';
        $confirmationContent = $confirmationContent ?? app(\App\Services\BookingConfirmationContentService::class)->forType('package', [
            'reference' => $booking->booking_reference,
            'package' => $packageName,
        ]);
        $pageContent = $confirmationContent['page'] ?? [];
        $nextStepsContent = $confirmationContent['next_steps'] ?? [];
        $supportContent = $confirmationContent['support'] ?? [];
        $buttonContent = $confirmationContent['buttons'] ?? [];
        $confirmationSteps = [
            ['label' => 'Details', 'status' => 'completed'],
            ['label' => 'Payment', 'status' => 'current'],
            ['label' => 'Confirmation', 'status' => 'pending'],
        ];
    @endphp

    <x-booking-progress :steps="$confirmationSteps" />

    <main class="py-8 sm:py-12 md:py-16 min-h-[80vh]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
                <header class="mb-6 sm:mb-8 text-center">
                    <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2" data-preview-slot="page.eyebrow">
                        {{ $pageContent['eyebrow'] ?? 'Package Booking' }}
                    </p>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#8f5d54]" data-preview-slot="page.title">
                        {{ $pageContent['title'] ?? 'Confirm Your Payment' }}
                    </h1>
                    <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto" data-preview-slot="page.description">
                        {{ $pageContent['description'] ?? 'Review the booking summary below before proceeding to secure checkout.' }}
                    </p>
                </header>

                <div class="grid gap-6 lg:gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] items-start">
                    <section class="space-y-6">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 sm:p-6">
                            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Booking Summary</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-500">Reference:</span>
                                    <span class="ml-2 text-gray-900">{{ $booking->booking_reference }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Package:</span>
                                    <span class="ml-2 text-gray-900">{{ $packageName }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Booking Date:</span>
                                    <span class="ml-2 text-gray-900">{{ $booking->formatted_booking_date }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Time:</span>
                                    <span class="ml-2 text-gray-900">{{ $booking->display_session }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Guests:</span>
                                    <span class="ml-2 text-gray-900">{{ $booking->total_guests }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500">Status:</span>
                                    <span class="ml-2 text-gray-900">{{ ucfirst($booking->payment_status ?? 'pending') }}</span>
                                </div>
                            </div>
                        </div>

                        @if(!empty($booking->special_requests))
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3">Special Requests</h2>
                                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $booking->special_requests }}</p>
                            </div>
                        @endif

                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:p-6">
                            <h2 class="text-lg sm:text-xl font-semibold text-blue-900 mb-3" data-preview-slot="next_steps.title">{{ $nextStepsContent['title'] ?? "What's Next?" }}</h2>
                            <ul class="space-y-2 text-sm text-blue-800" data-preview-list="next-steps" data-preview-list-style="package">
                                @forelse(($nextStepsContent['items'] ?? []) as $step)
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-dot text-lg leading-none mt-0.5"></i>
                                        <span>{{ $step }}</span>
                                    </li>
                                @empty
                                    <li class="flex items-start gap-2">
                                        <i class="bi bi-dot text-lg leading-none mt-0.5"></i>
                                        <span>Review your booking details and total amount.</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        @if(!empty($supportContent['email']) || !empty($supportContent['phone']))
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
                                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3" data-preview-slot="support.title">{{ $supportContent['title'] ?? 'Need assistance?' }}</h2>
                                <p class="text-sm text-gray-600">
                                    <span data-preview-slot="support.description">{{ $supportContent['description'] ?? 'Reach us anytime if you need help with your package payment.' }}</span>
                                </p>
                                <div class="mt-3 text-sm text-gray-700 space-y-1">
                                    @if(!empty($supportContent['email']))
                                        <p>Email: <a href="mailto:{{ $supportContent['email'] }}" class="text-[#8f5d54] hover:underline" data-preview-slot="support.email">{{ $supportContent['email'] }}</a></p>
                                    @endif
                                    @if(!empty($supportContent['phone']))
                                        <p>Phone: <span class="font-medium" data-preview-slot="support.phone">{{ $supportContent['phone'] }}</span></p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </section>

                    <aside class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Payment Details</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Package total</span>
                                <span class="font-semibold text-gray-900">₱{{ number_format((float) $booking->total_amount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Payment status</span>
                                <span class="font-semibold text-gray-900">{{ ucfirst($booking->payment_status ?? 'pending') }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-200 pt-3">
                                <span class="text-base font-semibold text-gray-900">Amount due</span>
                                <span class="text-xl font-bold text-[#8f5d54]">₱{{ number_format((float) $booking->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <button
                            id="proceed-to-payment"
                            type="button"
                            class="mt-6 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-5 py-3 text-sm font-semibold text-white hover:bg-[#a0675a] transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <i class="bi bi-credit-card"></i>
                            <span data-preview-slot="buttons.payment">{{ $buttonContent['payment'] ?? 'Proceed to Payment' }}</span>
                        </button>

                        <a href="{{ url('/packages') }}"
                           class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            <span data-preview-slot="buttons.home">{{ $buttonContent['home'] ?? 'Back to Packages' }}</span>
                        </a>

                        <p id="payment-error" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></p>
                    </aside>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        document.getElementById('proceed-to-payment')?.addEventListener('click', async function () {
            const button = this;
            const errorEl = document.getElementById('payment-error');

            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i><span>Preparing checkout...</span>';
            if (errorEl) {
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
            }

            try {
                const response = await fetch(`/api/payment/confirm?booking_id={{ $booking->id }}&token={{ $booking->public_token }}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.checkout_url) {
                    window.location.href = data.checkout_url;
                    return;
                }

                throw new Error(data.message || 'Failed to prepare payment checkout.');
            } catch (error) {
                if (errorEl) {
                    errorEl.textContent = error.message || 'Unable to start payment checkout.';
                    errorEl.classList.remove('hidden');
                }
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-credit-card"></i><span>Proceed to Payment</span>';
            }
        });
    </script>
    @endpush
</x-page-layout>

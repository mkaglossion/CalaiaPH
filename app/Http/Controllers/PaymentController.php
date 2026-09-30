<?php

namespace App\Http\Controllers;

use App\Models\PackageBooking;
use App\Services\PaymongoWebhookService;
use App\Services\BookingConfirmationContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private function hasValidPublicToken(PackageBooking $booking, string $token): bool
    {
        $storedToken = trim((string) ($booking->public_token ?? ''));
        $providedToken = trim($token);

        return $storedToken !== '' && $providedToken !== '' && hash_equals($storedToken, $providedToken);
    }

    /**
     * Show payment confirmation page
     */
    public function showConfirmation(PackageBooking $booking, string $token)
    {
        abort_unless($this->hasValidPublicToken($booking, $token), 403);

        // Load the booking with package relationship
        $booking->load('package');
        $packageName = $booking->package?->name ?? 'Package';
        $confirmationContent = app(BookingConfirmationContentService::class)->forType('package', [
            'reference' => $booking->booking_reference,
            'package' => $packageName,
        ]);
        
        return view('booking.payment-confirmation', [
            'booking' => $booking,
            'confirmationContent' => $confirmationContent,
        ]);
    }

    /**
     * Confirm payment and create PayMongo checkout
     */
    public function confirmCheckout(Request $request)
    {
        try {
            $bookingId = $request->query('booking_id');
            $token = (string) $request->query('token', '');
            
            if (!$bookingId || $token === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking ID and token are required'
                ], 400);
            }

            $booking = PackageBooking::with('package')->findOrFail($bookingId);
            if (!$this->hasValidPublicToken($booking, $token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid booking token'
                ], 403);
            }

            // Create PayMongo checkout with booking details
            return $this->createCheckout(new \Illuminate\Http\Request([
                'booking_id' => $booking->id,
                'token' => $token,
            ]));
        } catch (\Exception $e) {
            Log::error('Payment confirmation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to confirm payment.'
            ], 500);
        }
    }

    /**
     * Create PayMongo checkout session
     */
    public function createCheckout(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:package_bookings,id',
                'token' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Payment validation failed', [
                'errors' => $e->errors(),
                'booking_id' => $request->input('booking_id'),
            ]);
            
            // Format errors for frontend display
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = "{$field}: {$message}";
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $booking = PackageBooking::with('package')->findOrFail($validated['booking_id']);
            if (!$this->hasValidPublicToken($booking, $validated['token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid booking token'
                ], 403);
            }

            $packageName = $booking->package?->name ?? 'Package';
            $customerName = trim($booking->first_name . ' ' . $booking->last_name);
            $checkoutAmount = (float) $booking->total_amount;

            // Initialize PayMongo API
            $apiKey = config('services.paymongo.secret_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'PayMongo not configured. Please add PAYMONGO_SECRET_KEY to .env file'
                ], 422);
            }

            // Create PayMongo checkout
            $response = Http::withBasicAuth($apiKey, '')
                ->connectTimeout(config('services.paymongo.connect_timeout'))
                ->timeout(config('services.paymongo.timeout'))
                ->post('https://api.paymongo.com/v1/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'send_email_receipt' => true,
                            'show_payment_details' => true,
                            'line_items' => [
                                [
                                    'name' => $packageName,
                                    'quantity' => 1,
                                    'amount' => (int) round($checkoutAmount * 100), // Convert to centavos
                                    'currency' => 'PHP',
                                    'description' => 'Package Booking #' . $booking->booking_reference
                                ]
                            ],
                            'payment_method_types' => ['card', 'gcash', 'grab_pay', 'paymaya', 'qrph'],
                            'reference_number' => $booking->booking_reference,
                            'metadata' => [
                                'booking_id' => $booking->id,
                                'booking_reference' => $booking->booking_reference,
                                'booking_date' => optional($booking->booking_date)->toDateString(),
                                'session' => $booking->session,
                                'customer_email' => $booking->email,
                                'customer_name' => $customerName,
                                'first_name' => $booking->first_name,
                                'last_name' => $booking->last_name,
                                'phone' => $booking->phone,
                                'public_token' => $booking->public_token,
                            ],
                            'success_url' => route('booking.payment-success') . '?checkout_id={CHECKOUT_ID}',
                            'cancel_url' => route('booking.payment-cancelled') . '?checkout_id={CHECKOUT_ID}'
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['attributes']['checkout_url'])) {
                    // Update booking with PayMongo session reference
                    $booking->update([
                        'paymongo_reference' => $data['data']['id']
                    ]);

                    return response()->json([
                        'success' => true,
                        'checkout_url' => $data['data']['attributes']['checkout_url']
                    ]);
                }
            }

            Log::error('PayMongo checkout creation failed', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            $errorDetail = 'Failed to create PayMongo checkout';
            $paymongoResponse = $response->json();
            
            if (isset($paymongoResponse['errors']) && is_array($paymongoResponse['errors'])) {
                $errorMessages = array_map(function ($error) {
                    return $error['detail'] ?? $error['code'] ?? 'Unknown error';
                }, $paymongoResponse['errors']);
                $errorDetail = implode(', ', $errorMessages);
            }

            return response()->json([
                'success' => false,
                'message' => $errorDetail
            ], 422);

        } catch (\Exception $e) {
            Log::error('PayMongo checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed.'
            ], 500);
        }
    }

    /**
     * Handle successful payment
     */
    public function paymentSuccess(Request $request)
    {
        try {
            $checkoutSessionId = $request->query('checkout_session_id') ?: $request->query('checkout_id');

            if (!$checkoutSessionId) {
                return redirect('/packages')->with('error', 'Invalid payment reference');
            }

            // Fetch checkout session details from PayMongo
            $apiKey = config('services.paymongo.secret_key');
            $response = Http::withBasicAuth($apiKey, '')
                ->connectTimeout(config('services.paymongo.connect_timeout'))
                ->timeout(config('services.paymongo.timeout'))
                ->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutSessionId}");

            if ($response->successful()) {
                $data = $response->json();
                $paymentStatus = $data['data']['attributes']['payment_status'] ?? null;

                if ($paymentStatus === 'paid') {
                    // Update booking status
                    $booking = PackageBooking::where('paymongo_reference', $checkoutSessionId)->first();
                    
                    if ($booking) {
                        $paymentReference = data_get($data, 'data.attributes.payments.0.id')
                            ?: data_get($data, 'data.attributes.payment_intent.id')
                            ?: $checkoutSessionId;
                        $paidAmountCentavos = data_get($data, 'data.attributes.payments.0.attributes.amount')
                            ?? data_get($data, 'data.attributes.amount');

                        if (PaymongoWebhookService::markPackageBookingPaid(
                            $booking,
                            $paymentReference,
                            $paidAmountCentavos
                        )) {
                            return redirect('/packages')->with('success', 'Booking confirmed! Check your email for details.');
                        }
                    }
                }
            }

            return redirect('/packages')->with('error', 'Payment status could not be verified');

        } catch (\Exception $e) {
            Log::error('Payment success handler error: ' . $e->getMessage());
            return redirect('/packages')->with('error', 'An error occurred processing your payment');
        }
    }

    /**
     * Handle cancelled payment
     */
    public function paymentCancelled(Request $request)
    {
        // A browser redirect is not trusted payment evidence. Pending bookings
        // are reconciled through signed webhooks or server-side provider checks.
        return redirect('/packages')->with('warning', 'Payment was cancelled. No payment was recorded.');
    }

    /**
     * Handle PayMongo webhook for payment status updates
     */
    public function webhook(Request $request)
    {
        try {
            $data = $request->json()->all();
            $event = $data['data']['type'] ?? null;

            if ($event === 'payment.paid') {
                $paymentId = $data['data']['attributes']['id'] ?? null;
                $metadata = $data['data']['attributes']['metadata'] ?? [];

                if (isset($metadata['booking_id'])) {
                    $booking = PackageBooking::find($metadata['booking_id']);
                    
                    if ($booking) {
                        $booking->update([
                            'payment_status' => 'paid',
                            'payment_reference' => $paymentId,
                            'status' => 'confirmed'
                        ]);

                        // Send email
                        \Illuminate\Support\Facades\Mail::to($booking->email)
                            ->queue(new \App\Mail\PackageBookingConfirmation($booking));
                    }
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('PayMongo webhook error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}

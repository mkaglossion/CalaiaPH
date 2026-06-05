<?php

namespace App\Http\Controllers;

use App\Models\PackageBooking;
use App\Services\PaymongoWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Show payment confirmation page
     */
    public function showConfirmation(PackageBooking $booking)
    {
        // Load the booking with package relationship
        $booking->load('package');
        
        return view('booking.payment-confirmation', [
            'booking' => $booking
        ]);
    }

    /**
     * Confirm payment and create PayMongo checkout
     */
    public function confirmCheckout(Request $request)
    {
        try {
            $bookingId = $request->query('booking_id');
            
            if (!$bookingId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking ID is required'
                ], 400);
            }

            $booking = PackageBooking::with('package')->findOrFail($bookingId);

            // Create PayMongo checkout with booking details
            return $this->createCheckout(new \Illuminate\Http\Request([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'email' => $booking->email,
                'name' => $booking->first_name . ' ' . $booking->last_name,
                'package_name' => $booking->package->name
            ]));
        } catch (\Exception $e) {
            Log::error('Payment confirmation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error confirming payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create PayMongo checkout session
     */
    public function createCheckout(Request $request)
    {
        Log::info('Payment checkout request received', [
            'all_data' => $request->all(),
            'booking_id' => $request->input('booking_id'),
            'amount' => $request->input('amount'),
            'email' => $request->input('email')
        ]);

        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:package_bookings,id',
                'amount' => 'required|numeric|min:1',
                'email' => 'required|email',
                'name' => 'required|string',
                'package_name' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Payment validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
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
                'received_data' => $request->all()
            ], 422);
        }

        try {
            $booking = PackageBooking::findOrFail($validated['booking_id']);

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
                ->post('https://api.paymongo.com/v1/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'send_email_receipt' => true,
                            'show_payment_details' => true,
                            'line_items' => [
                                [
                                    'name' => $validated['package_name'],
                                    'quantity' => 1,
                                    'amount' => (int)($validated['amount'] * 100), // Convert to centavos
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
                                'customer_email' => $validated['email'],
                                'customer_name' => $validated['name'],
                                'first_name' => $booking->first_name,
                                'last_name' => $booking->last_name,
                                'phone' => $booking->phone,
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
                'message' => 'Payment initialization failed: ' . $e->getMessage()
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

                        if (PaymongoWebhookService::markPackageBookingPaid($booking, $paymentReference)) {
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
        try {
            $checkoutSessionId = $request->query('checkout_session_id') ?: $request->query('checkout_id');

            if ($checkoutSessionId) {
                $booking = PackageBooking::where('paymongo_reference', $checkoutSessionId)->first();
                
                if ($booking) {
                    $booking->update([
                        'status' => 'cancelled'
                    ]);
                }
            }

            return redirect('/packages')->with('warning', 'Payment was cancelled. Your booking has been removed.');

        } catch (\Exception $e) {
            Log::error('Payment cancelled handler error: ' . $e->getMessage());
            return redirect('/packages')->with('error', 'An error occurred');
        }
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

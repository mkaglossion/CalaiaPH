<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymongoWebhookEvent;
use App\Services\PaymongoWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymongoWebhookController extends Controller
{
    /**
     * Handle incoming PayMongo webhooks
     */
    public function handle(Request $request)
    {
        $payload = [];
        $signaturePresent = false;
        $signatureValid = false;
        $eventMeta = [];

        try {
            $payload = $request->json()->all();
            $eventMeta = $this->extractEventMeta($payload);

            // Signature is required for all webhook calls.
            // PayMongo commonly uses "Paymongo-Signature"; keep legacy "X-Paymongo-Signature" support too.
            $signature = $request->header('Paymongo-Signature')
                ?: $request->header('X-Paymongo-Signature');
            $signaturePresent = !empty($signature);
            if (!$signature) {
                Log::warning("PayMongo webhook missing signature header", [
                    'available_headers' => array_keys($request->headers->all()),
                ]);
                $this->storeWebhookEvent(
                    $payload,
                    array_merge($eventMeta, [
                        'signature_present' => false,
                        'signature_valid' => false,
                        'handled' => false,
                        'http_status' => 401,
                        'message' => 'Missing signature',
                    ])
                );
                return response()->json(['success' => false, 'message' => 'Missing signature'], 401);
            }

            $payloadString = $request->getContent();
            $signatureValid = PaymongoWebhookService::verifySignature($payloadString, $signature);
            if (!$signatureValid) {
                Log::warning("PayMongo webhook signature verification failed");
                $this->storeWebhookEvent(
                    $payload,
                    array_merge($eventMeta, [
                        'signature_present' => true,
                        'signature_valid' => false,
                        'handled' => false,
                        'http_status' => 401,
                        'message' => 'Invalid signature',
                    ])
                );
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
            }

            // Handle the webhook
            $result = PaymongoWebhookService::handleWebhook($payload);
            $this->storeWebhookEvent(
                $payload,
                array_merge($eventMeta, [
                    'signature_present' => true,
                    'signature_valid' => true,
                    'handled' => (bool) $result,
                    'http_status' => 200,
                    'message' => 'Webhook processed',
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed',
                'handled' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error("PayMongo webhook handling error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->storeWebhookEvent(
                $payload,
                array_merge($eventMeta, [
                    'signature_present' => $signaturePresent,
                    'signature_valid' => $signatureValid,
                    'handled' => false,
                    'http_status' => 500,
                    'message' => 'Webhook processing failed',
                    'error_message' => $e->getMessage(),
                ])
            );

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    private function extractEventMeta(array $payload): array
    {
        $normalized = PaymongoWebhookService::normalizeWebhookPayload($payload);
        $eventType = $normalized['event_type'];
        $resource = $normalized['resource'];
        $resourceType = $resource['type'] ?? null;
        $resourceId = $resource['id'] ?? null;
        $attributes = is_array($resource['attributes'] ?? null) ? $resource['attributes'] : [];
        $metadata = is_array($attributes['metadata'] ?? null) ? $attributes['metadata'] : [];

        $reference = $attributes['reference']
            ?? $attributes['reference_number']
            ?? ($metadata['reference'] ?? null)
            ?? ($metadata['reference_id'] ?? null)
            ?? ($metadata['reference_number'] ?? null);

        $checkoutSessionId = $attributes['checkout_session_id'] ?? null;
        $paymentId = $attributes['payment_id'] ?? null;

        if (($eventType && str_starts_with($eventType, 'checkout_session.')) || $resourceType === 'checkout_session') {
            $checkoutSessionId = $checkoutSessionId ?: $resourceId;
            $paymentId = $paymentId ?: data_get($attributes, 'payments.0.id');
        }
        if (($eventType && str_starts_with($eventType, 'payment.')) || $resourceType === 'payment') {
            $paymentId = $paymentId ?: $resourceId;
        }

        $amountCentavos = $attributes['amount'] ?? null;
        if (!is_numeric($amountCentavos)) {
            $amountCentavos = data_get($attributes, 'payments.0.attributes.amount');
            if (!is_numeric($amountCentavos)) {
                $amountCentavos = null;
            }
        }

        return [
            'event_type' => $eventType,
            'resource_type' => $resourceType,
            'reference' => $reference,
            'checkout_session_id' => $checkoutSessionId,
            'payment_id' => $paymentId,
            'amount_centavos' => $amountCentavos !== null ? (int) $amountCentavos : null,
        ];
    }

    private function storeWebhookEvent(array $payload, array $meta): void
    {
        try {
            PaymongoWebhookEvent::create([
                'event_type' => $meta['event_type'] ?? null,
                'resource_type' => $meta['resource_type'] ?? null,
                'reference' => $meta['reference'] ?? null,
                'checkout_session_id' => $meta['checkout_session_id'] ?? null,
                'payment_id' => $meta['payment_id'] ?? null,
                'amount_centavos' => $meta['amount_centavos'] ?? null,
                'signature_present' => (bool) ($meta['signature_present'] ?? false),
                'signature_valid' => (bool) ($meta['signature_valid'] ?? false),
                'handled' => (bool) ($meta['handled'] ?? false),
                'http_status' => (int) ($meta['http_status'] ?? 200),
                'message' => $meta['message'] ?? null,
                'error_message' => $meta['error_message'] ?? null,
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to persist PayMongo webhook event log', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

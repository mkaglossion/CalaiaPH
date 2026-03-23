<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        try {
            $payload = $request->json()->all();

            // Verify webhook signature if available
            $signature = $request->header('X-Paymongo-Signature');
            if ($signature) {
                $payloadString = $request->getContent();
                if (!PaymongoWebhookService::verifySignature($payloadString, $signature)) {
                    Log::warning("PayMongo webhook signature verification failed");
                    return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
                }
            }

            // Handle the webhook
            $result = PaymongoWebhookService::handleWebhook($payload);

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

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}

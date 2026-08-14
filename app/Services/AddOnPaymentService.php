<?php

namespace App\Services;

use App\Models\AddOnTransaction;
use App\Models\Food;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddOnPaymentService
{
    public function recoverCheckoutUrl(AddOnTransaction $addOn): ?string
    {
        if (filled($addOn->checkout_url)) {
            return $addOn->checkout_url;
        }

        $checkoutSessionId = $addOn->paymongo_checkout_id;
        $apiKey = config('services.paymongo.secret_key');
        if (!$checkoutSessionId || !$apiKey) {
            return null;
        }

        $response = Http::withBasicAuth($apiKey, '')
            ->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutSessionId}");

        if (!$response->successful()) {
            Log::warning('Unable to recover pending add-on checkout URL.', [
                'add_on_id' => $addOn->id,
                'checkout_session_id' => $checkoutSessionId,
                'http_status' => $response->status(),
            ]);

            return null;
        }

        $checkoutUrl = $response->json('data.attributes.checkout_url');
        if (!is_string($checkoutUrl) || !str_starts_with($checkoutUrl, 'https://')) {
            return null;
        }

        $addOn->update(['checkout_url' => $checkoutUrl]);

        return $checkoutUrl;
    }

    public function confirmCheckout(AddOnTransaction $addOn, string $checkoutSessionId): bool
    {
        if (!hash_equals((string) $addOn->paymongo_checkout_id, $checkoutSessionId)) {
            return false;
        }

        $apiKey = config('services.paymongo.secret_key');
        if (!$apiKey) {
            Log::error('Cannot verify add-on payment because PayMongo is not configured.', [
                'add_on_id' => $addOn->id,
            ]);
            return false;
        }

        $response = Http::withBasicAuth($apiKey, '')
            ->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutSessionId}");

        if (!$response->successful()) {
            Log::warning('Unable to verify add-on checkout session.', [
                'add_on_id' => $addOn->id,
                'checkout_session_id' => $checkoutSessionId,
                'http_status' => $response->status(),
            ]);
            return false;
        }

        $attributes = $response->json('data.attributes', []);
        $paymentStatus = $attributes['payment_status'] ?? null;
        $paymentIntentStatus = data_get($attributes, 'payment_intent.status');

        if ($paymentStatus !== 'paid' && $paymentIntentStatus !== 'succeeded') {
            return false;
        }

        $payment = data_get($attributes, 'payments.0', []);
        $paymentReference = $payment['id'] ?? data_get($attributes, 'payment_intent.id') ?? $checkoutSessionId;
        $amountCentavos = data_get($payment, 'attributes.amount') ?? $attributes['amount'] ?? null;

        return $this->markPaid($addOn, $paymentReference, $checkoutSessionId, $amountCentavos);
    }

    public function markPaid(
        AddOnTransaction $addOn,
        ?string $paymentReference = null,
        ?string $checkoutSessionId = null,
        mixed $amountCentavos = null
    ): bool {
        $expectedCentavos = (int) round((float) $addOn->total_amount * 100);
        if (is_numeric($amountCentavos) && (int) $amountCentavos !== $expectedCentavos) {
            Log::error('Add-on payment amount mismatch.', [
                'add_on_id' => $addOn->id,
                'expected_centavos' => $expectedCentavos,
                'paid_centavos' => (int) $amountCentavos,
            ]);
            return false;
        }

        return DB::transaction(function () use ($addOn, $paymentReference, $checkoutSessionId) {
            $lockedAddOn = AddOnTransaction::query()->lockForUpdate()->find($addOn->id);
            if (!$lockedAddOn) {
                return false;
            }

            if ($lockedAddOn->payment_status === 'paid') {
                return true;
            }

            $lockedAddOn->update([
                'status' => AddOnTransaction::STATUS_CONFIRMED,
                'payment_status' => 'paid',
                'payment_reference' => $paymentReference ?: $lockedAddOn->payment_reference,
                'paymongo_checkout_id' => $checkoutSessionId ?: $lockedAddOn->paymongo_checkout_id,
                'paid_at' => now(),
            ]);

            $this->reduceFoodInventory($lockedAddOn);

            return true;
        });
    }

    public function markFailed(AddOnTransaction $addOn, ?string $paymentReference = null): bool
    {
        if ($addOn->payment_status === 'paid') {
            return true;
        }

        $addOn->update([
            'status' => AddOnTransaction::STATUS_CANCELLED,
            'payment_status' => 'failed',
            'payment_reference' => $paymentReference ?: $addOn->payment_reference,
        ]);

        return true;
    }

    public function findForWebhook(
        array $metadata,
        ?string $referenceNumber = null,
        ?string $checkoutSessionId = null
    ): ?AddOnTransaction {
        $query = AddOnTransaction::query();

        if (!empty($metadata['add_on_transaction_id'])) {
            return $query->find($metadata['add_on_transaction_id']);
        }

        if ($referenceNumber && str_starts_with(strtoupper($referenceNumber), 'AO-')) {
            $addOn = (clone $query)->where('reference', $referenceNumber)->first();
            if ($addOn) {
                return $addOn;
            }
        }

        if ($checkoutSessionId) {
            return $query->where('paymongo_checkout_id', $checkoutSessionId)->first();
        }

        return null;
    }

    private function reduceFoodInventory(AddOnTransaction $addOn): void
    {
        foreach ($addOn->items ?? [] as $item) {
            if (($item['type'] ?? null) !== 'meal' || empty($item['item_id'])) {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $food = Food::query()->lockForUpdate()->find($item['item_id']);

            if (!$food || !$food->is_inventory_managed) {
                continue;
            }

            if ($food->stock_quantity < $quantity) {
                Log::error('Paid add-on has insufficient meal inventory.', [
                    'add_on_id' => $addOn->id,
                    'food_id' => $food->id,
                    'requested' => $quantity,
                    'available' => $food->stock_quantity,
                ]);
                continue;
            }

            $food->decrement('stock_quantity', $quantity);
        }
    }
}

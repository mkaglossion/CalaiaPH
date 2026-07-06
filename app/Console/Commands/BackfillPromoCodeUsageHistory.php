<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Console\Command;

class BackfillPromoCodeUsageHistory extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'promo-codes:backfill-usage-history {--dry-run : Show what would be written without saving changes}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill promo code usage history from paid bookings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $missingPromoData = 0;
        $unresolvedPromo = 0;
        $missingTransaction = 0;

        Booking::query()
            ->whereIn('payment_status', ['paid', 'confirmed'])
            ->orderBy('id')
            ->chunkById(200, function ($bookings) use (&$created, &$updated, &$skipped, &$missingPromoData, &$unresolvedPromo, &$missingTransaction, $dryRun) {
                foreach ($bookings as $booking) {
                    $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];
                    $paymentData = is_array($bookingData['payment_data'] ?? null)
                        ? $bookingData['payment_data']
                        : $bookingData;

                    $promoId = $paymentData['promo_id'] ?? $bookingData['promo_id'] ?? null;
                    $promoCodeValue = $paymentData['promo_code'] ?? $bookingData['promo_code'] ?? null;

                    if (empty($promoId) && empty($promoCodeValue)) {
                        $missingPromoData++;
                        $skipped++;
                        continue;
                    }

                    $promo = null;
                    if (!empty($promoId)) {
                        $promo = PromoCode::find((int) $promoId);
                    }

                    if (!$promo && !empty($promoCodeValue)) {
                        $promo = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper(trim((string) $promoCodeValue))])->first();
                    }

                    if (!$promo) {
                        $unresolvedPromo++;
                        $skipped++;
                        continue;
                    }

                    if (empty($booking->transaction_id)) {
                        $missingTransaction++;
                        $skipped++;
                        continue;
                    }

                    $discountAmount = $paymentData['promo_discount']
                        ?? data_get($bookingData, 'payment_summary.promo_discount')
                        ?? 0;

                    $checkoutId = $booking->paymongo_checkout_id
                        ?? $paymentData['checkout_id']
                        ?? $paymentData['checkout_session_id']
                        ?? null;

                    $paymentReference = $booking->payment_reference
                        ?? $paymentData['payment_reference']
                        ?? $paymentData['reference_id']
                        ?? null;

                    $recordedAt = $booking->updated_at ?? $booking->created_at ?? now();

                    if ($dryRun) {
                        $this->line(sprintf(
                            'Would backfill promo %s on booking #%d (%s)',
                            $promo->code,
                            $booking->id,
                            $booking->transaction_id
                        ));
                        $created++;
                        continue;
                    }

                    $usage = PromoCodeUsage::firstOrNew([
                        'promo_code_id' => $promo->id,
                        'booking_transaction_id' => $booking->transaction_id,
                    ]);

                    $isNew = !$usage->exists;

                    $usage->forceFill([
                        'booking_id' => $booking->id,
                        'checkout_id' => $checkoutId,
                        'payment_reference' => $paymentReference,
                        'discount_amount' => (float) $discountAmount,
                    ]);

                    if ($isNew) {
                        $usage->forceFill([
                            'created_at' => $recordedAt,
                        ]);
                    }

                    $usage->forceFill([
                        'updated_at' => $recordedAt,
                    ]);

                    $usage->timestamps = false;
                    $usage->save();

                    if ($isNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            });

        $message = sprintf(
            'Backfill complete. Created: %d, Updated: %d, Skipped: %d%s',
            $created,
            $updated,
            $skipped,
            $dryRun ? ' (dry run)' : ''
        );

        $this->info($message);
        $this->line(sprintf(
            'Skipped reasons - Missing promo data: %d, Unresolved promo: %d, Missing transaction: %d',
            $missingPromoData,
            $unresolvedPromo,
            $missingTransaction
        ));

        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

use Carbon\Carbon;

class StaycationPriceBreakdownService
{
    /**
     * Build customer-facing charge lines for one accommodation.
     *
     * New bookings carry the exact nightly breakdown. Older bookings are
     * supported by distributing their saved accommodation total across the
     * saved number of nights without recalculating their historical total.
     *
     * @return array<int, array{type: string, name: string, unit_price: float, quantity: int, total: float}>
     */
    public function accommodationLines(array $accommodation, array $bookingData, ?string $roomName = null): array
    {
        $roomName = trim((string) ($roomName
            ?? $accommodation['room_name']
            ?? $accommodation['name']
            ?? $accommodation['accommodation_name']
            ?? 'Accommodation'));

        $nightlyBreakdown = $this->nightlyBreakdown($accommodation, $bookingData);
        $lines = [];

        foreach ($nightlyBreakdown as $night) {
            $rate = round((float) ($night['rate'] ?? $night['total_for_night'] ?? 0), 2);
            if ($rate <= 0) {
                continue;
            }

            $date = $night['date'] ?? null;
            $dateLabel = $date
                ? Carbon::parse($date)->format('M d, Y')
                : 'Nightly rate';

            $lines[] = [
                'type' => 'accommodation',
                'name' => "{$roomName} - {$dateLabel}",
                'unit_price' => $rate,
                'quantity' => 1,
                'total' => $rate,
            ];
        }

        $nights = max(1, count($nightlyBreakdown) ?: $this->nights($bookingData));
        $additionalGuests = max(0, (int) ($accommodation['additional_adults'] ?? $accommodation['extra_mattress'] ?? 0));
        $additionalGuestTotal = round((float) ($accommodation['additional_adult_price'] ?? $accommodation['mattress_price'] ?? 0), 2);

        if ($additionalGuests > 0 && $additionalGuestTotal > 0) {
            $quantity = max(1, $additionalGuests * $nights);
            $unitPrice = round($additionalGuestTotal / $quantity, 2);

            if (round($unitPrice * $quantity, 2) !== $additionalGuestTotal) {
                $quantity = 1;
                $unitPrice = $additionalGuestTotal;
            }

            $lines[] = [
                'type' => 'additional_guest',
                'name' => sprintf(
                    'Extra guest/mattress (%d guest%s x %d night%s)',
                    $additionalGuests,
                    $additionalGuests === 1 ? '' : 's',
                    $nights,
                    $nights === 1 ? '' : 's'
                ),
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total' => $additionalGuestTotal,
            ];
        }

        $petCount = max(0, (int) ($accommodation['num_pets'] ?? $accommodation['pet_count'] ?? 0));
        $petTotal = round((float) ($accommodation['pet_price'] ?? 0), 2);

        if ($petCount > 0 && $petTotal > 0) {
            $lines[] = [
                'type' => 'pet',
                'name' => sprintf('Pet fee (%d pet%s)', $petCount, $petCount === 1 ? '' : 's'),
                'unit_price' => round($petTotal / $petCount, 2),
                'quantity' => $petCount,
                'total' => $petTotal,
            ];
        }

        if ($lines === []) {
            $total = round((float) ($accommodation['total_price'] ?? 0), 2);
            if ($total > 0) {
                $lines[] = [
                    'type' => 'accommodation',
                    'name' => $roomName,
                    'unit_price' => $total,
                    'quantity' => 1,
                    'total' => $total,
                ];
            }
        }

        return $lines;
    }

    /**
     * Build PayMongo line items while preserving the existing final amount.
     *
     * @return array<int, array{name: string, unit_price: float, quantity: int}>
     */
    public function paymongoLineItems(array $bookingData): array
    {
        $lineItems = [];

        foreach ($bookingData['accommodations'] ?? [] as $accommodation) {
            if (! is_array($accommodation)) {
                continue;
            }

            foreach ($this->accommodationLines($accommodation, $bookingData) as $line) {
                $lineItems[] = [
                    'name' => $line['name'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                ];
            }
        }

        foreach ($bookingData['selected_foods'] ?? [] as $food) {
            if (is_array($food) && ! empty($food['name']) && isset($food['price'])) {
                $lineItems[] = [
                    'name' => $food['name'],
                    'unit_price' => (float) $food['price'],
                    'quantity' => max(1, (int) ($food['quantity'] ?? 1)),
                ];
            }
        }

        foreach ($bookingData['selected_activities'] ?? [] as $activity) {
            if (is_array($activity) && ! empty($activity['name']) && isset($activity['price'])) {
                $lineItems[] = [
                    'name' => $activity['name'],
                    'unit_price' => (float) $activity['price'],
                    'quantity' => max(1, (int) ($activity['guests'] ?? 1)),
                ];
            }
        }

        foreach ($bookingData['selected_activity_packages'] ?? [] as $package) {
            if (is_array($package) && ! empty($package['name']) && isset($package['price'])) {
                $lineItems[] = [
                    'name' => $package['name'],
                    'unit_price' => (float) $package['price'],
                    'quantity' => max(1, (int) ($package['quantity'] ?? 1)),
                ];
            }
        }

        return $lineItems;
    }

    public function nights(array $bookingData): int
    {
        if (empty($bookingData['checkin']) || empty($bookingData['checkout'])) {
            return 1;
        }

        try {
            return max(
                1,
                Carbon::parse($bookingData['checkin'])
                    ->startOfDay()
                    ->diffInDays(Carbon::parse($bookingData['checkout'])->startOfDay(), false)
            );
        } catch (\Throwable) {
            return 1;
        }
    }

    /**
     * @return array<int, array{date: string, rate: float}>
     */
    private function nightlyBreakdown(array $accommodation, array $bookingData): array
    {
        $savedBreakdown = $accommodation['nightly_breakdown'] ?? [];

        if (is_array($savedBreakdown) && $savedBreakdown !== []) {
            return array_values(array_filter(array_map(function ($night): ?array {
                if (! is_array($night)) {
                    return null;
                }

                return [
                    'date' => (string) ($night['date'] ?? ''),
                    'rate' => round((float) ($night['rate'] ?? $night['total_for_night'] ?? 0), 2),
                ];
            }, $savedBreakdown)));
        }

        $nights = $this->nights($bookingData);
        $baseTotal = round((float) ($accommodation['accommodation_price'] ?? $accommodation['base_price'] ?? 0), 2);
        if ($baseTotal <= 0) {
            return [];
        }

        $checkin = ! empty($bookingData['checkin']) ? Carbon::parse($bookingData['checkin']) : null;
        $unitRate = round($baseTotal / $nights, 2);
        $breakdown = [];
        $allocated = 0.0;

        for ($index = 0; $index < $nights; $index++) {
            $rate = $index === $nights - 1
                ? round($baseTotal - $allocated, 2)
                : $unitRate;
            $allocated += $rate;

            $breakdown[] = [
                'date' => $checkin?->copy()->addDays($index)->toDateString() ?? '',
                'rate' => $rate,
            ];
        }

        return $breakdown;
    }
}

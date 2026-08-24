<?php

namespace App\Services;

use App\Models\AddOnTransaction;
use Illuminate\Support\Facades\DB;

class AddOnExpirationService
{
    public function expireStaleForBookings(iterable $bookingIds): int
    {
        $bookingIds = collect($bookingIds)->filter()->unique()->values();
        if ($bookingIds->isEmpty()) {
            return 0;
        }

        $candidateIds = AddOnTransaction::query()
            ->whereIn('booking_id', $bookingIds)
            ->stalePending()
            ->pluck('id');

        return $candidateIds->sum(fn (int $id) => $this->expireById($id) ? 1 : 0);
    }

    public function expireAllStale(): int
    {
        $candidateIds = AddOnTransaction::query()
            ->stalePending()
            ->pluck('id');

        return $candidateIds->sum(fn (int $id) => $this->expireById($id) ? 1 : 0);
    }

    public function expireIfStale(AddOnTransaction $addOn): bool
    {
        if (!$addOn->isStalePending()) {
            return false;
        }

        return $this->expireById($addOn->id);
    }

    private function expireById(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $addOn = AddOnTransaction::query()->lockForUpdate()->find($id);
            if (!$addOn || !$addOn->isStalePending()) {
                return false;
            }

            $addOn->update([
                'status' => AddOnTransaction::STATUS_EXPIRED,
                'payment_status' => 'expired',
                'expired_at' => now(),
            ]);

            return true;
        });
    }
}

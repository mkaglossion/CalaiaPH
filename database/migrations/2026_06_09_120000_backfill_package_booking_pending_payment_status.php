<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('package_bookings')
            ->where('status', 'pending_payment')
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', '');
            })
            ->update([
                'payment_status' => 'pending',
            ]);
    }

    public function down(): void
    {
        DB::table('package_bookings')
            ->where('status', 'pending_payment')
            ->where('payment_status', 'pending')
            ->update([
                'payment_status' => null,
            ]);
    }
};

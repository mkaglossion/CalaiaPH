<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        $usersByEmail = DB::table('users')
            ->select(['id', 'email'])
            ->get()
            ->filter(fn ($user) => trim((string) $user->email) !== '')
            ->mapWithKeys(fn ($user) => [strtolower(trim((string) $user->email)) => $user->id]);

        DB::table('bookings')
            ->whereNull('user_id')
            ->select(['id', 'customer_email'])
            ->orderBy('id')
            ->chunkById(250, function ($bookings) use ($usersByEmail) {
                foreach ($bookings as $booking) {
                    $userId = $usersByEmail->get(strtolower(trim((string) $booking->customer_email)));

                    if ($userId) {
                        DB::table('bookings')->where('id', $booking->id)->update(['user_id' => $userId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};

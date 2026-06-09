<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('package_bookings', function (Blueprint $table) {
            $table->string('public_token', 80)->nullable()->unique()->after('booking_reference');
        });

        DB::table('package_bookings')
            ->select('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('package_bookings')
                        ->where('id', $row->id)
                        ->update(['public_token' => Str::random(64)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_bookings', function (Blueprint $table) {
            $table->dropUnique('package_bookings_public_token_unique');
            $table->dropColumn('public_token');
        });
    }
};

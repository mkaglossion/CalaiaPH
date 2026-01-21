<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Make slot_id nullable if it exists
            if (Schema::hasColumn('bookings', 'slot_id')) {
                $table->unsignedBigInteger('slot_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'slot_id')) {
                $table->unsignedBigInteger('slot_id')->nullable(false)->change();
            }
        });
    }
};

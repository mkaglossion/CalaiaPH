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
            if (!Schema::hasColumn('bookings', 'paymongo_checkout_id')) {
                $table->string('paymongo_checkout_id')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('bookings', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('paymongo_checkout_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'paymongo_checkout_id')) {
                $table->dropColumn('paymongo_checkout_id');
            }
            if (Schema::hasColumn('bookings', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
        });
    }
};
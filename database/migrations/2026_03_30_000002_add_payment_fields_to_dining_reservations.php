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
        // Add payment-related fields if they do not already exist
        if (!Schema::hasColumn('dining_reservations', 'total_amount') || !Schema::hasColumn('dining_reservations', 'payment_status')) {
            Schema::table('dining_reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('dining_reservations', 'total_amount')) {
                    $table->decimal('total_amount', 10, 2)->nullable()->default(0)->after('special_requests');
                }
                if (!Schema::hasColumn('dining_reservations', 'payment_status')) {
                    $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded'])->default('pending')->after('total_amount');
                }
                if (!Schema::hasColumn('dining_reservations', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('dining_reservations', 'payment_notes')) {
                    $table->text('payment_notes')->nullable()->after('payment_method');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dining_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('dining_reservations', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('dining_reservations', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('dining_reservations', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('dining_reservations', 'payment_notes')) {
                $table->dropColumn('payment_notes');
            }
        });
    }
};

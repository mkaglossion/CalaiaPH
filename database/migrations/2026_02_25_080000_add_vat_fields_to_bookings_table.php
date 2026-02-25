<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add VAT-related fields to bookings table for Philippine tax compliance.
     * VAT rate: 12% (per Philippine TRAIN Law)
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Subtotal before VAT (net amount)
            $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount')
                ->comment('Amount before VAT (net of VAT)');
            
            // VAT amount
            $table->decimal('vat_amount', 12, 2)->default(0)->after('subtotal')
                ->comment('12% VAT amount');
            
            // VAT rate (for flexibility if rate changes in the future)
            $table->decimal('vat_rate', 5, 4)->default(0.12)->after('vat_amount')
                ->comment('VAT rate applied (default 12%)');
            
            // VAT inclusive flag
            $table->boolean('is_vat_inclusive')->default(true)->after('vat_rate')
                ->comment('Whether prices include VAT (default: true for B2C)');
        });

        // Add index for VAT-related queries
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['vat_rate', 'created_at'], 'bookings_vat_rate_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_vat_rate_created_at_index');
            $table->dropColumn(['subtotal', 'vat_amount', 'vat_rate', 'is_vat_inclusive']);
        });
    }
};

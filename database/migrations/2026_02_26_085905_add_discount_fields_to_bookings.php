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
            $table->string('discount_type')->nullable()->comment('Type of discount: senior or pwd');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Discount amount applied');
            $table->decimal('discount_rate', 5, 4)->default(0)->comment('Discount rate as decimal');
            $table->boolean('is_vat_exempt')->default(false)->comment('Whether the booking is VAT-exempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_amount', 'discount_rate', 'is_vat_exempt']);
        });
    }
};

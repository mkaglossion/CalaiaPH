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
        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('booking_transaction_id')->index();
            $table->string('checkout_id')->nullable()->index();
            $table->string('payment_reference')->nullable()->index();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['promo_code_id', 'booking_transaction_id'], 'promo_code_usage_unique_booking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_usages');
    }
};

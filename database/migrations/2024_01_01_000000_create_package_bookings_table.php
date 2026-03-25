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
        Schema::create('package_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->date('booking_date');
            $table->string('session')->nullable(); // e.g., "morning", "afternoon", "evening"
            $table->integer('total_guests');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->text('special_requests')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending_payment', 'confirmed', 'cancelled', 'completed'])->default('pending_payment');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('paymongo_reference')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index(['email']);
            $table->index(['booking_date']);
            $table->index(['status']);
            $table->index(['payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_bookings');
    }
};

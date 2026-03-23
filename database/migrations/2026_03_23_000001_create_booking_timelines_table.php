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
        Schema::create('booking_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('event_type'); // 'status_changed', 'payment_received', 'verification', etc.
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('field_name')->nullable(); // e.g., 'status', 'payment_status', 'checkin_status'
            $table->text('description')->nullable(); // Human-friendly description
            $table->string('triggered_by')->default('system'); // 'system', 'admin', 'customer', 'webhook'
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // If triggered by admin
            $table->json('metadata')->nullable(); // Additional context (payment method, etc.)
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('booking_id');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_timelines');
    }
};

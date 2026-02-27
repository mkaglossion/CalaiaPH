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
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            
            // Customer Information
            $table->string('order_reference')->unique(); // e.g., SHOP-XXXXXX
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            
            // Order Details
            $table->json('cart_items'); // Store cart items as JSON
            $table->decimal('original_total', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discounted_total', 12, 2);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('final_total', 12, 2);
            
            // Delivery/Pickup
            $table->enum('delivery_option', ['pickup', 'delivery']);
            $table->text('delivery_address')->nullable();
            $table->date('pickup_date');
            
            // Payment
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('paymongo_checkout_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Discount Verification (same structure as bookings)
            $table->enum('discount_type', ['none', 'senior', 'pwd'])->default('none');
            $table->string('discount_id_number')->nullable();
            $table->string('id_image_path')->nullable(); // Cloudinary or local path
            $table->enum('discount_status', ['pending', 'verified', 'rejected', 'claimed'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->string('verified_by')->nullable();
            
            // Order Status
            $table->enum('order_status', ['pending', 'processing', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->text('special_requests')->nullable();
            
            $table->timestamps();
            
            // Indexes for filtering
            $table->index('discount_status');
            $table->index('discount_type');
            $table->index('order_status');
            $table->index('pickup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};

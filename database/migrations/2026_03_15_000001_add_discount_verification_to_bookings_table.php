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
            // Discount verification fields
            $table->string('discount_id_number')->nullable()->comment('Senior/PWD ID number');
            $table->string('discount_id_image')->nullable()->comment('Path to uploaded ID image');
            $table->enum('discount_verification_status', ['pending', 'verified', 'rejected', 'not_required'])->default('not_required')->comment('ID verification status');
            $table->text('discount_rejection_reason')->nullable()->comment('Reason if verification rejected');
            $table->timestamp('discount_verified_at')->nullable()->comment('When ID was verified');
            $table->foreignId('discount_verified_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin who verified the ID');
            
            // For "present at check-in" flow
            $table->boolean('discount_claimed_at_checkin')->default(false)->comment('Whether discount was claimed at check-in');
            $table->decimal('discount_difference_paid', 10, 2)->default(0)->comment('Amount paid if no ID at check-in');
            $table->timestamp('discount_difference_paid_at')->nullable()->comment('When difference was paid');
            
            // Verification method chosen by customer
            $table->enum('discount_verification_method', ['upload', 'present_at_checkin', 'none'])->default('none')->comment('How customer chose to verify');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'discount_id_number',
                'discount_id_image',
                'discount_verification_status',
                'discount_rejection_reason',
                'discount_verified_at',
                'discount_verified_by',
                'discount_claimed_at_checkin',
                'discount_difference_paid',
                'discount_difference_paid_at',
                'discount_verification_method',
            ]);
        });
    }
};

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
        // Add day-visit columns to existing dining_reservations table
        Schema::table('dining_reservations', function (Blueprint $table) {
            // Only add columns if they don't already exist
            if (!Schema::hasColumn('dining_reservations', 'type')) {
                $table->enum('type', ['day_visit', 'overnight_stay'])->default('overnight_stay')->after('id')->comment('Type of dining reservation');
            }
            if (!Schema::hasColumn('dining_reservations', 'guests')) {
                $table->integer('guests')->default(1)->after('adults')->comment('Total number of guests (for day-visit reservations)');
            }
            if (!Schema::hasColumn('dining_reservations', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('email')->comment('Customer name (for day-visit reservations)');
            }
            if (!Schema::hasColumn('dining_reservations', 'selected_foods')) {
                $table->json('selected_foods')->nullable()->after('special_requests')->comment('Array of selected foods with quantities (for day-visit)');
            }
            if (!Schema::hasColumn('dining_reservations', 'reservation_data')) {
                $table->json('reservation_data')->nullable()->after('selected_foods')->comment('Full reservation payload for audit trail');
            }
            if (!Schema::hasColumn('dining_reservations', 'messenger_reference')) {
                $table->string('messenger_reference')->nullable()->after('reservation_data')->comment('Reference for Messenger notification');
            }
        });

        // Add day-visit availability fields to foods table
        Schema::table('foods', function (Blueprint $table) {
            if (!Schema::hasColumn('foods', 'available_for_day_visit')) {
                $table->boolean('available_for_day_visit')->default(false)->comment('Food is available for day-visit dining reservations');
            }
            if (!Schema::hasColumn('foods', 'day_visit_only')) {
                $table->boolean('day_visit_only')->default(false)->comment('Food is only available for day visits, not for overnight stays');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dining_reservations', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'guests',
                'customer_name',
                'selected_foods',
                'reservation_data',
                'messenger_reference'
            ]);
        });

        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn([
                'available_for_day_visit',
                'day_visit_only'
            ]);
        });
    }
};

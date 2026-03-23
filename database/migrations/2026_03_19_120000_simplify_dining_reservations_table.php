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
        Schema::table('dining_reservations', function (Blueprint $table) {
            // Drop the columns no longer needed
            $table->dropColumn(['children', 'salutation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dining_reservations', function (Blueprint $table) {
            $table->integer('children')->default(0)->after('adults');
            $table->string('salutation')->nullable()->after('children');
        });
    }
};

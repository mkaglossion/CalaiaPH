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
        Schema::table('events', function (Blueprint $table) {
            // Add columns if they don't exist
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('events', 'features')) {
                $table->text('features')->nullable();
            }
            if (!Schema::hasColumn('events', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['description', 'features', 'image']);
        });
    }
};

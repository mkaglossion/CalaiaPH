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
        Schema::table('staycations', function (Blueprint $table) {
            // Only rename if 'quantity' exists and 'base_quantity' doesn't
            if (Schema::hasColumn('staycations', 'quantity') && !Schema::hasColumn('staycations', 'base_quantity')) {
                $table->renameColumn('quantity', 'base_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycations', function (Blueprint $table) {
            $table->renameColumn('base_quantity', 'quantity');
        });
    }
};

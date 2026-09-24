<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accommodation_variants')) {
            return;
        }

        Schema::create('accommodation_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staycation_accommodation_id')
                ->constrained('staycation_accommodations')
                ->cascadeOnDelete();
            $table->foreignId('staycation_id')
                ->constrained('staycations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_variants');
    }
};

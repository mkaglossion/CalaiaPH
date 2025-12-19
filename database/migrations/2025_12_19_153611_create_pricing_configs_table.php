<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pricing_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peak_period_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('holiday_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('duration_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('day_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_configs');
    }
};


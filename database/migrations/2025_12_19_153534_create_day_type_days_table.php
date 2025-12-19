<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('day_type_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_type_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('weekday'); // 0=Sun, 1=Mon ... 6=Sat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_type_days');
    }
};


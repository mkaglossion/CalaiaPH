<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('duration_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 1 Night, 2 Nights, etc.
            $table->integer('duration'); // number of nights
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duration_sets');
    }
};


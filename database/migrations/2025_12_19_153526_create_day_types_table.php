<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('day_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Weekday, Weekend, Holiday
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_types');
    }
};


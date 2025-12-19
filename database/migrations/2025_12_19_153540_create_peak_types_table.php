<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peak_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Non-Peak, Peak, Super-Peak
            $table->tinyInteger('priority'); // Higher = higher priority
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peak_types');
    }
};


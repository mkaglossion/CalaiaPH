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
        Schema::create('staycation_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staycation_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('available_quantity');
            $table->timestamps();

            $table->unique(['staycation_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staycation_inventories');
    }
};

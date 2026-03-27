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
        Schema::create('package_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->date('date');
            $table->integer('max_groups')->default(3);
            $table->integer('booked_groups')->default(0);
            $table->integer('available_groups')->default(3);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            // Foreign key
            $table->foreign('package_id')
                  ->references('id')
                  ->on('packages')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Indexes
            $table->index('package_id');
            $table->index('date');
            $table->unique(['package_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_inventory');
    }
};

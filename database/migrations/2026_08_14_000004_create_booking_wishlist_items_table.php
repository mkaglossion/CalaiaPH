<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 40);
            $table->unsignedBigInteger('item_id');
            $table->string('context_hash', 64);
            $table->json('booking_context');
            $table->json('selection')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'item_type', 'item_id', 'context_hash'],
                'booking_wishlist_unique_item'
            );
            $table->index(['user_id', 'context_hash'], 'booking_wishlist_user_context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_wishlist_items');
    }
};

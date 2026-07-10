<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->enum('guest_type', ['adult', 'child'])->index();
            $table->date('birth_date')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_number', 30);
            $table->string('emergency_contact_relationship');
            $table->timestamp('waiver_acknowledged_at')->nullable()->index();
            $table->timestamp('signed_at')->nullable()->index();
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signed_by_name')->nullable();
            $table->string('signed_by_relationship')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_primary_contact')->default(false)->index();
            $table->timestamps();

            $table->index(['booking_id', 'guest_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_guests');
    }
};

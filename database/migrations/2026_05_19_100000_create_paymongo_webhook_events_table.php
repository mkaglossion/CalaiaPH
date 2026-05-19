<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paymongo_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->nullable()->index();
            $table->string('resource_type')->nullable();
            $table->string('reference')->nullable()->index();
            $table->string('checkout_session_id')->nullable()->index();
            $table->string('payment_id')->nullable()->index();
            $table->bigInteger('amount_centavos')->nullable();
            $table->boolean('signature_present')->default(false)->index();
            $table->boolean('signature_valid')->default(false)->index();
            $table->boolean('handled')->default(false)->index();
            $table->unsignedSmallInteger('http_status')->default(200)->index();
            $table->string('message')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paymongo_webhook_events');
    }
};


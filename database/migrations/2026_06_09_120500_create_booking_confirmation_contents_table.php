<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_confirmation_contents', function (Blueprint $table) {
            $table->id();
            $table->string('booking_type')->unique();
            $table->string('page_eyebrow')->nullable();
            $table->string('page_title');
            $table->text('page_description')->nullable();
            $table->string('next_steps_title')->nullable();
            $table->json('next_steps')->nullable();
            $table->string('support_title')->nullable();
            $table->text('support_description')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('home_button_label')->nullable();
            $table->string('print_button_label')->nullable();
            $table->string('payment_button_label')->nullable();
            $table->string('email_subject')->nullable();
            $table->string('email_greeting')->nullable();
            $table->text('email_intro')->nullable();
            $table->text('email_closing')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('booking_confirmation_contents')->insert([
            [
                'booking_type' => 'staycation',
                'page_eyebrow' => 'CalaiaPH Staycation',
                'page_title' => 'Booking Confirmed!',
                'page_description' => 'Your staycation booking has been successfully processed and confirmed.',
                'next_steps_title' => 'What\'s Next?',
                'next_steps' => json_encode([
                    'Check your email for a detailed confirmation and booking instructions.',
                    'Save the date in your calendar so your trip is locked in.',
                    'Contact us if you need to make changes or have any questions.',
                ]),
                'support_title' => 'Need assistance?',
                'support_description' => 'Reach us anytime if you need help with your booking.',
                'support_email' => 'calaia.ph@gmail.com',
                'support_phone' => '+63 905 129 4741',
                'home_button_label' => 'Return to Home',
                'print_button_label' => 'Print Confirmation',
                'payment_button_label' => null,
                'email_subject' => 'Booking Receipt - CalaiaPH',
                'email_greeting' => 'Dear',
                'email_intro' => 'Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!',
                'email_closing' => 'We look forward to welcoming you to CalaiaPH!',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_type' => 'dayvisit',
                'page_eyebrow' => 'CalaiaPH Day Visit',
                'page_title' => 'Day Visit Confirmed!',
                'page_description' => 'Your day visit booking has been successfully processed and confirmed.',
                'next_steps_title' => 'What\'s Next?',
                'next_steps' => json_encode([
                    'Check your email for your confirmation and entry details.',
                    'Prepare your booking reference before your visit.',
                    'Contact us if you need to make changes or have any questions.',
                ]),
                'support_title' => 'Need assistance?',
                'support_description' => 'Reach us anytime if you need help with your booking.',
                'support_email' => 'calaia.ph@gmail.com',
                'support_phone' => '+63 905 129 4741',
                'home_button_label' => 'Return to Home',
                'print_button_label' => 'Print Confirmation',
                'payment_button_label' => null,
                'email_subject' => 'Booking Receipt - CalaiaPH',
                'email_greeting' => 'Dear',
                'email_intro' => 'Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!',
                'email_closing' => 'We look forward to welcoming you to CalaiaPH!',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_type' => 'package',
                'page_eyebrow' => 'CalaiaPH Package Booking',
                'page_title' => 'Confirm Your Payment',
                'page_description' => 'Review the booking summary below before proceeding to secure checkout.',
                'next_steps_title' => 'What\'s Next?',
                'next_steps' => json_encode([
                    'Review your booking details and total amount.',
                    'Proceed to secure checkout to complete payment.',
                    'Keep your reference number for any future support requests.',
                ]),
                'support_title' => 'Need help with your package booking?',
                'support_description' => 'Reach us anytime if you need help with your package payment.',
                'support_email' => 'calaia.ph@gmail.com',
                'support_phone' => '+63 905 129 4741',
                'home_button_label' => 'Back to Packages',
                'print_button_label' => null,
                'payment_button_label' => 'Proceed to Payment',
                'email_subject' => 'Booking Confirmation - {reference}',
                'email_greeting' => 'Hi',
                'email_intro' => 'Your package booking is confirmed.',
                'email_closing' => 'If you need changes to your reservation, contact us with your reference number.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_confirmation_contents');
    }
};

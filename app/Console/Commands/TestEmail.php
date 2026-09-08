<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test booking confirmation email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';

        $this->info("Sending test booking confirmation email to: {$email}");

        // Sample booking data
        $bookingData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'type' => 'day-visit',
            'package' => 'Adventure Package',
            'price' => 1500,
            'booking_date' => now()->format('Y-m-d'),
            'guests' => 2,
            'special_requests' => 'Test booking',
            'payment_method' => 'gcash',
            'payment_date' => now(),
            'transaction_id' => 'TEST-' . strtoupper(uniqid()),
        ];

        $selectedActivities = [
            [
                'name' => 'Farm Tour',
                'price' => 500,
                'guests' => 2,
            ]
        ];

        try {
            Mail::to($email)->sendNow(new BookingConfirmation([
                'bookingData' => $bookingData,
                'selectedActivities' => $selectedActivities,
                'grandTotal' => 2500,
                'customerInfo' => [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => $email,
                ],
                'isDayvisit' => true,
            ]));
            $this->info('Test email sent successfully!');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}

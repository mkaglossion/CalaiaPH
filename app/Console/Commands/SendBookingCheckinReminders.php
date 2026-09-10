<?php

namespace App\Console\Commands;

use App\Mail\BookingCheckinReminder;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingCheckinReminders extends Command
{
    protected $signature = 'bookings:send-checkin-reminders
        {--date= : Check-in date to process in YYYY-MM-DD format}';

    protected $description = 'Email customers and the official admin mailbox before confirmed paid staycation check-ins';

    public function handle(): int
    {
        $targetDate = $this->resolveTargetDate();

        if ($targetDate === null) {
            return self::FAILURE;
        }

        $sent = 0;
        $failed = 0;

        Booking::query()
            ->whereIn('booking_type', Booking::STAYCATION_TYPES)
            ->where('payment_status', 'paid')
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', $targetDate->toDateString())
            ->whereNull('checkin_reminder_sent_at')
            ->whereNotNull('customer_email')
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use (&$sent, &$failed, $targetDate) {
                foreach ($bookings as $booking) {
                    if (filter_var($booking->customer_email, FILTER_VALIDATE_EMAIL) === false) {
                        $failed++;
                        Log::warning('Skipped check-in reminder with invalid customer email', [
                            'booking_id' => $booking->id,
                            'transaction_id' => $booking->transaction_id,
                            'customer_email' => $booking->customer_email,
                            'target_date' => $targetDate->toDateString(),
                        ]);
                        continue;
                    }

                    try {
                        Mail::to($booking->customer_email)->sendNow(new BookingCheckinReminder($booking));

                        $booking->forceFill([
                            'checkin_reminder_sent_at' => now(),
                        ])->save();

                        $sent++;
                    } catch (\Throwable $exception) {
                        $failed++;
                        Log::error('Failed to send booking check-in reminder', [
                            'booking_id' => $booking->id,
                            'transaction_id' => $booking->transaction_id,
                            'customer_email' => $booking->customer_email,
                            'target_date' => $targetDate->toDateString(),
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Sent {$sent} check-in reminder email(s) for {$targetDate->toDateString()}.");

        if ($failed > 0) {
            $this->error("Failed or skipped {$failed} check-in reminder email(s). Check the application log.");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function resolveTargetDate(): ?CarbonImmutable
    {
        $requestedDate = trim((string) $this->option('date'));

        if ($requestedDate === '') {
            $daysBefore = max(1, (int) config('booking.checkin_reminder.days_before', 1));

            return CarbonImmutable::today(config('app.timezone', 'Asia/Manila'))->addDays($daysBefore);
        }

        try {
            $targetDate = CarbonImmutable::createFromFormat('Y-m-d', $requestedDate, config('app.timezone', 'Asia/Manila'));
        } catch (\Throwable) {
            $targetDate = null;
        }

        if ($targetDate === null || $targetDate->format('Y-m-d') !== $requestedDate) {
            $this->error('The --date option must use a valid YYYY-MM-DD date.');
            return null;
        }

        return $targetDate->startOfDay();
    }
}

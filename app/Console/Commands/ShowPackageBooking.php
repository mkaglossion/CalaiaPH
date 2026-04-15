<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PackageBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;

class ShowPackageBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:show {booking_reference}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show PackageBooking by booking_reference';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $ref = $this->argument('booking_reference');

        try {
            $booking = PackageBooking::where('booking_reference', $ref)->first();

            if (! $booking) {
                $this->error("Booking {$ref} not found");
                return 1;
            }

            $this->line(print_r($booking->toArray(), true));

            return 0;
        } catch (QueryException $e) {
            $this->error('Database query failed: '.$e->getMessage());
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }

        // Provide DB diagnostics to help fix local environment
        try {
            $default = DB::getDefaultConnection();
            $conn = Config::get('database.connections.'.$default, []);

            $host = $conn['host'] ?? 'unknown';
            $port = $conn['port'] ?? 'unknown';
            $database = $conn['database'] ?? 'unknown';
            $username = $conn['username'] ?? 'unknown';

            $this->line("\nDB diagnostics:");
            $this->line("Connection: {$default}");
            $this->line("Host: {$host}");
            $this->line("Port: {$port}");
            $this->line("Database: {$database}");
            $this->line("Username: {$username}");
        } catch (\Exception $e) {
            $this->line('Unable to read DB config: '.$e->getMessage());
        }

        $this->line("\nCommon fixes:\n- Ensure your DB server is running (MySQL/MariaDB).\n- Check `.env` DB_* values and `config/database.php`.\n- If using Docker, run your containers.\n- If using XAMPP, start MySQL service.");

        return 1;
    }
}

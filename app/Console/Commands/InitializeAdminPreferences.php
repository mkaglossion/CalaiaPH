<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AdminPreference;

class InitializeAdminPreferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:initialize-preferences';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Initialize admin preferences for all admin users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminUsers = User::where('user_type', 'admin')
            ->doesntHave('adminPreference')
            ->get();

        if ($adminUsers->isEmpty()) {
            $this->info('No admin users need preference initialization.');
            return;
        }

        foreach ($adminUsers as $user) {
            AdminPreference::create([
                'user_id' => $user->id,
                'email_low_inventory_alerts' => true,
                'email_pending_payments' => true,
                'email_bookings' => true,
                'email_user_signups' => false,
                'theme_preference' => 'light',
                'language' => 'en',
            ]);

            $this->line("Initialized preferences for {$user->email}");
        }

        $this->info("Admin preferences initialized for {$adminUsers->count()} users.");
    }
}

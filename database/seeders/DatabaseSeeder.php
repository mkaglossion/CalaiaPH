<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
    {
        $users = [
            [
                'user_type' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'mobile_number' => '09171234567',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
            ],
            [
                'user_type' => 'client',
                'first_name' => 'Client',
                'last_name' => 'User',
                'email' => 'client@example.com',
                'mobile_number' => '09179876543',
                'username' => 'client',
                'password' => Hash::make('client123'),
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']], // search condition
                $user // create if not exists
            );
        }
    }
}

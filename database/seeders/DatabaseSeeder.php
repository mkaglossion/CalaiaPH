<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DayType;
use App\Models\DayTypeDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            BarangaySeeder::class,
            DayTypeSeeder::class,
            PeakTypeSeeder::class,
            DurationSetSeeder::class,
            DayTypeDaySeeder::class,
            StaycationSeeder::class,
            FoodSeeder::class,
            CookYourOwnSeeder::class,
            DiningReservationSeeder::class,
            BookingSeeder::class,
        ]);

        // --- Users Seeder ---
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
            User::firstOrCreate(['email' => $user['email']], $user);
        }

        // // --- swiper_images table ---
        // DB::table('swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/RCN8hvzF2TGXMQexfd3lXJ92MU1NdOk4nipLlG42.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:13:40'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:13:40'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/wfNsGeiwBwNQ79rzudvJkHbilQtb2WS19AWQkaO5.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:13:46'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:13:46'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/3RMYLgg2zY2baM5T7ka5VecaCfZOTuF9QZmX4ABN.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:13:53'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:13:53'),
        //     ],
        // ]);

        // // --- day_tour_swiper_images table ---
        // DB::table('day_tour_swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/ZQeHHZjO2QddDefZynQ9EnbZFm1jc9m4h0yJ0v19.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:15'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:15'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/wk6tNz3k37ftvYJAwSPWZf02bdzkcNutYT4FvszJ.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:21'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:21'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/AG7UUbIZ0E6GTDbMKvWWeCeoJyJmKEQIIqDxSTxW.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:27'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:27'),
        //     ],
        // ]);

        // // --- stay_swiper_images table ---
        // DB::table('stay_swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/artgAyTsrvBinphiOv3LttT1rsHyqlUzjZaDYeQ9.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:15'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:15'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/3yBoFVWln9hhH6GDxlda6bws3Pf9Er171YIuGTgv.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:21'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:21'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/ERwTYlGrKGXozvYVlFnuRPeE9geg95bWw5J8hCmJ.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:27'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:27'),
        //     ],
        // ]);

        // // --- do_swiper_images table ---
        // DB::table('do_swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/3kQ59IWbq3VZXU2dbGLf8Wuh9gKKlDtSgegPrPli.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:15'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:15'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/SEJ01ay6WKTTINQwTXoBO6fag7I6D1gokH5d76Y8.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:21'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:21'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/WmLt6upJBGgZimIIlTudCU6ICH8ZUvNpzM4qthUn.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:27'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:27'),
        //     ],
        // ]);

        // // --- food_swiper_images table ---
        // DB::table('food_swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/2s5GTNRhYK6qOrXwFA57XphzOMWH45OgItVC9X9p.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:15'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:15'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/StcprU6Um8BHpPx6pOfmg1JhVrFREBOfN61tny9G.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:21'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:21'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/nFhRvrF3iJGX4LYutPZH4lOPqfsDWAVGNuyBMXyy.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:27'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:27'),
        //     ],
        // ]);

        // // --- special_swiper_images table ---
        // DB::table('special_swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/LSbxCSaFDAfn7ixK5i3STg3v57cEJODKlA57uak1.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:15'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:15'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/qkaVdbnTeXeSX6AsHJVCn9BLxxWYg2uiBzy06w4A.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:21'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:21'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/x7qk81D2sOc3mq9NqlrP6exlWEPMtFuqkHYl6MHH.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:27'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:27'),
        //     ],
        // ]);

        // // --- farm_swiper_images table ---
        // DB::table('farm_swiper_images')->insert([
        //     [
        //         'id' => 1,
        //         'title' => null,
        //         'image_path' => 'swiper_images/LKYedSsJ8jQFGtRY1usUU11pB1WGwZ0KI7HKxflN.png',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:15'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:15'),
        //     ],
        //     [
        //         'id' => 2,
        //         'title' => null,
        //         'image_path' => 'swiper_images/hPyJgId8xwMViXVbniFGix3h6jcaX7i0uIzO4j9a.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:21'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:21'),
        //     ],
        //     [
        //         'id' => 3,
        //         'title' => null,
        //         'image_path' => 'swiper_images/D56OPL9oJi2U9LIrOwXCpg0jJsqJ1VHrEudI5oSz.jpg',
        //         'created_at' => Carbon::parse('2025-11-07 11:20:27'),
        //         'updated_at' => Carbon::parse('2025-11-07 11:20:27'),
        //     ],
        // ]);
    }
}

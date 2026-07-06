<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tables = [
    'home_swiper_images',
    'day_tour_swiper_images',
    'stay_swiper_images',
    'do_swiper_images',
    'special_swiper_images',
    'farm_swiper_images',
    'package_swiper_images',
    'food_swiper_images',
    'menu_swiper_images',
    'activity_swiper_images',
    'event_swiper_images',
    'farm_info_swiper_images',
];
foreach ($tables as $table) {
    try {
        echo $table . '=' . Illuminate\Support\Facades\DB::table($table)->count() . PHP_EOL;
    } catch (Throwable $e) {
        echo $table . '=ERR' . PHP_EOL;
    }
}

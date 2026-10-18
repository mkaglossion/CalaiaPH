<?php

namespace Tests\Feature;

use App\Models\WeatherModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherForecastAccuracyTest extends TestCase
{
    public function test_forecast_uses_openweathermap_precipitation_probability(): void
    {
        $forecast = [
            'list' => [
                [
                    'dt' => 1_788_403_600,
                    'main' => ['temp' => 30.06, 'humidity' => 75],
                    'weather' => [['main' => 'Rain', 'icon' => '10d']],
                    'pop' => 1.0,
                    'rain' => ['3h' => 1.2],
                ],
                [
                    'dt' => 1_788_414_400,
                    'main' => ['temp' => 29.4, 'humidity' => 70],
                    'weather' => [['main' => 'Clouds', 'icon' => '04d']],
                    'pop' => 0.37,
                ],
            ],
        ];

        $formatted = (new WeatherModel())->formatHourlyForecast($forecast);

        $this->assertSame([100, 37], array_column($formatted, 'rain_probability'));
        $this->assertSame([30.0, 29.0], array_column($formatted, 'temperature'));
    }

    public function test_missing_api_data_is_not_replaced_with_random_weather(): void
    {
        $model = new WeatherModel();

        $current = $model->formatWeatherData(null);

        $this->assertFalse($current['available']);
        $this->assertNull($current['temperature']);
        $this->assertNull($current['rain_probability']);
        $this->assertSame([], $model->formatHourlyForecast(null));
    }

    public function test_weather_api_response_is_reused_from_cache(): void
    {
        Cache::flush();
        config()->set('weather.api_key', 'test-key');
        config()->set('weather.cache_minutes', 30);
        config()->set('weather.stale_minutes', 120);
        config()->set('weather.connect_timeout_seconds', 2);
        config()->set('weather.timeout_seconds', 4);

        Http::fake([
            '*' => Http::response(['cod' => 200, 'name' => 'Calaca'], 200),
        ]);

        $model = new WeatherModel();
        $first = $model->getWeatherByCoordinates(13.9298, 120.8133);
        $second = $model->getWeatherByCoordinates(13.9298, 120.8133);

        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_weather_failure_uses_last_successful_response(): void
    {
        Cache::flush();
        config()->set('weather.api_key', 'test-key');

        Http::fakeSequence()
            ->push(['cod' => 200, 'name' => 'Calaca'], 200)
            ->pushStatus(503);

        $model = new WeatherModel();
        $successful = $model->getWeatherByCoordinates(13.9298, 120.8133);

        $url = 'https://api.openweathermap.org/data/2.5/weather?lat=13.9298&lon=120.8133&appid=test-key&units=metric';
        $cacheKey = 'weather_api:' . sha1(preg_replace('/appid=[^&]+/', 'appid=hidden', $url));
        Cache::forget($cacheKey);
        Cache::forget('illuminate:cache:flexible:created:' . $cacheKey);

        $fallback = $model->getWeatherByCoordinates(13.9298, 120.8133);

        $this->assertSame($successful, $fallback);
        Http::assertSentCount(2);
    }

    public function test_weather_data_endpoint_returns_live_weather_and_forecast(): void
    {
        Cache::flush();
        config()->set('weather.api_key', 'test-key');
        config()->set('weather.cache_minutes', 30);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/weather?')) {
                return Http::response([
                    'cod' => 200,
                    'name' => 'Calaca',
                    'main' => ['temp' => 30, 'feels_like' => 32, 'humidity' => 70],
                    'weather' => [['main' => 'Clouds', 'description' => 'scattered clouds', 'icon' => '03d']],
                    'wind' => ['speed' => 2],
                    'clouds' => ['all' => 50],
                ]);
            }

            return Http::response([
                'list' => [[
                    'dt' => now()->timestamp,
                    'main' => ['temp' => 30, 'humidity' => 70],
                    'weather' => [['main' => 'Clouds', 'icon' => '03d']],
                    'pop' => 0.2,
                ]],
            ]);
        });

        $this->getJson(route('weather.data'))
            ->assertOk()
            ->assertJsonPath('location', 'Calaca')
            ->assertJsonPath('now.available', true)
            ->assertJsonPath('now.temp', 30)
            ->assertJsonPath('hourly.0.rain', 20);
    }
}

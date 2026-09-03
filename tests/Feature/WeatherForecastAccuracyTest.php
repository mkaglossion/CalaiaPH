<?php

namespace Tests\Feature;

use App\Models\WeatherModel;
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
}

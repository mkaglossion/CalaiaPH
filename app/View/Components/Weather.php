<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\WeatherModel;

class Weather extends Component
{
    public array $data;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $cacheKey = 'weather_widget:' . sha1(implode('|', [
            config('weather.latitude'),
            config('weather.longitude'),
            config('weather.api_key'),
        ]));

        $this->data = Cache::remember(
            $cacheKey,
            now()->addMinutes(max(1, (int) config('weather.cache_minutes', 30))),
            fn (): array => $this->buildWeatherData(new WeatherModel())
        );
    }

    private function buildWeatherData(WeatherModel $weatherModel): array
    {
        // Get current weather data
        $currentWeather = $this->getCurrentWeather($weatherModel);

        // Get hourly forecast
        $hourlyForecast = $this->getHourlyForecast($weatherModel);

        // The current-weather endpoint has no precipitation probability.
        // Use the nearest forecast period's official OpenWeatherMap POP value.
        if (!empty($hourlyForecast)) {
            $currentWeather['rain_probability'] = $hourlyForecast[0]['rain_probability'];
        }

        // Generate weather tips
        $tips = $this->generateWeatherTips($currentWeather);

        // Format data for the view
        return [
            'location' => $currentWeather['city'] ?? 'Calaia Nature Park',
            'now' => [
                'available' => $currentWeather['available'] ?? false,
                'temp' => $currentWeather['temperature'],
                'condition' => $currentWeather['condition'],
                'feels_like' => $currentWeather['feels_like'],
                'humidity' => $currentWeather['humidity'],
                'rain' => $currentWeather['rain_probability'],
            ],
            'hourly' => array_map(function($hour) {
                return [
                    'time' => $hour['time'],
                    'temp' => $hour['temperature'],
                    'rain' => $hour['rain_probability'],
                ];
            }, $hourlyForecast),
            'tips' => $tips,
        ];
    }

    /**
     * Get current weather data
     * @param WeatherModel $weatherModel
     * @return array Weather data
     */
    private function getCurrentWeather($weatherModel)
    {
        try {
            $lat = (float) config('weather.latitude');
            $lon = (float) config('weather.longitude');

            return $weatherModel->formatWeatherData(
                $weatherModel->getWeatherByCoordinates($lat, $lon)
            );

        } catch (\Exception $e) {
            Log::error('Weather API Error: ' . $e->getMessage());
            return $weatherModel->formatWeatherData(null);
        }
    }

    /**
     * Get hourly forecast data
     * @param WeatherModel $weatherModel
     * @return array Hourly forecast data
     */
    private function getHourlyForecast($weatherModel)
    {
        try {
            $lat = (float) config('weather.latitude');
            $lon = (float) config('weather.longitude');

            return $weatherModel->formatHourlyForecast(
                $weatherModel->getWeatherForecastByCoordinates($lat, $lon)
            );

        } catch (\Exception $e) {
            Log::error('Weather Forecast API Error: ' . $e->getMessage());
            return $weatherModel->formatHourlyForecast(null);
        }
    }

    /**
     * Generate weather tips based on current conditions
     * @param array $weatherData Current weather data
     * @return array Weather tips
     */
    private function generateWeatherTips($weatherData)
    {
        if (empty($weatherData['available'])) {
            return ['Live weather information is temporarily unavailable. Please check again shortly.'];
        }

        $tips = [];
        $temp = $weatherData['temperature'];
        $humidity = $weatherData['humidity'];
        $condition = strtolower($weatherData['condition']);
        $windSpeed = $weatherData['wind_speed'];
        $rainProbability = (int) ($weatherData['rain_probability'] ?? 0);
        $rainyConditions = str_contains($condition, 'rain')
            || str_contains($condition, 'drizzle')
            || str_contains($condition, 'thunderstorm');

        if ($rainProbability >= 80) {
            $tips[] = 'Very high chance of rain (' . $rainProbability . '%) - prioritize indoor activities';
            $tips[] = 'Bring an umbrella, raincoat, and waterproof footwear';
            $tips[] = 'Allow extra travel time and watch for wet or slippery paths';
        } elseif ($rainProbability >= 60) {
            $tips[] = 'Moderate chance of rain (' . $rainProbability . '%) - consider indoor alternatives for your visit';
            $tips[] = 'Keep rain gear handy and monitor conditions before outdoor activities';
        } elseif ($rainProbability >= 40) {
            $tips[] = 'Rain is possible (' . $rainProbability . '%) - check conditions before outdoor activities';
            $tips[] = 'Light rain gear recommended for extended outdoor time';
        } elseif ($rainyConditions) {
            $tips[] = 'Rain is currently reported - prioritize sheltered or indoor activities';
            $tips[] = 'Bring rain gear if you need to spend time outdoors';
        } elseif (str_contains($condition, 'cloud')) {
            $tips[] = 'Partly cloudy skies provide comfortable conditions for all activities';
            $tips[] = 'Good weather for both indoor and outdoor farm experiences';
        } elseif (str_contains($condition, 'clear')) {
            $tips[] = 'Clear skies perfect for stargazing and evening activities';
            $tips[] = 'Excellent visibility for nature photography and farm tours';
        }

        if ($rainProbability < 40 && !$rainyConditions) {
            if ($temp >= 35) {
                $tips[] = 'Extreme heat today - stay hydrated and avoid prolonged sun exposure';
                $tips[] = 'Take regular breaks in shaded or air-conditioned areas';
            } elseif ($temp >= 28) {
                $tips[] = 'Warm conditions - stay hydrated and use sun protection outdoors';
            } elseif ($temp < 20) {
                $tips[] = 'Cool conditions - consider bringing a light jacket';
            }
        } elseif ($temp >= 28) {
            $tips[] = 'Warm and wet conditions - stay hydrated and wear light, quick-drying clothing';
        }

        // Humidity-based tips
        if ($humidity > 80) {
            $tips[] = 'High humidity - stay comfortable with light, breathable clothing';
        } elseif ($humidity < 40) {
            $tips[] = 'Low humidity - drink water regularly during your visit';
        }

        // Wind-based tips
        if ($windSpeed > 5) {
            $tips[] = 'Breezy conditions - secure loose items during outdoor activities';
        }

        // General tips
        $tips[] = 'Check the latest forecast before starting outdoor activities';
        $tips[] = 'Calaia Nature Park offers both indoor and outdoor experiences for any weather';

        return array_slice($tips, 0, 6); // Return top 6 tips
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.weather', [
            'data' => $this->data
        ]);
    }
}

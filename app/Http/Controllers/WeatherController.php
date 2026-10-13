<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\WeatherModel;

class WeatherController extends Controller
{
    /** @var WeatherModel */
    private $weatherModel;

    public function __construct()
    {
        $this->weatherModel = new WeatherModel();
    }

    public function show()
    {
        // Get current weather data
        $currentWeather = $this->getCurrentWeather();

        // Get hourly forecast
        $hourlyForecast = $this->getHourlyForecast();

        // Use the nearest forecast period's official precipitation probability.
        if (!empty($hourlyForecast)) {
            $currentWeather['rain_probability'] = $hourlyForecast[0]['rain_probability'];
        }

        // Generate weather tips
        $tips = $this->generateWeatherTips($currentWeather);

        // Format data for the view
        $data = [
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

        return view('weather', compact('data'));
    }

    public function data()
    {
        return response()->json(app(\App\View\Components\Weather::class)->refreshWeatherData());
    }

    /**
     * Get current weather data
     * @return array Weather data
     */
    public function getCurrentWeather()
    {
        try {
            $lat = (float) config('weather.latitude');
            $lon = (float) config('weather.longitude');

            return $this->weatherModel->formatWeatherData(
                $this->weatherModel->getWeatherByCoordinates($lat, $lon)
            );

        } catch (\Exception $e) {
            Log::error('Weather API Error: ' . $e->getMessage());
            return $this->weatherModel->formatWeatherData(null);
        }
    }

    /**
     * Get hourly forecast data
     * @return array Hourly forecast data
     */
    public function getHourlyForecast()
    {
        try {
            $lat = (float) config('weather.latitude');
            $lon = (float) config('weather.longitude');

            return $this->weatherModel->formatHourlyForecast(
                $this->weatherModel->getWeatherForecastByCoordinates($lat, $lon)
            );

        } catch (\Exception $e) {
            Log::error('Weather Forecast API Error: ' . $e->getMessage());
            return $this->weatherModel->formatHourlyForecast(null);
        }
    }

    /**
     * Get weather icon class for Bootstrap Icons
     * @param string $iconCode OpenWeatherMap icon code
     * @return string Bootstrap Icon class
     */
    public function getWeatherIconClass($iconCode)
    {
        return $this->weatherModel->getWeatherIconClass($iconCode);
    }

    /**
     * Get temperature class for styling
     * @param float $temperature Temperature in Celsius
     * @return string Temperature class
     */
    public function getTemperatureClass($temperature)
    {
        return $this->weatherModel->getTemperatureClass($temperature);
    }

    /**
     * Get rain forecast information
     * @return array Rain forecast data
     */
    public function getRainForecast()
    {
        $currentWeather = $this->getCurrentWeather();
        return [
            'current_rain_probability' => $currentWeather['rain_probability'] ?? 0,
            'current_rain_volume_1h' => $currentWeather['rain_volume_1h'] ?? 0,
            'current_rain_volume_3h' => $currentWeather['rain_volume_3h'] ?? 0,
        ];
    }

    /**
     * Check if it's currently raining
     * @return bool True if raining, false otherwise
     */
    public function isRaining()
    {
        $currentWeather = $this->getCurrentWeather();
        $rainProbability = $currentWeather['rain_probability'] ?? 0;
        $condition = strtolower($currentWeather['condition'] ?? '');
        $rainVolume = ($currentWeather['rain_volume_1h'] ?? 0) + ($currentWeather['rain_volume_3h'] ?? 0);

        // Consider it raining if:
        // - Rain probability > 70%
        // - Condition contains 'rain'
        // - There's actual rain volume
        return $rainProbability > 70 || strpos($condition, 'rain') !== false || $rainVolume > 0;
    }

    /**
     * Check if rain is expected in the forecast
     * @param int $hoursAhead Number of hours to check ahead (default: 6)
     * @return bool True if rain is expected, false otherwise
     */
    public function willRainInForecast($hoursAhead = 6)
    {
        $hourlyForecast = $this->getHourlyForecast();
        $hoursToCheck = min($hoursAhead, count($hourlyForecast));

        for ($i = 0; $i < $hoursToCheck; $i++) {
            $hour = $hourlyForecast[$i];
            $rainProbability = $hour['rain_probability'] ?? 0;
            $condition = strtolower($hour['condition'] ?? '');

            // Consider rain expected if:
            // - Rain probability > 60%
            // - Condition contains 'rain'
            if ($rainProbability > 60 || strpos($condition, 'rain') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate weather tips based on current conditions
     * @param array $weatherData Current weather data
     * @return array Weather tips
     */
    public function generateWeatherTips($weatherData)
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
}

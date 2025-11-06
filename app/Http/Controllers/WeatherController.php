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

        // Generate weather tips
        $tips = $this->generateWeatherTips($currentWeather);

        // Format data for the view
        $data = [
            'location' => $currentWeather['city'] ?? 'Calaia Nature Park',
            'now' => [
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

    /**
     * Get current weather data
     * @return array Weather data
     */
    public function getCurrentWeather()
    {
        try {
            // Try to get weather for Calaia Nature Park location
            // Coordinates for Calaia Nature Park (approximate)
            $lat = 14.0155;
            $lon = 120.8454;

            $weatherData = $this->weatherModel->getWeatherByCoordinates($lat, $lon);

            if ($weatherData) {
                return $this->weatherModel->formatWeatherData($weatherData);
            }

            // Fallback to city-based lookup
            return $this->weatherModel->formatWeatherData(
                $this->weatherModel->getCurrentWeather('Santa Rosa', 'PH')
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
            // Try to get forecast for Calaia Nature Park location
            $lat = 14.0155;
            $lon = 120.8454;

            $forecastData = $this->weatherModel->getWeatherByCoordinates($lat, $lon);

            if ($forecastData) {
                // For forecast, we'll use the city-based approach
                $cityForecast = $this->weatherModel->getWeatherForecast('Santa Rosa', 'PH');
                return $this->weatherModel->formatHourlyForecast($cityForecast);
            }

            return $this->weatherModel->formatHourlyForecast(null);

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
        $tips = [];
        $temp = $weatherData['temperature'];
        $humidity = $weatherData['humidity'];
        $condition = strtolower($weatherData['condition']);
        $windSpeed = $weatherData['wind_speed'];

        // Temperature-based tips
        if ($temp >= 35) {
            $tips[] = 'Extreme heat today - stay hydrated and avoid prolonged sun exposure';
            $tips[] = 'Perfect weather for indoor activities and air-conditioned spaces';
        } elseif ($temp >= 28) {
            $tips[] = 'Warm weather ideal for outdoor activities and farm tours';
            $tips[] = 'Remember to stay hydrated and use sunscreen for outdoor activities';
        } elseif ($temp >= 20) {
            $tips[] = 'Comfortable temperatures perfect for all farm activities';
            $tips[] = 'Great weather for nature walks and outdoor exploration';
        } elseif ($temp >= 15) {
            $tips[] = 'Cooler weather - perfect for light jackets and warm drinks';
            $tips[] = 'Ideal conditions for farm tours and nature activities';
        } else {
            $tips[] = 'Cold weather - dress warmly for outdoor activities';
            $tips[] = 'Perfect weather for hot beverages and cozy indoor spaces';
        }

        // Rain probability tips
        $rainProbability = $weatherData['rain_probability'] ?? 0;
        if ($rainProbability >= 80) {
            $tips[] = 'High chance of rain (' . $rainProbability . '%) - perfect day for indoor farm experiences and cozy activities';
            $tips[] = 'Pack rain gear, umbrellas, and waterproof clothing for outdoor activities';
            $tips[] = 'Great weather for our indoor workshops and farm-to-table dining experiences';
        } elseif ($rainProbability >= 60) {
            $tips[] = 'Moderate chance of rain (' . $rainProbability . '%) - consider indoor alternatives for your visit';
            $tips[] = 'Keep rain gear handy and check weather updates before outdoor activities';
        } elseif ($rainProbability >= 40) {
            $tips[] = 'Possible light rain (' . $rainProbability . '%) - mostly good conditions for farm activities';
            $tips[] = 'Light rain gear recommended for extended outdoor time';
        } elseif (strpos($condition, 'rain') !== false) {
            $tips[] = 'Rain expected - perfect for indoor activities and farm experiences';
            $tips[] = 'Don\'t forget rain gear if planning outdoor activities';
        } elseif (strpos($condition, 'cloud') !== false) {
            $tips[] = 'Partly cloudy skies provide comfortable conditions for all activities';
            $tips[] = 'Good weather for both indoor and outdoor farm experiences';
        } elseif (strpos($condition, 'clear') !== false) {
            $tips[] = 'Clear skies perfect for stargazing and evening activities';
            $tips[] = 'Excellent visibility for nature photography and farm tours';
        }

        // Humidity-based tips
        if ($humidity > 80) {
            $tips[] = 'High humidity - stay comfortable with light, breathable clothing';
        } elseif ($humidity < 40) {
            $tips[] = 'Low humidity - perfect for outdoor activities, minimal sweat';
        }

        // Wind-based tips
        if ($windSpeed > 5) {
            $tips[] = 'Breezy conditions - secure loose items during outdoor activities';
        }

        // General tips
        $tips[] = 'Always check weather conditions before planning outdoor activities';
        $tips[] = 'Calaia Nature Park offers both indoor and outdoor experiences for any weather';

        return array_slice($tips, 0, 6); // Return top 6 tips
    }
}
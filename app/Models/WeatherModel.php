<?php

namespace App\Models;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;

class WeatherModel {
    private string $apiKey;
    private string $baseUrl;
    private int $cacheMinutes;
    private int $timeoutSeconds;
    private bool $demoMode = false;

    public function __construct() {
        $this->apiKey = (string) config('weather.api_key', '');
        $this->baseUrl = rtrim((string) config('weather.base_url'), '/');
        $this->cacheMinutes = max(0, (int) config('weather.cache_minutes', 30));
        $this->timeoutSeconds = max(1, (int) config('weather.timeout_seconds', 10));

        // Keep demo data available for unconfigured local/test environments.
        if ($this->apiKey === '' || $this->apiKey === 'demo_key_for_testing' || $this->apiKey === 'your_openweathermap_api_key_here') {
            $this->demoMode = true;
        }
    }

    /**
     * Get current weather data for a location
     * @param string $city City name
     * @param string $country Country code (optional)
     * @return array|null Weather data or null on error
     */
    public function getCurrentWeather($city = 'Manila', $country = 'PH') {
        $url = $this->baseUrl . '/weather?q=' . urlencode($city . ',' . $country) . '&appid=' . $this->apiKey . '&units=metric';

        return $this->makeApiCall($url);
    }

    /**
     * Get weather forecast data for a location
     * @param string $city City name
     * @param string $country Country code (optional)
     * @return array|null Forecast data or null on error
     */
    public function getWeatherForecast($city = 'Manila', $country = 'PH') {
        if ($this->demoMode) {
            return null;
        }

        $url = $this->baseUrl . '/forecast?q=' . urlencode($city . ',' . $country) . '&appid=' . $this->apiKey . '&units=metric';

        return $this->makeApiCall($url);
    }

    /**
     * Get weather forecast data by coordinates.
     */
    public function getWeatherForecastByCoordinates(float $lat, float $lon): ?array {
        if ($this->demoMode) {
            return null;
        }

        $url = $this->baseUrl . '/forecast?lat=' . $lat . '&lon=' . $lon . '&appid=' . $this->apiKey . '&units=metric';

        return $this->makeApiCall($url);
    }

    /**
     * Get weather data by coordinates
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|null Weather data or null on error
     */
    public function getWeatherByCoordinates(float $lat, float $lon): ?array {
        if ($this->demoMode) {
            return null;
        }

        $url = $this->baseUrl . '/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $this->apiKey . '&units=metric';

        return $this->makeApiCall($url);
    }

    /**
     * Make API call to OpenWeatherMap
     * @param string $url API URL
     * @return array|null Response data or null on error
     */
    private function makeApiCall(string $url): ?array {
        if ($this->demoMode) {
            return null;
        }

        $cacheKey = 'weather_api:' . sha1(preg_replace('/appid=[^&]+/', 'appid=hidden', $url));

        if ($this->cacheMinutes === 0) {
            return $this->fetchApiData($url);
        }

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($this->cacheMinutes),
            fn () => $this->fetchApiData($url)
        );
    }

    /**
     * Fetch and validate an OpenWeatherMap response.
     */
    private function fetchApiData(string $url): ?array {

        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
        } catch (\Throwable $exception) {
            return null;
        }

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }

        if (isset($data['cod']) && (int) $data['cod'] >= 400) {
            return null;
        }

        return $data;
    }

    /**
     * Get weather icon class for Bootstrap Icons
     * @param string $iconCode OpenWeatherMap icon code
     * @return string Bootstrap Icon class
     */
    public function getWeatherIconClass($iconCode) {
        $iconMap = [
            '01d' => 'bi-sun',      // clear sky day
            '01n' => 'bi-moon',     // clear sky night
            '02d' => 'bi-cloud-sun', // few clouds day
            '02n' => 'bi-cloud-moon', // few clouds night
            '03d' => 'bi-cloud',    // scattered clouds
            '03n' => 'bi-cloud',    // scattered clouds
            '04d' => 'bi-clouds',   // broken clouds
            '04n' => 'bi-clouds',   // broken clouds
            '09d' => 'bi-cloud-rain-heavy', // shower rain
            '09n' => 'bi-cloud-rain-heavy', // shower rain
            '10d' => 'bi-cloud-rain', // rain day
            '10n' => 'bi-cloud-rain', // rain night
            '11d' => 'bi-cloud-lightning', // thunderstorm
            '11n' => 'bi-cloud-lightning', // thunderstorm
            '13d' => 'bi-snow',     // snow
            '13n' => 'bi-snow',     // snow
            '50d' => 'bi-cloud-fog', // mist
            '50n' => 'bi-cloud-fog', // mist
        ];

        return $iconMap[$iconCode] ?? 'bi-cloud';
    }

    /**
     * Get temperature class for styling
     * @param float $temperature Temperature in Celsius
     * @return string Temperature class
     */
    public function getTemperatureClass($temperature) {
        if ($temperature >= 35) return 'hot';
        elseif ($temperature >= 28) return 'warm';
        elseif ($temperature >= 20) return 'moderate';
        elseif ($temperature >= 15) return 'cool';
        else return 'cold';
    }

    /**
     * Format weather data for display
     * @param array $weatherData Raw weather data from API
     * @return array Formatted weather data
     */
    public function formatWeatherData($weatherData) {
        if (!$weatherData) {
            return $this->getDefaultWeatherData();
        }

        $main = $weatherData['main'] ?? [];
        $weather = $weatherData['weather'][0] ?? [];
        $wind = $weatherData['wind'] ?? [];
        $clouds = $weatherData['clouds'] ?? [];
        $sys = $weatherData['sys'] ?? [];

        return [
            'available' => true,
            'temperature' => round($main['temp'] ?? 28),
            'feels_like' => round($main['feels_like'] ?? 30),
            'humidity' => $main['humidity'] ?? 65,
            'pressure' => $main['pressure'] ?? 1013,
            'visibility' => $weatherData['visibility'] ?? 10000,
            'wind_speed' => $wind['speed'] ?? 0,
            'wind_direction' => $wind['deg'] ?? 0,
            'cloudiness' => $clouds['all'] ?? 0,
            'condition' => $weather['main'] ?? 'Partly Cloudy',
            'description' => $weather['description'] ?? 'partly cloudy',
            'icon' => $weather['icon'] ?? '02d',
            'city' => $weatherData['name'] ?? 'Calaia Nature Park',
            'country' => $sys['country'] ?? 'PH',
            'sunrise' => $sys['sunrise'] ?? time(),
            'sunset' => $sys['sunset'] ?? time() + 12*3600,
            'timezone' => $weatherData['timezone'] ?? 28800,
            'rain_probability' => $this->calculateRainProbability($weatherData),
            'rain_volume_1h' => $weatherData['rain']['1h'] ?? 0,
            'rain_volume_3h' => $weatherData['rain']['3h'] ?? 0,
        ];
    }

    /**
     * Calculate rain probability based on weather data
     * @param array $weatherData Raw weather data from API
     * @return int Rain probability percentage (0-100)
     */
    private function calculateRainProbability($weatherData) {
        if (!$weatherData) {
            return 0;
        }

        // Forecast responses provide OpenWeatherMap's probability of precipitation.
        if (isset($weatherData['pop']) && is_numeric($weatherData['pop'])) {
            return (int) round(min(1, max(0, (float) $weatherData['pop'])) * 100);
        }

        $weather = $weatherData['weather'][0] ?? [];
        $clouds = $weatherData['clouds']['all'] ?? 0;
        $main = $weather['main'] ?? '';

        // Base probability on weather condition
        $baseProbability = 0;

        switch (strtolower($main)) {
            case 'rain':
                $baseProbability = 90;
                break;
            case 'drizzle':
                $baseProbability = 80;
                break;
            case 'thunderstorm':
                $baseProbability = 95;
                break;
            case 'clouds':
            case 'overcast clouds':
                $baseProbability = 30;
                break;
            case 'clear':
            case 'clear sky':
                $baseProbability = 5;
                break;
            default:
                $baseProbability = 20;
        }

        // Adjust based on cloudiness
        if ($clouds > 80) {
            $baseProbability = min(95, $baseProbability + 20);
        } elseif ($clouds > 60) {
            $baseProbability = min(90, $baseProbability + 10);
        } elseif ($clouds < 20) {
            $baseProbability = max(5, $baseProbability - 15);
        }

        // If there's actual rain data in the response, use it
        if (isset($weatherData['rain'])) {
            if (isset($weatherData['rain']['1h']) && $weatherData['rain']['1h'] > 0) {
                $baseProbability = min(100, 70 + ($weatherData['rain']['1h'] * 10));
            } elseif (isset($weatherData['rain']['3h']) && $weatherData['rain']['3h'] > 0) {
                $baseProbability = min(100, 60 + ($weatherData['rain']['3h'] * 5));
            }
        }

        return min(100, max(0, $baseProbability));
    }

    /**
     * Get default weather data when API is unavailable
     * @return array Default weather data
     */
    private function getDefaultWeatherData() {
        return [
            'available' => false,
            'temperature' => null,
            'feels_like' => null,
            'humidity' => null,
            'pressure' => null,
            'visibility' => null,
            'wind_speed' => null,
            'wind_direction' => null,
            'cloudiness' => null,
            'condition' => 'Weather data unavailable',
            'description' => 'Weather data unavailable',
            'icon' => null,
            'city' => config('weather.city'),
            'country' => 'PH',
            'sunrise' => null,
            'sunset' => null,
            'timezone' => null,
            'rain_probability' => null,
            'rain_volume_1h' => null,
            'rain_volume_3h' => null,
        ];
    }

    /**
     * Get hourly forecast data
     * @param array $forecastData Raw forecast data from API
     * @return array Hourly forecast data
     */
    public function formatHourlyForecast($forecastData) {
        if (!$forecastData || !isset($forecastData['list'])) {
            return $this->getDefaultHourlyForecast();
        }

        $hourlyData = [];
        $count = 0;

        foreach ($forecastData['list'] as $forecast) {
            if ($count >= 6) break; // OpenWeatherMap provides 3-hour forecast periods.

            $dt = new DateTime('@' . $forecast['dt']);
            $dt->setTimezone(new DateTimeZone('Asia/Manila'));

            $hourlyData[] = [
                'time' => $dt->format('g:i A'),
                'temperature' => round($forecast['main']['temp']),
                'condition' => $forecast['weather'][0]['main'] ?? 'Clear',
                'icon' => $forecast['weather'][0]['icon'] ?? '01d',
                'humidity' => $forecast['main']['humidity'] ?? 0,
                'rain_probability' => $this->calculateRainProbability($forecast),
            ];

            $count++;
        }

        return $hourlyData;
    }

    /**
     * Get default hourly forecast when API is unavailable
     * @return array Default hourly forecast
     */
    private function getDefaultHourlyForecast() {
        return [];
    }
}

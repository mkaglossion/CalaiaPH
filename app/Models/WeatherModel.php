<?php

namespace App\Models;

use DateTime;
use DateTimeZone;

class WeatherModel {
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5';
    private $demoMode = false;

    public function __construct() {
        // You can set your API key here or use environment variable
        $this->apiKey = getenv('OPENWEATHER_API_KEY') ?: 'demo_key_for_testing';

        // If using demo key, return realistic test data
        if ($this->apiKey === 'demo_key_for_testing' || $this->apiKey === 'your_openweathermap_api_key_here') {
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
        // If in demo mode, generate forecast data
        if ($this->demoMode) {
            return $this->getDemoForecastData();
        }

        $url = $this->baseUrl . '/forecast?q=' . urlencode($city . ',' . $country) . '&appid=' . $this->apiKey . '&units=metric';

        return $this->makeApiCall($url);
    }

    /**
     * Get demo forecast data
     * @return array Demo forecast data
     */
    private function getDemoForecastData() {
        $forecast = [];
        $currentTime = time();

        for ($i = 0; $i < 6; $i++) {
            $forecastTime = $currentTime + ($i * 3 * 3600); // Every 3 hours
            $tempVariation = rand(-2, 2);

            $forecast[] = [
                'dt' => $forecastTime,
                'main' => [
                    'temp' => 28 + $tempVariation,
                    'humidity' => rand(60, 75)
                ],
                'weather' => [
                    [
                        'main' => 'Partly Cloudy',
                        'icon' => '02d',
                        'description' => 'partly cloudy'
                    ]
                ]
            ];
        }

        return ['list' => $forecast];
    }

    /**
     * Get weather data by coordinates
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|null Weather data or null on error
     */
    public function getWeatherByCoordinates($lat, $lon) {
        // If in demo mode, return demo data
        if ($this->demoMode) {
            return $this->getDemoWeatherData();
        }

        $url = $this->baseUrl . '/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $this->apiKey . '&units=metric';

        return $this->makeApiCall($url);
    }

    /**
     * Make API call to OpenWeatherMap
     * @param string $url API URL
     * @return array|null Response data or null on error
     */
    private function makeApiCall($url) {
        // If in demo mode, return realistic test data
        if ($this->demoMode) {
            return $this->getDemoWeatherData();
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * Get demo weather data for testing
     * @return array Demo weather data
     */
    private function getDemoWeatherData() {
        $currentHour = date('H');
        $baseTemp = 28; // Base temperature for the location

        // Simulate temperature variation throughout the day
        if ($currentHour >= 6 && $currentHour <= 12) {
            $temp = $baseTemp + rand(2, 5); // Morning: warmer
        } elseif ($currentHour >= 13 && $currentHour <= 17) {
            $temp = $baseTemp + rand(5, 8); // Afternoon: hottest
        } else {
            $temp = $baseTemp + rand(0, 3); // Evening/Night: cooler
        }

        return [
            'main' => [
                'temp' => $temp,
                'feels_like' => $temp + rand(1, 3),
                'humidity' => rand(60, 75),
                'pressure' => rand(1005, 1015)
            ],
            'weather' => [
                [
                    'main' => 'Partly Cloudy',
                    'description' => 'partly cloudy',
                    'icon' => '02d'
                ]
            ],
            'wind' => [
                'speed' => rand(2, 8),
                'deg' => rand(0, 360)
            ],
            'clouds' => [
                'all' => rand(20, 40)
            ],
            'name' => 'Calaia Nature Park',
            'sys' => [
                'country' => 'PH'
            ],
            'visibility' => rand(8000, 10000),
            'timezone' => 28800
        ];
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
            'temperature' => 28,
            'feels_like' => 30,
            'humidity' => 65,
            'pressure' => 1013,
            'visibility' => 10000,
            'wind_speed' => 2.5,
            'wind_direction' => 180,
            'cloudiness' => 25,
            'condition' => 'Partly Cloudy',
            'description' => 'partly cloudy',
            'icon' => '02d',
            'city' => 'Calaia Nature Park',
            'country' => 'PH',
            'sunrise' => time(),
            'sunset' => time() + 12*3600,
            'timezone' => 28800,
            'rain_probability' => 20,
            'rain_volume_1h' => 0,
            'rain_volume_3h' => 0,
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
            if ($count >= 6) break; // Get next 6 hours

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

        // If we don't have enough data, fill with defaults
        while (count($hourlyData) < 6) {
            $defaultData = $this->getDefaultHourlyForecast();
            $hourlyData[] = $defaultData[count($hourlyData)];
        }

        return $hourlyData;
    }

    /**
     * Get default hourly forecast when API is unavailable
     * @return array Default hourly forecast
     */
    private function getDefaultHourlyForecast() {
        $currentHour = date('G');
        $forecast = [];

        for ($i = 0; $i < 6; $i++) {
            $hour = ($currentHour + $i) % 24;
            $time = date('g:i A', strtotime("+$i hours"));

            $forecast[] = [
                'time' => $time,
                'temperature' => 28 + rand(-2, 2), // Random variation
                'condition' => 'Partly Cloudy',
                'icon' => '02d',
                'humidity' => 65 + rand(-10, 10),
                'rain_probability' => rand(10, 30), // Random rain probability for demo
            ];
        }

        return $forecast;
    }
}
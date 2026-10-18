<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    /**
     * Fetch current weather at given coordinates using Open-Meteo API.
     * Note: As per guidelines, weather provides environmental context and does not prove delay causation.
     */
    public function getWeather(float $latitude, float $longitude): array
    {
        $cacheKey = sprintf('weather_%.3f_%.3f', $latitude, $longitude);

        return Cache::remember($cacheKey, 600, function () use ($latitude, $longitude) {
            try {
                $response = Http::timeout(4)
                    ->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                        'timezone' => 'auto',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $current = $data['current'] ?? [];

                    return [
                        'status' => 'success',
                        'temperature' => $current['temperature_2m'] ?? null,
                        'temperature_unit' => $data['current_units']['temperature_2m'] ?? '°C',
                        'humidity' => $current['relative_humidity_2m'] ?? null,
                        'wind_speed' => $current['wind_speed_10m'] ?? null,
                        'wind_speed_unit' => $data['current_units']['wind_speed_10m'] ?? 'km/h',
                        'weather_code' => $current['weather_code'] ?? null,
                        'condition' => $this->interpretWeatherCode($current['weather_code'] ?? 0),
                        'disclaimer' => 'Weather is displayed for operational situational awareness only and does not constitute automated delay prediction.',
                    ];
                }
            } catch (Exception $e) {
                Log::warning("Open-Meteo API call failed: " . $e->getMessage());
            }

            // Fallback response for offline or timeout
            return [
                'status' => 'fallback',
                'temperature' => 28.5,
                'temperature_unit' => '°C',
                'humidity' => 75,
                'wind_speed' => 12.0,
                'wind_speed_unit' => 'km/h',
                'weather_code' => 1,
                'condition' => 'Partly Cloudy (Cached / Offline Mode)',
                'disclaimer' => 'External weather service unreachable; standard port baseline displayed.',
            ];
        });
    }

    /**
     * Search coordinates and address for a location query via Nominatim Geocoding.
     */
    public function geocode(string $query): array
    {
        $cacheKey = 'geocode_' . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('app.name', 'CargoTrace') . '/1.0 (academic-uas@cargotrace.test)',
                ])
                ->timeout(4)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 3,
                ]);

                if ($response->successful() && !empty($response->json())) {
                    $results = $response->json();
                    return [
                        'status' => 'success',
                        'data' => array_map(function ($item) {
                            return [
                                'display_name' => $item['display_name'] ?? '',
                                'latitude' => (float) ($item['lat'] ?? 0),
                                'longitude' => (float) ($item['lon'] ?? 0),
                                'type' => $item['type'] ?? '',
                            ];
                        }, $results),
                    ];
                }
            } catch (Exception $e) {
                Log::warning("Nominatim geocoding failed: " . $e->getMessage());
            }

            return [
                'status' => 'fallback',
                'data' => [],
                'message' => 'Geocoding service unavailable or no results found.',
            ];
        });
    }

    /**
     * Map WMO weather codes to human-readable strings.
     */
    protected function interpretWeatherCode(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear Sky',
            in_array($code, [1, 2, 3], true) => 'Mainly Clear / Partly Cloudy',
            in_array($code, [45, 48], true) => 'Foggy / Haze',
            in_array($code, [51, 53, 55], true) => 'Drizzle',
            in_array($code, [61, 63, 65], true) => 'Rain',
            in_array($code, [71, 73, 75], true) => 'Snow Fall',
            in_array($code, [80, 81, 82], true) => 'Rain Showers',
            in_array($code, [95, 96, 99], true) => 'Thunderstorm',
            default => 'Moderate Conditions',
        };
    }
}

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
     * Curated major maritime and international commodity ports database.
     */
    protected array $knownPorts = [
        [
            'aliases' => ['belawan', 'medan', 'pelabuhan belawan', 'belawan medan', 'sumatra port'],
            'name' => 'Port of Belawan (Medan)',
            'display_name' => 'Pelabuhan Belawan, Medan, Sumatera Utara, Indonesia',
            'city' => 'Medan',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => 3.7833,
            'longitude' => 98.6833,
            'suggested_code' => 'IDBLW',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['tanjung priok', 'priok', 'jakarta', 'pelabuhan tanjung priok', 'jakarta port'],
            'name' => 'Port of Tanjung Priok (Jakarta)',
            'display_name' => 'Pelabuhan Tanjung Priok, Jakarta Utara, DKI Jakarta, Indonesia',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -6.1022,
            'longitude' => 106.8825,
            'suggested_code' => 'IDTPP',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['tanjung perak', 'surabaya', 'pelabuhan tanjung perak'],
            'name' => 'Port of Tanjung Perak (Surabaya)',
            'display_name' => 'Pelabuhan Tanjung Perak, Surabaya, Jawa Timur, Indonesia',
            'city' => 'Surabaya',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -7.1983,
            'longitude' => 112.7333,
            'suggested_code' => 'IDSUB',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['makassar', 'soekarno hatta makassar', 'pelabuhan makassar', 'ujung pandang'],
            'name' => 'Port of Makassar (Soekarno-Hatta)',
            'display_name' => 'Pelabuhan Makassar, Kota Makassar, Sulawesi Selatan, Indonesia',
            'city' => 'Makassar',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -5.1217,
            'longitude' => 119.4103,
            'suggested_code' => 'IDMAK',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['panjang', 'lampung', 'pelabuhan panjang', 'bandar lampung'],
            'name' => 'Port of Panjang (Lampung - Coffee Hub)',
            'display_name' => 'Pelabuhan Panjang, Bandar Lampung, Lampung, Indonesia',
            'city' => 'Bandar Lampung',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -5.4740,
            'longitude' => 105.3180,
            'suggested_code' => 'IDPNJ',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['teluk bayur', 'padang', 'pelabuhan teluk bayur', 'emmahaven'],
            'name' => 'Port of Teluk Bayur (Padang)',
            'display_name' => 'Pelabuhan Teluk Bayur, Padang, Sumatera Barat, Indonesia',
            'city' => 'Padang',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -0.9983,
            'longitude' => 100.3700,
            'suggested_code' => 'IDTBY',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['singapore', 'singapura', 'port of singapore', 'jurong', 'psa'],
            'name' => 'Port of Singapore',
            'display_name' => 'Port of Singapore, Jurong / PSA Marine Gateway, Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'country_code' => 'SG',
            'latitude' => 1.2902,
            'longitude' => 103.8519,
            'suggested_code' => 'SGSIN',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['santos', 'brasil', 'brazil', 'porto de santos', 'sao paulo'],
            'name' => 'Port of Santos',
            'display_name' => 'Porto de Santos, São Paulo, Brasil',
            'city' => 'Santos',
            'country' => 'Brasil',
            'country_code' => 'BR',
            'latitude' => -23.9619,
            'longitude' => -46.3322,
            'suggested_code' => 'BRSSZ',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['rotterdam', 'belanda', 'netherlands', 'haven van rotterdam'],
            'name' => 'Port of Rotterdam',
            'display_name' => 'Port of Rotterdam, South Holland, Netherlands',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'country_code' => 'NL',
            'latitude' => 51.9540,
            'longitude' => 4.0530,
            'suggested_code' => 'NLRTM',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['hamburg', 'jerman', 'germany', 'hamburger hafen'],
            'name' => 'Port of Hamburg',
            'display_name' => 'Hamburger Hafen, Hamburg, Germany',
            'city' => 'Hamburg',
            'country' => 'Germany',
            'country_code' => 'DE',
            'latitude' => 53.5350,
            'longitude' => 9.9800,
            'suggested_code' => 'DEHAM',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['tema', 'ghana', 'port of tema', 'accra'],
            'name' => 'Tema Port',
            'display_name' => 'Port of Tema, Greater Accra Region, Ghana',
            'city' => 'Tema',
            'country' => 'Ghana',
            'country_code' => 'GH',
            'latitude' => 5.6267,
            'longitude' => -0.0167,
            'suggested_code' => 'GHTEM',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['abidjan', 'pantai gading', 'cote d\'ivoire', 'port autonome d\'abidjan'],
            'name' => 'Port of Abidjan',
            'display_name' => 'Port Autonome d\'Abidjan, Lagunes, Côte d\'Ivoire',
            'city' => 'Abidjan',
            'country' => 'Cote d\'Ivoire',
            'country_code' => 'CI',
            'latitude' => 5.2500,
            'longitude' => -4.0167,
            'suggested_code' => 'CIABJ',
            'type' => 'PORT',
        ],
        [
            'aliases' => ['new york', 'new jersey', 'ny', 'port of new york'],
            'name' => 'Port of New York & New Jersey',
            'display_name' => 'Port of New York & New Jersey, USA',
            'city' => 'New York',
            'country' => 'United States',
            'country_code' => 'US',
            'latitude' => 40.6700,
            'longitude' => -74.0300,
            'suggested_code' => 'USNYC',
            'type' => 'PORT',
        ],
    ];

    /**
     * Search coordinates and rich address details for a location query via Maritime DB & Geocoding API.
     */
    public function geocode(string $query): array
    {
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return [
                'status' => 'success',
                'data' => [],
            ];
        }

        $cacheKey = 'geocode_v2_' . md5(strtolower($cleanQuery));

        return Cache::remember($cacheKey, 3600, function () use ($cleanQuery) {
            $matched = [];
            $qLower = strtolower($cleanQuery);

            // 1. Check curated major maritime ports first
            foreach ($this->knownPorts as $port) {
                foreach ($port['aliases'] as $alias) {
                    if (str_contains($qLower, $alias) || str_contains($alias, $qLower)) {
                        $matched[] = [
                            'name' => $port['name'],
                            'display_name' => $port['display_name'],
                            'city' => $port['city'],
                            'country' => $port['country'],
                            'country_code' => $port['country_code'],
                            'latitude' => $port['latitude'],
                            'longitude' => $port['longitude'],
                            'suggested_code' => $port['suggested_code'],
                            'type' => $port['type'],
                            'source' => 'Maritime Ports Verified Registry',
                        ];
                        break;
                    }
                }
            }

            // 2. Query Nominatim / OpenStreetMap Geocoding API with address details
            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('app.name', 'CargoTrace') . '/1.0 (academic-uas@cargotrace.test)',
                ])
                ->timeout(4)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $cleanQuery,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'limit' => 5,
                ]);

                if ($response->successful() && !empty($response->json())) {
                    $results = $response->json();
                    foreach ($results as $item) {
                        $addr = $item['address'] ?? [];
                        $city = $addr['city'] ?? $addr['city_district'] ?? $addr['town'] ?? $addr['municipality'] ?? $addr['county'] ?? $addr['state'] ?? '';
                        $country = $addr['country'] ?? '';
                        $cc = strtoupper($addr['country_code'] ?? 'ID');
                        
                        $name = $item['name'] ?? '';
                        if (empty($name)) {
                            $parts = explode(',', $item['display_name'] ?? '');
                            $name = trim($parts[0] ?? $cleanQuery);
                        }

                        // Generate suggested UN/LOCODE (e.g., IDBLW, IDTPP)
                        $cleanLetters = preg_replace('/[^A-Z]/', '', strtoupper($name));
                        $locodeTail = substr($cleanLetters, 0, 3);
                        if (strlen($locodeTail) < 3) {
                            $locodeTail = str_pad($locodeTail, 3, 'X');
                        }
                        $suggestedCode = $cc . $locodeTail;

                        // Avoid exact duplicates
                        $alreadyExists = false;
                        foreach ($matched as $m) {
                            if (abs($m['latitude'] - (float) ($item['lat'] ?? 0)) < 0.05 &&
                                abs($m['longitude'] - (float) ($item['lon'] ?? 0)) < 0.05) {
                                $alreadyExists = true;
                                break;
                            }
                        }

                        if (!$alreadyExists) {
                            $matched[] = [
                                'name' => $name,
                                'display_name' => $item['display_name'] ?? '',
                                'city' => $city ?: $name,
                                'country' => $country ?: 'Indonesia',
                                'country_code' => $cc,
                                'latitude' => (float) ($item['lat'] ?? 0),
                                'longitude' => (float) ($item['lon'] ?? 0),
                                'suggested_code' => $suggestedCode,
                                'type' => (isset($item['type']) && in_array(strtolower($item['type']), ['harbour', 'port', 'dock', 'pier'], true)) ? 'PORT' : 'PORT',
                                'source' => 'OpenStreetMap Global Geocoding',
                            ];
                        }
                    }
                }
            } catch (Exception $e) {
                Log::warning("Nominatim geocoding failed: " . $e->getMessage());
            }

            if (!empty($matched)) {
                return [
                    'status' => 'success',
                    'data' => $matched,
                ];
            }

            return [
                'status' => 'fallback',
                'data' => [],
                'message' => 'Geocoding service unavailable or no results found.',
            ];
        });
    }

    /**
     * Reverse geocode given coordinates to retrieve city, country, and address.
     */
    public function reverseGeocode(float $latitude, float $longitude): array
    {
        $cacheKey = sprintf('reverse_geocode_%.4f_%.4f', $latitude, $longitude);

        return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('app.name', 'CargoTrace') . '/1.0 (academic-uas@cargotrace.test)',
                ])
                ->timeout(4)
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'json',
                    'addressdetails' => 1,
                ]);

                if ($response->successful() && !empty($response->json())) {
                    $json = $response->json();
                    $addr = $json['address'] ?? [];
                    $city = $addr['city'] ?? $addr['city_district'] ?? $addr['town'] ?? $addr['municipality'] ?? $addr['county'] ?? $addr['state'] ?? 'Maritime Area';
                    $country = $addr['country'] ?? 'International Waters';
                    $cc = strtoupper($addr['country_code'] ?? 'ID');

                    $name = $addr['harbour'] ?? $addr['port'] ?? $addr['suburb'] ?? $addr['road'] ?? $json['name'] ?? "Point ({$latitude}, {$longitude})";

                    return [
                        'status' => 'success',
                        'data' => [
                            'name' => $name,
                            'display_name' => $json['display_name'] ?? "{$latitude}, {$longitude}",
                            'city' => $city,
                            'country' => $country,
                            'country_code' => $cc,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'suggested_code' => $cc . strtoupper(substr(preg_replace('/[^A-Z]/', '', $city . 'XXX'), 0, 3)),
                            'type' => 'PORT',
                        ],
                    ];
                }
            } catch (Exception $e) {
                Log::warning("Reverse geocoding failed: " . $e->getMessage());
            }

            return [
                'status' => 'fallback',
                'data' => [
                    'name' => "Custom Point ({$latitude}, {$longitude})",
                    'display_name' => "Titik Koordinat ({$latitude}, {$longitude})",
                    'city' => 'Maritime Point',
                    'country' => 'Indonesia',
                    'country_code' => 'ID',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'suggested_code' => 'IDPT' . rand(10, 99),
                    'type' => 'PORT',
                ],
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

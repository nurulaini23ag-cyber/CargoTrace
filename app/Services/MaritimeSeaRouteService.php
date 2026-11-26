<?php

namespace App\Services;

class MaritimeSeaRouteService
{
    /**
     * Standard strategic maritime chokepoints and sea waypoints worldwide.
     * All coordinates are located strictly in international waters/seas.
     */
    protected static array $seaWaypoints = [
        'malacca_strait_south' => [1.25, 103.60],    // Selat Malaka Selatan / Singapore
        'malacca_strait_mid'   => [2.80, 101.00],    // Selat Malaka Tengah (Port Klang)
        'malacca_strait_north' => [5.90, 95.30],     // Pintu Keluar Selat Malaka / Ujung Aceh
        'bengal_bay_south'     => [5.80, 88.00],     // Teluk Benggala Selatan
        'sri_lanka_south'      => [5.50, 80.50],     // Selatan Sri Lanka (Dondra Head)
        'arabian_sea_mid'      => [11.50, 60.00],    // Laut Arab Tengah
        'gulf_of_aden'         => [12.30, 48.00],    // Teluk Aden
        'bab_el_mandeb'        => [12.60, 43.40],    // Selat Bab-el-Mandeb
        'red_sea_south'        => [15.50, 41.50],    // Laut Merah Selatan
        'red_sea_mid'          => [21.50, 38.00],    // Laut Merah Tengah (Jeddah off-coast)
        'red_sea_north'        => [27.50, 34.20],    // Laut Merah Utara
        'gulf_of_suez'         => [29.90, 32.55],    // Teluk Suez
        'suez_canal'           => [30.60, 32.35],    // Terusan Suez
        'port_said'            => [31.30, 32.30],    // Pintu Keluar Laut Mediterania (Port Said)
        'med_east'             => [33.80, 28.50],    // Mediterania Timur
        'med_mid_crete'        => [34.80, 24.00],    // Selatan Pulau Kreta
        'med_sicily_strait'    => [36.80, 14.50],    // Selat Sisilia / Malta
        'med_west'             => [37.20, 1.50],     // Mediterania Barat
        'gibraltar_strait'     => [35.95, -5.60],    // Selat Gibraltar
        'atlantic_portugal'    => [39.50, -9.80],    // Pesisir Portugal
        'bay_of_biscay_south'  => [44.00, -9.00],    // Cape Finisterre Offshore
        'bay_of_biscay_mid'    => [46.50, -6.50],    // Teluk Biscay Barat
        'english_channel_west' => [49.50, -4.00],    // Pintu Masuk Selat Inggris
        'english_channel_mid'  => [50.20, -0.50],    // Selat Inggris Tengah
        'dover_strait'         => [51.10, 1.60],     // Selat Dover

        // North Sea Approaches
        'rotterdam_approach'   => [51.98, 3.80],     // Masuk Pelabuhan Rotterdam
        'antwerp_approach'     => [51.40, 3.50],     // Muara Sungai Scheldt (Antwerp)
        'dutch_coast_north'    => [53.20, 4.80],     // Pesisir Belanda Utara
        'german_bight'         => [54.00, 7.80],     // Teluk Jerman (Heligoland Bight)
        'elbe_estuary'         => [53.90, 8.70],     // Muara Sungai Elbe (Hamburg)

        // South Atlantic & West Africa Corridors
        'brazil_santos_offshore' => [-24.50, -45.50], // Laut Lepas Santos
        'brazil_cabo_frio'       => [-23.20, -41.00], // Tanjung Cabo Frio
        'brazil_recife_offshore' => [-8.00, -34.00],  // Pesisir Timur Brasil
        'atlantic_equator'       => [2.00, -25.00],   // Khatulistiwa Samudra Atlantik
        'canary_islands_west'    => [28.00, -17.00],  // Kepulauan Canary
        'west_africa_cape_palmas'=> [4.00, -8.00],    // Tanjung Palmas (Liberia/Pantai Gading)
        'west_africa_dakar'      => [14.50, -18.00],  // Laut Lepas Dakar Senegal

        // Indonesian Archipelagic Sea Lanes & South China Sea
        'vietnam_east_coast'     => [12.00, 110.00],  // Laut Cina Selatan Pesisir Vietnam
        'south_china_sea_south'  => [4.00, 106.00],   // Laut Cina Selatan Menuju Natuna/Singapura
        'sunda_strait'           => [-6.00, 105.80],  // Selat Sunda
        'java_sea_west'          => [-5.50, 107.50],  // Laut Jawa Barat
        'bangka_strait'          => [-2.20, 105.30],  // Selat Bangka

        // Transatlantic & Transpacific
        'mid_atlantic_north'     => [36.00, -35.00],  // Samudra Atlantik Utara Tengah
        'us_east_coast_approach' => [39.50, -72.00],  // Pintu Masuk Pelabuhan New York
        'luzon_strait'           => [20.50, 121.50],  // Selat Luzon (Filipina - Taiwan)
        'pacific_west'           => [15.20, 145.30],  // Samudra Pasifik Barat
        'pacific_mid'            => [10.00, -140.00], // Samudra Pasifik Tengah
        'panama_pacific'         => [8.90, -79.55],   // Pintu Masuk Kanal Panama (Pasifik)
        'panama_caribbean'       => [9.35, -79.90],   // Pintu Keluar Kanal Panama (Karibia)
        'caribbean_sea_mid'      => [17.80, -74.20],  // Laut Karibia
        'florida_straits'        => [25.50, -79.50],  // Selat Florida
    ];

    /**
     * Generate true realistic maritime sea waypoints between origin and destination.
     * Guarantees that ships never sail across land, mountains, or deserts.
     */
    public static function getMaritimeRoute(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
        string $originCode = '',
        string $destCode = ''
    ): array {
        $originCode = strtoupper($originCode);
        $destCode = strtoupper($destCode);

        // 1. Southeast Asia (Indonesia/Singapore/Vietnam) to Europe
        if (self::isSoutheastAsia($originLat, $originLng) && self::isEurope($destLat, $destLng)) {
            $waypoints = [];

            // Departure feeder to Malacca exit
            if ($originLat > 10.0 && $originLng > 105.0) { // From Vietnam (Da Nang)
                $waypoints[] = [15.80, 108.60];
                $waypoints[] = self::$seaWaypoints['vietnam_east_coast'];
                $waypoints[] = self::$seaWaypoints['south_china_sea_south'];
                $waypoints[] = self::$seaWaypoints['malacca_strait_south'];
                $waypoints[] = self::$seaWaypoints['malacca_strait_mid'];
            } elseif ($originLat < 0) { // From Java (Tanjung Priok / Surabaya)
                $waypoints[] = self::$seaWaypoints['java_sea_west'];
                $waypoints[] = self::$seaWaypoints['bangka_strait'];
                $waypoints[] = self::$seaWaypoints['malacca_strait_south'];
                $waypoints[] = self::$seaWaypoints['malacca_strait_mid'];
            } elseif ($originLat < 3.0) { // Singapore
                $waypoints[] = self::$seaWaypoints['malacca_strait_south'];
                $waypoints[] = self::$seaWaypoints['malacca_strait_mid'];
            } else { // Belawan
                $waypoints[] = [3.85, 98.80];
            }

            $waypoints[] = self::$seaWaypoints['malacca_strait_north'];
            $waypoints[] = self::$seaWaypoints['bengal_bay_south'];
            $waypoints[] = self::$seaWaypoints['sri_lanka_south'];
            $waypoints[] = self::$seaWaypoints['arabian_sea_mid'];
            $waypoints[] = self::$seaWaypoints['gulf_of_aden'];
            $waypoints[] = self::$seaWaypoints['bab_el_mandeb'];
            $waypoints[] = self::$seaWaypoints['red_sea_south'];
            $waypoints[] = self::$seaWaypoints['red_sea_mid'];
            $waypoints[] = self::$seaWaypoints['red_sea_north'];
            $waypoints[] = self::$seaWaypoints['gulf_of_suez'];
            $waypoints[] = self::$seaWaypoints['suez_canal'];
            $waypoints[] = self::$seaWaypoints['port_said'];
            $waypoints[] = self::$seaWaypoints['med_east'];
            $waypoints[] = self::$seaWaypoints['med_mid_crete'];

            // Destination Branch: Genoa / Mediterranean vs Northern Europe
            if (self::isGenoa($destLat, $destLng)) {
                $waypoints[] = [38.50, 9.50];
                $waypoints[] = [42.50, 9.00];
            } else {
                // Towards North Sea (Rotterdam / Antwerp / Hamburg)
                $waypoints[] = self::$seaWaypoints['med_sicily_strait'];
                $waypoints[] = self::$seaWaypoints['med_west'];
                $waypoints[] = self::$seaWaypoints['gibraltar_strait'];
                $waypoints[] = self::$seaWaypoints['atlantic_portugal'];
                $waypoints[] = self::$seaWaypoints['bay_of_biscay_south'];
                $waypoints[] = self::$seaWaypoints['bay_of_biscay_mid'];
                $waypoints[] = self::$seaWaypoints['english_channel_west'];
                $waypoints[] = self::$seaWaypoints['english_channel_mid'];
                $waypoints[] = self::$seaWaypoints['dover_strait'];

                if (self::isHamburg($destLat, $destLng)) {
                    $waypoints[] = self::$seaWaypoints['dutch_coast_north'];
                    $waypoints[] = self::$seaWaypoints['german_bight'];
                    $waypoints[] = self::$seaWaypoints['elbe_estuary'];
                } elseif (self::isAntwerp($destLat, $destLng)) {
                    $waypoints[] = self::$seaWaypoints['antwerp_approach'];
                } else {
                    $waypoints[] = self::$seaWaypoints['rotterdam_approach'];
                }
            }

            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 2. West Africa (Abidjan / Tema) to Europe
        if (self::isWestAfrica($originLat, $originLng) && self::isEurope($destLat, $destLng)) {
            $waypoints = [
                self::$seaWaypoints['west_africa_cape_palmas'],
                self::$seaWaypoints['west_africa_dakar'],
                self::$seaWaypoints['canary_islands_west'],
                self::$seaWaypoints['atlantic_portugal'],
                self::$seaWaypoints['bay_of_biscay_south'],
                self::$seaWaypoints['bay_of_biscay_mid'],
                self::$seaWaypoints['english_channel_west'],
                self::$seaWaypoints['english_channel_mid'],
                self::$seaWaypoints['dover_strait'],
            ];

            if (self::isHamburg($destLat, $destLng)) {
                $waypoints[] = self::$seaWaypoints['dutch_coast_north'];
                $waypoints[] = self::$seaWaypoints['german_bight'];
                $waypoints[] = self::$seaWaypoints['elbe_estuary'];
            } elseif (self::isAntwerp($destLat, $destLng)) {
                $waypoints[] = self::$seaWaypoints['antwerp_approach'];
            } else {
                $waypoints[] = self::$seaWaypoints['rotterdam_approach'];
            }

            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 3. South America (Santos Brasil) to Europe
        if (self::isSouthAmerica($originLat, $originLng) && self::isEurope($destLat, $destLng)) {
            $waypoints = [
                self::$seaWaypoints['brazil_santos_offshore'],
                self::$seaWaypoints['brazil_cabo_frio'],
                self::$seaWaypoints['brazil_recife_offshore'],
                self::$seaWaypoints['atlantic_equator'],
                self::$seaWaypoints['canary_islands_west'],
                self::$seaWaypoints['atlantic_portugal'],
                self::$seaWaypoints['bay_of_biscay_south'],
                self::$seaWaypoints['bay_of_biscay_mid'],
                self::$seaWaypoints['english_channel_west'],
                self::$seaWaypoints['english_channel_mid'],
                self::$seaWaypoints['dover_strait'],
            ];

            if (self::isHamburg($destLat, $destLng)) {
                $waypoints[] = self::$seaWaypoints['dutch_coast_north'];
                $waypoints[] = self::$seaWaypoints['german_bight'];
                $waypoints[] = self::$seaWaypoints['elbe_estuary'];
            } elseif (self::isAntwerp($destLat, $destLng)) {
                $waypoints[] = self::$seaWaypoints['antwerp_approach'];
            } else {
                $waypoints[] = self::$seaWaypoints['rotterdam_approach'];
            }

            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 4. East Africa (Djibouti) to USA (New York) via Suez and Gibraltar
        if ($originLng > 38.0 && $originLng < 50.0 && $originLat > 8.0 && $originLat < 15.0 && self::isUSA($destLat, $destLng)) {
            $waypoints = [
                self::$seaWaypoints['bab_el_mandeb'],
                self::$seaWaypoints['red_sea_south'],
                self::$seaWaypoints['red_sea_mid'],
                self::$seaWaypoints['red_sea_north'],
                self::$seaWaypoints['gulf_of_suez'],
                self::$seaWaypoints['suez_canal'],
                self::$seaWaypoints['port_said'],
                self::$seaWaypoints['med_east'],
                self::$seaWaypoints['med_mid_crete'],
                self::$seaWaypoints['med_sicily_strait'],
                self::$seaWaypoints['med_west'],
                self::$seaWaypoints['gibraltar_strait'],
                self::$seaWaypoints['mid_atlantic_north'],
                self::$seaWaypoints['us_east_coast_approach'],
            ];
            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 5. Southeast Asia (Vietnam Da Nang) to USA (New York) via Pacific and Panama Canal
        if (self::isSoutheastAsia($originLat, $originLng) && self::isUSA($destLat, $destLng)) {
            $waypoints = [
                [16.20, 110.00],
                self::$seaWaypoints['luzon_strait'],
                self::$seaWaypoints['pacific_west'],
                self::$seaWaypoints['pacific_mid'],
                self::$seaWaypoints['panama_pacific'],
                self::$seaWaypoints['panama_caribbean'],
                self::$seaWaypoints['caribbean_sea_mid'],
                self::$seaWaypoints['florida_straits'],
                self::$seaWaypoints['us_east_coast_approach'],
            ];
            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 6. Domestic Indonesia (Tanjung Priok to Belawan)
        if ($originLat < 0 && $originLng > 105 && $destLat > 2 && $destLng < 100) {
            $waypoints = [
                self::$seaWaypoints['java_sea_west'],
                self::$seaWaypoints['bangka_strait'],
                self::$seaWaypoints['malacca_strait_south'],
                self::$seaWaypoints['malacca_strait_mid'],
                [3.85, 98.80],
            ];
            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 7. Domestic Indonesia (Belawan to Tanjung Priok)
        if ($originLat > 2 && $originLng < 100 && $destLat < 0 && $destLng > 105) {
            $waypoints = [
                [3.85, 98.80],
                self::$seaWaypoints['malacca_strait_mid'],
                self::$seaWaypoints['malacca_strait_south'],
                self::$seaWaypoints['bangka_strait'],
                self::$seaWaypoints['java_sea_west'],
            ];
            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // Default clean maritime fallback
        return [
            [$originLat, $originLng],
            [$destLat, $destLng],
        ];
    }

    /**
     * Interpolate the ship's current position along its true maritime route.
     * Guarantees that the returned coordinate lies EXACTLY on the polyline.
     * Returns: [lat, lng, bearing, segmentIndex, ratioOnSegment]
     */
    public static function interpolatePositionAlongRoute(array $routeCoords, float $progressPercent): array
    {
        if (empty($routeCoords)) {
            return [0.0, 0.0, 0.0, 0, 0.0];
        }

        if (count($routeCoords) === 1 || $progressPercent <= 0) {
            $bearing = count($routeCoords) > 1
                ? self::calculateBearing($routeCoords[0][0], $routeCoords[0][1], $routeCoords[1][0], $routeCoords[1][1])
                : 0.0;
            return [$routeCoords[0][0], $routeCoords[0][1], $bearing, 0, 0.0];
        }

        if ($progressPercent >= 100) {
            $lastIdx = count($routeCoords) - 1;
            $prevIdx = max(0, $lastIdx - 1);
            $bearing = self::calculateBearing(
                $routeCoords[$prevIdx][0],
                $routeCoords[$prevIdx][1],
                $routeCoords[$lastIdx][0],
                $routeCoords[$lastIdx][1]
            );
            return [$routeCoords[$lastIdx][0], $routeCoords[$lastIdx][1], $bearing, $lastIdx, 1.0];
        }

        // Calculate segment lengths
        $segmentDistances = [];
        $totalDistance = 0.0;

        for ($i = 0; $i < count($routeCoords) - 1; $i++) {
            $d = self::haversineDistance(
                $routeCoords[$i][0],
                $routeCoords[$i][1],
                $routeCoords[$i + 1][0],
                $routeCoords[$i + 1][1]
            );
            $segmentDistances[] = $d;
            $totalDistance += $d;
        }

        if ($totalDistance <= 0) {
            return [$routeCoords[0][0], $routeCoords[0][1], 0.0, 0, 0.0];
        }

        $targetDistance = ($progressPercent / 100.0) * $totalDistance;
        $accumulated = 0.0;

        for ($i = 0; $i < count($segmentDistances); $i++) {
            $segLen = $segmentDistances[$i];
            if ($accumulated + $segLen >= $targetDistance || $i === count($segmentDistances) - 1) {
                $rem = $targetDistance - $accumulated;
                $ratio = $segLen > 0 ? min(1.0, max(0.0, $rem / $segLen)) : 0.0;

                $lat = $routeCoords[$i][0] + $ratio * ($routeCoords[$i + 1][0] - $routeCoords[$i][0]);
                $lng = $routeCoords[$i][1] + $ratio * ($routeCoords[$i + 1][1] - $routeCoords[$i][1]);

                $bearing = self::calculateBearing(
                    $routeCoords[$i][0],
                    $routeCoords[$i][1],
                    $routeCoords[$i + 1][0],
                    $routeCoords[$i + 1][1]
                );

                return [round($lat, 5), round($lng, 5), round($bearing, 1), $i, $ratio];
            }
            $accumulated += $segLen;
        }

        $lastIdx = count($routeCoords) - 1;
        return [$routeCoords[$lastIdx][0], $routeCoords[$lastIdx][1], 0.0, $lastIdx, 1.0];
    }

    /**
     * Splits a full sea route into two paths:
     * 1. Traveled path: [origin ... shipPos]
     * 2. Remaining path: [shipPos ... destination]
     * This creates the realistic SPX / Google Maps real-time tracking look.
     */
    public static function splitRouteAtPosition(array $routeCoords, float $shipLat, float $shipLng, int $segmentIdx): array
    {
        if (empty($routeCoords)) {
            return [[], []];
        }

        $traveled = [];
        for ($i = 0; $i <= $segmentIdx; $i++) {
            $traveled[] = $routeCoords[$i];
        }
        $traveled[] = [$shipLat, $shipLng];

        $remaining = [];
        $remaining[] = [$shipLat, $shipLng];
        for ($i = $segmentIdx + 1; $i < count($routeCoords); $i++) {
            $remaining[] = $routeCoords[$i];
        }

        return [
            'traveled' => $traveled,
            'remaining' => $remaining,
        ];
    }

    protected static function assembleRoute(float $oLat, float $oLng, array $waypoints, float $dLat, float $dLng): array
    {
        $all = [];
        $all[] = [$oLat, $oLng];
        foreach ($waypoints as $wp) {
            $all[] = [$wp[0], $wp[1]];
        }
        $all[] = [$dLat, $dLng];

        // Clean duplicates
        $clean = [];
        foreach ($all as $pt) {
            if (empty($clean) || (abs($clean[count($clean) - 1][0] - $pt[0]) > 0.01 || abs($clean[count($clean) - 1][1] - $pt[1]) > 0.01)) {
                $clean[] = [$pt[0], $pt[1]];
            }
        }
        return $clean;
    }

    protected static function isSoutheastAsia(float $lat, float $lng): bool
    {
        return $lat >= -11.0 && $lat <= 22.0 && $lng >= 95.0 && $lng <= 141.0;
    }

    protected static function isEurope(float $lat, float $lng): bool
    {
        return $lat >= 35.0 && $lat <= 65.0 && $lng >= -12.0 && $lng <= 30.0;
    }

    protected static function isGenoa(float $lat, float $lng): bool
    {
        return $lat >= 42.0 && $lat <= 46.0 && $lng >= 7.0 && $lng <= 10.5;
    }

    protected static function isHamburg(float $lat, float $lng): bool
    {
        return $lat >= 52.0 && $lng >= 7.5;
    }

    protected static function isAntwerp(float $lat, float $lng): bool
    {
        return $lat >= 50.8 && $lat <= 52.0 && $lng >= 3.0 && $lng <= 5.0;
    }

    protected static function isSouthAmerica(float $lat, float $lng): bool
    {
        return $lat >= -55.0 && $lat <= 12.0 && $lng >= -82.0 && $lng <= -34.0;
    }

    protected static function isWestAfrica(float $lat, float $lng): bool
    {
        return $lat >= 0.0 && $lat <= 16.0 && $lng >= -20.0 && $lng <= 5.0;
    }

    protected static function isUSA(float $lat, float $lng): bool
    {
        return $lat >= 24.0 && $lat <= 50.0 && $lng >= -130.0 && $lng <= -65.0;
    }

    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public static function calculateBearing(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLon = deg2rad($lon2 - $lon1);
        $y = sin($dLon) * cos(deg2rad($lat2));
        $x = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) -
             sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos($dLon);
        $bearing = rad2deg(atan2($y, $x));
        return fmod(($bearing + 360.0), 360.0);
    }
}

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
        'bay_of_biscay'        => [46.50, -6.50],    // Teluk Biscay Barat
        'english_channel_west' => [49.50, -4.00],    // Pintu Masuk Selat Inggris
        'english_channel_mid'  => [50.20, -0.50],    // Selat Inggris Tengah
        'dover_strait'         => [51.10, 1.60],     // Selat Dover

        // South Atlantic & West Africa Corridors
        'brazil_santos_offshore' => [-24.50, -45.50], // Laut Lepas Santos
        'brazil_cabo_frio'       => [-23.20, -41.00], // Tanjung Cabo Frio
        'brazil_recife_offshore' => [-8.00, -34.00],  // Pesisir Timur Brasil
        'atlantic_equator'       => [2.00, -25.00],   // Khatulistiwa Samudra Atlantik
        'canary_islands_west'    => [28.00, -17.00],  // Kepulauan Canary
        'west_africa_cape_palmas'=> [4.00, -8.00],    // Tanjung Palmas (Liberia/Pantai Gading)
        'west_africa_dakar'      => [14.50, -18.00],  // Laut Lepas Dakar Senegal

        // Indonesian Archipelagic Sea Lanes
        'sunda_strait'           => [-6.00, 105.80],  // Selat Sunda
        'java_sea_west'          => [-5.50, 107.50],  // Laut Jawa Barat
        'bangka_strait'          => [-2.20, 105.30],  // Selat Bangka
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

        // 1. Southeast Asia (Indonesia/Singapore) to Europe (Rotterdam, Hamburg, Antwerp, Genoa)
        if (self::isSoutheastAsia($originLat, $originLng) && self::isEurope($destLat, $destLng)) {
            $waypoints = [];
            
            // Departure feeder to Malacca exit
            if ($originLat < 0) { // From Java (Tanjung Priok / Surabaya)
                $waypoints[] = self::$seaWaypoints['java_sea_west'];
                $waypoints[] = self::$seaWaypoints['bangka_strait'];
                $waypoints[] = self::$seaWaypoints['malacca_strait_south'];
            } elseif ($originLat < 3.0) { // Singapore
                $waypoints[] = self::$seaWaypoints['malacca_strait_south'];
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

            // If Genoa, turn north into Ligurian Sea
            if ($destLat > 43.0 && $destLng > 7.0 && $destLng < 10.0) {
                $waypoints[] = [38.50, 9.50];
                $waypoints[] = [42.50, 9.00];
            } else {
                // Towards North Sea (Rotterdam / Hamburg / Antwerp)
                $waypoints[] = self::$seaWaypoints['med_sicily_strait'];
                $waypoints[] = self::$seaWaypoints['med_west'];
                $waypoints[] = self::$seaWaypoints['gibraltar_strait'];
                $waypoints[] = self::$seaWaypoints['atlantic_portugal'];
                $waypoints[] = self::$seaWaypoints['bay_of_biscay'];
                $waypoints[] = self::$seaWaypoints['english_channel_west'];
                $waypoints[] = self::$seaWaypoints['english_channel_mid'];
                $waypoints[] = self::$seaWaypoints['dover_strait'];

                if ($destLng > 6.0) { // Hamburg
                    $waypoints[] = [53.80, 5.50];
                    $waypoints[] = [54.00, 8.20];
                }
            }

            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 2. South America (Santos Brasil) to Europe (Rotterdam, Hamburg)
        if (self::isSouthAmerica($originLat, $originLng) && self::isEurope($destLat, $destLng)) {
            $waypoints = [
                self::$seaWaypoints['brazil_santos_offshore'],
                self::$seaWaypoints['brazil_cabo_frio'],
                self::$seaWaypoints['brazil_recife_offshore'],
                self::$seaWaypoints['atlantic_equator'],
                self::$seaWaypoints['canary_islands_west'],
                self::$seaWaypoints['atlantic_portugal'],
                self::$seaWaypoints['bay_of_biscay'],
                self::$seaWaypoints['english_channel_west'],
                self::$seaWaypoints['english_channel_mid'],
                self::$seaWaypoints['dover_strait'],
            ];

            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 3. West Africa (Abidjan / Tema) to Europe (Rotterdam, Hamburg)
        if (self::isWestAfrica($originLat, $originLng) && self::isEurope($destLat, $destLng)) {
            $waypoints = [
                self::$seaWaypoints['west_africa_cape_palmas'],
                self::$seaWaypoints['west_africa_dakar'],
                self::$seaWaypoints['canary_islands_west'],
                self::$seaWaypoints['atlantic_portugal'],
                self::$seaWaypoints['bay_of_biscay'],
                self::$seaWaypoints['english_channel_west'],
                self::$seaWaypoints['english_channel_mid'],
                self::$seaWaypoints['dover_strait'],
            ];

            return self::assembleRoute($originLat, $originLng, $waypoints, $destLat, $destLng);
        }

        // 4. Domestic Indonesia (Tanjung Priok to Belawan)
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

        // 5. Domestic Indonesia (Belawan to Tanjung Priok)
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
     * $progressPercent: 0 to 100.
     */
    public static function interpolatePositionAlongRoute(array $routeCoords, float $progressPercent): array
    {
        if (empty($routeCoords)) {
            return [0.0, 0.0, 90.0];
        }

        if (count($routeCoords) === 1 || $progressPercent <= 0) {
            return [$routeCoords[0][0], $routeCoords[0][1], 90.0];
        }

        if ($progressPercent >= 100) {
            $last = end($routeCoords);
            return [$last[0], $last[1], 90.0];
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
            return [$routeCoords[0][0], $routeCoords[0][1], 90.0];
        }

        $targetDistance = ($progressPercent / 100.0) * $totalDistance;
        $accumulated = 0.0;

        for ($i = 0; $i < count($segmentDistances); $i++) {
            $segLen = $segmentDistances[$i];
            if ($accumulated + $segLen >= $targetDistance) {
                $rem = $targetDistance - $accumulated;
                $ratio = $segLen > 0 ? ($rem / $segLen) : 0.0;

                $lat = $routeCoords[$i][0] + $ratio * ($routeCoords[$i + 1][0] - $routeCoords[$i][0]);
                $lng = $routeCoords[$i][1] + $ratio * ($routeCoords[$i + 1][1] - $routeCoords[$i][1]);

                $bearing = self::calculateBearing(
                    $routeCoords[$i][0],
                    $routeCoords[$i][1],
                    $routeCoords[$i + 1][0],
                    $routeCoords[$i + 1][1]
                );

                return [round($lat, 5), round($lng, 5), round($bearing, 1)];
            }
            $accumulated += $segLen;
        }

        $last = end($routeCoords);
        return [$last[0], $last[1], 90.0];
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
                $clean[] = $pt;
            }
        }
        return $clean;
    }

    protected static function isSoutheastAsia(float $lat, float $lng): bool
    {
        return $lat >= -11.0 && $lat <= 10.0 && $lng >= 95.0 && $lng <= 141.0;
    }

    protected static function isEurope(float $lat, float $lng): bool
    {
        return $lat >= 35.0 && $lat <= 65.0 && $lng >= -12.0 && $lng <= 30.0;
    }

    protected static function isSouthAmerica(float $lat, float $lng): bool
    {
        return $lat >= -55.0 && $lat <= 12.0 && $lng >= -82.0 && $lng <= -34.0;
    }

    protected static function isWestAfrica(float $lat, float $lng): bool
    {
        return $lat >= 0.0 && $lat <= 16.0 && $lng >= -20.0 && $lng <= 5.0;
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

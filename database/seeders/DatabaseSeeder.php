<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\SystemSetting;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Settings & Delay Thresholds
        SystemSetting::set('delay_threshold_low', 6.0, 'delay_rules', 'Delay up to 6 hours is classified as LOW');
        SystemSetting::set('delay_threshold_medium', 24.0, 'delay_rules', 'Delay between 6 and 24 hours is classified as MEDIUM; >24h is HIGH');
        SystemSetting::set('group_scenario', 'A', 'scenario', 'Active group scenario preset (Group A: SE Asia to Europe, Customs Hold)');
        SystemSetting::set('platform_domain', 'Cocoa & Coffee Sea Freight', 'general', 'Specialized maritime domain');

        // 2. Users & Roles (Slide 05: Admin, Operator, Customer)
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'company_name' => 'CargoTrace Global Logistics HQ',
                'phone' => '+62 21 555 0100',
                'email_verified_at' => now(),
            ]
        );

        $operator1 = User::firstOrCreate(
            ['email' => 'operator@cargotrace.test'],
            [
                'name' => 'Bambang Wijaya (Chief Maritime Dispatcher)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OPERATOR,
                'company_name' => 'Tanjung Priok & Belawan Marine Dispatch Center',
                'phone' => '+62 21 555 0101',
                'email_verified_at' => now(),
            ]
        );

        $operator2 = User::firstOrCreate(
            ['email' => 'operator2@cargotrace.test'],
            [
                'name' => 'Kofi Mensah (West Africa Logistics Specialist)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OPERATOR,
                'company_name' => 'Tema & Abidjan Marine Operations Hub',
                'phone' => '+233 30 255 0202',
                'email_verified_at' => now(),
            ]
        );

        $customer1 = User::firstOrCreate(
            ['email' => 'customer@cargotrace.test'],
            [
                'name' => 'PT Nusantara Agri Komoditi (Hendra Gunawan)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'company_name' => 'PT Nusantara Agri Global Ekspor',
                'phone' => '+62 811 2345 6789',
                'email_verified_at' => now(),
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'customer2@cargotrace.test'],
            [
                'name' => 'JDE Peet\'s Roasters (Jan van Dijk)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'company_name' => 'JDE Peet\'s European Supply Chain B.V.',
                'phone' => '+31 20 555 8900',
                'email_verified_at' => now(),
            ]
        );

        $customer3 = User::firstOrCreate(
            ['email' => 'barry@cargotrace.test'],
            [
                'name' => 'Barry Callebaut Chocolatier (Hélène Dupont)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'company_name' => 'Barry Callebaut Sourcing AG (Hamburg Division)',
                'phone' => '+49 40 555 3400',
                'email_verified_at' => now(),
            ]
        );

        $customer4 = User::firstOrCreate(
            ['email' => 'bluebottle@cargotrace.test'],
            [
                'name' => 'Blue Bottle Specialty Coffee (Marcus Vance)',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'company_name' => 'Blue Bottle Specialty Importers LLC',
                'phone' => '+1 415 555 9821',
                'email_verified_at' => now(),
            ]
        );

        // 3. Carriers (Slide 05 & 11) - Leading Ocean Container Lines
        $maersk = Carrier::firstOrCreate(
            ['code' => 'MAEU'],
            [
                'name' => 'A.P. Moller - Maersk Line',
                'contact_email' => 'freight.support@maersk.com',
                'contact_phone' => '+45 33 63 33 63',
                'website' => 'https://www.maersk.com',
                'mode' => 'SEA',
                'is_active' => true,
            ]
        );

        $msc = Carrier::firstOrCreate(
            ['code' => 'MSCU'],
            [
                'name' => 'Mediterranean Shipping Company (MSC)',
                'contact_email' => 'cargo.info@msc.com',
                'contact_phone' => '+41 22 703 8888',
                'website' => 'https://www.msc.com',
                'mode' => 'SEA',
                'is_active' => true,
            ]
        );

        $cmacgm = Carrier::firstOrCreate(
            ['code' => 'CMDU'],
            [
                'name' => 'CMA CGM Ocean Freight Group',
                'contact_email' => 'booking@cma-cgm.com',
                'contact_phone' => '+33 4 88 91 90 00',
                'website' => 'https://www.cmacgm-group.com',
                'mode' => 'SEA',
                'is_active' => true,
            ]
        );

        $hapag = Carrier::firstOrCreate(
            ['code' => 'HLCU'],
            [
                'name' => 'Hapag-Lloyd Container Line',
                'contact_email' => 'commodities@hlag.com',
                'contact_phone' => '+49 40 3001 0',
                'website' => 'https://www.hapag-lloyd.com',
                'mode' => 'SEA',
                'is_active' => true,
            ]
        );

        $one = Carrier::firstOrCreate(
            ['code' => 'ONEY'],
            [
                'name' => 'Ocean Network Express (ONE)',
                'contact_email' => 'maritime@one-line.com',
                'contact_phone' => '+65 6899 9000',
                'website' => 'https://www.one-line.com',
                'mode' => 'SEA',
                'is_active' => true,
            ]
        );

        // 4. Locations & Verified Major Seaports (Cocoa & Coffee Global Gateways)
        $blw = Location::firstOrCreate(
            ['code' => 'IDBLW'],
            [
                'name' => 'Belawan Port, Medan',
                'city' => 'Medan',
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'latitude' => 3.7833,
                'longitude' => 98.6833,
                'type' => 'PORT',
            ]
        );

        $tpp = Location::firstOrCreate(
            ['code' => 'IDTPP'],
            [
                'name' => 'Port of Tanjung Priok, Jakarta',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'latitude' => -6.1022,
                'longitude' => 106.8825,
                'type' => 'PORT',
            ]
        );

        $sub = Location::firstOrCreate(
            ['code' => 'IDSUB'],
            [
                'name' => 'Port of Tanjung Perak, Surabaya',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'latitude' => -7.2000,
                'longitude' => 112.7333,
                'type' => 'PORT',
            ]
        );

        $tem = Location::firstOrCreate(
            ['code' => 'GHTEM'],
            [
                'name' => 'Tema Port, Ghana',
                'city' => 'Accra / Tema',
                'country' => 'Ghana',
                'country_code' => 'GH',
                'latitude' => 5.6267,
                'longitude' => -0.0167,
                'type' => 'PORT',
            ]
        );

        $abj = Location::firstOrCreate(
            ['code' => 'CIABJ'],
            [
                'name' => 'Port of Abidjan, Côte d\'Ivoire',
                'city' => 'Abidjan',
                'country' => 'Côte d\'Ivoire',
                'country_code' => 'CI',
                'latitude' => 5.2500,
                'longitude' => -4.0167,
                'type' => 'PORT',
            ]
        );

        $dad = Location::firstOrCreate(
            ['code' => 'VNDAD'],
            [
                'name' => 'Port of Da Nang, Vietnam',
                'city' => 'Da Nang',
                'country' => 'Vietnam',
                'country_code' => 'VN',
                'latitude' => 16.0833,
                'longitude' => 108.2333,
                'type' => 'PORT',
            ]
        );

        $dji = Location::firstOrCreate(
            ['code' => 'ETDJI'],
            [
                'name' => 'Port of Djibouti (Ethiopia Gateway)',
                'city' => 'Djibouti / Addis Ababa',
                'country' => 'Ethiopia',
                'country_code' => 'ET',
                'latitude' => 11.5880,
                'longitude' => 43.1450,
                'type' => 'PORT',
            ]
        );

        $rtm = Location::firstOrCreate(
            ['code' => 'NLRTM'],
            [
                'name' => 'Port of Rotterdam',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'country_code' => 'NL',
                'latitude' => 51.9540,
                'longitude' => 4.0530,
                'type' => 'PORT',
            ]
        );

        $ham = Location::firstOrCreate(
            ['code' => 'DEHAM'],
            [
                'name' => 'Port of Hamburg',
                'city' => 'Hamburg',
                'country' => 'Germany',
                'country_code' => 'DE',
                'latitude' => 53.5350,
                'longitude' => 9.9800,
                'type' => 'PORT',
            ]
        );

        $anr = Location::firstOrCreate(
            ['code' => 'BEANR'],
            [
                'name' => 'Port of Antwerp',
                'city' => 'Antwerp',
                'country' => 'Belgium',
                'country_code' => 'BE',
                'latitude' => 51.2667,
                'longitude' => 4.3833,
                'type' => 'PORT',
            ]
        );

        $goa = Location::firstOrCreate(
            ['code' => 'ITGOA'],
            [
                'name' => 'Port of Genoa',
                'city' => 'Genoa',
                'country' => 'Italy',
                'country_code' => 'IT',
                'latitude' => 44.4056,
                'longitude' => 8.9463,
                'type' => 'PORT',
            ]
        );

        $nyc = Location::firstOrCreate(
            ['code' => 'USNYC'],
            [
                'name' => 'Port of New York & New Jersey',
                'city' => 'New York',
                'country' => 'United States',
                'country_code' => 'US',
                'latitude' => 40.6700,
                'longitude' => -74.0300,
                'type' => 'PORT',
            ]
        );

        $sin = Location::firstOrCreate(
            ['code' => 'SGSIN'],
            [
                'name' => 'Port of Singapore Transshipment Hub',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'country_code' => 'SG',
                'latitude' => 1.2644,
                'longitude' => 103.8400,
                'type' => 'PORT',
            ]
        );

        $now = now();

        // 5. SHIPMENTS (15 Realistic Sea Freight Shipments for Cocoa & Coffee)
        $shipmentsData = [
            // Shipment 1: Indonesia -> Netherlands (Green Arabica Coffee, Medium Delay 14h, Customs Hold)
            [
                'number' => 'CTC-2026-0001',
                'customer' => $customer1,
                'carrier' => $maersk,
                'origin' => $blw,
                'destination' => $rtm,
                'cargo_type' => 'Green Arabica Coffee Beans',
                'weight' => 36000.00,
                'volume' => 72.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 8,
                'arr_add_days' => 7,
                'delay_hours' => 14.00,
                'delay_severity' => Shipment::SEVERITY_MEDIUM,
                'delay_reason' => 'Delay of 14 hours due to Customs Hold (Phytosanitary certificate inspection) at transshipment checkpoint.',
                'instructions' => 'Maintain dry food-grade ventilation. Moisture content must not exceed 12.5%.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Sumatra Mandheling Grade 1 Green Coffee',
                        'variety' => 'Arabica Typica & Catimor',
                        'grade' => 'Specialty Grade 1 (Triple Picked)',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 600,
                        'net_weight' => 36000.00,
                        'moisture' => 11.8,
                        'temp' => null,
                    ]
                ],
                'exception' => [
                    'type' => ShipmentException::TYPE_CUSTOMS_HOLD,
                    'severity' => ShipmentException::SEVERITY_MEDIUM,
                    'status' => ShipmentException::STATUS_INVESTIGATING,
                    'impact' => 14,
                    'desc' => 'EU Plant Health phytosanitary cross-check inspection held at Suez transit checkpoint.',
                    'detected_sub_hours' => 24,
                ],
                'waypoints' => [
                    ['time_sub_days' => 8, 'loc' => $blw, 'desc' => 'Container stuffed and sealed at Belawan Port Terminal.'],
                    ['time_sub_days' => 7, 'loc' => $blw, 'desc' => 'Vessel departed Belawan Port outward bound via Malacca Strait.'],
                    ['time_sub_days' => 5, 'loc' => $sin, 'desc' => 'Transshipment container inspection completed at Port of Singapore.'],
                    ['time_sub_days' => 2, 'loc' => null, 'name' => 'Indian Ocean Waypoint (10.2N, 75.8E)', 'lat' => 10.2000, 'lon' => 75.8000, 'desc' => 'Cruising speed 18.5 knots along primary maritime shipping lane.'],
                    ['time_sub_days' => 1, 'loc' => null, 'name' => 'Suez Canal Southern Anchorage', 'lat' => 27.8500, 'lon' => 34.2800, 'desc' => 'Convoy transit initiated; customs documentation submitted.'],
                ]
            ],

            // Shipment 2: Ghana -> Germany (Fermented Cocoa Beans, High Delay 28h, Port Congestion)
            [
                'number' => 'CTC-2026-0002',
                'customer' => $customer3,
                'carrier' => $msc,
                'origin' => $tem,
                'destination' => $ham,
                'cargo_type' => 'Fermented Cocoa Beans',
                'weight' => 50000.00,
                'volume' => 95.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 10,
                'arr_add_days' => 9,
                'delay_hours' => 28.00,
                'delay_severity' => Shipment::SEVERITY_HIGH,
                'delay_reason' => 'Delay of 28 hours due to Port Congestion at Hamburg Waltershof Terminal berth backlog.',
                'instructions' => 'Preserve dry ventilation. Keep away from fuel tanks and chemicals to prevent aroma absorption.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COCOA,
                        'name' => 'Ghana Standard Fermented Cocoa Beans',
                        'variety' => 'Amelonado & Forastero',
                        'grade' => 'Grade 1 Well-Fermented (Main Crop)',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 770,
                        'net_weight' => 50000.00,
                        'moisture' => 7.2,
                        'temp' => null,
                    ]
                ],
                'exception' => [
                    'type' => ShipmentException::TYPE_PORT_CONGESTION,
                    'severity' => ShipmentException::SEVERITY_HIGH,
                    'status' => ShipmentException::STATUS_OPEN,
                    'impact' => 28,
                    'desc' => 'Heavy container traffic backlog at Port of Hamburg container terminal Waltershof.',
                    'detected_sub_hours' => 18,
                ],
                'waypoints' => [
                    ['time_sub_days' => 10, 'loc' => $tem, 'desc' => 'Loading completed at Tema Port Container Berth 2.'],
                    ['time_sub_days' => 9, 'loc' => $tem, 'desc' => 'Vessel MSC Ilona sailed from Tema Port into Gulf of Guinea.'],
                    ['time_sub_days' => 6, 'loc' => null, 'name' => 'Cape Verde Maritime Transit Corridor', 'lat' => 15.1200, 'lon' => -23.6000, 'desc' => 'Vessel navigating northbound Atlantic route at 19 knots.'],
                    ['time_sub_days' => 2, 'loc' => null, 'name' => 'English Channel Southwest Approaches', 'lat' => 49.5000, 'lon' => -5.2000, 'desc' => 'Vessel entering English Channel maritime traffic scheme.'],
                ]
            ],

            // Shipment 3: Indonesia -> Netherlands (Cocoa Butter Reefer, On-Time, No Delay)
            [
                'number' => 'CTC-2026-0003',
                'customer' => $customer1,
                'carrier' => $cmacgm,
                'origin' => $sub,
                'destination' => $rtm,
                'cargo_type' => 'Cocoa Butter',
                'weight' => 22000.00,
                'volume' => 45.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 12,
                'arr_add_days' => 6,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'CRITICAL: Reefer container must be maintained strictly at 18.0°C. Continuous data logger active.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COCOA,
                        'name' => 'Pure Prime Pressed Cocoa Butter',
                        'variety' => 'Natural Deodorized Butter',
                        'grade' => 'Food Grade Export Standard (Kosher & Halal)',
                        'package_type' => 'BOX',
                        'package_count' => 880,
                        'net_weight' => 22000.00,
                        'moisture' => 0.15,
                        'temp' => 18.0,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 12, 'loc' => $sub, 'desc' => 'Reefer pre-trip inspection passed. Container stuffed at Tanjung Perak Surabaya.'],
                    ['time_sub_days' => 11, 'loc' => $sub, 'desc' => 'Vessel departed Surabaya en route to Singapore hub.'],
                    ['time_sub_days' => 8, 'loc' => $sin, 'desc' => 'Reefer plugged in at Singapore transshipment yard. Temperature recorded: 18.1°C.'],
                    ['time_sub_days' => 4, 'loc' => null, 'name' => 'Bab el Mandeb Strait', 'lat' => 12.5800, 'lon' => 43.3300, 'desc' => 'Vessel entering Red Sea transit corridor on schedule.'],
                    ['time_sub_days' => 1, 'loc' => null, 'name' => 'Mediterranean Sea Corridor (Crete)', 'lat' => 34.8000, 'lon' => 24.5000, 'desc' => 'Sailing west toward Strait of Gibraltar. ETA Rotterdam confirmed on time.'],
                ]
            ],

            // Shipment 4: Côte d'Ivoire -> Netherlands (Dried Cocoa Beans, Low Delay 5h, Weather Delay)
            [
                'number' => 'CTC-2026-0004',
                'customer' => $customer2,
                'carrier' => $hapag,
                'origin' => $abj,
                'destination' => $rtm,
                'cargo_type' => 'Dried Cocoa Beans',
                'weight' => 42000.00,
                'volume' => 80.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 9,
                'arr_add_days' => 8,
                'delay_hours' => 5.00,
                'delay_severity' => Shipment::SEVERITY_LOW,
                'delay_reason' => 'Delay of 5 hours due to Weather Delay (heavy swell and reduced cruising speed across Bay of Biscay).',
                'instructions' => 'Natural ventilation. Check desiccant silica gel packets in container corners.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COCOA,
                        'name' => 'Ivorian Dried Cocoa Beans (Single Origin)',
                        'variety' => 'Forastero Hybrid',
                        'grade' => 'Fairtrade Certified Good Fermented Grade',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 700,
                        'net_weight' => 42000.00,
                        'moisture' => 7.4,
                        'temp' => null,
                    ]
                ],
                'exception' => [
                    'type' => ShipmentException::TYPE_WEATHER_DELAY,
                    'severity' => ShipmentException::SEVERITY_LOW,
                    'status' => ShipmentException::STATUS_RESOLVED,
                    'impact' => 5,
                    'desc' => 'Heavy ocean swell in North Atlantic forced vessel speed reduction from 20 to 14 knots.',
                    'detected_sub_hours' => 36,
                    'resolved_sub_hours' => 12,
                    'notes' => 'Vessel cleared storm system and resumed standard cruising speed.',
                ],
                'waypoints' => [
                    ['time_sub_days' => 9, 'loc' => $abj, 'desc' => 'Loaded aboard Hapag-Lloyd vessel at Port of Abidjan Terminal.'],
                    ['time_sub_days' => 8, 'loc' => $abj, 'desc' => 'Vessel departed Abidjan outward bound for North Europe.'],
                    ['time_sub_days' => 4, 'loc' => null, 'name' => 'Canary Islands Oceanic Corridor', 'lat' => 28.1000, 'lon' => -15.4000, 'desc' => 'Sailing past Las Palmas waypoint at 19.8 knots.'],
                    ['time_sub_days' => 1, 'loc' => null, 'name' => 'Bay of Biscay Western Approach', 'lat' => 45.2000, 'lon' => -8.5000, 'desc' => 'Rough seas encountered; vessel route slightly adjusted for cargo safety.'],
                ]
            ],

            // Shipment 5: Indonesia -> Italy (Roasted Arabica Coffee, DELIVERED, On-Time)
            [
                'number' => 'CTC-2026-0005',
                'customer' => $customer1,
                'carrier' => $maersk,
                'origin' => $tpp,
                'destination' => $goa,
                'cargo_type' => 'Roasted Arabica Coffee',
                'weight' => 18500.00,
                'volume' => 40.00,
                'status' => Shipment::STATUS_DELIVERED,
                'dep_sub_days' => 26,
                'arr_add_days' => -2, // delivered 2 days ago
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Valve bags in moisture-barrier master cartons. Handled with white-glove logistics.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Toraja Sapan Artisan Roasted Arabica Coffee',
                        'variety' => 'Arabica S-795 & Typica',
                        'grade' => 'Specialty Medium Roast (1kg Valve Foil Bags)',
                        'package_type' => 'BOX',
                        'package_count' => 1850,
                        'net_weight' => 18500.00,
                        'moisture' => 2.1,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 26, 'loc' => $tpp, 'desc' => 'Gate-in and customs clearance completed at Tanjung Priok Jakarta.'],
                    ['time_sub_days' => 25, 'loc' => $tpp, 'desc' => 'Vessel Maersk Gateshead sailed westward.'],
                    ['time_sub_days' => 18, 'loc' => $sin, 'desc' => 'Container loaded onto Mediterranean express service feeder.'],
                    ['time_sub_days' => 8, 'loc' => null, 'name' => 'Suez Canal Transit', 'lat' => 29.9700, 'lon' => 32.5500, 'desc' => 'Northbound convoy transit completed without incident.'],
                    ['time_sub_days' => 2, 'loc' => $goa, 'desc' => 'Vessel safely berthed at Port of Genoa Calata Sanita container terminal.'],
                    ['time_sub_days' => 1, 'loc' => $goa, 'desc' => 'Customs import inspection released; container gate-out delivered to consignee.'],
                ]
            ],

            // Shipment 6: Vietnam -> Belgium (Green Robusta Coffee, In Transit, On-Time)
            [
                'number' => 'CTC-2026-0006',
                'customer' => $customer2,
                'carrier' => $one,
                'origin' => $dad,
                'destination' => $anr,
                'cargo_type' => 'Green Robusta Coffee Beans',
                'weight' => 48000.00,
                'volume' => 92.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 11,
                'arr_add_days' => 14,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Clean dry standard container. Screen size 18, max 0.5% defects.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Dak Lak Polished Green Robusta Coffee Beans',
                        'variety' => 'Coffea Canephora (Robusta)',
                        'grade' => 'Grade 1 Screen 18 Wet Polished',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 800,
                        'net_weight' => 48000.00,
                        'moisture' => 12.0,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 11, 'loc' => $dad, 'desc' => 'Container dispatched from Da Nang Port Tien Sa terminal.'],
                    ['time_sub_days' => 7, 'loc' => $sin, 'desc' => 'Vessel ONE Apus berthed at Singapore for Europe-bound consolidation.'],
                    ['time_sub_days' => 2, 'loc' => null, 'name' => 'Strait of Malacca Northwest Sector', 'lat' => 5.4000, 'lon' => 97.8000, 'desc' => 'Vessel cruising at 21.2 knots heading toward Sri Lanka waypoint.'],
                ]
            ],

            // Shipment 7: Ethiopia -> USA (Green Arabica Coffee Yirgacheffe, In Transit, Low Delay 4h)
            [
                'number' => 'CTC-2026-0007',
                'customer' => $customer4,
                'carrier' => $maersk,
                'origin' => $dji,
                'destination' => $nyc,
                'cargo_type' => 'Green Arabica Coffee Beans',
                'weight' => 21600.00,
                'volume' => 42.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 14,
                'arr_add_days' => 12,
                'delay_hours' => 4.00,
                'delay_severity' => Shipment::SEVERITY_LOW,
                'delay_reason' => 'Delay of 4 hours due to Documentation Issue during Bill of Lading endorsement at port of origin.',
                'instructions' => 'GrainPro hermetic inner liners inside jute bags. Protect from humidity variations.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Ethiopian Yirgacheffe Grade 1 Washed Coffee',
                        'variety' => 'Ethiopia Heirloom Cultivars',
                        'grade' => 'Specialty Grade 1 Washed (Q-Score 88.5)',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 360,
                        'net_weight' => 21600.00,
                        'moisture' => 10.9,
                        'temp' => null,
                    ]
                ],
                'exception' => [
                    'type' => ShipmentException::TYPE_DOCUMENTATION_ISSUE,
                    'severity' => ShipmentException::SEVERITY_LOW,
                    'status' => ShipmentException::STATUS_RESOLVED,
                    'impact' => 4,
                    'desc' => 'ICO Certificate of Origin serial number discrepancy cleared by Ethiopian Coffee Authority.',
                    'detected_sub_hours' => 48,
                    'resolved_sub_hours' => 44,
                    'notes' => 'Corrected digital ICO certificate uploaded and validated by customs.',
                ],
                'waypoints' => [
                    ['time_sub_days' => 14, 'loc' => $dji, 'desc' => 'Loaded at Doraleh Container Terminal Djibouti.'],
                    ['time_sub_days' => 9, 'loc' => null, 'name' => 'Red Sea Transit Lane', 'lat' => 22.1000, 'lon' => 38.2000, 'desc' => 'Vessel sailing northbound toward Suez Canal.'],
                    ['time_sub_days' => 5, 'loc' => null, 'name' => 'Mediterranean Sea (Sicily Strait)', 'lat' => 37.1000, 'lon' => 12.3000, 'desc' => 'Passing Mediterranean transit point on course for Gibraltar.'],
                    ['time_sub_days' => 1, 'loc' => null, 'name' => 'Mid-Atlantic Transit (34.5N, -35.2W)', 'lat' => 34.5000, 'lon' => -35.2000, 'desc' => 'Cruising westbound across North Atlantic at 19.5 knots.'],
                ]
            ],

            // Shipment 8: Indonesia -> Germany (Cocoa Powder, PICKED_UP)
            [
                'number' => 'CTC-2026-0008',
                'customer' => $customer3,
                'carrier' => $msc,
                'origin' => $sub,
                'destination' => $ham,
                'cargo_type' => 'Cocoa Powder',
                'weight' => 25000.00,
                'volume' => 50.00,
                'status' => Shipment::STATUS_PICKED_UP,
                'dep_sub_days' => 1,
                'arr_add_days' => 25,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Heavy-duty poly-lined 25kg multiwall paper sacks. Stacked on heat-treated pallets.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COCOA,
                        'name' => 'Dutch-Process Alkalized Cocoa Powder (10-12% Fat)',
                        'variety' => 'Dark Rich Brown Cocoa Powder',
                        'grade' => 'Premium Bakery & Confectionery Grade',
                        'package_type' => 'BOX',
                        'package_count' => 1000,
                        'net_weight' => 25000.00,
                        'moisture' => 4.5,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 2, 'loc' => $sub, 'desc' => 'Export declaration PEB verified at Tanjung Perak Surabaya.'],
                    ['time_sub_days' => 1, 'loc' => $sub, 'desc' => 'Container loaded aboard MSC Gülsün at International Container Terminal Surabaya.'],
                ]
            ],

            // Shipment 9: Indonesia -> Germany (Green Arabica Mandheling, BOOKED)
            [
                'number' => 'CTC-2026-0009',
                'customer' => $customer1,
                'carrier' => $cmacgm,
                'origin' => $blw,
                'destination' => $ham,
                'cargo_type' => 'Green Arabica Coffee Beans',
                'weight' => 38400.00,
                'volume' => 76.00,
                'status' => Shipment::STATUS_BOOKED,
                'dep_sub_days' => -2, // in 2 days
                'arr_add_days' => 28,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Double jute bagging. Keep away from odors and engine room heat bulkheads.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Sumatra Lintong Nihuta Arabica Green Coffee',
                        'variety' => 'Arabica Lasuna & Garunggang',
                        'grade' => 'Specialty Semi-Washed (Giling Basah)',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 640,
                        'net_weight' => 38400.00,
                        'moisture' => 11.5,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 1, 'loc' => $blw, 'desc' => 'Booking space confirmed on CMA CGM Jacques Saade. Vessel ETA at Belawan in 48 hours.'],
                ]
            ],

            // Shipment 10: Indonesia -> Netherlands (Green Robusta Lampung, CREATED)
            [
                'number' => 'CTC-2026-0010',
                'customer' => $customer1,
                'carrier' => $maersk,
                'origin' => $tpp,
                'destination' => $rtm,
                'cargo_type' => 'Green Robusta Coffee Beans',
                'weight' => 40000.00,
                'volume' => 78.00,
                'status' => Shipment::STATUS_CREATED,
                'dep_sub_days' => -4, // in 4 days
                'arr_add_days' => 30,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Standard dry bulk container liner with moisture traps.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Lampung EK-1 Natural Robusta Coffee Beans',
                        'variety' => 'Coffea Canephora Lampung',
                        'grade' => 'Grade 1 Dry Processed (Max 80 defects)',
                        'package_type' => 'BULK_LINER',
                        'package_count' => 1,
                        'net_weight' => 40000.00,
                        'moisture' => 12.2,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 0, 'loc' => $tpp, 'desc' => 'Shipment order created in CargoTrace. Awaiting empty container allocation at Tanjung Priok.'],
                ]
            ],

            // Shipment 11: Ghana -> Netherlands (Fermented Cocoa Beans, DELIVERED, On-Time)
            [
                'number' => 'CTC-2026-0011',
                'customer' => $customer3,
                'carrier' => $hapag,
                'origin' => $tem,
                'destination' => $rtm,
                'cargo_type' => 'Fermented Cocoa Beans',
                'weight' => 45000.00,
                'volume' => 88.00,
                'status' => Shipment::STATUS_DELIVERED,
                'dep_sub_days' => 22,
                'arr_add_days' => -1, // delivered yesterday
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Rainforest Alliance Certified. Batch testing for free fatty acids approved.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COCOA,
                        'name' => 'Ghana Cocoa Board Certified Beans (Batch GH-992)',
                        'variety' => 'Forastero High-Flavor',
                        'grade' => 'Main Crop Grade 1 (Bean count 95/100g)',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 750,
                        'net_weight' => 45000.00,
                        'moisture' => 7.1,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 22, 'loc' => $tem, 'desc' => 'Vessel departed Tema Port on schedule.'],
                    ['time_sub_days' => 14, 'loc' => null, 'name' => 'Atlantic Ocean (West Sahara Sector)', 'lat' => 23.5000, 'lon' => -17.5000, 'desc' => 'Smooth sailing at 19 knots.'],
                    ['time_sub_days' => 2, 'loc' => $rtm, 'desc' => 'Arrived at Port of Rotterdam Maasvlakte 2.'],
                    ['time_sub_days' => 1, 'loc' => $rtm, 'desc' => 'Cargo discharged and accepted at C. Steinweg cocoa warehouse Rotterdam.'],
                ]
            ],

            // Shipment 12: Indonesia -> Germany (Roasted Robusta Coffee, In Transit, High Delay 32h)
            [
                'number' => 'CTC-2026-0012',
                'customer' => $customer1,
                'carrier' => $msc,
                'origin' => $tpp,
                'destination' => $ham,
                'cargo_type' => 'Roasted Robusta Coffee',
                'weight' => 20000.00,
                'volume' => 44.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 15,
                'arr_add_days' => 10,
                'delay_hours' => 32.00,
                'delay_severity' => Shipment::SEVERITY_HIGH,
                'delay_reason' => 'Delay of 32 hours due to Misrouted feeder container at Singapore transshipment yard.',
                'instructions' => 'Fragile aromatic cargo. Nitrogen-purged packaging.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Dark Roast Espresso Robusta Blend',
                        'variety' => 'Java & Flores Robusta',
                        'grade' => 'Commercial Dark Roast for European Vending',
                        'package_type' => 'BOX',
                        'package_count' => 2000,
                        'net_weight' => 20000.00,
                        'moisture' => 1.8,
                        'temp' => null,
                    ]
                ],
                'exception' => [
                    'type' => ShipmentException::TYPE_MISROUTED,
                    'severity' => ShipmentException::SEVERITY_HIGH,
                    'status' => ShipmentException::STATUS_INVESTIGATING,
                    'impact' => 32,
                    'desc' => 'Container mistakenly routed to regional feeder stack; re-manifested to mother vessel MSC Zoe.',
                    'detected_sub_hours' => 40,
                ],
                'waypoints' => [
                    ['time_sub_days' => 15, 'loc' => $tpp, 'desc' => 'Vessel departed Tanjung Priok on schedule.'],
                    ['time_sub_days' => 12, 'loc' => $sin, 'desc' => 'Feeder connection missed due to yard sorting error.'],
                    ['time_sub_days' => 10, 'loc' => $sin, 'desc' => 'Container reloaded aboard mother vessel MSC Zoe. Departure resumed.'],
                    ['time_sub_days' => 3, 'loc' => null, 'name' => 'Gulf of Aden Transit Corridor', 'lat' => 12.8000, 'lon' => 47.5000, 'desc' => 'Vessel cruising under naval escort at 18 knots.'],
                ]
            ],

            // Shipment 13: Côte d'Ivoire -> Belgium (Cocoa Liquor, In Transit, On-Time)
            [
                'number' => 'CTC-2026-0013',
                'customer' => $customer2,
                'carrier' => $cmacgm,
                'origin' => $abj,
                'destination' => $anr,
                'cargo_type' => 'Cocoa Liquor',
                'weight' => 24000.00,
                'volume' => 48.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 7,
                'arr_add_days' => 11,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Solidified cocoa paste blocks in corrugated cartons with heat-reflective thermal liners.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COCOA,
                        'name' => 'Pure Ground Cocoa Mass / Liquor',
                        'variety' => 'West African Forastero Blend',
                        'grade' => 'Industrial Chocolate Manufacturer Grade (53% Cocoa Butter)',
                        'package_type' => 'BOX',
                        'package_count' => 960,
                        'net_weight' => 24000.00,
                        'moisture' => 1.2,
                        'temp' => 20.0,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 7, 'loc' => $abj, 'desc' => 'Vessel departed Port of Abidjan.'],
                    ['time_sub_days' => 3, 'loc' => null, 'name' => 'Dakar Oceanic Approaches', 'lat' => 14.6000, 'lon' => -17.8000, 'desc' => 'Cruising at 20 knots on direct Antwerp express service.'],
                ]
            ],

            // Shipment 14: Indonesia -> Italy (Green Arabica Gayo Organic, BOOKED)
            [
                'number' => 'CTC-2026-0014',
                'customer' => $customer1,
                'carrier' => $one,
                'origin' => $blw,
                'destination' => $goa,
                'cargo_type' => 'Green Arabica Coffee Beans',
                'weight' => 19200.00,
                'volume' => 38.00,
                'status' => Shipment::STATUS_BOOKED,
                'dep_sub_days' => -3,
                'arr_add_days' => 22,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'USDA & EU Organic Certified. Segregated hold required.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Aceh Gayo Organic Fairtrade Arabica Green Coffee',
                        'variety' => 'Arabica Gayo 1 & 2',
                        'grade' => 'Specialty Certified Organic (Hand Picked)',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 320,
                        'net_weight' => 19200.00,
                        'moisture' => 11.2,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 1, 'loc' => $blw, 'desc' => 'Booking accepted by ONE Line. Empty food-grade container allocated.'],
                ]
            ],

            // Shipment 15: Vietnam -> USA (Green Robusta Coffee, In Transit, On-Time)
            [
                'number' => 'CTC-2026-0015',
                'customer' => $customer4,
                'carrier' => $maersk,
                'origin' => $dad,
                'destination' => $nyc,
                'cargo_type' => 'Green Robusta Coffee Beans',
                'weight' => 54000.00,
                'volume' => 105.00,
                'status' => Shipment::STATUS_IN_TRANSIT,
                'dep_sub_days' => 16,
                'arr_add_days' => 9,
                'delay_hours' => 0.00,
                'delay_severity' => Shipment::SEVERITY_NONE,
                'delay_reason' => null,
                'instructions' => 'Trans-Pacific & Panama Canal routing. Continuous hygrometer monitoring.',
                'items' => [
                    [
                        'category' => ShipmentItem::CATEGORY_COFFEE,
                        'name' => 'Vietnam Central Highlands Robusta Grade 1',
                        'variety' => 'Coffea Canephora High-Density',
                        'grade' => 'Clean Polished Screen 16/18',
                        'package_type' => 'JUTE_BAG_60KG',
                        'package_count' => 900,
                        'net_weight' => 54000.00,
                        'moisture' => 11.9,
                        'temp' => null,
                    ]
                ],
                'exception' => null,
                'waypoints' => [
                    ['time_sub_days' => 16, 'loc' => $dad, 'desc' => 'Vessel Maersk Mc-Kinney Moller departed Da Nang.'],
                    ['time_sub_days' => 12, 'loc' => null, 'name' => 'Pacific Ocean Equatorial Lane', 'lat' => 15.2000, 'lon' => 145.3000, 'desc' => 'Sailing eastbound at 21 knots.'],
                    ['time_sub_days' => 4, 'loc' => null, 'name' => 'Panama Canal Pacific Anchorage', 'lat' => 8.9000, 'lon' => -79.5500, 'desc' => 'Panama Canal transit booked for northbound lock passage.'],
                    ['time_sub_days' => 1, 'loc' => null, 'name' => 'Caribbean Sea East of Jamaica', 'lat' => 17.8000, 'lon' => -74.2000, 'desc' => 'Sailing northward toward US East Coast. On time for New York arrival.'],
                ]
            ],
        ];

        $vesselDirectory = [
            'CTC-2026-0001' => 'MV Nusantara Jaya',
            'CTC-2026-0002' => 'CMA CGM Java Express',
            'CTC-2026-0003' => 'MSC Sumatra Star',
            'CTC-2026-0004' => 'MV Santos Pioneer',
            'CTC-2026-0005' => 'Maersk Abidjan Trader',
            'CTC-2026-0006' => 'Hapag-Lloyd Accra',
            'CTC-2026-0007' => 'ONE Malacca Voyager',
            'CTC-2026-0008' => 'Evergreen Toraja',
            'CTC-2026-0009' => 'COSCO Bali Mariner',
            'CTC-2026-0010' => 'Pacific Malacca Feeder',
            'CTC-2026-0011' => 'Yang Ming Cenderawasih',
            'CTC-2026-0012' => 'MSC Gayo Explorer',
            'CTC-2026-0013' => 'CMA CGM Atlantique',
            'CTC-2026-0014' => 'Maersk Rio Grande',
            'CTC-2026-0015' => 'KM Priok Sejahtera',
        ];

        // Seed each shipment and its related records
        foreach ($shipmentsData as $s) {
            $expectedArrival = $now->copy()->addDays($s['arr_add_days']);
            $estimatedArrival = $s['delay_hours'] > 0
                ? $expectedArrival->copy()->addHours((int)$s['delay_hours'])
                : $expectedArrival;

            $shipment = Shipment::create([
                'shipment_number' => $s['number'],
                'tracking_number' => $s['number'],
                'customer_id' => $s['customer']->id,
                'carrier_id' => $s['carrier']->id,
                'origin_id' => $s['origin']->id,
                'destination_id' => $s['destination']->id,
                'cargo_type' => $s['cargo_type'],
                'weight' => $s['weight'],
                'volume' => $s['volume'],
                'current_status' => $s['status'],
                'vessel_carrier' => $vesselDirectory[$s['number']] ?? 'MV Nusantara Jaya',
                'expected_departure' => $now->copy()->subDays($s['dep_sub_days']),
                'expected_arrival' => $expectedArrival,
                'estimated_arrival' => $estimatedArrival,
                'actual_departure' => $s['dep_sub_days'] > 0 ? $now->copy()->subDays($s['dep_sub_days'])->addHours(2) : null,
                'actual_arrival' => $s['status'] === Shipment::STATUS_DELIVERED ? $expectedArrival : null,
                'is_delayed' => $s['delay_hours'] > 0,
                'delay_hours' => $s['delay_hours'],
                'delay_severity' => $s['delay_severity'],
                'delay_reason' => $s['delay_reason'],
                'special_instructions' => $s['instructions'],
            ]);

            // Seed Shipment Items
            foreach ($s['items'] as $item) {
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'product_category' => $item['category'],
                    'product_name' => $item['name'],
                    'variety' => $item['variety'],
                    'grade' => $item['grade'],
                    'item_description' => "{$item['name']} - {$item['grade']}",
                    'hs_code' => $item['category'] === ShipmentItem::CATEGORY_COCOA ? '1801.00' : '0901.11',
                    'quantity' => $item['package_count'],
                    'package_type' => $item['package_type'],
                    'package_count' => $item['package_count'],
                    'net_weight' => $item['net_weight'],
                    'weight_kg' => $item['net_weight'],
                    'volume_cbm' => $s['volume'],
                    'moisture_content' => $item['moisture'],
                    'temperature_target' => $item['temp'],
                    'is_hazardous' => false,
                ]);
            }

            // Seed Tracking Events
            foreach ($s['waypoints'] as $wp) {
                $locationId = isset($wp['loc']) && $wp['loc'] ? $wp['loc']->id : null;
                $locationName = isset($wp['loc']) && $wp['loc'] ? $wp['loc']->name : ($wp['name'] ?? 'Maritime Transit Waypoint');
                $lat = isset($wp['loc']) && $wp['loc'] ? $wp['loc']->latitude : ($wp['lat'] ?? null);
                $lon = isset($wp['loc']) && $wp['loc'] ? $wp['loc']->longitude : ($wp['lon'] ?? null);

                TrackingEvent::create([
                    'shipment_id' => $shipment->id,
                    'event_type' => 'TRANSIT_UPDATE',
                    'status' => $s['status'],
                    'event_time' => $now->copy()->subDays($wp['time_sub_days']),
                    'location_id' => $locationId,
                    'location_name' => $locationName,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'description' => $wp['desc'],
                    'is_exception_event' => false,
                    'created_by' => $operator1->id,
                ]);
            }

            // Seed Exception if present
            if (!empty($s['exception'])) {
                $ex = $s['exception'];
                $isResolved = $ex['status'] === ShipmentException::STATUS_RESOLVED;

                $exceptionRecord = ShipmentException::create([
                    'shipment_id' => $shipment->id,
                    'exception_type' => $ex['type'],
                    'severity' => $ex['severity'],
                    'status' => $ex['status'],
                    'description' => $ex['desc'],
                    'detected_at' => $now->copy()->subHours($ex['detected_sub_hours']),
                    'resolved_at' => $isResolved ? $now->copy()->subHours($ex['resolved_sub_hours']) : null,
                    'impact_hours' => $ex['impact'],
                    'logged_by' => $operator1->id,
                    'resolved_by' => $isResolved ? $operator1->id : null,
                    'resolution_notes' => $isResolved ? ($ex['notes'] ?? 'Action taken and resolved.') : null,
                ]);

                // Also log a tracking event for this exception
                TrackingEvent::create([
                    'shipment_id' => $shipment->id,
                    'event_type' => 'EXCEPTION_OCCURRED',
                    'status' => $s['status'],
                    'event_time' => $now->copy()->subHours($ex['detected_sub_hours']),
                    'location_name' => 'Operational Exception Point',
                    'description' => "[EXCEPTION] {$ex['type']}: {$ex['desc']}",
                    'is_exception_event' => true,
                    'created_by' => $operator1->id,
                ]);
            }

            // In-app Notification for Customer
            Notification::create([
                'user_id' => $s['customer']->id,
                'shipment_id' => $shipment->id,
                'type' => $s['delay_hours'] > 0 ? 'DELAY_ALERT' : 'STATUS_UPDATE',
                'title' => $s['delay_hours'] > 0
                    ? "Schedule Alert: Shipment #{$shipment->shipment_number}"
                    : "Shipment Update: #{$shipment->shipment_number}",
                'message' => $s['delay_hours'] > 0
                    ? "Your shipment of {$s['cargo_type']} has an estimated delay of {$s['delay_hours']} hours ({$s['delay_severity']})."
                    : "Shipment #{$shipment->shipment_number} ({$s['cargo_type']}) is currently {$s['status']}.",
                'is_read' => false,
            ]);

            // Audit log for creation
            AuditLog::create([
                'user_id' => $operator1->id,
                'action' => 'SHIPMENT_SEEDED',
                'entity_type' => 'Shipment',
                'entity_id' => $shipment->id,
                'description' => "Initial registration for Cocoa & Coffee shipment #{$shipment->shipment_number}",
                'old_values' => null,
                'new_values' => [
                    'number' => $shipment->shipment_number,
                    'cargo' => $shipment->cargo_type,
                    'weight' => $shipment->weight,
                    'origin' => $s['origin']->name,
                    'destination' => $s['destination']->name,
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'DatabaseSeeder/CLI',
            ]);
        }
    }
}

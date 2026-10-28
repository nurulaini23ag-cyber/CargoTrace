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
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Settings & Delay Thresholds (Slide 08 & 23)
        SystemSetting::set('delay_threshold_low', 6.0, 'delay_rules', 'Delay up to 6 hours is classified as LOW');
        SystemSetting::set('delay_threshold_medium', 24.0, 'delay_rules', 'Delay between 6 and 24 hours is classified as MEDIUM; >24h is HIGH');
        SystemSetting::set('group_scenario', 'A', 'scenario', 'Active group scenario preset (Group A: SE Asia to Europe, Customs Hold)');

        // 2. Users & Roles (Slide 05)
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@cargotrace.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'company_name' => 'CargoTrace Global HQ',
            'phone' => '+62 21 555 0100',
            'email_verified_at' => now(),
        ]);

        $operator = User::create([
            'name' => 'Logistics Dispatcher Operator',
            'email' => 'operator@cargotrace.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OPERATOR,
            'company_name' => 'Tanjung Priok Logistics Operations Hub',
            'phone' => '+62 21 555 0101',
            'email_verified_at' => now(),
        ]);

        $customer1 = User::create([
            'name' => 'PT Nusantara Global Logistics (Budi Santoso)',
            'email' => 'customer@cargotrace.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
            'company_name' => 'PT Nusantara Global Trade',
            'phone' => '+62 811 2345 6789',
            'email_verified_at' => now(),
        ]);

        $customer2 = User::create([
            'name' => 'Pacific Rim Importers (Sarah Jenkins)',
            'email' => 'customer2@cargotrace.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
            'company_name' => 'Pacific Rim Imports Ltd',
            'phone' => '+1 310 555 0199',
            'email_verified_at' => now(),
        ]);

        // 3. Carriers (Slide 05 & 11)
        $maersk = Carrier::create([
            'code' => 'MAEU',
            'name' => 'A.P. Moller - Maersk Line',
            'contact_email' => 'support@maersk.example.com',
            'contact_phone' => '+45 33 63 33 63',
            'website' => 'https://www.maersk.com',
            'mode' => 'SEA',
            'is_active' => true,
        ]);

        $msc = Carrier::create([
            'code' => 'MSCU',
            'name' => 'Mediterranean Shipping Company (MSC)',
            'contact_email' => 'info@msc.example.com',
            'contact_phone' => '+41 22 703 8888',
            'website' => 'https://www.msc.com',
            'mode' => 'SEA',
            'is_active' => true,
        ]);

        $cmacgm = Carrier::create([
            'code' => 'CMDU',
            'name' => 'CMA CGM Group',
            'contact_email' => 'contact@cma-cgm.example.com',
            'contact_phone' => '+33 4 88 91 90 00',
            'website' => 'https://www.cmacgm-group.com',
            'mode' => 'SEA',
            'is_active' => true,
        ]);

        $dhl = Carrier::create([
            'code' => 'DHL',
            'name' => 'DHL Global Forwarding',
            'contact_email' => 'freight@dhl.example.com',
            'contact_phone' => '+49 228 1820',
            'website' => 'https://www.dhl.com',
            'mode' => 'AIR',
            'is_active' => true,
        ]);

        // 4. Locations & Major Ports (Slide 09 & 11)
        $jkt = Location::create([
            'code' => 'IDJKT',
            'name' => 'Port of Tanjung Priok',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'country_code' => 'ID',
            'latitude' => -6.1022,
            'longitude' => 106.8825,
            'type' => 'PORT',
        ]);

        $sin = Location::create([
            'code' => 'SGSIN',
            'name' => 'Port of Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'country_code' => 'SG',
            'latitude' => 1.2644,
            'longitude' => 103.8400,
            'type' => 'PORT',
        ]);

        $rtm = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'country_code' => 'NL',
            'latitude' => 51.9540,
            'longitude' => 4.0530,
            'type' => 'PORT',
        ]);

        $lax = Location::create([
            'code' => 'USLAX',
            'name' => 'Port of Los Angeles',
            'city' => 'Los Angeles',
            'country' => 'United States',
            'country_code' => 'US',
            'latitude' => 33.7432,
            'longitude' => -118.2673,
            'type' => 'PORT',
        ]);

        $sha = Location::create([
            'code' => 'CNSHA',
            'name' => 'Port of Shanghai',
            'city' => 'Shanghai',
            'country' => 'China',
            'country_code' => 'CN',
            'latitude' => 31.2304,
            'longitude' => 121.4737,
            'type' => 'PORT',
        ]);

        $mel = Location::create([
            'code' => 'AUMEL',
            'name' => 'Port of Melbourne',
            'city' => 'Melbourne',
            'country' => 'Australia',
            'country_code' => 'AU',
            'latitude' => -37.8467,
            'longitude' => 144.9288,
            'type' => 'PORT',
        ]);

        $ham = Location::create([
            'code' => 'DEHAM',
            'name' => 'Port of Hamburg',
            'city' => 'Hamburg',
            'country' => 'Germany',
            'country_code' => 'DE',
            'latitude' => 53.5350,
            'longitude' => 9.9800,
            'type' => 'PORT',
        ]);

        $etaService = app(EtaService::class);

        // 5. SHIPMENT 1 (Group A Scenario - Slide 23):
        // Route: Southeast Asia (IDJKT) -> Europe (NLRTM)
        // Exception: CUSTOMS_HOLD
        // Delay threshold: 12h (calculated delay: 14h -> MEDIUM delay)
        $now = now();
        $expectedArrival1 = $now->copy()->addDays(7);
        $estimatedArrival1 = $expectedArrival1->copy()->addHours(14); // 14 hours delay

        $shipment1 = Shipment::create([
            'shipment_number' => 'TRC-2026-EU001',
            'customer_id' => $customer1->id,
            'carrier_id' => $maersk->id,
            'origin_id' => $jkt->id,
            'destination_id' => $rtm->id,
            'cargo_type' => 'Semiconductor Electronics & Solar Inverters',
            'weight' => 24500.00,
            'volume' => 64.20,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => $now->copy()->subDays(6),
            'expected_arrival' => $expectedArrival1,
            'estimated_arrival' => $estimatedArrival1,
            'actual_departure' => $now->copy()->subDays(6)->addHours(2),
            'is_delayed' => true,
            'delay_hours' => 14.00,
            'delay_severity' => Shipment::SEVERITY_MEDIUM,
            'delay_reason' => 'ETA exceeds scheduled arrival by 14.00 hours due to Customs Hold inspection at transshipment hub.',
            'special_instructions' => 'Group A Academic UAS Scenario. Maintain sealed climate-controlled container at 20°C.',
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'item_description' => 'Industrial Solar Microinverters (Model XI-800)',
            'hs_code' => '8504.40',
            'quantity' => 120,
            'package_type' => 'PALLET',
            'weight_kg' => 12000,
            'volume_cbm' => 32,
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'item_description' => 'Lithium Battery Monitoring Units',
            'hs_code' => '8507.60',
            'quantity' => 250,
            'package_type' => 'BOX',
            'weight_kg' => 12500,
            'volume_cbm' => 32.2,
            'is_hazardous' => true,
        ]);

        // Tracking events for Shipment 1
        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'event_type' => 'CREATED',
            'status' => Shipment::STATUS_CREATED,
            'event_time' => $now->copy()->subDays(7),
            'location_id' => $jkt->id,
            'location_name' => 'Port of Tanjung Priok, Jakarta',
            'latitude' => -6.1022,
            'longitude' => 106.8825,
            'description' => 'Shipment booking registered in CargoTrace system.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'event_type' => 'BOOKED',
            'status' => Shipment::STATUS_BOOKED,
            'event_time' => $now->copy()->subDays(6)->subHours(12),
            'location_id' => $jkt->id,
            'location_name' => 'Port of Tanjung Priok Container Terminal 1',
            'latitude' => -6.1022,
            'longitude' => 106.8825,
            'description' => 'Carrier booking slot confirmed on vessel Maersk Mc-Kinney Moller.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'event_type' => 'PICKED_UP',
            'status' => Shipment::STATUS_PICKED_UP,
            'event_time' => $now->copy()->subDays(6)->subHours(2),
            'location_id' => $jkt->id,
            'location_name' => 'Tanjung Priok Gate 3 Outbound',
            'latitude' => -6.1022,
            'longitude' => 106.8825,
            'description' => 'Container loaded aboard vessel; customs departure manifest stamped.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'event_type' => 'IN_TRANSIT',
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_time' => $now->copy()->subDays(4),
            'location_id' => $sin->id,
            'location_name' => 'Port of Singapore Pasir Panjang Terminal',
            'latitude' => 1.2644,
            'longitude' => 103.8400,
            'description' => 'Vessel transshipment berth scan at Singapore hub.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'event_type' => 'EXCEPTION_OCCURRED',
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_time' => $now->copy()->subHours(18),
            'location_name' => 'Red Sea Transshipment Security Inspection Point',
            'latitude' => 20.0000,
            'longitude' => 38.5000,
            'description' => '[EXCEPTION: CUSTOMS_HOLD] (MEDIUM) Random secondary verification hold imposed by regional port customs authority.',
            'is_exception_event' => true,
            'created_by' => $operator->id,
        ]);

        // Exception for Shipment 1
        ShipmentException::create([
            'shipment_id' => $shipment1->id,
            'exception_type' => ShipmentException::TYPE_CUSTOMS_HOLD,
            'severity' => ShipmentException::SEVERITY_MEDIUM,
            'status' => ShipmentException::STATUS_OPEN,
            'detected_at' => $now->copy()->subHours(18),
            'impact_hours' => 14,
            'description' => 'Customs agency selected container for non-intrusive radiologic and document verification check.',
            'logged_by' => $operator->id,
        ]);

        Notification::create([
            'user_id' => $customer1->id,
            'shipment_id' => $shipment1->id,
            'type' => 'EXCEPTION_ALERT',
            'title' => 'Customs Hold Incident Alert: #TRC-2026-EU001',
            'message' => 'Shipment #TRC-2026-EU001 has encountered a Customs Hold in the Red Sea corridor. ETA adjusted by +14 hours.',
        ]);

        // 6. SHIPMENT 2 (Group B Scenario - Slide 23):
        // Route: Asia (CNSHA) -> North America (USLAX)
        // Exception: WEATHER_DELAY (Typhoon)
        // Delay: 20 hours (evaluated against 18h threshold -> HIGH delay)
        $expectedArrival2 = $now->copy()->addDays(5);
        $estimatedArrival2 = $expectedArrival2->copy()->addHours(20);

        $shipment2 = Shipment::create([
            'shipment_number' => 'TRC-2026-US002',
            'customer_id' => $customer2->id,
            'carrier_id' => $msc->id,
            'origin_id' => $sha->id,
            'destination_id' => $lax->id,
            'cargo_type' => 'Precision Medical Diagnostic Apparatus',
            'weight' => 18200.00,
            'volume' => 45.00,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'expected_departure' => $now->copy()->subDays(8),
            'expected_arrival' => $expectedArrival2,
            'estimated_arrival' => $estimatedArrival2,
            'actual_departure' => $now->copy()->subDays(8),
            'is_delayed' => true,
            'delay_hours' => 20.00,
            'delay_severity' => Shipment::SEVERITY_HIGH,
            'delay_reason' => 'ETA exceeds scheduled arrival by 20.00 hours. Tropical depression necessitated vessel course deviation.',
            'special_instructions' => 'Group B UAS Scenario. Shock-sensitive cargo, handle with pneumatic lifts only.',
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment2->id,
            'item_description' => 'Computed Tomography (CT) Scanning Sensor Ring',
            'hs_code' => '9022.12',
            'quantity' => 4,
            'package_type' => 'CRATE',
            'weight_kg' => 18200,
            'volume_cbm' => 45,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'event_type' => 'CREATED',
            'status' => Shipment::STATUS_CREATED,
            'event_time' => $now->copy()->subDays(9),
            'location_id' => $sha->id,
            'location_name' => 'Port of Shanghai Yangshan Terminal',
            'latitude' => 31.2304,
            'longitude' => 121.4737,
            'description' => 'Shipment booking created in Shanghai regional office.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'event_type' => 'PICKED_UP',
            'status' => Shipment::STATUS_PICKED_UP,
            'event_time' => $now->copy()->subDays(8),
            'location_id' => $sha->id,
            'location_name' => 'Port of Shanghai Deepwater Pier 4',
            'latitude' => 31.2304,
            'longitude' => 121.4737,
            'description' => 'Container hoisted onto MSC Gulsun vessel.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'event_type' => 'IN_TRANSIT',
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_time' => $now->copy()->subDays(3),
            'location_name' => 'Mid-Pacific Navigational Waypoint 44N',
            'latitude' => 35.5000,
            'longitude' => 170.0000,
            'description' => 'Vessel en route across Pacific great circle route.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'event_type' => 'EXCEPTION_OCCURRED',
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_time' => $now->copy()->subHours(10),
            'location_name' => 'North Pacific Storm Corridor',
            'latitude' => 36.2000,
            'longitude' => -150.0000,
            'description' => '[EXCEPTION: WEATHER_DELAY] (HIGH) Severe Pacific tropical storm forced vessel speed reduction and 150 nautical mile southern detour.',
            'is_exception_event' => true,
            'created_by' => $operator->id,
        ]);

        ShipmentException::create([
            'shipment_id' => $shipment2->id,
            'exception_type' => ShipmentException::TYPE_WEATHER_DELAY,
            'severity' => ShipmentException::SEVERITY_HIGH,
            'status' => ShipmentException::STATUS_INVESTIGATING,
            'detected_at' => $now->copy()->subHours(10),
            'impact_hours' => 20,
            'description' => 'Vessel captain routed 150 nautical miles south to avoid 8-meter swells, adding 20 hours to California ETA.',
            'logged_by' => $operator->id,
        ]);

        // 7. SHIPMENT 3 (Delivered On-Time Reference Case):
        // Route: Port of Tanjung Priok (IDJKT) -> Port of Melbourne (AUMEL)
        // Status: DELIVERED (terminal state)
        $deliveredTime = $now->copy()->subHours(8);
        $shipment3 = Shipment::create([
            'shipment_number' => 'TRC-2026-AU003',
            'customer_id' => $customer1->id,
            'carrier_id' => $cmacgm->id,
            'origin_id' => $jkt->id,
            'destination_id' => $mel->id,
            'cargo_type' => 'Organic Certified Coffee Beans (Arabica Gayo)',
            'weight' => 21000.00,
            'volume' => 52.00,
            'current_status' => Shipment::STATUS_DELIVERED,
            'expected_departure' => $now->copy()->subDays(14),
            'expected_arrival' => $now->copy()->subHours(12),
            'estimated_arrival' => $now->copy()->subHours(12),
            'actual_departure' => $now->copy()->subDays(14),
            'actual_arrival' => $deliveredTime,
            'is_delayed' => false,
            'delay_hours' => 0.00,
            'delay_severity' => Shipment::SEVERITY_NONE,
            'delay_reason' => 'Shipment arrived on schedule with zero variance.',
            'special_instructions' => 'Delivered and signed by consignee warehouse manager.',
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment3->id,
            'event_type' => 'PICKED_UP',
            'status' => Shipment::STATUS_PICKED_UP,
            'event_time' => $now->copy()->subDays(14),
            'location_id' => $jkt->id,
            'location_name' => 'Port of Tanjung Priok',
            'latitude' => -6.1022,
            'longitude' => 106.8825,
            'description' => 'Coffee container loaded aboard CMA CGM Coral.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment3->id,
            'event_type' => 'DELIVERED',
            'status' => Shipment::STATUS_DELIVERED,
            'event_time' => $deliveredTime,
            'location_id' => $mel->id,
            'location_name' => 'Port of Melbourne Swanson Dock',
            'latitude' => -37.8467,
            'longitude' => 144.9288,
            'description' => 'Container released and signed for by consignee at Swanson Dock East.',
            'created_by' => $operator->id,
        ]);

        // 8. SHIPMENT 4 (Group C Scenario - Slide 23):
        // Route: Port of Singapore (SGSIN) -> Port of Melbourne (AUMEL)
        // Exception: DOCUMENTATION_ISSUE (resolved)
        $shipment4 = Shipment::create([
            'shipment_number' => 'TRC-2026-AU004',
            'customer_id' => $customer2->id,
            'carrier_id' => $cmacgm->id,
            'origin_id' => $sin->id,
            'destination_id' => $mel->id,
            'cargo_type' => 'Pharmaceutical Vaccines & Cold Chain Supplies',
            'weight' => 5400.00,
            'volume' => 16.50,
            'current_status' => Shipment::STATUS_BOOKED,
            'expected_departure' => $now->copy()->addDays(2),
            'expected_arrival' => $now->copy()->addDays(10),
            'estimated_arrival' => $now->copy()->addDays(10),
            'is_delayed' => false,
            'delay_hours' => 0.00,
            'delay_severity' => Shipment::SEVERITY_NONE,
            'delay_reason' => 'Shipment is operating on schedule.',
            'special_instructions' => 'Group C Scenario: Documentation issue resolved during booking verification.',
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment4->id,
            'event_type' => 'CREATED',
            'status' => Shipment::STATUS_CREATED,
            'event_time' => $now->copy()->subDays(1),
            'location_id' => $sin->id,
            'location_name' => 'Port of Singapore',
            'latitude' => 1.2644,
            'longitude' => 103.8400,
            'description' => 'Consignment created in Singapore air-sea logistics system.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment4->id,
            'event_type' => 'BOOKED',
            'status' => Shipment::STATUS_BOOKED,
            'event_time' => $now->copy()->subHours(6),
            'location_id' => $sin->id,
            'location_name' => 'Port of Singapore',
            'latitude' => 1.2644,
            'longitude' => 103.8400,
            'description' => 'Vessel stowage slot booked and confirmed.',
            'created_by' => $operator->id,
        ]);

        // Audit Trail Initial Log
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'SYSTEM_INITIALIZED',
            'entity_type' => 'System',
            'entity_id' => 1,
            'description' => 'CargoTrace database initialized with test scenarios for Groups A, B, and C.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder/Console',
            'created_at' => now()->subDays(7),
        ]);
    }
}

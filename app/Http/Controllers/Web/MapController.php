<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Services\MaritimeSeaRouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MapController extends Controller
{
    /**
     * Display the Global Live Cargo Fleet Map.
     * Shows interactive worldwide maritime routes and exact current positions of coffee & cocoa shipments.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Shipment::with([
            'origin',
            'destination',
            'carrier',
            'customer',
            'items',
            'trackingEvents' => fn($q) => $q->orderBy('event_time', 'asc'),
        ]);

        if ($user && $user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Apply filters
        if ($request->filled('commodity')) {
            $comm = strtoupper($request->commodity);
            $query->where(function ($q) use ($comm) {
                $q->where('cargo_type', 'like', "%{$comm}%")
                  ->orWhereHas('items', function ($iq) use ($comm) {
                      $iq->where('commodity_category', 'like', "%{$comm}%")
                         ->orWhere('item_name', 'like', "%{$comm}%");
                  });
            });
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('q')) {
            $kw = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($kw) {
                $sub->where('shipment_number', 'like', $kw)
                    ->orWhere('tracking_number', 'like', $kw)
                    ->orWhere('vessel_carrier', 'like', $kw)
                    ->orWhere('cargo_type', 'like', $kw)
                    ->orWhereHas('customer', function ($cq) use ($kw) {
                        $cq->where('name', 'like', $kw)->orWhere('company_name', 'like', $kw);
                    })
                    ->orWhereHas('origin', function ($oq) use ($kw) {
                        $oq->where('name', 'like', $kw)->orWhere('city', 'like', $kw);
                    })
                    ->orWhereHas('destination', function ($dq) use ($kw) {
                        $dq->where('name', 'like', $kw)->orWhere('city', 'like', $kw);
                    });
            });
        }

        $shipments = $query->latest()->get();

        // Distinct, realistic vessel names mapped by shipment ID
        $vesselDirectory = [
            1 => 'MV Nusantara Jaya',
            2 => 'CMA CGM Java Express',
            3 => 'MSC Sumatra Star',
            4 => 'MV Santos Pioneer',
            5 => 'Maersk Abidjan Trader',
            6 => 'Hapag-Lloyd Accra',
            7 => 'ONE Malacca Voyager',
            8 => 'Evergreen Toraja',
            9 => 'COSCO Bali Mariner',
            10 => 'Pacific Malacca Feeder',
            11 => 'Yang Ming Cenderawasih',
            12 => 'MSC Gayo Explorer',
            13 => 'CMA CGM Atlantique',
            14 => 'Maersk Rio Grande',
            15 => 'KM Priok Sejahtera',
        ];

        // Map shipments to enriched geographic objects with realistic maritime routes
        $mapShipments = $shipments->map(function (Shipment $s) use ($user, $vesselDirectory) {
            $currentPos = $s->getCurrentPosition();
            $comm = $s->commodity_type;

            $itemsSummary = $s->items->count() > 0
                ? $s->items->map(fn($item) => ($item->item_name ?? $item->commodity_category) . ' (' . number_format($item->net_weight, 0) . ' kg)')->join(', ')
                : ($s->cargo_type . ' (' . number_format($s->weight, 0) . ' kg)');

            $originLat = (float) ($s->origin?->latitude ?? 0);
            $originLng = (float) ($s->origin?->longitude ?? 0);
            $destLat = (float) ($s->destination?->latitude ?? 0);
            $destLng = (float) ($s->destination?->longitude ?? 0);

            // True maritime sea corridor routed through authentic transit checkpoints
            $routeData = MaritimeSeaRouteService::getShipmentTransitRoute($s);
            $shipLat = $routeData['ship_lat'];
            $shipLng = $routeData['ship_lng'];
            $shipBearing = $routeData['bearing'];
            $progressPercent = $routeData['progress_percent'];
            $seaRouteCoords = $routeData['sea_route'];
            $traveledPath = $routeData['traveled_path'];
            $remainingPath = $routeData['remaining_path'];
            $locationName = $routeData['location_name'];

            // Customer-Requested Visual Types & Color Coding:
            // 1. Sudah Sampai (Delivered) -> Hijau Emerald (#10b981)
            // 2. Terhambat (Delayed / Exception / Problem) -> Merah Peringatan (#e11d48)
            // 3. Proses Asal (Staged / Created / Booked / Picked Up) -> Kuning Amber (#d97706)
            // 4. Sedang Berlayar (In Transit Normal) -> Biru Samudra (#0284c7)
            $isDelivered = ($s->current_status === 'DELIVERED');
            $isDelayed = ($s->is_delayed || $s->delay_hours > 0 || ($currentPos['is_signal_offline'] ?? false));
            $isStaged = in_array($s->current_status, [
                Shipment::STATUS_CREATED,
                Shipment::STATUS_BOOKED,
                Shipment::STATUS_PICKED_UP,
                'ORDER_CONFIRMED',
                'PROCESSING',
                'READY_FOR_SHIPMENT',
            ], true);

            if ($isDelivered) {
                $statusType = 'DELIVERED';
                $statusLabel = 'SUDAH SAMPAI DI TUJUAN';
                $statusColor = '#10b981'; // Emerald Green
                $statusBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                $shipColor = 'emerald';
                $navStatus = 'SUDAH SAMPAI (SELESAI)';
            } elseif ($isDelayed) {
                $statusType = 'DELAYED';
                $statusLabel = 'TERHAMBAT / PERINGATAN LOGISTIK (DELAY)';
                $statusColor = '#e11d48'; // Vibrant Warning Red
                $statusBadgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                $shipColor = 'rose';
                $navStatus = 'TERHAMBAT (DELAY ' . round($s->delay_hours, 1) . ' Jam)';
            } elseif ($isStaged) {
                $statusType = 'STAGED';
                $statusLabel = 'PROSES DI PELABUHAN ASAL';
                $statusColor = '#d97706'; // Amber Gold
                $statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                $shipColor = 'amber';
                $navStatus = 'SIAP MUAT DI ASAL';
            } else {
                $statusType = 'IN_TRANSIT';
                $statusLabel = 'SEDANG BERLAYAR (IN TRANSIT)';
                $statusColor = '#0284c7'; // Ocean Blue
                $statusBadgeClass = 'bg-blue-100 text-blue-800 border-blue-300';
                $shipColor = 'blue';
                $navStatus = 'SEDANG BERLAYAR LANCAR';
            }

            // Assign distinct vessel name
            $vesselName = ($s->vessel_carrier && $s->vessel_carrier !== 'MV Maersk Semarang')
                ? $s->vessel_carrier
                : ($vesselDirectory[$s->id] ?? ($s->carrier?->name . ' ' . $s->origin?->city . ' Liner'));

            return [
                'id' => $s->id,
                'number' => $s->shipment_number,
                'tracking_number' => $s->getActiveTrackingNumber(),
                'cargo_type' => $s->cargo_type,
                'commodity' => $comm,
                'weight' => (float) $s->weight,
                'weight_formatted' => number_format($s->weight, 0) . ' kg',
                'volume_formatted' => number_format($s->volume, 1) . ' m³',
                'items_summary' => $itemsSummary,
                'status' => $s->current_status,
                'status_type' => $statusType,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'status_badge_class' => $statusBadgeClass,
                'ship_color' => $shipColor,
                'progress_percent' => $progressPercent,
                'navigation_status' => $navStatus,
                'is_west_sector' => (bool) ($currentPos['is_west_sector'] ?? false),
                'sector_label' => $currentPos['sector_label'] ?? 'Koridor Maritim Internasional',
                'signal_status' => $currentPos['signal_status'] ?? 'ACTIVE',
                'is_signal_offline' => (bool) ($currentPos['is_signal_offline'] ?? false),
                'signal_label' => $currentPos['signal_label'] ?? '🟢 Sinyal AIS Aktif',
                'signal_note' => $currentPos['signal_note'] ?? 'Transmisi satelit AIS normal dan real-time.',
                'customer' => [
                    'id' => $s->customer?->id,
                    'name' => $s->customer?->name ?? 'Customer Consignee',
                    'company' => $s->customer?->company_name ?? 'International Buyer',
                ],
                'is_my_cargo' => $user ? ($s->customer_id === $user->id || $user->isAdmin() || $user->isOperator()) : true,
                'is_delayed' => (bool) $isDelayed,
                'delay_hours' => (float) $s->delay_hours,
                'carrier' => $s->carrier?->name ?? 'Maritime Express',
                'carrier_code' => $s->carrier?->code ?? 'OCN',
                'vessel_name' => $vesselName,
                'voyage_number' => $s->voyage_number ?? ('VOY-' . (2026 + $s->id)),
                'container_number' => $s->container_number ?? ('CTRU-' . (882900 + $s->id)),
                'expected_departure' => $s->expected_departure?->format('d M Y H:i'),
                'expected_arrival' => $s->expected_arrival?->format('d M Y'),
                'estimated_arrival' => $s->estimated_arrival?->format('d M Y'),
                'origin' => [
                    'name' => $s->origin?->name ?? 'Origin Port',
                    'city' => $s->origin?->city ?? 'Origin',
                    'country' => $s->origin?->country ?? 'Origin',
                    'lat' => $originLat,
                    'lng' => $originLng,
                ],
                'destination' => [
                    'name' => $s->destination?->name ?? 'Destination Port',
                    'city' => $s->destination?->city ?? 'Destination',
                    'country' => $s->destination?->country ?? 'Destination',
                    'lat' => $destLat,
                    'lng' => $destLng,
                ],
                'current_position' => [
                    'latitude' => $shipLat,
                    'longitude' => $shipLng,
                    'bearing' => $shipBearing,
                    'location_name' => $locationName,
                    'last_updated' => $currentPos['last_updated'] ?? now()->format('Y-m-d H:i'),
                    'progress_percent' => $progressPercent,
                    'is_west_sector' => (bool) ($currentPos['is_west_sector'] ?? false),
                ],
                'sea_route' => $seaRouteCoords,
                'traveled_path' => $traveledPath,
                'remaining_path' => $remainingPath,
                'detail_url' => route('shipments.show', $s->id),
            ];
        });

        // Apply post-collection filters if sector or signal filter requested
        if ($request->filled('sector') && $request->sector === 'BARAT') {
            $mapShipments = $mapShipments->filter(fn($m) => $m['is_west_sector'])->values();
        }

        if ($request->filled('signal')) {
            if ($request->signal === 'OFFLINE') {
                $mapShipments = $mapShipments->filter(fn($m) => $m['is_signal_offline'])->values();
            } elseif ($request->signal === 'ACTIVE') {
                $mapShipments = $mapShipments->filter(fn($m) => !$m['is_signal_offline'] && $m['status_type'] !== 'DELIVERED')->values();
            }
        }

        $carriers = Carrier::where('is_active', true)->get();

        $stats = [
            'total' => $mapShipments->count(),
            'delivered' => $mapShipments->where('status_type', 'DELIVERED')->count(),
            'in_transit' => $mapShipments->where('status_type', 'IN_TRANSIT')->count(),
            'delayed' => $mapShipments->where('status_type', 'DELAYED')->count(),
            'staged' => $mapShipments->where('status_type', 'STAGED')->count(),
            'west_sector' => $mapShipments->where('is_west_sector', true)->count(),
            'signal_offline' => $mapShipments->where('is_signal_offline', true)->count(),
            'coffee' => $mapShipments->filter(fn($s) => $s['commodity'] === 'COFFEE')->count(),
            'cocoa' => $mapShipments->filter(fn($s) => $s['commodity'] === 'COCOA')->count(),
        ];

        return view('map.index', compact('mapShipments', 'carriers', 'stats'));
    }

    /**
     * Return JSON data for map live reload or dynamic filtering.
     */
    public function shipmentsData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Shipment::with(['origin', 'destination', 'carrier', 'trackingEvents']);

        if ($user && $user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        $shipments = $query->get()->map(function (Shipment $s) {
            return [
                'id' => $s->id,
                'number' => $s->shipment_number,
                'commodity' => $s->commodity_type,
                'status' => $s->current_status,
                'is_delayed' => (bool) $s->is_delayed,
                'current_position' => $s->getCurrentPosition(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $shipments,
        ]);
    }
}

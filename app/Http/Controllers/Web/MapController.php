<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
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

        // Map shipments to enriched geographic objects with exact live positions
        $mapShipments = $shipments->map(function (Shipment $s) use ($user) {
            $currentPos = $s->getCurrentPosition();
            $comm = $s->commodity_type;

            $itemsSummary = $s->items->count() > 0
                ? $s->items->map(fn($item) => ($item->item_name ?? $item->commodity_category) . ' (' . number_format($item->net_weight, 0) . ' kg)')->join(', ')
                : ($s->cargo_type . ' (' . number_format($s->weight, 0) . ' kg)');

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
                'navigation_status' => $currentPos['navigation_status'] ?? ($s->current_status === 'DELIVERED' ? 'SUDAH SAMPAI' : ($s->current_status === 'IN_TRANSIT' ? 'SEDANG TRANSIT' : 'PROSES DI ASAL')),
                'is_west_sector' => (bool) ($currentPos['is_west_sector'] ?? false),
                'sector_label' => $currentPos['sector_label'] ?? 'Koridor Maritim',
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
                'is_delayed' => (bool) $s->is_delayed,
                'delay_hours' => (float) $s->delay_hours,
                'carrier' => $s->carrier?->name ?? 'Maritime Express',
                'carrier_code' => $s->carrier?->code ?? 'OCN',
                'vessel_name' => $s->vessel_name ?? $s->vessel_carrier ?? 'MV Cargo Liner',
                'voyage_number' => $s->voyage_number ?? 'VOY-2026',
                'container_number' => $s->container_number ?? 'CTRU-882910',
                'expected_departure' => $s->expected_departure?->format('d M Y H:i'),
                'expected_arrival' => $s->expected_arrival?->format('d M Y'),
                'estimated_arrival' => $s->estimated_arrival?->format('d M Y'),
                'origin' => [
                    'name' => $s->origin?->name ?? 'Origin Port',
                    'city' => $s->origin?->city ?? 'Origin',
                    'country' => $s->origin?->country ?? 'Origin',
                    'lat' => (float) ($s->origin?->latitude ?? 0),
                    'lng' => (float) ($s->origin?->longitude ?? 0),
                ],
                'destination' => [
                    'name' => $s->destination?->name ?? 'Destination Port',
                    'city' => $s->destination?->city ?? 'Destination',
                    'country' => $s->destination?->country ?? 'Destination',
                    'lat' => (float) ($s->destination?->latitude ?? 0),
                    'lng' => (float) ($s->destination?->longitude ?? 0),
                ],
                'current_position' => $currentPos,
                'waypoints' => $s->trackingEvents
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->map(fn($e) => [
                        'lat' => (float) $e->latitude,
                        'lng' => (float) $e->longitude,
                        'location' => $e->location_name,
                        'time' => $e->event_time?->format('Y-m-d H:i'),
                        'description' => $e->description,
                    ])->values()->all(),
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
                $mapShipments = $mapShipments->filter(fn($m) => !$m['is_signal_offline'] && $m['status'] !== 'DELIVERED')->values();
            }
        }

        $carriers = Carrier::where('is_active', true)->get();

        $allCollection = $shipments->map(fn($s) => $s->getCurrentPosition());
        $stats = [
            'total' => $shipments->count(),
            'in_transit' => $shipments->where('current_status', 'IN_TRANSIT')->count(),
            'delivered' => $shipments->where('current_status', 'DELIVERED')->count(),
            'west_sector' => $allCollection->where('is_west_sector', true)->count(),
            'signal_offline' => $allCollection->where('is_signal_offline', true)->count(),
            'signal_active' => $allCollection->where('signal_status', 'ACTIVE')->count(),
            'coffee' => $shipments->filter(fn($s) => $s->commodity_type === 'COFFEE')->count(),
            'cocoa' => $shipments->filter(fn($s) => $s->commodity_type === 'COCOA')->count(),
            'delayed' => $shipments->where('is_delayed', true)->count(),
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

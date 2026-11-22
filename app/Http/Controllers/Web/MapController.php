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

        $shipments = $query->latest()->get();

        // Map shipments to enriched geographic objects with exact live positions
        $mapShipments = $shipments->map(function (Shipment $s) {
            $currentPos = $s->getCurrentPosition();
            $comm = $s->commodity_type;

            return [
                'id' => $s->id,
                'number' => $s->shipment_number,
                'cargo_type' => $s->cargo_type,
                'commodity' => $comm,
                'status' => $s->current_status,
                'is_delayed' => (bool) $s->is_delayed,
                'delay_hours' => (float) $s->delay_hours,
                'carrier' => $s->carrier?->name ?? 'Maritime Express',
                'vessel_name' => $s->vessel_name ?? $s->vessel_carrier ?? 'MV Cargo Liner',
                'voyage_number' => $s->voyage_number ?? 'VOY-2026',
                'container_number' => $s->container_number ?? 'CTRU-882910',
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

        $carriers = Carrier::where('is_active', true)->get();

        $stats = [
            'total' => $mapShipments->count(),
            'in_transit' => $mapShipments->where('status', 'IN_TRANSIT')->count(),
            'coffee' => $mapShipments->where('commodity', 'COFFEE')->count(),
            'cocoa' => $mapShipments->where('commodity', 'COCOA')->count(),
            'delayed' => $mapShipments->where('is_delayed', true)->count(),
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

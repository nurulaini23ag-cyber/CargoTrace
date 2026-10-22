<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display live operational metrics, statistics, and charts.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Base query: scoped to customer if role is customer
        $shipmentsQuery = Shipment::query();
        $exceptionsQuery = ShipmentException::query();
        $eventsQuery = TrackingEvent::query();

        if ($user->isCustomer()) {
            $shipmentsQuery->where('customer_id', $user->id);
            $exceptionsQuery->whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
            $eventsQuery->whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }

        $totalShipments = (clone $shipmentsQuery)->count();
        $inTransitCount = (clone $shipmentsQuery)->where('current_status', Shipment::STATUS_IN_TRANSIT)->count();
        $deliveredCount = (clone $shipmentsQuery)->where('current_status', Shipment::STATUS_DELIVERED)->count();
        $delayedCount = (clone $shipmentsQuery)->where('is_delayed', true)->count();
        $openExceptionsCount = (clone $exceptionsQuery)->whereIn('status', ['OPEN', 'INVESTIGATING'])->count();

        // Calculate KPI percentages
        $delayRate = $totalShipments > 0 ? round(($delayedCount / $totalShipments) * 100, 1) : 0;
        $exceptionRate = $totalShipments > 0 ? round(($openExceptionsCount / $totalShipments) * 100, 1) : 0;
        $onTimeDeliveryRate = $deliveredCount > 0
            ? round(((clone $shipmentsQuery)->where('current_status', Shipment::STATUS_DELIVERED)->where('is_delayed', false)->count() / $deliveredCount) * 100, 1)
            : 100;

        // Breakdown by Status for Chart.js
        $statusCounts = (clone $shipmentsQuery)
            ->selectRaw('current_status, count(*) as count')
            ->groupBy('current_status')
            ->pluck('count', 'current_status')
            ->toArray();

        $allStatuses = [
            Shipment::STATUS_CREATED,
            Shipment::STATUS_BOOKED,
            Shipment::STATUS_PICKED_UP,
            Shipment::STATUS_IN_TRANSIT,
            Shipment::STATUS_DELIVERED,
            Shipment::STATUS_CANCELLED,
        ];
        $statusChartData = [];
        foreach ($allStatuses as $st) {
            $statusChartData[$st] = $statusCounts[$st] ?? 0;
        }

        // Breakdown by Carrier for Chart.js
        $carrierCounts = Carrier::withCount(['shipments' => function ($q) use ($user) {
            if ($user->isCustomer()) {
                $q->where('customer_id', $user->id);
            }
        }])
        ->get()
        ->filter(fn ($carrier) => $carrier->shipments_count > 0);

        $carrierChartLabels = $carrierCounts->pluck('name')->values()->toArray();
        $carrierChartData = $carrierCounts->pluck('shipments_count')->values()->toArray();

        // Recent tracking activity
        $recentEvents = (clone $eventsQuery)
            ->with(['shipment', 'location'])
            ->latest('event_time')
            ->take(5)
            ->get();

        // Critical / Open exceptions needing action
        $activeExceptions = (clone $exceptionsQuery)
            ->with(['shipment', 'logger'])
            ->whereIn('status', ['OPEN', 'INVESTIGATING'])
            ->latest('detected_at')
            ->take(5)
            ->get();

        // Recent shipments list
        $recentShipments = (clone $shipmentsQuery)
            ->with(['carrier', 'origin', 'destination', 'customer'])
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'delayRate',
            'exceptionRate',
            'onTimeDeliveryRate',
            'statusChartData',
            'carrierChartLabels',
            'carrierChartData',
            'recentEvents',
            'activeExceptions',
            'recentShipments'
        ));
    }
}

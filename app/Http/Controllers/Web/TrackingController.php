<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\ExternalApiService;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;
    protected ExternalApiService $externalApiService;

    public function __construct(TrackingService $trackingService, ExternalApiService $externalApiService)
    {
        $this->trackingService = $trackingService;
        $this->externalApiService = $externalApiService;
    }

    /**
     * Store new tracking event for a shipment (Admin & Operator).
     */
    public function store(StoreTrackingEventRequest $request, int $shipmentId): RedirectResponse
    {
        $shipment = Shipment::findOrFail($shipmentId);

        try {
            $this->trackingService->addEvent($shipment, $request->validated(), Auth::user());

            return back()->with('success', 'Tracking event logged and timeline updated successfully.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Public Cargo Tracking Portal (Search form).
     */
    public function publicTrack(Request $request): View
    {
        $trackingNumber = trim((string) $request->get('q', ''));
        $shipment = null;
        $currentPos = null;
        $weather = null;

        if (!empty($trackingNumber)) {
            $shipment = Shipment::with([
                'carrier',
                'origin',
                'destination',
                'items',
                'trackingEvents' => fn($q) => $q->orderBy('event_time', 'asc'),
            ])
            ->where('shipment_number', $trackingNumber)
            ->orWhere('tracking_number', $trackingNumber)
            ->first();

            if ($shipment) {
                $currentPos = $shipment->getCurrentPosition();
                $weather = $this->externalApiService->getWeather($currentPos['latitude'], $currentPos['longitude']);
            }
        }

        return view('tracking.public', compact('shipment', 'trackingNumber', 'currentPos', 'weather'));
    }

    /**
     * Direct link for tracking a specific shipment number.
     */
    public function trackNumber(string $number): View
    {
        $shipment = Shipment::with([
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents' => fn($q) => $q->orderBy('event_time', 'asc'),
        ])
        ->where('shipment_number', $number)
        ->orWhere('tracking_number', $number)
        ->first();

        $currentPos = null;
        $weather = null;
        $trackingNumber = $number;

        if ($shipment) {
            $currentPos = $shipment->getCurrentPosition();
            $weather = $this->externalApiService->getWeather($currentPos['latitude'], $currentPos['longitude']);
        }

        return view('tracking.public', compact('shipment', 'trackingNumber', 'currentPos', 'weather'));
    }
}

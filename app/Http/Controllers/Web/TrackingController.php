<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

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
}

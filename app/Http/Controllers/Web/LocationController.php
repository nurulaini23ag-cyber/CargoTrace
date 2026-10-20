<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Models\Location;
use App\Services\AuditService;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocationController extends Controller
{
    protected AuditService $auditService;
    protected ExternalApiService $externalApiService;

    public function __construct(AuditService $auditService, ExternalApiService $externalApiService)
    {
        $this->auditService = $auditService;
        $this->externalApiService = $externalApiService;
    }

    public function index(): View
    {
        $locations = Location::withCount(['outboundShipments', 'inboundShipments'])->latest()->paginate(15);
        return view('locations.index', compact('locations'));
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $location = Location::create($request->validated());

        $this->auditService->log(
            'LOCATION_CREATED',
            'Location',
            $location->id,
            "Location {$location->name} ({$location->code}) created via web.",
            null,
            $location->toArray(),
            Auth::id()
        );

        return back()->with('success', "Location {$location->name} added successfully.");
    }

    /**
     * API lookup to search geocode for a location.
     */
    public function geocodeLookup(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        return response()->json($this->externalApiService->geocode($query));
    }
}

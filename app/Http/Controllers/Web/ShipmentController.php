<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ExternalApiService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;
    protected TrackingService $trackingService;
    protected ExternalApiService $externalApiService;

    public function __construct(
        ShipmentService $shipmentService,
        TrackingService $trackingService,
        ExternalApiService $externalApiService
    ) {
        $this->shipmentService = $shipmentService;
        $this->trackingService = $trackingService;
        $this->externalApiService = $externalApiService;
    }

    /**
     * Display list of shipments with multi-attribute search and filters.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination', 'latestTrackingEvent']);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->origin_id);
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        if ($request->filled('is_delayed')) {
            $isDelayed = $request->is_delayed === 'yes';
            $query->where('is_delayed', $isDelayed);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expected_arrival', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expected_arrival', '<=', $request->date_to);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['created_at', 'expected_arrival', 'shipment_number', 'weight', 'current_status'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $shipments = $query->paginate(10)->withQueryString();

        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::all();

        return view('shipments.index', compact('shipments', 'carriers', 'locations'));
    }

    /**
     * Show form to create new shipment (Admin/Operator).
     */
    public function create(): View
    {
        $this->authorizeAccess();

        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::all();
        $customers = User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get();

        return view('shipments.create', compact('carriers', 'locations', 'customers'));
    }

    /**
     * Store new shipment.
     */
    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        try {
            $shipment = $this->shipmentService->createShipment($request->validated(), Auth::user());

            return redirect()->route('shipments.show', $shipment->id)
                ->with('success', "Shipment #{$shipment->shipment_number} created successfully.");
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show shipment detail, Leaflet map, tracking timeline, and exceptions.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $shipment = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator',
            'exceptions.logger',
            'exceptions.resolver',
        ])->findOrFail($id);

        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            abort(403, 'Unauthorized access: You can only view your own shipments.');
        }

        // Fetch live weather context for current position / destination
        $targetLat = $shipment->latestTrackingEvent?->latitude ?? $shipment->destination->latitude;
        $targetLon = $shipment->latestTrackingEvent?->longitude ?? $shipment->destination->longitude;
        $weather = $this->externalApiService->getWeather($targetLat, $targetLon);

        $locations = Location::all();

        return view('shipments.show', compact('shipment', 'weather', 'locations'));
    }

    /**
     * Show edit form for shipment (Admin/Operator).
     */
    public function edit(int $id): View
    {
        $this->authorizeAccess();

        $shipment = Shipment::with('items')->findOrFail($id);
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::all();
        $customers = User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get();

        return view('shipments.edit', compact('shipment', 'carriers', 'locations', 'customers'));
    }

    /**
     * Update shipment details.
     */
    public function update(UpdateShipmentRequest $request, int $id): RedirectResponse
    {
        $shipment = Shipment::findOrFail($id);

        try {
            $this->shipmentService->updateShipment($shipment, $request->validated(), Auth::user());

            return redirect()->route('shipments.show', $shipment->id)
                ->with('success', "Shipment #{$shipment->shipment_number} updated successfully.");
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Quick transition endpoint for action buttons in UI.
     */
    public function transition(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAccess();

        $request->validate([
            'target_status' => ['required', 'string'],
            'transition_note' => ['nullable', 'string', 'max:500'],
        ]);

        $shipment = Shipment::findOrFail($id);

        try {
            $this->shipmentService->transitionStatus(
                $shipment,
                $request->target_status,
                $request->transition_note,
                Auth::user()
            );

            return back()->with('success', "Status transitioned to {$request->target_status}.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Soft delete shipment (Admin only).
     */
    public function destroy(int $id): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete shipments.');
        }

        $shipment = Shipment::findOrFail($id);
        $this->shipmentService->deleteShipment($shipment, Auth::user());

        return redirect()->route('shipments.index')
            ->with('success', "Shipment #{$shipment->shipment_number} was soft-deleted.");
    }

    protected function authorizeAccess(): void
    {
        if (!Auth::user() || (!Auth::user()->isAdmin() && !Auth::user()->isOperator())) {
            abort(403, 'Unauthorized access.');
        }
    }
}

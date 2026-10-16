<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExceptionRequest;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Services\ExceptionService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;
    protected TrackingService $trackingService;
    protected ExceptionService $exceptionService;

    public function __construct(
        ShipmentService $shipmentService,
        TrackingService $trackingService,
        ExceptionService $exceptionService
    ) {
        $this->shipmentService = $shipmentService;
        $this->trackingService = $trackingService;
        $this->exceptionService = $exceptionService;
    }

    /**
     * List shipments with server-side search, filtering, and pagination.
     * Enforces customer role scoping (customers can only view their own).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Shipment::with(['customer:id,name,email,company_name', 'carrier:id,code,name,mode', 'origin:id,code,name,city,country', 'destination:id,code,name,city,country']);

        // Scope to customer if not admin or operator
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Search by shipment number or customer
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        // Filter by carrier
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        // Filter by origin
        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->origin_id);
        }

        // Filter by destination
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        // Filter by delay status
        if ($request->filled('is_delayed')) {
            $query->where('is_delayed', filter_var($request->is_delayed, FILTER_VALIDATE_BOOLEAN));
        }

        // Date range filter on expected_arrival
        if ($request->filled('date_from')) {
            $query->whereDate('expected_arrival', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expected_arrival', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['created_at', 'expected_arrival', 'estimated_arrival', 'shipment_number', 'weight', 'current_status'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $perPage = min(max((int) $request->get('per_page', 10), 1), 100);
        $shipments = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $shipments->items(),
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage(),
                'total' => $shipments->total(),
            ],
        ], 200);
    }

    /**
     * Create a new shipment (Admin/Operator only).
     */
    public function store(StoreShipmentRequest $request): JsonResponse
    {
        try {
            $shipment = $this->shipmentService->createShipment($request->validated(), $request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment created successfully.',
                'data' => $shipment,
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get single shipment details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::with([
            'customer:id,name,email,company_name,phone',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'exceptions',
        ])->find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        // Customer authorization
        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You can only view your own shipments.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment,
        ], 200);
    }

    /**
     * Update shipment details (Admin/Operator only).
     */
    public function update(UpdateShipmentRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        try {
            $updated = $this->shipmentService->updateShipment($shipment, $request->validated(), $request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment updated successfully.',
                'data' => $updated,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Soft delete shipment (Admin only).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Only administrators can delete shipments.',
            ], 403);
        }

        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        $this->shipmentService->deleteShipment($shipment, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment deleted successfully.',
        ], 200);
    }

    /**
     * Get tracking history / timeline for a shipment.
     */
    public function tracking(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You can only view tracking for your own shipments.',
            ], 403);
        }

        $timeline = $this->trackingService->getTimeline($shipment);

        return response()->json([
            'status' => 'success',
            'data' => $timeline,
        ], 200);
    }

    /**
     * Add tracking event to a shipment (Admin/Operator only).
     */
    public function storeTracking(StoreTrackingEventRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        try {
            $event = $this->trackingService->addEvent($shipment, $request->validated(), $request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Tracking event appended successfully.',
                'data' => $event->load(['location', 'creator']),
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get exceptions for a shipment.
     */
    public function exceptions(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You can only view exceptions for your own shipments.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment->exceptions()->with(['logger:id,name', 'resolver:id,name'])->get(),
        ], 200);
    }

    /**
     * Log a new exception for a shipment (Admin/Operator only).
     */
    public function storeException(StoreExceptionRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment not found.',
            ], 404);
        }

        $exception = $this->exceptionService->logException($shipment, $request->validated(), $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment exception recorded successfully.',
            'data' => $exception->load(['logger', 'shipment']),
        ], 201);
    }
}

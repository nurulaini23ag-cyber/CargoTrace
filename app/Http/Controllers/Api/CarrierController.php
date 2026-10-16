<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrierRequest;
use App\Models\Carrier;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request): JsonResponse
    {
        $carriers = Carrier::where('is_active', true)
            ->withCount('shipments')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $carriers,
        ], 200);
    }

    public function store(StoreCarrierRequest $request): JsonResponse
    {
        $carrier = Carrier::create($request->validated());

        $this->auditService->log(
            'CARRIER_CREATED',
            'Carrier',
            $carrier->id,
            "Carrier {$carrier->name} ({$carrier->code}) created.",
            null,
            $carrier->toArray(),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Carrier created successfully.',
            'data' => $carrier,
        ], 201);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Models\Location;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request): JsonResponse
    {
        $locations = Location::orderBy('name', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations,
        ], 200);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = Location::create($request->validated());

        $this->auditService->log(
            'LOCATION_CREATED',
            'Location',
            $location->id,
            "Location {$location->name} ({$location->code}) created.",
            null,
            $location->toArray(),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Location created successfully.',
            'data' => $location,
        ], 201);
    }
}

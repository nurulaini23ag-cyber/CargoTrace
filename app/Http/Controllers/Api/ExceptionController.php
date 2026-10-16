<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateExceptionRequest;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\JsonResponse;

class ExceptionController extends Controller
{
    protected ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    /**
     * Update exception status (e.g., INVESTIGATING, RESOLVED).
     */
    public function update(UpdateExceptionRequest $request, int $id): JsonResponse
    {
        $exception = ShipmentException::with('shipment')->find($id);

        if (!$exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shipment exception not found.',
            ], 404);
        }

        $validated = $request->validated();
        $updated = $this->exceptionService->updateStatus(
            $exception,
            $validated['status'],
            $validated['resolution_notes'] ?? null,
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Exception status updated successfully.',
            'data' => $updated->load(['logger:id,name', 'resolver:id,name', 'shipment']),
        ], 200);
    }
}

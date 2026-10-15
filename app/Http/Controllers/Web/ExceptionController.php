<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExceptionRequest;
use App\Http\Requests\UpdateExceptionRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ExceptionController extends Controller
{
    protected ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    public function store(StoreExceptionRequest $request, int $shipmentId): RedirectResponse
    {
        $shipment = Shipment::findOrFail($shipmentId);

        $this->exceptionService->logException($shipment, $request->validated(), Auth::user());

        return back()->with('warning', 'Exception incident logged and alert dispatched to customer.');
    }

    public function update(UpdateExceptionRequest $request, int $id): RedirectResponse
    {
        $exception = ShipmentException::findOrFail($id);

        $validated = $request->validated();
        $this->exceptionService->updateStatus(
            $exception,
            $validated['status'],
            $validated['resolution_notes'] ?? null,
            Auth::user()
        );

        return back()->with('success', "Exception #{$exception->id} status updated to {$validated['status']}.");
    }
}

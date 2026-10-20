<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrierRequest;
use App\Models\Carrier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CarrierController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(): View
    {
        $carriers = Carrier::withCount('shipments')->latest()->paginate(15);
        return view('carriers.index', compact('carriers'));
    }

    public function store(StoreCarrierRequest $request): RedirectResponse
    {
        $carrier = Carrier::create($request->validated());

        $this->auditService->log(
            'CARRIER_CREATED',
            'Carrier',
            $carrier->id,
            "Carrier {$carrier->name} ({$carrier->code}) created via web.",
            null,
            $carrier->toArray(),
            Auth::id()
        );

        return back()->with('success', "Carrier {$carrier->name} added successfully.");
    }

    public function toggle(int $id): RedirectResponse
    {
        $carrier = Carrier::findOrFail($id);
        $carrier->is_active = !$carrier->is_active;
        $carrier->save();

        return back()->with('info', "Carrier {$carrier->name} status toggled.");
    }
}

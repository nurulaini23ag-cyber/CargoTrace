<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\ExceptionController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ShipmentController;
use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CargoTrace REST API Routes (Slide 12 & 13)
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Endpoints (Laravel Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Current User & Logout
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Shipments (All authenticated users can list/show; customer is query-scoped)
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']);
    Route::get('/shipments/{id}/tracking', [ShipmentController::class, 'tracking']);
    Route::get('/shipments/{id}/exceptions', [ShipmentController::class, 'exceptions']);

    // Shipments management (Admin & Operator only)
    Route::middleware('role:admin,operator')->group(function () {
        Route::post('/shipments', [ShipmentController::class, 'store']);
        Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentController::class, 'update']);
        Route::post('/shipments/{id}/tracking', [ShipmentController::class, 'storeTracking']);
        Route::post('/shipments/{id}/exceptions', [ShipmentController::class, 'storeException']);
        Route::patch('/exceptions/{id}', [ExceptionController::class, 'update']);
    });

    // Shipment deletion (Admin only)
    Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy'])->middleware('role:admin');

    // Carriers
    Route::get('/carriers', [CarrierController::class, 'index']);
    Route::post('/carriers', [CarrierController::class, 'store'])->middleware('role:admin');

    // Locations
    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locations', [LocationController::class, 'store'])->middleware('role:admin');

    // External Services Endpoints (Open-Meteo & Nominatim)
    Route::get('/external/weather', function (Request $request, ExternalApiService $externalApi) {
        $lat = (float) $request->get('latitude', 0);
        $lon = (float) $request->get('longitude', 0);
        return response()->json($externalApi->getWeather($lat, $lon));
    });

    Route::get('/external/geocode', function (Request $request, ExternalApiService $externalApi) {
        $query = (string) $request->get('q', '');
        return response()->json($externalApi->geocode($query));
    });
});

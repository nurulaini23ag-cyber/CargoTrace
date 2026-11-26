<?php

use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CarrierController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExceptionController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\MapController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\ShipmentController;
use App\Http\Controllers\Web\SystemSettingController;
use App\Http\Controllers\Web\TrackingController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CargoTrace Web Application
|--------------------------------------------------------------------------
*/

// Public Landing, Tracking, Commodity Catalog & Live Global Fleet Map
Route::get('/', function () {
    if (Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::get('/track', [TrackingController::class, 'publicTrack'])->name('tracking.public');
Route::get('/track/{number}', [TrackingController::class, 'trackNumber'])->name('tracking.search');
Route::get('/map', [MapController::class, 'index'])->name('map.index');
Route::get('/api/map/shipments', [MapController::class, 'shipmentsData'])->name('map.data');

// Commodity Catalog (Publicly browseable)
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments (All authenticated users can browse and view authorized shipments)
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');

    // Customer Commodity Orders & Trade Transactions
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // Payment Gateway Simulator & Commercial Invoice
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // In-app Notifications & Live Market/Weather Feeds
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/live-feeds', [NotificationController::class, 'liveFeeds'])->name('notifications.liveFeeds');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Operational Management (Admin & Operator)
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/shipment/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/{id}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentController::class, 'update'])->name('shipments.update');
        Route::post('/shipments/{id}/transition', [ShipmentController::class, 'transition'])->name('shipments.transition');
        Route::post('/shipments/{id}/tracking', [TrackingController::class, 'store'])->name('shipments.tracking.store');
        Route::post('/shipments/{id}/exceptions', [ExceptionController::class, 'store'])->name('shipments.exceptions.store');
        Route::patch('/exceptions/{id}', [ExceptionController::class, 'update'])->name('exceptions.update');

        // Locations & Ports Directory (Admin & Operator)
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
        Route::get('/locations/geocode-lookup', [LocationController::class, 'geocodeLookup'])->name('locations.geocode');
        Route::get('/locations/reverse-geocode', [LocationController::class, 'reverseGeocode'])->name('locations.reverseGeocode');
    });

    // Administrator Only Routes
    Route::middleware('role:admin')->group(function () {
        Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

        // Carriers
        Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index');
        Route::post('/carriers', [CarrierController::class, 'store'])->name('carriers.store');
        Route::post('/carriers/{id}/toggle', [CarrierController::class, 'toggle'])->name('carriers.toggle');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{id}/role', [UserController::class, 'updateRole'])->name('users.role');

        // System Settings & Group Scenario Rules
        Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/preset/{group}', [SystemSettingController::class, 'applyPreset'])->name('settings.preset');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
    });
});

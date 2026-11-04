<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $dbStatus = 'healthy';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'unhealthy';
        }

        return response()->json([
            'status' => $dbStatus === 'healthy' ? 'operational' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'platform' => 'CargoTrace Visibility Engine',
            'version' => '1.0.0',
            'services' => [
                'database' => $dbStatus,
                'cache' => 'healthy',
                'storage' => is_writable(storage_path()) ? 'healthy' : 'degraded',
            ],
        ], $dbStatus === 'healthy' ? 200 : 503);
    }
}

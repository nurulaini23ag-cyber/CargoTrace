<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_endpoint_returns_operational(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'operational',
            'platform' => 'CargoTrace Visibility Engine',
            'version' => '1.0.0',
            'services' => [
                'database' => 'healthy',
                'cache' => 'healthy',
            ],
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LiveNewsAndMarketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveNewsAndMarketNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
        ]);

        // Mock external APIs for reliable testing
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'time_last_update_utc' => now()->toRfc2822String(),
                'rates' => [
                    'USD' => 1.0,
                    'IDR' => 16325.50,
                    'SGD' => 1.3485,
                    'BRL' => 5.6720,
                    'EUR' => 0.9230,
                    'GHS' => 15.65,
                    'VND' => 25480.0,
                ],
            ], 200),
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 25.5,
                    'relative_humidity_2m' => 78,
                    'wind_speed_10m' => 14.2,
                    'weather_code' => 1,
                ],
            ], 200),
        ]);
    }

    public function test_notifications_page_displays_live_currency_exchange_rates(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Kurs Valuta Asing Real-Time');
        $response->assertSee('Dolar Singapura');
        $response->assertSee('Rupiah Indonesia');
        $response->assertSee('Real Brasil');
        $response->assertSee('Euro');
        $response->assertSee('Perbarui Real-Time');
    }

    public function test_notifications_page_displays_global_port_weather_bulletins(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Port of Singapore');
        $response->assertSee('Port of Santos');
        $response->assertSee('Port of Rotterdam');
        $response->assertSee('Cuaca Maritim Global');
        $response->assertSee('Open-Meteo');
    }

    public function test_notifications_live_feeds_json_endpoint_returns_real_time_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/notifications/live-feeds?refresh=1');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'fx' => [
                    'rates' => [
                        'IDR',
                        'SGD',
                        'BRL',
                        'EUR',
                    ],
                ],
                'weather_ports',
                'news_items',
                'generated_at',
            ],
        ]);
    }

    public function test_notifications_page_renders_weather_visual_themes_and_animations(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Radar Visual Cuaca Pelabuhan Maritim Dunia');
        $response->assertSee('Awan Petir');
        $response->assertSee('Awan Mendung');
        $response->assertSee('anim-lightning');
        $response->assertSee('anim-cloud-1');
        $response->assertSee('anim-rain-1');
        $response->assertSee('anim-sun-core');
    }

    public function test_live_news_service_handles_api_failure_gracefully_with_fallbacks(): void
    {
        // Fake complete network failure
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $service = new LiveNewsAndMarketService();
        $feed = $service->getAggregatedLiveNewsFeed(true);

        $this->assertIsArray($feed);
        $this->assertNotEmpty($feed['fx']['rates']);
        $this->assertArrayHasKey('IDR', $feed['fx']['rates']);
        $this->assertArrayHasKey('SGD', $feed['fx']['rates']);
        $this->assertArrayHasKey('BRL', $feed['fx']['rates']);
        $this->assertNotEmpty($feed['weather_ports']);
        $this->assertNotEmpty($feed['news_items']);
    }
}

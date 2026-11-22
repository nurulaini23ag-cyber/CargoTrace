<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_editorial_landing_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('CARGOTRACE');
        $response->assertSee('Connecting Global');
        $response->assertSee('Coffee & Cocoa', false);
        $response->assertSee('COCOA LOGISTICS');
        $response->assertSee('COFFEE LOGISTICS');
        $response->assertSee('admin@cargotrace.com');
    }

    public function test_authenticated_user_visiting_home_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/dashboard');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('CargoTrace Platform');
    }

    public function test_user_can_login_via_web(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@test.com',
            'password' => bcrypt('secret123'),
            'role' => User::ROLE_OPERATOR,
        ]);

        $response = $this->post('/login', [
            'email' => 'operator@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@test.com',
            'password' => bcrypt('correctpass'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'customer@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_protected_web_routes(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseShipments = $this->get('/shipments');
        $responseShipments->assertRedirect('/login');
    }

    public function test_api_login_issues_valid_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api_user@test.com',
            'password' => bcrypt('tokenpass123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api_user@test.com',
            'password' => 'tokenpass123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'token_type',
            ],
        ]);
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'randompass',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid credentials provided.',
        ]);
    }

    public function test_api_register_creates_customer_and_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice Global Exporter',
            'email' => 'alice@exporter.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Alice Export Co.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.user.email', 'alice@exporter.test');
        $response->assertJsonPath('data.user.role', 'customer');
        $this->assertDatabaseHas('users', ['email' => 'alice@exporter.test']);
    }

    public function test_api_logout_revokes_token(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->fresh()->tokens);
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestApiArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = app(JwtService::class);
    }

    /** @test */
    public function it_can_register_a_candidate_and_issue_jwt_tokens(): void
    {
        $response = $this->postJson(route('api.v1.register'), [
            'name'                  => 'Test Candidate',
            'email'                 => 'candidate@example.com',
            'phone'                 => '9876543210',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'deviceName'            => 'iPhone 15',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'accessToken',
                'refreshToken',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'role',
                    'isActive',
                    'createdAt',
                ]
            ]
        ]);

        $this->assertTrue($response['success']);
        $this->assertDatabaseHas('users', ['email' => 'candidate@example.com']);
        $this->assertDatabaseHas('personal_refresh_tokens', ['device_name' => 'iPhone 15']);
    }

    /** @test */
    public function it_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name'      => 'Test User',
            'email'     => 'test@example.com',
            'phone'     => '9998887776',
            'password'  => Hash::make('password123'),
            'role'      => 'candidate',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('api.v1.login'), [
            'email'      => 'test@example.com',
            'password'   => 'password123',
            'deviceName' => 'Android Client',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['accessToken', 'refreshToken', 'user']
            ]);
    }

    /** @test */
    public function it_rejects_login_with_invalid_credentials(): void
    {
        $response = $this->postJson(route('api.v1.login'), [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    /** @test */
    public function it_authenticates_protected_endpoints_using_valid_jwt(): void
    {
        $user = User::create([
            'name'      => 'Authorized User',
            'email'     => 'auth@example.com',
            'phone'     => '9998887775',
            'password'  => Hash::make('password123'),
            'role'      => 'candidate',
            'is_active' => true,
        ]);

        $token = $this->jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson(route('api.v1.dashboard.data'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'auth@example.com');
    }

    /** @test */
    public function it_rejects_protected_endpoints_with_invalid_or_expired_jwt(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid.token.here')
            ->getJson(route('api.v1.dashboard.data'));

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized or invalid token.');
    }

    /** @test */
    public function it_can_rotate_refresh_tokens(): void
    {
        $user = User::create([
            'name'      => 'Refresh User',
            'email'     => 'refresh@example.com',
            'phone'     => '9998887774',
            'password'  => Hash::make('password123'),
            'role'      => 'candidate',
            'is_active' => true,
        ]);

        $refreshToken = $this->jwtService->generateRefreshToken($user, 'Test Device');

        $response = $this->postJson(route('api.v1.refresh'), [
            'refreshToken' => $refreshToken,
            'deviceName'   => 'Test Device Rotated',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['accessToken', 'refreshToken']
            ]);

        $this->assertDatabaseMissing('personal_refresh_tokens', [
            'token' => hash('sha256', $refreshToken)
        ]);
    }
}

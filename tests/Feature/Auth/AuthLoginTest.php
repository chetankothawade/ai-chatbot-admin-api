<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'active.user@example.com',
            'password' => Hash::make('Password@123'),
            'status' => UserStatus::ACTIVE->value,
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertTrue((bool) $response->json('status'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive.user@example.com',
            'password' => Hash::make('Password@123'),
            'status' => UserStatus::INACTIVE->value,
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $response->assertStatus(403);
        $this->assertFalse((bool) $response->json('status'));
    }

    public function test_login_with_invalid_credentials_returns_unauthorized(): void
    {
        User::factory()->create([
            'email' => 'known.user@example.com',
            'password' => Hash::make('Password@123'),
            'status' => UserStatus::ACTIVE->value,
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'known.user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $this->assertFalse((bool) $response->json('status'));
    }
}

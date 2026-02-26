<?php

namespace Tests\Feature\Api\ActivityLog;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActivityLogModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_logs_index_requires_authentication(): void
    {
        $this->getJson('/api/activity-logs')->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_activity_logs_index(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        activity()
            ->causedBy($user)
            ->event('login')
            ->log('User login');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/activity-logs');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('pagination.total', 1);
    }
}

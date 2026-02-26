<?php

namespace Tests\Feature\Api\Permissions;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_menu_requires_authentication(): void
    {
        $this->getJson('/api/user-permissions/side-menu')->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_user_module_access(): void
    {
        $authUser = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $targetUser = User::factory()->create([
            'role' => UserRole::USER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        Sanctum::actingAs($authUser);

        $response = $this->getJson("/api/user-permissions/{$targetUser->uuid}/module-access");

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['roleModules', 'permissions'],
            ]);
    }
}

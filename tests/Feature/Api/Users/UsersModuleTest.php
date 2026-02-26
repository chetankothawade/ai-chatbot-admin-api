<?php

namespace Tests\Feature\Api\Users;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_get_list_requires_authentication(): void
    {
        $this->getJson('/api/users/getList')->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_users_list(): void
    {
        $authUser = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $listedUser = User::factory()->create([
            'name' => 'Listed User',
            'role' => UserRole::USER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        Sanctum::actingAs($authUser);

        $response = $this->getJson('/api/users/getList');

        $response->assertOk()
            ->assertJsonPath('status', true);

        $this->assertNotEmpty($response->json('data'));
        $this->assertSame($listedUser->uuid, $response->json('data.0.uuid'));
    }
}

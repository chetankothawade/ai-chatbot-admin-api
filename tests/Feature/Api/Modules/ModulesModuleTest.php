<?php

namespace Tests\Feature\Api\Modules;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\ActiveInactiveStatus;
use App\Enums\YesNoFlag;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModulesModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_modules_index_requires_authentication(): void
    {
        $this->getJson('/api/modules')->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_modules_index(): void
    {
        $authUser = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        Module::create([
            'parent_id' => null,
            'name' => 'Dashboard',
            'url' => '/dashboard',
            'icon' => 'home',
            'seq_no' => 1,
            'is_sub_module' => YesNoFlag::NO->value,
            'is_permission' => YesNoFlag::NO->value,
            'status' => ActiveInactiveStatus::ACTIVE->value,
        ]);

        Sanctum::actingAs($authUser);

        $response = $this->getJson('/api/modules');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('pagination.total', 1);
    }
}

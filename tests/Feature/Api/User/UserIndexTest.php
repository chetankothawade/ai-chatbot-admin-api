<?php

namespace Tests\Feature\Api\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_requires_authentication(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_users_index_returns_paginated_users_and_applies_default_exclusions(): void
    {
        $authUser = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        Sanctum::actingAs($authUser);

        User::factory()->create([
            'name' => 'Visible User',
            'email' => 'visible.user@example.com',
            'role' => UserRole::USER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        User::factory()->create([
            'name' => 'Hidden Super',
            'email' => 'super.hidden@example.com',
            'role' => UserRole::SUPER_ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'pagination' => [
                    'total',
                    'perPage',
                    'currentPage',
                    'lastPage',
                    'from',
                    'to',
                ],
            ]);

        $responseData = $response->json('data');

        $this->assertCount(1, $responseData);
        $this->assertSame('visible.user@example.com', $responseData[0]['email']);
    }
}

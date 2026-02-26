<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_hashes_password_and_sets_login_ip(): void
    {
        $service = new UserService();

        $user = $service->createUser([
            'name' => 'Service User',
            'email' => 'service.user@example.com',
            'phone' => '9999999999',
            'password' => 'Password@123',
            'status' => UserStatus::ACTIVE->value,
        ], '127.0.0.1');

        $this->assertSame('Service User', $user->name);
        $this->assertSame('127.0.0.1', $user->last_login_ip);
        $this->assertTrue(Hash::check('Password@123', $user->password));
    }

    public function test_get_paginated_users_excludes_authenticated_and_super_admin_and_supports_search(): void
    {
        $service = new UserService();

        $authUser = User::factory()->create([
            'email' => 'auth.user@example.com',
            'role' => UserRole::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        Auth::login($authUser);

        User::factory()->create([
            'name' => 'Search Match User',
            'email' => 'search.match@example.com',
            'role' => UserRole::USER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        User::factory()->create([
            'name' => 'Search Match Super',
            'email' => 'search.super@example.com',
            'role' => UserRole::SUPER_ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        User::factory()->create([
            'name' => 'Other User',
            'email' => 'other.user@example.com',
            'role' => UserRole::USER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $result = $service->getPaginatedUsers([
            'search' => 'Search Match',
            'sortedField' => 'id',
            'sortedBy' => 'asc',
            'perPage' => 10,
        ]);

        $this->assertSame(1, $result->total());
        $this->assertSame('search.match@example.com', $result->items()[0]->email);
    }
}

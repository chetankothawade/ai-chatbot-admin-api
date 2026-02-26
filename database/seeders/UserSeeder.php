<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed users with a common password: Password@123
     */
    public function run(): void
    {
        $password = Hash::make('Password@123');

        $this->upsertUser('superadmin@yopmail.com', [
            'name' => 'Super Admin',
            'phone' => '9999999999',
            'password' => $password,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'role' => UserRole::SUPER_ADMIN->value,
            'last_login_at' => now(),
            'last_login_ip' => '127.0.0.1',
            'last_login_ua' => 'Seeder',
        ]);

        $this->upsertUser('admin@yopmail.com', [
            'name' => 'Admin User',
            'phone' => '8888888888',
            'password' => $password,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'role' => UserRole::ADMIN->value,
            'last_login_at' => now(),
            'last_login_ip' => '127.0.0.1',
            'last_login_ua' => 'Seeder',
        ]);

        $this->upsertUser('user@yopmail.com', [
            'name' => 'App User',
            'phone' => '7777777777',
            'password' => $password,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'role' => UserRole::USER->value,
            'last_login_at' => now(),
            'last_login_ip' => '127.0.0.1',
            'last_login_ua' => 'Seeder',
        ]);

        // Generate 20 random users
        User::factory()->count(count: 20)->create();

        User::whereNull('uuid')->each(function (User $user) {
            $user->uuid = (string) Str::uuid();
            $user->save();
        });
    }

    private function upsertUser(string $email, array $attributes): void
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->fill($attributes);

        if (empty($user->uuid)) {
            $user->uuid = (string) Str::uuid();
        }

        $user->save();
    }
}

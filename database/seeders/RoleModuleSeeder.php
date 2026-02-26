<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $modules = DB::table('modules')->pluck('id');

        $insert = [];

        foreach ($modules as $moduleId) {
            $insert[] = [
                'role' => UserRole::SUPER_ADMIN->value,
                'module_id' => $moduleId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // prevent empty upsert
        if (!empty($insert)) {
            DB::table('role_modules')->upsert(
                $insert,
                ['role', 'module_id'],
                ['updated_at']
            );
        }
    }
}

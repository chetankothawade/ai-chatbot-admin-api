<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = DB::table('modules')->get();
        $permissions = DB::table('permissions')->get();

        foreach ($modules as $module) {

            foreach ($permissions as $permission) {

                // Prevent duplicate insert (unique index module_id + permission_id)
                $exists = DB::table('module_permissions')
                    ->where('module_id', $module->id)
                    ->where('permission_id', $permission->id)
                    ->exists();

                if (! $exists) {
                    DB::table('module_permissions')->insert([
                        'module_id'     => $module->id,
                        'permission_id' => $permission->id,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                    ]);
                }
            }
        }
    }
}

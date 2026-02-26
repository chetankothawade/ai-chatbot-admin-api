<?php

namespace Database\Seeders;

use App\Enums\ActiveInactiveStatus;
use App\Enums\PermissionAction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions')->insert([
            [
                'action'     => PermissionAction::VIEW->value,
                'status'     => ActiveInactiveStatus::ACTIVE->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action'     => PermissionAction::CREATE->value,
                'status'     => ActiveInactiveStatus::ACTIVE->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action'     => PermissionAction::EDIT->value,
                'status'     => ActiveInactiveStatus::ACTIVE->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action'     => PermissionAction::DELETE->value,
                'status'     => ActiveInactiveStatus::ACTIVE->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action'     => PermissionAction::STATUS->value,
                'status'     => ActiveInactiveStatus::ACTIVE->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

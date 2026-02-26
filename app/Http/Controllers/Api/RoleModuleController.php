<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ActiveInactiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RoleModule\RoleModuleToggleRequest;
use App\Http\Resources\RoleModuleMatrixResource;
use App\Models\Module;
use App\Models\RoleModule;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RoleModuleController extends Controller
{
    use ApiResponse;

    public function matrix(): JsonResponse
    {
        $roles = RoleModule::roles();

        $modules = Module::where('status', ActiveInactiveStatus::ACTIVE->value)->get();

        $roleModules = RoleModule::all()
            ->groupBy('module_id')
            ->map(fn($items) => $items->pluck('role')->toArray());

        $matrix = $modules->map(function ($module) use ($roles, $roleModules) {
            $permissions = [];

            foreach ($roles as $role) {
                $permissions[$role] = in_array(
                    $role,
                    $roleModules[$module->id] ?? []
                );
            }

            return [
                'id' => $module->id,
                'name' => $module->name,
                'permissions' => $permissions
            ];
        });

        return $this->success('messages.role_module_matrix_success', new RoleModuleMatrixResource([
            'roles' => $roles,
            'modules' => $matrix,
        ]));
    }

    public function toggle(RoleModuleToggleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($validated['enabled']) {
            RoleModule::updateOrCreate([
                'role' => $validated['role'],
                'module_id' => $validated['module_id'],
            ]);
        } else {
            RoleModule::where([
                'role' => $validated['role'],
                'module_id' => $validated['module_id'],
            ])->delete();
        }

        return $this->success('messages.role_module_updated', ['success' => true]);
    }
}

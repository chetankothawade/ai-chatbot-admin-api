<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActiveInactiveStatus;
use App\Enums\PermissionAction;
use App\Enums\YesNoFlag;
use App\Models\Module;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;

class UserPermissionService
{
    /**
     * Assign or revoke a user permission (with transaction).
     */
    public function toggle(int $userId, int $modulePermissionId, bool $isChecked): UserPermission|int
    {
        return DB::transaction(function () use ($userId, $modulePermissionId, $isChecked) {
            if ($isChecked) {
                return UserPermission::firstOrCreate([
                    'user_id' => $userId,
                    'module_permission_id' => $modulePermissionId,
                ]);
            }

            return UserPermission::where([
                'user_id' => $userId,
                'module_permission_id' => $modulePermissionId,
            ])->delete();
        });
    }

    /**
     * Build full module -> permissions -> userPermissions matrix.
     */
    public function getModulePermissionMatrix(int $userId, string $isPermission = YesNoFlag::NO->value): array
    {
        $user = DB::table('users')->select('id', 'role')->where('id', $userId)->first();

        $query = DB::table('modules as m')
            ->join('role_modules as rm', function ($join) use ($user) {
                $join->on('rm.module_id', '=', 'm.id')
                    ->where('rm.role', '=', $user->role);
            })
            ->crossJoin('permissions as p')
            ->leftJoin('module_permissions as mp', function ($join) {
                $join->on('mp.module_id', '=', 'm.id')
                    ->on('mp.permission_id', '=', 'p.id');
            })
            ->leftJoin('user_permissions as up', function ($join) use ($userId) {
                $join->on('up.module_permission_id', '=', 'mp.id')
                    ->where('up.user_id', '=', $userId);
            })
            ->where('m.status', ActiveInactiveStatus::ACTIVE->value)
            ->where('p.status', ActiveInactiveStatus::ACTIVE->value);

        if ($isPermission === YesNoFlag::YES->value) {
            $query->where('m.is_permission', YesNoFlag::YES->value);
        }

        $rows = $query
            ->select([
                'm.id as module_id',
                'm.name as module_name',
                'm.parent_id',
                'm.url',
                'm.icon',
                'm.seq_no',
                'm.is_sub_module',
                'm.is_permission',
                'p.id as permission_id',
                'p.action as permission_action',
                'mp.id as module_permission_id',
                'up.module_permission_id as user_has_permission',
            ])
            ->orderBy('m.seq_no')
            ->orderBy('m.id')
            ->orderBy('p.id')
            ->get();

        $modules = [];
        $userPermissions = [];
        $parentNames = DB::table('modules')->pluck('name', 'id');

        foreach ($rows as $row) {
            if (! isset($modules[$row->module_id])) {
                $displayName = empty($row->parent_id)
                    ? $row->module_name
                    : ($parentNames[$row->parent_id] ?? 'Parent') . ' > ' . $row->module_name;

                $modules[$row->module_id] = [
                    'id' => $row->module_id,
                    'name' => $row->module_name,
                    'url' => $row->url,
                    'icon' => $row->icon,
                    'seq_no' => $row->seq_no,
                    'is_sub_module' => $row->is_sub_module,
                    'parent_id' => $row->parent_id,
                    'is_permission' => $row->is_permission,
                    'displayName' => $displayName,
                    'permissions' => [],
                ];
            }

            $modules[$row->module_id]['permissions'][] = [
                'id' => $row->permission_id,
                'action' => $row->permission_action,
                'modulePermissionId' => $row->module_permission_id,
            ];

            if (! empty($row->user_has_permission)) {
                $userPermissions[] = $row->user_has_permission;
            }
        }

        return [
            'modules' => array_values($modules),
            'userPermissions' => array_values(array_unique($userPermissions)),
        ];
    }

    /**
     * Fetch user's module + action access map.
     */
    public function getUserModuleAccess(int $userId): array
    {
        $user = DB::table('users')->select('id', 'role')->where('id', $userId)->first();

        // 1) Role allowed modules
        $roleModuleIds = DB::table('role_modules')
            ->where('role', $user->role)
            ->pluck('module_id')
            ->toArray();

        // 2) Load only role-allowed modules
        $modules = Module::whereIn('id', $roleModuleIds)
            ->with('modulePermissions.permission:id,action')
            ->get();

        // 3) Load user permissions
        $userPermissions = UserPermission::with([
            'modulePermission.module:id,name',
            'modulePermission.permission:id,action',
        ])
            ->where('user_id', $userId)
            ->get();

        // 4) Build user permission lookup
        $userAccess = [];
        foreach ($userPermissions as $up) {
            $mp = $up->modulePermission;
            if (! $mp || ! $mp->module || ! $mp->permission) {
                continue;
            }

            $userAccess[$mp->module->id][] = $mp->permission->action;
        }

        // 5) Build final permission map
        $permissionMap = [];
        $roleModules = [];

        foreach ($modules as $module) {
            $moduleKey = $module->name; // Use module_key if available
            $moduleId = $module->id;

            $roleModules[] = $moduleKey;

            if (! empty($userAccess[$moduleId])) {
                $permissionMap[$moduleKey] = array_values(
                    array_unique($userAccess[$moduleId])
                );
            } else {
                $permissionMap[$moduleKey] = $module->modulePermissions
                    ->pluck('permission.action')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        return [
            'roleModules' => array_values(array_unique($roleModules)),
            'permissions' => $permissionMap,
        ];
    }

    // Fully implements all rules:
    // - If module has view, show.
    // - If module has zero permissions configured, show.
    // - If module has some permissions except view, hide.
    // - Each module is checked independently.
    // - Parent/child relation is maintained.
    // - Role-based filtering is applied first.
    public function buildSidebarMenu(int $userId): array
    {
        $user = DB::table('users')->select('id', 'role')->where('id', $userId)->first();

        // 1) Get role-allowed module IDs
        $roleModuleIds = DB::table('role_modules')
            ->where('role', $user->role)
            ->pluck('module_id')
            ->toArray();

        // 2) Get full permission matrix
        $matrix = $this->getModulePermissionMatrix($userId, YesNoFlag::NO->value);

        // 3) Filter modules by role first
        $modules = array_filter($matrix['modules'], function ($m) use ($roleModuleIds) {
            return in_array($m['id'], $roleModuleIds);
        });

        // 4) Continue existing logic
        return $this->applyUserPermissionRules(
            $modules,
            $matrix['userPermissions']
        );
    }

    private function applyUserPermissionRules(array $modules, array $userPermissions): array
    {
        $userPermissionSet = array_flip($userPermissions);
        $moduleMap = [];

        foreach ($modules as $m) {
            $permissions = $m['permissions'] ?? [];
            $configuredPermissionIds = [];
            $viewMPId = null;

            foreach ($permissions as $perm) {
                if (! empty($perm['modulePermissionId'])) {
                    $configuredPermissionIds[] = $perm['modulePermissionId'];
                }
                if ($perm['action'] === PermissionAction::VIEW->value) {
                    $viewMPId = $perm['modulePermissionId'];
                }
            }

            $hasAnyConfigured = count($configuredPermissionIds) > 0;
            $userHasAnyForModule = false;

            foreach ($configuredPermissionIds as $cid) {
                if (isset($userPermissionSet[$cid])) {
                    $userHasAnyForModule = true;
                    break;
                }
            }

            if (
                ! $hasAnyConfigured ||
                ($viewMPId && isset($userPermissionSet[$viewMPId])) ||
                ($hasAnyConfigured && ! $userHasAnyForModule)
            ) {
                $moduleMap[$m['id']] = [
                    'id' => $m['id'],
                    'name' => $m['name'],
                    'url' => $m['url'],
                    'icon' => $m['icon'],
                    'parent_id' => $m['parent_id'],
                    'children' => [],
                ];
            }
        }

        $tree = [];
        foreach ($moduleMap as $m) {
            if (empty($m['parent_id'])) {
                $tree[$m['id']] = $m;
            } elseif (isset($moduleMap[$m['parent_id']])) {
                $tree[$m['parent_id']]['children'][] = $m;
            }
        }

        return array_values($tree);
    }
}

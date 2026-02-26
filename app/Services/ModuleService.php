<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActiveInactiveStatus;
use App\Enums\PermissionAction;
use App\Enums\UserRole;
use App\Enums\YesNoFlag;
use App\Models\Module;
use App\Models\ModulePermission;
use App\Models\Permission;
use App\Models\RoleModule;
use App\Services\Logging\ActivityLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ModuleService
{
    /**
     * Get paginated modules with filters
     */
    public function getPaginatedModules(array $filters): LengthAwarePaginator
    {
        $query = Module::query();
        $status = $filters['status'] ?? null;

        // Search by name or URL
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('url', 'LIKE', "%{$search}%");
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        // Sorting
        $sortedField = $filters['sortedField'] ?? 'id';
        $sortedBy    = $filters['sortedBy'] ?? 'desc';

        $query->orderBy($sortedField, $sortedBy);

        // Pagination
        $perPage = $filters['perPage'] ?? 10;

        return $query->paginate($perPage);
    }

    /**
     * Create module + default permission mapping
     */
    public function createModule(array $data): Module
    {
        return DB::transaction(function () use ($data) {

            // Create module
            $data['parent_id'] = $data['parent_id'] == 0 ? null : $data['parent_id'];
            $module = Module::create($data);

            if ($data['is_sub_module'] === YesNoFlag::NO->value) {

                // Fetch CRUD permissions
                $permissions = Permission::whereIn('action', PermissionAction::defaults())->get();

                // Insert permission mapping
                foreach ($permissions as $perm) {
                    ModulePermission::create([
                        'module_id'     => $module->id,
                        'permission_id' => $perm->id,
                    ]);
                }

                // Insert Role mapping for Super Admin
                RoleModule::create([
                    'module_id'     => $module->id,
                    'role'          => UserRole::SUPER_ADMIN->value,
                ]);
            }

            return $module;
        });
    }

    /**
     * Update module + handle permission mapping
     */

    public function updateModule(Module $module, array $data): Module
    {
        return DB::transaction(function () use ($module, $data) {

            // Update module fields
            $module->update($data);

            // Fetch existing permission mappings
            $existingMappings = ModulePermission::where('module_id', $module->id)->get();
            // Create default CRUD mappings if none exist and permissions are enabled.
            if ($existingMappings->isEmpty() && ($data['is_permission'] ?? $module->is_permission) === YesNoFlag::YES->value) {

                $permissions = Permission::whereIn('action', PermissionAction::defaults())->get();

                foreach ($permissions as $perm) {
                    ModulePermission::create([
                        'module_id'     => $module->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }

            return $module;
        });
    }
    /**
     * Get module by UUID
     */
    public function getByUuid(string $uuid): ?Module
    {
        return Module::where('uuid', $uuid)->first();
    }
    /**
     * Delete module
     */
    public function deleteModule(Module $module): bool
    {
        return $module->delete();
    }

    /**
     * Toggle Active/Inactive
     */
    public function toggleStatus(Module $module): Module
    {
        $newStatus = $module->status === ActiveInactiveStatus::ACTIVE->value
            ? ActiveInactiveStatus::INACTIVE->value
            : ActiveInactiveStatus::ACTIVE->value;

        activity()->withoutLogs(function () use ($module, $newStatus) {
            $module->status = $newStatus;
            $module->save();
        });

        $module = $module->fresh();
        $event = $newStatus === ActiveInactiveStatus::ACTIVE->value ? 'activated' : 'deactivated';
        $this->activityLogger()->log('module', $module, $event, [
            'status' => $newStatus,
        ]);

        return $module;
    }

    /**
     * Get all parent modules (optional)
     */
    public function getAllParent(): Collection
    {
        return Module::where(function ($q) {
            $q->where('parent_id', 0)
                ->orWhereNull('parent_id');
        })
            ->where('is_sub_module', YesNoFlag::YES->value)
            ->get();
    }

    private function activityLogger(): ActivityLogger
    {
        return app(ActivityLogger::class);
    }
}



<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Module\ModuleIndexRequest;
use App\Http\Requests\Api\Module\ModuleStoreRequest;
use App\Http\Requests\Api\Module\ModuleUpdateRequest;
use App\Services\ModuleService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ModuleResource;
use App\Traits\ApiResponse;

class ModuleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ModuleService $moduleService
    ) {}

    /**
     * Paginated module listing
     */
    public function index(ModuleIndexRequest $request): JsonResponse
    {
        $filters = $request->filters();

        $modules = $this->moduleService->getPaginatedModules($filters);

        return $this->paginate(
            'messages.module_list_success',
            ModuleResource::collection($modules),
            $modules
        );
    }

    /**
     * Create a module
     */
    public function store(ModuleStoreRequest $request): JsonResponse
    {
        return $this->safeExecute('messages.module_created', function () use ($request) {
            $module = $this->moduleService->createModule($request->validated());
            return new ModuleResource($module);
        }, 201);
    }

    /**
     * Show module by UUID
     */
    public function show(string $uuid): JsonResponse
    {
        $module = $this->moduleService->getByUuid($uuid);

        if (! $module) {
            return $this->error('messages.module_not_found', [], 404);
        }

        return $this->success('messages.module_details_success', new ModuleResource($module));
    }

    /**
     * Edit Module (same as show)
     */
    public function edit(string $uuid): JsonResponse
    {
        return $this->show($uuid);
    }

    /**
     * Update module
     */
    public function update(ModuleUpdateRequest $request, string $uuid): JsonResponse
    {
        $module = $this->moduleService->getByUuid($uuid);

        if (! $module) {
            return $this->error('messages.module_not_found', [], 404);
        }

        return $this->safeExecute('messages.module_updated', function () use ($module, $request) {
            $updated = $this->moduleService->updateModule($module, $request->validated());
            return new ModuleResource($updated);
        });
    }

    /**
     * Delete module
     */
    public function destroy(string $uuid): JsonResponse
    {
        $module = $this->moduleService->getByUuid($uuid);

        if (! $module) {
            return $this->error('messages.module_not_found', [], 404);
        }

        return $this->safeExecute('messages.module_deleted', function () use ($module) {
            $this->moduleService->deleteModule($module);
            return [];
        });
    }

    /**
     * Toggle Active / Inactive
     */
    public function active(string $uuid): JsonResponse
    {
        $module = $this->moduleService->getByUuid($uuid);

        if (! $module) {
            return $this->error('messages.module_not_found', [], 404);
        }

        return $this->safeExecute('messages.module_status_updated', function () use ($module) {
            $updated = $this->moduleService->toggleStatus($module);
            return ['status' => $updated->status];
        });
    }

    /**
     * Get module list (submodules only)
     * GET /module/getList
     */
    public function getModuleList(): JsonResponse
    {
        return $this->safeExecute('messages.module_list_success', function () {
            return $this->moduleService->getAllParent();
        });
    }
}

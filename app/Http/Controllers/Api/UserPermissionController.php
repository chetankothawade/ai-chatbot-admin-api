<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserPermission\UserPermissionToggleRequest;
use App\Http\Requests\Api\UserPermission\UserPermissionUserRequest;
use App\Enums\YesNoFlag;
use App\Http\Resources\SidebarMenuItemResource;
use App\Http\Resources\UserModuleAccessResource;
use App\Http\Resources\UserPermissionMatrixResource;
use App\Http\Resources\UserPermissionToggleResource;
use App\Models\User;
use App\Services\UserPermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserPermissionController extends Controller
{
    use ApiResponse;

    /**
     * Inject the UserPermissionService.
     *
     * @param UserPermissionService $service
     */
    public function __construct(
        protected UserPermissionService $service
    ) {}

    /**
     * Toggle a permission for a given user.
     * Accepts user UUID instead of numeric user ID.
     *
     * POST /user-permissions/toggle
     *
     * Body:
     *   - userUuid (string, required)
     *   - modulePermissionId (int, required)
     *   - isChecked (boolean, required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(UserPermissionToggleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Fetch user by UUID
        $user = User::where('uuid', $validated['userUuid'])->first();

        if (! $user) {
            return $this->error('messages.user_not_found', [], 404);
        }

        return $this->safeExecute('messages.user_permission_updated', function () use ($user, $validated) {
            $result = $this->service->toggle(
                $user->id,
                $validated['modulePermissionId'],
                $validated['isChecked']
            );

            return new UserPermissionToggleResource([
                'action' => $validated['isChecked'] ? 'assigned' : 'revoked',
                'result' => $result,
            ]);
        });
    }


    /**
     * Get module list + module permissions + user's assigned permissions.
     * Accepts user UUID.
     *
     * GET /user-permissions/getAll/{uuid}
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersModulesPermission(UserPermissionUserRequest $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $request->validated('uuid'))->first();

        if (! $user) {
            return $this->error('messages.user_not_found', [], 404);
        }

        return $this->safeExecute('messages.user_permission_matrix_success', function () use ($user) {
            return new UserPermissionMatrixResource(
                $this->service->getModulePermissionMatrix($user->id, YesNoFlag::YES->value)
            );
        });
    }


    /**
     * Get only the modules that user can access (menu-level access).
     * Accepts user UUID.
     *
     * GET /user-permissions/access/{uuid}
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function userModuleAccess(UserPermissionUserRequest $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $request->validated('uuid'))->first();

        if (! $user) {
            return $this->error('messages.user_not_found', [], 404);
        }

        return $this->safeExecute('messages.user_module_access_success', function () use ($user) {
            return new UserModuleAccessResource(
                $this->service->getUserModuleAccess($user->id)
            );
        });
    }
    public function sidebarMenu(): JsonResponse
    {
        $userId = Auth::id();

        if (! $userId) {
            return $this->error('messages.unauthorized', [], 401);
        }

        return $this->safeExecute('messages.sidebar_loaded', function () use ($userId) {
            return SidebarMenuItemResource::collection(
                collect($this->service->buildSidebarMenu($userId))
            );
        });
    }
}

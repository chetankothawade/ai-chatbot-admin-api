<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionToggleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $result = $this['result'] ?? null;

        if ($result instanceof UserPermission) {
            return [
                'action' => $this['action'] ?? null,
                'permission' => [
                    'id' => $result->id,
                    'user_id' => $result->user_id,
                    'module_permission_id' => $result->module_permission_id,
                ],
            ];
        }

        return [
            'action' => $this['action'] ?? null,
            'deleted' => (int) $result,
        ];
    }
}

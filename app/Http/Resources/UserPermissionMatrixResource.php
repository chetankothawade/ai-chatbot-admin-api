<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'modules' => UserPermissionModuleResource::collection(
                collect($this['modules'] ?? [])
            )->resolve(),
            'userPermissions' => array_values($this['userPermissions'] ?? []),
        ];
    }
}

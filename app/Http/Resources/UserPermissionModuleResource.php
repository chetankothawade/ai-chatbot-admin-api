<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'displayName' => $this['displayName'] ?? null,
            'url' => $this['url'] ?? null,
            'icon' => $this['icon'] ?? null,
            'seq_no' => $this['seq_no'] ?? null,
            'is_sub_module' => $this['is_sub_module'] ?? null,
            'is_permission' => $this['is_permission'] ?? null,
            'parent_id' => $this['parent_id'] ?? null,
            'permissions' => $this['permissions'] ?? [],
        ];
    }
}

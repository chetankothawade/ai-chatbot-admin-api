<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class RoleModuleMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $modules = $this['modules'] ?? [];
        $collection = $modules instanceof Collection ? $modules : collect($modules);

        return [
            'roles' => $this['roles'] ?? [],
            'modules' => RoleModuleMatrixItemResource::collection($collection)->resolve(),
        ];
    }
}

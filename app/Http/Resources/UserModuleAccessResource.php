<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserModuleAccessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'roleModules' => array_values($this['roleModules'] ?? []),
            'permissions' => $this['permissions'] ?? [],
        ];
    }
}

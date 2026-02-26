<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SidebarMenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'url' => $this['url'] ?? null,
            'icon' => $this['icon'] ?? null,
            'parent_id' => $this['parent_id'] ?? null,
            'children' => self::collection(collect($this['children'] ?? []))->resolve(),
        ];
    }
}

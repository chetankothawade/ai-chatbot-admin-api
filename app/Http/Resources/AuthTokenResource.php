<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => isset($this['user']) ? new UserResource($this['user']) : null,
            'token' => $this['token'] ?? null,
        ];
    }
}

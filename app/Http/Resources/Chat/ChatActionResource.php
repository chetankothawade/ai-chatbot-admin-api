<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => $this['message'] ?? null,
            'deleted' => isset($this['deleted']) ? (int) $this['deleted'] : null,
        ];
    }
}

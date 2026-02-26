<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatParticipantRemoveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => $this['message'] ?? 'Removed',
            'deleted' => (int) ($this['deleted'] ?? 0),
        ];
    }
}

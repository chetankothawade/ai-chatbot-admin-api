<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'role' => $this->role,
            'content' => $this->content,
            'tokens' => $this->tokens,
            'response_time_ms' => $this->response_time_ms,
            'parent_id' => $this->parent_id,
            'created_at' => optional($this->created_at)?->toISOString(),
        ];
    }
}

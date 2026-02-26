<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSessionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'chat' => new ChatSessionResource($this['chat']),
            'messages' => ChatMessageResource::collection($this['messages']),
        ];
    }
}

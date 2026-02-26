<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSendMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'chat' => new ChatSessionResource($this['chat']),
            'user_message' => new ChatMessageResource($this['user_message']),
            'assistant_message' => new ChatMessageResource($this['assistant_message']),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatParticipant;
use Illuminate\Database\Eloquent\Collection;

class ParticipantService
{
    public function index(int $chatId): Collection
    {
        return ChatParticipant::query()
            ->with('user:id,name')
            ->where('chat_id', $chatId)
            ->orderBy('id')
            ->get();
    }

    public function add(
      
        int $chatId,
        int $userId,
        ?string $role = null
    ): ChatParticipant {
       
        return ChatParticipant::firstOrCreate(
            [
                'chat_id' => $chatId,
                'user_id' => $userId,
            ],
            [
                'role' => $role ?? 'member',
            ]
        );
    }

    public function remove(
        int $chatId,
        int $userId
    ): int {

        return ChatParticipant::query()
            ->where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->delete();
    }
}

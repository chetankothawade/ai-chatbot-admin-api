<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

class ChatSessionService
{
    public function indexForUser(
        int $userId,
        ?string $search = null,
        int $limit = 20
    ): CursorPaginator {
        return ChatSession::query()
            ->where('user_id', $userId)
            ->when(
                $search,
                fn($q) =>
                $q->where('title', 'like', "%{$search}%")
            )
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->cursorPaginate($limit);
    }

    
    public function storeForUser(int $userId, array $data): ChatSession
    {
        return ChatSession::query()->create([
            'user_id' => $userId,
            'title' => $data['title'] ?? 'New Chat',
            'model' => $data['model'] ?? config('ai.model'),
            'last_message_at' => now(),
        ]);
    }

    public function showForUser(int $userId, int $chatId, int $limit = 50): array
    {
        $chat = $this->getForUser($userId, $chatId);

        $messages = Message::query()
            ->where('chat_id', $chat->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return [
            'chat' => $chat,
            'messages' => $messages,
        ];
    }

    public function updateForUser(int $userId, int $chatId, array $data): ChatSession
    {
        $chat = $this->getForUser($userId, $chatId);
        $chat->update($data);

        return $chat->fresh();
    }

    public function deleteForUser(int $userId, int $chatId): int
    {
        return ChatSession::query()
            ->where('id', $chatId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function clearMessagesForUser(int $userId, int $chatId): int
    {
        $chat = $this->getForUser($userId, $chatId);

        return Message::query()
            ->where('chat_id', $chat->id)
            ->delete();
    }

    public function updateContextForUser(int $userId, int $chatId, ?array $context): ChatSession
    {
        $chat = $this->getForUser($userId, $chatId);
        $chat->update(['context' => $context]);

        return $chat->fresh();
    }

    public function updateModelForUser(int $userId, int $chatId, string $model): ChatSession
    {
        $chat = $this->getForUser($userId, $chatId);
        $chat->update(['model' => $model]);

        return $chat->fresh();
    }

    public function togglePinForUser(int $userId, int $chatId): ChatSession
    {
        $chat = $this->getForUser($userId, $chatId);
        $chat->update(['is_pinned' => ! $chat->is_pinned]);

        return $chat->fresh();
    }

    public function getForUser(int $userId, int $chatId): ChatSession
    {
        return ChatSession::query()
            ->where('id', $chatId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}

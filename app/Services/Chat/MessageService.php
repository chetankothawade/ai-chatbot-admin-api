<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatSession;
use App\Models\Message;
use App\Services\AI\OpenAIService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessageService
{
    public function __construct(
        private OpenAIService $openAIService
    ) {}

    public function send(int $userId, string $content, int $chatId): array
    {
        $chat = ChatSession::query()
            ->where('id', $chatId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $userMessage = Message::query()->create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $content,
            'created_at' => now(),
        ]);

        $history = Message::query()
            ->where('chat_id', $chat->id)
            ->orderBy('id')
            ->get(['role', 'content'])
            ->map(fn (Message $message) => [
                'role' => (string) $message->role,
                'content' => (string) $message->content,
            ])
            ->all();

        $assistantContent = $this->openAIService->chat($history);

        $assistantMessage = Message::query()->create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => $assistantContent,
            'parent_id' => $userMessage->id,
            'created_at' => now(),
        ]);

        $chat->update(['last_message_at' => now()]);

        return [
            'chat' => $chat->fresh(),
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
        ];
    }

    public function indexForUser(int $userId, int $chatId, int $limit = 50): LengthAwarePaginator
    {
        ChatSession::query()
            ->where('id', $chatId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return Message::query()
            ->where('chat_id', $chatId)
            ->orderByDesc('id')
            ->paginate($limit);
    }

    public function deleteForUser(int $userId, int $messageId): int
    {
        return Message::query()
            ->where('id', $messageId)
            ->whereHas('chat', fn ($q) => $q->where('user_id', $userId))
            ->delete();
    }

    public function regenerateForUser(int $userId, int $messageId): array
    {
        $message = Message::query()
            ->where('id', $messageId)
            ->whereHas('chat', fn ($q) => $q->where('user_id', $userId))
            ->firstOrFail();

        return $this->send(
            $userId,
            (string) $message->content,
            (int) $message->chat_id
        );
    }
}

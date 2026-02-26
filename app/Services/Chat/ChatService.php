<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Cache;

class ChatService
{
    public function __construct(private OpenAIService $aiService) {}

    /**
     * Send message + get AI response
     */
    public function sendMessage(int $userId, string $message, ?int $chatId = null): array
    {
        return DB::transaction(function () use ($userId, $message, $chatId) {

            // 1. Create or fetch chat
            $chat = $chatId
                ? ChatSession::where('id', $chatId)->where('user_id', $userId)->firstOrFail()
                : $this->createNewChat($userId, $message);

            // 2. Save user message
            $userMsg = Message::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'content' => $message
            ]);

            // 3. Get last messages for context (limit 10)
            $history = Message::where('chat_id', $chat->id)
                ->latest()
                ->limit(10)
                ->get()
                ->reverse()
                ->values()
                ->map(fn($msg) => [
                    'role' => $msg->role,
                    'content' => $msg->content
                ])
                ->toArray();

            // 4. Call AI
            $startTime = microtime(true);

            $aiResponse = $this->aiService->chat($history);

            $responseTime = (int)((microtime(true) - $startTime) * 1000);

            // 5. Save AI message
            $assistantMsg = Message::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $aiResponse,
                'response_time_ms' => $responseTime
            ]);

            // 6. Update chat last activity
            $chat->update([
                'last_message_at' => now()
            ]);

            return [
                'chat' => $chat->fresh(),
                'chat_id' => $chat->id,
                'user_message' => $userMsg,
                'assistant_message' => $assistantMsg
            ];
        });
    }

    /**
     * Create new chat session
     */
    private function createNewChat(int $userId, string $message): ChatSession
    {
        return ChatSession::create([
            'uuid' => Str::uuid(),
            'user_id' => $userId,
            'title' => Str::limit($message, 30),
            'last_message_at' => now()
        ]);
    }

    /**
     * Get all chats for user (sidebar)
     */



    public function getUserChats(int $userId)
    {
        return Cache::remember("user_chats_{$userId}", 60, function () use ($userId) {
            return ChatSession::where('user_id', $userId)
                ->orderByDesc('is_pinned')
                ->orderByDesc('last_message_at')
                ->select('id', 'title', 'last_message_at', 'is_pinned')
                ->get();
        });
    }

    /**
     * Get messages allows pagination
     */
    public function getChatMessages(int $userId, int $chatId)
    {
        return Message::whereHas('chat', function ($q) use ($userId, $chatId) {
            $q->where('id', $chatId)->where('user_id', $userId);
        })
            ->orderBy('id')
            ->paginate(50);
    }

    public function getChatMessagesCursor(
        int $userId,
        int $chatId,
        int $limit = 50
    ): CursorPaginator {
        return Message::query()
            ->where('chat_id', $chatId)
            ->whereHas(
                'chat',
                fn($q) =>
                $q->where('user_id', $userId)
            )
            ->orderByDesc('id') // latest first
            ->cursorPaginate($limit);
    }

    public function deleteMessage(int $userId, int $messageId): int
    {
        return Message::query()
            ->where('id', $messageId)
            ->whereHas('chat', fn($q) => $q->where('user_id', $userId))
            ->delete();
    }

    public function regenerateMessage(int $userId, int $messageId): array
    {
        $message = Message::query()
            ->where('id', $messageId)
            ->whereHas('chat', fn($q) => $q->where('user_id', $userId))
            ->firstOrFail();

        return $this->sendMessage(
            $userId,
            (string) $message->content,
            (int) $message->chat_id
        );
    }

    /**
     * Delete chat
     */
    public function deleteChat(int $userId, int $chatId): void
    {
        ChatSession::where('id', $chatId)
            ->where('user_id', $userId)
            ->delete();
    }
}

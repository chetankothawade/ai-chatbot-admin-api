<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Cache;

class ChatService
{
    public function __construct(private OpenAIService $aiService) {}

    /**
     * Send message + get AI response
     */
    public function sendMessage(int $userId, string $message, ?int $chatId = null, array $attachments = []): array
    {
        return DB::transaction(function () use ($userId, $message, $chatId, $attachments) {

            // 1. Create or fetch chat
            $message = trim($message);
            $messageContent = $message !== ''
                ? $message
                : $this->attachmentOnlyMessage($attachments);

            $chat = $chatId
                ? ChatSession::where('id', $chatId)->where('user_id', $userId)->firstOrFail()
                : $this->createNewChat($userId, $messageContent);

            // 2. Save user message
            $userMsg = Message::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'content' => $messageContent
            ]);

            $storedAttachments = $this->storeAttachments($userMsg, $attachments);

            // 3. Get last messages for context (limit 10)
            $history = Message::where('chat_id', $chat->id)
                ->with('attachments')
                ->latest()
                ->limit(10)
                ->get()
                ->reverse()
                ->values()
                ->map(fn($msg) => [
                    'role' => $msg->role,
                    'content' => $this->contentForAi($msg)
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
                'parent_id' => $userMsg->id,
                'response_time_ms' => $responseTime
            ]);

            // 6. Update chat last activity
            $chat->update([
                'last_message_at' => now()
            ]);

            return [
                'chat' => $chat->fresh(),
                'chat_id' => $chat->id,
                'user_message' => $userMsg->setRelation('attachments', $storedAttachments),
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
            ->with('attachments')
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
            ->with('attachments')
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

    public function transcribeAudio(
        UploadedFile $audio,
        ?string $language = null,
        ?string $prompt = null
    ): array {
        return $this->aiService->transcribeAudio($audio, $language, $prompt);
    }

    private function storeAttachments(Message $message, array $attachments)
    {
        $records = collect();

        foreach ($attachments as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $extension = $file->getClientOriginalExtension();
            $filename = (string) Str::uuid() . ($extension ? ".{$extension}" : '');
            $path = $file->storeAs("uploads/chat/{$message->chat_id}", $filename, 'public');

            $records->push($message->attachments()->create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName() ?: $filename,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
            ]));
        }

        return $records;
    }

    private function attachmentOnlyMessage(array $attachments): string
    {
        $names = collect($attachments)
            ->filter(fn($file) => $file instanceof UploadedFile)
            ->map(fn(UploadedFile $file) => $file->getClientOriginalName())
            ->filter()
            ->values();

        return $names->isNotEmpty()
            ? 'Uploaded attachment(s): ' . $names->implode(', ')
            : '';
    }

    private function contentForAi(Message $message): string
    {
        $content = (string) $message->content;

        if (!$message->relationLoaded('attachments') || $message->attachments->isEmpty()) {
            return $content;
        }

        $attachmentLines = $message->attachments
            ->map(function ($attachment) {
                $line = "- {$attachment->original_name}";
                if ($attachment->mime_type) {
                    $line .= " ({$attachment->mime_type})";
                }

                $snippet = $this->readTextAttachmentSnippet($attachment->disk, $attachment->path, $attachment->mime_type);
                if ($snippet !== '') {
                    $line .= "\n{$snippet}";
                }

                return $line;
            })
            ->implode("\n");

        return trim($content . "\n\nAttachments:\n" . $attachmentLines);
    }

    private function readTextAttachmentSnippet(string $disk, string $path, ?string $mimeType): string
    {
        $textMimeTypes = [
            'text/plain',
            'text/csv',
            'application/json',
        ];

        if (!in_array((string) $mimeType, $textMimeTypes, true)) {
            return '';
        }

        $contents = Storage::disk($disk)->get($path);
        if (!is_string($contents) || trim($contents) === '') {
            return '';
        }

        return Str::limit(trim($contents), 4000);
    }
}

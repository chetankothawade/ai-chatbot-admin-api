<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\MessageIndexRequest;
use App\Http\Requests\Api\Chat\MessageRouteIdRequest;
use App\Http\Requests\Api\Chat\MessageSendRequest;
use App\Http\Requests\Api\Chat\MessageStreamRequest;
use App\Http\Resources\Chat\ChatActionResource;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Http\Resources\Chat\ChatSendMessageResource;
use App\Services\Chat\ChatService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(private ChatService $chatService) {}

    private function userId(): int
    {
        $id = Auth::id();

        if (!$id) {
            abort(401, 'Unauthenticated');
        }

        return (int) $id;
    }

    public function send(MessageSendRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->chatService->sendMessage(
            $this->userId(),
            $validated['message'],
            $validated['chat_id'] ?? null
        );

        return $this->success(
            'Message sent',
            new ChatSendMessageResource($result)
        );
    }

    public function index(MessageIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $messages = $this->chatService->getChatMessagesCursor(
            $this->userId(),
            $validated['chat_id'],
            $validated['limit'] ?? 50
        );

        return $this->success('Messages fetched', [
            'data' => ChatMessageResource::collection($messages),
            'pagination' => [
                'next_cursor' => $messages->nextCursor()?->encode(),
                'prev_cursor' => $messages->previousCursor()?->encode(),
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->chatService->deleteMessage(
            $this->userId(),
            $id
        );

        return $this->success('Message deleted', [
            'deleted' => $deleted
        ]);
    }

    public function regenerate(int $id): JsonResponse
    {
        $result = $this->chatService->regenerateMessage(
            $this->userId(),
            $id
        );

        return $this->success(
            'Message regenerated',
            new ChatSendMessageResource($result)
        );
    }

    public function stream(MessageStreamRequest $request)
    {
        return response()->stream(function () use ($request) {

            $validated = $request->validated();

            echo "data: Thinking...\n\n";
            flush();

            $result = $this->chatService->sendMessage(
                $this->userId(),
                $validated['message'],
                $validated['chat_id'] ?? null
            );

            $content = $result['assistant_message']->content;

            foreach (str_split($content, 20) as $chunk) {
                echo "data: {$chunk}\n\n";
                flush();
            }

            echo "event: done\n";
            echo "data: [DONE]\n\n";
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // IMPORTANT for nginx
        ]);
    }
}
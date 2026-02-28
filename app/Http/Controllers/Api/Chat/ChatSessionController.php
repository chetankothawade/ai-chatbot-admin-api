<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\ChatSessionIndexRequest;
use App\Http\Requests\Api\Chat\ChatSessionShowRequest;
use App\Http\Requests\Api\Chat\ChatSessionStoreRequest;
use App\Http\Requests\Api\Chat\ChatSessionUpdateContextRequest;
use App\Http\Requests\Api\Chat\ChatSessionUpdateModelRequest;
use App\Http\Requests\Api\Chat\ChatSessionUpdateRequest;
use App\Http\Resources\Chat\ChatSessionDetailResource;
use App\Http\Resources\Chat\ChatSessionResource;
use App\Services\Chat\ChatSessionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatSessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ChatSessionService $chatSessionService
    ) {}

    /**
     * Get authenticated user ID safely
     */
    private function userId(): int
    {
        $id = Auth::id();

        if (!$id) {
            abort(401, 'Unauthenticated');
        }

        return (int) $id;
    }

    public function index(ChatSessionIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $sessions = $this->chatSessionService->indexForUser(
            $this->userId(),
            $validated['search'] ?? null,
            (int) ($validated['limit'] ?? 10)
        );

        return $this->success('Chat sessions fetched', [
            'data' => ChatSessionResource::collection($sessions),
            'pagination' => [
                'next_cursor' => $sessions->nextCursor()?->encode(),
                'prev_cursor' => $sessions->previousCursor()?->encode(),
                'per_page' => $sessions->perPage(),
            ],
        ]);
    }

    public function store(ChatSessionStoreRequest $request): JsonResponse
    {
        $session = $this->chatSessionService->storeForUser(
            $this->userId(),
            $request->validated()
        );

        return $this->success(
            'Chat session created',
            new ChatSessionResource($session),
            201
        );
    }

    public function show(ChatSessionShowRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $payload = $this->chatSessionService->showForUser(
            $this->userId(),
            $id,
            (int) ($validated['limit'] ?? 50)
        );

        return $this->success(
            'Chat session details fetched',
            new ChatSessionDetailResource($payload)
        );
    }

    public function update(ChatSessionUpdateRequest $request, int $id): JsonResponse
    {
        $session = $this->chatSessionService->updateForUser(
            $this->userId(),
            $id,
            $request->validated()
        );

        return $this->success(
            'Chat session updated',
            new ChatSessionResource($session)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->chatSessionService->deleteForUser(
            $this->userId(),
            $id
        );

        return $this->success('Chat session deleted', [
            'deleted' => $deleted
        ]);
    }

    public function clearMessages(int $id): JsonResponse
    {
        $deleted = $this->chatSessionService->clearMessagesForUser(
            $this->userId(),
            $id
        );

        return $this->success('Chat messages cleared', [
            'deleted' => $deleted
        ]);
    }

    public function updateContext(ChatSessionUpdateContextRequest $request, int $id): JsonResponse
    {
        $session = $this->chatSessionService->updateContextForUser(
            $this->userId(),
            $id,
            $request->validated('context')
        );

        return $this->success(
            'Chat context updated',
            new ChatSessionResource($session)
        );
    }

    public function updateModel(ChatSessionUpdateModelRequest $request, int $id): JsonResponse
    {
        $session = $this->chatSessionService->updateModelForUser(
            $this->userId(),
            $id,
            (string) $request->validated('model')
        );

        return $this->success(
            'Chat model updated',
            new ChatSessionResource($session)
        );
    }

    public function togglePin(int $id): JsonResponse
    {
        $session = $this->chatSessionService->togglePinForUser(
            $this->userId(),
            $id
        );

        return $this->success(
            'Chat pin status updated',
            new ChatSessionResource($session)
        );
    }
}

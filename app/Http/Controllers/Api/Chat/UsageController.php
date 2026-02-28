<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\UsageChatRequest;
use App\Http\Requests\Api\Chat\UsageIndexRequest;
use App\Http\Resources\Chat\ChatUsageResource;
use App\Services\Chat\UsageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class UsageController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UsageService $usageService
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

    /**
     * Overall usage
     */
    public function index(UsageIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $usage = $this->usageService->indexForUser(
            $this->userId(),
            $validated['from'] ?? null,
            $validated['to'] ?? null
        );

        return $this->success(
            'Usage fetched',
            ChatUsageResource::collection($usage)
        );
    }

    /**
     * Chat-specific usage
     */
    public function chatUsage(UsageChatRequest $request, int $chat_id): JsonResponse
    {
        $usage = $this->usageService->byChatForUser(
            $this->userId(),
            $chat_id
        );

        return $this->success(
            'Chat usage fetched',
            ChatUsageResource::collection($usage)
        );
    }
}

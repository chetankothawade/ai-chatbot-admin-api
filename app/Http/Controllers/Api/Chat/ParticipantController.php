<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\ParticipantAddRequest;
use App\Http\Requests\Api\Chat\ParticipantRemoveRequest;
use App\Http\Resources\Chat\ChatParticipantResource;
use App\Services\Chat\ParticipantService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ParticipantController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ParticipantService $participantService
    ) {}

    /**
     * Add participant to chat
     */
    public function add(ParticipantAddRequest $request, int $chatId): JsonResponse
    {
        $validated = $request->validated();

        $participant = $this->participantService->add(
            $chatId,           // ✅ route param
            $validated['user_id'],
            $validated['role'] ?? null
        );

        return $this->success(
            'Participant added',
            new ChatParticipantResource($participant),
            201
        );
    }

    /**
     * Remove participant from chat
     */
    public function remove(int $chatId, int $userId): JsonResponse
    {
        $deleted = $this->participantService->remove(
            $chatId,
            $userId
        );

        return $this->success('Participant removed', [
            'deleted' => $deleted
        ]);
    }
}
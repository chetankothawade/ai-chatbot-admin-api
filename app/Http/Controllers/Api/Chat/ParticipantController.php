<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\ParticipantAddRequest;
use App\Http\Requests\Api\Chat\ParticipantIndexRequest;
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
     * List participants in chat
     */
    public function index(ParticipantIndexRequest $request, int $id): JsonResponse
    {
        $participants = $this->participantService->index($id);

        return $this->success(
            'Participants fetched',
            ChatParticipantResource::collection($participants)
        );
    }

    /**
     * Add participant to chat
     */
    public function add(ParticipantAddRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $participant = $this->participantService->add(
            $id,
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
    public function remove(ParticipantRemoveRequest $request, int $id, int $user_id): JsonResponse
    {
        $deleted = $this->participantService->remove(
            $id,
            $user_id
        );

        return $this->success('Participant removed', [
            'deleted' => $deleted
        ]);
    }
}

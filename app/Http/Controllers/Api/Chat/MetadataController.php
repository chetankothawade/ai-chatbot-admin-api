<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\MetadataIndexRequest;
use App\Http\Requests\Api\Chat\MetadataStoreRequest;
use App\Http\Resources\Chat\ChatMetadataResource;
use App\Services\Chat\MetadataService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MetadataController extends Controller
{
    use ApiResponse;

    public function __construct(
        private MetadataService $metadataService
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
     * Store metadata
     */
    public function store(MetadataStoreRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $metadata = $this->metadataService->storeForUser(
            $this->userId(),
            $id, // ✅ use route param
            $validated['type'],
            $validated['meta'] ?? null
        );

        return $this->success(
            'Metadata saved',
            new ChatMetadataResource($metadata),
            201
        );
    }

    /**
     * List metadata
     */
    public function index(MetadataIndexRequest $request, int $id): JsonResponse
    {
        $metadata = $this->metadataService->indexForUser(
            $this->userId(),
            $id // ✅ use route param
        );

        return $this->success(
            'Metadata fetched',
            ChatMetadataResource::collection($metadata)
        );
    }
}
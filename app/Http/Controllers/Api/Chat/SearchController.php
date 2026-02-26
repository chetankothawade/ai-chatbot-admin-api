<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\SearchRequest;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Services\Chat\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SearchService $searchService
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
     * Search messages
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $results = $this->searchService->searchForUser(
            $this->userId(),
            $validated['q'],
            $validated['limit'] ?? 50
        );

        return $this->success(
            'Search results fetched',
            ChatMessageResource::collection($results)
        );
    }
}
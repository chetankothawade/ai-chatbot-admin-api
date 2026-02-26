<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class SearchService
{
    public function searchForUser(int $userId, string $query, int $limit = 50): Collection
    {
        return Message::query()
            ->whereHas('chat', fn ($q) => $q->where('user_id', $userId))
            ->whereFullText('content', $query)
            ->limit($limit)
            ->get();
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\Message;
use App\Models\MessageMetadata;
use Illuminate\Database\Eloquent\Collection;

class MetadataService
{
    public function storeForUser(int $userId, int $messageId, string $type, ?array $meta): MessageMetadata
    {
        Message::query()
            ->where('id', $messageId)
            ->whereHas('chat', fn ($q) => $q->where('user_id', $userId))
            ->firstOrFail();

        return MessageMetadata::query()->create([
            'message_id' => $messageId,
            'type' => $type,
            'meta' => $meta,
        ]);
    }

    public function indexForUser(int $userId, int $messageId): Collection
    {
        Message::query()
            ->where('id', $messageId)
            ->whereHas('chat', fn ($q) => $q->where('user_id', $userId))
            ->firstOrFail();

        return MessageMetadata::query()
            ->where('message_id', $messageId)
            ->get();
    }
}

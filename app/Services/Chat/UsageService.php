<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\AIUsageLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class UsageService
{
    public function indexForUser(int $userId, $from = null, $to = null)
    {
        $cacheKey = "usage_{$userId}_{$from}_{$to}";

        return Cache::remember($cacheKey, 60, function () use ($userId, $from, $to) {
            return AIUsageLog::query()
                ->where('user_id', $userId)
                ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
                ->get();
        });
    }
    public function byChatForUser(int $userId, int $chatId): Collection
    {
        return AIUsageLog::query()
            ->where('user_id', $userId)
            ->where('chat_id', $chatId)
            ->get();
    }
}

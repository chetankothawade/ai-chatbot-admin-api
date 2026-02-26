<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActivityLog\ActivityLogIndexRequest;
use App\Http\Resources\ActivityLogResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function index(ActivityLogIndexRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->error('messages.unauthenticated', [], 401);
        }

        $filters = $request->filters();
        $search = $filters['search'];
        $perPage = $filters['perPage'];
        $sortBy = $filters['sortedField'];
        $sortDir = $filters['sortedBy'];

        $activities = Activity::query()
            ->with(['causer:id,name', 'subject'])
            ->when(
                $user->role !== UserRole::SUPER_ADMIN->value,
                fn($q) =>
                $q->where('causer_type', get_class($user))
                    ->where('causer_id', $user->getAuthIdentifier())
            )
            ->when($search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where('log_name', 'like', "%{$search}%")
                        ->where('event', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('subject_id', 'like', "%{$search}%")
                        ->orWhereHas('causer', fn($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        return $this->paginate(
            'messages.activity_logs_success',
            ActivityLogResource::collection($activities->getCollection()),
            $activities
        );
    }
}


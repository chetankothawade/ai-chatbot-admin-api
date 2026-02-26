<?php

declare(strict_types=1);

namespace App\Services\Logging;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity event for a subject model.
     */
    public function log(
        string $logName,
        Model $subject,
        string $event,
        array $properties = [],
        Model|Authenticatable|int|string|null $causer = null
    ): void {
        $resolvedCauser = $causer ?? $this->resolveCauser();

        $activity = activity($logName)->performedOn($subject);

        if ($resolvedCauser instanceof Model) {
            $activity->causedBy($resolvedCauser);
        } elseif ($resolvedCauser instanceof Authenticatable) {
            $activity->causedBy($resolvedCauser->getAuthIdentifier());
        } elseif (is_int($resolvedCauser) || is_string($resolvedCauser)) {
            $activity->causedBy($resolvedCauser);
        }

        if (! empty($properties)) {
            $activity->withProperties($properties);
        }

        $description = method_exists($subject, 'getActivityDescription')
            ? (string) $subject->getActivityDescription($event)
            : sprintf('%s %s', class_basename($subject), $event);

        $activity->event($event)->log($description);
    }

    /**
     * Resolve current authenticated user across guards.
     */
    private function resolveCauser(): Model|Authenticatable|int|string|null
    {
        return Auth::guard('sanctum')->user()
            ?? Auth::user()
            ?? Auth::guard('client')->user();
    }
}

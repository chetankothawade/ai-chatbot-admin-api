<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;


// Role-Based Rate Limiting
// Different users deserve different access. Free users may get 60 requests/min, while premium users get 200/min.
// Example: SaaS app with tiered plans. Give more access to paying customers, without hurting free-tier users.
class RateLimitByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role ?? UserRole::USER->value;
        $limits = [
            UserRole::ADMIN->value => 100,
            UserRole::SUPER_ADMIN->value => 200,
            UserRole::USER->value => 50,
        ];
        $maxAttempts = $limits[$role] ?? 20;
        $key = "rate:{$role}:" . ($request->user()?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json(['message' => __('messages.too_many_requests')], 429, [
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $maxAttempts,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}

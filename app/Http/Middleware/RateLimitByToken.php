<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

// API Token-Based Limiting
// Limit users based on their API token tier (free/paid/unknown).
// Example: API that limits based on token type.
class RateLimitByToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $subject = $token ? hash('sha256', $token) : $request->ip();
        $key = 'rate_limit:token:' . $subject;

        $maxAttempts = $token ? 100 : 20;
        if (RateLimiter::tooManyAttempts($key,  $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json(['message' => __('messages.rate_limit_exceeded')], 429, [
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $maxAttempts,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}

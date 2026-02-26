<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\Logging\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService,
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->safeExecute('messages.registration_success', function () use ($request) {

            return new AuthTokenResource($this->authService->register(
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            ));
        }, 201);
    }


    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if ($result === false) {
            return $this->error('messages.invalid_credentials', [], 401);
        }

        if (isset($result['inactive'])) {
            return $this->error('messages.user_not_active', [], 403);
        }

        return $this->success('messages.login_success', new AuthTokenResource($result));
    }

    /**
     * Get logged-in user profile
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success('messages.me_success', new UserResource($request->user()));
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->update(['last_logout_at' => now()]);
            $this->activityLogger->log('user', $user, 'logout', [], $user);
        }

        $user?->currentAccessToken()?->delete();

        return $this->success('messages.logout_success');
    }

    /**
     * Send forgot password link
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->safeExecute('messages.password_reset_sent', function () use ($request) {
            $this->authService->sendResetLink($request->validated('email'));
            return [];
        });
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword($request->validated());

        if (! $result) {
            return $this->error('messages.invalid_or_expired', [], 400);
        }

        return $this->success('messages.password_reset_done', []);
    }


    public function refreshToken(Request $request): JsonResponse
    {
        $result = $this->authService->refreshAdminToken($request);

        return $this->success('messages.token_refreshed', new AuthTokenResource($result));
    }
}

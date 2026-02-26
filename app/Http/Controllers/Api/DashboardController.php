<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class DashboardController extends Controller
{
    use ApiResponse;
    /**
     * Dashboard summary
     */
    public function summary(Request $request): JsonResponse
    {
        return $this->success('messages.dashboard_summary_success', []);
    }
}

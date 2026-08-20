<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretaryDashboardService;
use Illuminate\Http\JsonResponse;

class SecretaryDashboardController extends Controller
{
    public function __construct(
        private readonly SecretaryDashboardService $secretaryDashboardService
    ) {}

    /**
     * Get secretary dashboard summary statistics.
     */
    public function dashboard(): JsonResponse
    {
        return $this->secretaryDashboardService->dashboard();
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $adminDashboardService
    ) {}

    /**
     * Get admin dashboard summary statistics.
     */
    public function dashboard(): JsonResponse
    {
        return $this->adminDashboardService->dashboard();
    }
}

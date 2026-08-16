<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{
    public function __construct(
        private readonly DoctorDashboardService $doctorDashboardService
    ) {}

    /**
     * Doctor dashboard summary (stats, next appointment, today's schedule,
     * recent patients).
     */
    public function dashboard(Request $request): JsonResponse
    {
        return $this->doctorDashboardService->dashboard($request);
    }
}

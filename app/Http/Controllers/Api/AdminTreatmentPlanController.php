<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminTreatmentPlanService;
use Illuminate\Http\JsonResponse;

class AdminTreatmentPlanController extends Controller
{
    public function __construct(
        private readonly AdminTreatmentPlanService $adminTreatmentPlanService
    ) {}

    /**
     * Get paginated list of all treatment plans (read-only for admin).
     */
    public function index(): JsonResponse
    {
        return $this->adminTreatmentPlanService->index();
    }

    /**
     * Display the specified treatment plan.
     */
    public function show(int $treatmentPlan): JsonResponse
    {
        return $this->adminTreatmentPlanService->show($treatmentPlan);
    }
}

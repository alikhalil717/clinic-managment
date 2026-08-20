<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretaryTreatmentPlanService;
use Illuminate\Http\JsonResponse;

class SecretaryTreatmentPlanController extends Controller
{
    public function __construct(
        private readonly SecretaryTreatmentPlanService $secretaryTreatmentPlanService
    ) {}

    /**
     * Get list of all treatment plans (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        return $this->secretaryTreatmentPlanService->index();
    }

    /**
     * Display the specified treatment plan.
     */
    public function show(int $treatmentPlan): JsonResponse
    {
        return $this->secretaryTreatmentPlanService->show($treatmentPlan);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PatientTreatmentPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientTreatmentPlanController extends Controller
{
    public function __construct(
        private readonly PatientTreatmentPlanService $patientTreatmentPlanService
    ) {}

    /**
     * List all treatment plans belonging to the logged-in patient.
     */
    public function showTreatmentPlans(Request $request): JsonResponse
    {
        return $this->patientTreatmentPlanService->listForPatient($request->user());
    }

    /**
     * Show a single treatment plan's details (scoped to the patient).
     */
    public function showTreatmentPlanDetails(Request $request, int $treatmentPlan): JsonResponse
    {
        return $this->patientTreatmentPlanService->showForPatient($request->user(), $treatmentPlan);
    }
}

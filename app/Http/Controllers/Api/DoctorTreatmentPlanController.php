<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorTreatmentPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorTreatmentPlanController extends Controller
{
    public function __construct(
        private readonly DoctorTreatmentPlanService $service
    ) {}

    /**
     * List the treatment plans the authenticated doctor has for a patient.
     */
    public function index(Request $request, int $patient): JsonResponse
    {
        return $this->service->listForDoctor($request->user(), $patient);
    }

    /**
     * Show a single treatment plan's details (stages + appointments + chart).
     */
    public function show(Request $request, int $plan): JsonResponse
    {
        return $this->service->showForDoctor($request->user(), $plan);
    }

    /**
     * Create a new treatment plan (with optional first stage).
     */
    public function store(Request $request): JsonResponse
    {
        return $this->service->create($request->user(), $request->all());
    }

    /**
     * Add a stage to an existing plan.
     */
    public function storeStage(Request $request, int $plan): JsonResponse
    {
        return $this->service->addStage($request->user(), $plan, $request->all());
    }

    /**
     * Create an appointment inside a plan's stage.
     */
    public function storeStageAppointment(Request $request, int $plan, int $stage): JsonResponse
    {
        return $this->service->addStageAppointment($request->user(), $plan, $stage, $request->all());
    }

    /**
     * Get the dental chart belonging to a plan.
     */
    public function chart(Request $request, int $plan): JsonResponse
    {
        return $this->service->getChart($request->user(), $plan);
    }

    /**
     * Update a single tooth on a plan's dental chart.
     */
    public function updateChartTooth(Request $request, int $plan, int $tooth): JsonResponse
    {
        return $this->service->updateChartTooth($request->user(), $plan, $tooth, $request->all());
    }
}

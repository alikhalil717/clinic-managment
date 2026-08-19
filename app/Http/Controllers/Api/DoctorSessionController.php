<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorSessionController extends Controller
{
    public function __construct(
        private readonly DoctorSessionService $service
    ) {}

    /**
     * Start a clinical session for an appointment (status → ongoing).
     */
    public function start(Request $request, int $appointment): JsonResponse
    {
        return $this->service->start($request->user(), $appointment);
    }

    /**
     * Complete the current session (status → finished + session row).
     */
    public function complete(Request $request, int $appointment): JsonResponse
    {
        return $this->service->complete($request->user(), $appointment, $request->all());
    }

    /**
     * Mark a plan stage as completed.
     */
    public function markStageDone(Request $request, int $plan, int $stage): JsonResponse
    {
        return $this->service->markStageDone($request->user(), $plan, $stage);
    }

    /**
     * Finish a whole treatment plan (all stages/appointments done, except current).
     */
    public function finishPlan(Request $request, int $plan): JsonResponse
    {
        return $this->service->finishPlan($request->user(), $plan, $request);
    }

    /**
     * Cancel a treatment plan.
     */
    public function cancelPlan(Request $request, int $plan): JsonResponse
    {
        return $this->service->cancelPlan($request->user(), $plan);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretaryAppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretaryAppointmentController extends Controller
{
    public function __construct(
        private readonly SecretaryAppointmentService $secretaryAppointmentService
    ) {}

    /**
     * Get list of all appointments (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        return $this->secretaryAppointmentService->index();
    }

    /**
     * Display the specified appointment.
     */
    public function show(int $appointment): JsonResponse
    {
        return $this->secretaryAppointmentService->show($appointment);
    }

    /**
     * Update the status and/or reschedule an appointment (pending → confirmed/rejected).
     */
    public function update(Request $request, int $appointment): JsonResponse
    {
        return $this->secretaryAppointmentService->update($appointment, $request->all());
    }

    /**
     * Create a confirmed appointment for a treatment plan stage.
     */
    public function storeStage(Request $request, int $plan, int $stage): JsonResponse
    {
        return $this->secretaryAppointmentService->storeStageAppointment($plan, $stage, $request->all());
    }
}

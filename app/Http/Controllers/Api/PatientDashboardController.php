<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PatientDashboardService;
use Illuminate\Http\JsonResponse;

class PatientDashboardController extends Controller
{
    public function __construct(
        private readonly PatientDashboardService $patientDashboardService
    ) {}

    /**
     * Get patient dashboard: upcoming appointment, reward points,
     * treatment progress, and doctors list.
     */
    public function dashboard(int $patientId): JsonResponse
    {
        return $this->patientDashboardService->dashboard($patientId);
    }

    /**
     * Get upcoming appointments for a patient.
     */
    public function upcomingAppointments(int $patientId): JsonResponse
    {
        return $this->patientDashboardService->upcomingAppointments($patientId);
    }

    /**
     * Get reward points for a patient.
     */
    public function points(int $patientId): JsonResponse
    {
        return $this->patientDashboardService->points($patientId);
    }

    /**
     * Get treatment progress percentage for a patient.
     */
    public function progress(int $patientId): JsonResponse
    {
        return $this->patientDashboardService->progress($patientId);
    }

    /**
     * Get all doctors list.
     */
    public function allDoctors(): JsonResponse
    {
        return $this->patientDashboardService->allDoctors();
    }
}

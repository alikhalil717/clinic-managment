<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PatientAppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientAppointmentController extends Controller
{
    public function __construct(
        private readonly PatientAppointmentService $patientAppointmentService
    ) {}

    /**
     * List all appointments for the logged-in patient.
     */
    public function showAllAppointments(Request $request): JsonResponse
    {
        return $this->patientAppointmentService->index($request);
    }

    /**
     * Show a single appointment's details (scoped to the patient).
     */
    public function showAppointmentDetails(Request $request, int $appointment): JsonResponse
    {
        return $this->patientAppointmentService->show($request, $appointment);
    }

    /**
     * Add a new appointment for the logged-in patient.
     */
    public function addAppointment(Request $request): JsonResponse
    {
        return $this->patientAppointmentService->add($request);
    }

    /**
     * Cancel an appointment belonging to the logged-in patient.
     */
    public function cancelAppointment(Request $request, int $appointment): JsonResponse
    {
        return $this->patientAppointmentService->cancel($request, $appointment);
    }
}

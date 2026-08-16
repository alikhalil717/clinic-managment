<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorAppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAppointmentController extends Controller
{
    public function __construct(
        private readonly DoctorAppointmentService $doctorAppointmentService
    ) {}

    /**
     * List all appointments for the logged-in doctor.
     */
    public function showDoctorAppointments(Request $request): JsonResponse
    {
        return $this->doctorAppointmentService->index($request);
    }

    /**
     * List upcoming appointments for the logged-in doctor.
     */
    public function showDoctorUpcomingAppointments(Request $request): JsonResponse
    {
        return $this->doctorAppointmentService->upcoming($request);
    }

    /**
     * Show a single appointment's details (scoped to the doctor).
     */
    public function showDoctorAppointmentDetails(Request $request, int $appointment): JsonResponse
    {
        return $this->doctorAppointmentService->show($request, $appointment);
    }
}

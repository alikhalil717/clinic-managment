<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminAppointmentService;
use Illuminate\Http\JsonResponse;

class AdminAppointmentController extends Controller
{
    public function __construct(
        private readonly AdminAppointmentService $adminAppointmentService
    ) {}

    /**
     * Get paginated list of all appointments (read-only for admin).
     */
    public function index(): JsonResponse
    {
        return $this->adminAppointmentService->index();
    }

    /**
     * Display the specified appointment.
     */
    public function show(int $appointment): JsonResponse
    {
        return $this->adminAppointmentService->show($appointment);
    }
}

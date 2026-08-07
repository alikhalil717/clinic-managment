<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiagnosticAppointmentRequest;
use App\Http\Requests\StoreNormalAppointmentRequest;
use App\Services\AppointmentBookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentBookingService $appointmentBookingService
    ) {}

    /**
     * Create a diagnostic appointment.
     */
    public function createDiagnostic(StoreDiagnosticAppointmentRequest $request): JsonResponse
    {
        return $this->appointmentBookingService->createDiagnostic($request->validated());
    }

    /**
     * Return all busy diagnostic slots for a given date.
     */
    public function diagnosticBusySlots(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        return $this->appointmentBookingService->diagnosticBusySlots($date);
    }

    /**
     * Create a normal (doctor) appointment.
     */
    public function createNormal(StoreNormalAppointmentRequest $request): JsonResponse
    {
        return $this->appointmentBookingService->createNormal($request->validated());
    }

    /**
     * Return all available slots for a doctor, grouped by working day.
     */
    public function doctorAvailability(int $id): JsonResponse
    {
        return $this->appointmentBookingService->doctorAvailability($id);
    }

    /**
     * Return all busy slots for a specific doctor on a given date.
     */
    public function doctorBusySlots(Request $request, int $id): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        return $this->appointmentBookingService->doctorBusySlots($id, $date);
    }
}

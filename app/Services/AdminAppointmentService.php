<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class AdminAppointmentService
{
    public function index(): JsonResponse
    {
        $appointments = Appointment::with(['doctor.user', 'doctor.workingDays', 'patient.user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                    'doctor' => [
                        'doctor_id' => $appointment->doctor?->doctor_id,
                        'name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                        'specialization' => $appointment->doctor?->specialization,
                        'working_days' => $appointment->doctor?->working_days,
                    ],
                    'patient' => [
                        'patient_id' => $appointment->patient?->patient_id,
                        'name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    /**
     * Display the specified appointment.
     */
    public function show(int $appointmentId): JsonResponse
    {
        $appointment = Appointment::with(['doctor.user', 'patient.user', 'treatmentSessions'])
            ->findOrFail($appointmentId);

        return response()->json([
            'success' => true,
            'data' => [
                'appointment_id' => $appointment->appointment_id,
                'date' => $appointment->date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'status' => $appointment->status,
                'notes' => $appointment->notes,
                'doctor' => [
                    'doctor_id' => $appointment->doctor?->doctor_id,
                    'name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                    'specialization' => $appointment->doctor?->specialization,
                    'working_days' => $appointment->doctor?->working_days,
                ],
                'patient' => [
                    'patient_id' => $appointment->patient?->patient_id,
                    'name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                    'phone' => $appointment->patient?->user?->phone,
                ],
                'treatment_sessions_count' => $appointment->treatmentSessions->count(),
            ],
        ]);
    }
}

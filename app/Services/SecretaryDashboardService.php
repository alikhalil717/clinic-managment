<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class SecretaryDashboardService
{
    /**
     * Get secretary dashboard summary statistics.
     */
    public function dashboard(): JsonResponse
    {
        $totalDoctors = Doctor::query()->count();
        $totalPatients = Patient::query()->count();
        $totalAppointments = Appointment::query()->count();

        $recentAppointments = Appointment::with(['doctor.user', 'patient.user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->take(10)
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'time' => date('h:i A', strtotime($appointment->start_time)),
                    'status' => $appointment->status,
                    'doctor_id' => $appointment->doctor?->doctor_id,
                    'doctor_name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                    'patient_id' => $appointment->patient?->patient_id,
                    'patient_name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_doctors' => $totalDoctors,
                    'total_patients' => $totalPatients,
                    'total_appointments' => $totalAppointments,
                ],
                'recent_appointments' => $recentAppointments,
            ],
        ]);
    }
}
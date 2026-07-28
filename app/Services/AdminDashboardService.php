<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardService
{
    /**
     * Get admin dashboard summary statistics.
     */
    public function dashboard(): JsonResponse
    {
        $totalDoctors = Doctor::query()->count();
        $totalPatients = Patient::query()->count();
        $totalAppointments = Appointment::query()->count();
        $totalTreatmentPlans = TreatmentPlan::query()->count();

        $totalRevenue = Payment::query()
            ->where('is_income', true)
            ->sum('amount');

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
                    'doctor_name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                    'patient_name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                ];
            });

        $appointmentsByStatus = Appointment::query()
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_doctors' => $totalDoctors,
                    'total_patients' => $totalPatients,
                    'total_appointments' => $totalAppointments,
                    'total_treatment_plans' => $totalTreatmentPlans,
                    'total_revenue' => $totalRevenue,
                ],
                'appointments_by_status' => $appointmentsByStatus,
                'recent_appointments' => $recentAppointments,
            ],
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientPoints;
use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;

class PatientDashboardService
{
    /**
     * Get patient dashboard data: upcoming appointment, reward points,
     * treatment progress, and doctors list.
     */
    public function dashboard(int $patientId): JsonResponse
    {
        Patient::query()->findOrFail($patientId);

        return response()->json([
            'status' => 200,
            'success' => true,
            'upcoming_appointment' => $this->getUpcomingAppointmentData($patientId),
            'reward_points' => $this->getTotalPoints($patientId),
            'progress' => $this->getProgressPercentage($patientId),
            'doctors' => $this->getAllDoctorsData(),
        ]);
    }

    /**
     * Get upcoming appointments for a patient.
     */
    public function upcomingAppointments(int $patientId): JsonResponse
    {
        Patient::query()->findOrFail($patientId);

        $appointments = Appointment::with('doctor.user')
            ->where('patient_id', $patientId)
            ->where('date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('date')
            ->orderBy('st art_time')
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'title' => $appointment->notes ?: ($appointment->doctor?->specialization ?? 'Appointment'),
                    'date' => $appointment->date,
                    'time' => date('h:i A', strtotime($appointment->start_time)),
                    'status' => $appointment->status,
                    'doctor_name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                ];
            });

        return response()->json([
            'status' => 200,
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Get reward points for a patient.
     */
    public function points(int $patientId): JsonResponse
    {
        Patient::query()->findOrFail($patientId);

        $totalPoints = $this->getTotalPoints($patientId);

        return response()->json([
            'status' => 200,
            'success' => true,
            'reward_points' => $totalPoints,
        ]);
    }

    /**
     * Get treatment progress percentage for a patient.
     */
    public function progress(int $patientId): JsonResponse
    {
        Patient::query()->findOrFail($patientId);

        $progress = $this->getProgressPercentage($patientId);

        return response()->json([
            'status' => 200,
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * Get all doctors list.
     */
    public function allDoctors(): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'success' => true,
            'doctors' => $this->getAllDoctorsData(),
        ]);
    }

    /**
     * Get the upcoming appointment data for dashboard.
     */
    private function getUpcomingAppointmentData(int $patientId): ?array
    {
        $appointment = Appointment::with('doctor.user')
            ->where('patient_id', $patientId)
            ->where('date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        if (! $appointment) {
            return null;
        }

        return [
            'title' => $appointment->notes ?: ($appointment->doctor?->specialization ?? 'Appointment'),
            'date' => $appointment->date,
            'time' => date('h:i A', strtotime($appointment->start_time)),
        ];
    }

    /**
     * Calculate total reward points for a patient.
     */
    private function getTotalPoints(int $patientId): int
    {
        return (int) PatientPoints::where('patient_id', $patientId)->sum('points');
    }

    /**
     * Calculate average treatment progress percentage for a patient.
     */
    private function getProgressPercentage(int $patientId): int
    {
        return (int) TreatmentPlan::where('patient_id', $patientId)
            ->avg('progress_percentage') ?? 0;
    }

    /**
     * Get all doctors formatted for the dashboard.
     */
    private function getAllDoctorsData(): array
    {
        return Doctor::with('user')
            ->get()
            ->map(function ($doctor) {
                return [
                    'name' => 'Dr. ' . $doctor->user->first_name . ' ' . $doctor->user->last_name,
                    'specialty' => $doctor->specialization,
                    'experience' => $doctor->years_of_experience . '+ Years',
                    'image' => $doctor->user->profile_image
                        ? asset('storage/' . $doctor->user->profile_image)
                        : null,
                ];
            })
            ->toArray();
    }
}

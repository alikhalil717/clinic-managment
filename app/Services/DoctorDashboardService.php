<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorDashboardService
{
    /**
     * Dashboard summary for the authenticated doctor.
     *
     * Response shape matches what the Flutter doctor home screen reads:
     *   doctor, stats, next_appointment, today_schedule, patient_overview
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = Doctor::with('user')->findOrFail($user->user_id);

        $appointmentsCount = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->count();

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::today()->subDay()->toDateString();
        $todayCount = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->whereDate('date', $today)
            ->count();
        $yesterdayCount = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->whereDate('date', $yesterday)
            ->count();
        $trend = $yesterdayCount > 0
            ? (int) round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100)
            : 0;

        return response()->json([
            'success' => true,
            'doctor' => [
                'name' => trim(($doctor->user?->first_name ?? '') . ' ' . ($doctor->user?->last_name ?? '')),
                'image' => $doctor->user?->profile_image
                    ? asset('storage/' . $doctor->user->profile_image)
                    : null,
            ],
            'stats' => [
                'appointments_count' => $appointmentsCount,
                'appointments_trend' => $trend,
                'rating' => (float) ($doctor->rating ?? 0),
                'review_count' => (int) ($doctor->reviews_count ?? 0),
            ],
            'next_appointment' => $this->nextAppointment($doctor),
            'today_schedule' => $this->todaySchedule($doctor, $today),
            'patient_overview' => $this->patientOverview($doctor),
        ]);
    }

    private function nextAppointment(Doctor $doctor): ?array
    {
        $appointment = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->doctor_id)
            ->where('date', '>=', Carbon::today()->toDateString())
            ->whereNotIn('status', ['canceled', 'rejected', 'finished'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();

        if (! $appointment) {
            return null;
        }

        return [
            'patient_id' => $appointment->patient_id,
            'patient_name' => trim(
                ($appointment->patient?->user?->first_name ?? '') . ' ' . ($appointment->patient?->user?->last_name ?? '')
            ),
            'patient_image' => $appointment->patient?->user?->profile_image
                ? asset('storage/' . $appointment->patient->user->profile_image)
                : null,
            'treatment' => $appointment->notes ?? ($appointment->doctor?->specialization ?? 'Appointment'),
            'time' => date('h:i A', strtotime($appointment->start_time)),
            'date' => $appointment->date,
            'room' => '',
        ];
    }

    private function todaySchedule(Doctor $doctor, string $date): array
    {
        return Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->doctor_id)
            ->whereDate('date', $date)
            ->orderBy('start_time')
            ->get()
            ->map(fn(Appointment $appointment) => [
                'time' => date('h:i A', strtotime($appointment->start_time)),
                'patient_name' => trim(
                    ($appointment->patient?->user?->first_name ?? '') . ' ' . ($appointment->patient?->user?->last_name ?? '')
                ),
                'patient_id' => $appointment->patient_id,
                'patient_image' => $appointment->patient?->user?->profile_image
                    ? asset('storage/' . $appointment->patient->user->profile_image)
                    : null,
                'treatment' => $appointment->notes ?? ($appointment->doctor?->specialization ?? 'Appointment'),
                'status' => $appointment->status,
            ])
            ->toArray();
    }

    private function patientOverview(Doctor $doctor): array
    {
        $patientIds = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->orderByDesc('date')
            ->pluck('patient_id')
            ->unique()
            ->take(10);

        $patients = Patient::with(['user'])
            ->whereIn('patient_id', $patientIds)
            ->get();

        return $patients->map(function (Patient $patient) use ($doctor) {
            $lastAppointment = Appointment::query()
                ->where('doctor_id', $doctor->doctor_id)
                ->where('patient_id', $patient->patient_id)
                ->orderByDesc('date')
                ->first();

            return [
                'patient_name' => trim(
                    ($patient->user?->first_name ?? '') . ' ' . ($patient->user?->last_name ?? '')
                ),
                'patient_id' => $patient->patient_id,
                'patient_image' => $patient->user?->profile_image
                    ? asset('storage/' . $patient->user->profile_image)
                    : null,
                'type' => $this->lastTreatmentType($patient->patient_id),
                'last_visit' => $lastAppointment?->date
                    ? Carbon::parse($lastAppointment->date)->format('d M Y')
                    : null,
            ];
        })->toArray();
    }

    private function lastTreatmentType(int $patientId): ?string
    {
        return TreatmentPlan::query()
            ->where('patient_id', $patientId)
            ->latest('created_at')
            ->value('title');
    }
}

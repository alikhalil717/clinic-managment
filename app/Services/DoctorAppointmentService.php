<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAppointmentService
{
    /**
     * All appointments belonging to the authenticated doctor.
     */
    public function index(Request $request): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($request->user()->user_id);

        $appointments = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->doctor_id)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(fn(Appointment $appointment) => $this->format($appointment));

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    /**
     * Upcoming appointments for the authenticated doctor.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($request->user()->user_id);

        $appointments = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->doctor_id)
            ->where('date', '>=', Carbon::today()->toDateString())
            ->whereNotIn('status', ['canceled', 'rejected', 'finished'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn(Appointment $appointment) => $this->format($appointment));

        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Single appointment details (must belong to the doctor).
     */
    public function show(Request $request, int $appointmentId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($request->user()->user_id);

        $appointment = Appointment::with(['patient.user', 'treatmentSessions'])
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($appointmentId);

        $data = $this->format($appointment);
        $data['treatment_sessions_count'] = $appointment->treatmentSessions->count();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function format(Appointment $appointment): array
    {
        return [
            'appointment_id' => $appointment->appointment_id,
            'date' => $appointment->date,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'time' => date('h:i A', strtotime($appointment->start_time)),
            'status' => $appointment->status,
            'appointment_type' => $appointment->appointment_type ?? 'normal',
            'notes' => $appointment->notes,
            'patient' => [
                'patient_id' => $appointment->patient?->patient_id,
                'name' => trim(
                    ($appointment->patient?->user?->first_name ?? '') . ' ' . ($appointment->patient?->user?->last_name ?? '')
                ),
                'phone' => $appointment->patient?->user?->phone,
                'email' => $appointment->patient?->user?->email,
                'image' => $appointment->patient?->user?->profile_image
                    ? asset('storage/' . $appointment->patient->user->profile_image)
                    : null,
            ],
        ];
    }
}

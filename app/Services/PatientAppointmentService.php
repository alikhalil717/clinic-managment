<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientAppointmentService
{
    /**
     * All appointments belonging to the authenticated patient.
     * The app splits these into upcoming / completed / cancelled.
     */
    public function index(Request $request): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $appointments = Appointment::with(['doctor.user'])
            ->where('patient_id', $patient->patient_id)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(fn(Appointment $appointment) => $this->format($appointment));

        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Single appointment details (must belong to the patient).
     */
    public function show(Request $request, int $appointmentId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $appointment = Appointment::with(['doctor.user'])
            ->where('patient_id', $patient->patient_id)
            ->findOrFail($appointmentId);

        return response()->json([
            'success' => true,
            'data' => $this->format($appointment),
        ]);
    }

    /**
     * Add a new appointment for the authenticated patient.
     */
    public function add(Request $request): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctor,doctor_id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $data['doctor_id'],
            'date' => $data['date'],
            'start_time' => $data['time'] . ':00',
            'end_time' => \Carbon\Carbon::parse($data['time'])->addMinutes(30)->format('H:i') . ':00',
            'status' => 'pending',
            'appointment_type' => 'normal',
            'notes' => $data['notes'] ?? 'Normal appointment',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment created successfully.',
            'data' => $this->format($appointment->load('doctor.user')),
        ], 201);
    }

    /**
     * Cancel an appointment belonging to the authenticated patient.
     */
    public function cancel(Request $request, int $appointmentId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $appointment = Appointment::query()
            ->where('patient_id', $patient->patient_id)
            ->findOrFail($appointmentId);

        $appointment->update(['status' => 'canceled']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
        ]);
    }

    private function format(Appointment $appointment): array
    {
        return [
            'appointment_id' => $appointment->appointment_id,
            'title' => $appointment->notes ?: ($appointment->doctor?->specialization ?? 'Appointment'),
            'date' => $appointment->date,
            'time' => date('h:i A', strtotime($appointment->start_time)),
            'status' => $appointment->status,
            'appointment_type' => $appointment->appointment_type ?? 'normal',
            'doctor' => $appointment->doctor?->user
                ? trim(($appointment->doctor->user->first_name ?? '') . ' ' . ($appointment->doctor->user->last_name ?? ''))
                : null,
            'doctor_name' => $appointment->doctor?->user
                ? trim(($appointment->doctor->user->first_name ?? '') . ' ' . ($appointment->doctor->user->last_name ?? ''))
                : null,
            'doctor_id' => $appointment->doctor_id,
            'specialty' => $appointment->doctor?->specialization,
            'location' => 'DentaPrint',
            'room' => $appointment->room ?? '',
        ];
    }
}

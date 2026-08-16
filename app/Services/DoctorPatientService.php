<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorPatientService
{
    /**
     * List of patients the authenticated doctor is (or has been) working with.
     *
     * Response shape matches the Flutter "My Patients" screen:
     *   patient_id, patient_name, patient_image, type, last_visit, status
     */
    public function index(Request $request): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($request->user()->user_id);

        $patientIds = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->orderByDesc('date')
            ->pluck('patient_id')
            ->unique();

        $patients = Patient::with('user')
            ->whereIn('patient_id', $patientIds)
            ->get()
            ->map(function (Patient $patient) use ($doctor) {
                return $this->format($patient, $doctor);
            })
            ->values();

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    }

    private function format(Patient $patient, Doctor $doctor): array
    {
        $lastAppointment = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->where('patient_id', $patient->patient_id)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->first();

        $treatment = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->where('patient_id', $patient->patient_id)
            ->latest('created_at')
            ->value('title');

        return [
            'patient_id' => $patient->patient_id,
            'patient_name' => trim(
                ($patient->user?->first_name ?? '') . ' ' . ($patient->user?->last_name ?? '')
            ),
            'patient_image' => $patient->user?->profile_image
                ? asset('storage/' . $patient->user->profile_image)
                : null,
            'type' => $treatment ?? $lastAppointment?->notes ?? 'General Checkup',
            'last_visit' => $lastAppointment?->date
                ? Carbon::parse($lastAppointment->date)->format('d M Y')
                : null,
            'status' => $lastAppointment?->status === 'finished' ? 'Completed' : 'Active',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class SecretaryPatientService
{
    /**
     * Get list of all patients (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        $patients = Patient::with('user')
            ->get()
            ->map(function ($patient) {
                return [
                    'patient_id' => $patient->patient_id,
                    'first_name' => $patient->user->first_name,
                    'last_name' => $patient->user->last_name,
                    'email' => $patient->user->email,
                    'phone' => $patient->user->phone,
                    'date_of_birth' => $patient->date_of_birth,
                    'profile_image' => $patient->user->profile_image
                        ? asset('storage/' . $patient->user->profile_image)
                        : null,
                    'created_at' => $patient->user->created_at,
                    'is_verified' => $patient->medicalRecords()->exists(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    /**
     * Display the specified patient (read-only for secretary).
     */
    public function show(int $patientId): JsonResponse
    {
        $patient = Patient::with(['user', 'appointments', 'treatmentPlans', 'payments'])
            ->findOrFail($patientId);

        return response()->json([
            'success' => true,
            'data' => [
                'patient_id' => $patient->patient_id,
                'first_name' => $patient->user->first_name,
                'last_name' => $patient->user->last_name,
                'email' => $patient->user->email,
                'phone' => $patient->user->phone,
                'date_of_birth' => $patient->date_of_birth,
                'profile_image' => $patient->user->profile_image
                    ? asset('storage/' . $patient->user->profile_image)
                    : null,
                'appointments_count' => $patient->appointments->count(),
                'treatment_plans_count' => $patient->treatmentPlans->count(),
                'total_payments' => $patient->payments->sum('amount'),
            ],
        ]);
    }
}
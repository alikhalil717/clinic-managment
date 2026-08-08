<?php

namespace App\Services;

use App\Http\Requests\VerifyPatientRequest;
use App\Models\Allergy;
use App\Models\Diagnosis;
use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientVerificationService
{
    /**
     * Complete / verify a patient's profile after their diagnostic
     * appointment by creating (or updating) their medical record,
     * allergies, medical history and diagnoses.
     */
    public function verify(VerifyPatientRequest $request, int $patientId): JsonResponse
    {
        Patient::query()->findOrFail($patientId);

        // The authenticated doctor completes the patient's profile.
        $doctorId = $request->user()->user_id;

        // Create the patient's medical record if it doesn't exist yet.
        $record = MedicalRecord::firstOrCreate(
            ['patient_id' => $patientId],
            ['created_at' => now()]
        );

        // Allergies
        foreach ($request->validated('allergies') ?? [] as $allergy) {
            Allergy::create([
                'record_id' => $record->record_id,
                'allergy_name' => $allergy['name'],
                'severity' => $allergy['severity'] ?? 'low',
                'notes' => $allergy['notes'] ?? '',
            ]);
        }

        // Medical history conditions
        foreach ($request->validated('medical_histories') ?? [] as $history) {
            MedicalHistory::create([
                'record_id' => $record->record_id,
                'condition_name' => $history['condition_name'],
                'description' => $history['description'] ?? '',
                'diagnosed_date' => $history['diagnosed_date'] ?? null,
            ]);
        }

        // Diagnoses
        foreach ($request->validated('diagnoses') ?? [] as $diagnosis) {
            Diagnosis::create([
                'record_id' => $record->record_id,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'session_id' => null,
                'diagnosis_name' => $diagnosis['diagnosis_name'],
                'description' => $diagnosis['description'] ?? null,
                'severity' => $diagnosis['severity'] ?? null,
                'diagnosed_at' => $diagnosis['diagnosed_at'] ?? now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Patient profile verified and medical record completed.',
            'data' => [
                'record_id' => $record->record_id,
                'allergies' => $record->allergies,
                'medical_histories' => $record->histories,
                'diagnoses' => $record->diagnoses,
            ],
        ], 201);
    }
}

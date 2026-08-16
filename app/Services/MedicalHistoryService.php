<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalHistoryService
{
    /**
     * Free-text medical history for the authenticated patient.
     * The Flutter "Medical History" page reads exactly these keys.
     */
    public function index(Request $request): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $record = MedicalRecord::firstOrCreate(
            ['patient_id' => $patient->patient_id],
            ['created_at' => now()]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'allergies' => $record->allergies,
                'chronic_diseases' => $record->chronic_diseases,
                'medications' => $record->medications,
                'doctor_email' => $record->doctor_email,
            ],
        ]);
    }

    /**
     * Save the patient's free-text medical history.
     */
    public function update(Request $request): JsonResponse
    {
        $patient = Patient::query()->findOrFail($request->user()->user_id);

        $record = MedicalRecord::firstOrCreate(
            ['patient_id' => $patient->patient_id],
            ['created_at' => now()]
        );

        $data = $request->validate([
            'allergies' => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'doctor_email' => ['nullable', 'string', 'email'],
        ]);

        $record->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Medical history updated successfully.',
            'data' => [
                'allergies' => $record->allergies,
                'chronic_diseases' => $record->chronic_diseases,
                'medications' => $record->medications,
                'doctor_email' => $record->doctor_email,
            ],
        ]);
    }
}

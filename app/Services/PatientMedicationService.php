<?php

namespace App\Services;

use App\Http\Requests\StorePatientMedicationRequest;
use App\Http\Requests\UpdatePatientMedicationRequest;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Http\JsonResponse;

class PatientMedicationService
{
    /**
     * Prescribe a medication to a patient (doctor action).
     */
    public function store(StorePatientMedicationRequest $request, int $patientId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($patientId);

        $record = MedicalRecord::firstOrCreate(
            ['patient_id' => $patient->patient_id],
            ['created_at' => now()]
        );

        $doctorId = $request->user()->user_id;

        $medication = PatientMedication::create([
            'record_id' => $record->record_id,
            'medication_id' => $request->validated('medication_id'),
            'dosage' => $request->validated('dosage'),
            'frequency' => $request->validated('frequency'),
            'start_date' => $request->validated('start_date'),
            'end_date' => $request->validated('end_date'),
            'prescribed_by' => $doctorId,
            'notes' => $request->validated('notes'),
            'is_current' => $request->validated('is_current', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medication prescribed successfully.',
            'data' => $medication->load('medication', 'prescribedBy.user'),
        ], 201);
    }

    /**
     * List a patient's current medications (patient + doctor view).
     */
    public function index(int $patientId, bool $currentOnly = false): JsonResponse
    {
        $patient = Patient::query()->findOrFail($patientId);

        $record = MedicalRecord::where('patient_id', $patient->patient_id)->first();

        $query = $record
            ? $record->medications()->with('medication', 'prescribedBy.user')
            : PatientMedication::query()->whereRaw('1 = 0');

        if ($currentOnly) {
            $query->where('is_current', true);
        }

        $medications = $query->orderByDesc('is_current')->orderByDesc('start_date')->get();

        return response()->json([
            'success' => true,
            'data' => $medications,
        ]);
    }

    /**
     * Update a patient medication (dosage/frequency/status).
     * Phase I: only the prescribing doctor may update.
     */
    public function update(UpdatePatientMedicationRequest $request, int $patientMedicationId): JsonResponse
    {
        $medication = PatientMedication::query()->findOrFail($patientMedicationId);
        $doctorId = $request->user()->user_id;

        // Phase I: ownership rule — only the prescribing doctor may update.
        if ($medication->prescribed_by !== $doctorId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update medications you prescribed.',
            ], 403);
        }

        $medication->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medication updated successfully.',
            'data' => $medication->load('medication', 'prescribedBy.user'),
        ]);
    }

    /**
     * Mark a patient medication as no longer current (stopped).
     * Phase I: only the prescribing doctor may remove.
     */
    public function destroy(int $patientMedicationId, ?int $doctorId = null): JsonResponse
    {
        $medication = PatientMedication::query()->findOrFail($patientMedicationId);

        // Phase I: ownership rule — only the prescribing doctor may remove.
        if ($doctorId !== null && $medication->prescribed_by !== $doctorId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only remove medications you prescribed.',
            ], 403);
        }

        $medication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medication removed successfully.',
        ]);
    }
}

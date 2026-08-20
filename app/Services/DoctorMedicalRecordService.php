<?php

namespace App\Services;

use App\Models\Allergy;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\DoctorNote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase I — Full medical-record aggregate + doctor CRUD.
 *
 * Provides a single doctor-facing endpoint that returns the entire medical
 * record for a patient (allergies, medical history, diagnoses, current
 * medications, doctor notes) and per-entity CRUD so the doctor can add,
 * edit, or delete entries.
 *
 * Ownership rules: the doctor must be authenticated and the patient must
 * exist (doctor→patient relationship is validated by the caller routes).
 */
class DoctorMedicalRecordService
{
    /**
     * Return the full medical-record aggregate for a patient.
     */
    public function show(User $user, int $patientId): JsonResponse
    {
        $patient = Patient::query()->findOrFail($patientId);

        $record = MedicalRecord::firstOrCreate(
            ['patient_id' => $patient->patient_id],
            ['created_at' => now()]
        );
        
        $record->load([
            'allergies',
            'histories',
            'diagnoses.doctor.user',
            'medications.medication',
            'medications.prescribedBy.user',
            'doctorNotes.doctor.user',
        ]);

        $allergies = $record->getRelation('allergies') ?? collect();
        $histories = $record->getRelation('histories') ?? collect();
        $diagnoses = $record->getRelation('diagnoses') ?? collect();
        $medications = $record->getRelation('medications') ?? collect();
        $doctorNotes = $record->getRelation('doctorNotes') ?? collect();

        return response()->json([
            'success' => true,
            'data' => [
                'record_id' => $record->record_id,
                'patient_id' => $record->patient_id,
                'allergies' => $allergies->map(fn($a) => [
                    'allergy_id' => $a->allergy_id,
                    'allergy_name' => $a->allergy_name,
                    'severity' => $a->severity,
                    'notes' => $a->notes,
                ]),
                'medical_history' => $histories->map(fn($h) => [
                    'history_id' => $h->history_id,
                    'condition_name' => $h->condition_name,
                    'description' => $h->description,
                    'diagnosed_date' => $h->diagnosed_date,
                ]),
                'diagnoses' => $diagnoses->map(fn($d) => [
                    'diagnosis_id' => $d->diagnosis_id,
                    'diagnosis_name' => $d->diagnosis_name,
                    'description' => $d->description,
                    'severity' => $d->severity,
                    'diagnosed_at' => $d->diagnosed_at,
                    'doctor' => $d->doctor ? [
                        'doctor_id' => $d->doctor->doctor_id,
                        'name' => trim(
                            ($d->doctor->user->first_name ?? '') . ' ' . ($d->doctor->user->last_name ?? '')
                        ),
                    ] : null,
                ]),
                'medications' => $medications->map(fn($m) => [
                    'patient_medication_id' => $m->patient_medication_id,
                    'medication_id' => $m->medication_id,
                    'medication' => $m->medication ? [
                        'medication_id' => $m->medication->medication_id,
                        'name' => $m->medication->name,
                        'dosage_form' => $m->medication->dosage_form,
                    ] : null,
                    'dosage' => $m->dosage,
                    'frequency' => $m->frequency,
                    'start_date' => $m->start_date,
                    'end_date' => $m->end_date,
                    'is_current' => $m->is_current,
                    'prescribed_by' => $m->prescribed_by,
                    'prescribed_by_name' => $m->prescribedBy ? trim(
                        ($m->prescribedBy->user->first_name ?? '') . ' ' . ($m->prescribedBy->user->last_name ?? '')
                    ) : null,
                    'notes' => $m->notes,
                ]),
                'doctor_notes' => $doctorNotes->map(fn($n) => [
                    'note_id' => $n->note_id,
                    'title' => $n->title,
                    'note' => $n->note,
                    'note_type' => $n->note_type,
                    'created_at' => $n->created_at,
                    'doctor' => $n->doctor ? [
                        'doctor_id' => $n->doctor->doctor_id,
                        'name' => trim(
                            ($n->doctor->user->first_name ?? '') . ' ' . ($n->doctor->user->last_name ?? '')
                        ),
                    ] : null,
                ]),
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Allergies CRUD
    // ------------------------------------------------------------------

    /**
     * Create an allergy entry on a patient's medical record.
     */
    public function createAllergy(User $user, int $patientId, array $data): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $allergy = Allergy::create([
            'record_id' => $record->record_id,
            'allergy_name' => $data['allergy_name'],
            'severity' => $data['severity'] ?? 'low',
            'notes' => $data['notes'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Allergy added.',
            'data' => $allergy,
        ], 201);
    }

    /**
     * Update an allergy entry.
     */
    public function updateAllergy(User $user, int $patientId, int $allergyId, array $data): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $allergy = Allergy::query()
            ->where('record_id', $record->record_id)
            ->findOrFail($allergyId);

        $allergy->update(array_filter([
            'allergy_name' => $data['allergy_name'] ?? $allergy->allergy_name,
            'severity' => $data['severity'] ?? $allergy->severity,
            'notes' => $data['notes'] ?? $allergy->notes,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Allergy updated.',
            'data' => $allergy,
        ]);
    }

    /**
     * Delete an allergy entry.
     */
    public function deleteAllergy(User $user, int $patientId, int $allergyId): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $allergy = Allergy::query()
            ->where('record_id', $record->record_id)
            ->findOrFail($allergyId);

        $allergy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Allergy deleted.',
        ]);
    }

    // ------------------------------------------------------------------
    // Medical History CRUD
    // ------------------------------------------------------------------

    /**
     * Create a medical history entry.
     */
    public function createHistory(User $user, int $patientId, array $data): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $history = MedicalHistory::create([
            'record_id' => $record->record_id,
            'condition_name' => $data['condition_name'],
            'description' => $data['description'] ?? '',
            'diagnosed_date' => $data['diagnosed_date'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medical history entry added.',
            'data' => $history,
        ], 201);
    }

    /**
     * Update a medical history entry.
     */
    public function updateHistory(User $user, int $patientId, int $historyId, array $data): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $history = MedicalHistory::query()
            ->where('record_id', $record->record_id)
            ->findOrFail($historyId);

        $history->update(array_filter([
            'condition_name' => $data['condition_name'] ?? $history->condition_name,
            'description' => $data['description'] ?? $history->description,
            'diagnosed_date' => $data['diagnosed_date'] ?? $history->diagnosed_date,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Medical history entry updated.',
            'data' => $history,
        ]);
    }

    /**
     * Delete a medical history entry.
     */
    public function deleteHistory(User $user, int $patientId, int $historyId): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $history = MedicalHistory::query()
            ->where('record_id', $record->record_id)
            ->findOrFail($historyId);

        $history->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medical history entry deleted.',
        ]);
    }

    // ------------------------------------------------------------------
    // Diagnosis CRUD
    // ------------------------------------------------------------------

    /**
     * Create a diagnosis entry.
     */
    public function createDiagnosis(User $user, int $patientId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $record = $this->getOrCreateRecord($patientId);

        $diagnosis = Diagnosis::create([
            'record_id' => $record->record_id,
            'patient_id' => $patientId,
            'doctor_id' => $doctor->doctor_id,
            'session_id' => $data['session_id'] ?? null,
            'diagnosis_name' => $data['diagnosis_name'],
            'description' => $data['description'] ?? null,
            'severity' => $data['severity'] ?? null,
            'diagnosed_at' => $data['diagnosed_at'] ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis added.',
            'data' => $diagnosis->load('doctor.user'),
        ], 201);
    }

    /**
     * Update a diagnosis entry.
     */
    public function updateDiagnosis(User $user, int $patientId, int $diagnosisId, array $data): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $diagnosis = Diagnosis::query()
            ->where('record_id', $record->record_id)
            ->findOrFail($diagnosisId);

        $diagnosis->update(array_filter([
            'diagnosis_name' => $data['diagnosis_name'] ?? $diagnosis->diagnosis_name,
            'description' => $data['description'] ?? $diagnosis->description,
            'severity' => $data['severity'] ?? $diagnosis->severity,
            'diagnosed_at' => $data['diagnosed_at'] ?? $diagnosis->diagnosed_at,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis updated.',
            'data' => $diagnosis->load('doctor.user'),
        ]);
    }

    /**
     * Delete a diagnosis entry.
     */
    public function deleteDiagnosis(User $user, int $patientId, int $diagnosisId): JsonResponse
    {
        $record = $this->getOrCreateRecord($patientId);

        $diagnosis = Diagnosis::query()
            ->where('record_id', $record->record_id)
            ->findOrFail($diagnosisId);

        $diagnosis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis deleted.',
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function getOrCreateRecord(int $patientId): MedicalRecord
    {
        $patient = Patient::query()->findOrFail($patientId);

        return MedicalRecord::firstOrCreate(
            ['patient_id' => $patient->patient_id],
            ['created_at' => now()]
        );
    }
}

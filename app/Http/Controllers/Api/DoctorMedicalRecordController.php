<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorMedicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase I — Doctor-facing medical record management.
 *
 * Provides the full medical-record aggregate for a patient and CRUD
 * operations on allergies, medical history, and diagnoses.
 * Medications and doctor notes are managed through their own controllers
 * (PatientMedicationController, DoctorNoteController).
 */
class DoctorMedicalRecordController extends Controller
{
    public function __construct(
        private readonly DoctorMedicalRecordService $service
    ) {}

    /**
     * Get the full medical-record aggregate for a patient.
     * GET /doctor/patients/{patient}/medical-record
     */
    public function show(Request $request, int $patient): JsonResponse
    {
        return $this->service->show($request->user(), $patient);
    }

    // ------------------------------------------------------------------
    // Allergies
    // ------------------------------------------------------------------

    /**
     * Create an allergy entry.
     * POST /doctor/patients/{patient}/medical-record/allergies
     */
    public function storeAllergy(Request $request, int $patient): JsonResponse
    {
        $data = $request->validate([
            'allergy_name' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'in:low,medium,high'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->service->createAllergy($request->user(), $patient, $data);
    }

    /**
     * Update an allergy entry.
     * PUT /doctor/patients/{patient}/medical-record/allergies/{allergy}
     */
    public function updateAllergy(Request $request, int $patient, int $allergy): JsonResponse
    {
        $data = $request->validate([
            'allergy_name' => ['sometimes', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'in:low,medium,high'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->service->updateAllergy($request->user(), $patient, $allergy, $data);
    }

    /**
     * Delete an allergy entry.
     * DELETE /doctor/patients/{patient}/medical-record/allergies/{allergy}
     */
    public function destroyAllergy(Request $request, int $patient, int $allergy): JsonResponse
    {
        return $this->service->deleteAllergy($request->user(), $patient, $allergy);
    }

    // ------------------------------------------------------------------
    // Medical History
    // ------------------------------------------------------------------

    /**
     * Create a medical history entry.
     * POST /doctor/patients/{patient}/medical-record/history
     */
    public function storeHistory(Request $request, int $patient): JsonResponse
    {
        $data = $request->validate([
            'condition_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'diagnosed_date' => ['nullable', 'date'],
        ]);

        return $this->service->createHistory($request->user(), $patient, $data);
    }

    /**
     * Update a medical history entry.
     * PUT /doctor/patients/{patient}/medical-record/history/{history}
     */
    public function updateHistory(Request $request, int $patient, int $history): JsonResponse
    {
        $data = $request->validate([
            'condition_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'diagnosed_date' => ['nullable', 'date'],
        ]);

        return $this->service->updateHistory($request->user(), $patient, $history, $data);
    }

    /**
     * Delete a medical history entry.
     * DELETE /doctor/patients/{patient}/medical-record/history/{history}
     */
    public function destroyHistory(Request $request, int $patient, int $history): JsonResponse
    {
        return $this->service->deleteHistory($request->user(), $patient, $history);
    }

    // ------------------------------------------------------------------
    // Diagnosis
    // ------------------------------------------------------------------

    /**
     * Create a diagnosis entry.
     * POST /doctor/patients/{patient}/medical-record/diagnoses
     */
    public function storeDiagnosis(Request $request, int $patient): JsonResponse
    {
        $data = $request->validate([
            'diagnosis_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['nullable', 'string', 'in:low,medium,high'],
            'session_id' => ['nullable', 'integer', 'exists:treatment_session,session_id'],
            'diagnosed_at' => ['nullable', 'date'],
        ]);

        return $this->service->createDiagnosis($request->user(), $patient, $data);
    }

    /**
     * Update a diagnosis entry.
     * PUT /doctor/patients/{patient}/medical-record/diagnoses/{diagnosis}
     */
    public function updateDiagnosis(Request $request, int $patient, int $diagnosis): JsonResponse
    {
        $data = $request->validate([
            'diagnosis_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['nullable', 'string', 'in:low,medium,high'],
            'diagnosed_at' => ['nullable', 'date'],
        ]);

        return $this->service->updateDiagnosis($request->user(), $patient, $diagnosis, $data);
    }

    /**
     * Delete a diagnosis entry.
     * DELETE /doctor/patients/{patient}/medical-record/diagnoses/{diagnosis}
     */
    public function destroyDiagnosis(Request $request, int $patient, int $diagnosis): JsonResponse
    {
        return $this->service->deleteDiagnosis($request->user(), $patient, $diagnosis);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientMedicationRequest;
use App\Http\Requests\UpdatePatientMedicationRequest;
use App\Services\PatientMedicationService;
use Illuminate\Http\JsonResponse;

class PatientMedicationController extends Controller
{
    public function __construct(private readonly PatientMedicationService $patientMedicationService) {}

    /**
     * Prescribe a medication to a patient (doctor).
     */
    public function store(StorePatientMedicationRequest $request, int $patientId): JsonResponse
    {
        return $this->patientMedicationService->store($request, $patientId);
    }

    /**
     * List a patient's medications.
     */
    public function index(int $patientId): JsonResponse
    {
        return $this->patientMedicationService->index($patientId);
    }

    /**
     * List a patient's current medications only.
     */
    public function current(int $patientId): JsonResponse
    {
        return $this->patientMedicationService->index($patientId, currentOnly: true);
    }

    /**
     * Update a patient medication.
     */
    public function update(UpdatePatientMedicationRequest $request, int $patientMedicationId): JsonResponse
    {
        return $this->patientMedicationService->update($request, $patientMedicationId);
    }

    /**
     * Remove / stop a patient medication.
     */
    public function destroy(int $patientMedicationId): JsonResponse
    {
        return $this->patientMedicationService->destroy($patientMedicationId);
    }
}

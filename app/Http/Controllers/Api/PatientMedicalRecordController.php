<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorMedicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientMedicalRecordController extends Controller
{
    public function __construct(
        private readonly DoctorMedicalRecordService $medicalRecordService
    ) {}

    /**
     * GET /patient/medical-record
     *
     * The SAME aggregate the doctor's record screen shows
     * (allergies, medical history, diagnoses, medications, doctor
     * notes) for the AUTHENTICATED patient. Read-only by design —
     * patients get no CRUD routes here; only doctors can modify
     * a record through the doctor endpoints.
     */
    public function show(Request $request): JsonResponse
    {
        return $this->medicalRecordService->show(
            $request->user(),
            $request->user()->user_id,
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyPatientRequest;
use App\Services\PatientVerificationService;
use Illuminate\Http\JsonResponse;

class PatientVerificationController extends Controller
{
    public function __construct(private readonly PatientVerificationService $patientVerificationService) {}

    /**
     * Verify a patient's profile (complete medical record + allergies)
     * after their first (diagnostic) appointment.
     */
    public function verify(VerifyPatientRequest $request, int $patientId): JsonResponse
    {
        return $this->patientVerificationService->verify($request, $patientId);
    }
}

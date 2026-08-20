<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PatientPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientPaymentController extends Controller
{
    public function __construct(
        private readonly PatientPaymentService $patientPaymentService
    ) {}

    /**
     * Submit an electronic payment for the authenticated patient's sessions.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sessionIds' => ['required', 'array', 'min:1'],
            'sessionIds.*' => ['integer'],
            'password' => ['required', 'string'],
            'paymentCode' => ['sometimes', 'string'],
        ]);

        return $this->patientPaymentService->submit(
            $request->user(),
            $validated['sessionIds'],
            $validated['password'],
            $validated['paymentCode'] ?? null
        );
    }
}
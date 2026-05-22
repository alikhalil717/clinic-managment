<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientLoginRequest;
use App\Http\Requests\PatientRegisterRequest;
use App\Services\PatientAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientAuthController extends Controller
{
    public function __construct(private readonly PatientAuthService $patientAuthService) {}

    public function register(PatientRegisterRequest $request): JsonResponse
    {
        return $this->patientAuthService->register($request);
    }

    public function login(PatientLoginRequest $request): JsonResponse
    {
        return $this->patientAuthService->login($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->patientAuthService->logout($request);
    }
}

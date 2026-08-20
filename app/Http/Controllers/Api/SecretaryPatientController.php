<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretaryPatientService;
use Illuminate\Http\JsonResponse;

class SecretaryPatientController extends Controller
{
    public function __construct(
        private readonly SecretaryPatientService $secretaryPatientService
    ) {}

    /**
     * Get list of all patients (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        return $this->secretaryPatientService->index();
    }

    /**
     * Display the specified patient (read-only for secretary).
     */
    public function show(int $patient): JsonResponse
    {
        return $this->secretaryPatientService->show($patient);
    }
}
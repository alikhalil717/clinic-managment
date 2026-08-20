<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretaryDoctorService;
use Illuminate\Http\JsonResponse;

class SecretaryDoctorController extends Controller
{
    public function __construct(
        private readonly SecretaryDoctorService $secretaryDoctorService
    ) {}

    /**
     * Get list of all doctors (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        return $this->secretaryDoctorService->index();
    }
}
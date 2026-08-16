<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoctorPatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorPatientController extends Controller
{
    public function __construct(
        private readonly DoctorPatientService $doctorPatientService
    ) {}

    /**
     * List the patients the authenticated doctor is working with.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->doctorPatientService->index($request);
    }
}

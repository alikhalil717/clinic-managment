<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MedicalHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalHistoryController extends Controller
{
    public function __construct(
        private readonly MedicalHistoryService $medicalHistoryService
    ) {}

    /**
     * Get the authenticated patient's medical history.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->medicalHistoryService->index($request);
    }

    /**
     * Update the authenticated patient's medical history.
     */
    public function update(Request $request): JsonResponse
    {
        return $this->medicalHistoryService->update($request);
    }
}

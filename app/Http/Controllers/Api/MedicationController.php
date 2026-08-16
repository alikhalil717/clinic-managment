<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicationRequest;
use App\Http\Requests\UpdateMedicationRequest;
use App\Services\MedicationService;
use Illuminate\Http\JsonResponse;

class MedicationController extends Controller
{
    public function __construct(private readonly MedicationService $medicationService) {}

    /**
     * List the medication catalog.
     */
    public function index(): JsonResponse
    {
        return $this->medicationService->index();
    }

    /**
     * Show a single medication.
     */
    public function show(int $medicationId): JsonResponse
    {
        return $this->medicationService->show($medicationId);
    }

    /**
     * Create a medication (admin).
     */
    public function store(StoreMedicationRequest $request): JsonResponse
    {
        return $this->medicationService->store($request);
    }

    /**
     * Update a medication (admin).
     */
    public function update(UpdateMedicationRequest $request, int $medicationId): JsonResponse
    {
        return $this->medicationService->update($request, $medicationId);
    }

    /**
     * Delete a medication (admin).
     */
    public function destroy(int $medicationId): JsonResponse
    {
        return $this->medicationService->destroy($medicationId);
    }
}

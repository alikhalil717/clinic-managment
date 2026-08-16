<?php

namespace App\Services;

use App\Http\Requests\StoreMedicationRequest;
use App\Http\Requests\UpdateMedicationRequest;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;

class MedicationService
{
    /**
     * List all medications (catalog).
     */
    public function index(): JsonResponse
    {
        $medications = Medication::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $medications,
        ]);
    }

    /**
     * Show a single medication.
     */
    public function show(int $medicationId): JsonResponse
    {
        $medication = Medication::query()->findOrFail($medicationId);

        return response()->json([
            'success' => true,
            'data' => $medication,
        ]);
    }

    /**
     * Create a medication in the catalog (admin action).
     */
    public function store(StoreMedicationRequest $request): JsonResponse
    {
        $medication = Medication::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medication created successfully.',
            'data' => $medication,
        ], 201);
    }

    /**
     * Update a medication in the catalog (admin action).
     */
    public function update(UpdateMedicationRequest $request, int $medicationId): JsonResponse
    {
        $medication = Medication::query()->findOrFail($medicationId);

        $medication->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medication updated successfully.',
            'data' => $medication,
        ]);
    }

    /**
     * Soft-disable / remove a medication from the catalog (admin action).
     */
    public function destroy(int $medicationId): JsonResponse
    {
        $medication = Medication::query()->findOrFail($medicationId);

        $medication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medication deleted successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Services\AdminPatientService;
use Illuminate\Http\JsonResponse;

class AdminPatientController extends Controller
{
    public function __construct(
        private readonly AdminPatientService $adminPatientService
    ) {}

    /**
     * Get paginated list of all patients.
     */
    public function index(): JsonResponse
    {
        return $this->adminPatientService->index();
    }

    /**
     * Store a newly created patient.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        return $this->adminPatientService->store($request);
    }

    /**
     * Display the specified patient.
     */
    public function show(int $patient): JsonResponse
    {
        return $this->adminPatientService->show($patient);
    }

    /**
     * Update the specified patient.
     */
    public function update(UpdatePatientRequest $request, int $patient): JsonResponse
    {
        return $this->adminPatientService->update($request, $patient);
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(int $patient): JsonResponse
    {
        return $this->adminPatientService->destroy($patient);
    }
}

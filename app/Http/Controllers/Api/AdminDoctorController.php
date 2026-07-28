<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Services\AdminDoctorService;
use Illuminate\Http\JsonResponse;

class AdminDoctorController extends Controller
{
    public function __construct(
        private readonly AdminDoctorService $adminDoctorService
    ) {}

    /**
     * Get paginated list of all doctors.
     */
    public function index(): JsonResponse
    {
        return $this->adminDoctorService->index();
    }

    /**
     * Store a newly created doctor.
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        return $this->adminDoctorService->store($request);
    }

    /**
     * Display the specified doctor.
     */
    public function show(int $doctor): JsonResponse
    {
        return $this->adminDoctorService->show($doctor);
    }

    /**
     * Update the specified doctor.
     */
    public function update(UpdateDoctorRequest $request, int $doctor): JsonResponse
    {
        return $this->adminDoctorService->update($request, $doctor);
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(int $doctor): JsonResponse
    {
        return $this->adminDoctorService->destroy($doctor);
    }
}

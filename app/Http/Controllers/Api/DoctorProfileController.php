<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorProfileResource;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;

class DoctorProfileController extends Controller
{
    public function show(int $doctorId): JsonResponse
    {
        $doctor = Doctor::with(['user', 'treatmentPlans.case'])
            ->findOrFail($doctorId);

        return response()->json([
            'data' => new DoctorProfileResource($doctor),
        ]);
    }
}

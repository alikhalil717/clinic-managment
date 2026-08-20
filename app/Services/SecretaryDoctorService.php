<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;

class SecretaryDoctorService
{
    /**
     * Get list of all doctors (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        $doctors = Doctor::with('user')
            ->get()
            ->map(function ($doctor) {
                return [
                    'doctor_id' => $doctor->doctor_id,
                    'first_name' => $doctor->user->first_name,
                    'last_name' => $doctor->user->last_name,
                    'email' => $doctor->user->email,
                    'phone' => $doctor->user->phone,
                    'specialization' => $doctor->specialization,
                    'years_of_experience' => $doctor->years_of_experience,
                    'rating' => $doctor->rating,
                    'reviews_count' => $doctor->reviews_count,
                    'about' => $doctor->about,
                    'working_days' => $doctor->working_days,
                    'profile_image' => $doctor->user->profile_image
                        ? asset('storage/' . $doctor->user->profile_image)
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $doctors,
        ]);
    }
}

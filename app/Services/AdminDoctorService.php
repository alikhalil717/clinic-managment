<?php

namespace App\Services;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorProfileResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDoctorService
{
    /**
     * Get paginated list of all doctors.
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
                    'license_number' => $doctor->license_number,
                    'years_of_experience' => $doctor->years_of_experience,
                    'rating' => $doctor->rating,
                    'reviews_count' => $doctor->reviews_count,
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

    /**
     * Store a newly created doctor.
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'Doctor',
            'api_token' => Str::random(60),
        ]);

        $doctor = Doctor::query()->create([
            'doctor_id' => $user->user_id,
            'specialization' => $data['specialization'],
            'license_number' => $data['license_number'],
            'years_of_experience' => $data['years_of_experience'],
            'rating' => 0,
            'reviews_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor created successfully.',
            'data' => new DoctorProfileResource($doctor->load('user')),
        ], 201);
    }

    /**
     * Display the specified doctor.
     */
    public function show(int $doctorId): JsonResponse
    {
        $doctor = Doctor::with('user')->findOrFail($doctorId);

        return response()->json([
            'success' => true,
            'data' => new DoctorProfileResource($doctor),
        ]);
    }

    /**
     * Update the specified doctor.
     */
    public function update(UpdateDoctorRequest $request, int $doctorId): JsonResponse
    {
        $doctor = Doctor::with('user')->findOrFail($doctorId);
        $data = $request->validated();

        $userData = [];
        if (isset($data['first_name'])) {
            $userData['first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $userData['last_name'] = $data['last_name'];
        }
        if (isset($data['email'])) {
            $userData['email'] = $data['email'];
        }
        if (isset($data['phone'])) {
            $userData['phone'] = $data['phone'];
        }
        if (isset($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        if (!empty($userData)) {
            $doctor->user->update($userData);
        }

        $doctorData = [];
        if (isset($data['specialization'])) {
            $doctorData['specialization'] = $data['specialization'];
        }
        if (isset($data['license_number'])) {
            $doctorData['license_number'] = $data['license_number'];
        }
        if (isset($data['years_of_experience'])) {
            $doctorData['years_of_experience'] = $data['years_of_experience'];
        }
        if (isset($data['about'])) {
            $doctorData['about'] = $data['about'];
        }
        if (isset($data['education'])) {
            $doctorData['education'] = $data['education'];
        }
        if (isset($data['certifications'])) {
            $doctorData['certifications'] = $data['certifications'];
        }
        if (isset($data['expertise'])) {
            $doctorData['expertise'] = $data['expertise'];
        }

        if (!empty($doctorData)) {
            $doctor->update($doctorData);
        }

        $doctor->refresh()->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated successfully.',
            'data' => new DoctorProfileResource($doctor),
        ]);
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(int $doctorId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($doctorId);
        $doctor->user()->delete();
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted successfully.',
        ]);
    }
}

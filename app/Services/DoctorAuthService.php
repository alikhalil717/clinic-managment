<?php

namespace App\Services;

use App\Http\Requests\DoctorLoginRequest;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\DoctorProfileResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorAuthService
{
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $doctorData = [];
        if (isset($data['specialization'])) {
            $doctorData['specialization'] = $data['specialization'];
            unset($data['specialization']);
        }
        if (isset($data['license_number'])) {
            $doctorData['license_number'] = $data['license_number'];
            unset($data['license_number']);
        }
        if (isset($data['years_of_experience'])) {
            $doctorData['years_of_experience'] = $data['years_of_experience'];
            unset($data['years_of_experience']);
        }

        if (!empty($doctorData)) {
            Doctor::query()->where('doctor_id', $user->user_id)->update($doctorData);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new DoctorProfileResource(Doctor::query()->find($user->user_id)),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = Doctor::query()->find($user->user_id);

        return response()->json([
            'success' => true,
            'data' => new DoctorProfileResource($doctor),
        ]);
    }
    public function register(DoctorRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'Doctor',
            'api_token' => Str::random(60),
        ]);

        $doctor = Doctor::create([
            'doctor_id' => $user->user_id,
            'specialization' => $data['specialization'],
            'license_number' => $data['license_number'],
            'years_of_experience' => $data['years_of_experience'],
            'rating' => $data['rating'] ?? 0,
            'reviews_count' => $data['reviews_count'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor registered successfully.',
            'token' => $user->api_token,
            'user' => $user,
            'doctor' => $doctor,
        ], 201);
    }

    public function login(DoctorLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'Doctor')
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user->forceFill([
            'api_token' => Str::random(60),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Doctor logged in successfully.',
            'token' => $user->api_token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== 'Doctor') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $user->forceFill([
            'api_token' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Doctor logged out successfully.',
        ]);
    }
}

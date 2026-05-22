<?php

namespace App\Services;

use App\Http\Requests\DoctorLoginRequest;
use App\Http\Requests\DoctorRegisterRequest;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorAuthService
{
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

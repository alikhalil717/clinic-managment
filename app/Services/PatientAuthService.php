<?php

namespace App\Services;

use App\Http\Requests\PatientLoginRequest;
use App\Http\Requests\PatientRegisterRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class PatientAuthService
{
    public function register(PatientRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'Patient',
            'api_token' => Str::random(60),
        ]);

        $patient = Patient::create([
            'patient_id' => $user->user_id,
            'date_of_birth' => $data['date_of_birth'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient registered successfully.',
            'token' => $user->api_token,
            'user' => $user,
            'patient' => $patient,
        ], 201);
    }

    public function login(PatientLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'Patient')
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
            'message' => 'Patient logged in successfully.',
            'token' => $user->api_token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== 'Patient') {
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
            'message' => 'Patient logged out successfully.',
        ]);
    }
}

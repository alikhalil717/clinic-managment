<?php

namespace App\Services;

use App\Http\Requests\PatientLoginRequest;
use App\Http\Requests\PatientRegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\PatientProfileResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class PatientAuthService
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

        if (isset($data['date_of_birth'])) {
            Patient::query()->where('patient_id', $user->user_id)->update([
                'date_of_birth' => $data['date_of_birth'],
            ]);
            unset($data['date_of_birth']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new PatientProfileResource(Patient::query()->find($user->user_id)),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $patient = Patient::query()->find($user->user_id);

        return response()->json([
            'success' => true,
            'data' => new PatientProfileResource($patient),
        ]);
    }
    public function register(PatientRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'patient',
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
            ->where('role', 'patient')
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

        if (! $user instanceof User || $user->role !== 'patient') {
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

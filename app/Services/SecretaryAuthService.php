<?php

namespace App\Services;

use App\Http\Requests\SecretaryLoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\SecretaryProfileResource;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecretaryAuthService
{
    public function login(SecretaryLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'secretary')
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
            'message' => 'Secretary logged in successfully.',
            'token' => $user->api_token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== 'secretary') {
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
            'message' => 'Secretary logged out successfully.',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $secretary = Secretary::query()->find($user->user_id);

        return response()->json([
            'success' => true,
            'data' => new SecretaryProfileResource($secretary),
        ]);
    }

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

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new SecretaryProfileResource(Secretary::query()->find($user->user_id)),
        ]);
    }
}

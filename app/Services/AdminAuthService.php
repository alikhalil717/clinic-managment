<?php

namespace App\Services;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AdminProfileResource;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAuthService
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'Admin')
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
            'message' => 'Admin logged in successfully.',
            'token' => $user->api_token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== 'Admin') {
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
            'message' => 'Admin logged out successfully.',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $admin = Admin::query()->find($user->user_id);

        return response()->json([
            'success' => true,
            'data' => new AdminProfileResource($admin),
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
            'data' => new AdminProfileResource(Admin::query()->find($user->user_id)),
        ]);
    }
}

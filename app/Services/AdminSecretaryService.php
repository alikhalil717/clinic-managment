<?php

namespace App\Services;

use App\Http\Requests\StoreSecretaryRequest;
use App\Http\Requests\UpdateSecretaryRequest;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminSecretaryService
{
    /**
     * List all secretary accounts (with their user info).
     */
    public function index(): JsonResponse
    {
        $secretaries = Secretary::with('user')
            ->get()
            ->map(function (Secretary $secretary) {
                return [
                    'secretary_id' => $secretary->secretary_id,
                    'first_name' => $secretary->user->first_name,
                    'last_name' => $secretary->user->last_name,
                    'email' => $secretary->user->email,
                    'phone' => $secretary->user->phone,
                    'profile_image' => $secretary->user->profile_image
                        ? asset('storage/' . $secretary->user->profile_image)
                        : null,
                    'created_at' => $secretary->user->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $secretaries,
        ]);
    }

    /**
     * Create a secretary account: user (role=secretary) + secretary row.
     * The secretary can then log in with the email/password set here.
     */
    public function store(StoreSecretaryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $profileImage = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profiles', 'public')
            : null;

        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'secretary',
            'profile_image' => $profileImage,
            'created_at' => now(),
        ]);

        Secretary::query()->create([
            'secretary_id' => $user->user_id,
            'shift' => 'morning',
            'office_number' => '0',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Secretary account created successfully. She can now log in with the provided email and password.',
            'data' => [
                'secretary_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => $user->password->unhash(),
                'profile_image' => $profileImage ? asset('storage/' . $profileImage) : null,
            ],
        ], 201);
    }

    /**
     * Update a secretary account (password optional).
     */
    public function update(UpdateSecretaryRequest $request, int $secretaryId): JsonResponse
    {
        $secretary = Secretary::query()->findOrFail($secretaryId);
        $user = $secretary->user;
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        } elseif (!empty($data['remove_profile_image'])) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = null;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Secretary account updated successfully.',
            'data' => [
                'secretary_id' => $secretary->secretary_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => $user->password,
                'profile_image' => $user->profile_image
                    ? asset('storage/' . $user->profile_image)
                    : null,
            ],
        ]);
    }

    /**
     * Delete a secretary account (the user row cascades to the secretary row).
     */
    public function destroy(int $secretaryId): JsonResponse
    {
        $secretary = Secretary::query()->findOrFail($secretaryId);

        if ($secretary->user->profile_image) {
            Storage::disk('public')->delete($secretary->user->profile_image);
        }

        $secretary->user()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Secretary account deleted successfully.',
        ]);
    }
}

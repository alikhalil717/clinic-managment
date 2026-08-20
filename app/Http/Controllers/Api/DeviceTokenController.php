<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Register (or refresh) the authenticated user's device push token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => ['required', 'string'],
            'platform' => ['sometimes', 'string', 'in:android,ios'],
        ]);

        DeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->user_id,
                'device_token' => $validated['device_token'],
            ],
            [
                'platform' => $validated['platform'] ?? null,
                'created_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully.',
        ]);
    }

    /**
     * Unregister the authenticated user's device push token.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => ['required', 'string'],
        ]);

        DeviceToken::query()
            ->where('user_id', $request->user()->user_id)
            ->where('device_token', $validated['device_token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully.',
        ]);
    }
}
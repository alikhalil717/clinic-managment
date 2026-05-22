<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBearerRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Bearer token is required.',
            ], 401);
        }

        $user = User::query()
            ->where('api_token', $token)
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Invalid bearer token.',
            ], 401);
        }

        if ($roles !== [] && ! in_array(strtolower($user->role), array_map('strtolower', $roles), true)) {
            return response()->json([
                'message' => 'You are not authorized to access this resource.',
            ], 403);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}

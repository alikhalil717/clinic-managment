<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SecretaryLoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\SecretaryAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretaryAuthController extends Controller
{
    public function __construct(private readonly SecretaryAuthService $secretaryAuthService) {}

    public function login(SecretaryLoginRequest $request): JsonResponse
    {
        return $this->secretaryAuthService->login($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->secretaryAuthService->logout($request);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->secretaryAuthService->profile($request);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->secretaryAuthService->updateProfile($request);
    }
}

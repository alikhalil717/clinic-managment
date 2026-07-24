<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function __construct(private readonly AdminAuthService $adminAuthService) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {
        return $this->adminAuthService->login($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->adminAuthService->logout($request);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->adminAuthService->profile($request);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->adminAuthService->updateProfile($request);
    }
}

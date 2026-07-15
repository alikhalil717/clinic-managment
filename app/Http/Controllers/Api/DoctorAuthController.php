<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorLoginRequest;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\DoctorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAuthController extends Controller
{
    public function __construct(private readonly DoctorAuthService $doctorAuthService) {}

    public function register(DoctorRegisterRequest $request): JsonResponse
    {
        return $this->doctorAuthService->register($request);
    }

    public function login(DoctorLoginRequest $request): JsonResponse
    {
        return $this->doctorAuthService->login($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->doctorAuthService->logout($request);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->doctorAuthService->profile($request);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->doctorAuthService->updateProfile($request);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSecretaryRequest;
use App\Http\Requests\UpdateSecretaryRequest;
use App\Services\AdminSecretaryService;
use Illuminate\Http\JsonResponse;

class AdminSecretaryController extends Controller
{
    public function __construct(
        private readonly AdminSecretaryService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->service->index();
    }

    public function store(StoreSecretaryRequest $request): JsonResponse
    {
        return $this->service->store($request);
    }

    public function update(UpdateSecretaryRequest $request, int $secretary): JsonResponse
    {
        return $this->service->update($request, $secretary);
    }

    public function destroy(int $secretary): JsonResponse
    {
        return $this->service->destroy($secretary);
    }
}

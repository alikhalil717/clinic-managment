<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CaseFinishRequest;
use App\Services\CaseService;
use Illuminate\Http\JsonResponse;

class CaseController extends Controller
{
    public function __construct(private readonly CaseService $caseService) {}

    /**
     * Mark a case as done and upload the after photo.
     */
    public function finish(CaseFinishRequest $request, int $caseId): JsonResponse
    {
        return $this->caseService->finish($request, $caseId);
    }
}

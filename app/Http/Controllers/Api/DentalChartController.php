<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DentalChartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DentalChartController extends Controller
{
    public function __construct(
        private readonly DentalChartService $dentalChartService
    ) {}

    /**
     * Get the authenticated patient's full dental chart.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->dentalChartService->index($request);
    }
}

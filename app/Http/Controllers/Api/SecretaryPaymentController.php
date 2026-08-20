<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretaryPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretaryPaymentController extends Controller
{
    public function __construct(
        private readonly SecretaryPaymentService $secretaryPaymentService
    ) {}

    /**
     * Get the patient's unpaid invoices.
     */
    public function pendingInvoices(int $patient): JsonResponse
    {
        return $this->secretaryPaymentService->pendingInvoices($patient);
    }

    /**
     * Collect a cash payment for the selected invoices.
     */
    public function store(Request $request, int $patient): JsonResponse
    {
        $validated = $request->validate([
            'session_ids' => ['required', 'array', 'min:1'],
            'session_ids.*' => ['integer'],
            'method' => ['sometimes', 'string', 'in:cash,card,transfer'],
        ]);

        return $this->secretaryPaymentService->collectCash(
            $patient,
            $validated['session_ids'],
            $validated['method'] ?? 'cash',
            $request->user()
        );
    }
}
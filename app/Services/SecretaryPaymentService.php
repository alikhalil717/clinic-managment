<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SecretaryPaymentService
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}
    /**
     * List unpaid invoices (treatment sessions with remaining balance) for a patient.
     */
    public function pendingInvoices(int $patientId): JsonResponse
    {
        $patient = Patient::findOrFail($patientId);

        $invoices = TreatmentSession::with('doctor.user', 'appointment')
            ->where('patient_id', $patientId)
            ->get()
            ->filter(function ($session) {
                return $session->estimated_cost !== null && (float) $session->estimated_cost > 0;
            })
            ->map(function ($session) {
                $estimate = round((float) $session->estimated_cost, 2);
                $paid = round($session->payments->where('is_income', true)->sum('amount'), 2);

                return [
                    'session_id' => $session->session_id,
                    'appointment_id' => $session->appointment_id,
                    'date' => $session->appointment?->date ?? $session->session_date,
                    'doctor_name' => $session->doctor?->user
                        ? $session->doctor->user->first_name . ' ' . $session->doctor->user->last_name
                        : 'N/A',
                    'notes' => $session->notes,
                    'estimated_cost' => $estimate,
                    'total_paid' => $paid,
                    'remaining' => round(max(0, $estimate - $paid), 2),
                ];
            })
            ->filter(fn ($invoice) => $invoice['remaining'] > 0)
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'patient_id' => $patientId,
                'invoices' => $invoices,
                'total_remaining' => round(array_sum(array_column($invoices, 'remaining')), 2),
            ],
        ]);
    }

    /**
     * Collect cash payment: mark the selected patient's invoices as fully paid.
     */
    public function collectCash(int $patientId, array $sessionIds, string $method, ?User $actor = null): JsonResponse
    {
        $patient = Patient::findOrFail($patientId);

        $amountReceived = 0.0;
        $paidCount = 0;

        DB::transaction(function () use ($patientId, $sessionIds, $method, &$amountReceived, &$paidCount) {
            $sessions = TreatmentSession::with('payments')
                ->where('patient_id', $patientId)
                ->whereIn('session_id', $sessionIds)
                ->get();

            foreach ($sessions as $session) {
                $estimate = round((float) $session->estimated_cost, 2);
                if ($estimate <= 0) {
                    continue;
                }

                $paid = round($session->payments->where('is_income', true)->sum('amount'), 2);
                $remaining = round(max(0, $estimate - $paid), 2);
                if ($remaining <= 0) {
                    continue;
                }

                Payment::create([
                    'patient_id' => $patientId,
                    'amount' => $remaining,
                    'method' => $method,
                    'date' => now(),
                    'related_session_id' => $session->session_id,
                    'type' => 'session_payment',
                    'is_income' => true,
                ]);

                $amountReceived += $remaining;
                $paidCount++;
            }
        });

        if ($paidCount > 0) {
            $actorName = $actor ? trim(($actor->first_name ?? '') . ' ' . ($actor->last_name ?? '')) : 'The secretary';

            $this->notifications->notify(
                $patientId,
                'payment',
                'Payment Received',
                "A payment of {$amountReceived} {$method} was received by {$actorName}.",
                null,
                [
                    'amount' => round($amountReceived, 2),
                    'invoices_paid' => $paidCount,
                    'method' => $method,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Cash payment successfully',
            'data' => [
                'amount_received' => round($amountReceived, 2),
                'invoices_paid' => $paidCount,
            ],
        ]);
    }
}
<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\TreatmentSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientPaymentService
{
    /**
     * Submit an electronic payment for the authenticated patient's selected sessions.
     */
    public function submit(User $user, array $sessionIds, string $password, ?string $paymentCode = null): JsonResponse
    {
        if (! Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 401);
        }

        $patientId = $user->user_id;

        $amountReceived = 0.0;
        $paidCount = 0;

        DB::transaction(function () use ($patientId, $sessionIds, &$amountReceived, &$paidCount) {
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
                    'method' => 'card',
                    'date' => now(),
                    'related_session_id' => $session->session_id,
                    'type' => 'session_payment',
                    'is_income' => true,
                ]);

                $amountReceived += $remaining;
                $paidCount++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Electronic payment successful',
            'data' => [
                'amount_received' => round($amountReceived, 2),
                'invoices_paid' => $paidCount,
            ],
        ]);
    }
}
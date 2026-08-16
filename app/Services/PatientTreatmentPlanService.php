<?php

namespace App\Services;

use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PatientTreatmentPlanService
{
    /**
     * Get all treatment plans belonging to the authenticated patient.
     */
    public function listForPatient(User $user): JsonResponse
    {
        $plans = TreatmentPlan::query()
            ->where('patient_id', $user->user_id)
            ->with(['doctor.user', 'case'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($plan) => $this->summary($plan));

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get a single treatment plan's details (must belong to the patient).
     */
    public function showForPatient(User $user, int $planId): JsonResponse
    {
        $plan = TreatmentPlan::query()
            ->where('patient_id', $user->user_id)
            ->with(['doctor.user', 'case', 'stages'])
            ->findOrFail($planId);

        return response()->json([
            'success' => true,
            'data' => $this->details($plan),
        ]);
    }

    /**
     * Compact shape used in the list view.
     */
    private function summary(TreatmentPlan $plan): array
    {
        return [
            'plan_id' => $plan->plan_id,
            'title' => $plan->title,
            'description' => $plan->description,
            'estimated_total_cost' => $plan->estimated_total_cost,
            'actual_total_cost' => $plan->actual_total_cost,
            'progress_percentage' => $plan->progress_percentage,
            'created_at' => $plan->created_at,
            'patient' => [
                'patient_id' => $plan->patient?->patient_id,
                'name' => trim(
                    ($plan->patient?->user?->first_name ?? '') . ' ' . ($plan->patient?->user?->last_name ?? '')
                ),
            ],
            'doctor' => [
                'doctor_id' => $plan->doctor?->doctor_id,
                'name' => 'Dr. ' . trim(
                    ($plan->doctor?->user?->first_name ?? '') . ' ' . ($plan->doctor?->user?->last_name ?? '')
                ),
                'working_days' => $plan->doctor?->working_days,
            ],
            'case' => $this->caseSummary($plan),
        ];
    }

    /**
     * Full shape used in the details view.
     */
    private function details(TreatmentPlan $plan): array
    {
        return [
            'plan_id' => $plan->plan_id,
            'title' => $plan->title,
            'description' => $plan->description,
            'estimated_total_cost' => $plan->estimated_total_cost,
            'actual_total_cost' => $plan->actual_total_cost,
            'progress_percentage' => $plan->progress_percentage,
            'created_at' => $plan->created_at,
            'patient' => [
                'patient_id' => $plan->patient?->patient_id,
                'name' => trim(
                    ($plan->patient?->user?->first_name ?? '') . ' ' . ($plan->patient?->user?->last_name ?? '')
                ),
                'phone' => $plan->patient?->user?->phone,
                'email' => $plan->patient?->user?->email,
            ],
            'doctor' => [
                'doctor_id' => $plan->doctor?->doctor_id,
                'name' => 'Dr. ' . trim(
                    ($plan->doctor?->user?->first_name ?? '') . ' ' . ($plan->doctor?->user?->last_name ?? '')
                ),
                'specialization' => $plan->doctor?->specialization,
                'working_days' => $plan->doctor?->working_days,
            ],
            'case' => $this->caseSummary($plan),
            'stages' => $plan->stages->map(fn($stage) => [
                'stage_id' => $stage->stage_id,
                'stage_name' => $stage->stage_name,
                'description' => $stage->description,
                'status' => $stage->status,
                'estimated_cost' => $stage->estimated_cost,
                'actual_cost' => $stage->actual_cost,
                'start_date' => $stage->start_date,
                'end_date' => $stage->end_date,
            ]),
        ];
    }

    private function caseSummary(TreatmentPlan $plan): ?array
    {
        if (! $plan->case) {
            return null;
        }

        return [
            'case_id' => $plan->case->case_id,
            'title' => $plan->case->title,
            'description' => $plan->case->description ?? null,
            'patient_age' => $plan->case->patient_age,
            'before_photo' => $plan->case->before_photo
                ? asset('storage/' . $plan->case->before_photo)
                : null,
            'after_photo' => $plan->case->after_photo
                ? asset('storage/' . $plan->case->after_photo)
                : null,
            'case_duration' => $plan->case->case_duration,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;

class SecretaryTreatmentPlanService
{
    /**
     * Get list of all treatment plans (read-only for secretary).
     */
    public function index(): JsonResponse
    {
        $plans = TreatmentPlan::with(['patient.user', 'doctor.user', 'doctor.workingDays', 'case'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($plan) {
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
                        'name' => $plan->patient?->user?->first_name . ' ' . $plan->patient?->user?->last_name,
                    ],
                    'doctor' => [
                        'doctor_id' => $plan->doctor?->doctor_id,
                        'name' => 'Dr. ' . $plan->doctor?->user?->first_name . ' ' . $plan->doctor?->user?->last_name,
                        'working_days' => $plan->doctor?->working_days,
                    ],
                    'case' => $plan->case ? [
                        'case_id' => $plan->case->case_id,
                        'title' => $plan->case->title,
                        'patient_age' => $plan->case->patient_age,
                        'before_photo' => $plan->case->before_photo
                            ? asset('storage/' . $plan->case->before_photo)
                            : null,
                        'after_photo' => $plan->case->after_photo
                            ? asset('storage/' . $plan->case->after_photo)
                            : null,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Display the specified treatment plan.
     */
    public function show(int $planId): JsonResponse
    {
        $plan = TreatmentPlan::with([
            'patient.user',
            'doctor.user',
            'case',
            'stages.appointments.doctor.user',
        ])->findOrFail($planId);

        return response()->json([
            'success' => true,
            'data' => [
                'plan_id' => $plan->plan_id,
                'title' => $plan->title,
                'description' => $plan->description,
                'estimated_total_cost' => $plan->estimated_total_cost,
                'actual_total_cost' => $plan->actual_total_cost,
                'progress_percentage' => $plan->progress_percentage,
                'created_at' => $plan->created_at,
                'patient' => [
                    'patient_id' => $plan->patient?->patient_id,
                    'name' => $plan->patient?->user?->first_name . ' ' . $plan->patient?->user?->last_name,
                    'phone' => $plan->patient?->user?->phone,
                    'email' => $plan->patient?->user?->email,
                ],
                'doctor' => [
                    'doctor_id' => $plan->doctor?->doctor_id,
                    'name' => 'Dr. ' . $plan->doctor?->user?->first_name . ' ' . $plan->doctor?->user?->last_name,
                    'specialization' => $plan->doctor?->specialization,
                    'working_days' => $plan->doctor?->working_days,
                ],
                'case' => $plan->case ? [
                    'case_id' => $plan->case->case_id,
                    'title' => $plan->case->title,
                    'patient_age' => $plan->case->patient_age,
                    'before_photo' => $plan->case->before_photo
                        ? asset('storage/' . $plan->case->before_photo)
                        : null,
                    'after_photo' => $plan->case->after_photo
                        ? asset('storage/' . $plan->case->after_photo)
                        : null,
                    'case_duration' => $plan->case->case_duration,
                ] : null,
                'stages' => $plan->stages->map(function ($stage) {
                    return [
                        'stage_id' => $stage->stage_id,
                        'stage_name' => $stage->stage_name,
                        'description' => $stage->description,
                        'status' => $stage->status,
                        'estimated_cost' => $stage->estimated_cost,
                        'actual_cost' => $stage->actual_cost,
                        'start_date' => $stage->start_date,
                        'end_date' => $stage->end_date,
                        'appointments' => $stage->appointments->map(function ($appointment) {
                            return [
                                'appointment_id' => $appointment->appointment_id,
                                'date' => $appointment->date,
                                'start_time' => $appointment->start_time,
                                'end_time' => $appointment->end_time,
                                'status' => $appointment->status,
                                'appointment_type' => $appointment->appointment_type,
                                'notes' => $appointment->notes,
                                'doctor' => $appointment->doctor ? [
                                    'doctor_id' => $appointment->doctor->doctor_id,
                                    'name' => trim(
                                        ($appointment->doctor->user->first_name ?? '') . ' ' . ($appointment->doctor->user->last_name ?? '')
                                    ),
                                ] : null,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }
}

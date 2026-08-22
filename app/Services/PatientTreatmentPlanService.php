<?php

namespace App\Services;

use App\Models\Tooth;
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
            ->with(['doctor.user', 'doctor.workingDays', 'case'])
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
            ->with(['doctor.user', 'doctor.workingDays', 'case', 'stages.appointments.doctor.user', 'dentalChart.teeth.tooth'])
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
                'appointments' => $stage->appointments->map(fn($appointment) => [
                    'appointment_id' => $appointment->appointment_id,
                    'title' => $appointment->notes ?: ($appointment->doctor?->specialization ?? 'Appointment'),
                    'date' => $appointment->date,
                    'time' => date('h:i A', strtotime($appointment->start_time)),
                    'status' => $appointment->status,
                    'appointment_type' => $appointment->appointment_type ?? 'normal',
                    'doctor' => $appointment->doctor?->user
                        ? trim(($appointment->doctor->user->first_name ?? '') . ' ' . ($appointment->doctor->user->last_name ?? ''))
                        : null,
                    'doctor_id' => $appointment->doctor_id,
                    'room' => $appointment->room ?? '',
                ]),
            ]),
            'dental_chart' => $this->dentalChartSummary($plan),
        ];
    }

    /**
     * Maps the DB condition_status enum to the display labels the Flutter
     * patient dental chart renders (see PatientDentalChartScreen.statusColors).
     * Mirrors DentalChartService::STATUS_MAP so plan charts pair with the UI.
     */
    private const STATUS_MAP = [
        'healthy' => 'Healthy',
        'decay' => 'Caries',
        'damaged' => 'Filled',
        'treated' => 'Root Canal',
        'missing' => 'Missing',
    ];

    private function dentalChartSummary(TreatmentPlan $plan): ?array
    {
        $chart = $plan->dentalChart;

        if (! $chart) {
            return null;
        }

        // Build a lookup of the charted teeth keyed by tooth_id.
        $charted = $chart->teeth->keyBy('tooth_id');

        // Return ALL FDI catalog teeth (52), overlaying the charted ones so
        // the patient chart never shows empty/null teeth for a charted plan.
        $teeth = Tooth::query()
            ->orderBy('tooth_id')
            ->get()
            ->map(function (Tooth $tooth) use ($charted) {
                $record = $charted->get($tooth->tooth_id);
                $status = $record?->condition_status ?? 'healthy';

                return [
                    'iso' => $tooth->tooth_code,
                    'tooth_id' => $tooth->tooth_id,
                    'tooth_name' => $tooth->tooth_name,
                    'status' => self::STATUS_MAP[$status] ?? 'Healthy',
                    'notes' => $record?->notes,
                    'treatment_type' => $record?->treatment_type,
                    'severity' => $record?->severity_level,
                    'estimated_price' => $record?->estimated_price,
                ];
            });

        return [
            'chart_id' => $chart->chart_id,
            'teeth' => $teeth,
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

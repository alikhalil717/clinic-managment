<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DentalChart;
use App\Models\DentalChartTooth;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\TreatmentStage;
use App\Models\Tooth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DoctorTreatmentPlanService
{
    /**
     * List treatment plans belonging to the authenticated doctor for a patient.
     */
    public function listForDoctor(User $user, int $patientId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        Patient::query()->findOrFail($patientId);

        $plans = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->where('patient_id', $patientId)
            ->with(['case', 'stages'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(TreatmentPlan $plan) => [
                'plan_id' => $plan->plan_id,
                'title' => $plan->title,
                'description' => $plan->description,
                'estimated_total_cost' => $plan->estimated_total_cost,
                'actual_total_cost' => $plan->actual_total_cost,
                'progress_percentage' => $plan->progress_percentage,
                'created_at' => $plan->created_at,
                'case' => $plan->case ? [
                    'case_id' => $plan->case->case_id,
                    'title' => $plan->case->title,
                    'before_photo' => $plan->case->before_photo,
                    'after_photo' => $plan->case->after_photo,
                ] : null,
                'stages_count' => $plan->stages->count(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get a single treatment plan's details (must belong to the doctor).
     * Mirrors the patient details shape so the doctor Flutter screens can
     * reuse the same parsing: stages[] with their appointments[] plus the
     * plan's OWN dental_chart.
     */
    public function showForDoctor(User $user, int $planId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);

        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->with(['patient.user', 'case', 'stages.appointments.doctor.user', 'dentalChart.teeth.tooth'])
            ->findOrFail($planId);

        return response()->json([
            'success' => true,
            'data' => $this->details($plan),
        ]);
    }

    /**
     * Full shape used in the doctor details view (mirrors the patient
     * treatment-plan details endpoint).
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

    private function dentalChartSummary(TreatmentPlan $plan): ?array
    {
        $chart = $plan->dentalChart;

        if (! $chart) {
            return null;
        }

        // Build a lookup of the charted teeth keyed by tooth_id.
        $charted = $chart->teeth->keyBy('tooth_id');

        // Return ALL FDI catalog teeth (52), overlaying the charted ones so
        // the doctor chart never shows empty/null teeth for a charted plan.
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
                    'status' => $status,
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

    /**
     * Create a new treatment plan (optionally with its first stage).
     */
    public function create(User $user, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);

        $validated = $data['plan'] ?? $data;

        $plan = DB::transaction(function () use ($doctor, $validated) {
            $plan = TreatmentPlan::create([
                'patient_id' => $validated['patient_id'] ?? $validated['patientId'] ?? null,
                'doctor_id' => $doctor->doctor_id,
                'title' => $validated['title'] ?? 'Treatment Plan',
                'description' => $validated['description'] ?? null,
                'estimated_total_cost' => $validated['estimated_total_cost'] ?? 0,
                'actual_total_cost' => $validated['actual_total_cost'] ?? 0,
                'progress_percentage' => $validated['progress_percentage'] ?? 0,
                'created_at' => now(),
            ]);

            // Each plan owns exactly one dental chart.
            DentalChart::create([
                'plan_id' => $plan->plan_id,
                'patient_id' => $plan->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'created_at' => now(),
            ]);

            // Optional first stage.
            if (! empty($validated['stage_name'])) {
                $this->createStage($plan, $validated['stage_name'], $validated['stage_description'] ?? null);
            }

            return $plan;
        });

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan created.',
            'data' => $plan->load('stages', 'dentalChart'),
        ], 201);
    }

    /**
     * Add a stage to an existing plan.
     */
    public function addStage(User $user, int $planId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($planId);

        $stage = $this->createStage(
            $plan,
            $data['stage_name'] ?? 'New Stage',
            $data['stage_description'] ?? null,
            $data['estimated_cost'] ?? null,
            $data['actual_cost'] ?? null,
            $data['status'] ?? 'upcoming',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Stage added.',
            'data' => $stage,
        ], 201);
    }

    /**
     * Create an appointment inside a plan's stage.
     */
    public function addStageAppointment(User $user, int $planId, int $stageId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($planId);

        $stage = $plan->stages()->findOrFail($stageId);

        $appointment = Appointment::create([
            'patient_id' => $plan->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'treatment_plan_id' => $plan->plan_id,
            'treatment_stage_id' => $stage->stage_id,
            'date' => $data['date'] ?? now()->toDateString(),
            'start_time' => ($data['time'] ?? '09:00') . ':00',
            'end_time' => ($data['time'] ?? '09:00') . ':30',
            'status' => $data['status'] ?? 'pending',
            'appointment_type' => 'normal',
            'notes' => $data['notes'] ?? 'Stage appointment',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment added to stage.',
            'data' => $appointment,
        ], 201);
    }

    /**
     * Get the dental chart that belongs to a plan.
     */
    public function getChart(User $user, int $planId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($planId);

        $chart = $plan->dentalChart;

        if (! $chart) {
            $chart = DentalChart::create([
                'plan_id' => $plan->plan_id,
                'patient_id' => $plan->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'created_at' => now(),
            ]);
        }

        $chart->load('teeth.tooth');

        $teeth = Tooth::query()->orderBy('tooth_id')->get()->map(function (Tooth $tooth) use ($chart) {
            $record = $chart->teeth->firstWhere('tooth_id', $tooth->tooth_id);

            return [
                'iso' => $tooth->tooth_code,
                'tooth_id' => $tooth->tooth_id,
                'tooth_name' => $tooth->tooth_name,
                'status' => $record?->condition_status ?? 'healthy',
                'notes' => $record?->notes,
                'treatment_type' => $record?->treatment_type,
                'severity' => $record?->severity_level,
                'estimated_price' => $record?->estimated_price,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'chart_id' => $chart->chart_id,
                'teeth' => $teeth,
            ],
        ]);
    }

    /**
     * Update a single tooth on a plan's dental chart.
     */
    public function updateChartTooth(User $user, int $planId, int $toothId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($planId);

        $chart = $plan->dentalChart ?: DentalChart::create([
            'plan_id' => $plan->plan_id,
            'patient_id' => $plan->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'created_at' => now(),
        ]);

        Tooth::query()->findOrFail($toothId);

        $record = DentalChartTooth::query()->updateOrCreate(
            ['chart_id' => $chart->chart_id, 'tooth_id' => $toothId],
            [
                'condition_status' => $data['status'] ?? $data['condition_status'] ?? 'healthy',
                'treatment_type' => $data['treatment_type'] ?? 'cleaning',
                'treatment_description' => $data['treatment_description'] ?? null,
                'estimated_price' => $data['estimated_price'] ?? null,
                'severity_level' => $data['severity'] ?? $data['severity_level'] ?? 'low',
                'notes' => $data['notes'] ?? null,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Tooth updated.',
            'data' => $record->load('tooth'),
        ], 200);
    }

    private function createStage(
        TreatmentPlan $plan,
        string $name,
        ?string $description = null,
        ?float $estimatedCost = null,
        ?float $actualCost = null,
        string $status = 'upcoming',
        ?string $startDate = null,
        ?string $endDate = null,
    ): TreatmentStage {
        return TreatmentStage::create([
            'plan_id' => $plan->plan_id,
            'stage_name' => $name,
            'description' => $description ?? '',
            'estimated_cost' => $estimatedCost ?? 0,
            'actual_cost' => $actualCost ?? 0,
            'status' => $status,
            'start_date' => $startDate ?? now()->toDateString(),
            'end_date' => $endDate ?? now()->addMonth()->toDateString(),
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CaseModel;
use App\Models\DentalChart;
use App\Models\DentalChartTooth;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\TreatmentStage;
use App\Models\Tooth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorTreatmentPlanService
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}

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
            ->map(fn(TreatmentPlan $plan) => $this->summary($plan));

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * List ALL treatment plans the authenticated doctor manages (Phase E:
     * the doctor "Plans" tab). Each entry includes the patient name so the
     * list screen can show who the plan belongs to.
     */
    public function listAllForDoctor(User $user): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);

        $plans = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->with(['patient.user', 'case', 'stages'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(TreatmentPlan $plan) => $this->summary($plan, true));

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Compact shape used in plan lists. Includes Phase F status + derived
     * stage counters so the UI can render "Stages Done / All Stages".
     */
    private function summary(TreatmentPlan $plan, bool $includePatient = false): array
    {
        $data = [
            'plan_id' => $plan->plan_id,
            'title' => $plan->title,
            'description' => $plan->description,
            'estimated_total_cost' => $plan->estimated_total_cost,
            'actual_total_cost' => $plan->actual_total_cost,
            'progress_percentage' => $plan->progress_percentage,
            'status' => $plan->status ?? 'in_progress',
            'stages_done' => $plan->stagesDoneCount(),
            'stages_total' => $plan->stagesTotalCount(),
            'stages_count' => $plan->stages->count(),
            'created_at' => $plan->created_at,
            'case' => $plan->case ? [
                'case_id' => $plan->case->case_id,
                'title' => $plan->case->title,
                'before_photo' => $plan->case->before_photo,
                'after_photo' => $plan->case->after_photo,
            ] : null,
        ];

        if ($includePatient) {
            $data['patient'] = [
                'patient_id' => $plan->patient?->patient_id,
                'name' => trim(
                    ($plan->patient?->user?->first_name ?? '') . ' ' . ($plan->patient?->user?->last_name ?? '')
                ),
            ];
        }

        return $data;
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
            'status' => $plan->status ?? 'in_progress',
            'stages_done' => $plan->stagesDoneCount(),
            'stages_total' => $plan->stagesTotalCount(),
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
     *
     * Phase H: "Case is a treatment plan" — a 1:1 `cases` row is created
     * alongside the plan and dental chart, with the doctor-provided
     * `before_photo` (required to initialize the case).
     */
    public function create(User $user, Request $request): JsonResponse
    {
        $doctor = Doctor::query()->with('user')->findOrFail($user->user_id);

        $data = $request->all();
        $validated = $data['plan'] ?? $data;

        $beforePhoto = $request->hasFile('before_photo')
            ? $request->file('before_photo')->store('cases/before', 'public')
            : ($validated['before_photo'] ?? null);

        $plan = DB::transaction(function () use ($doctor, $validated, $beforePhoto) {
            $plan = TreatmentPlan::create([
                'patient_id' => $validated['patient_id'] ?? $validated['patientId'] ?? null,
                'doctor_id' => $doctor->doctor_id,
                'title' => $validated['title'] ?? 'Treatment Plan',
                'description' => $validated['description'] ?? null,
                'estimated_total_cost' => $validated['estimated_total_cost'] ?? 0,
                'actual_total_cost' => $validated['actual_total_cost'] ?? 0,
                'progress_percentage' => $validated['progress_percentage'] ?? 0,
                'status' => 'in_progress',
                'created_at' => now(),
            ]);

            // Each plan owns exactly one dental chart.
            DentalChart::create([
                'plan_id' => $plan->plan_id,
                'patient_id' => $plan->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'created_at' => now(),
            ]);

            // Phase H: Case = treatment plan. Initialize the 1:1 case row with
            // the doctor-uploaded before photo (optional at creation time).
            CaseModel::create([
                'treatment_plan_id' => $plan->plan_id,
                'title' => $validated['title'] ?? 'Treatment Plan',
                'patient_age' => $validated['patient_age'] ?? null,
                'before_photo' => $beforePhoto,
                'created_at' => now(),
            ]);

            // Optional first stage.
            if (! empty($validated['stage_name'])) {
                $this->createStage($plan, $validated['stage_name'], $validated['stage_description'] ?? null);
            }

            return $plan;
        });

        $doctorName = trim(($doctor->user?->first_name ?? '') . ' ' . ($doctor->user?->last_name ?? ''));

        $this->notifications->notify(
            $plan->patient_id,
            'treatment',
            'New Treatment Plan',
            "Dr. {$doctorName} created a new treatment plan \"{$plan->title}\" for you.",
            $plan->plan_id,
            $plan->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan created.',
            'data' => $plan->load('stages', 'dentalChart', 'case'),
        ], 201);
    }

    /**
     * Add a stage to an existing plan.
     * Guard (Phase F): only the owning doctor may edit, and only while the
     * plan is In Progress.
     */
    public function addStage(User $user, int $planId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($planId);

        if (! $plan->isInProgress()) {
            return response()->json([
                'success' => false,
                'message' => 'Only in-progress treatment plans can be changed.',
            ], 422);
        }

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

        $this->notifications->notify(
            $plan->patient_id,
            'treatment',
            'New Treatment Stage',
            "A new stage \"{$stage->stage_name}\" was added to your treatment plan \"{$plan->title}\".",
            $plan->plan_id,
            ['plan' => $plan->toArray(), 'stage' => $stage->toArray()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Stage added.',
            'data' => $stage,
        ], 201);
    }

    /**
     * Create an appointment inside a plan's stage.
     *
     * NOTE (Phase F/H): adding appointments to a treatment plan is the
     * secretary's job and is NOT implemented for doctors — always 422.
     */
    public function addStageAppointment(User $user, int $planId, int $stageId, array $data): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "Adding appointments to a treatment plan is the secretary's job.",
        ], 422);
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
     * Guard (Phase F/H): only the owning doctor may edit, and only while the
     * plan is In Progress (read-only otherwise).
     */
    public function updateChartTooth(User $user, int $planId, int $toothId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($planId);

        if (! $plan->isInProgress()) {
            return response()->json([
                'success' => false,
                'message' => 'Dental chart is read-only for finished/cancelled plans.',
            ], 422);
        }

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

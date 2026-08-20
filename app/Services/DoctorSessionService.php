<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\TreatmentPlan;
use App\Models\TreatmentSession;
use App\Models\TreatmentStage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Phase F — clinical session lifecycle + treatment-plan status rules.
 *
 * Guards enforced here (mirror the requirements in prompt.md):
 *  - The doctor can only start a session once the appointment time has arrived.
 *  - Starting a session sets the appointment to "ongoing"; session details can
 *    only be added after that.
 *  - A session can only be completed while the appointment is "ongoing".
 *  - Only the owning doctor may mutate a plan; only "in_progress" plans can be
 *    changed.
 *  - A plan may only be finished when every stage is completed and every linked
 *    appointment is finished/completed — except the current appointment.
 */
class DoctorSessionService
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}

    /**
     * Start a clinical session for an appointment.
     * Sets the appointment status to "ongoing" (idempotent if already ongoing).
     */
    public function start(User $user, int $appointmentId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $appointment = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($appointmentId);

        // The appointment time must have arrived.
        $startsAt = Carbon::parse($appointment->date . ' ' . $appointment->start_time);
        if ($startsAt->gt(Carbon::now())) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment time has not arrived yet.',
            ], 422);
        }

        if ($appointment->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'This appointment has already finished.',
            ], 422);
        }

        $appointment->update(['status' => 'ongoing']);

        $this->notifications->notify(
            $appointment->patient_id,
            'appointment',
            'Session Started',
            "Your session on {$appointment->date} at {$appointment->start_time} has started.",
            $appointment->appointment_id,
            $appointment->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Session started.',
            'data' => $this->formatAppointment($appointment),
        ]);
    }

    /**
     * Complete the current clinical session for an appointment.
     * Requires the appointment to be "ongoing"; sets it to "finished" and
     * creates the treatment_session row.
     */
    public function complete(User $user, int $appointmentId, array $data): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $appointment = Appointment::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($appointmentId);

        if ($appointment->status !== 'ongoing') {
            return response()->json([
                'success' => false,
                'message' => 'Session not started — appointment must be ongoing.',
            ], 422);
        }

        $appointment->update(['status' => 'finished']);

        $session = TreatmentSession::create([
            'appointment_id' => $appointment->appointment_id,
            'plan_id' => $appointment->treatment_plan_id,
            'doctor_id' => $doctor->doctor_id,
            'patient_id' => $appointment->patient_id,
            'session_date' => now()->toDateString(),
            'estimated_cost' => $data['estimated_cost'] ?? null,
            'notes' => $data['notes'] ?? $appointment->notes ?? null,
        ]);

        $this->notifications->notify(
            $appointment->patient_id,
            'treatment',
            'Session Completed',
            'Your session has been completed' . ($session->estimated_cost ? " — estimated cost: {$session->estimated_cost}." : '.'),
            $appointment->appointment_id,
            [
                'appointment' => $appointment->toArray(),
                'session' => $session->toArray(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Session completed.',
            'data' => $this->formatAppointment($appointment),
        ]);
    }

    /**
     * Mark a single stage as completed.
     */
    public function markStageDone(User $user, int $planId, int $stageId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = $this->inProgressPlanForDoctor($doctor->doctor_id, $planId);

        $stage = $plan->stages()->findOrFail($stageId);
        $stage->update(['status' => 'completed']);

        $this->notifications->notify(
            $plan->patient_id,
            'treatment',
            'Stage Completed',
            "Stage \"{$stage->stage_name}\" of your treatment plan \"{$plan->title}\" has been completed.",
            $plan->plan_id,
            ['plan' => $plan->toArray(), 'stage' => $stage->toArray()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Stage marked as done.',
            'data' => $stage,
        ]);
    }

    /**
     * Finish the whole treatment plan.
     *
     * Guard: every stage must be completed and every linked appointment must be
     * finished/completed — except the appointment passed as `current_appointment_id`
     * (the one just being completed in the current session).
     *
     * Phase H: when finishing, the doctor may provide the Case `after_photo`
     * (+ optional case info) which is stored on the plan's 1:1 `cases` row.
     */
    public function finishPlan(User $user, int $planId, Request $request): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = $this->inProgressPlanForDoctor($doctor->doctor_id, $planId);

        $data = $request->all();
        $currentAppointmentId = $data['current_appointment_id'] ?? null;

        // All stages must be completed.
        $openStages = $plan->stages()
            ->where('status', '!=', 'completed')
            ->get();

        if ($openStages->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot finish the plan — there are still unfinished stages.',
                'data' => [
                    'open_stages' => $openStages->map(fn($s) => $s->stage_name),
                ],
            ], 422);
        }

        // All linked appointments must be finished/completed (except the current one).
        $openAppointments = Appointment::query()
            ->where('treatment_plan_id', $plan->plan_id)
            ->whereNotIn('status', ['finished', 'completed'])
            ->when($currentAppointmentId, fn($q) => $q->where('appointment_id', '!=', $currentAppointmentId))
            ->get();

        if ($openAppointments->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot finish the plan — there are still unfinished appointments.',
                'data' => [
                    'open_appointments' => $openAppointments->map(fn($a) => $a->appointment_id),
                ],
            ], 422);
        }

        DB::transaction(function () use ($plan, $request, $data) {
            $plan->update([
                'status' => 'finished',
                'progress_percentage' => 100,
            ]);

            // Phase H: finalize the case — upload after photo + remaining info.
            if ($plan->case) {
                $afterPhoto = $request->hasFile('after_photo')
                    ? $request->file('after_photo')->store('cases/after', 'public')
                    : ($data['after_photo'] ?? $plan->case->after_photo);

                $plan->case()->update([
                    'title' => $data['case_title'] ?? $plan->case->title,
                    'description' => $data['case_description'] ?? $plan->case->description,
                    'patient_age' => $data['patient_age'] ?? $plan->case->patient_age,
                    'after_photo' => $afterPhoto,
                    'created_at' => now(), // used for case_duration
                ]);
            }
        });

        $this->notifications->notify(
            $plan->patient_id,
            'treatment',
            'Treatment Plan Finished',
            "Your treatment plan \"{$plan->title}\" has been completed. Congratulations!",
            $plan->plan_id,
            $plan->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan finished.',
            'data' => $plan->load('stages', 'patient', 'doctor', 'case'),
        ]);
    }

    /**
     * Cancel a treatment plan.
     */
    public function cancelPlan(User $user, int $planId): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($user->user_id);
        $plan = $this->inProgressPlanForDoctor($doctor->doctor_id, $planId);

        $plan->update([
            'status' => 'cancelled',
            'progress_percentage' => 0,
        ]);

        $this->notifications->notify(
            $plan->patient_id,
            'treatment',
            'Treatment Plan Cancelled',
            "Your treatment plan \"{$plan->title}\" has been cancelled.",
            $plan->plan_id,
            $plan->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan cancelled.',
            'data' => $plan->load('stages', 'patient', 'doctor'),
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Fetch an In Progress plan that belongs to the given doctor, or 404.
     */
    private function inProgressPlanForDoctor(int $doctorId, int $planId): TreatmentPlan
    {
        $plan = TreatmentPlan::query()
            ->where('doctor_id', $doctorId)
            ->with('stages')
            ->findOrFail($planId);

        if (! $plan->isInProgress()) {
            abort(422, 'Only in-progress treatment plans can be changed.');
        }

        return $plan;
    }

    private function formatAppointment(Appointment $appointment): array
    {
        return [
            'appointment_id' => $appointment->appointment_id,
            'date' => $appointment->date,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'status' => $appointment->status,
            'appointment_type' => $appointment->appointment_type ?? 'normal',
            'notes' => $appointment->notes,
            'treatment_plan_id' => $appointment->treatment_plan_id,
            'treatment_stage_id' => $appointment->treatment_stage_id,
            'patient' => [
                'patient_id' => $appointment->patient?->patient_id,
                'name' => trim(
                    ($appointment->patient?->user?->first_name ?? '') . ' ' . ($appointment->patient?->user?->last_name ?? '')
                ),
            ],
        ];
    }
}

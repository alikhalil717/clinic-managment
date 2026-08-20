<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\TreatmentStage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SecretaryAppointmentService
{
    /**
     * Clinic time slots used by the secretary scheduler.
     */
    public const CLINIC_SLOTS = [
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
    ];
    public function index(): JsonResponse
    {
        $appointments = Appointment::with(['doctor.user', 'patient.user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                    'doctor' => [
                        'doctor_id' => $appointment->doctor?->doctor_id,
                        'name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                        'specialization' => $appointment->doctor?->specialization,
                        'working_days' => $appointment->doctor?->working_days,
                    ],
                    'patient' => [
                        'patient_id' => $appointment->patient?->patient_id,
                        'name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    /**
     * Display the specified appointment for the secretary.
     */
    public function show(int $appointmentId): JsonResponse
    {
        $appointment = Appointment::with(['doctor.user', 'patient.user', 'treatmentSessions.payments'])
            ->findOrFail($appointmentId);

        $isCompleted = in_array(strtolower($appointment->status ?? ''), ['finished', 'completed']);

        $billing = null;
        if ($isCompleted) {
            $sessions = $appointment->treatmentSessions;
            $totalDue = round($sessions->sum('estimated_cost'), 2);
            $totalPaid = round($sessions->flatMap(fn($s) => $s->payments)->sum('amount'), 2);

            $methods = $sessions
                ->flatMap(fn($s) => $s->payments)
                ->pluck('method')
                ->unique()
                ->values()
                ->all();

            $billing = [
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'remaining' => round(max(0, $totalDue - $totalPaid), 2),
                'payment_status' => $totalPaid <= 0
                    ? 'unpaid'
                    : ($totalPaid >= $totalDue ? 'paid' : 'partial'),
                'methods' => array_map(fn($m) => in_array($m, ['card', 'transfer'], true) ? 'electronic' : 'cash', $methods),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'appointment_id' => $appointment->appointment_id,
                'date' => $appointment->date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'status' => $appointment->status,
                'notes' => $appointment->notes,
                'doctor' => [
                    'doctor_id' => $appointment->doctor?->doctor_id,
                    'name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                    'specialization' => $appointment->doctor?->specialization,
                    'working_days' => $appointment->doctor?->working_days,
                ],
                'patient' => [
                    'patient_id' => $appointment->patient?->patient_id,
                    'name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                    'phone' => $appointment->patient?->user?->phone,
                ],
                'treatment_sessions_count' => $appointment->treatmentSessions->count(),
                'billing' => $billing,
            ],
        ]);
    }

    /**
     * Update an appointment (status change and/or reschedule) — readonly→ confirmed/rejected.
     */
    public function update(int $appointmentId, array $data): JsonResponse
    {
        $appointment = Appointment::with(['doctor.user', 'patient.user'])
            ->findOrFail($appointmentId);

        $status = strtolower($data['status'] ?? '');

        if ($status === '') {
            return response()->json([
                'success' => false,
                'message' => 'Status is required.',
            ], 422);
        }

        if (! in_array($status, ['confirmed', 'rejected'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Only confirmed or rejected are allowed.',
            ], 422);
        }

        $currentStatus = strtolower($appointment->status ?? '');

        if (! in_array($currentStatus, ['pending', 'confirmed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or confirmed appointments can be edited.',
            ], 422);
        }

        // الموعد المؤكد لا يُغيّر حالته بل يُعاد جدولته فقط بنفس الحالة
        if ($currentStatus === 'confirmed' && $status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'A confirmed appointment can only be rescheduled, not rejected.',
            ], 422);
        }

        $updateData = ['status' => $status];

        // Reschedule data (optional — used by the edit modal)
        if (array_key_exists('date', $data) || array_key_exists('slots', $data)) {
            $validator = Validator::make($data, [
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'slots' => ['required', 'array', 'min:1'],
                'slots.*' => ['required', 'string', 'in:' . implode(',', self::CLINIC_SLOTS)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reschedule data.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $slots = array_values(array_unique($data['slots']));
            sort($slots);

            $indices = array_map(fn($slot) => (int) array_search($slot, self::CLINIC_SLOTS, true), $slots);

            // الشرائح المحددة يجب أن تكون متتالية في الترتيب
            if (max($indices) - min($indices) + 1 !== count($indices)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected time slots must be consecutive.',
                ], 422);
            }

            // فحص التضارب مع مواعيد نفس الدكتور في نفس التاريخ
            // (الموعد ذو end_time <= start_time يُعامل كمدة 30 دقيقة)
            $proposedStart = $slots[0];
            $proposedEnd = Carbon::parse(end($slots))->addMinutes(30)->format('H:i');
            $conflict = Appointment::query()
                ->where('appointment_id', '!=', $appointmentId)
                ->where('doctor_id', $appointment->doctor_id)
                ->where('date', $data['date'])
                ->whereNotIn('status', ['canceled', 'rejected'])
                ->where(function ($query) use ($proposedStart, $proposedEnd) {
                    $query->whereRaw('start_time < ?', [$proposedEnd])
                        ->whereRaw('IF(end_time > start_time, end_time, DATE_ADD(start_time, INTERVAL 30 MINUTE)) > ?', [$proposedStart]);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected time conflicts with an existing appointment.',
                ], 422);
            }

            $updateData = array_merge($updateData, [
                'date' => $data['date'],
                'start_time' => reset($slots),
                'end_time' => @Carbon::parse(end($slots))->addMinutes(30)->format('H:i'),
            ]);
        }

        $appointment->update($updateData);
        $appointment->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully.',
            'data' => [
                'appointment_id' => $appointment->appointment_id,
                'date' => $appointment->date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'status' => $appointment->status,
                'patient' => [
                    'patient_id' => $appointment->patient?->patient_id,
                    'name' => $appointment->patient?->user?->first_name . ' ' . $appointment->patient?->user?->last_name,
                ],
                'doctor' => [
                    'doctor_id' => $appointment->doctor?->doctor_id,
                    'name' => $appointment->doctor?->user?->first_name . ' ' . $appointment->doctor?->user?->last_name,
                ],
            ],
        ]);
    }

    /**
     * Create a confirmed appointment for a treatment plan stage.
     */
    public function storeStageAppointment(int $planId, int $stageId, array $data): JsonResponse
    {
        $validator = Validator::make($data, [
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*' => ['required', 'string', 'in:' . implode(',', self::CLINIC_SLOTS)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid appointment data.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $stage = TreatmentStage::with('plan')
            ->where('plan_id', $planId)
            ->findOrFail($stageId);

        $slots = array_values(array_unique($data['slots']));
        sort($slots);

        $indices = array_map(fn($slot) => (int) array_search($slot, self::CLINIC_SLOTS, true), $slots);

        // الشرائح المحددة يجب أن تكون متتالية في الترتيب
        if (max($indices) - min($indices) + 1 !== count($indices)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slots must be consecutive.',
            ], 422);
        }

        $start = $slots[0];
        $end = Carbon::parse(end($slots))->addMinutes(30)->format('H:i');

        // فحص التضارب مع مواعيد نفس الدكتور في نفس التاريخ
        $conflict = Appointment::query()
            ->where('doctor_id', $stage->plan->doctor_id)
            ->where('date', $data['date'])
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->where(function ($query) use ($start, $end) {
                $query->whereRaw('start_time < ?', [$end])
                    ->whereRaw('IF(end_time > start_time, end_time, DATE_ADD(start_time, INTERVAL 30 MINUTE)) > ?', [$start]);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'The selected time conflicts with an existing appointment.',
            ], 422);
        }

        $appointment = Appointment::create([
            'patient_id' => $stage->plan->patient_id,
            'doctor_id' => $stage->plan->doctor_id,
            'treatment_plan_id' => $stage->plan_id,
            'treatment_stage_id' => $stage->stage_id,
            'date' => $data['date'],
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'confirmed',
            'appointment_type' => 'normal',
            'notes' => 'Stage: ' . $stage->stage_name . ' | ' . $stage->plan->title,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment confirmed successfully',
            'data' => [
                'appointment_id' => $appointment->appointment_id,
                'date' => $appointment->date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'status' => $appointment->status,
            ],
        ]);
    }
}

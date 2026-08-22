<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AppointmentBookingService
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}

    /**
     * Diagnostic working window (09:00–16:00) as per the requirements.
     */
    private const DIAGNOSTIC_START = '09:00';
    private const DIAGNOSTIC_END = '16:00';

    /**
     * Slot length in minutes.
     */
    private const SLOT_MINUTES = 30;

    /**
     * Create a diagnostic appointment.
     */
    public function createDiagnostic(array $data): JsonResponse
    {
        Patient::query()->findOrFail($data['patient_id']);

        $date = $data['date'];
        $time = $data['time'];

        // Validate diagnostic working hours
        if ($time < self::DIAGNOSTIC_START || $time > self::DIAGNOSTIC_END) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnostic appointments are available between 09:00 and 16:00.',
            ], 422);
        }

        // Check slot is not busy (any doctor, diagnostic slots are clinic-wide)
        if ($this->isSlotBusy(null, $date, $time)) {
            return response()->json([
                'success' => false,
                'message' => "The slot {$time} on {$date} is already booked.",
            ], 409);
        }

        $appointment = Appointment::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => null,
            'date' => $date,
            'start_time' => $time . ':00',
            'end_time' => $this->addMinutes($time, self::SLOT_MINUTES),
            'status' => 'pending',
            'appointment_type' => 'diagnostic',
            'notes' => 'Diagnostic appointment',
        ]);

        $this->notifications->notify(
            $appointment->patient_id,
            'appointment',
            'Appointment Requested',
            "Your diagnostic appointment on {$date} at {$time} has been requested. We'll confirm it soon.",
            $appointment->appointment_id,
            $appointment->toArray()
        );

        return response()->json([
            'success' => true,
            'appointment_type' => 'diagnostic',
            'date' => $date,
            'time' => $time,
            'message' => "We'll send you a reminder.",
            'appointment_id' => $appointment->appointment_id,
        ], 201);
    }

    /**
     * Create a normal (doctor) appointment.
     *
     * When the booking belongs to a treatment-plan stage, `treatment_plan_id`
     * and `treatment_stage_id` are stored and the "diagnostic-first" gate is
     * skipped (the plan itself is the clinical context). Standalone bookings
     * (no stage) still require a prior diagnostic so the doctor can verify
     * the patient's profile.
     */
    public function createNormal(array $data): JsonResponse
    {
        $patient = Patient::with('user')->findOrFail($data['patient_id']);
        $doctor = Doctor::with(['user', 'workingDays', 'workingHours'])->findOrFail($data['doctor_id']);

        $planId = $data['treatment_plan_id'] ?? null;
        $stageId = $data['treatment_stage_id'] ?? null;

        if ($stageId !== null) {
            // Stage-scoped booking: stage must belong to the given plan.
            $stage = DB::table('treatment_stage')
                ->where('stage_id', $stageId)
                ->where('plan_id', $planId)
                ->first();

            if (! $stage) {
                return response()->json([
                    'success' => false,
                    'message' => 'The stage does not belong to the given treatment plan.',
                ], 422);
            }
        } else {
            // Standalone booking: a patient's first appointment must be a
            // diagnostic one, so the doctor can verify their profile
            // (medical record + allergies) before they can book a normal
            // (treatment) appointment.
            $hasPriorAppointment = Appointment::query()
                ->where('patient_id', $data['patient_id'])
                ->whereNotIn('status', ['canceled', 'rejected'])
                ->exists();

            if (! $hasPriorAppointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must book a diagnostic appointment first so the doctor can verify your profile.',
                ], 422);
            }
        }

        $date = $data['date'];
        $time = $data['time'];

        // Doctor must work on this day
        $day = strtolower(Carbon::parse($date)->format('l'));
        $hours = $doctor->workingHoursForDay($day);

        if ($hours === null) {
            return response()->json([
                'success' => false,
                'message' => "Doctor does not work on {$day}.",
            ], 422);
        }

        // Time must be inside the doctor's working hours
        if ($time < $hours['start'] || $time > $hours['end']) {
            return response()->json([
                'success' => false,
                'message' => "Doctor working hours on {$day} are {$hours['start']}–{$hours['end']}.",
            ], 422);
        }

        // Check the slot is not busy for this doctor
        if ($this->isSlotBusy($doctor->doctor_id, $date, $time)) {
            return response()->json([
                'success' => false,
                'message' => "The slot {$time} on {$date} is already booked.",
            ], 409);
        }

        $appointment = Appointment::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $doctor->doctor_id,
            'treatment_plan_id' => $planId,
            'treatment_stage_id' => $stageId,
            'date' => $date,
            'start_time' => $time . ':00',
            'end_time' => $this->addMinutes($time, self::SLOT_MINUTES),
            'status' => 'pending',
            'appointment_type' => 'normal',
            'notes' => $data['notes'] ?? 'Normal appointment',
        ]);

        $doctorName = trim(($doctor->user?->first_name ?? '') . ' ' . ($doctor->user?->last_name ?? ''));
        $patientName = trim(($patient->user?->first_name ?? '') . ' ' . ($patient->user?->last_name ?? ''));

        $this->notifications->notify(
            $appointment->patient_id,
            'appointment',
            'Appointment Requested',
            "Your appointment with Dr. {$doctorName} on {$date} at {$time} has been requested. We'll confirm it soon.",
            $appointment->appointment_id,
            $appointment->toArray()
        );

        $this->notifications->notify(
            $doctor->doctor_id,
            'appointment',
            'New Appointment Request',
            "Patient {$patientName} requested an appointment with you on {$date} at {$time}.",
            $appointment->appointment_id,
            $appointment->toArray()
        );

        return response()->json([
            'success' => true,
            'appointment_type' => 'normal',
            'date' => $date,
            'time' => $time,
            'doctor' => [
                'id' => $doctor->doctor_id,
                'name' => 'Dr. ' . ($doctor->user?->first_name ?? '') . ' ' . ($doctor->user?->last_name ?? ''),
                'specialty' => $doctor->specialization,
            ],
            'message' => "We'll send you a reminder.",
            'appointment_id' => $appointment->appointment_id,
        ], 201);
    }

    /**
     * Return all busy diagnostic slots for a given date.
     */
    public function diagnosticBusySlots(string $date): JsonResponse
    {
        $busy = Appointment::query()
            ->whereDate('date', $date)
            ->where('appointment_type', 'diagnostic')
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->get()
            ->flatMap(fn($appointment) => $this->busySlotsForAppointment($appointment))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'date' => $date,
            'busy_slots' => $busy,
        ]);
    }

    /**
     * Return all busy slots for a specific doctor on a given date.
     */
    public function doctorBusySlots(int $doctorId, string $date): JsonResponse
    {
        Doctor::query()->findOrFail($doctorId);

        $busy = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->get()
            ->flatMap(fn($appointment) => $this->busySlotsForAppointment($appointment))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'doctor_id' => $doctorId,
            'date' => $date,
            'busy_slots' => $busy,
        ]);
    }

    /**
     * Return the busy slots per day for a doctor over the next 30 days.
     *
     * @return array<int, array{date: string, busy_slots: string[]}>
     */
    public function doctorAvailability(int $doctorId): JsonResponse
    {
        $doctor = Doctor::with('user')->findOrFail($doctorId);

        $start = Carbon::today();
        $end = Carbon::today()->addDays(30);

        // Pre-fetch busy appointments for the whole range (avoid N+1).
        $busyByDate = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['canceled', 'rejected'])
            ->get()
            ->groupBy(fn($a) => $a->date);

        $availability = [];

        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();

            $busySlots = ($busyByDate[$date] ?? collect())
                ->flatMap(fn($appointment) => $this->busySlotsForAppointment($appointment))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $availability[] = [
                'date' => $date,
                'busy_slots' => $busySlots,
            ];

            $cursor->addDay();
        }

        return response()->json([
            'success' => true,
            'doctor_id' => $doctorId,
            'availability' => $availability,
        ]);
    }

    /**
     * Generate 30-minute slots between start and end times ("HH:MM").
     *
     * @return string[] e.g. ["10:00", "10:30", "11:00", ...]
     */
    private function generateSlots(string $start, string $end): array
    {
        $slots = [];
        $current = Carbon::createFromFormat('H:i', $start);
        $endTime = Carbon::createFromFormat('H:i', $end);

        while ($current <= $endTime) {
            $slots[] = $current->format('H:i');
            $current->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    /**
     * Expand an appointment into the 30-minute slot start times it occupies,
     * so a 09:30–10:30 appointment marks both 09:30 and 10:00 as busy.
     *
     * @return string[] e.g. ["09:30", "10:00"]
     */
    private function busySlotsForAppointment(Appointment $appointment): array
    {
        $start = Carbon::createFromFormat('H:i:s', $appointment->start_time);
        $end = Carbon::createFromFormat('H:i:s', $appointment->end_time);

        $slots = [];

        while ($start->lt($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    /**
     * Check whether a slot is busy (overlaps any appointment's time range) (overlaps any appointment's time range).
     *
     * @param int|null $doctorId null = diagnostic (clinic-wide)
     */
    private function isSlotBusy(?int $doctorId, string $date, string $time): bool
    {
        $start = $time . ':00';
        $end = $this->addMinutes($time, self::SLOT_MINUTES);

        $query = Appointment::query()
            ->whereDate('date', $date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->whereNotIn('status', ['canceled', 'rejected']);

        if ($doctorId !== null) {
            $query->where('doctor_id', $doctorId);
        }

        return $query->exists();
    }

    /**
     * Add minutes to a "HH:MM" string.
     */
    private function addMinutes(string $time, int $minutes): string
    {
        return Carbon::createFromFormat('H:i', $time)
            ->addMinutes($minutes)
            ->format('H:i') . ':00';
    }
}

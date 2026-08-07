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
     */
    public function createNormal(array $data): JsonResponse
    {
        Patient::query()->findOrFail($data['patient_id']);
        $doctor = Doctor::with('user')->findOrFail($data['doctor_id']);

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
            'date' => $date,
            'start_time' => $time . ':00',
            'end_time' => $this->addMinutes($time, self::SLOT_MINUTES),
            'status' => 'pending',
            'appointment_type' => 'normal',
            'notes' => 'Normal appointment',
        ]);

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
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
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
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
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
                ->pluck('start_time')
                ->map(fn($t) => substr($t, 0, 5))
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
     * Check whether a slot is busy.
     *
     * @param int|null $doctorId null = diagnostic (clinic-wide)
     */
    private function isSlotBusy(?int $doctorId, string $date, string $time): bool
    {
        $start = $time . ':00';

        $query = Appointment::query()
            ->whereDate('date', $date)
            ->where('start_time', $start)
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

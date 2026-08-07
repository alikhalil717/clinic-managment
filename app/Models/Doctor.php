<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends ClinicModel
{
    protected $table = 'doctor';

    protected $primaryKey = 'doctor_id';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'education' => 'array',
            'certifications' => 'array',
            'expertise' => 'array',
            'working_days' => 'array',
            'working_hours' => 'array',
        ];
    }

    /**
     * Get the working hours for a given day name (e.g. "saturday").
     * Falls back to 09:00–17:00 when the doctor has no explicit schedule.
     *
     * @return array{start: string, end: string}|null
     */
    public function workingHoursForDay(string $day): ?array
    {
        $day = strtolower($day);

        if (! in_array($day, $this->working_days ?? [], true)) {
            return null;
        }

        $hours = $this->working_hours ?? [];

        if (isset($hours[$day]) && isset($hours[$day]['start']) && isset($hours[$day]['end'])) {
            return [
                'start' => $hours[$day]['start'],
                'end' => $hours[$day]['end'],
            ];
        }

        // Default working hours fallback
        return ['start' => '09:00', 'end' => '17:00'];
    }

    /**
     * Check whether the doctor works on a given date (YYYY-MM-DD).
     */
    public function isWorkingOn(string $date): bool
    {
        $day = strtolower(\Carbon\Carbon::parse($date)->format('l')); // e.g. "Saturday"

        return $this->workingHoursForDay($day) !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'doctor_id', 'doctor_id');
    }

    public function treatmentSessions(): HasMany
    {
        return $this->hasMany(TreatmentSession::class, 'doctor_id', 'doctor_id');
    }

    public function toothConditions(): HasMany
    {
        return $this->hasMany(ToothCondition::class, 'doctor_id', 'doctor_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(DoctorPayout::class, 'doctor_id', 'doctor_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'doctor_id', 'doctor_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'doctor_id', 'doctor_id');
    }
}

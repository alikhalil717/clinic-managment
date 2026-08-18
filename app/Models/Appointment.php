<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends ClinicModel
{
    protected $table = 'appointment';

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'treatment_plan_id',
        'treatment_stage_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'appointment_type',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id', 'plan_id');
    }

    public function treatmentStage(): BelongsTo
    {
        return $this->belongsTo(TreatmentStage::class, 'treatment_stage_id', 'stage_id');
    }

    public function treatmentSessions(): HasMany
    {
        return $this->hasMany(TreatmentSession::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Scope: appointments on a given date that block a slot
     * (pending/confirmed/ongoing only).
     */
    public function scopeBusyOn($query, string $date)
    {
        return $query->whereDate('date', $date)
            ->whereNotIn('status', ['canceled', 'rejected']);
    }

    /**
     * Scope: appointments for a specific doctor on a given date that block a slot.
     */
    public function scopeBusyForDoctor($query, int $doctorId, string $date)
    {
        return $query->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->whereNotIn('status', ['canceled', 'rejected']);
    }
}

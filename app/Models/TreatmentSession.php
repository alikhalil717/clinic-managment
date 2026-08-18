<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentSession extends ClinicModel
{
    protected $table = 'treatment_session';

    protected $primaryKey = 'session_id';

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'plan_id', 'plan_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function toothConditions(): HasMany
    {
        return $this->hasMany(ToothCondition::class, 'session_id', 'session_id');
    }

    public function treatmentDetails(): HasMany
    {
        return $this->hasMany(TreatmentDetails::class, 'session_id', 'session_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'related_session_id', 'session_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(DoctorPayout::class, 'session_id', 'session_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'session_id', 'session_id');
    }
}

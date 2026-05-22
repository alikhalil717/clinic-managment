<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends ClinicModel
{
    protected $table = 'patient';

    protected $primaryKey = 'patient_id';

    public $incrementing = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'patient_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'patient_id', 'patient_id');
    }

    public function treatmentSessions(): HasMany
    {
        return $this->hasMany(TreatmentSession::class, 'patient_id', 'patient_id');
    }

    public function toothConditions(): HasMany
    {
        return $this->hasMany(ToothCondition::class, 'patient_id', 'patient_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'patient_id', 'patient_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'patient_id', 'patient_id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(PatientPoints::class, 'patient_id', 'patient_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'patient_id', 'patient_id');
    }
}
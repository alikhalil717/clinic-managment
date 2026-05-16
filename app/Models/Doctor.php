<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends ClinicModel
{
    protected $table = 'doctor';

    protected $primaryKey = 'doctor_id';

    public $incrementing = false;

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
}
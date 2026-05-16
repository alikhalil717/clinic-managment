<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends ClinicModel
{
    protected $table = 'treatment_plan';

    protected $primaryKey = 'plan_id';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TreatmentStage::class, 'plan_id', 'plan_id');
    }
}
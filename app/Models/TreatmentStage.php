<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentStage extends ClinicModel
{
    protected $table = 'treatment_stage';

    protected $primaryKey = 'stage_id';

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'plan_id', 'plan_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'treatment_stage_id', 'stage_id');
    }
}

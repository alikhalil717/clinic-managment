<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientPoints extends ClinicModel
{
    protected $table = 'patient_points';

    protected $primaryKey = 'point_id';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }
}
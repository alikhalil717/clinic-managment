<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToothCondition extends ClinicModel
{
    protected $table = 'tooth_condition';

    protected $primaryKey = 'condition_id';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(Tooth::class, 'tooth_id', 'tooth_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TreatmentSession::class, 'session_id', 'session_id');
    }
}
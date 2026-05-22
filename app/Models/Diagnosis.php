<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends ClinicModel
{
    protected $table = 'diagnosis';

    protected $primaryKey = 'diagnosis_id';

    public function record(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
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

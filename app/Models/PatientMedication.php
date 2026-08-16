<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedication extends ClinicModel
{
    protected $table = 'patient_medication';

    protected $primaryKey = 'patient_medication_id';

    protected $fillable = [
        'record_id',
        'medication_id',
        'dosage',
        'frequency',
        'start_date',
        'end_date',
        'prescribed_by',
        'notes',
        'is_current',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'medication_id', 'medication_id');
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by', 'doctor_id');
    }
}

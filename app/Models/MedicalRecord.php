<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends ClinicModel
{
    protected $table = 'medical_record';

    protected $primaryKey = 'record_id';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(MedicalHistory::class, 'record_id', 'record_id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class, 'record_id', 'record_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'record_id', 'record_id');
    }

    /**
     * Medications currently prescribed to the patient.
     */
    public function medications(): HasMany
    {
        return $this->hasMany(PatientMedication::class, 'record_id', 'record_id');
    }

    /**
     * Notes written by doctors on the patient's record.
     */
    public function doctorNotes(): HasMany
    {
        return $this->hasMany(DoctorNote::class, 'record_id', 'record_id');
    }
}

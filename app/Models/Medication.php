<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends ClinicModel
{
    protected $table = 'medication';

    protected $primaryKey = 'medication_id';

    public function patientMedications(): HasMany
    {
        return $this->hasMany(PatientMedication::class, 'medication_id', 'medication_id');
    }
}

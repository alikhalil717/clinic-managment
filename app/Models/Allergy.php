<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allergy extends ClinicModel
{
    protected $table = 'allergies';

    protected $primaryKey = 'allergy_id';

    public function record(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalHistory extends ClinicModel
{
    protected $table = 'medical_history';

    protected $primaryKey = 'history_id';

    public function record(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }
}
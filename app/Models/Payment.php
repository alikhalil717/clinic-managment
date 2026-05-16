<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends ClinicModel
{
    protected $table = 'payment';

    protected $primaryKey = 'payment_id';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function relatedSession(): BelongsTo
    {
        return $this->belongsTo(TreatmentSession::class, 'related_session_id', 'session_id');
    }
}
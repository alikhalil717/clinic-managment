<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentDetails extends ClinicModel
{
    protected $table = 'treatment_details';

    protected $primaryKey = 'detail_id';

    public function session(): BelongsTo
    {
        return $this->belongsTo(TreatmentSession::class, 'session_id', 'session_id');
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(Tooth::class, 'tooth_id', 'tooth_id');
    }
}
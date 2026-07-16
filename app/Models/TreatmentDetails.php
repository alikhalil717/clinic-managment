<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentDetails extends ClinicModel
{
    protected $table = 'treatment_details';

    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'session_id',
        'tooth_id',
        'previous_condition',
        'new_condition',
        'cost',
        'before_photo',
        'after_photo',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TreatmentSession::class, 'session_id', 'session_id');
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(Tooth::class, 'tooth_id', 'tooth_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPayout extends ClinicModel
{
    protected $table = 'doctor_payout';

    protected $primaryKey = 'payout_id';

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TreatmentSession::class, 'session_id', 'session_id');
    }
}
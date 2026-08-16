<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNote extends ClinicModel
{
    protected $table = 'doctor_note';

    protected $primaryKey = 'note_id';

    protected $fillable = [
        'record_id',
        'doctor_id',
        'title',
        'note',
        'note_type',
        'created_at',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}

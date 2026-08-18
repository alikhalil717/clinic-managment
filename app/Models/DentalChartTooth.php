<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalChartTooth extends ClinicModel
{
    protected $table = 'dental_chart_tooth';

    protected $primaryKey = 'id';

    protected $fillable = [
        'chart_id',
        'tooth_id',
        'condition_status',
        'treatment_type',
        'treatment_description',
        'estimated_price',
        'severity_level',
        'notes',
        'updated_at',
    ];

    public function chart(): BelongsTo
    {
        return $this->belongsTo(DentalChart::class, 'chart_id', 'chart_id');
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(Tooth::class, 'tooth_id', 'tooth_id');
    }
}

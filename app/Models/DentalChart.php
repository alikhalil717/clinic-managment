<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DentalChart extends ClinicModel
{
    protected $table = 'dental_chart';

    protected $primaryKey = 'chart_id';

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'plan_id', 'plan_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function teeth(): HasMany
    {
        return $this->hasMany(DentalChartTooth::class, 'chart_id', 'chart_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseModel extends ClinicModel
{
    // "Case" is a reserved word in PHP, so we name the class CaseModel
    protected $table = 'cases';

    protected $primaryKey = 'case_id';

    protected function casts(): array
    {
        return [
            'patient_age' => 'integer',
        ];
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id', 'plan_id');
    }

    /**
     * Duration is calculated from the treatment plan creation date
     * until the case's created_at (which gets updated on finish).
     */
    public function getCaseDurationAttribute(): ?string
    {
        if ($this->after_photo && $this->treatmentPlan) {
            $start = \Carbon\Carbon::parse($this->treatmentPlan->created_at);
            $end = \Carbon\Carbon::parse($this->created_at);

            return $start->diffInDays($end) . ' days';
        }

        return null;
    }
}

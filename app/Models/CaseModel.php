<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseModel extends ClinicModel
{
    // "Case" is a reserved word in PHP, so we name the class CaseModel
    protected $table = 'cases';

    protected $primaryKey = 'case_id';

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id', 'plan_id');
    }
}

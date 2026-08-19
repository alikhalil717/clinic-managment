<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TreatmentPlan extends ClinicModel
{
    protected $table = 'treatment_plan';

    protected $primaryKey = 'plan_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'title',
        'description',
        'estimated_total_cost',
        'actual_total_cost',
        'progress_percentage',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'integer',
            'status' => 'string',
        ];
    }

    // ---- Status helpers (Phase F) ----

    public function isInProgress(): bool
    {
        return ($this->status ?? 'in_progress') === 'in_progress';
    }

    public function isFinished(): bool
    {
        return ($this->status ?? '') === 'finished';
    }

    /**
     * Count of completed stages (status == 'completed').
     */
    public function stagesDoneCount(): int
    {
        return $this->stages
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Total stages count.
     */
    public function stagesTotalCount(): int
    {
        return $this->stages->count();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TreatmentStage::class, 'plan_id', 'plan_id');
    }

    public function case(): HasOne
    {
        return $this->hasOne(CaseModel::class, 'treatment_plan_id', 'plan_id');
    }

    public function dentalChart(): HasOne
    {
        return $this->hasOne(DentalChart::class, 'plan_id', 'plan_id');
    }
}

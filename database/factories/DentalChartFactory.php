<?php

namespace Database\Factories;

use App\Models\DentalChart;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DentalChart>
 */
class DentalChartFactory extends Factory
{
    protected $model = DentalChart::class;

    public function definition(): array
    {
        return [
            'plan_id' => TreatmentPlan::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'created_at' => now(),
        ];
    }
}

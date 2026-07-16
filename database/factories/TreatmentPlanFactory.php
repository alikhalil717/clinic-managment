<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentPlan>
 */
class TreatmentPlanFactory extends Factory
{
    protected $model = TreatmentPlan::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'estimated_total_cost' => fake()->randomFloat(2, 100, 5000),
            'actual_total_cost' => fake()->randomFloat(2, 100, 5000),
            'progress_percentage' => fake()->numberBetween(0, 100),
            'created_at' => now(),
        ];
    }
}

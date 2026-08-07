<?php

namespace Database\Factories;

use App\Models\TreatmentPlan;
use App\Models\TreatmentStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentStage>
 */
class TreatmentStageFactory extends Factory
{
    protected $model = TreatmentStage::class;

    public function definition(): array
    {
        return [
            'plan_id' => TreatmentPlan::factory(),
            'stage_name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'estimated_cost' => fake()->randomFloat(2, 50, 2500),
            'actual_cost' => fake()->randomFloat(2, 50, 2500),
            'status' => fake()->randomElement(['upcoming', 'in_progress', 'completed']),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
        ];
    }
}

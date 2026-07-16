<?php

namespace Database\Factories;

use App\Models\CaseModel;
use App\Models\TreatmentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseModel>
 */
class CaseModelFactory extends Factory
{
    protected $model = CaseModel::class;

    public function definition(): array
    {
        return [
            'treatment_plan_id' => TreatmentPlan::factory(),
            'before_photo' => 'cases/before/' . fake()->uuid() . '.jpg',
            'after_photo' => null,
            'status' => 'in-progress',
            'created_at' => now(),
        ];
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'after_photo' => 'cases/after/' . fake()->uuid() . '.jpg',
            'status' => 'done',
        ]);
    }
}

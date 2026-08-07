<?php

namespace Database\Factories;

use App\Models\Allergy;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allergy>
 */
class AllergyFactory extends Factory
{
    protected $model = Allergy::class;

    public function definition(): array
    {
        return [
            'record_id' => MedicalRecord::factory(),
            'allergy_name' => fake()->word(),
            'severity' => fake()->randomElement(['low', 'medium', 'high']),
            'notes' => fake()->sentence(),
        ];
    }
}

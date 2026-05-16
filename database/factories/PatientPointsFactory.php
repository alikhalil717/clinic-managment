<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\PatientPoints;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientPoints>
 */
class PatientPointsFactory extends Factory
{
    protected $model = PatientPoints::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'points' => fake()->numberBetween(-50, 100),
            'source' => fake()->randomElement(['Session', 'Payment', 'Rating', 'Offer', 'Manual']),
            'related_id' => null,
            'description' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
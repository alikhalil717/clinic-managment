<?php

namespace Database\Factories;

use App\Models\Tooth;
use App\Models\TreatmentDetails;
use App\Models\TreatmentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentDetails>
 */
class TreatmentDetailsFactory extends Factory
{
    protected $model = TreatmentDetails::class;

    public function definition(): array
    {
        return [
            'session_id' => TreatmentSession::factory(),
            'tooth_id' => Tooth::factory(),
            'previous_condition' => fake()->word(),
            'new_condition' => fake()->word(),
            'cost' => fake()->randomFloat(2, 10, 1000),
        ];
    }
}
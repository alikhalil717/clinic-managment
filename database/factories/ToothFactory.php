<?php

namespace Database\Factories;

use App\Models\Tooth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tooth>
 */
class ToothFactory extends Factory
{
    protected $model = Tooth::class;

    public function definition(): array
    {
        return [
            'tooth_code' => fake()->bothify('T##'),
            'tooth_name' => fake()->word(),
        ];
    }
}
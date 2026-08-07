<?php

namespace Database\Factories;

use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Secretary>
 */
class SecretaryFactory extends Factory
{
    protected $model = Secretary::class;

    public function definition(): array
    {
        return [
            'secretary_id' => User::factory()->state([
                'role' => 'secretary',
            ]),
            'shift' => fake()->randomElement(['morning', 'evening', 'night']),
            'office_number' => fake()->bothify('OFF-###'),
        ];
    }
}

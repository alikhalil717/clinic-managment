<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->state([
                'role' => 'admin',
            ]),
            'permissions' => fake()->sentence(),
        ];
    }
}

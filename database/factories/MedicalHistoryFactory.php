<?php

namespace Database\Factories;

use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalHistory>
 */
class MedicalHistoryFactory extends Factory
{
    protected $model = MedicalHistory::class;

    public function definition(): array
    {
        return [
            'record_id' => MedicalRecord::factory(),
            'condition_name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'diagnosed_date' => fake()->date(),
        ];
    }
}
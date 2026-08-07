<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Tooth;
use App\Models\ToothCondition;
use App\Models\TreatmentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ToothCondition>
 */
class ToothConditionFactory extends Factory
{
    protected $model = ToothCondition::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'tooth_id' => Tooth::factory(),
            'doctor_id' => Doctor::factory(),
            'condition_status' => fake()->randomElement(['healthy', 'decay', 'damaged']),
            'treatment_type' => fake()->randomElement(['filling', 'extraction', 'root_canal', 'cleaning', 'whitening', 'crown']),
            'treatment_description' => fake()->sentence(),
            'estimated_price' => fake()->randomFloat(2, 10, 1000),
            'severity_level' => fake()->randomElement(['low', 'medium', 'high']),
            'notes' => fake()->sentence(),
            'session_id' => TreatmentSession::factory(),
            'updated_at' => now(),
        ];
    }
}

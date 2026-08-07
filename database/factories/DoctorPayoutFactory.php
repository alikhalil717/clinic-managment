<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\TreatmentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorPayout>
 */
class DoctorPayoutFactory extends Factory
{
    protected $model = DoctorPayout::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'session_id' => TreatmentSession::factory(),
            'amount' => fake()->randomFloat(2, 20, 5000),
            'payout_date' => now(),
            'status' => fake()->randomElement(['pending', 'paid']),
            'notes' => fake()->sentence(),
        ];
    }
}

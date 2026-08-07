<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Patient;
use App\Models\TreatmentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'amount' => fake()->randomFloat(2, 20, 5000),
            'method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'date' => now(),
            'related_session_id' => TreatmentSession::factory(),
            'type' => fake()->randomElement(['session_payment', 'plan_payment', 'deposit']),
            'is_income' => true,
        ];
    }
}

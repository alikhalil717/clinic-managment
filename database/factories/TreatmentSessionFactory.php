<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentSession>
 */
class TreatmentSessionFactory extends Factory
{
    protected $model = TreatmentSession::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'doctor_id' => Doctor::factory(),
            'patient_id' => Patient::factory(),
            'session_date' => fake()->date(),
            'notes' => fake()->sentence(),
        ];
    }
}
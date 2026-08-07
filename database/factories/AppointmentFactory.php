<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'date' => fake()->date(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => fake()->randomElement(['pending', 'confirmed', 'finished']),
            'notes' => fake()->sentence(),
        ];
    }
}

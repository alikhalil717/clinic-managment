<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorNote>
 */
class DoctorNoteFactory extends Factory
{
    protected $model = DoctorNote::class;

    public function definition(): array
    {
        return [
            'record_id' => MedicalRecord::factory(),
            'doctor_id' => Doctor::factory(),
            'title' => fake()->words(3, true),
            'note' => fake()->paragraph(),
            'note_type' => fake()->randomElement(['general', 'prescription', 'follow_up', 'referral']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

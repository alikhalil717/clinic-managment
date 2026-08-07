<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'doctor_id' => User::factory()->state([
                'role' => 'doctor',
            ]),
            'specialization' => fake()->randomElement(['General Dentistry', 'Orthodontics', 'Endodontics', 'Pediatric Dentistry']),
            'license_number' => fake()->unique()->bothify('LIC-#####'),
            'years_of_experience' => fake()->numberBetween(1, 30),
            'rating' => fake()->randomFloat(2, 1, 5),
            'reviews_count' => fake()->numberBetween(0, 500),
            'about' => fake()->paragraph(),
            'education' => [
                fake()->randomElement(['BDS', 'DDS', 'DMD']) . ' - ' . fake()->city() . ' University',
            ],
            'certifications' => [
                'Board Certified ' . fake()->randomElement(['Orthodontist', 'Endodontist', 'Pediatric Dentist']),
                fake()->randomElement(['Invisalign', 'ClearCorrect']) . '® Certified Provider',
            ],
            'expertise' => fake()->randomElements(
                ['Orthodontics', 'Clear Aligners', 'Braces', 'Smile Design', 'Teeth Whitening', 'Cosmetic Dentistry', 'Endodontics', 'Pediatric Dentistry'],
                fake()->numberBetween(3, 6)
            ),
            'working_days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday'],
            'working_hours' => [
                'saturday' => ['start' => '10:00', 'end' => '15:00'],
                'sunday' => ['start' => '09:00', 'end' => '14:00'],
                'monday' => ['start' => '10:00', 'end' => '15:00'],
                'tuesday' => ['start' => '09:00', 'end' => '14:00'],
                'wednesday' => ['start' => '15:00', 'end' => '20:00'],
            ],
        ];
    }
}

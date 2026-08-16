<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\PatientMedication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientMedication>
 */
class PatientMedicationFactory extends Factory
{
    protected $model = PatientMedication::class;

    public function definition(): array
    {
        return [
            'record_id' => MedicalRecord::factory(),
            'medication_id' => Medication::factory(),
            'dosage' => fake()->randomElement(['250mg', '500mg', '1g', '5ml', '10ml']),
            'frequency' => fake()->randomElement(['once daily', 'twice daily', 'three times daily', 'every 6 hours', 'as needed']),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->date(),
            'prescribed_by' => Doctor::factory(),
            'notes' => fake()->optional()->sentence(),
            'is_current' => true,
        ];
    }
}

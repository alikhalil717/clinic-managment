<?php

namespace Database\Factories;

use App\Models\Diagnosis;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiagnosisFactory extends Factory
{
    protected $model = Diagnosis::class;

    public function definition()
    {
        return [
            'record_id' => MedicalRecord::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'session_id' => null,
            'diagnosis_name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
            'diagnosed_at' => $this->faker->dateTime(),
        ];
    }
}

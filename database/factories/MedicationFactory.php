<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Amoxicillin',
                'Ibuprofen',
                'Paracetamol',
                'Metronidazole',
                'Chlorhexidine',
                'Lidocaine',
                'Prednisolone',
                'Fluconazole',
            ]),
            'category' => fake()->randomElement(['antibiotic', 'analgesic', 'anti-inflammatory', 'antiseptic', 'anesthetic', 'corticosteroid', 'antifungal']),
            'dosage_form' => fake()->randomElement(['tablet', 'capsule', 'syrup', 'gel', 'injection', 'mouthwash']),
            'description' => fake()->sentence(),
            'side_effects' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

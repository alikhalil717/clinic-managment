<?php

namespace Database\Factories;

use App\Models\DentalChart;
use App\Models\DentalChartTooth;
use App\Models\Tooth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DentalChartTooth>
 */
class DentalChartToothFactory extends Factory
{
    protected $model = DentalChartTooth::class;

    public function definition(): array
    {
        return [
            'chart_id' => DentalChart::factory(),
            'tooth_id' => Tooth::factory(),
            'condition_status' => fake()->randomElement(['healthy', 'decay', 'damaged', 'treated', 'missing']),
            'treatment_type' => fake()->randomElement(['filling', 'extraction', 'root_canal', 'cleaning', 'whitening', 'crown']),
            'treatment_description' => fake()->sentence(),
            'estimated_price' => fake()->randomFloat(2, 50, 1500),
            'severity_level' => fake()->randomElement(['low', 'medium', 'high']),
            'notes' => fake()->sentence(),
            'updated_at' => now(),
        ];
    }
}

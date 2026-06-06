<?php

namespace Database\Factories;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['PPN', 'VAT', 'GST', 'Sales Tax']),
            'percentage' => $this->faker->randomElement([5, 8, 10, 11, 12, 15, 20]),
            'is_compound' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(2, true)),
            'description' => $this->faker->sentence(),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'default_price' => $this->faker->randomFloat(2, 5, 2000),
            'unit' => $this->faker->randomElement(['hour', 'unit', 'license', 'month']),
            'is_active' => true,
        ];
    }
}

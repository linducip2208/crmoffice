<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Travel', 'Software', 'Office', 'Marketing', 'Subcontractor']),
            'description' => $this->faker->sentence(),
        ];
    }
}

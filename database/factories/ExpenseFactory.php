<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'category_id' => ExpenseCategory::query()->inRandomOrder()->value('id') ?? ExpenseCategory::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::factory(),
            'expense_date' => now()->subDays(rand(0, 90)),
            'description' => $this->faker->sentence(),
            'is_billable' => $this->faker->boolean(40),
        ];
    }
}

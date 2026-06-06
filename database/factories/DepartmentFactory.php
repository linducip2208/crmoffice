<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Support', 'Sales', 'Billing', 'Engineering']),
            'email_pipe' => $this->faker->unique()->safeEmail(),
            'is_active' => true,
        ];
    }
}

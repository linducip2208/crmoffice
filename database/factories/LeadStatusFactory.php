<?php

namespace Database\Factories;

use App\Models\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadStatusFactory extends Factory
{
    protected $model = LeadStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['New', 'Contacted', 'Qualified', 'Proposal', 'Won', 'Lost']),
            'color' => $this->faker->hexColor(),
            'order' => $this->faker->numberBetween(1, 10),
            'is_won' => false,
            'is_lost' => false,
        ];
    }
}

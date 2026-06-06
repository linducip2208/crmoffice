<?php

namespace Database\Factories;

use App\Models\TicketPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketPriorityFactory extends Factory
{
    protected $model = TicketPriority::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Low', 'Medium', 'High', 'Urgent']),
            'color' => $this->faker->hexColor(),
            'order' => $this->faker->numberBetween(1, 4),
        ];
    }
}

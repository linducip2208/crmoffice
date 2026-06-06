<?php

namespace Database\Factories;

use App\Models\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketStatusFactory extends Factory
{
    protected $model = TicketStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Open', 'In Progress', 'Waiting', 'Resolved', 'Closed']),
            'color' => $this->faker->hexColor(),
            'order' => $this->faker->numberBetween(1, 5),
            'is_open' => true,
            'is_resolved' => false,
        ];
    }
}

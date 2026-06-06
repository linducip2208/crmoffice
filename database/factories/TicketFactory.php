<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'number' => 'TKT-'.$this->faker->unique()->numberBetween(100000, 999999),
            'subject' => ucfirst($this->faker->words(5, true)),
            'body' => $this->faker->paragraph(3),
            'client_id' => Client::query()->inRandomOrder()->value('id'),
            'department_id' => Department::query()->inRandomOrder()->value('id') ?? Department::factory(),
            'priority_id' => TicketPriority::query()->inRandomOrder()->value('id') ?? TicketPriority::factory(),
            'status_id' => TicketStatus::query()->inRandomOrder()->value('id') ?? TicketStatus::factory(),
        ];
    }
}
